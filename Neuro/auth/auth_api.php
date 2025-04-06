<?php
// Database configuration
$db_host = "localhost";
$db_user = "root";  // Replace with your actual database username
$db_pass = "";      // Replace with your actual database password
$db_name = "neurosync";

// Create database connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    sendResponse(false, "Database connection failed: " . $conn->connect_error);
    exit();
}

// Get JSON input data
$input = json_decode(file_get_contents('php://input'), true);

// Check if action is provided
if (!isset($input['action'])) {
    sendResponse(false, "No action specified");
    exit();
}

// Route to the appropriate function based on action
switch ($input['action']) {
    case 'login':
        handleLogin($conn, $input);
        break;
    case 'register':
        handleRegister($conn, $input);
        break;
    default:
        sendResponse(false, "Invalid action");
        break;
}

// Close the database connection
$conn->close();

/**
 * Handle user login
 */
function handleLogin($conn, $data) {
    // Validate input data
    if (!isset($data['user_email']) || !isset($data['user_password'])) {
        sendResponse(false, "Email and password are required");
        return;
    }
    
    // Sanitize input
    $email = $conn->real_escape_string($data['user_email']);
    $password = $data['user_password'];
    
    // Query the database for the user
    $sql = "SELECT user_id, user_name, user_email, user_password, user_status FROM user WHERE user_email = '$email'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify password (assuming password is stored hashed)
        if (password_verify($password, $user['user_password'])) {
            // Check if account is active
            if ($user['user_status'] != '1') {
                sendResponse(false, "Account is not active. Please contact support.");
                return;
            }
            
            // Start session
            session_start();
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['user_name'];
            $_SESSION['user_email'] = $user['user_email'];
            
            // Send success response with user data (except password)
            unset($user['user_password']);
            sendResponse(true, "Login successful", $user);
        } else {
            sendResponse(false, "Invalid email or password");
        }
    } else {
        sendResponse(false, "Invalid email or password");
    }
}

/**
 * Handle user registration
 */
function handleRegister($conn, $data) {
    // Validate input data
    if (!isset($data['user_name']) || !isset($data['user_email']) || !isset($data['user_password'])) {
        sendResponse(false, "Name, email, and password are required");
        return;
    }
    
    // Sanitize input
    $name = $conn->real_escape_string($data['user_name']);
    $email = $conn->real_escape_string($data['user_email']);
    $password = $data['user_password'];
    
    // Check if email already exists
    $checkSql = "SELECT user_id FROM user WHERE user_email = '$email'";
    $checkResult = $conn->query($checkSql);
    
    if ($checkResult->num_rows > 0) {
        sendResponse(false, "Email already in use. Please use a different email or try logging in.");
        return;
    }
    
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $sql = "INSERT INTO user (user_name, user_email, user_password, user_status) 
            VALUES ('$name', '$email', '$hashedPassword', '1')";
    
    if ($conn->query($sql) === TRUE) {
        sendResponse(true, "Registration successful", [
            "user_id" => $conn->insert_id,
            "user_name" => $name,
            "user_email" => $email
        ]);
    } else {
        sendResponse(false, "Registration failed: " . $conn->error);
    }
}

/**
 * Send JSON response
 */
function sendResponse($success, $message, $data = null) {
    $response = [
        "success" => $success,
        "message" => $message
    ];
    
    if ($data) {
        $response = array_merge($response, $data);
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>