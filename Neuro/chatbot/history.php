<?php
$conn = new mysqli("localhost", "root", "", "chatbot_db");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$result = $conn->query("SELECT user_input, bot_response, timestamp FROM messages ORDER BY timestamp DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat History</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="chat-container">
        <h2>Chat History 📜</h2>
        <div id="chat-box">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="user-message"><b>You:</b> <?php echo htmlspecialchars($row["user_input"]); ?></div>
                <div class="bot-message"><b>Bot:</b> <?php echo htmlspecialchars($row["bot_response"]); ?></div>
                <hr>
            <?php endwhile; ?>
        </div>
        <a href="index.html">Back to Chat</a>
    </div>
</body>
</html>
<?php $conn->close(); ?>
