<div class="max-w-xl mx-auto">
  <div class="bg-white shadow rounded-lg p-6">
    <h1 class="text-xl font-semibold text-gray-900">Employment Verification</h1>
    <p class="text-sm text-gray-600 mb-4">Confirm the employment details below.</p>
    <form action="/hr/verify" method="post" class="space-y-4">
      <input type="hidden" name="token" value="<?= htmlspecialchars($request['token'] ?? '') ?>" />
      <div>
        <label class="block text-sm font-medium text-gray-700">Status</label>
        <select name="status" class="mt-1 block w-full rounded-md border-gray-300">
          <option value="verified">Verified</option>
          <option value="mismatch">Mismatch</option>
          <option value="declined">Declined</option>
        </select>
      </div>
      <div class="flex items-center gap-2">
        <input type="checkbox" name="confirmed_working" value="1" class="h-4 w-4 text-blue-600 border-gray-300 rounded" />
        <label class="text-sm text-gray-700">Was the candidate working at this company?</label>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Duration</label>
        <input name="duration_text" class="mt-1 block w-full rounded-md border-gray-300" placeholder="e.g., Jan 2022 to Dec 2024" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Designation</label>
        <input name="designation" class="mt-1 block w-full rounded-md border-gray-300" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Rehire Eligibility</label>
        <select name="rehire_eligibility" class="mt-1 block w-full rounded-md border-gray-300">
          <option value="yes">Yes</option>
          <option value="no">No</option>
          <option value="unknown">Unknown</option>
        </select>
      </div>
      <div class="flex items-center gap-2">
        <input type="checkbox" name="misconduct" value="1" class="h-4 w-4 text-red-600 border-gray-300 rounded" />
        <label class="text-sm text-gray-700">Any misconduct reported?</label>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Remarks</label>
        <textarea name="remarks" rows="3" class="mt-1 block w-full rounded-md border-gray-300"></textarea>
      </div>
      <button class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Submit</button>
    </form>
  </div>
</div>
