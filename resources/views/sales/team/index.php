<?php
// Ensure executives array exists
if (!isset($executives)) {
    $executives = [];
}
if (!isset($kpis)) {
    $kpis = [];
}
?>

<div class="flex flex-col h-[calc(100vh-8rem)]">
    
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Sales Team</h2>
            <p class="text-slate-500 text-sm mt-1">Manage team members and performance.</p>
        </div>
        <button onclick="document.getElementById('addMemberForm').classList.toggle('hidden')" class="px-4 py-2 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Member
        </button>
    </div>

    <!-- Add Member Form (Hidden by default) -->
    <div id="addMemberForm" class="hidden bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6 transition-all">
        <h3 class="text-lg font-bold text-slate-800 mb-4">Add New Team Member</h3>
        <form action="/sales/team/add" method="post" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-1">
                <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                <input name="name" type="text" placeholder="e.g. Jane Doe" class="w-full text-sm border-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="md:col-span-1">
                <label class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                <input name="email" type="email" placeholder="jane@company.com" class="w-full text-sm border-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="md:col-span-1">
                <label class="block text-sm font-medium text-slate-700 mb-1">Role</label>
                <select name="role" class="w-full text-sm border-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="executive">Sales Executive</option>
                    <option value="manager">Sales Manager</option>
                </select>
            </div>
            <div class="md:col-span-1 flex items-end">
                <button class="w-full px-4 py-2 bg-slate-900 text-white rounded-lg font-medium hover:bg-slate-800 transition-colors">Create Account</button>
            </div>
        </form>
    </div>

    <!-- Team Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 flex-1 overflow-hidden flex flex-col">
        <div class="overflow-x-auto flex-1">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50 sticky top-0 z-10">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Member</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Role</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Active Leads</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Conversions</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Follow-ups</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    <?php foreach ($executives as $e): $uid = (int)$e['id']; ?>
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <img class="h-10 w-10 rounded-full ring-2 ring-white" src="https://ui-avatars.com/api/?name=<?= urlencode($e['name'] ?? 'User') ?>&background=random" alt="">
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-slate-900"><?= htmlspecialchars($e['name'] ?? 'Unknown') ?></div>
                                    <div class="text-sm text-slate-500"><?= htmlspecialchars($e['email'] ?? '') ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if (($e['role'] ?? '') === 'sales_manager' || ($e['role'] ?? '') === 'manager'): ?>
                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800 border border-purple-200">
                                    Manager
                                </span>
                            <?php else: ?>
                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                                    Executive
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-slate-700"><?= (int)($kpis[$uid]['leads'] ?? 0) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-emerald-600"><?= (int)($kpis[$uid]['conversions'] ?? 0) ?></span>
                                <span class="text-xs text-slate-400">won</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php $pending = (int)($kpis[$uid]['pending_followups'] ?? 0); ?>
                            <span class="px-2.5 py-1 inline-flex text-xs font-medium rounded-lg <?= $pending > 5 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600' ?>">
                                <?= $pending ?> pending
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="/sales/manager/view-executive/<?= $uid ?>" class="inline-flex items-center px-3 py-1.5 border border-indigo-200 text-xs font-medium rounded-lg text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-colors mr-2">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                View
                            </a>
                            <form action="/sales/team/<?= $uid ?>/remove" method="post" class="inline" onsubmit="return confirm('Are you sure?');">
                                <button class="text-slate-400 hover:text-red-600 transition-colors p-2 hover:bg-red-50 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
