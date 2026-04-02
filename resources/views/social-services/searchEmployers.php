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
        <h1 class="text-[18px] sm:text-[24px] md:text-[28px] lg:text-[32px] font-bold text-slate-900 tracking-tight">Search employers</h1>
    </div>

    <script>
        window.EMPLOYERS = <?php echo json_encode($employers ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    </script>
    <div class="grid grid-cols-1 min-[900px]:grid-cols-4 gap-8" x-data="{ employerType: '', keyword: '', employers: window.EMPLOYERS || [], currentPage: 1, perPage: 10, get filtered() { const q=this.keyword.toLowerCase(); return this.employers.filter(e => (e.name||'').toLowerCase().includes(q) && (!this.employerType || e.type===this.employerType)); }, get totalPages(){ const t=this.filtered.length; return t>0?Math.ceil(t/this.perPage):1; }, get pageItems(){ const s=(this.currentPage-1)*this.perPage; return this.filtered.slice(s, s+this.perPage); }, get pagesCompact(){ const tp=this.totalPages; const arr=[]; if(tp<=7){ for(let i=1;i<=tp;i++) arr.push(i); } else { arr.push(1,2,3,4,'...',tp); } return arr; } }">
        <aside class="space-y-6">
            <div class="bg-white p-6 rounded-xl border border-slate-200">
                <h2 class="text-[13px] uppercase tracking-widest text-black mb-6">Search employers by using one or more of the filters below.</h2>
                <div class="space-y-2 mb-6">
                    <label class="block text-base font-bold text-slate-800">Keywords</label>
                    <div class="relative">
                        <input x-model="keyword" type="text" placeholder="Employer name..." 
                               class="w-full pl-4 pr-4 py-2 bg-white border border-slate-300 focus:ring-2 focus:ring-[#e15f55]/20 focus:border-[#e15f55] transition-all outline-none text-sm">
                    </div>
                </div>
                <div class="space-y-2 mb-6">
                    <label class="block text-base font-bold text-slate-800">Employer Type</label>
                    <div class="relative border border-slate-300">
                        <select x-model="employerType" 
                                class="w-full appearance-none pl-3 pr-10 py-2 bg-white text-sm text-slate-700 outline-none focus:ring-0 focus:border-[#e15f55]">
                            <option value="">Select</option>
                            <option value="direct">Direct Employer</option>
                            <option value="recruiter">Recruiter / Staffing</option>
                        </select>
                        <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
                    </div>
                </div>
                <button @click="currentPage=1" class="w-full bg-[#e15f55] text-white font-bold py-2 rounded-md hover:bg-white hover:text-[#e15f55] border border-[#e15f55]">
                    Apply filters
                </button>
            </div>
        </aside>

        <main class="min-[900px]:col-span-3 space-y-4">
            <div class="grid grid-cols-1 gap-4">
                <template x-if="pageItems && pageItems.length > 0">    
                    <template x-for="(employer, index) in pageItems" :key="index">
                        <div class="bg-white p-5 rounded-md border border-slate-200 flex items-center justify-between">
                            <div class="flex flex-col">
                                <a :href="'/organizationDetails?id=' + (employer.id||'')" class="no-underline">
                                    <span class="text-[#e15f55] text-[18px] font-medium" x-text="'“' + (employer.name||'') + '”'"></span>
                                </a>
                            </div>
                            <div class="w-28 h-16 bg-white border border-slate-200 rounded-md flex items-center justify-center overflow-hidden flex-shrink-0 ml-4">
                                <img :src="employer.logo"
                                     :alt="employer.name" 
                                     class="w-full h-full object-contain p-1"
                                     @error="$el.src='https://ui-avatars.com/api/?name=' + encodeURIComponent(employer.name)">
                            </div>
                        </div>
                    </template>
                </template>
            </div>

            <div class="pt-8 flex flex-wrap items-center justify-center gap-2">
                <button @click="if(currentPage > 1) currentPage--" :disabled="currentPage === 1" class="w-10 h-10 flex items-center justify-center rounded border border-slate-200 text-slate-500">
                    <i class="fa-solid fa-chevron-left text-xs"></i>
                </button>
                <template x-for="(p, i) in pagesCompact" :key="i">
                    <template x-if="p === '...'">
                        <span class="px-2 text-slate-500">...</span>
                    </template>
                    <template x-if="p !== '...'">
                        <button @click="currentPage = p" :class="currentPage === p ? 'bg-[#e15f55] text-white' : 'border border-slate-200 text-slate-700 hover:bg-white'" class="w-10 h-10 flex items-center justify-center rounded font-bold" x-text="p"></button>
                    </template>
                </template>
                <button @click="if(currentPage < totalPages) currentPage++" :disabled="currentPage === totalPages" class="px-4 h-10 flex items-center justify-center rounded border border-slate-200 text-slate-700 font-bold">
                    Next
                </button>
            </div>
        </main>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>

</body>
</html>
