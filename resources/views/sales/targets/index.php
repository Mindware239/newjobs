<?php
$title = 'Sales Targets';
?>

<div class="space-y-6" x-data="targetsApp(<?= htmlspecialchars(json_encode($members)) ?>, '<?= $month ?>')">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Sales Targets</h1>
            <p class="text-slate-500 dark:text-slate-400">Track team performance against monthly goals</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" action="/sales/manager/targets" class="flex items-center gap-3">
                <input type="month" name="month" value="<?= $month ?>" 
                       class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       onchange="this.form.submit()">
            </form>
            <button @click="openModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Set Target
            </button>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Revenue Goal Card -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Revenue Goal</p>
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white mt-1">₹<?= number_format($summary['revenue_goal']) ?></h3>
                </div>
                <div class="p-2 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg text-indigo-600 dark:text-indigo-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-2 mb-2">
                <div class="bg-indigo-600 h-2 rounded-full" style="width: <?= min(100, $summary['revenue_percent']) ?>%"></div>
            </div>
            <div class="flex justify-between text-xs">
                <span class="text-slate-600 dark:text-slate-400">Achieved: ₹<?= number_format($summary['revenue_achieved']) ?></span>
                <span class="text-indigo-600 font-medium"><?= $summary['revenue_percent'] ?>%</span>
            </div>
        </div>

        <!-- Deals Closed Card -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Deals Closed Goal</p>
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white mt-1"><?= $summary['deals_goal'] ?></h3>
                </div>
                <div class="p-2 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg text-emerald-600 dark:text-emerald-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 018 0z" />
                    </svg>
                </div>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-2 mb-2">
                <div class="bg-emerald-500 h-2 rounded-full" style="width: <?= min(100, $summary['deals_percent']) ?>%"></div>
            </div>
            <div class="flex justify-between text-xs">
                <span class="text-slate-600 dark:text-slate-400">Achieved: <?= $summary['deals_achieved'] ?></span>
                <span class="text-emerald-600 font-medium"><?= $summary['deals_percent'] ?>%</span>
            </div>
        </div>

        <!-- Participation Card -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Team Participation</p>
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white mt-1"><?= $summary['participation'] ?>/<?= $summary['total_members'] ?></h3>
                </div>
                <div class="p-2 bg-purple-50 dark:bg-purple-900/20 rounded-lg text-purple-600 dark:text-purple-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-2 mb-2">
                <?php 
                $partPercent = $summary['total_members'] > 0 ? round(($summary['participation'] / $summary['total_members']) * 100) : 0;
                ?>
                <div class="bg-purple-500 h-2 rounded-full" style="width: <?= $partPercent ?>%"></div>
            </div>
            <div class="flex justify-between text-xs">
                <span class="text-slate-600 dark:text-slate-400">Active Members</span>
                <span class="text-purple-600 font-medium"><?= $partPercent ?>%</span>
            </div>
        </div>
    </div>

    <!-- Team Targets Table -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200 dark:border-slate-700 flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="text-lg font-bold text-slate-800 dark:text-white">Individual Performance</h2>
            <div class="relative">
                <input type="text" x-model="search" placeholder="Search member..." class="pl-10 pr-4 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white w-full md:w-64">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700 text-xs uppercase text-slate-500 dark:text-slate-400 font-semibold">
                        <th class="p-4">Sales Executive</th>
                        <th class="p-4">Revenue Target</th>
                        <th class="p-4">Achieved</th>
                        <th class="p-4">Deals (Target/Achieved)</th>
                        <th class="p-4">Progress</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <template x-for="member in filteredMembers" :key="member.id">
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-xs" x-text="member.initials"></div>
                                    <div>
                                        <p class="text-sm font-medium text-slate-800 dark:text-white" x-text="member.name"></p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400" x-text="member.role"></p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-sm text-slate-600 dark:text-slate-400" x-text="'₹' + formatNumber(member.target_revenue)"></td>
                            <td class="p-4 text-sm font-medium text-slate-800 dark:text-white" x-text="'₹' + formatNumber(member.achieved_revenue)"></td>
                            <td class="p-4 text-sm text-slate-600 dark:text-slate-400">
                                <span x-text="member.target_deals"></span> / <span x-text="member.achieved_deals" class="font-medium text-slate-800 dark:text-white"></span>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-24 bg-slate-100 dark:bg-slate-700 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full" :class="getProgressColor(member.percentage)" :style="'width: ' + Math.min(member.percentage, 100) + '%'"></div>
                                    </div>
                                    <span class="text-xs font-medium" :class="getProgressTextColor(member.percentage)" x-text="member.percentage + '%'"></span>
                                </div>
                            </td>
                            <td class="p-4 text-right">
                                <button @click="openModal(member)" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm font-medium">Edit</button>
                            </td>
                        </tr>
                    </template>
                    <template x-if="filteredMembers.length === 0">
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500 dark:text-slate-400">
                                No members found.
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Target Modal -->
    <div x-show="isModalOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
         style="display: none;">
        
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg w-full max-w-md p-6 m-4" @click.away="closeModal()">
            <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-4" x-text="modalTitle">Set Target</h3>
            
            <form action="/sales/manager/targets" method="POST">
                <input type="hidden" name="user_id" x-model="editingMember.id">
                <input type="hidden" name="month" x-model="targetMonth">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Sales Executive</label>
                        <select name="user_id_select" x-model="editingMember.id" class="w-full bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" :disabled="isEditing">
                             <template x-for="m in members" :key="m.id">
                                 <option :value="m.id" x-text="m.name"></option>
                             </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Revenue Target (₹)</label>
                        <input type="number" step="0.01" name="revenue_target" x-model="editingMember.target_revenue" class="w-full bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Deals Target</label>
                        <input type="number" name="deals_target" x-model="editingMember.target_deals" class="w-full bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="closeModal()" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200">Cancel</button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">Save Target</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function targetsApp(membersData, currentMonth) {
    return {
        search: '',
        members: membersData || [],
        targetMonth: currentMonth,
        isModalOpen: false,
        isEditing: false,
        modalTitle: 'Set Target',
        editingMember: {
            id: '',
            name: '',
            target_revenue: 0,
            target_deals: 0
        },
        
        get filteredMembers() {
            if (!this.search) return this.members;
            return this.members.filter(m => 
                m.name.toLowerCase().includes(this.search.toLowerCase()) || 
                m.email.toLowerCase().includes(this.search.toLowerCase())
            );
        },

        formatNumber(num) {
            return new Intl.NumberFormat('en-IN').format(num);
        },

        getProgressColor(percentage) {
            if (percentage >= 80) return 'bg-emerald-500';
            if (percentage >= 50) return 'bg-indigo-500';
            return 'bg-amber-500';
        },

        getProgressTextColor(percentage) {
            if (percentage >= 80) return 'text-emerald-600 dark:text-emerald-400';
            if (percentage >= 50) return 'text-indigo-600 dark:text-indigo-400';
            return 'text-amber-600 dark:text-amber-400';
        },

        openModal(member = null) {
            if (member) {
                this.isEditing = true;
                this.modalTitle = 'Edit Target for ' + member.name;
                this.editingMember = JSON.parse(JSON.stringify(member)); // Deep copy
            } else {
                this.isEditing = false;
                this.modalTitle = 'Set New Target';
                this.editingMember = {
                    id: this.members.length > 0 ? this.members[0].id : '',
                    name: '',
                    target_revenue: 0,
                    target_deals: 0
                };
            }
            this.isModalOpen = true;
        },

        closeModal() {
            this.isModalOpen = false;
        }
    }
}
</script>
