<?php 
/**
 * @var string $title
 * @var \App\Models\Employer $employer
 * @var array $conversations
 * @var int $unreadCount
 */
?>

<style>
    /* Custom Scrollbar Styles */
    #messages-container::-webkit-scrollbar,
    #conversations-list::-webkit-scrollbar {
        width: 8px;
    }
    #messages-container::-webkit-scrollbar-track,
    #conversations-list::-webkit-scrollbar-track {
        background: #f7fafc;
        border-radius: 4px;
    }
    #messages-container::-webkit-scrollbar-thumb,
    #conversations-list::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 4px;
    }
    #messages-container::-webkit-scrollbar-thumb:hover,
    #conversations-list::-webkit-scrollbar-thumb:hover {
        background: #a0aec0;
    }
    /* Firefox */
    #messages-container,
    #conversations-list {
        scrollbar-width: thin;
        scrollbar-color: #cbd5e0 #f7fafc;
    }
</style>

<div class="h-[calc(100vh-200px)] flex flex-col bg-white rounded-lg shadow-md overflow-hidden" style="min-height: 600px;">
    <!-- Header -->
    <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Messages</h1>
            <p class="text-sm text-gray-500 mt-1">Online status: <span class="text-green-600 font-medium">On</span></p>
        </div>
        <div class="flex items-center space-x-4">
            <button onclick="refreshMessages()" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">
                <svg class="h-5 w-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </button>
        </div>
    </div>

    <div class="flex flex-1 overflow-hidden">
        <!-- Conversations List (Left Sidebar) -->
        <div class="w-1/3 border-r border-gray-200 flex flex-col overflow-hidden">
            <!-- Tabs -->
            <div class="border-b border-gray-200 px-4">
                <div class="flex space-x-4">
                    <button onclick="setActiveTab('inbox')" 
                            id="tab-inbox"
                            class="py-3 px-2 border-b-2 border-indigo-600 text-indigo-600 font-medium text-sm">
                        Inbox (<span id="inbox-count"><?= count($conversations) ?></span>)
                    </button>
                    <button onclick="setActiveTab('unread')" 
                            id="tab-unread"
                            class="py-3 px-2 border-b-2 border-transparent text-gray-600 hover:text-gray-900 font-medium text-sm">
                        Unread (<span id="unread-count"><?= $unreadCount ?></span>)
                    </button>
                </div>
            </div>

            <!-- Filter -->
            <div class="px-4 py-3 border-b border-gray-200">
                <select id="job-filter" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-[#eef2ff]">
                    <option value="all">Filter by job: All jobs</option>
                    <!-- Jobs will be loaded dynamically -->
                </select>
            </div>

            <!-- Conversations List -->
            <div id="conversations-list" class="flex-1 overflow-y-scroll" style="overflow-y: scroll !important;">
                <?php if (empty($conversations)): ?>
                    <div class="p-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No conversations</h3>
                        <p class="mt-1 text-sm text-gray-500">Start a conversation with a candidate.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($conversations as $conv): ?>
                        <div onclick="loadConversation(<?= $conv['id'] ?>)" 
                             class="conversation-item px-4 py-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer <?= $conv['unread_count'] > 0 ? 'bg-[#eef2ff]' : '' ?>"
                             data-conversation-id="<?= $conv['id'] ?>">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-2">
                                        <h3 class="text-sm font-medium text-gray-900 truncate">
                                            <?= htmlspecialchars($conv['candidate']['name'] ?? $conv['candidate']['email'] ?? 'Unknown') ?>
                                        </h3>
                                        <?php if ($conv['unread_count'] > 0): ?>
                                            <span class="flex-shrink-0 bg-[#eef2ff] text-gray-900 text-xs font-medium px-2 py-0.5 rounded-full unread-badge">
                                                <?= $conv['unread_count'] ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($conv['job']): ?>
                                        <p class="text-xs text-gray-500 mt-1 truncate">
                                            Applied to <?= htmlspecialchars($conv['job']['title']) ?>
                                        </p>
                                    <?php endif; ?>
                                    <?php if ($conv['last_message']): ?>
                                        <p class="text-sm text-gray-600 mt-1 truncate">
                                            <?= htmlspecialchars(substr($conv['last_message']['body'], 0, 60)) ?>
                                            <?= strlen($conv['last_message']['body']) > 60 ? '...' : '' ?>
                                        </p>
                                        <p class="text-xs text-gray-400 mt-1">
                                            <?= date('M d', strtotime($conv['last_message']['created_at'])) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Chat Area (Right Side) -->
        <div id="chat-area" class="flex-1 flex flex-col <?= empty($conversations) ? 'hidden' : '' ?>">
            <div id="no-conversation-selected" class="flex-1 flex items-center justify-center bg-gray-50">
                <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Select a conversation</h3>
                    <p class="mt-1 text-sm text-gray-500">Choose a conversation from the list to start messaging.</p>
                </div>
            </div>

            <!-- Active Conversation -->
            <div id="active-conversation" class="hidden flex-1 flex flex-col" style="min-height: 0;">
                <!-- Conversation Header -->
                <div id="conversation-header" class="flex-shrink-0 border-b border-gray-200 px-6 py-4 bg-white">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div>
                                <h2 id="candidate-name" class="text-lg font-semibold text-gray-900"></h2>
                                <p id="job-title" class="text-sm text-gray-500"></p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="scheduleInterview()" class="p-2 text-gray-400 hover:text-gray-600">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </button>
                            <button class="p-2 text-gray-400 hover:text-gray-600">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Messages Container -->
                <div id="messages-container" class="flex-1 overflow-y-scroll px-4 py-4 space-y-2 bg-gray-50" style="overflow-y: scroll !important; min-height: 0;">
                    <!-- Messages will be loaded here -->
                </div>

                <!-- Message Input Area - WhatsApp Style -->
                <div class="border-t border-gray-200 bg-white">
                    <!-- Message Preview (WhatsApp style) -->
                    <div id="message-preview" class="hidden px-4 pt-3 pb-2 bg-gray-50 border-b border-gray-200">
                        <div class="flex items-start space-x-3">
                            <div class="flex-1 bg-white rounded-lg p-3 border border-gray-200 shadow-sm">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-semibold text-gray-500">Preview</span>
                                    <button onclick="clearPreview()" class="text-gray-400 hover:text-gray-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                <!-- Text Preview -->
                                <div id="text-preview" class="text-sm text-gray-800 whitespace-pre-wrap"></div>
                                <!-- File Preview -->
                                <div id="file-preview-container" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Input Form -->
                    <form id="message-form" onsubmit="sendMessage(event)" class="px-4 py-3">
                        <div class="flex items-end space-x-2">
                            <!-- Attachment Button -->
                            <label for="file-input" class="flex-shrink-0 p-2 text-gray-500 hover:text-gray-700 cursor-pointer rounded-full hover:bg-gray-100 transition-colors">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                </svg>
                            </label>
                            <input type="file" id="file-input" class="hidden" accept="image/*,video/*,.pdf,.doc,.docx,.txt" onchange="handleFileSelect(event)" multiple>
                            
                            <!-- Text Input -->
                            <div class="flex-1 relative">
                    <textarea id="message-input" 
                                          rows="1"
                                          placeholder="Type a message..."
                                          class="w-full px-4 py-2.5 pr-10 border border-gray-300 rounded-full focus:ring-2 focus:ring-[#eef2ff] focus:border-[#eef2ff] resize-none overflow-hidden"
                                          style="min-height: 44px; max-height: 120px;"
                                          onkeydown="handleKeyDown(event)"
                                          oninput="handleInputChange(this)"></textarea>
                            </div>
                            
                            <!-- Send Button -->
                            <button type="submit" 
                                    id="send-button"
                                    class="flex-shrink-0 p-2.5 bg-[#eef2ff] text-gray-900 rounded-full hover:bg-[#e0e7ff] focus:outline-none focus:ring-2 focus:ring-[#eef2ff] disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentConversationId = null;
let messagePollInterval = null;
let selectedFiles = [];
let activeTab = 'inbox';

const conversations = <?= json_encode($conversations, JSON_UNESCAPED_UNICODE) ?>;

function setActiveTab(tab) {
    activeTab = tab;
        document.getElementById('tab-inbox').classList.toggle('border-[#eef2ff]', tab === 'inbox');
        document.getElementById('tab-inbox').classList.toggle('text-gray-900', tab === 'inbox');
    document.getElementById('tab-inbox').classList.toggle('border-transparent', tab !== 'inbox');
    document.getElementById('tab-inbox').classList.toggle('text-gray-600', tab !== 'inbox');
    
        document.getElementById('tab-unread').classList.toggle('border-[#eef2ff]', tab === 'unread');
        document.getElementById('tab-unread').classList.toggle('text-gray-900', tab === 'unread');
    document.getElementById('tab-unread').classList.toggle('border-transparent', tab !== 'unread');
    document.getElementById('tab-unread').classList.toggle('text-gray-600', tab !== 'unread');
    
    filterConversations();
}

function filterConversations() {
    const items = document.querySelectorAll('.conversation-item');
    items.forEach(item => {
        const unreadCount = parseInt(item.querySelector('.unread-badge')?.textContent || '0');
        if (activeTab === 'unread') {
            item.style.display = unreadCount > 0 ? 'block' : 'none';
        } else {
            item.style.display = 'block';
        }
    });
}

async function loadConversation(conversationId) {
    currentConversationId = conversationId;
    
    // Update UI
    document.getElementById('no-conversation-selected').classList.add('hidden');
    document.getElementById('active-conversation').classList.remove('hidden');
    
    // Highlight selected conversation
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.classList.remove('bg-purple-100');
        item.classList.remove('bg-[#eef2ff]');
    });
    document.querySelector(`[data-conversation-id="${conversationId}"]`)?.classList.add('bg-[#eef2ff]');
    
    try {
        const response = await fetch(`/employer/messages/conversation/${conversationId}`, {
            headers: {
                'X-CSRF-Token': getCsrfToken()
            }
        });
        
        const data = await response.json();
        
        if (response.ok) {
            // Update header
            document.getElementById('candidate-name').textContent = data.candidate?.name || data.candidate?.email || 'Unknown';
            if (data.job) {
                document.getElementById('job-title').textContent = `Applied to ${data.job.title}`;
            } else {
                document.getElementById('job-title').textContent = '';
            }
            
            // Load messages
            displayMessages(data.messages);
            
            // Start polling for new messages
            startMessagePolling();
        } else {
            alert('Error loading conversation: ' + (data.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error loading conversation');
    }
}

function displayMessages(messages) {
    const container = document.getElementById('messages-container');
    container.innerHTML = '';
    
    if (messages.length === 0) {
        container.innerHTML = '<div class="text-center text-gray-500 py-8">No messages yet. Start the conversation!</div>';
        return;
    }
    
    let currentDate = null;
    messages.forEach(message => {
        const messageDate = new Date(message.created_at).toDateString();
        if (currentDate !== messageDate) {
            currentDate = messageDate;
            const dateDiv = document.createElement('div');
            dateDiv.className = 'text-center text-xs text-gray-500 my-4';
            dateDiv.textContent = formatDate(message.created_at);
            container.appendChild(dateDiv);
        }
        
        const messageDiv = document.createElement('div');
        messageDiv.className = `flex ${message.is_own ? 'justify-end' : 'justify-start'} items-end space-x-2 mb-1`;
        
        // Avatar for received messages
        if (!message.is_own) {
            const avatarDiv = document.createElement('div');
            avatarDiv.className = 'flex-shrink-0 w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center';
            const senderInitial = (message.sender?.name || message.sender?.email || 'U').charAt(0).toUpperCase();
            avatarDiv.innerHTML = `<span class="text-xs font-semibold text-gray-600">${senderInitial}</span>`;
            messageDiv.appendChild(avatarDiv);
        }
        
        const bubbleDiv = document.createElement('div');
        bubbleDiv.className = `max-w-xs lg:max-w-md px-3 py-2 rounded-lg shadow-sm ${
            message.is_own 
                ? 'bg-[#eef2ff] text-gray-900 rounded-br-none' 
                : 'bg-white text-gray-900 border border-gray-200 rounded-bl-none'
        }`;
        
        // Attachments first (WhatsApp style)
        if (message.attachments && message.attachments.length > 0) {
            message.attachments.forEach(att => {
                const attDiv = document.createElement('div');
                attDiv.className = 'mb-2 rounded-lg overflow-hidden';
                
                const fileType = att.type || '';
                const fileName = att.name || 'attachment';
                const fileUrl = att.url || '#';
                
                if (fileType.startsWith('image/')) {
                    // Image preview
                    const imgDiv = document.createElement('div');
                    imgDiv.className = 'relative';
                    const img = document.createElement('img');
                    img.src = fileUrl;
                    img.className = 'w-full max-w-sm rounded-lg cursor-pointer hover:opacity-90 transition-opacity';
                    img.alt = fileName;
                    img.onclick = () => window.open(fileUrl, '_blank');
                    imgDiv.appendChild(img);
                    attDiv.appendChild(imgDiv);
                } else if (fileType.startsWith('video/')) {
                    // Video preview
                    const videoDiv = document.createElement('div');
                    videoDiv.className = 'relative';
                    const video = document.createElement('video');
                    video.src = fileUrl;
                    video.className = 'w-full max-w-sm rounded-lg';
                    video.controls = true;
                    videoDiv.appendChild(video);
                    attDiv.appendChild(videoDiv);
                } else {
                    // PDF or other files
                    const fileLink = document.createElement('a');
                    fileLink.href = fileUrl;
                    fileLink.target = '_blank';
                    fileLink.className = `flex items-center space-x-2 p-3 rounded-lg ${
                        message.is_own ? 'bg-[#eef2ff]' : 'bg-gray-100'
                    } hover:opacity-90 transition-opacity`;
                    fileLink.innerHTML = `
                        <svg class="w-8 h-8 ${message.is_own ? 'text-white' : 'text-red-500'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium ${message.is_own ? 'text-white' : 'text-gray-900'} truncate">${fileName}</p>
                            <p class="text-xs ${message.is_own ? 'text-gray-500' : 'text-gray-500'}">${att.size ? formatFileSize(att.size) : 'Click to download'}</p>
                        </div>
                        <svg class="w-5 h-5 ${message.is_own ? 'text-white' : 'text-gray-500'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                    `;
                    attDiv.appendChild(fileLink);
                }
                
                bubbleDiv.appendChild(attDiv);
            });
        }
        
        // Message body
        if (message.body) {
            const bodyP = document.createElement('p');
            bodyP.className = `text-sm whitespace-pre-wrap ${message.attachments && message.attachments.length > 0 ? 'mt-2' : ''}`;
            bodyP.textContent = message.body;
            bubbleDiv.appendChild(bodyP);
        }
        
        // Time and read status
        const footerDiv = document.createElement('div');
        footerDiv.className = `flex items-center justify-end space-x-1 mt-1`;
        const timeSpan = document.createElement('span');
        timeSpan.className = `text-xs ${message.is_own ? 'text-gray-500' : 'text-gray-500'}`;
        timeSpan.textContent = formatTime(message.created_at);
        footerDiv.appendChild(timeSpan);
        
        // Read receipt for sent messages
        if (message.is_own && message.is_read) {
            const readIcon = document.createElement('span');
            readIcon.className = 'text-gray-400 ml-1';
            readIcon.innerHTML = '<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>';
            footerDiv.appendChild(readIcon);
        }
        
        bubbleDiv.appendChild(footerDiv);
        messageDiv.appendChild(bubbleDiv);
        
        // Avatar for sent messages (on the right)
        if (message.is_own) {
            const avatarDiv = document.createElement('div');
            avatarDiv.className = 'flex-shrink-0 w-8 h-8 rounded-full bg-[#eef2ff] flex items-center justify-center';
            avatarDiv.innerHTML = '<span class="text-xs font-semibold text-gray-900">You</span>';
            messageDiv.appendChild(avatarDiv);
        }
        
        container.appendChild(messageDiv);
    });
    
    // Scroll to bottom with smooth animation
    setTimeout(() => {
        container.scrollTo({
            top: container.scrollHeight,
            behavior: 'smooth'
        });
    }, 100);
}

async function sendMessage(event) {
    event.preventDefault();
    
    if (!currentConversationId) {
        alert('Please select a conversation');
        return;
    }
    
    const input = document.getElementById('message-input');
    const body = input.value.trim();
    
    if (!body && (!selectedFiles || selectedFiles.length === 0)) {
        return;
    }
    
    // Disable send button
    const sendButton = document.getElementById('send-button');
    sendButton.disabled = true;
    sendButton.innerHTML = '<svg class="animate-spin h-6 w-6" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
    
    const formData = new FormData();
    formData.append('conversation_id', currentConversationId);
    if (body) {
        formData.append('body', body);
    }
    
    // Add all selected files
    if (selectedFiles && selectedFiles.length > 0) {
        selectedFiles.forEach((file, index) => {
            formData.append(`attachment_${index}`, file);
        });
    }
    
    try {
        console.log('Sending message to conversation:', currentConversationId);
        console.log('Message body:', body);
        console.log('Files count:', selectedFiles ? selectedFiles.length : 0);
        console.log('Selected files:', selectedFiles);
        
        // Debug: Log FormData contents
        console.log('FormData entries:');
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ', pair[1]);
        }
        
        const response = await fetch('/employer/messages/send', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': getCsrfToken()
                // Don't set Content-Type for FormData - browser will set it with boundary
            },
            body: formData
        });
        
        console.log('Response status:', response.status, response.statusText);
        
        let data;
        try {
            const responseText = await response.text();
            console.log('Response text:', responseText);
            data = responseText ? JSON.parse(responseText) : {};
        } catch (parseError) {
            console.error('Failed to parse response:', parseError);
            throw new Error('Invalid response from server');
        }
        
        if (response.ok && data.success) {
            console.log('Message sent successfully');
            input.value = '';
            input.style.height = 'auto';
            clearPreview();
            // Reload messages without full page reload
            await loadConversation(currentConversationId);
            // Refresh conversation list without full page reload
            await refreshConversationList();
        } else {
            console.error('Error response:', data);
            let errorMsg = data.error || 'Failed to send message';
            // Show more detailed error for file upload issues
            if (errorMsg.includes('file size') || errorMsg.includes('exceeds')) {
                errorMsg += '\n\nPlease try:\n- Compressing the file\n- Using a smaller file\n- Contacting admin to increase upload limits';
            }
            alert('Error: ' + errorMsg);
        }
    } catch (error) {
        console.error('Error sending message:', error);
        alert('Error sending message: ' + error.message);
    } finally {
        // Re-enable send button
        sendButton.disabled = false;
        sendButton.innerHTML = '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>';
    }
}

function handleKeyDown(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        document.getElementById('message-form').dispatchEvent(new Event('submit'));
    }
}

function handleFileSelect(event) {
    const files = Array.from(event.target.files);
    if (files.length > 0) {
        // Check file sizes (2MB limit from PHP config)
        const maxSize = 2 * 1024 * 1024; // 2MB in bytes
        const oversizedFiles = files.filter(file => file.size > maxSize);
        
        if (oversizedFiles.length > 0) {
            const fileNames = oversizedFiles.map(f => f.name).join(', ');
            const fileSizes = oversizedFiles.map(f => (f.size / (1024 * 1024)).toFixed(2) + ' MB').join(', ');
            alert(`File size too large!\n\nFiles: ${fileNames}\nSizes: ${fileSizes}\n\nMaximum allowed: 2 MB per file\n\nPlease compress or use smaller files.`);
            // Clear the input
            event.target.value = '';
            return;
        }
        
        selectedFiles = files;
        showFilePreview(files);
        showMessagePreview();
    }
}

function showFilePreview(files) {
    const container = document.getElementById('file-preview-container');
    container.innerHTML = '';
    
    files.forEach((file, index) => {
        const fileDiv = document.createElement('div');
        fileDiv.className = 'flex items-center space-x-2 p-2 bg-gray-50 rounded-lg mb-2';
        fileDiv.id = `file-preview-${index}`;
        
        // File icon based on type
        let icon = '';
        let preview = '';
        
        if (file.type.startsWith('image/')) {
            icon = `<svg class="w-8 h-8 text-[#eef2ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>`;
            // Image preview
            const reader = new FileReader();
            reader.onload = (e) => {
                preview = `<img src="${e.target.result}" class="w-20 h-20 object-cover rounded-lg" alt="Preview">`;
                fileDiv.innerHTML = `
                    ${preview}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">${file.name}</p>
                        <p class="text-xs text-gray-500">${formatFileSize(file.size)}</p>
                    </div>
                    <button onclick="removeFile(${index})" class="text-red-500 hover:text-red-700 p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                `;
            };
            reader.readAsDataURL(file);
        } else if (file.type.startsWith('video/')) {
            icon = `<svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
            </svg>`;
            // Video preview
            const reader = new FileReader();
            reader.onload = (e) => {
                preview = `<video src="${e.target.result}" class="w-20 h-20 object-cover rounded-lg" controls></video>`;
                fileDiv.innerHTML = `
                    ${preview}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">${file.name}</p>
                        <p class="text-xs text-gray-500">${formatFileSize(file.size)}</p>
                    </div>
                    <button onclick="removeFile(${index})" class="text-red-500 hover:text-red-700 p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                `;
            };
            reader.readAsDataURL(file);
        } else {
            // PDF or other files
            icon = `<svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>`;
            fileDiv.innerHTML = `
                ${icon}
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">${file.name}</p>
                    <p class="text-xs text-gray-500">${formatFileSize(file.size)}</p>
                </div>
                <button onclick="removeFile(${index})" class="text-red-500 hover:text-red-700 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            `;
        }
        
        container.appendChild(fileDiv);
    });
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

function removeFile(index) {
    selectedFiles.splice(index, 1);
    const fileDiv = document.getElementById(`file-preview-${index}`);
    if (fileDiv) fileDiv.remove();
    if (selectedFiles.length === 0) {
        const textInput = document.getElementById('message-input');
        if (!textInput.value.trim()) {
            clearPreview();
        }
    } else {
        showFilePreview(selectedFiles);
    }
}

function handleInputChange(textarea) {
    // Auto-resize textarea
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
    
    // Show preview if there's content
    const text = textarea.value.trim();
    if (text || (selectedFiles && selectedFiles.length > 0)) {
        showMessagePreview();
    } else {
        hideMessagePreview();
    }
}

function showMessagePreview() {
    const previewDiv = document.getElementById('message-preview');
    const textPreview = document.getElementById('text-preview');
    const textInput = document.getElementById('message-input');
    
    if (textInput.value.trim()) {
        textPreview.textContent = textInput.value;
        textPreview.classList.remove('hidden');
    } else {
        textPreview.textContent = '';
        textPreview.classList.add('hidden');
    }
    
    if (textInput.value.trim() || (selectedFiles && selectedFiles.length > 0)) {
        previewDiv.classList.remove('hidden');
    } else {
        previewDiv.classList.add('hidden');
    }
}

function hideMessagePreview() {
    document.getElementById('message-preview').classList.add('hidden');
}

function clearPreview() {
    selectedFiles = [];
    document.getElementById('file-input').value = '';
    document.getElementById('file-preview-container').innerHTML = '';
    document.getElementById('message-input').value = '';
    document.getElementById('message-input').style.height = 'auto';
    hideMessagePreview();
}

function startMessagePolling() {
    if (messagePollInterval) {
        clearInterval(messagePollInterval);
    }
    
    messagePollInterval = setInterval(async () => {
        if (currentConversationId) {
            try {
                const response = await fetch(`/employer/messages/${currentConversationId}/messages`, {
                    headers: {
                        'X-CSRF-Token': getCsrfToken()
                    }
                });
                const data = await response.json();
                if (response.ok) {
                    displayMessages(data.messages);
                }
            } catch (error) {
                console.error('Polling error:', error);
            }
        }
    }, 3000); // Poll every 3 seconds
}

function refreshMessages() {
    location.reload();
}

async function refreshConversationList() {
    try {
        const response = await fetch('/employer/messages');
        if (response.ok) {
            // Just update the conversation list without full reload
            // The polling will handle message updates
            console.log('Conversation list refreshed');
        }
    } catch (error) {
        console.error('Error refreshing conversation list:', error);
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);
    
    if (date.toDateString() === today.toDateString()) {
        return 'Today';
    } else if (date.toDateString() === yesterday.toDateString()) {
        return 'Yesterday';
    } else {
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }
}

function formatTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
}

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

function scheduleInterview() {
    alert('Interview scheduling feature coming soon!');
}

// Auto-load conversation if conversation ID is in URL
<?php if ($selectedConversationId > 0): ?>
document.addEventListener('DOMContentLoaded', () => {
    loadConversation(<?= $selectedConversationId ?>);
});
<?php endif; ?>

// Auto-refresh unread count
setInterval(async () => {
    try {
        const response = await fetch('/employer/messages/unread-count', {
            headers: {
                'X-CSRF-Token': getCsrfToken()
            }
        });
        const data = await response.json();
        if (response.ok) {
            document.getElementById('unread-count').textContent = data.unread_count || 0;
        }
    } catch (error) {
        console.error('Error fetching unread count:', error);
    }
}, 10000); // Every 10 seconds
</script>
