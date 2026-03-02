<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Employers | Mindware Infotech</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<?php $base = $base ?? '/'; ?>
<body class="bg-slate-50 min-h-screen font-sans">


<?php include __DIR__ . '/header.php'; ?>


<div class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12 py-12">
    <div class="mb-10 text-center min-[900px]:text-left">
        <h1 class="text-[18px] sm:text-[24px] md:text-[28px] lg:text-[32px] font-bold text-slate-900 tracking-tight">Search Employers</h1>
    </div>

    <script>
        window.EMPLOYERS = <?php echo json_encode($employers ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    </script>
    <div class="grid grid-cols-1 min-[900px]:grid-cols-4 gap-8" x-data="{ employerType: '', keyword: '', employers: window.EMPLOYERS || [], currentPage: 1, perPage: 10, get filtered() { const q=this.keyword.toLowerCase(); return this.employers.filter(e => (e.name||'').toLowerCase().includes(q) && (!this.employerType || e.type===this.employerType)); }, get totalPages(){ const t=this.filtered.length; return t>0?Math.ceil(t/this.perPage):1; }, get pageItems(){ const s=(this.currentPage-1)*this.perPage; return this.filtered.slice(s, s+this.perPage); } }">
        <aside class="space-y-6">
            <div class="bg-gray-100 p-6 rounded-3xl border border-slate-200 shadow-sm">
                <h2 class="text-[13px] uppercase tracking-widest text-black mb-6">Search employers by using one or more of the filters below.</h2>
                <div class="space-y-2 mb-6">
                    <label class="block text-base font-bold text-slate-800">Keywords</label>
                    <div class="relative">
                        <input x-model="keyword" type="text" placeholder="Employer name..." 
                               class="w-full pl-4 pr-4 py-3 bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-[#5b6bd5]/20 focus:border-[#08080a] transition-all outline-none text-sm font-semibold">
                    </div>
                </div>
                <div class="space-y-2 mb-6">
                    <label class="block text-base font-bold text-slate-800">Employer Type</label>
                    <div class="relative border border-gray-300">
                        <select x-model="employerType" 
                                class="w-full appearance-none pl-4 pr-10 py-3 bg-slate-50 text-sm font-semibold text-slate-700 outline-none focus:ring-0 focus:border-gray-300">
                            <option value="">Select</option>
                            <option value="direct">Direct Employer</option>
                            <option value="recruiter">Recruiter / Staffing</option>
                        </select>
                        <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
                    </div>
                </div>
                <button @click="currentPage=1" class="w-full bg-red-500 text-white font-bold py-4 rounded-2xl hover:bg-white hover:text-red-500 border-2 border-red-500">
                    Apply Filters
                </button>
            </div>
        </aside>

        <main class="min-[900px]:col-span-3 space-y-4">
            <div class="grid grid-cols-1 gap-4">
                <template x-if="pageItems && pageItems.length > 0">    
                    <template x-for="(employer, index) in pageItems" :key="index">
                        <div class="bg-white p-5 rounded-2xl group flex items-center justify-between">
                            <div class="flex flex-col">
                                <a :href="'/organizationDetails?id=' + (employer.id||'')" class="no-underline">
                                    <h3 class="font-bold text-lg text-[#54595f] transition-colors" x-text="employer.name"></h3>
                                </a>
                            </div>
                            <div class="w-14 h-14 bg-white border border-slate-100 rounded-xl flex items-center justify-center overflow-hidden flex-shrink-0 ml-4">
                                <img :src="employer.logo"
                                     :alt="employer.name" 
                                     class="w-full h-full object-contain p-2"
                                     @error="$el.src='https://ui-avatars.com/api/?name=' + encodeURIComponent(employer.name)">
                            </div>
                        </div>
                    </template>
                </template>
            </div>

            <div class="pt-8 flex flex-wrap items-center justify-center gap-2">
                <button 
                    @click="if(currentPage > 1) currentPage--"
                    :disabled="currentPage === 1"
                    :class="currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-white'"
                    class="w-10 h-10 flex items-center justify-center rounded-xl border border-slate-200 text-slate-400 transition-all">
                    <i class="fa-solid fa-chevron-left text-xs"></i>
                </button>
                <template x-for="page in totalPages" :key="page">
                    <button 
                        @click="currentPage = page"
                        :class="currentPage === page ? 'bg-[#5b6bd5] text-white' : 'border border-slate-200 text-slate-600 hover:bg-white'"
                        class="w-10 h-10 flex items-center justify-center rounded-xl font-bold transition-all"
                        x-text="page">
                    </button>
                </template>
                <button 
                    @click="if(currentPage < totalPages) currentPage++"
                    :disabled="currentPage === totalPages"
                    :class="currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-white'"
                    class="px-4 h-10 flex items-center justify-center rounded-xl border border-slate-200 text-slate-600 font-bold transition-all">
                    Next <i class="fa-solid fa-chevron-right text-xs ml-2"></i>
                </button>
            </div>
        </main>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>

</body>
</html>
