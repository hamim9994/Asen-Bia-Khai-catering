<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Basic validation
    if (!isset($data['name']) || !isset($data['rating']) || !isset($data['comment'])) {
        echo json_encode(['success' => false, 'message' => 'Name, rating, and comment are required']);
        exit;
    }
    
    $sql = "INSERT INTO reviews (customer_name, rating, comment) 
            VALUES (:name, :rating, :comment)";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        ':name' => $data['name'],
        ':rating' => $data['rating'],
        ':comment' => $data['comment']
    ]);
    
    echo json_encode(['success' => $result, 'message' => $result ? 'Review submitted successfully!' : 'Failed to submit review']);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT * FROM reviews WHERE is_approved = TRUE ORDER BY created_at DESC";
    $stmt = $pdo->query($sql);
    $reviews = $stmt->fetchAll();
    echo json_encode($reviews);
}
?>