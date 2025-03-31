<?php
header("Content-Type: application/json");

// API Key
$api_key = "AIzaSyDSaj87x0jJfRHQtk5b4BvZ8DDVmHQ8B6k";

// Get user input request
$input_text = json_decode(file_get_contents("php://input"), true)['user_input'] ?? '';

if (!$input_text) {
    echo json_encode(["error" => "No input provided"]);
    exit;
}

// API URL
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=$api_key";

// Prepare API request data
$data = json_encode([
    "contents" => [
        [
            "role" => "user",
            "parts" => [
                ["text" => $input_text]
            ]
        ]
    ]
]);

// Set up HTTP request options
$options = [
    "http" => [
        "header"  => "Content-Type: application/json",
        "method"  => "POST",
        "content" => $data,
        "ignore_errors" => true
    ]
];

$context  = stream_context_create($options);
$response = file_get_contents($url, false, $context);
$response_data = json_decode($response, true);

// Extract bot response correctly
$bot_response = "Sorry, I couldn't understand your request.";
if (isset($response_data["candidates"][0]["content"]["parts"])) {
    $bot_response = $response_data["candidates"][0]["content"]["parts"][0]["text"];
}

// Connect to MySQL database
$conn = new mysqli("localhost", "root", "", "chatbot_db");

// Check for connection errors
if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

// Insert chat into the database
$stmt = $conn->prepare("INSERT INTO messages (user_input, bot_response) VALUES (?, ?)");
$stmt->bind_param("ss", $input_text, $bot_response);
$stmt->execute();
$stmt->close();
$conn->close();

// Return chatbot response
echo json_encode(["response" => $bot_response]);
?>
