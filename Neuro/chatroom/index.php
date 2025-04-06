<?php
// Start session and check authentication
session_start();
include 'config.php';

// Redirect to your global login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/index.html'); // Update with your auth URL
    exit();
}

// Fetch user data from your global auth system
$user_id = $_SESSION['user_id'];
$user_query = $conn->prepare("SELECT user_name FROM users WHERE user_id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user = $user_query->get_result()->fetch_assoc();
?>
<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html>
<!-- Your existing HTML content -->
<a href="chatroom.php" class="cta-button">Start Chatting Now</a>
<!-- Rest of your landing page -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ChatterBox</title>
    <style>
        /* Combined CSS from both index.html and chatroom.php */
        :root {
            --primary: #38bdf8;
            --success: #00CC00;
            --danger: #FF0000;
            --bg: #0f172a;
        }
        
        /* Include all your original CSS styles here */
        /* From both index.html and chatroom.php */
    </style>
</head>
<body>
    <div class="container">
        <!-- Chat Interface from original chatroom.php -->
        <div class="header">
            <div class="user-info">
                <span class="user-name"><?= htmlspecialchars($user['user_name']) ?></span>
            </div>
            <form action="/global-logout" method="POST"> <!-- Update with your logout URL -->
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>

        <div class="chat-container">
            <div class="chat-area" id="chatarea"></div>
            <div class="users-sidebar">
                <h3 class="users-title">Online Users</h3>
                <div id="user-status"></div>
            </div>
        </div>

        <div class="message-input-container">
            <form class="message-form" onsubmit="sendMessage(event)">
                <textarea class="message-input" id="text" placeholder="Type message..." required></textarea>
                <button type="submit" class="send-btn">Send</button>
            </form>
        </div>
    </div>

    <script>
    // Modernized Chat Functions
    const API_BASE = '/api/chat'; // Update with your API endpoint

    async function sendMessage(e) {
        e.preventDefault();
        const message = document.getElementById('text').value.trim();
        if (!message) return;

        try {
            const response = await fetch(`${API_BASE}/send`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>'
                },
                body: JSON.stringify({message})
            });
            
            if (response.ok) {
                document.getElementById('text').value = '';
            }
        } catch (error) {
            console.error('Send message error:', error);
        }
    }

    // Real-time Updates
    async function updateChat() {
        try {
            // Fetch messages
            const messagesRes = await fetch(`${API_BASE}/messages`);
            const messages = await messagesRes.json();
            
            document.getElementById('chatarea').innerHTML = messages
                .map(msg => `
                    <div class="message">
                        <b>${msg.username}:</b> 
                        <span>${msg.content}</span>
                        <small>${new Date(msg.timestamp).toLocaleTimeString()}</small>
                    </div>
                `).join('');

            // Fetch user status
            const usersRes = await fetch(`${API_BASE}/users`);
            const users = await usersRes.json();
            
            document.getElementById('user-status').innerHTML = users
                .map(user => `
                    <div class="user-status ${user.online ? 'online' : 'offline'}">
                        ${user.name}
                        <span>${user.online ? '🟢' : '⚫'}</span>
                    </div>
                `).join('');
                
        } catch (error) {
            console.error('Update error:', error);
        }
    }

    // Initial load
    updateChat();
    // Refresh every 2 seconds
    setInterval(updateChat, 2000);
    </script>
</body>
</html>