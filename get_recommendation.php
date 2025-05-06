<?php
session_name('SESSION_USER');
session_start();
header('Content-Type: application/json');
include 'php/config.php';

if (!isset($_SESSION['valid'])) {
    die(json_encode(['error' => 'User not logged in']));
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['prompt'])) {
    die(json_encode(['error' => 'No valid POST data received']));
}

$prompt = trim($data['prompt']);

// Store user message in ai_chat_logs
$stmt = $con->prepare(
    "INSERT INTO ai_chat_logs (chat_session_id, user_id, sender_type, message) 
     VALUES (?, ?, 'user', ?)"
);
$chat_session_id = $_SESSION['Email'] ?? 'default_session';
$stmt->bind_param("sis", $chat_session_id, $user_id, $prompt);
if (!$stmt->execute()) {
    die(json_encode(['error' => 'Failed to store user message: ' . $stmt->error]));
}

// Google Gemini API call with response time measurement
$api_key = 'AIzaSyBna22bmHOERocHsb-bdNrAurgqVkkH-1A'; // Replace with your valid API key
$api_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent';

$start_time = microtime(true); // Start timing

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "$api_url?key=$api_key",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode([
        'contents' => [
            ['role' => 'user', 'parts' => [['text' => $prompt]]]
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'maxOutputTokens' => 150
        ]
    ])
]);

$response = curl_exec($curl);
if (curl_errno($curl)) {
    die(json_encode(['error' => 'API call failed: ' . curl_error($curl)]));
}
curl_close($curl);

$end_time = microtime(true); // End timing
$response_time_ms = (int)(($end_time - $start_time) * 1000); // Convert to milliseconds

$api_data = json_decode($response, true);
$ai_message = $api_data['candidates'][0]['content']['parts'][0]['text'] ?? "Sorry, I couldn’t process that. Try again!";

// Store AI response in ai_chat_logs with response time
$stmt = $con->prepare(
    "INSERT INTO ai_chat_logs (chat_session_id, user_id, sender_type, message, response_time_ms) 
     VALUES (?, NULL, 'ai', ?, ?)"
);
$stmt->bind_param("ssi", $chat_session_id, $ai_message, $response_time_ms);
if (!$stmt->execute()) {
    die(json_encode(['error' => 'Failed to store AI message: ' . $stmt->error]));
}

echo json_encode(['message' => $ai_message, 'response_time_ms' => $response_time_ms]);
mysqli_close($con);
?>