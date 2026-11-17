<?php
// api/get_appointment.php - Get single appointment data for Admin edit modal
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Valid appointment ID required.']);
    exit;
}

try {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT a.*, m.name AS mechanic_name, m.specialty AS mechanic_specialty
        FROM appointments a
        JOIN mechanics m ON a.mechanic_id = m.id
        WHERE a.id = ?
    ");
    $stmt->execute([$id]);
    $appointment = $stmt->fetch();

    if (!$appointment) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Appointment not found.']);
        exit;
    }

    echo json_encode(['success' => true, 'appointment' => $appointment]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
