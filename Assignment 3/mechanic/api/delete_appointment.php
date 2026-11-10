<?php
// api/delete_appointment.php - Admin endpoint to cancel or delete an appointment
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'POST method required.']);
    exit;
}

try {
    $pdo = getDB();

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $action = isset($_POST['action']) ? trim($_POST['action']) : 'cancel'; // 'cancel' or 'delete'

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid appointment ID.']);
        exit;
    }

    if ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = ?");
        $stmt->execute([$id]);
        $msg = "Appointment #{$id} has been permanently deleted.";
    } else {
        // Soft cancel to keep audit history while freeing up the mechanic slot
        $stmt = $pdo->prepare("UPDATE appointments SET status = 'Cancelled', updated_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);
        $msg = "Appointment #{$id} has been cancelled and the mechanic slot has been released.";
    }

    echo json_encode(['success' => true, 'message' => $msg]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
