<?php
include("php/config.php");
session_name('SESSION_USER');
session_start();

if (!isset($_SESSION['valid'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$chat_session_id = mysqli_real_escape_string($con, $_POST['chat_session_id']);
$query = "UPDATE chat_messages SET is_deleted_by_user = 1 WHERE chat_session_id = '$chat_session_id'";
if (mysqli_query($con, $query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => mysqli_error($con)]);
}
exit();
?>