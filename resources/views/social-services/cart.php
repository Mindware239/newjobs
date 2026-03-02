<?php
session_start();
// Define base URL if not set
$base = ""; 

$navItems = [
    ['label' => 'Home', 'url' => 'index'],
    ['label' => 'Pricing', 'url' => 'pricing'],
    ['label' => 'Hiring Insight', 'url' => 'hiringInsight'],
    ['label' => 'About Us', 'url' => 'aboutus'],
    ['label' => 'Support', 'url' => 'supports'],
    ['label' => 'Specials', 'url' => 'specials'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Your Cart | Mindware Infotech</title>
    <style>
        [x-cloak] { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="text-[#54595f] bg-slate-50/50 min-h-screen font-sans antialiased" x-data="{ mobileOpen: false }">

<header x-data="{ mobileMenu: false }"
        class="w-full bg-white border-b border-gray-100 shadow-sm sticky top-0 z-50">
    <div class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12 h-20 md:h-24 flex items-center justify-between">
        <div class="flex-shrink-0">
            <a href="<?php echo $base; ?>">
                <img src="<?php echo $base; ?>uploads/Mindware-infotech.png" alt="Logo" class="h-9 sm:h-11 md:h-14 lg:h-16 w-auto">
            </a>
        </div>

        <div class="hidden min-[900px]:flex items-center gap-8">
            <div class="text-[14px]">
                <span class="text-black font-medium mr-2">Candidates & Job Seekers:</span>
                <a href="candidate" class="text-red-400 font-semibold hover:underline">Login</a>
                <span class="mx-1 text-gray-300">|</span>
                <a href="#" class="text-red-400 font-semibold hover:underline">Create account</a>
            </div>
            <a href="employers" 
               class="bg-red-400 text-white px-7 py-3 rounded-[4px] text-[13px] font-bold tracking-wider uppercase hover:bg-white  hover:border-red-500 border-2 hover:text-red-400">
                Employers
            </a>
        </div>

        <button @click="mobileMenu = !mobileMenu" class="min-[900px]:hidden p-2 text-[#333]">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path x-show="!mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                <path x-show="mobileMenu" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <nav class="hidden min-[900px]:block border-t border-gray-50">
        <div class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12 flex gap-10">
            <?php 
                $current_page = basename($_SERVER['PHP_SELF'], ".php");
                $navItems = [
                    ['label' => 'Home', 'url' => 'index'],
                    ['label'=> 'Find a job','url'=> 'find-a-job'],
                    ['label'=> 'Create job alerts','url'=> 'newsubscriptions'],
                    ['label'=> 'Search Employers','url'=> 'searchEmployers'],
                    ['label' => 'Career Insight', 'url' => 'hiringInsight'],
                    ['label' => 'About Us', 'url' => 'aboutus'],
                    ['label' => 'Support', 'url' => 'supports'],
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

    <div x-show="mobileMenu" x-cloak x-transition id="mobileNav" class="min-[900px]:hidden bg-gray-50 px-4 border-t">
        <ul class="py-4 space-y-1">
            <?php foreach($navItems as $item): ?>
            <li>
                <a href="<?= $item['url'] ?>" class="block py-3 text-[15px] font-medium border-b border-gray-100 hover:text-[#e15f55]">
                    <?= $item['label'] ?>
                </a>
            </li>
            <?php endforeach; ?>
            <li class="pt-4 flex flex-col gap-3">
                <a href="candidate" class="w-full py-3 bg-[#5b6bd5] text-white text-center font-bold rounded">JOBSEEKERS</a>
                <div class="text-center text-sm py-2">
                    Employers: <a href="employers" class="text-[#5b6bd5] font-bold">Login</a>
                </div>
            </li>
        </ul>
    </div>
</header>

<div x-data="cartManager()">
    <div x-show="notification.show" x-cloak x-transition 
         class="fixed top-24 right-4 sm:right-10 z-[100] w-[calc(100%-2rem)] max-w-md">
        <div class="bg-white border-t-[3px] border-[#8fae1b] px-6 py-4 shadow-2xl flex items-center justify-between gap-4 rounded-b-lg">
            <div class="flex items-center gap-3 text-[#515151] text-[14.4px]">
                <span class="text-[#8fae1b] font-bold">✓</span>
                <p x-text="notification.message"></p>
            </div>
            <button @click="notification.show = false" class="text-xs font-bold uppercase text-gray-400 hover:text-black">Close</button>
        </div>
    </div>

    <main class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12 py-8 md:py-12">
        <section class="bg-[#c54d4d] mb-10 rounded-lg p-2 md:p-6 text-center">
            <h1 class="text-white text-[18px] sm:text-[24px] md:text-[28px] lg:text-[32px] font-semibold">Your Selection</h1>
        </section>

        <div class="flex flex-col lg:flex-row gap-8 md:gap-10">    
            <div class="lg:w-[65%] order-2 lg:order-1">
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm">
                    <div class="overflow-x-auto no-scrollbar">
                        <table class="w-full text-[14px] border-collapse min-w-[600px]">
                            <thead>
                                <tr class="text-left text-[#54595f] font-bold uppercase text-[12px] border-b bg-gray-50">
                                    <th class="p-4">Item</th>
                                    <th class="p-4">Price</th>
                                    <th class="p-4 text-center">Qty</th>
                                    <th class="p-4 text-right">Total</th>
                                    <th class="p-4 w-10"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in items" :key="index">
                                    <tr class="border-b hover:bg-gray-50 transition-colors">
                                        <td class="p-4 font-medium text-[#5b6bd5]" x-text="item.name"></td>
                                        <td class="p-4 text-[#777]" x-text="'$' + Number(item.price).toFixed(2)"></td>
                                        <td class="p-4">
                                            <div class="flex items-center border rounded w-max mx-auto overflow-hidden bg-white">
                                                <button @click="updateQty(index, -1)" class="px-3 py-1 bg-gray-50 hover:bg-gray-200">-</button>
                                                <input type="number" x-model.number="item.qty" @input="sync()" class="w-12 text-center border-none text-sm focus:ring-0">
                                                <button @click="updateQty(index, 1)" class="px-3 py-1 bg-gray-50 hover:bg-gray-200">+</button>
                                            </div>
                                        </td>
                                        <td class="p-4 text-right font-bold" x-text="'$' + (item.price * item.qty).toFixed(2)"></td>
                                        <td class="p-4 text-right">
                                            <button @click="removeItem(index)" class="text-gray-300 hover:text-[#e15f55] text-2xl transition-colors">&times;</button>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="items.length === 0">
                                    <tr>
                                        <td colspan="5" class="py-16 text-center text-gray-400 italic">Your cart is empty. Select a package below.</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="lg:w-[35%] order-1 lg:order-2">
                <div class="border p-6 md:p-8 rounded-lg bg-white sticky top-28 shadow-sm border-t-4 border-t-[#5b6bd5]">
                    <h2 class="text-xl font-bold mb-6 border-b pb-4">Cart totals</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span class="font-bold">Subtotal</span>
                            <span x-text="'$' + calculateTotal().toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between text-xl font-bold border-t pt-4 text-[#54595f]">
                            <span>Total</span>
                            <span x-text="'$' + calculateTotal().toFixed(2)"></span>
                        </div>
                    </div>
                    <button @click="checkout()" :disabled="items.length === 0" 
                            class="w-full mt-8 bg-red-500 hover:bg-white text-white hover:text-red-500 border-2 border-red-500 py-4 rounded font-bold uppercase">
                        Proceed to checkout
                    </button>
                </div>
            </div>
        </div>
    </main>

    <hr class="border-gray-100">

    <section class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12 pt-16 pb-8 text-center">
        <h2 class="text-[#242222] text-[20px] sm:text-[24px] md:text-[28px] lg:text-[32px] font-bold leading-tight mb-6">Job listing credits</h2>
        <div class="text-[16px] leading-[1.6] max-w-3xl mx-auto space-y-4 text-[#222121]">
            <p>Two value-rich options to choose from.</p>
        </div>
    </section>

    <section class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12 pb-20">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-16">
            <div class="flex flex-col items-center bg-white p-8 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                <h3 class="text-[#333] text-[20px] font-bold mb-2 text-center">Standard job listing</h3>
                <span class="text-[#333] text-[26px] font-semibold mb-6">$105</span>
                <button @click="addItem('Standard job listing', 105)" class="bg-red-500 hover:bg-white text-white hover:text-red-500 border-2 border-red-500 text-[13px] font-bold tracking-wider uppercase px-8 py-3.5 rounded-[4px] mb-8 w-full sm:w-auto">
                    Add to Cart
                </button>
                <div class="bg-[#f8f9fa] p-6 rounded-lg text-left w-full text-[15px] border border-gray-50">
                    <ul class="list-disc ml-5 space-y-2">
                        <li>30-day listing</li>
                        <li>Logo featured</li>
                        <li>Search optimization</li>
                    </ul>
                </div>
            </div>

            <div class="flex flex-col items-center bg-white p-8 rounded-xl border-2 border-[#5b6bd5] shadow-md hover:shadow-lg transition-shadow">
                <h3 class="text-[#333] text-[20px] font-bold mb-2 text-center">Premium job listing</h3>
                <span class="text-[#333] text-[26px] font-semibold mb-6">$180</span>
                <button @click="addItem('Premium job listing', 180)" class="bg-red-500 hover:bg-white text-white hover:text-red-500 border-2 border-red-500 text-[13px] font-bold tracking-wider uppercase px-8 py-3.5 rounded-[4px] mb-8 w-full sm:w-auto">
                    Add to Cart
                </button>
                <div class="bg-[#f8f9fa] p-6 rounded-lg text-left w-full text-[15px] border border-gray-50">
                    <ul class="list-disc ml-5 space-y-2">
                        <li>Top-ranked highlighted</li>
                        <li>Featured on home page</li>
                        <li>Custom keywords</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-gray-50 py-16 border-y border-gray-100">
        <div class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12">
            <h4 class="text-center text-[24px] md:text-[28px] font-bold mb-12">Multi-listing packages</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 lg:gap-8">
                <div class="flex flex-col items-center p-8 bg-white border border-gray-200 rounded-xl">
                    <h5 class="text-[18px] font-bold mb-3 text-center">3 Standard listings</h5>
                    <span class="text-[26px] font-semibold text-[#333] mb-4">$275</span>
                    <button @click="addItem('3 Standard listings', 275)" class="bg-red-500 hover:bg-white text-white hover:text-red-500 border-2 border-red-500 text-[12px] font-bold uppercase px-6 py-2.5 rounded mb-4">Add to Cart</button>
                    <span class="text-[#e15f55] font-bold text-sm">15% discount</span>
                </div>
                <div class="flex flex-col items-center p-8 bg-white border border-gray-200 rounded-xl">
                    <h5 class="text-[18px] font-bold mb-3 text-center">5 Standard listings</h5>
                    <span class="text-[26px] font-semibold text-[#333] mb-4">$430</span>
                    <button @click="addItem('5 Standard listings', 430)" class="bg-red-500 hover:bg-white text-white hover:text-red-500 border-2 border-red-500 text-[12px] font-bold uppercase px-6 py-2.5 rounded mb-4">Add to Cart</button>
                    <span class="text-[#e15f55] font-bold text-sm">18% discount</span>
                </div>
                <div class="flex flex-col items-center p-8 bg-white border border-gray-200 rounded-xl">
                    <h5 class="text-[18px] font-bold mb-3 text-center">10 Standard listings</h5>
                    <span class="text-[26px] font-semibold text-[#333] mb-4">$755</span>
                    <button @click="addItem('10 Standard listings', 755)" class="bg-red-500 hover:bg-white text-white hover:text-red-500 border-2 border-red-500 text-[12px] font-bold uppercase px-6 py-2.5 rounded mb-4">Add to Cart</button>
                    <span class="text-[#e15f55] font-bold text-sm">28% discount</span>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 bg-white">
        <div class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12">
            <h4 class="text-center text-[22px] md:text-[26px] font-bold mb-12">Internship and volunteer listings</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 max-w-4xl mx-auto">
                <div class="flex flex-col items-center p-8 bg-slate-50 rounded-xl border border-gray-100">
                    <h4 class="text-[20px] font-bold mb-2">Internship listing</h4>
                    <span class="text-[26px] font-semibold mb-6">$30</span>
                    <button @click="addItem('Internship listing', 30)" class="bg-red-500 hover:bg-white text-white hover:text-red-500 border-2 border-red-500 text-[13px] font-bold uppercase px-10 py-3 rounded ">Add to Cart</button>
                </div>
                <div class="flex flex-col items-center p-8 bg-slate-50 rounded-xl border border-gray-100">
                    <h4 class="text-[20px] font-bold mb-2">Volunteer listing</h4>
                    <span class="text-[26px] font-semibold mb-6">$10</span>
                    <button @click="addItem('Volunteer listing', 10)" class="bg-red-500 hover:bg-white text-white hover:text-red-500 border-2 border-red-500 text-[13px] font-bold uppercase px-10 py-3 rounded ">Add to Cart</button>
                </div>
            </div>
        </div>
    </section>
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
                    <img width="127" height="70" 
                         src="<?php echo $base; ?>uploads/Mindware-infotech.png" 
                         class="h-auto w-[127px] brightness-0 invert" 
                         alt="Mindware Infotech Logo">
                </div>

                <nav class="mb-[30px]">
                    <ul class="flex flex-wrap justify-center gap-x-[25px] gap-y-2">
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
                    <a href="https://www.linkedin.com/company/mindwareinfotech/" target="_blank" 
                       class="bg-[#444444] hover:bg-[#0077b5] transition-all duration-300 p-3 rounded-full flex items-center justify-center">
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

<script>
    function cartManager() {
        return {
            items: JSON.parse(localStorage.getItem('employer_cart') || '[]'),
            notification: { show: false, message: '' },

            addItem(name, price) {
                let existing = this.items.find(i => i.name === name);
                if (existing) {
                    existing.qty++;
                } else {
                    this.items.push({ name, price: parseFloat(price), qty: 1 });
                }
                this.showNotice(`Added ${name} to cart`);
                this.sync();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },

            updateQty(index, delta) {
                if (this.items[index].qty + delta >= 1) {
                    this.items[index].qty += delta;
                    this.sync();
                }
            },

            removeItem(index) {
                this.items.splice(index, 1);
                this.sync();
            },

            calculateTotal() {
                return this.items.reduce((sum, item) => sum + (item.price * item.qty), 0);
            },

            showNotice(msg) {
                this.notification.message = msg;
                this.notification.show = true;
                setTimeout(() => this.notification.show = false, 3000);
            },

            sync() {
                localStorage.setItem('employer_cart', JSON.stringify(this.items));
            },

           checkout() {
                if (this.items.length === 0) return;
                localStorage.setItem('employer_cart', JSON.stringify(this.items));
                fetch('/social-services/cart/save', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ items: this.items })
                })
                .then(() => {
                    window.location.href = "/social-employer/checkout";
                })
                .catch(() => {
                    window.location.href = "/social-employer/checkout";
                });
            }

        }
    }
    
</script>

</body>
</html>
