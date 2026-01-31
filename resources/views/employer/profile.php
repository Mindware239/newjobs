<?php 
/**
 * @var string $title
 * @var \App\Models\Employer $employer
 * @var \App\Models\User $user
 * @var array $address
 */
?>

<div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">My Profile</h1>

    <div x-data="profileForm()" class="space-y-6">
        <!-- Company Information -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Company Information</h2>
            
            <form @submit.prevent="updateProfile" class="space-y-4">
                <input type="hidden" name="_token" :value="csrfToken">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Company Name *</label>
                        <input type="text" 
                               x-model="formData.company_name"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Website</label>
                        <input type="url" 
                               x-model="formData.website"
                               placeholder="https://example.com"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Company Description</label>
                    <textarea x-model="formData.description"
                              rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Industry</label>
                        <input type="text" 
                               x-model="formData.industry"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Company Size *</label>
                        <select x-model="formData.company_size"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select size</option>
                            <option value="1-10">1-10 employees</option>
                            <option value="11-50">11-50 employees</option>
                            <option value="51-200">51-200 employees</option>
                            <option value="201-500">201-500 employees</option>
                            <option value="501-1000">501-1000 employees</option>
                            <option value="1000+">1000+ employees</option>
                        </select>
                    </div>
                </div>

                <!-- Logo Upload -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Company Logo</label>
                    <div class="flex items-center space-x-4">
                        <?php if (!empty($employer->logo_url)): ?>
                            <img src="<?= htmlspecialchars($employer->logo_url) ?>" 
                                 alt="Company Logo" 
                                 class="h-20 w-20 object-cover rounded-md">
                        <?php endif; ?>
                        <input type="file" 
                               name="logo"
                               accept="image/*"
                               @change="handleLogoUpload"
                               class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </form>
        </div>

        <!-- Contact Information -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Contact Information</h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                    <input type="email" 
                           x-model="formData.email"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                    <input type="tel" 
                           x-model="formData.phone"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <!-- Address Information -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Address</h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Country *</label>
                    <input type="text" 
                           x-model="formData.country"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">State</label>
                        <input type="text" 
                               x-model="formData.address.state"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                        <input type="text" 
                               x-model="formData.address.city"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Postal Code</label>
                    <input type="text" 
                           x-model="formData.address.postal_code"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Street Address</label>
                    <textarea x-model="formData.address.street"
                              rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"><?= htmlspecialchars($address['street'] ?? '', ENT_QUOTES) ?></textarea>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end space-x-4">
            <button @click="updateProfile" 
                    :disabled="isSubmitting"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed shadow-md">
                <span x-show="!isSubmitting">Save Changes</span>
                <span x-show="isSubmitting">Saving...</span>
            </button>
            <a href="/employer/dashboard" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 font-medium">
                Cancel
            </a>
        </div>
    </div>
</div>

<script>
function profileForm() {
    return {
        isSubmitting: false,
        csrfToken: document.querySelector('meta[name="csrf-token"]').content,
        formData: {
            company_name: '<?= htmlspecialchars($employer->company_name ?? '', ENT_QUOTES) ?>',
            website: '<?= htmlspecialchars($employer->website ?? '', ENT_QUOTES) ?>',
            description: <?= json_encode($employer->description ?? '') ?>,
            industry: '<?= htmlspecialchars($employer->industry ?? '', ENT_QUOTES) ?>',
            company_size: '<?= htmlspecialchars($employer->size ?? '', ENT_QUOTES) ?>',
            email: '<?= htmlspecialchars($user->email ?? '', ENT_QUOTES) ?>',
            phone: '<?= htmlspecialchars($user->phone ?? '', ENT_QUOTES) ?>',
            country: '<?= htmlspecialchars($employer->country ?? '', ENT_QUOTES) ?>',
            address: {
                state: <?= json_encode($address['state'] ?? '') ?>,
                city: <?= json_encode($address['city'] ?? '') ?>,
                postal_code: <?= json_encode($address['postal_code'] ?? '') ?>,
                street: <?= json_encode($address['street'] ?? '') ?>
            }
        },
        handleLogoUpload(event) {
            // Logo will be handled by FormData
        },
        async updateProfile() {
            this.isSubmitting = true;
            
            try {
                const formData = new FormData();
                formData.append('company_name', this.formData.company_name);
                formData.append('website', this.formData.website || '');
                formData.append('description', this.formData.description || '');
                formData.append('industry', this.formData.industry || '');
                formData.append('company_size', this.formData.company_size);
                formData.append('email', this.formData.email);
                formData.append('phone', this.formData.phone || '');
                formData.append('country', this.formData.country);
                formData.append('address', JSON.stringify(this.formData.address));
                formData.append('_token', this.csrfToken);

                // Add logo if selected
                const logoInput = document.querySelector('input[name="logo"]');
                if (logoInput && logoInput.files[0]) {
                    formData.append('logo', logoInput.files[0]);
                }

                const response = await fetch('/employer/profile', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    alert('Profile updated successfully!');
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Failed to update profile'));
                }
            } catch (error) {
                alert('Error: ' + error.message);
            } finally {
                this.isSubmitting = false;
            }
        }
    }
}
</script>

