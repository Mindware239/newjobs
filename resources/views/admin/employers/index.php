<?php
$__countryIso = [
    'india' => 'in', 'united states' => 'us', 'usa' => 'us', 'united kingdom' => 'gb', 'uk' => 'gb',
    'canada' => 'ca', 'australia' => 'au', 'germany' => 'de', 'france' => 'fr', 'italy' => 'it',
    'spain' => 'es', 'china' => 'cn', 'japan' => 'jp', 'south korea' => 'kr', 'brazil' => 'br',
    'mexico' => 'mx', 'russia' => 'ru', 'south africa' => 'za', 'united arab emirates' => 'ae', 'uae' => 'ae',
    'saudi arabia' => 'sa', 'pakistan' => 'pk', 'bangladesh' => 'bd', 'nepal' => 'np', 'sri lanka' => 'lk',
    'indonesia' => 'id', 'philippines' => 'ph', 'malaysia' => 'my', 'thailand' => 'th', 'singapore' => 'sg',
    'vietnam' => 'vn', 'nigeria' => 'ng', 'kenya' => 'ke', 'egypt' => 'eg', 'turkey' => 'tr'
];
$__dialCode = [
    'india' => '91', 'united states' => '1', 'usa' => '1', 'united kingdom' => '44', 'uk' => '44',
    'canada' => '1', 'australia' => '61', 'germany' => '49', 'france' => '33', 'italy' => '39',
    'spain' => '34', 'china' => '86', 'japan' => '81', 'south korea' => '82', 'brazil' => '55',
    'mexico' => '52', 'russia' => '7', 'south africa' => '27', 'united arab emirates' => '971', 'uae' => '971',
    'saudi arabia' => '966', 'pakistan' => '92', 'bangladesh' => '880', 'nepal' => '977', 'sri lanka' => '94',
    'indonesia' => '62', 'philippines' => '63', 'malaysia' => '60', 'thailand' => '66', 'singapore' => '65',
    'vietnam' => '84', 'nigeria' => '234', 'kenya' => '254', 'egypt' => '20', 'turkey' => '90'
];
function __iso($name) { global $__countryIso; $k = strtolower(trim((string)$name)); return $k && isset($__countryIso[$k]) ? $__countryIso[$k] : ''; }
function __dial($name) { global $__dialCode; $k = strtolower(trim((string)$name)); return $k && isset($__dialCode[$k]) ? $__dialCode[$k] : ''; }
function __fmt_phone($number, $country) {
    $n = trim((string)$number);
    $code = __dial($country);
    if ($n === '') return 'N/A';
    $digits = preg_replace('/\D+/', '', $n);
    if ($code !== '' && strpos($n, '+') !== 0 && strpos($digits, $code) !== 0) {
        return '+' . $code . ' ' . $digits;
    }
    return $n;
}
?>
<div>
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Manage Employers</h1>
        <p class="mt-2 text-sm text-gray-600">View and manage all employer accounts</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" 
                   placeholder="Search by company or email" 
                   class="px-4 py-2 border border-gray-300 rounded-md">
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-md">
                <option value="all" <?= ($filters['status'] ?? 'all') === 'all' ? 'selected' : '' ?>>All Status</option>
                <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="blocked" <?= ($filters['status'] ?? '') === 'blocked' ? 'selected' : '' ?>>Blocked</option>
            </select>
            <select name="kyc_status" class="px-4 py-2 border border-gray-300 rounded-md">
                <option value="all" <?= ($filters['kyc_status'] ?? 'all') === 'all' ? 'selected' : '' ?>>All KYC Status</option>
                <option value="pending" <?= ($filters['kyc_status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="approved" <?= ($filters['kyc_status'] ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                <option value="rejected" <?= ($filters['kyc_status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Filter</button>
        </form>
    </div>

    <!-- Employers Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Company</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Country</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jobs</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">KYC Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Featured</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($employers as $employer): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            <?= htmlspecialchars($employer['company_name'] ?? 'N/A') ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= htmlspecialchars($employer['email'] ?? '') ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?= htmlspecialchars(__fmt_phone($employer['phone'] ?? '', $employer['country'] ?? '')) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php $iso = __iso($employer['country'] ?? ''); ?>
                        <span class="inline-flex items-center gap-2 px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                            <?php if ($iso): ?>
                                <img src="https://flagcdn.com/24x18/<?= $iso ?>.png" width="24" height="18" alt="<?= htmlspecialchars($employer['country'] ?? '') ?>">
                            <?php endif; ?>
                            <?= htmlspecialchars($employer['country'] ?? 'N/A') ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= number_format($employer['jobs_count'] ?? 0) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            <?= ($employer['kyc_status'] ?? '') === 'approved' ? 'bg-green-100 text-green-800' : 
                                (($employer['kyc_status'] ?? '') === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                            <?= ucfirst($employer['kyc_status'] ?? 'pending') ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= ($employer['user_status'] ?? '') === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                            <?= ucfirst($employer['user_status'] ?? 'unknown') ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                        <?php $co = $companies[$employer['id']] ?? null; ?>
                        <form method="POST" action="/admin/employers/<?= (int)$employer['id'] ?>/feature" class="flex items-center gap-2">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="is_featured" value="1" <?= !empty($co['is_featured']) ? 'checked' : '' ?> class="rounded border-gray-300">
                                <span>Featured</span>
                            </label>
                            <input type="number" name="featured_order" value="<?= (int)($co['featured_order'] ?? 0) ?>" min="0" class="w-20 px-2 py-1 border border-gray-300 rounded" title="Order">
                            <button type="submit" class="px-3 py-1.5 bg-gray-800 text-white rounded hover:bg-gray-900">Save</button>
                        </form>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="/admin/employers/<?= $employer['id'] ?>" class="text-blue-600 hover:text-blue-900">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($pagination['totalPages'] > 1): ?>
    <div class="mt-6 flex items-center justify-between">
        <div class="text-sm text-gray-700">
            Showing <?= (($pagination['page'] - 1) * $pagination['perPage']) + 1 ?> to <?= min($pagination['page'] * $pagination['perPage'], $pagination['total']) ?> of <?= $pagination['total'] ?> results
        </div>
        <div class="flex space-x-2">
            <?php if ($pagination['page'] > 1): ?>
                <a href="?page=<?= $pagination['page'] - 1 ?>&search=<?= urlencode($filters['search'] ?? '') ?>&status=<?= urlencode($filters['status'] ?? 'all') ?>&kyc_status=<?= urlencode($filters['kyc_status'] ?? 'all') ?>" 
                   class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">Previous</a>
            <?php endif; ?>
            <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                <a href="?page=<?= $pagination['page'] + 1 ?>&search=<?= urlencode($filters['search'] ?? '') ?>&status=<?= urlencode($filters['status'] ?? 'all') ?>&kyc_status=<?= urlencode($filters['kyc_status'] ?? 'all') ?>" 
                   class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">Next</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

