<?php
// api/check_client_booking.php - Real-time client appointment check
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db.php';

try {
    $pdo = getDB();

    $date = isset($_GET['date']) ? trim($_GET['date']) : '';
    $phone = isset($_GET['phone']) ? trim($_GET['phone']) : '';
    $carLicense = isset($_GET['car_license']) ? trim($_GET['car_license']) : '';

    if (empty($date) || (empty($phone) && empty($carLicense))) {
        echo json_encode(['has_booking' => false]);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT a.id, a.client_name, a.phone, a.car_license, a.appointment_date, m.name as mechanic_name
        FROM appointments a
        JOIN mechanics m ON a.mechanic_id = m.id
        WHERE ((? != '' AND a.phone = ?) OR (? != '' AND a.car_license = ?))
          AND a.appointment_date = ?
          AND a.status != 'Cancelled'
        LIMIT 1
    ");
    $stmt->execute([$phone, $phone, $carLicense, $carLicense, $date]);
    $existing = $stmt->fetch();

    if ($existing) {
        echo json_encode([
            'has_booking' => true,
            'message' => "You already have an appointment on " . htmlspecialchars($date) . " with " . htmlspecialchars($existing['mechanic_name']) . " (Appointment #" . $existing['id'] . ").",
            'appointment' => $existing
        ]);
    } else {
        echo json_encode(['has_booking' => false]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['has_booking' => false, 'error' => $e->getMessage()]);
}
