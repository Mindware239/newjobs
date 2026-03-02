<?php
$job = $job ?? [];

$title      = $job['role_name'] ?? '';
$company    = $job['organization_name'] ?? '';
$logo       = $job['logo'] ?? '';
$logoFinal  = $logo !== '' ? $logo : ('https://ui-avatars.com/api/?name=' . urlencode($company) . '&background=ffffff&color=54595f');
$location   = $job['job_location'] ?? ($job['location_details'] ?? '');
$type       = $job['time_commitment'] ?? '';
$workplace  = $job['workplace_option'] ?? '';
$minPay     = $job['min_pay'] ?? '';
$maxPay     = $job['max_pay'] ?? '';
$payType    = $job['pay_type'] ?? '';
$education  = $job['education_level'] ?? '';
$experience = $job['experience_years'] ?? '';
$category   = $job['work_category'] ?? '';
$focus      = $job['role_mission_focus'] ?? '';
$short      = $job['short_description'] ?? '';
$full       = $job['full_description'] ?? '';
$website    = $job['website'] ?? '';
$applyMethod = $job['apply_method'] ?? 'email';
$notificationEmail = $job['notification_emails'] ?? '';

$posted  = !empty($job['created_at']) ? date('F d, Y', strtotime($job['created_at'])) : '';
$expires = !empty($job['publish_date']) ? date('F d, Y', strtotime($job['publish_date'])) : '';
$scheme = $_SERVER['REQUEST_SCHEME'] ?? ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base = isset($base) && is_string($base) ? $base : ($scheme . '://' . $host . '/');
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($title ?: 'Job Details') ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 text-gray-900 overflow-x-hidden">

<!-- ================= PUBLIC HEADER ================= -->

<header class="w-full bg-white border-b border-gray-100 shadow-sm sticky top-0 z-50">
    <div class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12 h-20 md:h-24 flex items-center justify-between">
        <div class="flex-shrink-0">
            <a href="<?php echo $base; ?>">
                <img src="<?php echo $base; ?>uploads/Mindware-infotech.png" alt="Logo" class="h-9 sm:h-11 md:h-14 lg:h-16 w-auto">
            </a>
        </div>

        <div class="hidden min-[900px]:flex items-center gap-8">
            <div class="text-[14px]">
                <span class="text-gray-400 font-medium mr-2">Employers:</span>
                <a href="login" class="text-red-400 font-semibold hover:underline">Login</a>
                <span class="mx-1 text-gray-300">|</span>
                <a href="#" class="text-red-400 font-semibold hover:underline">Create account</a>
            </div>
            <a href="candidate" 
               class="bg-red-400 text-white px-7 py-3 rounded-[4px] text-[13px] font-bold tracking-wider uppercase hover:bg-white hover:text-red-400  border-2 hover:border-red-400">
                Jobseekers
            </a>
        </div>

        <button @click="mobileMenuOpen = !mobileMenuOpen" class="min-[900px]:hidden p-2 text-[#333]">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
        </button>
    </div>

    <nav class="hidden min-[900px]:block border-t border-gray-50">
        <div class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12 flex gap-10">
            <?php 
                $current_page = basename($_SERVER['PHP_SELF'], ".php");
                $navItems = [
                    ['label' => 'Home', 'url' => 'index'],
                    ['label' => 'Pricing', 'url' => 'pricing'],
                    ['label' => 'Hiring Insight', 'url' => 'hiringInsight'],
                    ['label' => 'About Us', 'url' => 'aboutus'],
                    ['label' => 'Support', 'url' => 'supports'],
                    ['label' => 'Specials', 'url' => 'specials'],
                ];

                foreach($navItems as $item): 
                    $isActive = ($current_page == $item['url']);
                    $class = $isActive ? 'text-[#e15f55]' : 'text-black hover:text-[#e15f55]';
            ?>
                <a href="<?php echo $base . '/' . $item['url']; ?>" 
                   class="relative py-4 text-[15px] font-semibold transition-colors duration-300 <?php echo $class; ?>">
                    <?php echo $item['label']; ?>
                    <?php if($isActive): ?>
                        <span class="absolute bottom-0 left-0 w-full h-0.5 bg-[#e15f55]"></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </nav>

    <div x-show="mobileMenuOpen" x-cloak class="min-[900px]:hidden bg-gray-50 px-4 sm:px-6 shadow-inner">
        <ul class="py-4 space-y-1">
            <?php foreach($navItems as $item): ?>
            <li>
                <a href="<?= $item['url'] ?>" class="block py-3 text-[15px] font-medium border-b border-gray-100">
                    <?= $item['label'] ?>
                </a>
            </li>
            <?php endforeach; ?>
            <li class="pt-4 flex flex-col gap-3 pb-6">
                <a href="candidate" class="w-full py-3 bg-[#5b6bd5] text-white text-center font-bold rounded">JOBSEEKERS</a>
                <div class="text-center text-sm py-2">
                    Employers: <a href="employers" class="text-[#5b6bd5] font-bold">Login</a>
                </div>
            </li>
        </ul>
    </div>
</header>
<!-- ================= MAIN CONTENT ================= -->
<div class="max-w-6xl mx-auto px-6 py-10">

<a href="/find-a-job" class="text-red-500 text-sm font-semibold">
← Back
</a>

<div class="mt-6 grid grid-cols-3 gap-8">

<!-- ================= LEFT COLUMN ================= -->
<div class="col-span-2 bg-white border rounded-lg p-8">

<h1 class="text-3xl font-bold break-words">
<?= htmlspecialchars($title) ?>
</h1>

<p class="text-lg mt-2 font-semibold">
<?= htmlspecialchars($company) ?>
</p>

<div class="text-sm text-gray-600 mt-3">
<span><?= htmlspecialchars($location) ?></span>
<?php if($type): ?> • <span><?= htmlspecialchars($type) ?></span><?php endif; ?>
<?php if($workplace): ?> • <span><?= htmlspecialchars($workplace) ?></span><?php endif; ?>
</div>

<div class="text-sm text-red-500 mt-2">
<?php if($posted): ?> Posted: <?= $posted ?><?php endif; ?>
<?php if($expires): ?> <span class="ml-4">Expires: <?= $expires ?></span><?php endif; ?>
</div>

<hr class="my-6">

<div class="space-y-4 text-sm">

<?php if($minPay || $maxPay): ?>
<div>
<strong>Compensation:</strong>
$<?= htmlspecialchars($minPay) ?>
<?php if($maxPay): ?> - $<?= htmlspecialchars($maxPay) ?><?php endif; ?>
<?php if($payType == 'hourly') echo " per hour"; ?>
<?php if($payType == 'salary') echo " per year"; ?>
<?php if($payType == 'contract') echo " contract"; ?>
</div>
<?php endif; ?>

<?php if($education): ?>
<div><strong>Minimum education requirement:</strong> <?= htmlspecialchars($education) ?></div>
<?php endif; ?>

<?php if($experience): ?>
<div><strong>Minimum experience requirement:</strong> <?= htmlspecialchars($experience) ?> years</div>
<?php endif; ?>

<?php if($category): ?>
<div><strong>Role categories:</strong> <?= htmlspecialchars($category) ?></div>
<?php endif; ?>

<?php if($focus): ?>
<div><strong>Mission focus areas:</strong> <?= htmlspecialchars($focus) ?></div>
<?php endif; ?>

</div>

<hr class="my-6">

<?php if($short): ?>
<p class="mb-6 text-gray-800"><?= nl2br(htmlspecialchars($short)) ?></p>
<?php endif; ?>

<?php if($full): ?>
<div class="prose max-w-none break-words text-gray-800">
<?= nl2br(htmlspecialchars($full)) ?>
</div>
<?php endif; ?>

<hr class="my-8">

<h3 class="font-semibold text-lg mb-2">Company Info</h3>

<?php if($website): ?>
<div class="text-sm mb-2">
<strong>Website:</strong>
<a href="<?= htmlspecialchars($website) ?>" target="_blank" class="text-red-500 underline">
<?= htmlspecialchars($website) ?>
</a>
</div>
<?php endif; ?>

<div class="text-sm">
<strong>Location:</strong> <?= htmlspecialchars($location) ?>
</div>

</div>

<!-- ================= RIGHT SIDEBAR ================= -->
<div class="col-span-1 space-y-6">

<div class="bg-white border p-6 rounded-lg">
    <div class="w-full h-24 flex items-center justify-center">
        <img src="<?= htmlspecialchars($logoFinal) ?>" alt="<?= htmlspecialchars($company) ?>" class="max-w-full max-h-full object-contain"
             onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=<?= urlencode($company) ?>&background=ffffff&color=54595f'">
    </div>
    <div class="mt-3 text-center text-sm font-semibold text-gray-800">
        <?= htmlspecialchars($company) ?>
    </div>
</div>

<div class="bg-red-500 text-white p-6 rounded-lg">

<?php if($applyMethod == 'website'): ?>
<a href="<?= htmlspecialchars($website) ?>" target="_blank"
class="bg-white text-red-500 px-4 py-2 rounded font-semibold block text-center">
Start Application
</a>
<?php else: ?>
<a id="apply-btn" href="<?= rtrim($base,'/') ?>/social-candidate/candidate?job_id=<?= htmlspecialchars($job['id'] ?? 0) ?>"
   class="bg-red-500 text-white px-4 py-2 rounded font-semibold block text-center hover:bg-white hover:text-red-500 border border-red-500 transition">
   Apply Now
</a>
<?php endif; ?>

</div>

<div class="bg-white border p-6 rounded-lg">
<h3 class="font-semibold mb-3">Share this job</h3>
<div class="flex gap-4 text-red-500 text-sm">
<span>Facebook</span>
<span>LinkedIn</span>
<span>Email</span>
</div>
</div>

</div>

</div>
</div>

</body>
<script>
(function(){
  var meta = {
    content_type: 'job',
    content_ids: [<?= json_encode((int)($job['id'] ?? 0)) ?>],
    content_name: <?= json_encode((string)($job['role_name'] ?? '')) ?>,
    content_category: <?= json_encode((string)($job['work_category'] ?? '')) ?>,
    location: <?= json_encode((string)($job['job_location'] ?? ($job['location_details'] ?? ''))) ?>,
    employment_type: <?= json_encode((string)($job['time_commitment'] ?? '')) ?>,
    workplace: <?= json_encode((string)($job['workplace_option'] ?? '')) ?>,
    company_name: <?= json_encode((string)($job['organization_name'] ?? '')) ?>,
    value: 0,
    currency: 'INR'
  };
  document.addEventListener('DOMContentLoaded',function(){
    try{ if(window.MWMarketing){ window.MWMarketing.trackJobView(meta); } }catch(_){}
    var b=document.getElementById('apply-btn');
    if(b){
      b.addEventListener('click',function(){
        try{
          if(window.MWMarketing){
            window.MWMarketing.trackApply({
              content_type: 'job',
              job_id: <?= json_encode((int)($job['id'] ?? 0)) ?>,
              job_title: <?= json_encode((string)($job['role_name'] ?? '')) ?>,
              content_category: <?= json_encode((string)($job['work_category'] ?? '')) ?>,
              location: <?= json_encode((string)($job['job_location'] ?? ($job['location_details'] ?? ''))) ?>,
              value: 0,
              currency: 'INR'
            });
          }
        }catch(_){}
      });
    }
  });
})();
</script>
</html>
