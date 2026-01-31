<div class="space-y-6">
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                <?= htmlspecialchars($lead['company_name'] ?? 'Unknown Company') ?>
            </h2>
            <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
                <div class="mt-2 flex items-center text-sm text-gray-500">
                    <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                    </svg>
                    <?= htmlspecialchars($lead['contact_email'] ?? '') ?>
                </div>
                <div class="mt-2 flex items-center text-sm text-gray-500">
                    <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                    </svg>
                    Lead Age: <?= !empty($lead['created_at']) ? floor((time() - strtotime($lead['created_at'])) / 86400) : 0 ?> days
                </div>
                <?php if (!empty($lead['is_featured'])): ?>
                    <div class="mt-2 flex items-center text-sm text-yellow-600 font-medium">
                        <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        Featured
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
            <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                Edit Lead
            </button>
            <?php if ($lead['stage'] !== 'converted'): ?>
                <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none">
                    Mark Converted
                </button>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Content Grid -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Left Column (Main Info) -->
        <div class="space-y-6 lg:col-span-2">
            <!-- Deal Card -->
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Deal Information</h3>
                </div>
                <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                    <dl class="sm:divide-y sm:divide-gray-200">
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Deal Value</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <?= htmlspecialchars($lead['currency'] ?? 'INR') ?> <?= number_format((float)($lead['deal_value'] ?? 0), 2) ?>
                            </dd>
                        </div>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Paid Amount</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <?= htmlspecialchars($lead['currency'] ?? 'INR') ?> <?= number_format((float)($lead['paid_amount'] ?? 0), 2) ?>
                                <?php if (($lead['deal_value'] ?? 0) > 0): ?>
                                    <span class="ml-2 text-xs text-gray-500">
                                        (<?= round((($lead['paid_amount'] ?? 0) / $lead['deal_value']) * 100) ?>% Paid)
                                    </span>
                                <?php endif; ?>
                            </dd>
                        </div>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Stage</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    <?= ucfirst(str_replace('_', ' ', $lead['stage'])) ?>
                                </span>
                            </dd>
                        </div>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Plan Type</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <?= htmlspecialchars($lead['plan_type'] ?? 'N/A') ?>
                            </dd>
                        </div>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Source / Campaign</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <?= htmlspecialchars($lead['source'] ?? 'Unknown') ?>
                                <?php if (!empty($lead['campaign'])): ?>
                                    <span class="text-gray-500">via <?= htmlspecialchars($lead['campaign']) ?></span>
                                <?php endif; ?>
                            </dd>
                        </div>
                        <?php if (!empty($lead['lost_reason'])): ?>
                            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-red-50">
                                <dt class="text-sm font-medium text-red-700">Lost Reason</dt>
                                <dd class="mt-1 text-sm text-red-900 sm:mt-0 sm:col-span-2">
                                    <?= htmlspecialchars($lead['lost_reason']) ?>
                                    <?php if (!empty($lead['lost_notes'])): ?>
                                        <p class="mt-1 text-xs text-red-700"><?= htmlspecialchars($lead['lost_notes']) ?></p>
                                    <?php endif; ?>
                                </dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>
            
            <!-- Internal Notes -->
             <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Internal Notes</h3>
                    <button class="text-xs text-indigo-600 hover:text-indigo-900">Add Note</button>
                </div>
                <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                    <p class="text-sm text-gray-900 whitespace-pre-wrap"><?= htmlspecialchars($lead['internal_notes'] ?? 'No notes added.') ?></p>
                </div>
             </div>
             
             <!-- Activity Timeline (Placeholder) -->
             <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Activity Timeline</h3>
                </div>
                <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                    <ul class="space-y-4">
                        <li class="flex space-x-3">
                            <div class="flex-shrink-0">
                                <div class="h-2 w-2 rounded-full bg-gray-400 mt-2"></div>
                            </div>
                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                <div>
                                    <p class="text-sm text-gray-500">Lead created <span class="font-medium text-gray-900">manually</span></p>
                                </div>
                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                    <?php if (!empty($lead['created_at'])): ?>
                                        <time datetime="<?= $lead['created_at'] ?>"><?= date('M j', strtotime($lead['created_at'])) ?></time>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                        <!-- More dynamic items would go here -->
                    </ul>
                </div>
             </div>

            <!-- Existing Employer Data (if any) -->
            <?php if ($employer): ?>
                <div class="bg-white shadow sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Linked Employer Account</h3>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                         <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <div class="text-sm text-gray-500">Company</div>
                                <div class="font-medium"><?= htmlspecialchars($employer['company_name']) ?></div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">KYC Status</div>
                                <div><?= htmlspecialchars($employer['kyc_status']) ?></div>
                            </div>
                         </div>
                         
                         <?php if (!empty($subscription)): ?>
                            <div class="mt-4 pt-4 border-t">
                                <div class="text-sm text-gray-500 mb-2">Current Subscription</div>
                                <div class="flex items-center justify-between">
                                    <span class="font-medium"><?= htmlspecialchars($subscription['status']) ?></span>
                                    <span class="text-sm text-gray-500">Expires: <?= htmlspecialchars($subscription['expires_at'] ?? 'N/A') ?></span>
                                </div>
                            </div>
                         <?php endif; ?>
                    </div>
                </div>
                
                <?php if (!empty($payments)): ?>
                    <div class="bg-white shadow sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Payments</h3>
                        </div>
                        <div class="border-t border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach (array_slice($payments, 0, 5) as $p): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('M j, Y', strtotime($p['created_at'])) ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= $p['currency'] ?> <?= $p['amount'] ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $p['status'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <!-- Right Column (Sidebar) -->
        <div class="space-y-6 lg:col-span-1">
            <!-- Contact Card -->
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Contact Details</h3>
                </div>
                <div class="border-t border-gray-200 px-4 py-5 sm:px-6 space-y-4">
                    <div>
                        <div class="text-sm text-gray-500">Name</div>
                        <div class="font-medium"><?= htmlspecialchars($lead['contact_name']) ?></div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Email</div>
                        <div class="text-indigo-600 hover:underline">
                            <a href="mailto:<?= htmlspecialchars($lead['contact_email']) ?>"><?= htmlspecialchars($lead['contact_email']) ?></a>
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Phone</div>
                        <div class="font-medium"><?= htmlspecialchars($lead['contact_phone']) ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Assignment Card -->
             <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Assignment</h3>
                </div>
                <div class="border-t border-gray-200 px-4 py-5 sm:px-6 space-y-4">
                    <div>
                        <div class="text-sm text-gray-500">Assigned To</div>
                        <div class="flex items-center mt-1">
                            <?php if (!empty($lead['assigned_name'])): ?>
                                <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-600 mr-2">
                                    <?= strtoupper(substr($lead['assigned_name'], 0, 1)) ?>
                                </div>
                                <span class="font-medium"><?= htmlspecialchars($lead['assigned_name']) ?></span>
                            <?php else: ?>
                                <span class="text-gray-400 italic">Unassigned</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Manager</div>
                        <div class="font-medium mt-1">
                            <?= htmlspecialchars($lead['manager_name'] ?? 'None') ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Follow Up Card -->
             <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Follow Up</h3>
                </div>
                <div class="border-t border-gray-200 px-4 py-5 sm:px-6 space-y-4">
                    <div>
                        <div class="text-sm text-gray-500">Next Follow Up</div>
                        <?php if (!empty($lead['next_followup_at'])): ?>
                            <div class="font-medium mt-1 <?= (strtotime($lead['next_followup_at']) < time() && $lead['followup_status'] != 'done') ? 'text-red-600' : '' ?>">
                                <?= date('M j, Y g:i A', strtotime($lead['next_followup_at'])) ?>
                            </div>
                        <?php else: ?>
                            <div class="text-gray-400 italic mt-1">Not scheduled</div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Status</div>
                        <div class="mt-1">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                <?= ucfirst($lead['followup_status'] ?? 'Pending') ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
