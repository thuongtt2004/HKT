<?php
session_start();
require_once 'config/connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login_page.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Lấy thông tin user
$user_query = "SELECT username, full_name FROM users WHERE user_id = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_info = $user_stmt->get_result()->fetch_assoc();

// Lấy tin nhắn giữa user và admin
$messages_query = "SELECT m.*, 
                   CASE 
                       WHEN m.sender_type = 'admin' THEN a.full_name
                       ELSE u.full_name
                   END as sender_name
                   FROM messages m
                   LEFT JOIN administrators a ON m.sender_id = a.admin_id AND m.sender_type = 'admin'
                   LEFT JOIN users u ON m.sender_id = u.user_id AND m.sender_type = 'user'
                   WHERE (m.sender_id = ? AND m.sender_type = 'user') 
                      OR (m.receiver_id = ? AND m.sender_type = 'admin')
                   ORDER BY m.created_at ASC";
$msg_stmt = $conn->prepare($messages_query);
$msg_stmt->bind_param("ii", $user_id, $user_id);
$msg_stmt->execute();
$messages = $msg_stmt->get_result();

// Đánh dấu tin nhắn từ admin đã đọc
$mark_read = "UPDATE messages SET is_read = 1 WHERE receiver_id = ? AND sender_type = 'admin' AND is_read = 0";
$read_stmt = $conn->prepare($mark_read);
$read_stmt->bind_param("i", $user_id);
$read_stmt->execute();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat với Admin - HKT Store</title>
    <link rel="stylesheet" href="css/chat.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/chat.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="chat-container">
        <div class="chat-header">
            <div class="chat-info">
                <i class="fas fa-headset"></i>
                <div>
                    <h3>Hỗ trợ khách hàng</h3>
                    <p>Chúng tôi luôn sẵn sàng hỗ trợ bạn</p>
                </div>
            </div>
        </div>

        <div class="chat-messages" id="chatMessages">
            <?php if ($messages->num_rows > 0): ?>
                <?php while ($msg = $messages->fetch_assoc()): ?>
                    <div class="message <?php echo $msg['sender_type'] == 'user' ? 'message-user' : 'message-admin'; ?>">
                        <div class="message-avatar">
                            <?php if ($msg['sender_type'] == 'admin'): ?>
                                <i class="fas fa-user-shield"></i>
                            <?php else: ?>
                                <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </div>
                        <div class="message-content">
                            <div class="message-header">
                                <span class="message-sender"><?php echo htmlspecialchars($msg['sender_name']); ?></span>
                                <span class="message-time"><?php echo date('H:i d/m/Y', strtotime($msg['created_at'])); ?></span>
                            </div>
                            <div class="message-text"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-chat">
                    <i class="fas fa-comments"></i>
                    <p>Chưa có tin nhắn nào. Hãy gửi tin nhắn đầu tiên!</p>
                </div>
            <?php endif; ?>
        </div>

        <form class="chat-input-form" id="chatForm">
            <textarea 
                id="messageInput" 
                placeholder="Nhập tin nhắn của bạn..."
                rows="3"
                required
            ></textarea>
            <button type="submit" class="btn-send">
                <i class="fas fa-paper-plane"></i> Gửi
            </button>
        </form>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        const userId = <?php echo $user_id; ?>;
        const chatMessages = document.getElementById('chatMessages');
        const chatForm = document.getElementById('chatForm');
        const messageInput = document.getElementById('messageInput');

        // Auto scroll to bottom
        function scrollToBottom() {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        scrollToBottom();

        // Gửi tin nhắn
        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const message = messageInput.value.trim();
            if (!message) return;

            try {
                const formData = new FormData();
                formData.append('message', message);
                formData.append('sender_type', 'user');

                const response = await fetch('send_message.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                
                if (result.success) {
                    messageInput.value = '';
                    loadMessages();
                } else {
                    alert('Lỗi: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi gửi tin nhắn');
            }
        });

        // Load tin nhắn mới
        async function loadMessages() {
            try {
                const response = await fetch('load_messages.php');
                const result = await response.json();
                
                if (result.success && result.messages) {
                    updateMessages(result.messages);
                    scrollToBottom();
                }
            } catch (error) {
                console.error('Error loading messages:', error);
            }
        }

        // Cập nhật tin nhắn
        function updateMessages(messages) {
            if (messages.length === 0) {
                chatMessages.innerHTML = `
                    <div class="empty-chat">
                        <i class="fas fa-comments"></i>
                        <p>Chưa có tin nhắn nào. Hãy gửi tin nhắn đầu tiên!</p>
                    </div>
                `;
                return;
            }

            chatMessages.innerHTML = messages.map(msg => `
                <div class="message ${msg.sender_type == 'user' ? 'message-user' : 'message-admin'}">
                    <div class="message-avatar">
                        ${msg.sender_type == 'admin' ? '<i class="fas fa-user-shield"></i>' : '<i class="fas fa-user"></i>'}
                    </div>
                    <div class="message-content">
                        <div class="message-header">
                            <span class="message-sender">${msg.sender_name}</span>
                            <span class="message-time">${msg.time}</span>
                        </div>
                        <div class="message-text">${msg.message.replace(/\n/g, '<br>')}</div>
                    </div>
                </div>
            `).join('');
        }

        // Auto reload messages every 3 seconds
        setInterval(loadMessages, 3000);

        // Enter để gửi, Shift+Enter để xuống dòng
        messageInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                chatForm.dispatchEvent(new Event('submit'));
            }
        });
    </script>
</body>
</html>
