<?php
require_once '../config.php';

header('Content-Type: application/json');

try {
    $conn = Database::getConnection();
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $conn->prepare("SELECT * FROM messages ORDER BY sent_at DESC LIMIT 50");
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
    } 
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $conn->prepare("INSERT INTO messages (user_id, content) VALUES (?, ?)");
        $stmt->bind_param("is", $_SESSION['user_id'], $data['content']);
        $stmt->execute();
        echo json_encode(['status' => 'success']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}