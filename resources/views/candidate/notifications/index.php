<?php
$candidate = $candidate ?? null;
$notifications = $notifications ?? [];
ob_start();
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="p-3 bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl shadow-lg shadow-blue-200">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Notifications</h1>
                <p class="text-gray-600 mt-1">Stay updated with your job applications</p>
            </div>
        </div>
        
        <div x-data>
            <button @click="$dispatch('mark-all-read')" 
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition shadow-sm">
                <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Mark all as read
            </button>
            <button @click="$dispatch('delete-read')" 
                    class="ml-3 inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition shadow-sm">
                <svg class="w-4 h-4 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-1 12a2 2 0 01-2 2H8a2 2 0 01-2-2L5 7m5 4v6m4-6v6M9 7h6m2 0H7"></path>
                </svg>
                Clear read
            </button>
        </div>
    </div>

    <div x-data="notifPage()" x-init="init()" x-cloak class="space-y-6">
        <!-- Filters -->
        <div class="flex items-center gap-2 overflow-x-auto pb-2 sm:pb-0 scrollbar-hide">
            <button @click="filter='all'" 
                    :class="filter==='all' ? 'bg-gray-900 text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200'"
                    class="px-5 py-2 rounded-full text-sm font-medium transition-all whitespace-nowrap">
                All
            </button>
            <button @click="filter='unread'" 
                    :class="filter==='unread' ? 'bg-gray-900 text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200'"
                    class="px-5 py-2 rounded-full text-sm font-medium transition-all whitespace-nowrap">
                Unread
            </button>
        </div>

        <!-- Notifications List -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden min-h-[400px]">
            <?php if (empty($notifications)): ?>
                <div class="flex flex-col items-center justify-center h-[400px] text-center px-4">
                    <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">No notifications yet</h3>
                    <p class="text-gray-500 max-w-sm">When you get job updates, interview requests, or messages, they'll show up here.</p>
                </div>
            <?php else: ?>
                <ul class="divide-y divide-gray-100" x-ref="list">
                    <?php foreach ($notifications as $n): 
                        $isRead = (int)($n['is_read'] ?? 0) === 1;
                        $id = (int)($n['id'] ?? 0);
                        $link = $n['link'] ?? '';
                        $iconType = $n['icon_type'] ?? 'system';
                        
                        // Icon Colors & SVGs
                        $iconBg = 'bg-gray-100';
                        $iconColor = 'text-gray-500';
                        $iconSvg = '';

                        switch ($iconType) {
                            case 'interview':
                                $iconBg = 'bg-blue-100';
                                $iconColor = 'text-blue-600';
                                $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />';
                                break;
                            case 'application':
                                $iconBg = 'bg-blue-100';
                                $iconColor = 'text-blue-600';
                                $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />';
                                break;
                            case 'message':
                $iconBg = 'bg-blue-100';
                $iconColor = 'text-blue-600';
                $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />';
                break;
                            default:
                                $iconBg = 'bg-gray-100';
                                $iconColor = 'text-gray-600';
                                $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 9v2m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />';
                        }
                    ?>
                    <li class="group relative transition-all duration-200 hover:bg-gray-50"
                        :class="rowClass(<?= $isRead ? 'true' : 'false' ?>)"
                        data-id="<?= $id ?>"
                        data-read="<?= $isRead ? '1' : '0' ?>"
                    >
                        <div class="flex items-start p-6 gap-5">
                            <!-- Icon -->
                            <div class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center <?= $iconBg ?> <?= $iconColor ?>">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <?= $iconSvg ?>
                                </svg>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between mb-1">
                                    <h3 class="text-base font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                                        <?= htmlspecialchars($n['title'] ?? 'Notification') ?>
                                    </h3>
                                    <span class="text-xs font-medium text-gray-400 whitespace-nowrap ml-2">
                                        <?= htmlspecialchars($n['time_ago'] ?? '') ?>
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 leading-relaxed mb-3">
                                    <?= htmlspecialchars($n['message'] ?? '') ?>
                                </p>
                                
                                <div class="flex items-center gap-4">
                                    <?php if (!empty($link)): ?>
                                        <a href="<?= htmlspecialchars($link) ?>" class="inline-flex items-center text-sm font-semibold text-blue-600 hover:text-blue-700">
                                            View Details
                                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                            </svg>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if (!$isRead): ?>
                                        <button @click="markRead(<?= $id ?>)" class="text-xs font-medium text-gray-400 hover:text-gray-600">
                                            Mark as read
                                        </button>
                                    <?php endif; ?>
                                    <button @click="deleteNotif(<?= $id ?>)" class="text-xs font-medium text-red-500 hover:text-red-600">
                                        Delete
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Unread Indicator -->
                            <div class="w-2.5 h-2.5 rounded-full bg-blue-600 mt-2 shadow-sm shadow-blue-200" 
                                 x-show="<?= $isRead ? 'false' : 'true' ?>"></div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php if (!empty($pagination ?? null)): 
            $page = (int)($pagination['page'] ?? 1);
            $totalPages = (int)($pagination['totalPages'] ?? 1);
            $perPage = (int)($pagination['perPage'] ?? 20);
            $tab = htmlspecialchars((string)($pagination['tab'] ?? 'all'));
            $baseUrl = '/candidate/notifications';
            $prevPage = max(1, $page - 1);
            $nextPage = min($totalPages, $page + 1);
            $qsPrev = $baseUrl . '?page=' . $prevPage . '&per_page=' . $perPage . '&tab=' . $tab;
            $qsNext = $baseUrl . '?page=' . $nextPage . '&per_page=' . $perPage . '&tab=' . $tab;
        ?>
        <div class="flex items-center justify-between mt-4">
            <a href="<?= $qsPrev ?>" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Previous
            </a>
            <div class="text-sm text-gray-600">
                Page <?= $page ?> of <?= $totalPages ?>
            </div>
            <a href="<?= $qsNext ?>" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                Next
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function notifPage() {
    return {
        filter: 'all',
        init() {
            this.applyFilter();
            this.$watch('filter', () => this.applyFilter());
            window.addEventListener('mark-all-read', () => this.markAll());
            window.addEventListener('delete-read', () => this.clearRead());
        },
        rowClass(isRead) {
            return isRead ? 'opacity-75' : 'bg-blue-50/30';
        },
        applyFilter() {
            const list = this.$refs.list;
            if (!list) return;
            [...list.children].forEach(li => {
                const read = li.getAttribute('data-read') === '1';
                let show = true;
                if (this.filter === 'unread') show = !read;
                li.style.display = show ? '' : 'none';
            });
        },
        async markRead(id) {
            try {
                await fetch(`/candidate/notifications/${id}/read`, {
                    method: 'POST',
                    headers: {'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''}
                });
                
                const li = this.$refs.list.querySelector(`[data-id="${id}"]`);
                if (li) {
                    li.setAttribute('data-read', '1');
                    const dot = li.querySelector('.w-2\\.5');
                    if (dot) dot.style.display = 'none';
                    
                    const btn = li.querySelector('button[class*="text-xs"]');
                    if (btn) btn.remove();
                    
                    li.classList.add('opacity-75');
                    li.classList.remove('bg-blue-50/30');
                }
                this.applyFilter();
            } catch (e) {
                console.error(e);
            }
        },
        async markAll() {
            try {
                await fetch('/candidate/notifications/read-all', {
                    method: 'POST',
                    headers: {'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''}
                });
                
                const list = this.$refs.list;
                if (!list) return;
                
                [...list.children].forEach(li => {
                    li.setAttribute('data-read', '1');
                    const dot = li.querySelector('.w-2\\.5');
                    if (dot) dot.style.display = 'none';
                    
                    const btn = li.querySelector('button[class*="text-xs"]');
                    if (btn) btn.remove();
                    
                    li.classList.add('opacity-75');
                    li.classList.remove('bg-blue-50/30');
                });
                this.applyFilter();
            } catch (e) {
                console.error(e);
            }
        },
        async deleteNotif(id) {
            try {
                const res = await fetch(`/candidate/notifications/${id}/delete`, {
                    method: 'POST',
                    headers: {'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''}
                });
                const out = await res.json();
                if (out && out.success) {
                    const li = this.$refs.list.querySelector(`[data-id="${id}"]`);
                    if (li) li.remove();
                }
            } catch (e) {
                console.error(e);
            }
        },
        async clearRead() {
            try {
                const res = await fetch('/candidate/notifications/delete-read', {
                    method: 'POST',
                    headers: {'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''}
                });
                const out = await res.json();
                if (out && out.success) {
                    const list = this.$refs.list;
                    if (!list) return;
                    [...list.children].forEach(li => {
                        const read = li.getAttribute('data-read') === '1';
                        if (read) li.remove();
                    });
                }
            } catch (e) {
                console.error(e);
            }
        }
    }
}
</script>

<?php
$content = ob_get_clean();
$title = 'Notifications';
require __DIR__ . '/../../candidate/layout.php';
?>
