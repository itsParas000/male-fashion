<style>
    /* Chatbot Icon */
    .chatbot-icon {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 60px;
        height: 60px;
        background: #e53637; /* Primary red from index.php */
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        z-index: 1000;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .chatbot-icon:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
    }
    .chatbot-icon img {
        width: 30px;
        height: 30px;
        filter: brightness(0) invert(1);
    }

    /* Chatbot Window */
    .chatbot-window {
        position: fixed;
        bottom: 100px;
        right: 30px;
        width: 360px;
        height: 480px;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        display: none;
        flex-direction: column;
        z-index: 1000;
        font-family: 'Nunito Sans', sans-serif; /* Match index.php font */
        overflow: hidden;
    }

    /* Chatbot Header */
    .chatbot-header {
        background: #111111; /* Dark header like index.php navbar */
        color: #ffffff;
        padding: 12px 20px;
        border-radius: 12px 12px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .chatbot-header .title {
        font-size: 18px;
        font-weight: 700;
        letter-spacing: 1px;
    }
    .chatbot-header .close-btn, .chatbot-header .clear-btn {
        background: rgba(255, 255, 255, 0.1);
        border: none;
        color: #ffffff;
        font-size: 14px;
        padding: 6px 12px;
        border-radius: 5px;
        cursor: pointer;
        transition: background 0.3s ease;
    }
    .chatbot-header .close-btn {
        font-size: 20px;
        width: 28px;
        height: 28px;
        line-height: 28px;
    }
    .chatbot-header .close-btn:hover, .chatbot-header .clear-btn:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    /* Chatbot Body */
    .chatbot-body {
        flex: 1;
        overflow-y: auto;
        padding: 15px;
        background: #f9f9f9; /* Light background similar to index.php sections */
        scrollbar-width: thin;
        scrollbar-color: #e53637 #f9f9f9;
    }
    .chatbot-body::-webkit-scrollbar {
        width: 6px;
    }
    .chatbot-body::-webkit-scrollbar-thumb {
        background: #e53637;
        border-radius: 10px;
    }

    /* Message Styles */
    .chat-message {
        margin: 12px 0;
        padding: 12px 18px;
        border-radius: 12px;
        max-width: 80%;
        position: relative;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s ease;
    }
    .chat-message:hover {
        transform: translateY(-2px);
    }
    .user-message {
        background: #6b48ff;
        color: #fff;
        width: 65%;
        padding: 10px;
        border-radius: 5px;
        margin: 10px 10px 10px auto; /* Adjusted margin to align right */
        align-self: flex-end;
    }
    .ai-message {
        background: rgb(241, 241, 241);
        color: #2d3748;
        align-self: flex-start;
        width: 65%;
        margin: 10px;
        padding: 10px;
        border-radius: 5px;
        border: 1px solid #e2e8f0;
    }
    .chat-message .time {
        font-size: 10px;
        color: #a0aec0;
        text-align: right;
        margin-top: 2px;
    }
    .chat-message .response-time {
        font-size: 9px;
        color: #a0aec0;
        text-align: right;
        margin-top: 1px;
    }
    .chat-message .delete-link {
        position: absolute;
        top: 2px;
        right: 5px;
        background: none;
        border: none;
        color: #ffffff; /* Explicitly white */
        font-size: 16px;
        cursor: pointer;
        display: none;
        line-height: 1;
        padding: 0;
        text-decoration: none;
    }
    .chat-message:hover .delete-link {
        display: block;
    }

    /* Chatbot Input */
    .chatbot-input {
        display: flex;
        padding: 12px;
        background: #ffffff;
        border-radius: 0 0 12px 12px;
        border-top: 1px solid #f1f1f1;
    }
    .chatbot-input input {
        flex: 1;
        padding: 10px 15px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        outline: none;
        font-size: 14px;
        font-family: 'Nunito Sans', sans-serif;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }
    .chatbot-input input:focus {
        border-color: #e53637;
        box-shadow: 0 0 0 2px rgba(229, 54, 55, 0.2);
    }
    .chatbot-input button {
        margin-left: 10px;
        padding: 10px 20px;
        background: #e53637;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        font-family: 'Nunito Sans', sans-serif;
        transition: background 0.3s ease, transform 0.2s ease;
    }
    .chatbot-input button:hover {
        background: #c9302c;
        transform: translateY(-1px);
    }
    .chatbot-input button:disabled {
        background: #d6d6d6;
        transform: none;
    }

    /* Typing Indicator */
    .typing-indicator {
        font-style: italic;
        color: #3d3d3d; /* Match index.php text color */
        padding: 10px;
        background: #f1f1f1;
        border-radius: 8px;
        margin: 10px 0;
    }

    /* Animation */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .chat-message {
        animation: fadeIn 0.4s ease-out;
    }

    /* Responsive Design */
    @media (max-width: 767px) {
        .chatbot-window {
            width: 90%;
            height: 80vh;
            bottom: 20px;
            right: 5%;
        }
        .chatbot-icon {
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
        }
        .chatbot-icon img {
            width: 25px;
            height: 25px;
        }
    }
</style>

<div class="chatbot-icon" id="chatbot-icon">
    <img src="https://cdn-icons-png.flaticon.com/512/4712/4712109.png" alt="Chatbot Icon">
</div>

<div class="chatbot-window" id="chatbot-window">
    <div class="chatbot-header">
        <span class="title">Fashion Assistant</span>
        <div>
            <button class="clear-btn" id="clear-btn">Clear</button>
            <button class="close-btn" id="close-btn">×</button>
        </div>
    </div>
    <div class="chatbot-body" id="chatbot-body"></div>
    <div class="chatbot-input">
        <input type="text" id="chatbot-input" placeholder="Ask about fashion, products, or more...">
        <button id="chatbot-send">Send</button>
    </div>
</div>

<script>
    const chatbotIcon = document.getElementById('chatbot-icon');
    const chatbotWindow = document.getElementById('chatbot-window');
    const chatBody = document.getElementById('chatbot-body');
    const chatInput = document.getElementById('chatbot-input');
    const sendBtn = document.getElementById('chatbot-send');
    const closeBtn = document.getElementById('close-btn');
    const clearBtn = document.getElementById('clear-btn');

    // Toggle chatbot window
    chatbotIcon.addEventListener('click', () => {
        chatbotWindow.style.display = 'flex';
        chatbotIcon.style.display = 'none';
        loadChatHistory();
    });
    closeBtn.addEventListener('click', () => {
        chatbotWindow.style.display = 'none';
        chatbotIcon.style.display = 'flex';
    });

    // Load chat history
    function loadChatHistory() {
        fetch('get_chat_history.php')
            .then(response => response.json())
            .then(data => {
                chatBody.innerHTML = '';
                data.messages.forEach(msg => {
                    const time = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    const responseTime = msg.response_time_ms ? `<div class="response-time">Response time: ${msg.response_time_ms}ms</div>` : '';
                    const deleteLink = msg.sender_type === 'user' ? `<a href="#" class="delete-link" data-id="${msg.id}">×</a>` : '';
                    chatBody.innerHTML += `
                        <div class="${msg.sender_type}-message">
                            ${msg.message.replace(/\n/g, '<br>')}
                            ${deleteLink}
                            <div class="time">${time}</div>
                            ${responseTime}
                        </div>`;
                });
                chatBody.scrollTop = chatBody.scrollHeight;
                addDeleteListeners();
            })
            .catch(error => console.error('Error loading chat history:', error));
    }

    // Add delete functionality
    function addDeleteListeners() {
        document.querySelectorAll('.delete-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent default link behavior
                const messageId = this.dataset.id;
                fetch('delete_message.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message_id: messageId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadChatHistory();
                    } else {
                        console.error('Deletion failed:', data.error);
                    }
                })
                .catch(error => console.error('Error deleting message:', error));
            });
        });
    }

    // Clear all chat
    clearBtn.addEventListener('click', () => {
        if (confirm('Are you sure you want to clear all chat history?')) {
            fetch('delete_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message_id: -1 }) // Send -1 to clear all
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    chatBody.innerHTML = '<div class="typing-indicator">Chat cleared!</div>';
                    setTimeout(loadChatHistory, 1000);
                } else {
                    console.error('Clear failed:', data.error);
                    chatBody.innerHTML = '<div class="typing-indicator">Failed to clear chat: ' + data.error + '</div>';
                }
            })
            .catch(error => {
                console.error('Error clearing chat:', error);
                chatBody.innerHTML = '<div class="typing-indicator">Failed to clear chat!</div>';
            });
        }
    });

    // Send message
    sendBtn.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') sendMessage(); });

    function sendMessage() {
        const message = chatInput.value.trim();
        if (!message) return;

        const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        chatBody.innerHTML += `
            <div class="user-message">
                ${message}
                <a href="#" class="delete-link" data-id="temp">×</a>
                <div class="time">${time}</div>
            </div>`;
        chatInput.value = '';
        chatBody.scrollTop = chatBody.scrollHeight;
        sendBtn.disabled = true;

        chatBody.innerHTML += `<div class="typing-indicator">AI is thinking...</div>`;
        chatBody.scrollTop = chatBody.scrollHeight;

        fetch('get_recommendation.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ prompt: message })
        })
        .then(response => response.json())
        .then(data => {
            chatBody.querySelector('.typing-indicator').remove();
            const aiTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            chatBody.innerHTML += `
                <div class="ai-message">
                    ${data.message.replace(/\n/g, '<br>')}
                    <div class="time">${aiTime}</div>
                    <div class="response-time">Response time: ${data.response_time_ms}ms</div>
                </div>`;
            chatBody.scrollTop = chatBody.scrollHeight;
            loadChatHistory();
        })
        .catch(error => {
            chatBody.querySelector('.typing-indicator').remove();
            chatBody.innerHTML += `<div class="ai-message">Oops, something went wrong!<div class="time">${time}</div></div>`;
            console.error(error);
        })
        .finally(() => sendBtn.disabled = false);
    }
</script>