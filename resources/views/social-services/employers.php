<?php $base = $base ?? '/'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hire Talent | Mindware Infotech</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <style>
        [x-cloak] { display: none !important; }
        .hero-soft {
            background: linear-gradient(180deg, #f9fafb 0%, #f1f5f9 50%, #ffffff 100%);
            border-bottom: 1px solid #e5e7eb;
        }
    </style>
</head>

<body class="bg-white text-[#54595f]" x-data="{ mobileMenuOpen: false }">

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
                <a href="/social-services/login" class="text-red-400 font-semibold hover:underline">Login/CreateAccount</a>
            </div>
            <a href="index" 
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
$current_page = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), "/");

if ($current_page === '') $current_page = 'index';

$navItems = [
    ['label' => 'Home', 'url' => 'employers'],
    ['label' => 'Pricing', 'url' => 'pricing'],
    ['label' => 'Hiring Insight', 'url' => 'hiringInsight'],
    ['label' => 'About Us', 'url' => 'aboutuss'],
    ['label' => 'Supports', 'url' => 'supportss'],
    ['label' => 'Specials', 'url' => 'specials'],
];

foreach($navItems as $item): 

$isActive = ($current_page === $item['url']);

$class = $isActive 
    ? 'text-[#e15f55]' 
    : 'text-black hover:text-[#e15f55]';
?>

<a href="<?= $base . $item['url']; ?>" 
   class="relative py-4 text-[15px] font-semibold transition-colors duration-300 <?= $class; ?>">

<?= $item['label']; ?>

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

<section class="relative w-full overflow-hidden bg-white">
    <div class="flex w-full min-h-px mx-auto">
        <div class="relative flex w-full min-h-px">
            <div class="flex relative w-full p-2 sm:p-6 md:p-8 flex-wrap content-start bg-[#e15f55]">
                <div class="w-full mb-0 text-center">
                    <div class="elementor-widget-container">
                        <h1 class="m-0 p-0 text-white font-sans 
                            text-[18px] sm:text-[24px] md:text-[28px] lg:text-[32px] 
                            leading-[1.3em] tracking-tight font-bold">
                           Find top talent to drive your mission.
                        </h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12 py-12">
  <div class="max-w-4xl">
    <p class="text-[18px] text-[#54595f] leading-relaxed mb-6">
      We understand the needs of mission-driven organizations. You want diverse, qualified talent passionate about making a difference, and not just a paycheck – and finding them needs to be simple, affordable, and effective.
    </p>
    <p class="text-[18px] text-[#54595f]">
      Let <strong>Mindware Infotech</strong> work for you. 
      <a href="/pricing" class="text-[#e15f55] font-semibold hover:underline">
        Post a job now!
      </a>
    </p>
  </div>
</section>

<section class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12 pb-20">
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    <div class="border-[10px] border-[#004a99] p-6 sm:p-10 flex flex-col min-h-[350px]">
      <h2 class="text-[22px] font-bold text-[#333] mb-6 leading-tight">
        Reach a diverse network of purpose-driven professionals.
      </h2>
      <ul class="list-disc pl-5 space-y-4 text-[#54595f] text-[15px]">
        <li>Hundreds of thousands of registered professionals</li>
        <li>Over 80% of active resumes have 4-plus years experience working in the nonprofit sector.</li>
      </ul>
    </div>

    <div class="border-[10px] border-[#333333] p-6 sm:p-10 flex flex-col min-h-[350px]">
      <h2 class="text-[22px] font-bold text-[#333] mb-6 leading-tight">
        Save money with one or more of our affordable listing options.
      </h2>
      <ul class="list-disc pl-5 space-y-3 text-[#54595f] text-[15px] mb-8">
        <li>$105 job listings. (<a href="pricing" class="text-[#e15f55] underline">Buy now!</a>)</li>
        <li>Save big with a multi-listing package.</li>
        <li>Stand out and maximize results with our Premium listings.</li>
        <li>$30 internships and FREE volunteer listing.</li>
      </ul>
      <a href="pricing" class="mt-auto text-center font-bold text-[#e15f55] tracking-widest text-sm hover:underline">LEARN MORE</a>
    </div>

    <div class="border-[10px] border-[#a5dbd5] p-6 sm:p-10 flex flex-col min-h-[350px] md:col-span-2 lg:col-span-1">
      <h2 class="text-[22px] font-bold text-[#333] mb-6 leading-tight">
        Stay up-to-date on all of the most recent hiring headlines.
      </h2>
      <ul class="list-disc pl-5 space-y-4 text-[#54595f] text-[15px]">
        <li>
          <a href="hiringInsightSignUp" class="text-[#e15f55] font-semibold underline">Subscribe</a> 
          to Hiring Insight, our monthly thought-leadership series, to keep your team informed.
        </li>
        <li>
          Read our latest Hiring Insight article, 
          <a href="#" class="text-[#e15f55] font-semibold underline">Effective job postings: 4 DOs and 4 DON’Ts</a>
        </li>
      </ul>
    </div>
  </div>
</section>

<div class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12 py-10">
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-[50px] items-start">
    <div class="flex flex-col">
      <h4 class="font-sans font-bold text-[#333333] text-[22px] mb-[20px] leading-[1.2em]">
        Post a job with us today, you'll be in good company.
      </h4>
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-0">
        <div class="p-2 flex justify-center items-center"><img src="https://workforgood.org/wp-content/uploads/2024/11/Care-Logo-2.png" alt="Care Logo" class="max-h-16 w-auto object-contain"></div>
        <div class="p-2 flex justify-center items-center"><img src="https://workforgood.org/wp-content/uploads/2024/11/Parkinsons-Foundation-Logo.png" alt="Parkinson's Foundation" class="max-h-16 w-auto object-contain"></div>
        <div class="p-2 flex justify-center items-center"><img src="https://workforgood.org/wp-content/uploads/2024/11/United-Way-of-Greater-Atlanta-Logo.jpg" alt="United Way" class="max-h-16 w-auto object-contain"></div>
        <div class="p-2 flex justify-center items-center"><img src="https://workforgood.org/wp-content/uploads/2024/11/The-Salvation-Army-Logo.png" alt="The Salvation Army" class="max-h-16 w-auto object-contain"></div>
        <div class="p-2 flex justify-center items-center"><img src="https://workforgood.org/wp-content/uploads/2024/11/American-Cancer-Society-Logo.png" alt="American Cancer Society" class="max-h-16 w-auto object-contain"></div>
        <div class="p-2 flex justify-center items-center"><img src="https://workforgood.org/wp-content/uploads/2024/11/American-Red-Cross-Logo.png" alt="American Red Cross" class="max-h-16 w-auto object-contain"></div>
        <div class="p-2 flex justify-center items-center"><img src="https://workforgood.org/wp-content/uploads/2024/11/Cystic-Fibrosis-Foundation-Logo.jpg" alt="Cystic Fibrosis Foundation" class="max-h-16 w-auto object-contain"></div>
        <div class="p-2 flex justify-center items-center"><img src="https://workforgood.org/wp-content/uploads/2024/11/Girl-Scouts-of-Greater-Atlanta-Logo.jpg" alt="Girl Scouts" class="max-h-16 w-auto object-contain"></div>
      </div>
    </div>

    <div class="flex flex-col">
      <h4 class="font-sans font-bold text-[#333333] text-[22px] mb-[20px] leading-[1.2em]">
        Testimonials
      </h4>
      <div class="bg-white border border-[#eeeeee] p-[30px] shadow-sm relative rounded-[3px]">
        <div class="text-[#7a7a7a] text-[16px] leading-[1.6em] font-sans">
          <p class="italic mb-[20px]">
            &#8220;My days are jam-packed and my need for qualified employees is immense. The value-added service provided by your team is greatly appreciated!&#8221;
          </p>
          <div class="mt-[15px]">
            <p class="text-[#333333] font-bold text-[18px] mb-0 leading-tight">Jinger Robins</p>
            <p class="text-[#7a7a7a] text-[13px] uppercase tracking-[1px] font-bold mt-1">Executive Director</p>
            <p class="text-[#7a7a7a] text-[15px] mt-0">SafePath Children’s Advocacy Center</p>
          </div>
        </div>
        <div class="absolute -bottom-[10px] left-[30px] w-0 h-0 border-l-[10px] border-l-transparent border-r-[10px] border-r-transparent border-t-[10px] border-t-white"></div>
      </div>
    </div>
  </div>
</div>

<footer class="w-full">
    <section class="bg-[#f2f2f2] py-[15px] border-b border-[#eeeeee]">
        <div class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12">
            <div class="text-center">
                <p class="text-[#333333] font-sans text-[15px] m-0">
                    Need help? Email 
                    <a href="mailto:gm@mindwareinfotech.com" class="text-red-500 font-bold hover:underline break-all">
                        gm@mindwareinfotech.com
                    </a>.
                </p>
            </div>
        </div>
    </section>

    <section class="bg-[#232323] py-[50px]">
        <div class="w-full max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12">
            <div class="flex flex-col items-center">
                <div class="mb-[30px]">
                    <img width="127" height="70" src="/uploads/Mindware-infotech.png" class="h-auto w-[127px] brightness-0 invert" alt="Mindware Infotech Logo">
                </div>

                <nav class="mb-[30px]">
                    <ul class="flex flex-wrap justify-center gap-x-[25px] gap-y-4">
                        <li><a href="aboutus" class="text-white text-[14px] font-medium font-sans tracking-wider hover:text-[#e15f55] transition-colors">About us</a></li>
                        <li><a href="/../contact" class="text-white text-[14px] font-medium font-sans tracking-wider hover:text-[#e15f55] transition-colors">Contact us</a></li>
                        <li><a href="hiringInsightSignUp" class="text-white text-[14px] font-medium font-sans tracking-wider hover:text-[#e15f55] transition-colors">Subscribe</a></li>
                        <li><a href="/../terms" class="text-white text-[14px] font-medium font-sans tracking-wider hover:text-[#e15f55] transition-colors">Terms & Conditions</a></li>
                        <li><a href="/../privacy" class="text-white text-[14px] font-medium font-sans tracking-wider hover:text-[#e15f55] transition-colors">Privacy policy</a></li>
                        <li><a href="supports" class="text-white text-[14px] font-medium font-sans tracking-wider hover:text-[#e15f55] transition-colors">Support</a></li>
                        <li><a href="employers" class="text-white text-[14px] font-medium font-sans tracking-wider hover:text-[#e15f55] transition-colors">Post a job</a></li>
                      </ul>
                </nav>

                <div class="flex justify-center mb-[25px]">
                    <a href="https://www.linkedin.com/company/mindwareinfotech/" target="_blank" class="bg-[#444444] hover:bg-[#0077b5] transition-all duration-300 p-3 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 fill-white" viewBox="0 0 310 310" xmlns="http://www.w3.org/2000/svg">
                            <path d="M72.16,99.73H9.927c-2.762,0-5,2.239-5,5v199.928c0,2.762,2.238,5,5,5H72.16c2.762,0,5-2.238,5-5V104.73 C77.16,101.969,74.922,99.73,72.16,99.73z"></path>
                            <path d="M41.066,0.341C18.422,0.341,0,18.743,0,41.362C0,63.991,18.422,82.4,41.066,82.4 c22.626,0,41.033-18.41,41.033-41.038C82.1,18.743,63.692,0.341,41.066,0.341z"></path>
                            <path d="M230.454,94.761c-24.995,0-43.472,10.745-54.679,22.954V104.73c0-2.761-2.238-5-5-5h-59.599 c-2.762,0-5,2.239-5,5v199.928c0,2.762,2.238,5,5,5h62.097c2.762,0,5-2.238,5-5v-98.918c0-33.333,9.054-46.319,32.29-46.319 c25.306,0,27.317,20.818,27.317,48.034v97.204c0,2.762,2.238,5,5,5H305c2.762,0,5-2.238,5-5V194.995 C310,145.43,300.549,94.761,230.454,94.761z"></path>
                        </svg>
                    </a>
                </div>

                <div class="text-[#7a7a7a] text-[13px] font-sans">
                    <p>© <?php echo date("Y"); ?> Mindware Infotech. Powered by Decent.</p>
                </div>
            </div>
        </div>
    </section>
</footer>

</body>
</html>
