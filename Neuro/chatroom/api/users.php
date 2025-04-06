<?php
declare(strict_types=1);
require '../includes/config.php';

header('Content-Type: application/json');
$conn = Database::connect();

$result = $conn->execute_query("
    SELECT 
        user_id,
        user_name,
        last_activity > NOW() - INTERVAL 5 MINUTE AS is_online
    FROM users
    ORDER BY is_online DESC, user_name
");

echo json_encode($result->fetch_all(MYSQLI_ASSOC));