<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Basic validation
    if (!isset($data['name']) || !isset($data['email'])) {
        echo json_encode(['success' => false, 'message' => 'Name and email are required']);
        exit;
    }
    
    $sql = "INSERT INTO orders (customer_name, email, phone, event_date, menu_items, message) 
            VALUES (:name, :email, :phone, :event_date, :menu_items, :message)";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        ':name' => $data['name'],
        ':email' => $data['email'],
        ':phone' => $data['phone'] ?? null,
        ':event_date' => $data['event_date'] ?? null,
        ':menu_items' => $data['menu_items'] ?? null,
        ':message' => $data['message'] ?? null
    ]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Order placed successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to place order']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>