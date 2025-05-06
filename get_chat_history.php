<?php
session_name('SESSION_USER');
session_start();
header('Content-Type: application/json');
include 'php/config.php';

if (!isset($_SESSION['valid'])) {
    die(json_encode(['error' => 'User not logged in']));
}

$chat_session_id = $_SESSION['Email'] ?? 'default_session';

$stmt = $con->prepare(
    "SELECT id, sender_type, message, created_at, response_time_ms 
     FROM ai_chat_logs 
     WHERE chat_session_id = ? AND is_deleted = 0 
     ORDER BY created_at ASC"
);
$stmt->bind_param("s", $chat_session_id);
$stmt->execute();
$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode(['messages' => $messages]);
mysqli_close($con);
?>