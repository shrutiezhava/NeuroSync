<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ChatterBox</title>
    <style>
        /* Combined CSS from all files */
        :root {
            --primary: #38bdf8;
            --success: #00CC00;
            --danger: #FF0000;
            --bg: #0f172a;
        }
        
        /* Include all CSS rules from original files here */
    </style>
</head>
<body>
    <?php if(!isset($_SESSION['user_id'])): ?>
        <!-- Connect to your global auth system -->
        <div id="global-auth-container">
            <!-- Your existing global auth components -->
        </div>
    <?php else: ?>
        <div class="container">
            <!-- Chat Interface -->
            <div class="header">
                <div class="user-info">
                    <span class="user-name"><?= htmlspecialchars($_SESSION['name']) ?></span>
                </div>
            </div>

            <div class="chat-container">
                <div class="chat-area" id="chatarea"></div>
                <div class="users-sidebar">
                    <h3 class="users-title">Users Status</h3>
                    <div id="user-status"></div>
                </div>
            </div>

            <div class="message-input-container">
                <form class="message-form">
                    <textarea class="message-input" id="text" placeholder="Type message..."></textarea>
                    <button type="button" class="send-btn" onclick="sendMessage()">Send</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <script>
    // Unified API Handler
    const apiHandler = async (action, data = {}) => {
        try {
            const response = await fetch(`api.php?action=${action}`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            });
            return await response.json();
        } catch (error) {
            console.error('API Error:', error);
        }
    };

    // Chat Functions
    const sendMessage = () => {
        const message = document.getElementById('text').value.trim();
        if(message) {
            apiHandler('send_message', {message})
                .then(() => document.getElementById('text').value = '');
        }
    };

    // Auto-refresh
    setInterval(async () => {
        const messages = await apiHandler('get_messages');
        document.getElementById('chatarea').innerHTML = messages
            .map(msg => `<b>${msg.username}:</b> ${msg.content}<br>`)
            .join('');

        const users = await apiHandler('get_users');
        document.getElementById('user-status').innerHTML = users
            .map(user => `<div class="user-status-item" style="color:${user.status ? 'var(--success)' : 'var(--danger)'}">
                ${user.name} (${user.status ? 'Online' : 'Offline'})
            </div>`)
            .join('');
    }, 2000);
    </script>
</body>
</html>