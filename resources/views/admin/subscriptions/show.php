<div>
    <div class="mb-8">
        <a href="/admin/subscriptions" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">← Back to Subscriptions</a>
        <h1 class="text-3xl font-bold text-gray-900">Subscription Details</h1>
        <p class="mt-2 text-sm text-gray-600">Review subscription info and payment history</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div class="text-sm text-gray-500">Company</div>
                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($subscription['company_name'] ?? 'N/A') ?></div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Employer Email</div>
                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($subscription['employer_email'] ?? 'N/A') ?></div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Plan</div>
                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($subscription['plan_name'] ?? 'N/A') ?></div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Status</div>
                        <?php 
                        $status = strtolower((string)($subscription['status'] ?? ''));
                        $badge = 'bg-gray-100 text-gray-800';
                        if ($status === 'active' || $status === 'trial') $badge = 'bg-green-100 text-green-800';
                        elseif ($status === 'expired') $badge = 'bg-red-100 text-red-800';
                        elseif ($status === 'cancelled') $badge = 'bg-yellow-100 text-yellow-800';
                        ?>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $badge ?>">
                            <?= ucfirst($status ?: 'unknown') ?>
                        </span>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Billing Cycle</div>
                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars(ucfirst((string)($subscription['billing_cycle'] ?? 'monthly'))) ?></div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Auto Renew</div>
                        <div class="text-sm font-medium text-gray-900"><?= ((int)($subscription['auto_renew'] ?? 0)) === 1 ? 'Yes' : 'No' ?></div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Start Date</div>
                        <div class="text-sm font-medium text-gray-900"><?= !empty($subscription['started_at']) ? date('M d, Y', strtotime($subscription['started_at'])) : '—' ?></div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">End Date</div>
                        <div class="text-sm font-medium text-gray-900"><?= !empty($subscription['expires_at']) ? date('M d, Y', strtotime($subscription['expires_at'])) : 'N/A' ?></div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Next Billing</div>
                        <div class="text-sm font-medium text-gray-900"><?= !empty($subscription['next_billing_date']) ? date('M d, Y', strtotime($subscription['next_billing_date'])) : '—' ?></div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Trial Ends</div>
                        <div class="text-sm font-medium text-gray-900"><?= !empty($subscription['trial_ends_at']) ? date('M d, Y', strtotime($subscription['trial_ends_at'])) : '—' ?></div>
                    </div>
                    <div class="md:col-span-2">
                        <div class="text-sm text-gray-500">Usage & Limits</div>
                        <?php
                            $limits = [
                                'Contacts' => ['used' => (int)($subscription['contacts_used_this_month'] ?? 0), 'limit' => (int)($subscription['max_contacts_per_month'] ?? 0)],
                                'Resume downloads' => ['used' => (int)($subscription['resume_downloads_used_this_month'] ?? 0), 'limit' => (int)($subscription['max_resume_downloads'] ?? 0)],
                                'Chat messages' => ['used' => (int)($subscription['chat_messages_used_this_month'] ?? 0), 'limit' => (int)($subscription['max_chat_messages'] ?? 0)],
                                'Job posts' => ['used' => (int)($subscription['job_posts_used'] ?? 0), 'limit' => (int)($subscription['max_job_posts'] ?? 0)],
                            ];
                        ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-gray-900">
                            <?php foreach ($limits as $label => $vals): ?>
                                <div><?= $label ?>: <?= (int)$vals['used'] ?> / <?= ($vals['limit'] === -1 ? 'Unlimited' : (int)$vals['limit']) ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Automation</h3>
                <div class="space-y-2 text-sm text-gray-700">
                    <div>Auto renew: <?= ((int)($subscription['auto_renew'] ?? 0)) === 1 ? 'Yes' : 'No' ?></div>
                    <div>Next billing: <?= !empty($subscription['next_billing_date']) ? date('M d, Y', strtotime($subscription['next_billing_date'])) : '—' ?></div>
                    <div>Expires: <?= !empty($subscription['expires_at']) ? date('M d, Y', strtotime($subscription['expires_at'])) : '—' ?></div>
                    <div>Grace ends: <?= !empty($subscription['grace_period_ends_at']) ? date('M d, Y', strtotime($subscription['grace_period_ends_at'])) : '—' ?></div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Payments</h3>
                <?php if (empty($payments)): ?>
                    <div class="text-sm text-gray-600">No payment records found for this subscription.</div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Txn ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Currency</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($payments as $p): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($p['gateway_payment_id'] ?? ($p['txn_id'] ?? '')) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= number_format((float)($p['amount'] ?? 0), 2) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($p['currency'] ?? 'INR') ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= (($p['status'] ?? '') === 'completed' || ($p['payment_status'] ?? '') === 'success') ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                            <?= htmlspecialchars(ucfirst((string)($p['status'] ?? ($p['payment_status'] ?? 'unknown')))) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= !empty($p['created_at']) ? date('M d, Y H:i', strtotime($p['created_at'])) : '—' ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="/admin/subscriptions/payments/<?= (int)($p['id'] ?? 0) ?>/invoice" class="text-blue-600 hover:text-blue-800">Download</a>
                                        <form method="POST" action="/admin/subscriptions/payments/<?= (int)($p['id'] ?? 0) ?>/regenerate-invoice" class="inline-block ml-2">
                                            <button type="submit" class="text-gray-700 hover:text-gray-900">Regenerate</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Subscription History</h3>
                <?php
                    $historyRows = [];
                    try {
                        $db = \App\Core\Database::getInstance();
                        $historyRows = $db->fetchAll("SELECT h.*, sp.name as plan_name FROM subscription_history h LEFT JOIN subscription_plans sp ON sp.id = h.plan_id WHERE h.subscription_id = :sid ORDER BY h.id DESC", ['sid' => (int)($subscription['id'] ?? 0)]);
                    } catch (\Throwable $t) {}
                ?>
                <?php if (empty($historyRows)): ?>
                    <div class="text-sm text-gray-600">No history available.</div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Start</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">End</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($historyRows as $h): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($h['plan_name'] ?? '-') ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars(ucfirst((string)($h['status'] ?? '-'))) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= !empty($h['started_at']) ? date('M d, Y', strtotime($h['started_at'])) : '—' ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= !empty($h['ends_at']) ? date('M d, Y', strtotime($h['ends_at'])) : '—' ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($h['change_reason'] ?? '') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Actions</h3>
                <div class="space-y-6 text-sm text-gray-700">
                    <form method="POST" action="/admin/subscriptions/<?= (int)($subscription['id'] ?? 0) ?>/status" class="space-y-2">
                        <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="font-medium">Update Status</div>
                        <div class="flex items-center space-x-2">
                            <select name="status" class="px-3 py-2 border border-gray-300 rounded-md">
                                <?php $cur = strtolower((string)($subscription['status'] ?? 'active')); ?>
                                <option value="active" <?= $cur === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="trial" <?= $cur === 'trial' ? 'selected' : '' ?>>Trial</option>
                                <option value="inactive" <?= $cur === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                <option value="expired" <?= $cur === 'expired' ? 'selected' : '' ?>>Expired</option>
                                <option value="cancelled" <?= $cur === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                            <input type="text" name="reason" placeholder="Reason (optional)" class="px-3 py-2 border border-gray-300 rounded-md w-60">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Save</button>
                        </div>
                    </form>

                    <form method="POST" action="/admin/subscriptions/<?= (int)($subscription['id'] ?? 0) ?>/change-plan" class="space-y-2">
                        <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="font-medium">Change Plan</div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                            <select name="plan_id" class="px-3 py-2 border border-gray-300 rounded-md">
                                <?php foreach (($plans ?? []) as $p): ?>
                                    <option value="<?= (int)$p['id'] ?>" <?= ((int)($subscription['plan_id'] ?? 0) === (int)$p['id']) ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="billing_cycle" class="px-3 py-2 border border-gray-300 rounded-md">
                                <?php $bc = (string)($subscription['billing_cycle'] ?? 'monthly'); ?>
                                <option value="monthly" <?= $bc === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                                <option value="quarterly" <?= $bc === 'quarterly' ? 'selected' : '' ?>>Quarterly</option>
                                <option value="annual" <?= $bc === 'annual' ? 'selected' : '' ?>>Annual</option>
                            </select>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Update</button>
                        </div>
                    </form>

                    <form method="POST" action="/admin/subscriptions/<?= (int)($subscription['id'] ?? 0) ?>/extend" class="space-y-2">
                        <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="font-medium">Extend Expiry</div>
                        <div class="flex items-center space-x-2">
                            <input type="number" name="days" min="1" value="30" class="px-3 py-2 border border-gray-300 rounded-md w-24">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Extend</button>
                        </div>
                    </form>

                    <form method="POST" action="/admin/subscriptions/<?= (int)($subscription['id'] ?? 0) ?>/grace" class="space-y-2">
                        <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="font-medium">Set Grace Period</div>
                        <div class="flex items-center space-x-2">
                            <input type="number" name="grace_days" min="0" value="<?= (int)(!empty($subscription['grace_period_ends_at']) ? ceil((strtotime($subscription['grace_period_ends_at']) - time())/86400) : 3) ?>" class="px-3 py-2 border border-gray-300 rounded-md w-24">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Apply</button>
                        </div>
                    </form>

                    <form method="POST" action="/admin/subscriptions/<?= (int)($subscription['id'] ?? 0) ?>/auto-renew" class="space-y-2">
                        <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="font-medium">Auto Renew</div>
                        <div class="flex items-center space-x-2">
                            <input type="hidden" name="auto_renew" value="<?= ((int)($subscription['auto_renew'] ?? 0)) === 1 ? 0 : 1 ?>">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">
                                <?= ((int)($subscription['auto_renew'] ?? 0)) === 1 ? 'Disable' : 'Enable' ?>
                            </button>
                        </div>
                    </form>

                    <form method="POST" action="/admin/subscriptions/<?= (int)($subscription['id'] ?? 0) ?>/reset-usage" class="space-y-2">
                        <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="font-medium">Reset Monthly Usage</div>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md">Reset</button>
                    </form>

                    <form method="POST" action="/admin/subscriptions/<?= (int)($subscription['id'] ?? 0) ?>/credits" class="space-y-2">
                        <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="font-medium">Add Credits</div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                            <select name="type" class="px-3 py-2 border border-gray-300 rounded-md">
                                <option value="job_posts">Job Posts</option>
                                <option value="contacts">Contact Views</option>
                                <option value="resume_downloads">Resume Downloads</option>
                                <option value="chat_messages">Chat Messages</option>
                                <option value="days">Days Extension</option>
                            </select>
                            <input type="number" name="amount" min="1" value="5" class="px-3 py-2 border border-gray-300 rounded-md">
                            <input type="text" name="note" placeholder="Reason" class="px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Add</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
