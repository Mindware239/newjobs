<div class="max-w-4xl mx-auto py-10 sm:px-6 lg:px-8">
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Create New Sales Lead
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Add a new prospect to your sales pipeline.
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <a href="/master/sales/leads" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Cancel
            </a>
        </div>
    </div>

    <div class="bg-white shadow-xl rounded-lg overflow-hidden">
        <form action="/master/sales/leads/store" method="POST">
            <div class="p-8 space-y-8">
                
                <!-- Section 1: Company & Contact -->
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900 border-b pb-2 mb-4">
                        Company & Contact Details
                    </h3>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                        <div class="col-span-1">
                            <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">Company Name <span class="text-red-500">*</span></label>
                            <input type="text" name="company_name" id="company_name" required 
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition duration-150 ease-in-out">
                        </div>

                        <div class="col-span-1">
                            <label for="contact_name" class="block text-sm font-medium text-gray-700 mb-1">Contact Name <span class="text-red-500">*</span></label>
                            <input type="text" name="contact_name" id="contact_name" required 
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition duration-150 ease-in-out">
                        </div>

                        <div class="col-span-1">
                            <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input type="email" name="contact_email" id="contact_email" 
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition duration-150 ease-in-out">
                        </div>

                        <div class="col-span-1">
                            <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                            <input type="text" name="contact_phone" id="contact_phone" 
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition duration-150 ease-in-out">
                        </div>
                    </div>
                </div>

                <!-- Section 2: Deal Information -->
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900 border-b pb-2 mb-4">
                        Deal Information
                    </h3>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label for="deal_value" class="block text-sm font-medium text-gray-700 mb-1">Deal Value</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input type="number" name="deal_value" id="deal_value" step="0.01" placeholder="0.00" 
                                    class="block w-full pl-7 pr-12 px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition duration-150 ease-in-out">
                                <div class="absolute inset-y-0 right-0 flex items-center">
                                    <label for="currency" class="sr-only">Currency</label>
                                    <select id="currency" name="currency" class="focus:ring-indigo-500 focus:border-indigo-500 h-full py-0 pl-2 pr-7 border-transparent bg-transparent text-gray-500 sm:text-sm rounded-md">
                                        <option value="INR">INR</option>
                                        <option value="USD">USD</option>
                                        <option value="EUR">EUR</option>
                                        <option value="GBP">GBP</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="source" class="block text-sm font-medium text-gray-700 mb-1">Lead Source</label>
                            <select id="source" name="source" 
                                class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md transition duration-150 ease-in-out border">
                                <option value="manual">Manual Entry</option>
                                <option value="website">Website Inquiry</option>
                                <option value="referral">Referral</option>
                                <option value="linkedin">LinkedIn</option>
                                <option value="cold_call">Cold Call</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Assignment & Workflow -->
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900 border-b pb-2 mb-4">
                        Assignment & Workflow
                    </h3>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                        <div class="col-span-1">
                            <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1">Assign To</label>
                            <select id="assigned_to" name="assigned_to" 
                                class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md transition duration-150 ease-in-out border">
                                <option value="">-- Unassigned --</option>
                                <?php foreach ($salesTeam as $staff): ?>
                                    <option value="<?= $staff['id'] ?>">
                                        <?= htmlspecialchars($staff['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-span-1">
                            <label for="stage" class="block text-sm font-medium text-gray-700 mb-1">Initial Stage</label>
                            <select id="stage" name="stage" 
                                class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md transition duration-150 ease-in-out border">
                                <option value="new">New</option>
                                <option value="contacted">Contacted</option>
                                <option value="demo_done">Demo Done</option>
                                <option value="follow_up">Follow Up</option>
                                <option value="payment_pending">Payment Pending</option>
                            </select>
                        </div>

                        <div class="col-span-1">
                            <label for="next_followup_at" class="block text-sm font-medium text-gray-700 mb-1">Next Follow-up</label>
                            <input type="datetime-local" name="next_followup_at" id="next_followup_at" 
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition duration-150 ease-in-out">
                        </div>
                    </div>

                    <div class="mt-4 space-y-4">
                        <div class="relative flex items-start">
                            <div class="flex items-center h-5">
                                <input id="is_urgent" name="is_urgent" type="checkbox" 
                                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded transition duration-150 ease-in-out">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="is_urgent" class="font-medium text-gray-700">Urgent Lead</label>
                                <p class="text-gray-500">Mark this lead as high priority.</p>
                            </div>
                        </div>
                        <div class="relative flex items-start">
                            <div class="flex items-center h-5">
                                <input id="is_featured" name="is_featured" type="checkbox" 
                                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded transition duration-150 ease-in-out">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="is_featured" class="font-medium text-gray-700">Featured</label>
                                <p class="text-gray-500">Highlight this lead in lists.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Notes -->
                <div>
                    <label for="internal_notes" class="block text-sm font-medium text-gray-700 mb-1">Internal Notes</label>
                    <textarea id="internal_notes" name="internal_notes" rows="4" 
                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition duration-150 ease-in-out"></textarea>
                </div>

            </div>

            <div class="px-8 py-5 bg-gray-50 border-t border-gray-200 flex justify-end items-center">
                <a href="/master/sales/leads" class="text-sm font-medium text-gray-700 hover:text-gray-500 mr-5">
                    Cancel
                </a>
                <button type="submit" class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                    Create Lead
                </button>
            </div>
        </form>
    </div>
</div>
