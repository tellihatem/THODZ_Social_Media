<?php
session_start();
if (!(isset($_SESSION['IS_LOGGED']) && $_SESSION['IS_LOGGED'] == true && isset($_SESSION['uid']))){
    header("location: ./login.php");
    die();
}

require_once('./models/user.class.php');
require_once('./models/chat.class.php');
require_once('./controler/image.controler.php');

$user = new User();
$chat = new Chat();
$images = new Image();

$currentUserId = $_SESSION['uid'];
$currentUser = $user->getData($currentUserId);
$currentUserImg = $images->get_thumb_profile($currentUser['profileimg']);

// Get chat partner if specified
$chatWithId = isset($_GET['uid']) && is_numeric($_GET['uid']) ? intval($_GET['uid']) : null;
$chatWith = null;
$chatWithImg = null;
$messages = [];

if ($chatWithId) {
    $chatWith = $user->getData($chatWithId);
    if ($chatWith) {
        $chatWithImg = $images->get_thumb_profile($chatWith['profileimg']);
        $messages = $chat->getMessages($currentUserId, $chatWithId);
    }
}

// Get all users for chat list
$allUsers = $chat->getAllUsers($currentUserId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | THODZ</title>
    <link rel="stylesheet" href="Styles/app.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="./images/logo.png">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-left">
            <a href="./home.php">
                <img src="./images/logo.png" alt="THODZ" class="navbar-logo">
            </a>
            <div class="search-container">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" id="searchInput" placeholder="Search THODZ" autocomplete="off">
                <div class="search-results" id="searchResults"></div>
            </div>
        </div>

        <div class="navbar-center">
            <a href="./home.php" class="nav-link">
                <i class="fas fa-home"></i>
            </a>
            <a href="./following.php" class="nav-link">
                <i class="fas fa-user-friends"></i>
            </a>
            <a href="./chat.php" class="nav-link active">
                <i class="fas fa-comments"></i>
            </a>
        </div>

        <div class="navbar-right">
            <a href="./profile.php" class="nav-profile">
                <img src="<?php echo htmlspecialchars($currentUserImg); ?>" alt="Profile">
                <span><?php echo htmlspecialchars($currentUser['fname']); ?></span>
            </a>
        </div>
    </nav>

    <!-- Chat Container -->
    <div class="chat-container">
        <!-- Chat Sidebar -->
        <div class="chat-sidebar" id="chatSidebar">
            <div class="chat-sidebar-header">
                <h2>Chats</h2>
                <div class="chat-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="chatSearchInput" placeholder="Search messages">
                </div>
            </div>
            <div class="chat-list" id="chatList">
                <?php if (is_array($allUsers) && count($allUsers) > 0): ?>
                    <?php foreach ($allUsers as $chatUser): 
                        if ($chatUser['uid'] == $currentUserId) continue;
                        $chatUserImg = $images->get_thumb_profile($user->getData($chatUser['uid'])['profileimg']);
                        $lastMsg = $chat->getLastMessage($chatUser['uid'], $currentUserId);
                        $isActive = ($chatWithId == $chatUser['uid']);
                        $isOnline = ($chatUser['status'] == 'online');
                    ?>
                    <a href="./chat.php?uid=<?php echo $chatUser['uid']; ?>" 
                       class="chat-list-item <?php echo $isActive ? 'active' : ''; ?>">
                        <div class="chat-list-avatar">
                            <img src="<?php echo htmlspecialchars($chatUserImg); ?>" alt="Profile">
                            <?php if ($isOnline): ?>
                            <div class="online-dot"></div>
                            <?php endif; ?>
                        </div>
                        <div class="chat-list-info">
                            <h4><?php echo htmlspecialchars($chatUser['fname'] . ' ' . $chatUser['lname']); ?></h4>
                            <p><?php echo $lastMsg ? htmlspecialchars($lastMsg) : 'Start a conversation'; ?></p>
                        </div>
                    </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="chat-empty" style="padding: 40px;">
                        <i class="fas fa-users"></i>
                        <h3>No conversations</h3>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Chat Main Area -->
        <div class="chat-main">
            <?php if ($chatWith): ?>
            <!-- Chat Header -->
            <div class="chat-header">
                <button class="chat-back-btn" onclick="showChatList()" title="Back">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <a href="./profile.php?uid=<?php echo $chatWithId; ?>" class="chat-header-avatar">
                    <img src="<?php echo htmlspecialchars($chatWithImg); ?>" alt="Profile">
                </a>
                <div class="chat-header-info">
                    <h3><?php echo htmlspecialchars($chatWith['fname'] . ' ' . $chatWith['lname']); ?></h3>
                    <span><?php echo $chatWith['status'] == 'online' ? 'Active now' : 'Offline'; ?></span>
                </div>
                <div class="chat-header-actions">
                    <button class="chat-header-btn" title="Voice call">
                        <i class="fas fa-phone"></i>
                    </button>
                    <button class="chat-header-btn" title="Video call">
                        <i class="fas fa-video"></i>
                    </button>
                    <a href="./profile.php?uid=<?php echo $chatWithId; ?>" class="chat-header-btn" title="Info">
                        <i class="fas fa-info-circle"></i>
                    </a>
                </div>
            </div>

            <!-- Chat Messages -->
            <div class="chat-messages" id="chatMessages">
                <?php if (is_array($messages) && count($messages) > 0): ?>
                    <?php foreach ($messages as $msg): 
                        $isOutgoing = ($msg['outgoing_msg_id'] == $currentUserId);
                        $msgContent = htmlspecialchars($msg['msg']);
                        
                        // Detect links and make them clickable
                        $urlPattern = '/(https?:\/\/[^\s]+)/i';
                        $msgContent = preg_replace($urlPattern, '<a href="$1" target="_blank" rel="noopener noreferrer" style="color: inherit; text-decoration: underline;">$1</a>', $msgContent);
                    ?>
                    <div class="chat-message <?php echo $isOutgoing ? 'outgoing' : 'incoming'; ?>">
                        <?php if (!$isOutgoing): ?>
                        <div class="chat-message-avatar">
                            <img src="<?php echo htmlspecialchars($chatWithImg); ?>" alt="Profile">
                        </div>
                        <?php endif; ?>
                        <div class="chat-message-content">
                            <?php if (!empty($msg['image'])): ?>
                            <img src="./uploads/chat/<?php echo htmlspecialchars($msg['image']); ?>" 
                                 alt="Image" class="chat-message-image" onclick="openImage(this.src)">
                            <?php endif; ?>
                            <?php if (!empty($msg['audio'])): ?>
                            <audio controls class="chat-message-audio">
                                <source src="./uploads/chat/<?php echo htmlspecialchars($msg['audio']); ?>" type="audio/webm">
                            </audio>
                            <?php endif; ?>
                            <?php if (!empty($msg['msg'])): ?>
                            <div class="chat-message-bubble"><?php echo $msgContent; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                        <i class="fas fa-hand-wave" style="font-size: 48px; margin-bottom: 16px;"></i>
                        <p>Say hello to <?php echo htmlspecialchars($chatWith['fname']); ?>!</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Image Preview -->
            <div class="chat-preview" id="chatPreview">
                <div class="chat-preview-content">
                    <img src="" alt="Preview" id="chatPreviewImage">
                    <button class="chat-preview-remove" onclick="removePreview()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Recording UI -->
            <div class="chat-recording" id="chatRecording">
                <div class="recording-indicator"></div>
                <span class="recording-time" id="recordingTime">0:00</span>
                <span class="recording-cancel" onclick="cancelRecording()">Cancel</span>
                <button class="chat-send-btn" onclick="stopRecording()">
                    <i class="fas fa-stop"></i>
                </button>
            </div>

            <!-- Chat Input -->
            <div class="chat-input-area" id="chatInputArea">
                <div class="chat-input-wrapper">
                    <div class="chat-input-actions">
                        <label class="chat-input-btn" for="chatImageInput" title="Send image">
                            <i class="fas fa-image"></i>
                        </label>
                        <input type="file" id="chatImageInput" accept="image/*" style="display: none;">
                        <button class="chat-input-btn" id="recordBtn" title="Voice message">
                            <i class="fas fa-microphone"></i>
                        </button>
                    </div>
                    <div class="chat-input-container">
                        <textarea class="chat-input" id="chatInput" placeholder="Aa" rows="1"></textarea>
                    </div>
                    <button class="chat-send-btn" id="sendBtn" disabled>
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
            <?php else: ?>
            <!-- Empty State -->
            <div class="chat-empty">
                <i class="fas fa-comments"></i>
                <h3>Select a conversation</h3>
                <p style="color: var(--text-muted);">Choose from your existing conversations or start a new one.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Image Viewer Modal -->
    <div class="modal-overlay" id="imageViewerModal" onclick="closeImageViewer()">
        <img src="" alt="Full Image" id="fullImage" style="max-width: 90%; max-height: 90%; object-fit: contain;">
    </div>

    <script src="./Js/jquery.min.js"></script>
    <script src="./Js/app.js?v=<?php echo time(); ?>"></script>
    <script>
        const chatWithId = <?php echo $chatWithId ?: 'null'; ?>;
        const currentUserId = <?php echo $currentUserId; ?>;
        let mediaRecorder = null;
        let audioChunks = [];
        let recordingInterval = null;
        let recordingSeconds = 0;
        let selectedImage = null;

        // Mobile navigation
        function showChatList() {
            document.getElementById('chatSidebar').classList.remove('hidden');
        }

        function hideChatList() {
            document.getElementById('chatSidebar').classList.add('hidden');
        }

        // On mobile, hide sidebar when a chat is selected
        if (chatWithId && window.innerWidth <= 900) {
            hideChatList();
        }

        // Handle resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 900) {
                document.getElementById('chatSidebar').classList.remove('hidden');
            } else if (chatWithId) {
                hideChatList();
            }
        });

        // Scroll to bottom of messages
        function scrollToBottom() {
            const messages = document.getElementById('chatMessages');
            if (messages) {
                messages.scrollTop = messages.scrollHeight;
            }
        }
        scrollToBottom();

        // Auto-resize textarea
        const chatInput = document.getElementById('chatInput');
        const sendBtn = document.getElementById('sendBtn');
        
        if (chatInput) {
            chatInput.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 100) + 'px';
                sendBtn.disabled = !this.value.trim() && !selectedImage;
            });

            // Send on Enter (Shift+Enter for new line)
            chatInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    if (!sendBtn.disabled) {
                        sendMessage();
                    }
                }
            });
        }

        // Send message
        async function sendMessage() {
            const message = chatInput?.value.trim();
            if (!message && !selectedImage) return;
            if (!chatWithId) return;

            const formData = new FormData();
            formData.append('user_id', chatWithId);
            formData.append('message', message);
            
            if (selectedImage) {
                formData.append('image', selectedImage);
            }

            try {
                sendBtn.disabled = true;
                
                const response = await fetch('/api/ajax.php?action=insertmessage', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Add message to UI
                    addMessageToUI(message, true, selectedImage);
                    chatInput.value = '';
                    chatInput.style.height = 'auto';
                    
                    // Update sidebar last message
                    updateSidebarLastMessage(message, selectedImage ? true : false, false);
                    
                    removePreview();
                    
                    // Update lastMessageId to prevent duplicate from polling
                    if (data.message_id) {
                        lastMessageId = Math.max(lastMessageId, data.message_id);
                    }
                } else if (data.error) {
                    // Show error to user
                    alert(data.message || 'Failed to send message');
                }
            } catch (error) {
                console.error('Send message error:', error);
                alert('Failed to send message. Please try again.');
            } finally {
                sendBtn.disabled = !chatInput?.value.trim();
            }
        }

        function addMessageToUI(text, isOutgoing, imageFile = null) {
            const messagesContainer = document.getElementById('chatMessages');
            if (!messagesContainer) return;

            const msgDiv = document.createElement('div');
            msgDiv.className = `chat-message ${isOutgoing ? 'outgoing' : 'incoming'}`;
            
            let content = '<div class="chat-message-content">';
            
            if (imageFile) {
                const imgUrl = URL.createObjectURL(imageFile);
                content += `<img src="${imgUrl}" alt="Image" class="chat-message-image" onclick="openImage(this.src)">`;
            }
            
            if (text) {
                // Convert URLs to links
                const urlPattern = /(https?:\/\/[^\s]+)/gi;
                const linkedText = text.replace(urlPattern, '<a href="$1" target="_blank" rel="noopener noreferrer" style="color: inherit; text-decoration: underline;">$1</a>');
                content += `<div class="chat-message-bubble">${escapeHtml(text).replace(urlPattern, '<a href="$1" target="_blank" rel="noopener noreferrer" style="color: inherit; text-decoration: underline;">$1</a>')}</div>`;
            }
            
            content += '</div>';
            msgDiv.innerHTML = content;
            messagesContainer.appendChild(msgDiv);
            scrollToBottom();
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Send button click
        sendBtn?.addEventListener('click', sendMessage);

        // Image upload
        const chatImageInput = document.getElementById('chatImageInput');
        chatImageInput?.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                selectedImage = this.files[0];
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('chatPreviewImage').src = event.target.result;
                    document.getElementById('chatPreview').classList.add('active');
                    sendBtn.disabled = false;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });

        function removePreview() {
            selectedImage = null;
            document.getElementById('chatPreview')?.classList.remove('active');
            document.getElementById('chatPreviewImage').src = '';
            document.getElementById('chatImageInput').value = '';
            sendBtn.disabled = !chatInput?.value.trim();
        }

        // Voice recording
        const recordBtn = document.getElementById('recordBtn');
        recordBtn?.addEventListener('click', startRecording);

        async function startRecording() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                mediaRecorder = new MediaRecorder(stream);
                audioChunks = [];
                
                mediaRecorder.ondataavailable = (e) => {
                    audioChunks.push(e.data);
                };
                
                mediaRecorder.onstop = async () => {
                    const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                    await sendAudioMessage(audioBlob);
                    stream.getTracks().forEach(track => track.stop());
                };
                
                mediaRecorder.start();
                recordingSeconds = 0;
                updateRecordingTime();
                recordingInterval = setInterval(updateRecordingTime, 1000);
                
                document.getElementById('chatInputArea').style.display = 'none';
                document.getElementById('chatRecording').classList.add('active');
            } catch (error) {
                console.error('Recording error:', error);
                alert('Could not access microphone');
            }
        }

        let isRecordingCancelled = false;

        function updateRecordingTime() {
            recordingSeconds++;
            const mins = Math.floor(recordingSeconds / 60);
            const secs = recordingSeconds % 60;
            document.getElementById('recordingTime').textContent = `${mins}:${secs.toString().padStart(2, '0')}`;
        }

        function stopRecording() {
            isRecordingCancelled = false;
            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                mediaRecorder.stop();
            }
            clearInterval(recordingInterval);
            document.getElementById('chatRecording').classList.remove('active');
            document.getElementById('chatInputArea').style.display = 'block';
        }

        function cancelRecording() {
            isRecordingCancelled = true;
            audioChunks = []; // Clear chunks first
            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                mediaRecorder.stop();
            }
            clearInterval(recordingInterval);
            document.getElementById('chatRecording').classList.remove('active');
            document.getElementById('chatInputArea').style.display = 'block';
        }

        async function sendAudioMessage(audioBlob) {
            if (isRecordingCancelled || audioChunks.length === 0) return; // Cancelled
            
            const formData = new FormData();
            formData.append('user_id', chatWithId);
            formData.append('audio', audioBlob, 'voice.webm');
            formData.append('message', '');

            try {
                const response = await fetch('/api/ajax.php?action=insertmessage', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                if (data.success) {
                    // Add audio message to UI without reload
                    const messagesContainer = document.getElementById('chatMessages');
                    if (messagesContainer) {
                        const msgDiv = document.createElement('div');
                        msgDiv.className = 'chat-message outgoing';
                        msgDiv.innerHTML = `<div class="chat-message-content">
                            <audio controls class="chat-message-audio">
                                <source src="${URL.createObjectURL(audioBlob)}" type="audio/webm">
                            </audio>
                        </div>`;
                        messagesContainer.appendChild(msgDiv);
                        scrollToBottom();
                    }
                    
                    // Update lastMessageId
                    if (data.message_id) {
                        lastMessageId = Math.max(lastMessageId, data.message_id);
                    }
                    
                    // Update sidebar
                    updateSidebarLastMessage('', false, true);
                } else if (data.error) {
                    alert(data.message || 'Failed to send voice message');
                }
            } catch (error) {
                console.error('Send audio error:', error);
                alert('Failed to send voice message. Please try again.');
            }
        }

        // Image viewer
        function openImage(src) {
            document.getElementById('fullImage').src = src;
            document.getElementById('imageViewerModal').classList.add('active');
        }

        function closeImageViewer() {
            document.getElementById('imageViewerModal').classList.remove('active');
        }

        // Track last message ID for polling
        let lastMessageId = <?php 
            if (is_array($messages) && count($messages) > 0) {
                echo end($messages)['mid'];
            } else {
                echo '0';
            }
        ?>;

        // Keep current user online & poll for status updates
        async function updateOnlineStatus() {
            try {
                const response = await fetch('/api/ajax.php?action=getonlinestatus', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'heartbeat=1'
                });
                
                const data = await response.json();
                
                if (data.success && data.users) {
                    // Update online dots in sidebar
                    document.querySelectorAll('.chat-list-item').forEach(item => {
                        const href = item.getAttribute('href');
                        const match = href.match(/uid=(\d+)/);
                        if (match) {
                            const uid = match[1];
                            const dot = item.querySelector('.online-dot');
                            const isOnline = data.users[uid] === 'online';
                            
                            if (isOnline && !dot) {
                                // Add online dot
                                const avatar = item.querySelector('.chat-list-avatar');
                                if (avatar) {
                                    const newDot = document.createElement('div');
                                    newDot.className = 'online-dot';
                                    avatar.appendChild(newDot);
                                }
                            } else if (!isOnline && dot) {
                                // Remove online dot
                                dot.remove();
                            }
                        }
                    });
                    
                    // Update chat header online status
                    if (chatWithId && data.users[chatWithId]) {
                        const headerStatus = document.querySelector('.chat-header-status');
                        if (headerStatus) {
                            headerStatus.textContent = data.users[chatWithId] === 'online' ? 'Active now' : 'Offline';
                        }
                    }
                }
            } catch (error) {
                // Silent fail
            }
        }

        // Update online status every 10 seconds
        updateOnlineStatus();
        setInterval(updateOnlineStatus, 10000);

        // Real-time message polling
        if (chatWithId) {
            // Mark messages as read when opening chat
            fetch('/api/ajax.php?action=markasread', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `chat_with=${chatWithId}`
            });

            // Poll for new messages every 3 seconds
            setInterval(async () => {
                try {
                    const response = await fetch('/api/ajax.php?action=getnewmessages', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `chat_with=${chatWithId}&last_message_id=${lastMessageId}`
                    });
                    
                    const data = await response.json();
                    
                    if (data.success && data.messages && data.messages.length > 0) {
                        data.messages.forEach(msg => {
                            // Only add if not already displayed
                            if (msg.mid > lastMessageId) {
                                const isOutgoing = msg.outgoing_msg_id == currentUserId;
                                addMessageToUIFromServer(msg, isOutgoing);
                                lastMessageId = msg.mid;
                            }
                        });
                        
                        // Mark as read
                        fetch('/api/ajax.php?action=markasread', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `chat_with=${chatWithId}`
                        });
                    }
                } catch (error) {
                    // Silent fail - will retry on next interval
                }
            }, 3000);
        }

        // Add message from server response (with proper escaping)
        function addMessageToUIFromServer(msg, isOutgoing) {
            const messagesContainer = document.getElementById('chatMessages');
            if (!messagesContainer) return;

            const msgDiv = document.createElement('div');
            msgDiv.className = `chat-message ${isOutgoing ? 'outgoing' : 'incoming'}`;
            
            let content = '';
            
            if (!isOutgoing) {
                content += `<div class="chat-message-avatar">
                    <img src="<?php echo htmlspecialchars($chatWithImg ?? ''); ?>" alt="Profile">
                </div>`;
            }
            
            content += '<div class="chat-message-content">';
            
            if (msg.image) {
                content += `<img src="./uploads/chat/${escapeHtml(msg.image)}" alt="Image" class="chat-message-image" onclick="openImage(this.src)">`;
            }
            
            if (msg.audio) {
                content += `<audio controls class="chat-message-audio">
                    <source src="./uploads/chat/${escapeHtml(msg.audio)}" type="audio/webm">
                </audio>`;
            }
            
            if (msg.msg) {
                // Convert URLs to links
                let text = escapeHtml(msg.msg);
                const urlPattern = /(https?:\/\/[^\s]+)/gi;
                text = text.replace(urlPattern, '<a href="$1" target="_blank" rel="noopener noreferrer" style="color: inherit; text-decoration: underline;">$1</a>');
                content += `<div class="chat-message-bubble">${text}</div>`;
            }
            
            content += '</div>';
            msgDiv.innerHTML = content;
            messagesContainer.appendChild(msgDiv);
            scrollToBottom();
        }

        // Update sidebar last message preview
        function updateSidebarLastMessage(text, isImage = false, isAudio = false) {
            // Find the active chat item in sidebar
            const activeItem = document.querySelector(`.chat-list-item[href="./chat.php?uid=${chatWithId}"]`);
            if (activeItem) {
                const msgPreview = activeItem.querySelector('.chat-list-info p');
                if (msgPreview) {
                    let preview = 'You: ';
                    if (isAudio) {
                        preview += '🎤 Voice message';
                    } else if (isImage && !text) {
                        preview += '📷 Photo';
                    } else if (text) {
                        preview += text.length > 23 ? text.substring(0, 23) + '...' : text;
                    }
                    msgPreview.textContent = preview;
                }
            }
        }

        // Chat search
        const chatSearchInput = document.getElementById('chatSearchInput');
        chatSearchInput?.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            document.querySelectorAll('.chat-list-item').forEach(item => {
                const name = item.querySelector('h4')?.textContent.toLowerCase() || '';
                item.style.display = name.includes(query) ? 'flex' : 'none';
            });
        });
    </script>
</body>
</html>
