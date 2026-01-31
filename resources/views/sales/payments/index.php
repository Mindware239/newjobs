<?php
$title = 'Payments & Invoices';

// Mock Data
$stats = [
    'total_collected' => ['value' => '₹45,23,000', 'change' => '+12%', 'trend' => 'up'],
    'pending_amount' => ['value' => '₹8,50,000', 'change' => '-5%', 'trend' => 'down'],
    'overdue' => ['value' => '₹2,10,000', 'change' => '+2%', 'trend' => 'down'],
    'invoices_sent' => ['value' => '142', 'change' => '+8', 'trend' => 'up'],
];

$payments = [
    ['id' => 101, 'client' => 'Acme Corp', 'amount' => '1,20,000', 'status' => 'paid', 'date' => '2023-10-24', 'invoice' => '#INV-2023-001', 'method' => 'Bank Transfer'],
    ['id' => 102, 'client' => 'Globex Inc', 'amount' => '45,000', 'status' => 'pending', 'date' => '2023-10-25', 'invoice' => '#INV-2023-002', 'method' => 'Pending'],
    ['id' => 103, 'client' => 'Soylent Corp', 'amount' => '2,50,000', 'status' => 'overdue', 'date' => '2023-10-15', 'invoice' => '#INV-2023-003', 'method' => 'Credit Card'],
    ['id' => 104, 'client' => 'Umbrella Corp', 'amount' => '85,000', 'status' => 'paid', 'date' => '2023-10-22', 'invoice' => '#INV-2023-004', 'method' => 'UPI'],
    ['id' => 105, 'client' => 'Stark Ind', 'amount' => '5,00,000', 'status' => 'pending', 'date' => '2023-10-26', 'invoice' => '#INV-2023-005', 'method' => 'Pending'],
    ['id' => 106, 'client' => 'Wayne Ent', 'amount' => '3,20,000', 'status' => 'paid', 'date' => '2023-10-20', 'invoice' => '#INV-2023-006', 'method' => 'Bank Transfer'],
];

$statusColors = [
    'paid' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
    'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
    'overdue' => 'bg-red-100 text-red-700 border-red-200',
];
?>

<div class="space-y-8" x-data="{ currentTab: 'all', searchTerm: '' }">
    
    <!-- Header & Actions -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Financial Overview</h2>
            <p class="text-slate-500">Track revenue, invoices, and pending payments.</p>
        </div>
        <div class="flex gap-3">
            <button class="px-4 py-2 bg-white text-slate-600 hover:bg-slate-50 border border-slate-200 rounded-xl font-medium shadow-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                Export
            </button>
            <button class="px-4 py-2 bg-indigo-600 text-white hover:bg-indigo-700 rounded-xl font-medium shadow-lg shadow-indigo-200 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                Create Invoice
            </button>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <span class="flex items-center text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">
                    <?= $stats['total_collected']['change'] ?>
                    <svg class="w-3 h-3 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                </span>
            </div>
            <p class="text-sm font-medium text-slate-500">Total Collected</p>
            <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= $stats['total_collected']['value'] ?></h3>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <span class="flex items-center text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">
                    -5% vs last month
                </span>
            </div>
            <p class="text-sm font-medium text-slate-500">Pending Amount</p>
            <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= $stats['pending_amount']['value'] ?></h3>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center text-red-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <span class="flex items-center text-xs font-medium text-red-600 bg-red-50 px-2 py-1 rounded-full">
                    <?= $stats['overdue']['change'] ?>
                </span>
            </div>
            <p class="text-sm font-medium text-slate-500">Overdue</p>
            <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= $stats['overdue']['value'] ?></h3>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                </div>
                <span class="flex items-center text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">
                    <?= $stats['invoices_sent']['change'] ?>
                </span>
            </div>
            <p class="text-sm font-medium text-slate-500">Invoices Sent</p>
            <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= $stats['invoices_sent']['value'] ?></h3>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        
        <!-- Toolbar -->
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2 overflow-x-auto pb-2 sm:pb-0">
                <button @click="currentTab = 'all'" :class="currentTab === 'all' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'" class="px-4 py-2 rounded-lg text-sm font-medium border transition-colors whitespace-nowrap">
                    All Transactions
                </button>
                <button @click="currentTab = 'paid'" :class="currentTab === 'paid' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'" class="px-4 py-2 rounded-lg text-sm font-medium border transition-colors whitespace-nowrap">
                    Paid
                </button>
                <button @click="currentTab = 'pending'" :class="currentTab === 'pending' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'" class="px-4 py-2 rounded-lg text-sm font-medium border transition-colors whitespace-nowrap">
                    Pending
                </button>
                <button @click="currentTab = 'overdue'" :class="currentTab === 'overdue' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'" class="px-4 py-2 rounded-lg text-sm font-medium border transition-colors whitespace-nowrap">
                    Overdue
                </button>
            </div>
            
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input x-model="searchTerm" type="text" class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 sm:text-sm transition duration-150 ease-in-out" placeholder="Search invoice, client...">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Invoice</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Client</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Amount</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    <?php foreach($payments as $p): ?>
                    <tr x-show="(currentTab === 'all' || currentTab === '<?= $p['status'] ?>') && ('<?= strtolower($p['client']) ?>'.includes(searchTerm.toLowerCase()) || '<?= strtolower($p['invoice']) ?>'.includes(searchTerm.toLowerCase()))" class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-indigo-600 font-mono"><?= $p['invoice'] ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600 mr-3">
                                    <?= substr($p['client'], 0, 2) ?>
                                </div>
                                <div class="text-sm font-medium text-slate-900"><?= $p['client'] ?></div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                            <?= date('M d, Y', strtotime($p['date'])) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900">
                            ₹<?= $p['amount'] ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border <?= $statusColors[$p['status']] ?>">
                                <?= ucfirst($p['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button class="text-slate-400 hover:text-indigo-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" /></svg>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <!-- Empty State for Search/Filter -->
                    <tr x-show="$el.parentElement.children.length > 1 && [...$el.parentElement.children].filter(el => el.tagName === 'TR' && el.style.display !== 'none').length === 1" style="display: none;">
                         <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-slate-900">No payments found</h3>
                                <p class="text-slate-500 mt-1">Try adjusting your filters or search terms.</p>
                            </div>
                         </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
            <div class="text-sm text-slate-500">Showing <span class="font-medium text-slate-900">1</span> to <span class="font-medium text-slate-900">6</span> of <span class="font-medium text-slate-900">24</span> results</div>
            <div class="flex gap-2">
                <button class="px-3 py-1 border border-slate-200 rounded-lg text-sm text-slate-600 hover:bg-slate-50 disabled:opacity-50" disabled>Previous</button>
                <button class="px-3 py-1 border border-slate-200 rounded-lg text-sm text-slate-600 hover:bg-slate-50">Next</button>
            </div>
        </div>
    </div>
</div>
