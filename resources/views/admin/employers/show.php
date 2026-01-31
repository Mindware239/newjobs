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
        <a href="/admin/employers" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">← Back to Employers</a>
        <h1 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($employer['company_name'] ?? 'Unknown Company') ?></h1>
        <div class="mt-2 flex flex-wrap gap-2">
            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                Last Login: <?= !empty($employer['last_login']) ? date('M d, Y H:i', strtotime($employer['last_login'])) : 'Never' ?>
            </span>
            <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">
                Plan: <?= htmlspecialchars(($subscription['plan_name'] ?? 'N/A')) ?>
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Company Information</h2>
                <dl class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($employer['email'] ?? 'N/A') ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars(__fmt_phone($employer['phone'] ?? '', $employer['country'] ?? '')) ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Website</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($employer['website'] ?? 'N/A') ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Industry</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($employer['industry'] ?? 'N/A') ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Company Size</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($employer['size'] ?? 'N/A') ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">KYC Status</dt>
                        <dd class="mt-1">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?= ($employer['kyc_status'] ?? '') === 'approved' ? 'bg-green-100 text-green-800' : 
                                    (($employer['kyc_status'] ?? '') === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                <?= ucfirst($employer['kyc_status'] ?? 'pending') ?>
                            </span>
                        </dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Location</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <?= htmlspecialchars($employer['city'] ?? '') ?>
                            <?= !empty($employer['state']) ? ', ' . htmlspecialchars($employer['state']) : '' ?>
                            <?= !empty($employer['postal_code']) ? ' ' . htmlspecialchars($employer['postal_code']) : '' ?>
                            <?= !empty($employer['country']) ? ', ' . htmlspecialchars($employer['country']) : '' ?>
                        </dd>
                        <?php $iso = __iso($employer['country'] ?? ''); ?>
                        <?php if ($iso): ?>
                            <div class="mt-2">
                                <img src="https://flagcdn.com/36x27/<?= $iso ?>.png" width="36" height="27" alt="<?= htmlspecialchars($employer['country'] ?? '') ?>" class="inline-block rounded-sm border border-gray-200">
                            </div>
                        <?php endif; ?>
                    </div>
                </dl>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Jobs Posted (<?= count($jobs) ?>)</h2>
                <div class="space-y-3">
                    <?php foreach (array_slice($jobs, 0, 10) as $job): ?>
                    <div class="border-b pb-3">
                        <a href="/admin/jobs/<?= $job['id'] ?>" class="text-blue-600 hover:text-blue-800 font-medium">
                            <?= htmlspecialchars($job['title'] ?? 'N/A') ?>
                        </a>
                        <p class="text-sm text-gray-500">Posted on <?= date('M d, Y', strtotime($job['created_at'] ?? 'now')) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if (!empty($kycDocuments)): ?>
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">KYC Documents</h2>
                <div class="space-y-4">
                    <?php foreach ($kycDocuments as $doc): ?>
                    <div class="border rounded-md p-3">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-medium mb-1"><?= htmlspecialchars(($doc['doc_type'] ?? $doc['document_type'] ?? 'N/A')) ?></p>
                                <?php if (!empty($doc['file_name'])): ?>
                                    <p class="text-xs text-gray-500 mb-1">File: <?= htmlspecialchars($doc['file_name']) ?></p>
                                <?php endif; ?>
                                <p class="text-sm">Status:
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?= ($doc['review_status'] ?? $doc['status'] ?? 'pending') === 'approved' ? 'bg-green-100 text-green-800' : 
                                            (($doc['review_status'] ?? $doc['status'] ?? 'pending') === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                        <?= ucfirst($doc['review_status'] ?? $doc['status'] ?? 'pending') ?>
                                    </span>
                                </p>
                                <?php if (!empty($doc['review_notes'])): ?>
                                    <p class="text-xs text-gray-600 mt-2">Note: <?= htmlspecialchars($doc['review_notes']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($doc['reviewed_at'])): ?>
                                    <p class="text-xs text-gray-400">Reviewed: <?= htmlspecialchars($doc['reviewed_at']) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="ml-4">
                                <?php if (!empty($doc['file_url'])): ?>
                                <a href="<?= htmlspecialchars($doc['file_url']) ?>" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View</a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <form method="POST" action="/admin/employers/<?= (int)($employer['id'] ?? 0) ?>/kyc-documents/<?= (int)($doc['id'] ?? 0) ?>/approve">
                                <input type="text" name="notes" placeholder="Add approval note (optional)" class="w-full mb-2 px-3 py-2 border rounded-md" />
                                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Approve</button>
                            </form>
                            <form method="POST" action="/admin/employers/<?= (int)($employer['id'] ?? 0) ?>/kyc-documents/<?= (int)($doc['id'] ?? 0) ?>/reject">
                                <input type="text" name="notes" placeholder="Rejection reason" class="w-full mb-2 px-3 py-2 border rounded-md" />
                                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Reject</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Actions Sidebar -->
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">KYC Actions</h3>
                <div class="space-y-2">
                    <?php if (($employer['kyc_status'] ?? '') === 'pending'): ?>
                        <form method="POST" action="/admin/employers/<?= $employer['id'] ?>/approve-kyc">
                            <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                Approve KYC
                            </button>
                        </form>
                        <form method="POST" action="/admin/employers/<?= $employer['id'] ?>/reject-kyc" class="mt-2">
                            <input type="text" name="reason" placeholder="Rejection reason" class="w-full mb-2 px-3 py-2 border rounded-md">
                            <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                Reject KYC
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Account Actions</h3>
                <div class="space-y-2">
                    <?php if (($employer['user_status'] ?? '') === 'active'): ?>
                        <form method="POST" action="/admin/employers/<?= $employer['id'] ?>/block">
                            <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                Block Employer
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="POST" action="/admin/employers/<?= $employer['id'] ?>/unblock">
                            <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                Unblock Employer
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($subscription): ?>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Subscription</h3>
                <p class="text-sm text-gray-600">Plan: <?= htmlspecialchars($subscription['plan_name'] ?? 'N/A') ?></p>
                <p class="text-sm text-gray-600">Status: <span class="font-medium"><?= ucfirst($subscription['status'] ?? 'N/A') ?></span></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

