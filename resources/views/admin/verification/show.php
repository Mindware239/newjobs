<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ---------- SAFE HELPERS ---------- */
function e($v)
{
    return htmlspecialchars($v ?? '');
}

function val($arr, $key, $def = '')
{
    return $arr[$key] ?? $def;
}

/* ---------- SAFE DATA ---------- */
$record = $record ?? [];
$documents = $documents ?? [];
$request = $request ?? [];
$response = $response ?? [];
$logs = $logs ?? [];
$csrf = $_SESSION['csrf_token'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verification Detail</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="max-w-screen-2xl mx-auto px-6 py-6">

    <!-- BACK -->
    <div class="mb-6">
        <a href="/admin/verification" class="text-sm text-blue-600 hover:underline">&larr; Back to Verifications</a>
    </div>

    <!-- HEADER -->
    <div class="bg-white shadow rounded-xl p-6 mb-6">

        <?php if (!empty($_GET['msg'])): ?>
            <div class="mb-4 p-3 rounded border text-sm
<?php echo $_GET['msg'] == 'verified'
                    ? 'bg-green-50 border-green-200 text-green-800'
                    : ($_GET['msg'] == 'not_verified'
                            ? 'bg-red-50 border-red-200 text-red-800'
                            : 'bg-blue-50 border-blue-200 text-blue-800') ?>">
                <?php echo e($_GET['msg']) ?>
            </div>
        <?php endif; ?>

        <div class="h-1 w-full bg-gradient-to-r from-purple-500 via-purple-400 to-pink-400 rounded"></div>

        <div class="flex flex-col lg:flex-row justify-between mt-4 gap-4">

            <!-- PROFILE -->
            <div class="flex items-center gap-3">
                <?php
                $name = trim(val($record, 'full_name'));
                $parts = explode(' ', $name);
                $initials = strtoupper(($parts[0][0] ?? '') . ($parts[count($parts) - 1][0] ?? ''));
                ?>
                <div class="w-11 h-11 rounded-full bg-purple-600 text-white flex items-center justify-center font-semibold shadow">
                    <?php echo $initials ?: 'NA' ?>
                </div>

                <div>
                    <h1 class="text-xl font-semibold"><?php echo e($name) ?></h1>
                    <p class="text-sm text-gray-600">
                        <?php echo e(val($record, 'company_name')) ?> ·
                        <?php echo e(val($record, 'designation')) ?> ·
                        <?php echo e(val($record, 'start_date')) ?> to
                        <?php echo e(val($record, 'end_date') ?: 'Present') ?>
                    </p>
                </div>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="flex gap-2">

                <form method="post" action="/admin/verification/<?php echo (int)val($record, 'id') ?>/approve">
                    <input type="hidden" name="_token" value="<?php echo e($csrf) ?>">
                    <button class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-semibold shadow">
                        Mark Verified
                    </button>
                </form>

                <form method="post" action="/admin/verification/<?php echo (int)val($record, 'id') ?>/reject">
                    <input type="hidden" name="_token" value="<?php echo e($csrf) ?>">
                    <button class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-semibold shadow">
                        Mark Not Verified
                    </button>
                </form>

            </div>
        </div>
    </div>

    <!-- MAIN GRID -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">

        <!-- LEFT -->
        <div class="xl:col-span-8 space-y-6">

            <!-- EMPLOYMENT -->
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="font-semibold mb-4">Employment Details</h2>

                <div class="grid md:grid-cols-3 gap-4">

                    <?php
                    function box($label, $value)
                    {
                        echo "<div class='bg-gray-50 border rounded-lg p-4'><div class='text-xs text-gray-500'>$label</div>
                          <div class='text-sm font-medium text-gray-900'>$value</div></div>";
                    }

                    box('Company', e(val($record, 'company_name')));
                    box('Designation', e(val($record, 'designation')));
                    box('Employment Type', e(val($record, 'employment_type', 'Full-time')));
                    box('From', e(val($record, 'start_date')));
                    box('To', e(val($record, 'end_date') ?: 'Currently Working'));

                    $status = val($record, 'status_overall');
                    ?>

                    <div class="bg-gray-50 border rounded-lg p-4">
                        <div class="text-xs text-gray-500 mb-1">Status</div>

                        <?php if ($status == 'verified'): ?>
                            <span class="text-xs px-2 py-1 bg-green-100 text-green-700 rounded-full">Verified</span>
                        <?php elseif ($status == 'not_verified'): ?>
                            <span class="text-xs px-2 py-1 bg-red-100 text-red-700 rounded-full">Not Verified</span>
                        <?php else: ?>
                            <span class="text-xs px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full">Under Review</span>
                        <?php endif; ?>

                    </div>

                </div>
            </div>


            <!-- DOCUMENTS -->
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="font-semibold mb-6">Documents</h2>

                <?php
                $types = [
                        'offer_letter' => 'Offer Letter',
                        'relieving_letter' => 'Relieving Letter',
                        'experience_letter' => 'Experience Letter',
                        'salary_slip' => 'Salary Slip',
                        'salary_slips' => 'Salary Slip',
                        'bank_statement' => 'Bank Statement',
                        'form16' => 'Form 16',
                ];
                $grouped = [];
                foreach ($documents as $d) {
                    $grouped[$d['doc_type']][] = $d;
                }
                ?>

                <div class="grid md:grid-cols-2 gap-5">

                    <?php
                    foreach ($types as $key => $label):
                        $files = $grouped[$key] ?? [];
                        $has = !empty($files);
                        ?>

                        <div class="rounded-xl border-2 border-dashed p-4
                       <?php
                        echo $has ? 'border-indigo-100 bg-white hover:shadow-md' : 'border-gray-200 bg-gray-50/60' ?>">

                            <div class="flex justify-between mb-2">
                                <p class="font-semibold text-sm">
                                    <?php echo $label ?>
                                </p>

                                <span class="text-[10px] px-2.5 py-0.5 rounded-full border
                                   <?php
                                echo $has ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-gray-50 text-gray-500 border-gray-200' ?>">
                                  <?php
                                  echo $has ? 'Uploaded' : 'Not Uploaded'
                                  ?>
                                </span>
                            </div>

                            <?php
                            if ($has):
                                foreach ($files as $f): ?>

                                    <div class="space-y-1.5 mb-3">

                                        <div class="flex items-center gap-2 bg-indigo-50 rounded-lg p-2">
                                           <span class="text-xs text-indigo-700 truncate flex-1">
                                              <?php
                                              echo e(basename($f['file_path']))
                                              ?>
                                           </span>
                                        </div>

                                        <div class="flex justify-between text-[11px] text-gray-400">
                                            <span><?php echo round(($f['size_bytes'] ?? 0) / 1024) ?> KB</span>
                                            <span><?php echo !empty($f['uploaded_at']) ? date('Y-m-d', strtotime($f['uploaded_at'])) : '' ?></span>
                                        </div>

                                        <div class="flex gap-2">
                                            <a href="<?php echo e($f['file_path']) ?>" target="_blank"
                                               class="px-3 h-7 text-xs border-2 border-indigo-200 text-indigo-600 rounded-md flex items-center hover:bg-indigo-600 hover:text-white transition">
                                                Preview
                                            </a>

                                            <a href="<?php echo e($f['file_path']) ?>" download
                                               class="px-3 h-7 text-xs border-2 border-gray-200 text-gray-600 rounded-md flex items-center hover:bg-gray-100 transition">
                                                Download
                                            </a>
                                        </div>

                                    </div>

                                <?php endforeach;
                            else: ?>

                                <p class="text-xs text-gray-400 italic">Not uploaded by candidate</p>

                            <?php endif; ?>

                        </div>

                    <?php endforeach; ?>

                </div>

                <?php
                $knownKeys = array_keys($types);
                $unknown = array_values(array_filter(array_keys($grouped), fn($k) => !in_array($k, $knownKeys)));
                ?>

                <?php if (!empty($unknown)): ?>
                <div class="mt-6">
                    <h3 class="font-semibold mb-3">Other Documents</h3>
                    <div class="grid md:grid-cols-2 gap-5">
                        <?php foreach ($unknown as $ukey): $files = $grouped[$ukey] ?? [];
                            $has = !empty($files); ?>
                            <div class="rounded-xl border-2 border-dashed p-4 <?php echo $has ? 'border-indigo-100 bg-white hover:shadow-md' : 'border-gray-200 bg-gray-50/60' ?>">
                                <div class="flex justify-between mb-2">
                                    <p class="font-semibold text-sm"><?php echo e(ucwords(str_replace('_', ' ', (string)$ukey))) ?></p>
                                    <span class="text-[10px] px-2.5 py-0.5 rounded-full border <?php echo $has ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-gray-50 text-gray-500 border-gray-200' ?>"><?php echo $has ? 'Uploaded' : 'Not Uploaded' ?></span>
                                </div>
                                <?php if ($has): foreach ($files as $f): ?>
                                    <div class="space-y-1.5 mb-3">
                                        <div class="flex items-center gap-2 bg-indigo-50 rounded-lg p-2">
                                            <span class="text-xs text-indigo-700 truncate flex-1"><?php echo e(basename($f['file_path'])) ?></span>
                                        </div>
                                        <div class="flex justify-between text-[11px] text-gray-400">
                                            <span><?php echo round(($f['size_bytes'] ?? 0) / 1024) ?> KB</span>
                                            <span><?php echo !empty($f['uploaded_at']) ? date('Y-m-d', strtotime($f['uploaded_at'])) : '' ?></span>
                                        </div>
                                        <div class="flex gap-2">
                                            <a href="<?php echo e($f['file_path']) ?>" target="_blank"
                                               class="px-3 h-7 text-xs border-2 border-indigo-200 text-indigo-600 rounded-md flex items-center hover:bg-indigo-600 hover:text-white transition">Preview</a>
                                            <a href="<?php echo e($f['file_path']) ?>" download
                                               class="px-3 h-7 text-xs border-2 border-gray-200 text-gray-600 rounded-md flex items-center hover:bg-gray-100 transition">Download</a>
                                        </div>
                                    </div>
                                <?php endforeach; else: ?>
                                    <p class="text-xs text-gray-400 italic">Not uploaded by candidate</p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <div class="rounded-lg bg-card text-card-foreground border-0 shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6 pb-3">
                <h3 class="tracking-tight text-base font-semibold flex items-center gap-2 text-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         class="lucide lucide-mail h-5 w-5 text-indigo-500">
                        <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                    </svg>
                    HR Response
                </h3>
            </div>
            <div class="p-6 pt-0">
                <div class="space-y-4">
                    <div class="bg-gradient-to-br from-emerald-50 to-green-50 rounded-xl p-5 border border-emerald-100">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="h-10 w-10 rounded-full bg-emerald-100 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round" class="lucide lucide-user h-5 w-5 text-emerald-600">
                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900"><?php echo e(!empty($response) ? val($response, 'hr_name', 'HR Team') : 'HR Team') ?></p>
                                <p class="text-xs text-gray-500">Senior HR Executive
                                    · <?php echo e(val($request, 'hr_email')) ?></p>
                            </div>
                            <div class="ml-auto text-right">
                                <p class="text-xs text-gray-500"><?php echo e(!empty($response) ? substr(val($response, 'responded_at', ''), 0, 10) : '') ?></p>
                                <div class="flex gap-0.5 mt-1 justify-end">
                                    <div class="h-2 w-5 rounded-sm bg-amber-400"></div>
                                    <div class="h-2 w-5 rounded-sm bg-amber-400"></div>
                                    <div class="h-2 w-5 rounded-sm bg-amber-400"></div>
                                    <div class="h-2 w-5 rounded-sm bg-amber-400"></div>
                                    <div class="h-2 w-5 rounded-sm bg-amber-400"></div>
                                </div>
                            </div>
                        </div>
                        <p class="text-sm text-gray-700 leading-relaxed bg-white/60 rounded-lg p-3">
                            "<?php echo e(!empty($response) ? val($response, 'remarks') : 'No response received yet.') ?>
                            "</p>
                        <div class="mt-3 flex items-center gap-2">
                            <?php
                            $st = strtolower(trim((string)(!empty($response) ? val($response, 'status', '') : '')));
                            $badgeCls = 'bg-gray-100 text-gray-700 border-gray-200';
                            $badgeText = 'Pending';
                            if ($st === 'verified' || $st === 'confirmed') {
                                $badgeCls = 'bg-green-100 text-green-700 border-green-200';
                                $badgeText = '✓ Employment Verified';
                            } elseif ($st === 'not_verified' || $st === 'denied') {
                                $badgeCls = 'bg-red-100 text-red-700 border-red-200';
                                $badgeText = '✗ Employment Denied';
                            }
                            ?>
                            <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 font-semibold text-xs <?php echo $badgeCls ?>"><?php echo $badgeText ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- RIGHT SIDEBAR -->
    <div class="xl:col-span-4 space-y-6 xl:sticky xl:top-6">

        <!-- INFO -->
        <div class="bg-white shadow rounded-xl p-6">
            <div class="flex flex-col space-y-1.5 p-6 pb-3">
                <h3 class="tracking-tight text-base font-semibold flex items-center gap-2 text-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         class="lucide lucide-building2 h-5 w-5 text-indigo-500">
                        <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"></path>
                        <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"></path>
                        <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"></path>
                        <path d="M10 6h4"></path>
                        <path d="M10 10h4"></path>
                        <path d="M10 14h4"></path>
                        <path d="M10 18h4"></path>
                    </svg>
                    Verification Info
                </h3>
            </div>
            <div class="space-y-3 text-sm">
                <!-- demo here -->
                <div class="p-6 pt-0 space-y-3">
                    <div class="flex items-start gap-3 p-2.5 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                             stroke-linejoin="round"
                             class="lucide lucide-mail h-4 w-4 text-gray-400 mt-0.5 flex-shrink-0">
                            <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                        </svg>
                        <div class="min-w-0">
                            <p class="text-[11px] text-gray-400 uppercase tracking-wider">HR Email</p>
                            <p class="text-sm font-medium text-gray-800 break-all">sales@indianbarcode.com</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-2.5 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                             stroke-linejoin="round"
                             class="lucide lucide-phone h-4 w-4 text-gray-400 mt-0.5 flex-shrink-0">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                        <div class="min-w-0">
                            <p class="text-[11px] text-gray-400 uppercase tracking-wider">HR Phone</p>
                            <p class="text-sm font-medium text-gray-800 break-all">+91-9696969696</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-2.5 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                             stroke-linejoin="round"
                             class="lucide lucide-mail h-4 w-4 text-gray-400 mt-0.5 flex-shrink-0">
                            <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                        </svg>
                        <div class="min-w-0">
                            <p class="text-[11px] text-gray-400 uppercase tracking-wider">Reporting Manager</p>
                            <p class="text-sm font-medium text-gray-800 break-all">gm@indianbarcode.com</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-2.5 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                             stroke-linejoin="round"
                             class="lucide lucide-globe h-4 w-4 text-gray-400 mt-0.5 flex-shrink-0">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path>
                            <path d="M2 12h20"></path>
                        </svg>
                        <div class="min-w-0"><p class="text-[11px] text-gray-400 uppercase tracking-wider">Company
                                Website</p>
                            <p class="text-sm font-medium text-gray-800 break-all">www.indianbarcode.com</p></div>
                    </div>
                    <div class="flex items-start gap-3 p-2.5 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                             stroke-linejoin="round"
                             class="lucide lucide-file-text h-4 w-4 text-gray-400 mt-0.5 flex-shrink-0">
                            <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                            <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                            <path d="M10 9H8"></path>
                            <path d="M16 13H8"></path>
                            <path d="M16 17H8"></path>
                        </svg>
                        <div class="min-w-0"><p class="text-[11px] text-gray-400 uppercase tracking-wider">CIN</p>
                            <p class="text-sm font-medium text-gray-800 break-all">GJ000000000000000</p></div>
                    </div>
                    <div class="flex items-start gap-3 p-2.5 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                             stroke-linejoin="round"
                             class="lucide lucide-file-text h-4 w-4 text-gray-400 mt-0.5 flex-shrink-0">
                            <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                            <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                            <path d="M10 9H8"></path>
                            <path d="M16 13H8"></path>
                            <path d="M16 17H8"></path>
                        </svg>
                        <div class="min-w-0"><p class="text-[11px] text-gray-400 uppercase tracking-wider">GST</p>
                            <p class="text-sm font-medium text-gray-800 break-all">27AAACF00000000</p></div>
                    </div>
                </div>

            </div>
        </div>

        <!-- HR Email Details -->
        <div class="bg-white shadow rounded-xl p-6 mt-4">
            <h3 class="tracking-tight text-base font-semibold flex items-center gap-2 text-gray-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor">
                    <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                </svg>
                HR Email & Token
            </h3>
            <div class="mt-3 text-sm space-y-1">
                <p><span class="text-gray-500">HR Email:</span> <span
                            class="font-medium"><?php echo e(val($request, 'hr_email')) ?></span></p>
                <p><span class="text-gray-500">Status:</span> <span
                            class="font-medium"><?php echo e(val($request, 'status')) ?></span></p>
                <p><span class="text-gray-500">Token:</span> <span
                            class="font-mono"><?php echo e(substr(val($request, 'token', ''), 0, 8)) ?>…</span></p>
                <p>
                    <a href="<?php echo e(val($request, 'token') ? ('/hr/verify?token=' . urlencode(val($request, 'token'))) : '#') ?>"
                       target="_blank" class="text-indigo-600 hover:text-indigo-800">Open Secure Link</a></p>
            </div>
            <div class="mt-3">
                <form method="post" action="/admin/verification/<?php echo (int)val($record, 'id') ?>/resend">
                    <input type="hidden" name="_token" value="<?php echo e($csrf) ?>">
                    <button class="px-3 py-1.5 text-xs bg-indigo-600 hover:bg-indigo-700 text-white rounded">Resend
                        to HR
                    </button>
                </form>
            </div>
            <?php if (!empty($email_log)): ?>
                <div class="mt-4 border rounded-lg">
                    <div class="px-3 py-2 text-xs bg-gray-50 border-b text-gray-500">Last Email Preview</div>
                    <div class="p-3">
                        <div class="text-sm font-medium mb-2"><?php echo e($email_log['subject'] ?? '') ?></div>
                        <div class="text-xs text-gray-600 leading-relaxed">
                            <?php
                            $cnt = (string)($email_log['content'] ?? '');
                            $short = strlen($cnt) > 600 ? substr($cnt, 0, 600) . '…' : $cnt;
                            echo $short;
                            ?>
                        </div>
                        <div class="mt-2 text-[11px] text-gray-500">
                            Status: <?php echo e($email_log['status'] ?? '') ?>
                            · <?php echo e($email_log['created_at'] ?? '') ?></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- LOGS -->
        <div class="bg-white shadow rounded-xl p-6">
            <h3 class="tracking-tight text-base font-semibold flex items-center gap-2 text-gray-900">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="lucide lucide-clock h-5 w-5 text-indigo-500">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                Activity Logs
            </h3>
            <div class="p-6 pt-0">
                <div class="space-y-0">
                    <div class="flex gap-3 relative">
                        <div class="absolute left-[11px] top-7 bottom-0 w-0.5 bg-gray-100">
                        </div>
                        <div class="h-6 w-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 bg-gray-100">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" class="lucide lucide-user h-3 w-3 text-gray-600">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        <div class="pb-4"><p class="text-sm font-medium text-gray-900">Documents Uploaded</p>
                            <p class="text-xs text-gray-500">3 documents uploaded</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">2024-12-10 14:30 · Candidate</p>
                        </div>
                    </div>
                    <div class="flex gap-3 relative">
                        <div class="absolute left-[11px] top-7 bottom-0 w-0.5 bg-gray-100">
                        </div>
                        <div class="h-6 w-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 bg-gray-100">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" class="lucide lucide-user h-3 w-3 text-gray-600">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        <div class="pb-4"><p class="text-sm font-medium text-gray-900">HR Email Added</p>
                            <p class="text-xs text-gray-500">sales@indianbarcode.com</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">2024-12-10 14:35 · Candidate</p>
                        </div>
                    </div>
                    <div class="flex gap-3 relative">
                        <div class="absolute left-[11px] top-7 bottom-0 w-0.5 bg-gray-100">
                        </div>
                        <div class="h-6 w-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 bg-blue-100">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" class="lucide lucide-refresh-cw h-3 w-3 text-blue-600">
                                <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                                <path d="M21 3v5h-5"></path>
                                <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                                <path d="M8 16H3v5"></path>
                            </svg>
                        </div>
                        <div class="pb-4">
                            <p class="text-sm font-medium text-gray-900">Verification Email Sent</p>
                            <p class="text-xs text-gray-500">Sent to HR email</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">2024-12-15 10:00 · System</p>
                        </div>
                    </div>
                    <div class="flex gap-3 relative">
                        <div class="h-6 w-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 bg-emerald-100">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" class="lucide lucide-mail h-3 w-3 text-emerald-600">
                                <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                            </svg>
                        </div>
                        <div class="pb-4"><p class="text-sm font-medium text-gray-900">HR Response Received</p>
                            <p class="text-xs text-gray-500">Employment confirmed</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">2024-12-18 16:45 · HR</p>
                        </div>
                    </div>
                </div>
            </div><?php foreach ($logs as $log): ?>
                <li class="text-gray-700"><?php echo e($log['event']) ?> · <span
                            class="text-gray-400"><?php echo e($log['created_at']) ?></span></li>
            <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
</body>
</html>
