<?php
$title = 'Marketing Campaigns';
?>

<div class="space-y-8" x-data="campaignsApp(<?= htmlspecialchars(json_encode($campaigns)) ?>)">
    
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight">Marketing Campaigns</h2>
            <p class="text-slate-500 dark:text-slate-400">Manage and track your outreach initiatives.</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- View Toggle -->
            <div class="flex bg-slate-100 dark:bg-slate-800 p-1 rounded-lg">
                <button @click="view = 'grid'" :class="view === 'grid' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'" class="p-2 rounded-md transition-all">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                </button>
                <button @click="view = 'list'" :class="view === 'list' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'" class="p-2 rounded-md transition-all">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
            </div>

            <a href="/sales/manager/campaigns/create" class="px-4 py-2 bg-indigo-600 text-white hover:bg-indigo-700 rounded-xl font-medium shadow-lg shadow-indigo-200/50 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                <span class="hidden sm:inline">Create Campaign</span>
            </a>
        </div>
    </div>

    <!-- Stats Summary -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <template x-for="stat in stats" :key="stat.label">
            <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                <div :class="stat.colorClass" class="text-sm font-medium" x-text="stat.label"></div>
                <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1" x-text="stat.value"></div>
            </div>
        </template>
    </div>

    <!-- Filters & Search -->
    <div class="flex flex-col sm:flex-row gap-4">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            <input type="text" x-model="search" placeholder="Search campaigns..." class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent dark:text-white placeholder-slate-400">
        </div>
        <select x-model="filter" class="px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:text-white">
            <option value="all">All Status</option>
            <option value="active">Active</option>
            <option value="scheduled">Scheduled</option>
            <option value="paused">Paused</option>
            <option value="completed">Completed</option>
        </select>
    </div>

    <!-- Campaign Grid View -->
    <div x-show="view === 'grid'" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <template x-for="c in filteredCampaigns" :key="c.id">
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 hover:shadow-md hover:border-indigo-200 dark:hover:border-indigo-900 transition-all group flex flex-col">
                <div class="p-6 flex-1">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                <span x-html="getTypeIcon(c.type)"></span>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors" x-text="c.name"></h3>
                                <span class="text-xs text-slate-500 dark:text-slate-400" x-text="c.type"></span>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium" :class="getStatusClass(c.status)" x-text="capitalize(c.status)"></span>
                    </div>

                    <div class="grid grid-cols-3 gap-4 mb-6">
                        <div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Leads</div>
                            <div class="font-semibold text-slate-800 dark:text-slate-200" x-text="formatNumber(c.leads)"></div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Spent</div>
                            <div class="font-semibold text-slate-800 dark:text-slate-200" x-text="c.spent"></div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">ROI</div>
                            <div class="font-semibold text-emerald-600 dark:text-emerald-400" x-text="c.roi"></div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400">
                            <span>Progress</span>
                            <span x-text="c.progress + '%'"></span>
                        </div>
                        <div class="h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-500 rounded-full transition-all duration-500" :style="'width: ' + c.progress + '%'"></div>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between rounded-b-2xl">
                    <div class="flex -space-x-2">
                        <template x-for="i in c.members" :key="i">
                            <img class="w-6 h-6 rounded-full border-2 border-white dark:border-slate-800" :src="'https://ui-avatars.com/api/?name=User+' + i + '&background=random'" alt="">
                        </template>
                    </div>
                    <button class="text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">View Details &rarr;</button>
                </div>
            </div>
        </template>

        <!-- Create New Placeholder -->
        <a href="/sales/manager/campaigns/create" class="flex flex-col items-center justify-center p-6 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-800 hover:border-indigo-300 dark:hover:border-indigo-700 hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 transition-all h-full min-h-[280px] group">
            <div class="w-12 h-12 rounded-full bg-indigo-50 dark:bg-indigo-900/20 text-indigo-400 flex items-center justify-center mb-4 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/40 group-hover:text-indigo-600 dark:group-hover:text-indigo-300 transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            </div>
            <span class="font-semibold text-slate-600 dark:text-slate-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Create New Campaign</span>
            <span class="text-sm text-slate-400 dark:text-slate-500 mt-1">Launch a new outreach</span>
        </a>
    </div>

    <!-- Campaign List View -->
    <div x-show="view === 'list'" class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-950/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Campaign</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Stats</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Progress</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    <template x-for="c in filteredCampaigns" :key="c.id">
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 mr-3">
                                        <span x-html="getTypeIcon(c.type)" class="scale-75"></span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-slate-900 dark:text-white" x-text="c.name"></div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400" x-text="c.type"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium" :class="getStatusClass(c.status)" x-text="capitalize(c.status)"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-900 dark:text-white"><span x-text="formatNumber(c.leads)"></span> Leads</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400" x-text="c.spent"></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="w-full max-w-[140px]">
                                    <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400 mb-1">
                                        <span x-text="c.progress + '%'"></span>
                                    </div>
                                    <div class="h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                        <div class="h-full bg-indigo-500 rounded-full" :style="'width: ' + c.progress + '%'"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a :href="'/sales/manager/campaigns/' + c.id + '/edit'" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">Edit</a>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function campaignsApp(campaignsData) {
    return {
        view: 'grid',
        search: '',
        filter: 'all',
        campaigns: campaignsData || [],
        
        get filteredCampaigns() {
            return this.campaigns.filter(c => {
                const matchesSearch = c.name.toLowerCase().includes(this.search.toLowerCase()) || 
                                      c.type.toLowerCase().includes(this.search.toLowerCase());
                const matchesFilter = this.filter === 'all' || c.status === this.filter;
                return matchesSearch && matchesFilter;
            });
        },

        get stats() {
            return [
                { label: 'Active Campaigns', value: this.campaigns.filter(c => c.status === 'active').length, colorClass: 'text-indigo-600 dark:text-indigo-400' },
                { label: 'Total Leads', value: this.formatNumber(this.campaigns.reduce((acc, c) => acc + c.leads, 0)), colorClass: 'text-emerald-600 dark:text-emerald-400' },
                { label: 'Conversions', value: this.campaigns.reduce((acc, c) => acc + c.conversions, 0), colorClass: 'text-purple-600 dark:text-purple-400' },
                { label: 'Total Spend', value: '₹' + this.formatNumber(this.campaigns.reduce((acc, c) => acc + parseInt(c.spent.replace(/[^0-9]/g, '')), 0)), colorClass: 'text-slate-600 dark:text-slate-400' },
            ];
        },

        getStatusClass(status) {
            const classes = {
                'active': 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 ring-1 ring-emerald-600/20 dark:ring-emerald-400/20',
                'scheduled': 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 ring-1 ring-blue-600/20 dark:ring-blue-400/20',
                'paused': 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 ring-1 ring-amber-600/20 dark:ring-amber-400/20',
                'completed': 'bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-400 ring-1 ring-slate-600/20 dark:ring-slate-400/20',
            };
            return classes[status] || classes['completed'];
        },

        getTypeIcon(type) {
            const icons = {
                'Email': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />',
                'Webinar': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />',
                'Social': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />',
                'Phone': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />',
            };
            return '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">' + (icons[type] || icons['Email']) + '</svg>';
        },

        capitalize(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        },

        formatNumber(num) {
            return new Intl.NumberFormat('en-IN').format(num);
        }
    }
}
</script>