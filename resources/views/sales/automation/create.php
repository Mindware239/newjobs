<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Add Automation Rule</h1>
        <a href="/sales/manager/automation" class="text-sm text-blue-600 hover:text-blue-800">Back to Rules</a>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <form action="/sales/manager/automation" method="POST" class="space-y-4">
            <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div>
                <label class="block text-sm font-medium text-gray-700">Rule Name</label>
                <input type="text" name="name" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Trigger</label>
                <select name="trigger" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="new_lead">New Lead Created</option>
                    <option value="stage_contacted">Stage = Contacted</option>
                    <option value="stage_demo_done">Stage = Done</option>
                    <option value="stage_converted">Stage = Converted</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Action</label>
                <select name="action" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="assign_round_robin">Assign Round Robin</option>
                    <option value="send_email">Send Email</option>
                    <option value="schedule_followup">Schedule Follow-up</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="Active">Active</option>
                    <option value="Paused">Paused</option>
                </select>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Create</button>
            </div>
        </form>
    </div>
</div>
