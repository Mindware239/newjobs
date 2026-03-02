<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Career Insights | Mindware Infotech</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<?php $base = $base ?? '/'; ?>
<body class="bg-gray-50 text-[#54595f] font-sans" x-data="{ mobileMenuOpen: false }">

<?php include __DIR__ . '/header.php'; ?>

<script>
  window.ARTICLES = <?= json_encode($articles ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>

<div class="max-w-[1340px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12 py-10"
            x-data="{
            searchQuery: '',
            currentPage: 1,
            perPage: 10,
            articles: window.ARTICLES,
            get filteredArticles() {
                const q = this.searchQuery.toLowerCase();
                return this.articles.filter(i => (i.title || '').toLowerCase().includes(q) || (i.desc || '').toLowerCase().includes(q));
            },
            get totalPages() {
                return Math.ceil(this.filteredArticles.length / this.perPage);
            },
            get paginatedArticles() {
                let start = (this.currentPage - 1) * this.perPage;
                let end = start + this.perPage;
                return this.filteredArticles.slice(start, end);
            },
            resetPage() {
                this.currentPage = 1;
            }
          }">

    <div class="flex flex-col lg:flex-row gap-8">
        <aside class="w-full lg:w-1/4 space-y-8">
            <h1 class="font-bold text-[#54595f] text-[22px] sm:text-[28px] md:text-[32px] lg:text-[36px]">Search Articles</h1>

            <div class="bg-gray-100 p-6 rounded-xl shadow-sm border border-gray-200">
                <h3 class="text-md font-bold text-gray-800 mb-4">Search by keyword</h3>
                <div class="relative">
                    <input type="text"
                           x-model="searchQuery"
                           placeholder="Search by keyword..."
                           class="w-full pl-4 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#5b6bd5] focus:border-[#5b6bd5] outline-none transition-all">
                </div>

                <h3 class="text-[16px] font-bold mt-4 text-black mb-2">Subscribe</h3>
                <p class="text-sm text-black leading-relaxed mb-4">
                    Want to get hiring insights delivered to your inbox each month, featuring practical, topical, dependable resources?
                </p>
                <button
                    x-ref="redirectBtn"
                    @click="$refs.redirectBtn.innerText = 'Opening...'; window.location.href = 'hiringInsightSignUp';"
                    type="submit" class="w-full bg-red-500 hover:opacity-90 text-white font-semibold py-2 rounded-lg hover:bg-white hover:text-red-500  border-2 border-red-500">
                     Subscribe today!
                </button>
            </div>
        </aside>

        <main class="w-full lg:w-3/4">
            <div class="space-y-6">
                <template x-for="article in paginatedArticles" :key="article.id">
                    <div class="bg-white p-4 flex flex-col md:flex-row gap-6 group">
                        <div class="md:w-1/3 h-48 md:h-40 shrink-0 overflow-hidden rounded-xl">
                            <img :src="article.img" class="w-full h-full object-cover" alt="Article Thumbnail">
                        </div>

                        <div class="flex flex-col justify-center flex-grow">
                            <h2 class="text-xl font-bold text-slate-900 mb-1" x-text="article.title"></h2>
                            <p class="text-sm font-semibold text-slate-900 mb-3" x-text="article.date"></p>
                            <p class="text-slate-600 text-sm line-clamp-2 mb-4" x-text="article.desc"></p>
                            <a :href="'hiringInsight/article?id=' + article.id" class="text-sm font-bold flex items-center gap-2">
                                Read Article <i class="fa-solid fa-arrow-right-long text-xs"></i>
                            </a>
                        </div>
                    </div>
                </template>
            </div>

            <div class="pt-8 flex items-center justify-center gap-2">
                <button
                    @click="if(currentPage > 1) currentPage--"
                    :disabled="currentPage === 1"
                    :class="currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-white'"
                    class="w-10 h-10 flex items-center justify-center rounded-xl border border-slate-200 text-[#5b6bd5] transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
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

