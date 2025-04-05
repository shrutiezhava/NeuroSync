<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neuro - Login & Registration</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #FF6B6B;
            --secondary-color: #4ECDC4;
            --accent-color: #FFE66D;
            --error-color: #FF5252;
            --background-color: #f8f9fa;
            --card-background: #ffffff;
            --text-color: #2C3E50;
            --gradient: linear-gradient(135deg, #FF6B6B, #4ECDC4);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: var(--background-color);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(255, 107, 107, 0.1) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, rgba(78, 205, 196, 0.1) 0%, transparent 20%);
        }

        .container {
            display: flex;
            gap: 40px;
            max-width: 1200px;
            width: 100%;
            flex-wrap: wrap;
            justify-content: center;
        }

        .form-card {
            background: var(--card-background);
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .form-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: var(--gradient);
        }

        .form-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }

        h1 {
            color: var(--primary-color);
            margin-bottom: 30px;
            text-align: center;
            font-size: 2.2em;
            font-weight: 600;
            position: relative;
            display: inline-block;
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: var(--gradient);
            border-radius: 3px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-color);
            font-weight: 500;
            font-size: 0.95em;
        }

        input, select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--secondary-color);
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(78, 205, 196, 0.1);
        }

        .btn {
            background: var(--gradient);
            color: white;
            padding: 14px 28px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
        }

        .btn:active {
            transform: translateY(0);
        }

        .message {
            margin-top: 20px;
            padding: 12px 20px;
            border-radius: 12px;
            text-align: center;
            font-size: 0.95em;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-10px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .success {
            background: #e8f5e9;
            color: var(--secondary-color);
            border: 1px solid rgba(78, 205, 196, 0.2);
        }

        .error {
            background: #ffebee;
            color: var(--error-color);
            border: 1px solid rgba(255, 82, 82, 0.2);
        }

        .validation-message {
            font-size: 14px;
            margin-top: 8px;
            padding: 4px 8px;
            border-radius: 6px;
            display: inline-block;
        }

        @media (max-width: 768px) {
            .container {
                gap: 20px;
            }
            
            .form-card {
                padding: 30px 20px;
            }

            h1 {
                font-size: 1.8em;
            }
        }

        /* Cute elements */
        .form-card::after {
            content: '✨';
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 24px;
            opacity: 0.5;
            animation: twinkle 2s infinite;
        }

        @keyframes twinkle {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--gradient);
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Registration Form -->
        <div class="form-card">
            <h1>Registration</h1>
            <?php if(isset($_GET['registeration_successfull'])){ ?>
                <div class="message success"><?php echo $_GET['registeration_successfull']; ?></div>
            <?php } ?>
            <form method="post" action="insert.php">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" required placeholder="Enter your name" />
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" onBlur="getEmail(this.value)" required placeholder="Enter your email" />
                    <div id="emailDiv" class="validation-message"></div>
                </div>

                <div class="form-group">
                    <label>Country</label>
                    <select name="country" onchange="getcity(this.value)" required>
                        <option value="">Select a country</option>
                        <?php
                        include_once('config.php');
                        $result = mysqli_query($conn, 'select * from country');
                        if(!$result){
                            echo 'query failed';
                        }
                        while($row = mysqli_fetch_assoc($result)){ ?>
                            <option value="<?php echo $row['country_id']; ?>">
                                <?php echo $row['country_name']; ?>
                            </option>
                        <?php } ?>
                    </select>
                    <div id="city_display" class="validation-message"></div>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="pass1" id="pass1" required placeholder="Enter your password" />
                </div>

                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="pass2" id="pass2" onblur="password()" required placeholder="Confirm your password" />
                    <div id="cnfrmpass" class="validation-message"></div>
                </div>

                <button type="submit" name="sbt" class="btn">Register</button>
            </form>
        </div>

        <!-- Login Form -->
        <div class="form-card">
            <h1>Login</h1>
            <?php if(isset($_GET['logout_successfully'])){ ?>
                <div class="message success"><?php echo $_GET['logout_successfully']; ?></div>
            <?php } ?>
            <form method="post" action="process.php">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="Enter your email" />
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Enter your password" />
                </div>

                <button type="submit" name="loginbtn" class="btn">Login</button>
            </form>
            <?php if(isset($_GET['login_error'])){ ?>
                <div class="message error"><?php echo $_GET['login_error']; ?></div>
            <?php } ?>
        </div>
    </div>

    <script>
        function getcity(id) {
            if (!id) return;
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'test.php?idd=' + id, true);
            xhr.send();
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    document.getElementById("city_display").innerHTML = xhr.responseText;
                }
            }
        }

        function getEmail(emailid) {
            if (!emailid) return;
            const email = new XMLHttpRequest();
            email.open('GET', 'test2.php?email=' + emailid, true);
            email.send();
            email.onreadystatechange = function() {
                if (email.readyState == 4 && email.status == 200) {
                    document.getElementById('emailDiv').innerHTML = email.responseText;
                }
            }
        }

        function password() {
            const a = document.getElementById('pass1').value;
            const b = document.getElementById('pass2').value;
            const messageDiv = document.getElementById('cnfrmpass');
            
            if (a === b) {
                messageDiv.innerHTML = "<span style='color: var(--secondary-color)'>✨ Passwords match</span>";
            } else {
                messageDiv.innerHTML = "<span style='color: var(--error-color)'>❌ Passwords do not match</span>";
            }
        }

        // Add smooth scroll behavior
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html> 