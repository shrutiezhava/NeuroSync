<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChatterBox - Modern Chat</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            background: #1e293b;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .header {
            background: #334155;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #475569;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-name {
            font-weight: 600;
            color: #38bdf8;
        }

        .logout-btn {
            background: #ef4444;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.2s;
        }

        .logout-btn:hover {
            background: #dc2626;
        }

        .chat-container {
            display: flex;
            height: 600px;
        }

        .chat-area {
            flex: 1;
            padding: 1rem;
            overflow-y: auto;
            background: #1e293b;
        }

        .users-sidebar {
            width: 250px;
            background: #334155;
            padding: 1rem;
            border-left: 1px solid #475569;
        }

        .users-title {
            font-weight: 600;
            margin-bottom: 1rem;
            color: #38bdf8;
        }

        .message-input-container {
            padding: 1rem;
            background: #334155;
            border-top: 1px solid #475569;
        }

        .message-form {
            display: flex;
            gap: 1rem;
        }

        .message-input {
            flex: 1;
            background: #1e293b;
            border: 1px solid #475569;
            border-radius: 8px;
            padding: 0.75rem;
            color: #e2e8f0;
            resize: none;
            font-family: inherit;
        }

        .message-input:focus {
            outline: none;
            border-color: #38bdf8;
        }

        .send-btn {
            background: #38bdf8;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.2s;
        }

        .send-btn:hover {
            background: #0ea5e9;
        }

        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #1e293b;
        }

        ::-webkit-scrollbar-thumb {
            background: #475569;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="user-info">
                <span class="user-name"><?php echo $_SESSION['name']; ?></span>
            </div>
            <form action="">
                <input type="submit" name="logout" value="Logout" class="logout-btn">
            </form>
        </div>

        <div class="chat-container">
            <div class="chat-area" id="chatarea"></div>
            <div class="users-sidebar">
                <h3 class="users-title">Online Users</h3>
                <div id="loginperson"></div>
            </div>
        </div>

        <div class="message-input-container">
            <form class="message-form">
                <textarea 
                    class="message-input" 
                    id="text" 
                    placeholder="Type your message..."
                    rows="1"
                ></textarea>
                <button type="button" class="send-btn" onclick="getText()">Send</button>
            </form>
        </div>
    </div>

    <script>
    function getText() {
        var chatInput = document.getElementById('text');
        var message = chatInput.value;

        if (message.trim() === "") return; // Prevent empty messages from being sent

        xhr = new XMLHttpRequest();
        xhr.open('POST', 'chatdb.php', true);
        xhr.setRequestHeader('content-type', 'application/x-www-form-urlencoded');
        xhr.send('chat=' + encodeURIComponent(message));

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                chatInput.value = ""; // Clear the text area after sending the message
            }
        };
    }

    function setText() {
        xhr = new XMLHttpRequest();
        xhr.open('POST', 'chatFetch.php', true);
        xhr.setRequestHeader('content-type', 'application/x-www-form-urlencoded');
        xhr.send();
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                document.getElementById('chatarea').innerHTML = xhr.responseText;
            }
        };
    }
    setInterval(setText, 2000);
    setInterval(users, 3000);

    function users() {
        xhr1 = new XMLHttpRequest();
        xhr1.open('POST', 'userFetch.php', true);
        xhr1.setRequestHeader('content-type', 'application/x-www-form-urlencoded');
        xhr1.send();
        xhr1.onreadystatechange = function () {
            if (xhr1.readyState === 4 && xhr1.status === 200) {
                document.getElementById('loginperson').innerHTML = xhr1.responseText;
            }
        };
    }
    </script>

    <?php
    if(!isset($_SESSION['email']) && !isset($_SESSION['password'])){
        header('location: practice.php');
    }
    ?>
</body>
</html>
