<?php 
/** @var \App\Models\Candidate $candidate */
/** @var array $items */
$candidate = $candidate ?? null;
$items = $items ?? [];

if ($candidate):
    $isPremium = $candidate->isPremium();
    $premiumExpires = $candidate->attributes['premium_expires_at'] ?? null;
    $currentPlanType = null;
    if (!empty($items) && isset($items[0])) {
        foreach ($items as $item) {
            if (($item['status'] ?? '') === 'completed') {
                $currentPlanType = $item['plan_type'] ?? null;
                break;
            }
        }
    }
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Active Premium Status -->
    <?php if ($isPremium && $premiumExpires): ?>
    <div class="bg-gradient-to-r from-primary-50 to-white border-2 border-primary rounded-2xl p-6 mb-10 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 p-4 opacity-10">
            <svg class="w-24 h-24 text-primary" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.321-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
            </svg>
        </div>
        <div class="flex flex-col md:flex-row items-center justify-between gap-6 relative z-10">
            <div class="flex items-center gap-6">
                <div class="w-20 h-20 bg-primary rounded-full flex items-center justify-center shadow-lg ring-4 ring-primary-50">
                    <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.321-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                </div>
                <div>
                    <div class="text-primary font-bold uppercase tracking-wider text-sm">Active Premium Subscription</div>
                    <div class="text-3xl font-extrabold text-gray-900 mt-1">
                        <?= htmlspecialchars(ucfirst(str_replace('_', ' ', (string)($currentPlanType ?? 'Premium')))) ?>
                    </div>
                    <div class="flex items-center gap-2 mt-2 text-gray-700 font-medium">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Expires: <span class="text-gray-900"><?= date('M d, Y', strtotime($premiumExpires)) ?></span>
                    </div>
                </div>
            </div>
            <div class="text-right">
                <div class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-xl font-bold shadow-md">
                    <span class="animate-pulse">⭐</span>
                    <span>Premium Active</span>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-2xl p-10 mb-10 text-center">
        <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">No active premium subscription</h3>
        <p class="text-gray-600 mb-6 max-w-md mx-auto">Upgrade to Jobsence Pro to get top profile visibility, unlimited applications, and AI-powered job matching.</p>
        <a href="/candidate/premium/plans" class="inline-flex items-center px-8 py-3 bg-primary text-white rounded-xl hover:bg-primary-700 font-bold transition shadow-lg hover:shadow-xl transform hover:scale-[1.02]">
            Upgrade to Jobsence Pro
        </a>
    </div>
    <?php endif; ?>

    <!-- Premium Features Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 mb-10">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
            <span class="w-2 h-8 bg-primary rounded-full"></span>
            Premium Benefits
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php 
            $benefits = [
                ['Top Profile Visibility', 'Show your profile at the top to recruiters', 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
                ['Higher Search Ranking', 'Priority placement in search results', 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'],
                ['Verified Badge', 'Stand out with a verified profile badge', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                ['Unlimited Applications', 'Apply to as many jobs as you want', 'M12 19l9 2-9-18-9 18 9-2zm0 0v-8'],
                ['Advanced Analytics', 'Track your profile performance in detail', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                ['Priority Support', 'Get 24/7 faster response from support', 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z']
            ];
            foreach ($benefits as $benefit):
            ?>
            <div class="flex items-start gap-4 p-5 bg-gray-50 rounded-xl hover:bg-primary-50 transition-colors border border-transparent hover:border-primary-100 group">
                <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center shadow-sm text-primary group-hover:bg-primary group-hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $benefit[2] ?>"></path>
                    </svg>
                </div>
                <div>
                    <div class="font-bold text-gray-900"><?= $benefit[0] ?></div>
                    <div class="text-sm text-gray-600 mt-1"><?= $benefit[1] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Billing History -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-8 py-6 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Billing History</h2>
            <span class="text-sm text-gray-500 font-medium"><?= count($items) ?> Records Found</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-8 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-8 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Plan</th>
                        <th class="px-8 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Payment Method</th>
                        <th class="px-8 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-8 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-8 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="6" class="px-8 py-16 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-gray-500 font-medium">No billing history found</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($items as $row): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-8 py-5 whitespace-nowrap text-sm text-gray-900 font-medium">
                                <?= date('M d, Y', strtotime($row['created_at'] ?? 'now')) ?>
                                <div class="text-xs text-gray-400 font-normal"><?= date('H:i A', strtotime($row['created_at'] ?? 'now')) ?></div>
                            </td>
                            <td class="px-8 py-5 whitespace-nowrap">
                                <div class="text-sm text-gray-900 font-bold"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', (string)($row['plan_type'] ?? 'Unknown')))) ?></div>
                                <div class="text-xs text-gray-500">ID: #PUR-<?= $row['id'] ?></div>
                            </td>
                            <td class="px-8 py-5 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                                    <span class="text-sm text-gray-600 font-medium uppercase"><?= $row['payment_method'] ?? '—' ?></span>
                                </div>
                            </td>
                            <td class="px-8 py-5 whitespace-nowrap text-sm text-right font-extrabold text-gray-900">
                                ₹<?= number_format((float)($row['amount'] ?? 0), 2) ?>
                            </td>
                            <td class="px-8 py-5 whitespace-nowrap">
                                <?php 
                                $status = strtolower($row['status'] ?? 'pending');
                                $statusClasses = [
                                    'completed' => 'bg-green-100 text-green-800 border-green-200',
                                    'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
                                    'refunded' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'failed' => 'bg-red-100 text-red-800 border-red-200'
                                ];
                                $statusClass = $statusClasses[$status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                ?>
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full border <?= $statusClass ?>">
                                    <?= ucfirst($status) ?>
                                </span>
                            </td>
                            <td class="px-8 py-5 whitespace-nowrap text-right text-sm">
                                <?php if (($row['status'] ?? '') === 'completed'): ?>
                                    <a href="<?= htmlspecialchars((string)($row['receipt_url'] ?? '#')) ?>" target="_blank" class="inline-flex items-center px-4 py-2 bg-primary text-white text-xs font-bold rounded-lg hover:bg-primary-700 transition shadow-sm hover:shadow-md">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                                        </svg>
                                        Receipt
                                    </a>
                                <?php elseif (($row['status'] ?? '') === 'pending'): ?>
                                    <a href="/candidate/premium/plans" class="inline-flex items-center px-4 py-2 border-2 border-primary text-primary text-xs font-bold rounded-lg hover:bg-primary hover:text-white transition group">
                                        Complete
                                        <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                        </svg>
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-400">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="px-8 py-4 bg-gray-50 border-t border-gray-200">
            <p class="text-xs text-gray-500 text-center">For any billing queries, please contact <a href="mailto:gm@jobsence.com" class="text-primary font-bold">gm@jobsence.com</a></p>
        </div>
    </div>
</div>
<?php endif; ?>
