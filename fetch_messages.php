<?php
include("php/config.php");
session_name('SESSION_USER');
session_start();

if (!isset($_SESSION['valid'])) {
    echo json_encode([]);
    exit();
}

$chat_session_id = mysqli_real_escape_string($con, $_POST['chat_session_id']);
$query = "SELECT * FROM chat_messages WHERE chat_session_id = '$chat_session_id' AND is_deleted_by_user = 0 ORDER BY created_at ASC";
$result = mysqli_query($con, $query);
$messages = [];
while ($row = mysqli_fetch_assoc($result)) {
    $messages[] = [
        'sender_type' => $row['sender_type'],
        'message' => htmlspecialchars($row['message']),
        'created_at' => $row['created_at']
    ];
}
echo json_encode($messages);
exit();
?>