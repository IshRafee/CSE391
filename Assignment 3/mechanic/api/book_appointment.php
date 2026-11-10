<?php
// api/book_appointment.php - Processes and validates client appointment booking
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method. Only POST allowed.']);
    exit;
}

try {
    $pdo = getDB();

    // 1. Retrieve and sanitize input fields
    $clientName  = isset($_POST['client_name']) ? trim($_POST['client_name']) : '';
    $address     = isset($_POST['address']) ? trim($_POST['address']) : '';
    $phone       = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $carLicense  = isset($_POST['car_license']) ? strtoupper(trim($_POST['car_license'])) : '';
    $carEngine   = isset($_POST['car_engine']) ? trim($_POST['car_engine']) : '';
    $appointDate = isset($_POST['appointment_date']) ? trim($_POST['appointment_date']) : '';
    $mechanicId  = isset($_POST['mechanic_id']) ? (int)$_POST['mechanic_id'] : 0;

    $errors = [];

    // 2. Validate Client Name
    if (empty($clientName)) {
        $errors['client_name'] = 'Client name is required and cannot be empty.';
    } elseif (strlen($clientName) < 2) {
        $errors['client_name'] = 'Client name must be at least 2 characters.';
    }

    // 3. Validate Address
    if (empty($address)) {
        $errors['address'] = 'Address is required and cannot be empty.';
    }

    // 4. Validate Phone (Prompt requirement: "that it has only number in it")
    if (empty($phone)) {
        $errors['phone'] = 'Phone number is required and cannot be empty.';
    } elseif (!preg_match('/^[0-9]+$/', $phone)) {
        $errors['phone'] = 'Phone number must contain only numbers (digits 0-9).';
    } elseif (strlen($phone) < 7 || strlen($phone) > 15) {
        $errors['phone'] = 'Phone number must be between 7 and 15 digits.';
    }

    // 5. Validate Car License / Registration Number
    if (empty($carLicense)) {
        $errors['car_license'] = 'Car License/Registration number is required and cannot be empty.';
    }

    // 6. Validate Car Engine Number (Prompt requirement: "that it has only number in it")
    if (empty($carEngine)) {
        $errors['car_engine'] = 'Car Engine number is required and cannot be empty.';
    } elseif (!preg_match('/^[0-9]+$/', $carEngine)) {
        $errors['car_engine'] = 'Car Engine number must contain only numbers (digits 0-9).';
    }

    // 7. Validate Appointment Date (Prompt requirement: "that it has only date in it")
    if (empty($appointDate)) {
        $errors['appointment_date'] = 'Appointment date is required.';
    } else {
        $d = DateTime::createFromFormat('Y-m-d', $appointDate);
        if (!$d || $d->format('Y-m-d') !== $appointDate) {
            $errors['appointment_date'] = 'Appointment date format must be a valid date (YYYY-MM-DD).';
        } else {
            // Cannot book in the past
            $today = date('Y-m-d');
            if ($appointDate < $today) {
                $errors['appointment_date'] = 'Appointment date cannot be in the past.';
            }
        }
    }

    // 8. Validate Mechanic Selection (Prompt requirement: "must select a mechanic from the list to ensure the entry")
    if ($mechanicId <= 0) {
        $errors['mechanic_id'] = 'You must select a senior mechanic from the list.';
    } else {
        $mechStmt = $pdo->prepare("SELECT id, name, specialty, max_daily_slots FROM mechanics WHERE id = ? AND is_active = 1");
        $mechStmt->execute([$mechanicId]);
        $mechanic = $mechStmt->fetch();
        if (!$mechanic) {
            $errors['mechanic_id'] = 'Selected mechanic was not found or is inactive.';
        }
    }

    // If initial validation fails, return all validation errors
    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'code' => 'VALIDATION_ERROR',
            'message' => 'Please correct the highlighted errors in the form.',
            'errors' => $errors
        ]);
        exit;
    }

    // 9. Requirement: Check whether client has already taken any appointment from any mechanic on certain date or not
    // "After a client makes data submission, it should check whether the client has already taken any appointment
    // from any mechanic on certain date or not. If not, then the client is allowed to take an appointment otherwise
    // s/he will be notifying that he has already taken an appointment on that specific date."
    $dupStmt = $pdo->prepare("
        SELECT a.id, a.client_name, a.appointment_date, m.name as mechanic_name
        FROM appointments a
        JOIN mechanics m ON a.mechanic_id = m.id
        WHERE (a.phone = ? OR a.car_license = ?)
          AND a.appointment_date = ?
          AND a.status != 'Cancelled'
        LIMIT 1
    ");
    $dupStmt->execute([$phone, $carLicense, $appointDate]);
    $existingBooking = $dupStmt->fetch();

    if ($existingBooking) {
        echo json_encode([
            'success' => false,
            'code' => 'ALREADY_BOOKED',
            'message' => "Appointment Rejected: You have already taken an appointment on {$appointDate} (with mechanic {$existingBooking['mechanic_name']}, Ticket #{$existingBooking['id']}). Clients and vehicles are permitted a maximum of one appointment per date."
        ]);
        exit;
    }

    // 10. Requirement: Check if mechanic is occupied with maximum number of appointments (4)
    // "And also if the mechanic is occupied with maximum no. of appointment the system should notify client
    // otherwise it will approve the appointment."
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) AS booked_count
        FROM appointments
        WHERE mechanic_id = ?
          AND appointment_date = ?
          AND status != 'Cancelled'
    ");
    $countStmt->execute([$mechanicId, $appointDate]);
    $currentBooked = (int)$countStmt->fetchColumn();
    $maxSlots = (int)$mechanic['max_daily_slots'];

    if ($currentBooked >= $maxSlots) {
        echo json_encode([
            'success' => false,
            'code' => 'MECHANIC_OCCUPIED',
            'message' => "Appointment Rejected: Senior Mechanic {$mechanic['name']} is already occupied with the maximum limit of {$maxSlots} active cars for {$appointDate}. Please select another available mechanic or choose a different date."
        ]);
        exit;
    }

    // 11. All rules passed! Insert appointment and approve
    $insertStmt = $pdo->prepare("
        INSERT INTO appointments 
        (client_name, address, phone, car_license, car_engine, appointment_date, mechanic_id, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Approved')
    ");
    $insertStmt->execute([
        $clientName,
        $address,
        $phone,
        $carLicense,
        $carEngine,
        $appointDate,
        $mechanicId
    ]);

    $newAppointmentId = $pdo->lastInsertId();

    // Fetch full appointment info to return in response
    $fetchStmt = $pdo->prepare("
        SELECT a.*, m.name AS mechanic_name, m.specialty AS mechanic_specialty, m.phone AS mechanic_phone
        FROM appointments a
        JOIN mechanics m ON a.mechanic_id = m.id
        WHERE a.id = ?
    ");
    $fetchStmt->execute([$newAppointmentId]);
    $appointmentData = $fetchStmt->fetch();

    echo json_encode([
        'success' => true,
        'code' => 'APPROVED',
        'message' => "Appointment Approved! Your appointment with Senior Mechanic {$mechanic['name']} on {$appointDate} has been confirmed.",
        'appointment' => $appointmentData
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'code' => 'SERVER_ERROR',
        'message' => 'An error occurred while booking appointment: ' . $e->getMessage()
    ]);
}
