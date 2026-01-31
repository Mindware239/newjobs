<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Sales Leads</h1>
            <p class="text-sm text-gray-500">Manage your sales pipeline and track revenue.</p>
        </div>
        <div class="flex space-x-2">
            <a href="/master/sales/leads/create" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Lead
            </a>
            <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4">
        <form method="get" action="/master/sales/leads" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div class="col-span-1 md:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                <input type="text" name="search" id="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Company, Email, Name" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            </div>
            <div>
                <label for="stage" class="block text-sm font-medium text-gray-700">Stage</label>
                <select name="stage" id="stage" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">All Stages</option>
                    <?php foreach (['new','contacted','demo_done','follow_up','payment_pending','converted','lost'] as $opt): ?>
                        <option value="<?= $opt ?>" <?= (($filters['stage'] ?? '') === $opt) ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $opt)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="assigned_to" class="block text-sm font-medium text-gray-700">Assigned To</label>
                <select name="assigned_to" id="assigned_to" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="0">All Staff</option>
                    <?php if (!empty($salesTeam)): ?>
                        <?php foreach ($salesTeam as $staff): ?>
            <option value="<?= $staff['id'] ?>" <?= ((int)($filters['assigned_to'] ?? 0) === (int)$staff['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($staff['name']) ?>
            </option>
        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div>
                <label for="source" class="block text-sm font-medium text-gray-700">Source</label>
                <select name="source" id="source" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">All Sources</option>
                    <?php foreach (['import','form','referral','cold_call'] as $src): ?>
                        <option value="<?= $src ?>" <?= (($filters['source'] ?? '') === $src) ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $src)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-black text-white px-4 py-2 rounded-md hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Leads Table -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deal Value</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stage</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Follow Up</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($leads)): ?>
                        <?php foreach ($leads as $lead): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" name="selected_leads[]" value="<?= $lead['id'] ?>" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <?php if (!empty($lead['logo_url'])): ?>
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full object-cover" src="<?= htmlspecialchars($lead['logo_url']) ?>" alt="">
                                            </div>
                                        <?php else: ?>
                                            <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded-full flex items-center justify-center text-gray-500 font-bold">
                                                <?= strtoupper(substr($lead['company_name'] ?? '?', 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?= htmlspecialchars($lead['company_name'] ?? 'Unknown Company') ?>
                                                <?php if ($lead['is_urgent']): ?>
                                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Urgent</span>
                                                <?php endif; ?>
                                                <?php if ($lead['is_featured']): ?>
                                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Featured</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?= htmlspecialchars($lead['source'] ?? 'Unknown Source') ?>
                                                <?php if ($lead['campaign']): ?>
                                                    <span class="text-xs text-gray-400">• <?= htmlspecialchars($lead['campaign']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 font-semibold">
                                        <?= htmlspecialchars($lead['currency'] ?? 'INR') ?> <?= number_format((float)($lead['deal_value'] ?? 0), 2) ?>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Paid: <?= number_format((float)($lead['paid_amount'] ?? 0), 2) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?= htmlspecialchars($lead['contact_name'] ?? '') ?></div>
                                    <div class="text-sm text-gray-500"><?= htmlspecialchars($lead['contact_email'] ?? '') ?></div>
                                    <div class="text-sm text-gray-500"><?= htmlspecialchars($lead['contact_phone'] ?? '') ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php 
                                    $stageColors = [
                                        'new' => 'bg-blue-100 text-blue-800',
                                        'contacted' => 'bg-indigo-100 text-indigo-800',
                                        'demo_done' => 'bg-purple-100 text-purple-800',
                                        'follow_up' => 'bg-yellow-100 text-yellow-800',
                                        'payment_pending' => 'bg-orange-100 text-orange-800',
                                        'converted' => 'bg-green-100 text-green-800',
                                        'lost' => 'bg-red-100 text-red-800',
                                    ];
                                    $s = $lead['stage'] ?? 'new';
                                    $color = $stageColors[$s] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $color ?>">
                                        <?= ucfirst(str_replace('_', ' ', $s)) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php if (!empty($lead['assigned_name'])): ?>
                                        <div class="flex items-center">
                                            <div class="h-6 w-6 rounded-full bg-gray-200 flex items-center justify-center text-xs text-gray-600 font-bold mr-2">
                                                <?= strtoupper(substr($lead['assigned_name'], 0, 1)) ?>
                                            </div>
                                            <?= htmlspecialchars($lead['assigned_name']) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-gray-400 italic">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if (!empty($lead['next_followup_at'])): ?>
                                        <?php 
                                            $followup = strtotime($lead['next_followup_at']);
                                            $isOverdue = $followup < time() && ($lead['followup_status'] ?? '') !== 'done';
                                        ?>
                                        <div class="text-sm <?= $isOverdue ? 'text-red-600 font-bold' : 'text-gray-900' ?>">
                                            <?= date('M j, Y', $followup) ?>
                                        </div>
                                        <div class="text-xs text-gray-500"><?= date('g:i A', $followup) ?></div>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="/master/sales/leads/<?= $lead['id'] ?>" class="text-indigo-600 hover:text-indigo-900">View</a>
                                    <span class="mx-1 text-gray-300">|</span>
                                    <a href="#" class="text-gray-400 hover:text-gray-600 cursor-not-allowed">Edit</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-gray-500">
                                No sales leads found matching your criteria.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination (Simple Placeholder) -->
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Previous</a>
                    <a href="#" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Next</a>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing <span class="font-medium">1</span> to <span class="font-medium"><?= count($leads) ?></span> of <span class="font-medium"><?= count($leads) ?></span> results
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const masterCheckbox = document.querySelector('thead input[type="checkbox"]');
        const childCheckboxes = document.querySelectorAll('tbody input[name="selected_leads[]"]');
        
        if(masterCheckbox) {
            masterCheckbox.addEventListener('change', function() {
                childCheckboxes.forEach(cb => cb.checked = this.checked);
            });
        }
    });
</script>
