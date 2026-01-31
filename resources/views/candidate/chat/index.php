<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title>Messages - Mindware Infotech</title>
    <link href="/css/output.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        /* Custom Scrollbar Styles */
        .overflow-y-scroll::-webkit-scrollbar,
        #conversations-list::-webkit-scrollbar {
            width: 8px;
        }
        .overflow-y-scroll::-webkit-scrollbar-track,
        #conversations-list::-webkit-scrollbar-track {
            background: #f7fafc;
            border-radius: 4px;
        }
        .overflow-y-scroll::-webkit-scrollbar-thumb,
        #conversations-list::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 4px;
        }
        .overflow-y-scroll::-webkit-scrollbar-thumb:hover,
        #conversations-list::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }
        /* Firefox */
        .overflow-y-scroll,
        #conversations-list {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e0 #f7fafc;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <?php $base = '/'; require __DIR__ . '/../../include/header.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
            <!-- Messages Header -->
            <div class="bg-white border-b border-gray-100 px-6 py-5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold text-gray-900">Messages</h1>
                        <?php if ($totalUnread > 0): ?>
                        <span class="px-3 py-1 bg-red-500 text-white text-xs font-bold uppercase tracking-wide rounded-full shadow-sm animate-pulse">
                            <?= $totalUnread ?> new
                        </span>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center gap-2 px-3 py-1 bg-blue-50 text-blue-700 rounded-full border border-blue-100">
                        <span class="relative flex h-2.5 w-2.5">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-blue-500"></span>
                        </span>
                        <span class="text-xs font-semibold uppercase tracking-wide">Online</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col md:flex-row h-[calc(100vh-250px)] min-h-[600px]">
                <!-- Left Sidebar - Conversations List -->
                <div class="w-full md:w-1/3 border-r border-gray-100 overflow-y-auto bg-gray-50/50" id="conversations-list">
                    <?php if (empty($conversations)): ?>
                        <div class="h-full flex flex-col items-center justify-center p-8 text-center text-gray-500">
                            <div class="bg-white p-4 rounded-full shadow-sm mb-4">
                                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">No messages yet</h3>
                            <p class="text-sm">Conversations with employers will appear here.</p>
                        </div>
                    <?php else: ?>
                        <div class="divide-y divide-gray-100">
                            <?php foreach ($conversations as $conv): ?>
                                <a href="/candidate/chat/<?= $conv['id'] ?>" 
                                   class="block px-5 py-4 hover:bg-white hover:shadow-sm transition-all duration-200 group <?= (($selectedConversationId ?? 0) == $conv['id']) ? 'bg-white border-l-4 border-blue-600 shadow-sm' : 'border-l-4 border-transparent' ?>">
                                    <div class="flex items-start space-x-4">
                                        <!-- Employer Logo/Avatar -->
                                        <div class="flex-shrink-0 relative">
                                            <?php if (!empty($conv['employer_logo'])): ?>
                                                <img src="<?= htmlspecialchars($conv['employer_logo']) ?>" 
                                                     alt="<?= htmlspecialchars($conv['employer_name']) ?>"
                                                     class="w-12 h-12 rounded-full object-cover border-2 border-white shadow-sm group-hover:border-blue-100 transition-colors">
                                            <?php else: ?>
                                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-100 to-blue-50 flex items-center justify-center border-2 border-white shadow-sm group-hover:border-blue-100 transition-colors">
                                                    <span class="text-blue-600 font-bold text-lg">
                                                        <?= strtoupper(substr($conv['employer_name'], 0, 1)) ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($conv['unread_count'] > 0): ?>
                                                <span class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 ring-2 ring-white text-[10px] font-bold text-white">
                                                    <?= $conv['unread_count'] ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <!-- Employer Name -->
                                            <div class="flex items-center justify-between mb-0.5">
                                                <p class="text-sm font-bold text-gray-900 truncate group-hover:text-blue-700 transition-colors">
                                                    <?= htmlspecialchars($conv['employer_name']) ?>
                                                </p>
                                                <?php if (!empty($conv['last_message_time'])): ?>
                                                    <span class="text-xs text-gray-400 font-medium whitespace-nowrap ml-2">
                                                        <?php
                                                        $timestamp = strtotime($conv['last_message_time']);
                                                        $diff = time() - $timestamp;
                                                        
                                                        if ($diff < 60) {
                                                            echo 'Just now';
                                                        } elseif ($diff < 3600) {
                                                            $mins = floor($diff / 60);
                                                            echo $mins . 'm';
                                                        } elseif ($diff < 86400) {
                                                            $hours = floor($diff / 3600);
                                                            echo $hours . 'h';
                                                        } elseif ($diff < 604800) {
                                                            $days = floor($diff / 86400);
                                                            echo $days . 'd';
                                                        } else {
                                                            echo date('M d', $timestamp);
                                                        }
                                                        ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Job Title (if available) -->
                                            <?php if (!empty($conv['job_title'])): ?>
                                                <p class="text-xs text-blue-600 font-medium truncate mb-1 bg-blue-50 inline-block px-1.5 py-0.5 rounded">
                                                    <?= htmlspecialchars($conv['job_title']) ?>
                                                </p>
                                            <?php endif; ?>

                                            <!-- Last Message Preview -->
                                            <?php if (!empty($conv['last_message'])): ?>
                                                <p class="text-sm text-gray-500 truncate group-hover:text-gray-700 transition-colors">
                                                    <?= $conv['last_message_sender_id'] == ($user_id ?? 0) ? '<span class="text-gray-400">You:</span> ' : '' ?>
                                                    <?= htmlspecialchars($conv['last_message']) ?>
                                                </p>
                                            <?php else: ?>
                                                <p class="text-sm text-gray-400 italic">No messages yet</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Right Side - Empty State or Selected Conversation -->
                <div class="hidden md:flex flex-1 items-center justify-center bg-white bg-[url('/assets/images/pattern.svg')] bg-repeat opacity-90">
                    <div class="text-center p-8 max-w-md">
                        <div class="bg-blue-50 p-6 rounded-full inline-flex mb-6 animate-bounce-slow">
                            <svg class="h-16 w-16 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Select a Conversation</h3>
                        <p class="text-gray-500 leading-relaxed">Choose a conversation from the list to view your message history with employers.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh unread count every 30 seconds
        setInterval(async () => {
            try {
                const response = await fetch('/candidate/chat/unread-count');
                const data = await response.json();
                if (data.unread_count > 0) {
                    // Update badge if exists
                    const badge = document.querySelector('.unread-badge');
                    if (badge) {
                        badge.textContent = data.unread_count + ' unread';
                    }
                }
            } catch (error) {
                console.error('Failed to fetch unread count:', error);
            }
        }, 30000);
    </script>
     <?php
require __DIR__ . '/../../include/footer.php';
?>
</body>
</html>
        
