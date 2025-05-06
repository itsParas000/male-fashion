<?php
require_once '../php/config.php';
session_name('SESSION_ADMIN');
session_start();

if (!isset($_SESSION['valid']) || $_SESSION['role'] !== 'admin') {
    echo json_encode([]);
    exit();
}

$result = mysqli_query($con, "SELECT DISTINCT cm.chat_session_id, MAX(cm.created_at) as latest, u.Username,
                              (SELECT COUNT(*) FROM chat_messages cm2 WHERE cm2.chat_session_id = cm.chat_session_id AND cm2.sender_type = 'user' AND cm2.is_read = 0) as unread_count
                              FROM chat_messages cm 
                              LEFT JOIN users u ON cm.user_id = u.Id 
                              WHERE cm.sender_type = 'user' 
                              GROUP BY cm.chat_session_id 
                              ORDER BY latest DESC");

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = [
        'chat_session_id' => $row['chat_session_id'],
        'latest' => $row['latest'],
        'Username' => htmlspecialchars($row['Username']),
        'unread_count' => $row['unread_count']
    ];
}
echo json_encode($data);
exit();
?>