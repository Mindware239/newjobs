<?php 
/**
 * @var string $title
 * @var array $company
 * @var array $blogs
 * @var array $reviews
 * @var array $jobs
 * @var array $stats
 * @var \App\Models\Employer $employer
 */
?>

<div class="max-w-screen-2xl mx-auto px-6 py-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Company Profile</h1>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Total Reviews</div>
            <div class="text-2xl font-bold text-gray-900"><?= number_format($stats['reviews_count'] ?? 0) ?></div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Average Rating</div>
            <div class="text-2xl font-bold text-gray-900"><?= number_format($stats['rating'] ?? 0, 1) ?> ⭐</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Followers</div>
            <div class="text-2xl font-bold text-gray-900"><?= number_format($stats['followers_count'] ?? 0) ?></div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Blogs Published</div>
            <div class="text-2xl font-bold text-gray-900"><?= count($blogs ?? []) ?></div>
        </div>
    </div>

    <div x-data="companyProfileForm()" class="space-y-6">
        <!-- Company Profile Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Company Information</h2>
            <form @submit.prevent="save" class="space-y-4" enctype="multipart/form-data">
                <input type="hidden" name="_token" :value="csrfToken">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Company Name *</label>
                        <input type="text" x-model="form.short_name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Website</label>
                        <input type="url" x-model="form.website" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Headquarters</label>
                        <input type="text" x-model="form.headquarters" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Founded Year</label>
                        <input type="text" x-model="form.founded_year" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Company Size</label>
                        <select x-model="form.company_size" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select size</option>
                            <option value="1-10">1-10 employees</option>
                            <option value="11-50">11-50 employees</option>
                            <option value="51-200">51-200 employees</option>
                            <option value="201-500">201-500 employees</option>
                            <option value="501-1000">501-1000 employees</option>
                            <option value="1000+">1000+ employees</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Revenue</label>
                        <input type="text" x-model="form.revenue" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">About Company</label>
                    <textarea x-model="form.description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="border rounded-lg p-4 bg-gray-50">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-md font-semibold text-gray-900">Why Join Points</h4>
                        <button type="button" @click="form.why_points.push('')" class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">Add Point</button>
                    </div>
                    <template x-for="(p, idx) in form.why_points" :key="idx">
                        <div class="flex items-center gap-2 mb-2">
                            <input type="text" x-model="form.why_points[idx]" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., Great work-life balance">
                            <button type="button" @click="form.why_points.splice(idx,1)" class="px-2 py-2 text-sm bg-gray-200 rounded hover:bg-gray-300">Remove</button>
                        </div>
                    </template>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">CEO Name</label>
                        <input type="text" x-model="form.ceo_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">CEO Photo</label>
                        <input type="file" name="ceo_photo" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php if (!empty($company['ceo_photo'])): ?>
                            <img src="<?= htmlspecialchars($company['ceo_photo']) ?>" alt="CEO Photo" class="mt-2 h-20 w-20 object-cover rounded">
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Logo</label>
                        <input type="file" name="logo" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php if (!empty($company['logo_url'])): ?>
                            <img src="<?= htmlspecialchars($company['logo_url']) ?>" alt="Logo" class="mt-2 h-20 w-20 object-cover rounded">
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Banner</label>
                        <input type="file" name="banner" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php if (!empty($company['banner_url'])): ?>
                            <img src="<?= htmlspecialchars($company['banner_url']) ?>" alt="Banner" class="mt-2 h-32 w-full object-cover rounded">
                        <?php endif; ?>
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <button @click="save" :disabled="isSubmitting" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!isSubmitting">Save Changes</span>
                        <span x-show="isSubmitting">Saving...</span>
                    </button>
                    <a href="/employer/dashboard" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 font-medium">Cancel</a>
                </div>
            </form>
        </div>

        <!-- Jobs Section -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Jobs</h2>
                <a href="/employer/jobs/create" class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">New Job</a>
            </div>
            <div class="mt-2">
                <?php if (empty($jobs)): ?>
                    <div class="text-gray-600">No jobs yet.</div>
                <?php else: ?>
                    <ul class="divide-y">
                        <?php foreach ($jobs as $j): ?>
                            <li class="py-3 flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-gray-900"><?= htmlspecialchars($j['title'] ?? 'Job') ?></div>
                                    <div class="text-xs text-gray-500">Status: <?= htmlspecialchars($j['status'] ?? 'draft') ?> • <?= !empty($j['created_at']) ? date('M d, Y', strtotime($j['created_at'])) : '' ?></div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a class="px-3 py-1 text-sm border rounded hover:bg-gray-50" href="/employer/jobs/<?= htmlspecialchars($j['slug'] ?? '') ?>/edit">Edit</a>
                                    <a class="px-3 py-1 text-sm border rounded hover:bg-gray-50" href="/employer/jobs/<?= htmlspecialchars($j['slug'] ?? '') ?>/preview" target="_blank">Preview</a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Blogs Section -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Blogs</h2>
                <button type="button" @click="showNewBlogForm = !showNewBlogForm" class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">New Blog</button>
            </div>

            <!-- New Blog Form -->
            <div x-show="showNewBlogForm" x-cloak class="mb-6 p-4 border rounded-lg bg-gray-50">
                <form @submit.prevent="createBlog" enctype="multipart/form-data" class="space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                            <input type="text" x-model="newBlog.title" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Image</label>
                            <input type="file" @change="newBlog.imageFile = $event.target.files[0]" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Excerpt</label>
                        <input type="text" x-model="newBlog.excerpt" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Content *</label>
                        <textarea x-model="newBlog.content" rows="4" required class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showNewBlogForm = false" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</button>
                        <button type="submit" :disabled="isCreatingBlog" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
                            <span x-show="!isCreatingBlog">Publish Blog</span>
                            <span x-show="isCreatingBlog">Publishing...</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Blogs List -->
            <div class="mt-6">
                <?php if (empty($blogs)): ?>
                    <div class="text-gray-600">No blogs yet.</div>
                <?php else: ?>
                    <ul class="divide-y">
                        <?php foreach ($blogs as $b): ?>
                            <li class="py-3 flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <?php if (!empty($b['image'])): ?>
                                        <img src="<?= htmlspecialchars($b['image']) ?>" alt="<?= htmlspecialchars($b['title']) ?>" class="w-16 h-16 object-cover rounded">
                                    <?php endif; ?>
                                    <div>
                                        <div class="font-medium text-gray-900"><?= htmlspecialchars($b['title']) ?></div>
                                        <div class="text-xs text-gray-500">
                                            <?= !empty($b['created_at']) ? date('M d, Y', strtotime($b['created_at'])) : '' ?>
                                            <span class="ml-2 px-2 py-0.5 text-xs rounded <?= ($b['status'] ?? '') === 'published' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                                <?= ucfirst($b['status'] ?? 'draft') ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <?php if (!empty($b['slug'])): ?>
                                        <a class="px-3 py-1 text-sm border rounded hover:bg-gray-50" href="/company/<?= htmlspecialchars($company['slug'] ?? '') ?>/blogs" target="_blank">View</a>
                                    <?php endif; ?>
                                    <button @click="deleteBlog(<?= (int)$b['id'] ?>)" class="px-3 py-1 text-sm border rounded hover:bg-red-50 text-red-600">Delete</button>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Reviews (<?= count($reviews ?? []) ?>)</h2>
            <?php if (empty($reviews)): ?>
                <div class="text-gray-600">No reviews yet.</div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($reviews as $review): ?>
                        <div class="border rounded-lg p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="font-semibold text-gray-900"><?= htmlspecialchars($review['reviewer_name'] ?? 'Anonymous') ?></div>
                                        <div class="flex items-center">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <svg class="w-4 h-4 <?= $i <= ($review['rating'] ?? 0) ? 'text-yellow-400' : 'text-gray-300' ?>" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                </svg>
                                            <?php endfor; ?>
                                        </div>
                                        <div class="text-sm text-gray-500"><?= !empty($review['created_at']) ? date('M d, Y', strtotime($review['created_at'])) : '' ?></div>
                                    </div>
                                    <div class="font-medium text-gray-800 mb-1"><?= htmlspecialchars($review['title'] ?? '') ?></div>
                                    <div class="text-gray-600"><?= htmlspecialchars($review['review_text'] ?? '') ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Followers Section -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Followers (<?= $stats['followers_count'] ?? 0 ?>)</h2>
            <div class="text-gray-600">
                <p>Followers are managed automatically when candidates follow your company profile.</p>
                <p class="mt-2">To view detailed follower information, visit your company's public profile page.</p>
            </div>
        </div>
    </div>
</div>

<script>
function companyProfileForm() {
    return {
        isSubmitting: false,
        isCreatingBlog: false,
        showNewBlogForm: false,
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '',
        form: {
            short_name: '<?= htmlspecialchars(($company['short_name'] ?? ($employer->company_name ?? '')), ENT_QUOTES) ?>',
            website: '<?= htmlspecialchars($company['website'] ?? '', ENT_QUOTES) ?>',
            headquarters: '<?= htmlspecialchars($company['headquarters'] ?? '', ENT_QUOTES) ?>',
            founded_year: '<?= htmlspecialchars($company['founded_year'] ?? '', ENT_QUOTES) ?>',
            company_size: '<?= htmlspecialchars($company['company_size'] ?? '', ENT_QUOTES) ?>',
            revenue: '<?= htmlspecialchars($company['revenue'] ?? '', ENT_QUOTES) ?>',
            description: '',
            ceo_name: '<?= htmlspecialchars($company['ceo_name'] ?? '', ENT_QUOTES) ?>',
            why_points: []
        },
        newBlog: {
            title: '',
            excerpt: '',
            content: '',
            imageFile: null
        },
        init() {
            const raw = '<?= addslashes($company['description'] ?? '') ?>';
            try {
                const parsed = JSON.parse(raw);
                if (parsed && typeof parsed === 'object') {
                    this.form.description = parsed.about || '';
                    this.form.why_points = Array.isArray(parsed.why_points) ? parsed.why_points : [];
                } else {
                    this.form.description = raw;
                    this.form.why_points = [];
                }
            } catch(e) {
                this.form.description = raw;
                this.form.why_points = [];
            }
            if (this.form.why_points.length === 0) {
                this.form.why_points = ['', '', '', ''];
            }
        },
        async save() {
            this.isSubmitting = true;
            try {
                const fd = new FormData();
                fd.append('short_name', this.form.short_name);
                fd.append('website', this.form.website || '');
                fd.append('headquarters', this.form.headquarters || '');
                fd.append('founded_year', this.form.founded_year || '');
                fd.append('company_size', this.form.company_size || '');
                fd.append('revenue', this.form.revenue || '');
                fd.append('description', this.form.description || '');
                (this.form.why_points || []).forEach((p) => {
                    const v = (p || '').trim();
                    if (v) fd.append('why_points[]', v);
                });
                fd.append('ceo_name', this.form.ceo_name || '');
                fd.append('_token', this.csrfToken);

                const logoInput = document.querySelector('input[name="logo"]');
                if (logoInput && logoInput.files[0]) fd.append('logo', logoInput.files[0]);
                const bannerInput = document.querySelector('input[name="banner"]');
                if (bannerInput && bannerInput.files[0]) fd.append('banner', bannerInput.files[0]);
                const ceoPhotoInput = document.querySelector('input[name="ceo_photo"]');
                if (ceoPhotoInput && ceoPhotoInput.files[0]) fd.append('ceo_photo', ceoPhotoInput.files[0]);

                const res = await fetch('/employer/company-profile', { method: 'POST', body: fd });
                const data = await res.json();
                if (res.ok && data.success) {
                    alert('Company profile updated successfully');
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Failed to update'));
                }
            } catch (e) {
                alert('Error: ' + e.message);
            } finally {
                this.isSubmitting = false;
            }
        },
        async createBlog() {
            this.isCreatingBlog = true;
            try {
                const fd = new FormData();
                fd.append('title', this.newBlog.title);
                fd.append('excerpt', this.newBlog.excerpt || '');
                fd.append('content', this.newBlog.content);
                if (this.newBlog.imageFile) {
                    fd.append('image', this.newBlog.imageFile);
                }
                fd.append('_token', this.csrfToken);

                const res = await fetch('/employer/company-profile/blogs', { method: 'POST', body: fd });
                const data = await res.json();
                if (res.ok && data.success) {
                    alert('Blog created successfully');
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Failed to create blog'));
                }
            } catch (e) {
                alert('Error: ' + e.message);
            } finally {
                this.isCreatingBlog = false;
            }
        },
        async deleteBlog(blogId) {
            if (!confirm('Are you sure you want to delete this blog?')) return;
            try {
                const res = await fetch('/employer/company-profile/blogs/' + blogId, { 
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-Token': this.csrfToken
                    }
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    alert('Blog deleted successfully');
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Failed to delete blog'));
                }
            } catch (e) {
                alert('Error: ' + e.message);
            }
        }
    }
}
</script>
<style>
[x-cloak] { display: none !important; }
</style>
