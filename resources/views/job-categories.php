<?php
$getCatIcon = function($name) {
    $n = strtolower($name);
    // Default briefcase
    $default = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z" />';

    if (str_contains($n, 'software') || str_contains($n, 'it') || str_contains($n, 'developer') || str_contains($n, 'code')) {
        return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />';
    }
    if (str_contains($n, 'manufactur') || str_contains($n, 'factory') || str_contains($n, 'industr')) {
        return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5M12 6.75h1.5M15 6.75h1.5M9 9.75h1.5M12 9.75h1.5M15 9.75h1.5M9 12.75h1.5M12 12.75h1.5M15 12.75h1.5M9 15.75h1.5M12 15.75h1.5M15 15.75h1.5" />';
    }
    if (str_contains($n, 'educat') || str_contains($n, 'train') || str_contains($n, 'teach')) {
        return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.499 5.216 50.592 50.592 0 00-2.662.813m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />';
    }
    if (str_contains($n, 'bank') || str_contains($n, 'financ') || str_contains($n, 'money')) {
        return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />';
    }
    if (str_contains($n, 'driv') || str_contains($n, 'transport')) {
        return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />';
    }
    if (str_contains($n, 'deliver') || str_contains($n, 'logist')) {
        return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />';
    }
    if (str_contains($n, 'sale') || str_contains($n, 'market')) {
        return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />';
    }
    if (str_contains($n, 'health') || str_contains($n, 'medic') || str_contains($n, 'doctor') || str_contains($n, 'nurs')) {
        return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />';
    }
    if (str_contains($n, 'food') || str_contains($n, 'restaurant') || str_contains($n, 'chef') || str_contains($n, 'cook')) {
        return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />';
    }
    if (str_contains($n, 'textile') || str_contains($n, 'garment') || str_contains($n, 'fashion')) {
        return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42" />';
    }

    return $default;
};
?>
<div class="bg-white py-12">
    <div class="container mx-auto px-4 lg:px-8">
        <!-- Breadcrumbs -->
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="/" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                        Home
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Job Categories</span>
                    </div>
                </li>
            </ol>
        </nav>

        <h1 class="text-3xl font-bold text-gray-900 mb-2">Browse Jobs by Category</h1>
        <p class="text-gray-600 mb-8">Explore thousands of jobs across different industries and find your perfect match.</p>

        <!-- Alphabetical Index -->
        <div class="flex flex-wrap gap-2 mb-12">
            <?php foreach (range('A', 'Z') as $char): ?>
                <?php $has = isset($groupedCategories[$char]); ?>
                <a href="#cat-<?= $char ?>" 
                   class="w-8 h-8 flex items-center justify-center rounded-lg text-sm font-semibold transition-colors
                          <?= $has ? 'bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white' : 'bg-gray-50 text-gray-300 cursor-default pointer-events-none' ?>">
                    <?= $char ?>
                </a>
            <?php endforeach; ?>
            <?php if (isset($groupedCategories['#'])): ?>
                <a href="#cat-other" class="px-3 h-8 flex items-center justify-center rounded-lg text-sm font-semibold bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-colors">#</a>
            <?php endif; ?>
        </div>

        <!-- Categories List -->
        <div class="space-y-12">
            <?php foreach ($groupedCategories as $letter => $cats): ?>
                <div id="cat-<?= $letter === '#' ? 'other' : $letter ?>" class="scroll-mt-24">
                    <div class="flex items-center gap-4 mb-6">
                        <span class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 text-gray-700 font-bold text-xl">
                            <?= $letter ?>
                        </span>
                        <div class="h-px flex-1 bg-gray-100"></div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        <?php foreach ($cats as $cat): ?>
                            <a href="/jobs-in-category/<?= urlencode($cat['slug'] ?? $cat['name']) ?>" 
                               class="group flex items-center p-4 rounded-xl border border-gray-100 hover:border-blue-100 hover:bg-blue-50/50 hover:shadow-sm transition-all duration-200">
                                <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <?= $getCatIcon($cat['name'] ?? '') ?>
                                    </svg>
                                </div>
                                <div class="ml-4 min-w-0">
                                    <h3 class="font-medium text-gray-900 group-hover:text-blue-600 truncate transition-colors" title="<?= htmlspecialchars($cat['name'] ?? '') ?>">
                                        <?= htmlspecialchars($cat['name'] ?? '') ?>
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        <?= $cat['count'] ?? 0 ?> Active Jobs
                                    </p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($groupedCategories)): ?>
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">No categories found</h3>
                    <p class="text-gray-500 mt-1">Check back later for new job categories.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>