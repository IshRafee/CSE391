<?php
// api/update_appointment.php - Admin endpoint to change date and assigned mechanic
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'POST method required.']);
    exit;
}

try {
    $pdo = getDB();

    $id          = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $newDate     = isset($_POST['appointment_date']) ? trim($_POST['appointment_date']) : '';
    $newMechanic = isset($_POST['mechanic_id']) ? (int)$_POST['mechanic_id'] : 0;
    $status      = isset($_POST['status']) ? trim($_POST['status']) : 'Approved';
    $notes       = isset($_POST['admin_notes']) ? trim($_POST['admin_notes']) : '';

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid appointment ID.']);
        exit;
    }

    // Fetch existing appointment
    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ?");
    $stmt->execute([$id]);
    $current = $stmt->fetch();

    if (!$current) {
        echo json_encode(['success' => false, 'message' => 'Appointment record not found.']);
        exit;
    }

    // Validate new date
    $d = DateTime::createFromFormat('Y-m-d', $newDate);
    if (!$d || $d->format('Y-m-d') !== $newDate) {
        echo json_encode(['success' => false, 'message' => 'Invalid appointment date format (YYYY-MM-DD).']);
        exit;
    }

    // Validate new mechanic
    $mechStmt = $pdo->prepare("SELECT id, name, max_daily_slots FROM mechanics WHERE id = ? AND is_active = 1");
    $mechStmt->execute([$newMechanic]);
    $mechanic = $mechStmt->fetch();

    if (!$mechanic) {
        echo json_encode(['success' => false, 'message' => 'Selected mechanic does not exist or is inactive.']);
        exit;
    }

    // Check if target mechanic has free capacity on target date (excluding this appointment)
    $maxSlots = (int)$mechanic['max_daily_slots'];
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM appointments 
        WHERE mechanic_id = ? 
          AND appointment_date = ? 
          AND id != ? 
          AND status != 'Cancelled'
    ");
    $countStmt->execute([$newMechanic, $newDate, $id]);
    $existingCount = (int)$countStmt->fetchColumn();

    if ($existingCount >= $maxSlots) {
        echo json_encode([
            'success' => false,
            'message' => "Cannot reassign: Senior Mechanic {$mechanic['name']} already has {$existingCount}/{$maxSlots} active appointments scheduled on {$newDate}."
        ]);
        exit;
    }

    // Check duplicate client appointment on new date (excluding this appointment)
    $clientDupStmt = $pdo->prepare("
        SELECT id, mechanic_id 
        FROM appointments 
        WHERE (phone = ? OR car_license = ?)
          AND appointment_date = ? 
          AND id != ? 
          AND status != 'Cancelled'
        LIMIT 1
    ");
    $clientDupStmt->execute([$current['phone'], $current['car_license'], $newDate, $id]);
    if ($clientDupStmt->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => "Cannot change date: The client ({$current['client_name']}) already has another active appointment on {$newDate}."
        ]);
        exit;
    }

    // Update appointment
    $updateStmt = $pdo->prepare("
        UPDATE appointments 
        SET appointment_date = ?, 
            mechanic_id = ?, 
            status = ?, 
            admin_notes = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    $updateStmt->execute([$newDate, $newMechanic, $status, $notes, $id]);

    echo json_encode([
        'success' => true,
        'message' => "Appointment #{$id} updated successfully. Assigned to {$mechanic['name']} on {$newDate}."
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error updating appointment: ' . $e->getMessage()]);
}
