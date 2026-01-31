<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title>Chat with <?= htmlspecialchars($employer['company_name'] ?? 'Employer') ?> - Mindware Infotech</title>
    <link href="/css/output.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 h-screen flex flex-col overflow-hidden">
    <div x-data="chatConversation()" x-cloak class="flex-1 flex flex-col h-full">
        <?php $base = $base ?? '/'; require __DIR__ . '/../../include/header.php'; ?>

        <div class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 h-[calc(100vh-80px)]">
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden h-full flex flex-col">
                <!-- Chat Header -->
                <div class="bg-white border-b border-gray-100 px-6 py-4 flex-shrink-0 z-10 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <a href="/candidate/chat" class="text-gray-400 hover:text-gray-600 transition-colors mr-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                            </a>
                            <div class="relative">
                                <?php if (!empty($employer['logo_url'])): ?>
                                    <img src="<?= htmlspecialchars($employer['logo_url']) ?>" 
                                         alt="<?= htmlspecialchars($employer['company_name'] ?? 'Employer') ?>"
                                         class="w-12 h-12 rounded-full object-cover border-2 border-gray-100">
                                <?php else: ?>
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-100 to-blue-50 flex items-center justify-center border-2 border-gray-100">
                                        <span class="text-blue-600 font-bold text-lg">
                                            <?= strtoupper(substr($employer['company_name'] ?? 'E', 0, 1)) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                <span class="absolute bottom-0 right-0 block h-3 w-3 rounded-full ring-2 ring-white bg-blue-400"></span>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">
                                    <?= htmlspecialchars($employer['company_name'] ?? 'Employer') ?>
                                </h2>
                                <?php if (!empty($job)): ?>
                                    <p class="text-sm text-gray-500 flex items-center gap-1">
                                        <span>Re:</span>
                                        <a href="/candidate/jobs/<?= htmlspecialchars($job['slug'] ?? $job['id']) ?>" 
                                           class="text-blue-600 hover:text-blue-700 font-medium hover:underline truncate max-w-[200px]">
                                            <?= htmlspecialchars($job['title'] ?? 'Job') ?>
                                        </a>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 px-3 py-1 bg-blue-50 text-blue-700 rounded-full border border-blue-100">
                            <span class="relative flex h-2 w-2">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                            </span>
                            <span class="text-xs font-semibold uppercase tracking-wide">Online</span>
                        </div>
                    </div>
                </div>

                <!-- Messages Container -->
                <div class="flex-1 overflow-y-auto p-6 space-y-6 bg-gray-50/50 scroll-smooth" 
                     id="messages-container">
                    <?php if (empty($messages)): ?>
                        <div class="h-full flex flex-col items-center justify-center text-center">
                            <div class="bg-white p-4 rounded-full shadow-sm mb-4">
                                <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-500 font-medium">No messages yet. Start the conversation!</p>
                        </div>
                    <?php else: ?>
                        <template x-for="msg in messages" :key="msg.id">
                            <div class="flex" :class="msg.is_own ? 'justify-end' : 'justify-start'">
                                <div class="max-w-[75%] md:max-w-[60%]" :class="msg.is_own ? 'order-2' : 'order-1'">
                                    <div class="flex items-end gap-2" :class="msg.is_own ? 'flex-row-reverse' : 'flex-row'">
                                        <!-- Avatar (Optional for own messages) -->
                                        <div class="flex-shrink-0 w-8 h-8 rounded-full overflow-hidden shadow-sm" :class="msg.is_own ? 'hidden md:block' : ''">
                                            <template x-if="msg.is_own">
                                                <div class="w-full h-full bg-blue-100 flex items-center justify-center text-blue-700 text-xs font-bold">
                                                    <?= strtoupper(substr($candidate->attributes['full_name'] ?? 'U', 0, 1)) ?>
                                                </div>
                                            </template>
                                            <template x-if="!msg.is_own">
                                                <?php if (!empty($employer['logo_url'])): ?>
                                                    <img src="<?= htmlspecialchars($employer['logo_url']) ?>" class="w-full h-full object-cover">
                                                <?php else: ?>
                                                    <div class="w-full h-full bg-blue-100 flex items-center justify-center text-blue-600 text-xs font-bold">
                                                        <?= strtoupper(substr($employer['company_name'] ?? 'E', 0, 1)) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </template>
                                        </div>

                                        <!-- Message Bubble -->
                                        <div class="rounded-2xl px-5 py-3 shadow-sm relative group transition-all duration-200"
                                             :class="msg.is_own ? 'bg-blue-100 text-blue-700 rounded-br-none' : 'bg-white text-gray-900 border border-gray-100 rounded-bl-none'">
                                            <p class="text-sm whitespace-pre-wrap leading-relaxed" x-text="msg.body"></p>
                                            
                                            <!-- Attachments (Placeholder logic if needed dynamically) -->
                                            <template x-if="msg.attachments && msg.attachments.length">
                                                <div class="mt-3 space-y-2">
                                                    <template x-for="attachment in (typeof msg.attachments === 'string' ? JSON.parse(msg.attachments) : msg.attachments)" :key="attachment.url">
                                                        <a :href="attachment.url" target="_blank" 
                                                           class="flex items-center gap-2 p-2 rounded bg-black/10 hover:bg-black/20 transition-colors text-xs font-medium">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                                            </svg>
                                                            <span x-text="attachment.name || 'Attachment'"></span>
                                                        </a>
                                                    </template>
                                                </div>
                                            </template>

                                            <div class="flex items-center justify-end gap-1 mt-1 opacity-75">
                                                <span class="text-[10px] font-medium" 
                                                      x-text="new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})">
                                                </span>
                                                <template x-if="msg.is_own && msg.is_read">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    <?php endif; ?>
                </div>

                <!-- Message Input -->
                <div class="border-t border-gray-100 bg-white px-6 py-5 flex-shrink-0 z-10">
                    <form @submit.prevent="sendMessage()" class="flex items-end gap-3">
                        <div class="flex-1 relative">
                            <textarea x-model="messageBody" 
                                      @keydown.enter.exact.prevent="sendMessage()"
                                      @keydown.enter.shift.exact="messageBody += '\n'"
                                      placeholder="Type your message..."
                                      rows="1"
                                      class="w-full pl-5 pr-12 py-3 bg-gray-50 border border-gray-200 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white resize-none transition-all duration-200 min-h-[48px] max-h-32"
                                      style="scrollbar-width: none;"></textarea>
                            
                            <!-- Attachment Input -->
                            <input type="file" x-ref="fileInput" class="hidden" @change="handleFileSelect">
                            <button type="button" 
                                    @click="$refs.fileInput.click()"
                                    class="absolute right-3 bottom-3 text-gray-400 hover:text-gray-600 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                </svg>
                            </button>
                        </div>
                        <button type="submit" 
                                :disabled="!messageBody.trim() && !attachment && !isSending"
                                class="h-12 w-12 flex items-center justify-center bg-blue-600 text-white rounded-full hover:bg-blue-700 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none transition-all duration-200 group">
                            <svg x-show="!isSending" class="w-5 h-5 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            <svg x-show="isSending" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function chatConversation() {
            return {
                messages: <?= json_encode($messages ?? [], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>.map(msg => ({...msg, is_own: msg.sender_user_id == <?= $candidate->attributes['user_id'] ?? 0 ?>})),
                messageBody: '',
                attachment: null,
                conversationId: <?= $conversation['id'] ?? 0 ?>,
                lastMessageId: <?= !empty($messages) ? (int)end($messages)['id'] : 0 ?>,
                isSending: false,
                candidateUserId: <?= $candidate->attributes['user_id'] ?? 0 ?>,

                handleFileSelect(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.attachment = file;
                        this.messageBody = `[Attachment: ${file.name}] ` + this.messageBody;
                    }
                },

                async sendMessage() {
                    if ((!this.messageBody.trim() && !this.attachment) || this.isSending) return;

                    this.isSending = true;
                    const messageText = this.messageBody.trim();
                    const file = this.attachment;
                    
                    // Optimistic update
                    const tempId = Date.now();
                    // Don't push temp message if it's just an attachment upload to avoid complex rollback
                    
                    this.messageBody = '';
                    this.attachment = null;
                    if (this.$refs.fileInput) this.$refs.fileInput.value = '';

                    try {
                        const formData = new FormData();
                        formData.append('conversation_id', this.conversationId);
                        formData.append('body', messageText);
                        if (file) {
                            formData.append('attachment', file);
                        }

                        const response = await fetch('/candidate/chat/send', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-Token': this.getCsrfToken()
                            },
                            body: formData
                        });

                        const data = await response.json();
                        if (data.success && data.message) {
                            data.message.is_own = true;
                            this.messages.push(data.message);
                            this.lastMessageId = data.message.id;
                            this.scrollToBottom();
                        } else {
                            alert(data.error || 'Failed to send message');
                            this.messageBody = messageText; // Restore
                        }
                    } catch (error) {
                        console.error('Send error:', error);
                        alert('Failed to send message. Please try again.');
                        this.messageBody = messageText; // Restore
                    } finally {
                        this.isSending = false;
                    }
                },

                async pollMessages() {
                    try {
                        const response = await fetch(`/candidate/chat/messages?conversation_id=${this.conversationId}&last_message_id=${this.lastMessageId}`);
                        if (!response.ok) {
                            if (response.status === 401 || response.status === 403) {
                                // Session expired
                                window.location.reload();
                                return;
                            }
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const contentType = response.headers.get("content-type");
                        if (!contentType || !contentType.includes("application/json")) {
                            // HTML response received (likely error page)
                            console.warn("Received non-JSON response from poll");
                            return;
                        }

                        const data = await response.json();
                        if (data.success && data.messages && data.messages.length > 0) {
                            const newMessages = data.messages.map(msg => ({
                                ...msg,
                                is_own: (msg.sender_user_id == this.candidateUserId)
                            }));
                            this.messages.push(...newMessages);
                            this.lastMessageId = data.messages[data.messages.length - 1].id;
                            this.scrollToBottom();
                        }
                    } catch (error) {
                        console.error('Poll error:', error);
                    }
                },

                scrollToBottom() {
                    this.$nextTick(() => {
                        const container = document.getElementById('messages-container');
                        if (container) {
                            setTimeout(() => {
                                container.scrollTop = container.scrollHeight;
                            }, 50);
                        }
                    });
                },

                getCsrfToken() {
                    return document.querySelector('meta[name="csrf-token"]')?.content || '';
                },

                init() {
                    this.scrollToBottom();
                    // Poll for new messages every 3 seconds
                    setInterval(() => {
                        this.pollMessages();
                    }, 3000);
                }
            }
        }
    </script>
       <?php
require __DIR__ . '/../../../include/footer.php';
?>
</body>
</html>
