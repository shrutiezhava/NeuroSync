<?php
header("Content-Type: application/json");

// Google Gemini API Key
$api_key = "your api key";

// Get user input
$input_text = json_decode(file_get_contents("php://input"), true)['user_input'] ?? '';

if (!$input_text) {
    echo json_encode(["error" => "No input provided"]);
    exit;
}

// Google Gemini API URL
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=$api_key";

// API Request
$data = json_encode([
    "contents" => [
        [
            "role" => "user",
            "parts" => [["text" => $input_text]]
        ]
    ]
]);

$options = [
    "http" => [
        "header"  => "Content-Type: application/json",
        "method"  => "POST",
        "content" => $data,
        "ignore_errors" => true
    ]
];

$response = file_get_contents($url, false, stream_context_create($options));
$response_data = json_decode($response, true);

// Extract AI Response
$bot_responses = [
    "I'm here for you. Tell me what's on your mind. 💙",
    "That sounds tough. You're not alone in this. 🤗",
    "I'm listening. Take your time. 🌿",
    "I'm here to support you. You matter. 💖",
];

$bot_response = $bot_responses[array_rand($bot_responses)];

if (isset($response_data["candidates"][0]["content"]["parts"][0]["text"])) {
    $bot_response = $response_data["candidates"][0]["content"]["parts"][0]["text"];
}

// Save to Database
$conn = new mysqli("localhost", "root", "", "chatbot_db");
if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO messages (user_input, bot_response) VALUES (?, ?)");
$stmt->bind_param("ss", $input_text, $bot_response);
$stmt->execute();
$stmt->close();
$conn->close();

// Return Response
echo json_encode(["response" => $bot_response]);
?>
