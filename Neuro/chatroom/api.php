<?php
include 'config.php';

header('Content-Type: application/json');

try {
    $action = $_GET['action'] ?? '';
    $data = json_decode(file_get_contents('php://input'), true);

    switch($action) {
        case 'send_message':
            $stmt = $conn->prepare("INSERT INTO chat (chat_person_name, chat_value) VALUES (?, ?)");
            $stmt->bind_param("ss", $_SESSION['name'], $data['message']);
            $stmt->execute();
            echo json_encode(['status' => 'success']);
            break;

        case 'get_messages':
            $result = $conn->query("SELECT * FROM chat ORDER BY chat_time DESC LIMIT 50");
            echo json_encode($result->fetch_all(MYSQLI_ASSOC));
            break;

        case 'check_email':
            $stmt = $conn->prepare("SELECT user_email FROM user WHERE user_email = ?");
            $stmt->bind_param("s", $data['email']);
            $stmt->execute();
            echo json_encode(['available' => $stmt->get_result()->num_rows === 0]);
            break;

        case 'get_users':
            $result = $conn->query("SELECT user_name, user_status FROM user");
            $users = [];
            while($row = $result->fetch_assoc()) {
                $users[] = [
                    'name' => htmlspecialchars($row['user_name']),
                    'status' => (bool)$row['user_status']
                ];
            }
            echo json_encode($users);
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch(Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}