<?php
session_name('SESSION_USER');
session_start();
include("php/config.php");

if (!isset($_SESSION['valid'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$chat_session_id = $_SESSION['valid']; // User's email from login

// Handle chat message submission via AJAX or form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['chat_message'])) {
    $message = mysqli_real_escape_string($con, $_POST['chat_message']);
    $query = "INSERT INTO chat_messages (chat_session_id, user_id, sender_type, message) 
              VALUES ('$chat_session_id', '$user_id', 'user', '$message')";
    if (mysqli_query($con, $query)) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            echo json_encode(['success' => true]);
            exit();
        } else {
            header("Location: contact us.php");
            exit();
        }
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($con)]);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <!-- Header-specific styles (from header.php) -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" integrity="sha256-eZrrJcwDc/3uDhsdt61sL2oOBY362qM3lon1gyExkL0=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/elegant-icons@0.1.0/style.css" integrity="sha256-IcKRxYb84chZGPZEVYH38X5kPinewF6z3kQ2wUHw1/E=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/magnific-popup@1.1.0/dist/magnific-popup.css" integrity="sha256-WkKDNlHVfGSEe4e4bHe+HGkT3NRO8PhN9pTIKjQhD/s=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nice-select@1.1.0/css/nice-select.css" integrity="sha256-mLBIhmBvigTFWPSCtvdu6a76T+3Xyt+K571hupeFLg4=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/assets/owl.carousel.min.css" integrity="sha256-UhQQ4fxEeABh4JrcmAJ1+16id/1dnlOEVCFOxDef9Lw=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery.slicknav@1.0.8/dist/slicknav.min.css" integrity="sha256-zG7SvGJYL+DL4TmoRISLyQVSVxV6+WR4GjPQ+SwQSmc=" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css" type="text/css">
    <style>
        body { 
            font-family: 'Nunito Sans', sans-serif; 
            color: #111111;
        }
        .contact.spad {
            padding-top: 80px;
            padding-bottom: 80px;
        }
        .section-title {
            margin-bottom: 45px;
        }
        .section-title span {
            font-size: 16px;
            color: #e53637;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 700;
        }
        .section-title h2 {
            font-size: 36px;
            font-weight: 700;
            margin-top: 14px;
            line-height: 46px;
        }
        .contact__text {
            margin-bottom: 30px;
        }
        .contact__text ul li {
            list-style: none;
            margin-bottom: 20px;
        }
        .contact__text ul li h4 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .contact__text ul li p {
            margin-bottom: 0;
            font-size: 15px;
            line-height: 26px;
            color: #3d3d3d;
        }
        .chat-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .chat-header {
            background: #111111;
            color: white;
            padding: 15px 20px;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            text-align: center;
        }
        .chat-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .chat-body {
            max-height: 400px;
            padding: 20px;
            overflow-y: auto;
            background: #f8f8f8;
            flex-grow: 1;
        }
        .chat-message {
            margin: 12px 0;
            padding: 12px 15px;
            border-radius: 8px;
            max-width: 75%;
            font-size: 15px;
            line-height: 1.5;
            position: relative;
            font-weight: 500;
        }
        .chat-message.user {
            background: #e53637;
            color: white;
            margin-left: auto;
            border-bottom-right-radius: 2px;
        }
        .chat-message.admin {
            background: #e9ecef;
            color: #111111;
            margin-right: auto;
            border-bottom-left-radius: 2px;
        }
        .chat-message p.message-text {
            margin: 0;
            word-break: break-word;
        }
        .chat-message .timestamp {
            font-size: 13px;
            color: #333;
            margin-top: 6px;
            text-align: right;
            font-weight: 600;
        }
        .chat-footer {
            padding: 15px 20px;
            border-top: 1px solid #eee;
            background: white;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        #chat-form {
            display: flex;
            flex: 1;
            gap: 10px;
        }
        #chat-message {
            flex: 1;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            outline: none;
            font-family: 'Nunito Sans', sans-serif;
            transition: border-color 0.3s;
        }
        #chat-message:focus {
            border-color: #e53637;
        }
        .chat-footer button[type="submit"] {
            padding: 0;
            width: 45px;
            height: 45px;
            background: #e53637;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s;
        }
        .chat-footer button[type="submit"]:hover {
            background: #ca2e30;
        }
        .chat-footer button[type="submit"] i {
            font-size: 18px;
        }
        #clear-chat {
            padding: 0;
            width: 45px;
            height: 45px;
            background: #6f6f6f;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s;
        }
        #clear-chat:hover {
            background: #5a5a5a;
        }
        #clear-chat i {
            font-size: 18px;
        }
        @media (max-width: 768px) {
            .chat-container {
                margin-top: 20px;
            }
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">
<?php include 'header.php'; ?>

    <!-- Map Begin -->
    <div class="map">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d111551.9926412813!2d-90.27317134641879!3d38.606612219170856!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x54eab584e432360b%3A0x1c3bb99243deb742!2sUnited%20States!5e0!3m2!1sen!2sbd!4v1597926938024!5m2!1sen!2sbd" height="500" style="border:0;" allowfullscreen="" aria-hidden="false" tabindex="0"></iframe>
    </div>
    <!-- Map End -->

    <!-- Contact Section Begin -->
    <section class="contact spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-6">
                    <div class="contact__text">
                        <div class="section-title">
                            <span>Information</span>
                            <h2>Contact Us</h2>
                            <p>As you might expect of a company that began as a high-end interiors contractor, we pay strict attention.</p>
                        </div>
                        <ul>
                        <li>
                                <h4>Navi mumbai</h4>
                                <p>sanpada sec.9, Navi mumbai,400705 <br />+1234567890</p>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6">
                    <div class="chat-container">
                        <div class="chat-header">
                            <h3>Chat with Us</h3>
                        </div>
                        <div class="chat-body" id="chat-body"></div>
                        <div class="chat-footer">
                            <form id="chat-form">
                                <input type="text" name="chat_message" id="chat-message" placeholder="Type a message..." required>
                                <button type="submit">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </form>
                            <button id="clear-chat"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Contact Section End -->

    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="footer__about">
                        <div class="footer__logo">
                            <a href="./index.php"><img src="img/footer-logo.png" alt="Male Fashion"></a>
                        </div>
                        <p>The customer is at the heart of our unique business model.</p>
                    </div>
                </div>
                <div class="col-lg-2 offset-lg-1 col-md-3 col-sm-6">
                    <div class="footer__widget">
                        <h6>Shopping</h6>
                        <ul>
                            <li><a href="#">Clothing Store</a></li>
                            <li><a href="#">Trending Shoes</a></li>
                            <li><a href="#">Accessories</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <div class="footer__widget">
                        <h6>Quick Links</h6>
                        <ul>
                            <li><a href="Contact Us.php">Contact Us</a></li>
                            <li><a href="tracking.php">Tracking Orders</a></li>
                            <li><a href="terms&conditions.php">Terms & Conditions</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="footer__copyright__text">
                        <p>Copyright © <?php echo date('Y'); ?> All rights reserved | Outfits</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script>
    const chatBody = document.getElementById('chat-body');
    const chatForm = document.getElementById('chat-form');
    const chatMessageInput = document.getElementById('chat-message');
    const chatSessionId = '<?php echo $chat_session_id; ?>';

    // Format timestamp to readable date and time
    function formatTimestamp(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    }

    // Fetch chat messages dynamically
    function fetchChatMessages() {
        fetch('fetch_messages.php', { 
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'chat_session_id=' + encodeURIComponent(chatSessionId)
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(messages => {
            chatBody.innerHTML = '';
            messages.forEach(msg => {
                const div = document.createElement('div');
                div.className = `chat-message ${msg.sender_type}`;
                div.innerHTML = `<p class="message-text">${msg.message}</p><p class="timestamp">${formatTimestamp(msg.created_at)}</p>`;
                chatBody.appendChild(div);
            });
            chatBody.scrollTop = chatBody.scrollHeight;
        })
        .catch(error => console.error('Error fetching messages:', error));
    }

    // Poll every 5 seconds
    setInterval(fetchChatMessages, 5000);
    fetchChatMessages(); // Initial fetch

    // Handle form submission
    if (chatForm) {
        chatForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(chatForm);
            fetch('contact us.php', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    chatMessageInput.value = '';
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

        chatMessageInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                chatForm.dispatchEvent(new Event('submit'));
            }
        });
    }

    // Clear chat
    document.getElementById('clear-chat').addEventListener('click', () => {
        fetch('clear_chat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'chat_session_id=' + encodeURIComponent(chatSessionId)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                chatBody.innerHTML = ''; // Clear chat visually
            } else {
                alert('Failed to clear chat');
            }
        })
        .catch(error => console.error('Error clearing chat:', error));
    });
    </script>

    <!-- Header-specific scripts (from header.php) -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.3.1/dist/jquery.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.nice-select@1.1.0/js/jquery.nice-select.min.js" integrity="sha256-Zr3vByTlMGQhvMfgkQ5BtWRSKBGa2QlspKYJnkjZTmo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.nicescroll@3.7.6/jquery.nicescroll.min.js" integrity="sha256-eyLxo2W5hVgeJ88zEqUsSGHzTuGUvJWUeuTmXg0Ne5I=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/magnific-popup@1.1.0/dist/jquery.magnific-popup.min.js" integrity="sha256-P93G0oq6PBPWTP1I0Y8+7DfWdN/pN3d7X691XTDrmzs=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.countdown@2.2.0/dist/jquery.countdown.min.js" integrity="sha256-IiR9fU+DX0zOH/0ZY4rJrT5hDDEkTDowSc5z5V5lwHc=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.slicknav@1.1.0/js/jquery.slicknav.min.js" integrity="sha256-mT2vKBwjvmz2zM5RPX+4AjrM4Xnsyku/U57i5o/TMPo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/mixitup@3.3.1/dist/mixitup.min.js" integrity="sha256-q4HLmF7zMEP2nKPVszwVUWZaTShZFr8jX691XTDrmzs=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/owl.carousel.min.js" integrity="sha256-pTxD+DSzIwmwhOqTFN+DB+nHjO4iAsbgfyFq5K5bcE0=" crossorigin="anonymous"></script>
    <script src="main1.js"></script>
</body>
</html>