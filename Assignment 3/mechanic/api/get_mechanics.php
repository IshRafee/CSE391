<?php
// api/get_mechanics.php - Get all mechanics and their slot availability for a given date
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db.php';

try {
    $pdo = getDB();

    $date = isset($_GET['date']) ? trim($_GET['date']) : date('Y-m-d');
    
    // Validate date format
    $d = DateTime::createFromFormat('Y-m-d', $date);
    if (!$d || $d->format('Y-m-d') !== $date) {
        $date = date('Y-m-d');
    }

    // Fetch active mechanics
    $stmt = $pdo->prepare("SELECT id, name, specialty, experience_years, phone, max_daily_slots FROM mechanics WHERE is_active = 1 ORDER BY id ASC");
    $stmt->execute();
    $mechanics = $stmt->fetchAll();

    // Fetch booked counts for this date
    $countStmt = $pdo->prepare("
        SELECT mechanic_id, COUNT(*) AS booked_count 
        FROM appointments 
        WHERE appointment_date = ? AND status != 'Cancelled'
        GROUP BY mechanic_id
    ");
    $countStmt->execute([$date]);
    $bookedMap = [];
    while ($row = $countStmt->fetch()) {
        $bookedMap[(int)$row['mechanic_id']] = (int)$row['booked_count'];
    }

    $result = [];
    foreach ($mechanics as $m) {
        $id = (int)$m['id'];
        $maxSlots = (int)$m['max_daily_slots'];
        $booked = isset($bookedMap[$id]) ? $bookedMap[$id] : 0;
        $slotsLeft = max(0, $maxSlots - $booked);
        $isOccupied = ($slotsLeft <= 0);

        $result[] = [
            'id' => $id,
            'name' => $m['name'],
            'specialty' => $m['specialty'],
            'experience_years' => (int)$m['experience_years'],
            'phone' => $m['phone'],
            'max_daily_slots' => $maxSlots,
            'booked_count' => $booked,
            'slots_left' => $slotsLeft,
            'is_occupied' => $isOccupied,
            'status_label' => $isOccupied ? 'Fully Occupied' : ($slotsLeft . ' / ' . $maxSlots . ' slots free')
        ];
    }

    echo json_encode([
        'success' => true,
        'date' => $date,
        'mechanics' => $result
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving mechanic availability: ' . $e->getMessage()
    ]);
}
