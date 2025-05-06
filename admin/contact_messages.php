<?php
// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
header("Pragma: no-cache");

session_name('SESSION_ADMIN');
session_start();
require_once '../php/config.php';

if (!isset($_SESSION['valid']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// API-like endpoints
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['fetch_messages'])) {
        // Fetch message list
        $result = mysqli_query($con, "SELECT DISTINCT cm.chat_session_id, MAX(cm.created_at) as latest, u.Username,
                                      (SELECT COUNT(*) FROM chat_messages cm2 WHERE cm2.chat_session_id = cm.chat_session_id AND cm2.sender_type = 'user' AND cm2.is_read = 0) as unread_count
                                      FROM chat_messages cm 
                                      LEFT JOIN users u ON cm.user_id = u.Id 
                                      WHERE cm.sender_type = 'user' 
                                      GROUP BY cm.chat_session_id 
                                      ORDER BY latest DESC");
        if (!$result) {
            echo json_encode(['error' => 'Query failed: ' . mysqli_error($con)]);
            exit();
        }
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
    } elseif (isset($_POST['fetch_chat']) && isset($_POST['chat_session_id'])) {
        // Fetch chat messages (include is_deleted_by_user for admin acknowledgment)
        $chat_session_id = mysqli_real_escape_string($con, $_POST['chat_session_id']);
        $query = "SELECT sender_type, message, created_at, is_deleted_by_user 
                  FROM chat_messages 
                  WHERE chat_session_id = '$chat_session_id' 
                  ORDER BY created_at ASC";
        $result = mysqli_query($con, $query);
        if (!$result) {
            echo json_encode(['error' => 'Query failed: ' . mysqli_error($con)]);
            exit();
        }
        $messages = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $messages[] = [
                'sender_type' => $row['sender_type'],
                'message' => htmlspecialchars($row['message']),
                'created_at' => $row['created_at'],
                'is_deleted_by_user' => (int)$row['is_deleted_by_user']
            ];
        }
        echo json_encode($messages);
        exit();
    } elseif (isset($_POST['message']) && isset($_POST['chat_session_id'])) {
        // Handle reply submission
        $chat_session_id = mysqli_real_escape_string($con, $_POST['chat_session_id']);
        $message = mysqli_real_escape_string($con, $_POST['message']);
        $query = "INSERT INTO chat_messages (chat_session_id, sender_type, message) 
                  VALUES ('$chat_session_id', 'admin', '$message')";
        if (mysqli_query($con, $query)) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                echo json_encode(['success' => true]);
                exit();
            } else {
                header("Location: contact_messages.php?chat=$chat_session_id");
                exit();
            }
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_error($con)]);
            exit();
        }
    }
}

// Mark messages as read when a chat is viewed
if (isset($_GET['chat'])) {
    $chat_session_id = mysqli_real_escape_string($con, $_GET['chat']);
    $update_query = "UPDATE chat_messages SET is_read = 1 WHERE chat_session_id = '$chat_session_id' AND sender_type = 'user'";
    mysqli_query($con, $update_query);
}

// Fetch all chat sessions with unread message count for initial render
$messages_result = mysqli_query($con, "SELECT DISTINCT cm.chat_session_id, MAX(cm.created_at) as latest, u.Username,
                                       (SELECT COUNT(*) FROM chat_messages cm2 WHERE cm2.chat_session_id = cm.chat_session_id AND cm2.sender_type = 'user' AND cm2.is_read = 0) as unread_count
                                       FROM chat_messages cm 
                                       LEFT JOIN users u ON cm.user_id = u.Id 
                                       WHERE cm.sender_type = 'user' 
                                       GROUP BY cm.chat_session_id 
                                       ORDER BY latest DESC");

$chat_sessions = [];
while ($row = mysqli_fetch_assoc($messages_result)) {
    $chat_sessions[$row['chat_session_id']] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin - Contact Messages</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="navigation.css">
    <style>
        .chat-bubble { 
            max-width: 70%; 
            padding: 12px 16px; 
            border-radius: 12px; 
            margin: 8px 0; 
        }
        .chat-bubble.user { 
            background-color: #dcf8c6; 
            align-self: flex-start; 
        }
        .chat-bubble.admin { 
            background-color: #e5e7eb; 
            align-self: flex-end; 
        }
        .chat-container { 
            height: 50vh; 
            overflow-y: auto; 
            display: flex; 
            flex-direction: column; 
        }
        .unread-bubble { 
            display: inline-block; 
            width: 20px; 
            height: 20px; 
            background-color: #ef4444; 
            color: white; 
            border-radius: 50%; 
            text-align: center; 
            line-height: 20px; 
            font-size: 12px; 
        }
        .messages-list {
            max-height: 80vh;
            overflow-y: auto;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <div class="container">
        <!-- Navigation -->
        <div class="navigation">
            <?php include 'navigation.php'; ?>
        </div>

        <!-- Main Content -->
        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <ion-icon name="menu-outline"></ion-icon>
                </div>
            </div>
            <div class="p-8 flex flex-row h-[calc(100vh-60px)]">
                <!-- Messages List -->
                <div class="w-1/3 bg-white shadow-lg p-4 rounded-lg mr-4">
                    <h1 class="text-xl font-bold text-gray-800 mb-4">Messages</h1>
                    <div class="messages-list" id="messages-list">
                        <?php foreach ($chat_sessions as $row) { ?>
                            <a href="?chat=<?php echo $row['chat_session_id']; ?>" class="block p-3 border-b hover:bg-gray-100 <?php echo (isset($_GET['chat']) && $_GET['chat'] == $row['chat_session_id']) ? 'bg-gray-200' : ''; ?>">
                                <div class="flex justify-between">
                                    <div>
                                        <p class="font-semibold"><?php echo htmlspecialchars($row['Username'] ?: $row['chat_session_id']); ?></p>
                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($row['chat_session_id']); ?></p>
                                    </div>
                                    <div class="flex items-center">
                                        <?php if ($row['unread_count'] > 0) { ?>
                                            <span class="unread-bubble"><?php echo $row['unread_count']; ?></span>
                                        <?php } ?>
                                        <p class="text-sm text-gray-500 ml-2"><?php echo date('H:i', strtotime($row['latest'])); ?></p>
                                    </div>
                                </div>
                            </a>
                        <?php } ?>
                    </div>
                </div>

                <!-- Chat Area -->
                <div class="flex-1 flex flex-col">
                    <?php if (isset($_GET['chat'])) { 
                        $selected_chat = $_GET['chat'];
                        $chat_user = isset($chat_sessions[$selected_chat]) ? $chat_sessions[$selected_chat]['Username'] : $selected_chat;
                    ?>
                        <div class="bg-white rounded-lg shadow-lg flex-1 flex flex-col">
                            <div class="p-4 border-b">
                                <h2 class="text-xl font-bold">Chat with: <?php echo htmlspecialchars($chat_user); ?></h2>
                                <p class="text-gray-600"><?php echo htmlspecialchars($selected_chat); ?></p>
                            </div>
                            <div class="chat-container p-4" id="chat-container"></div>
                            <form id="chat-form" class="p-4 border-t">
                                <input type="hidden" name="chat_session_id" value="<?php echo $selected_chat; ?>">
                                <div class="flex">
                                    <textarea id="message-input" name="message" class="w-full p-2 border rounded-l focus:outline-none focus:ring-2 focus:ring-gray-800" placeholder="Type your reply..." required></textarea>
                                    <button type="submit" class="bg-gray-800 text-white px-4 rounded-r hover:bg-gray-700">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php } else { ?>
                        <div class="flex-1 flex items-center justify-center">
                            <p class="text-gray-500">Select a message to start chatting</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="main.js"></script>
    <script>
        const chatContainer = document.getElementById('chat-container');
        const messagesList = document.getElementById('messages-list');
        const chatForm = document.getElementById('chat-form');
        const messageInput = document.getElementById('message-input');
        const chatSessionId = '<?php echo isset($_GET['chat']) ? $_GET['chat'] : ''; ?>';

        // Fetch chat messages dynamically
        function fetchChatMessages() {
            if (!chatSessionId) return;
            fetch('contact_messages.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'fetch_chat=true&chat_session_id=' + encodeURIComponent(chatSessionId)
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(messages => {
                chatContainer.innerHTML = '';
                messages.forEach(msg => {
                    const div = document.createElement('div');
                    div.className = `chat-bubble ${msg.sender_type}`;
                    div.innerHTML = `<p>${msg.message}${msg.is_deleted_by_user ? ' <span class="text-red-500 text-xs">[Deleted by user]</span>' : ''}</p>
                                     <p class="text-xs text-gray-500">${msg.created_at}</p>`;
                    chatContainer.appendChild(div);
                });
                chatContainer.scrollTop = chatContainer.scrollHeight;
            })
            .catch(error => console.error('Error fetching messages:', error));
        }

        // Fetch message list dynamically
        // Fetch message list dynamically

      function fetchMessagesList() {
        fetch('contact_messages.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'fetch_messages=true'
     })
     .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        messagesList.innerHTML = '';
        data.forEach(row => {
            const a = document.createElement('a');
            a.href = `?chat=${row.chat_session_id}`;
            a.className = `block p-3 border-b hover:bg-gray-100 ${chatSessionId === row.chat_session_id ? 'bg-gray-200' : ''}`;
            a.innerHTML = `
                <div class="flex justify-between">
                    <div>
                        <p class="font-semibold">${row.Username || row.chat_session_id}</p>
                        <p class="text-sm text-gray-600">${row.chat_session_id}</p>
                    </div>
                    <div class="flex items-center">
                        ${row.unread_count > 0 && chatSessionId !== row.chat_session_id ? `<span class="unread-bubble">${row.unread_count}</span>` : ''}
                        <p class="text-sm text-gray-500 ml-2">${new Date(row.latest).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</p>
                    </div>
                </div>
            `;
            messagesList.appendChild(a);
        });
    })
    .catch(error => console.error('Error fetching message list:', error));
}

        // Poll every 5 seconds
        if (chatSessionId) setInterval(fetchChatMessages, 5000);
        setInterval(fetchMessagesList, 5000);

        // Initial fetch
        if (chatSessionId) fetchChatMessages();
        fetchMessagesList();

        // Handle form submission
        if (chatForm) {
            chatForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const formData = new FormData(chatForm);
                fetch('contact_messages.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        messageInput.value = '';
                        fetchChatMessages();
                    } else {
                        console.error('Error sending message:', data.error);
                        alert('Failed to send message: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    alert('Error sending message');
                });
            });

            messageInput.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    chatForm.dispatchEvent(new Event('submit'));
                }
            });
        }
    </script>
        <?php include 'loading.php'; ?>
</body>
</html>