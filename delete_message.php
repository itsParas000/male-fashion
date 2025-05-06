<?php
session_name('SESSION_USER');
session_start();
header('Content-Type: application/json');
include 'php/config.php';

if (!isset($_SESSION['valid'])) {
    die(json_encode(['error' => 'User not logged in']));
}

$user_id = $_SESSION['user_id'];
$chat_session_id = $_SESSION['Email'] ?? 'default_session';
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !array_key_exists('message_id', $data)) {
    die(json_encode(['error' => 'No valid message ID received']));
}

$message_id = $data['message_id'];

if ($message_id === -1) { // Special value to clear all chat
    // Clear all messages for this session
    $stmt = $con->prepare("UPDATE ai_chat_logs SET is_deleted = 1 WHERE chat_session_id = ?");
    $stmt->bind_param("s", $chat_session_id);
    $success = $stmt->execute();

    if (!$success) {
        die(json_encode(['error' => 'Failed to clear chat: ' . $stmt->error]));
    }
} else { // Delete a single message
    $message_id = (int)$message_id;
    $stmt = $con->prepare("UPDATE ai_chat_logs SET is_deleted = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $message_id, $user_id);
    $success = $stmt->execute();

    if (!$success) {
        die(json_encode(['error' => 'Failed to delete message: ' . $stmt->error]));
    }
}

echo json_encode(['success' => $success]);
mysqli_close($con);
?>