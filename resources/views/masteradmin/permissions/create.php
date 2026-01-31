<?php
$old = $old ?? [];
$modules = $modules ?? [];
?>
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
            <div class="px-4 sm:px-0">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Create Permission</h3>
                <p class="mt-1 text-sm text-gray-600">
                    Define a new system permission. Permissions control what users can do within the application.
                </p>
                <div class="mt-4 bg-blue-50 border-l-4 border-blue-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                <strong>Naming Convention:</strong><br>
                                Use <code>resource.action</code> format for slugs (e.g., <code>users.create</code>, <code>reports.view</code>).
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-5 md:mt-0 md:col-span-2">
            <?php if (!empty($error)): ?>
                <div class="mb-4 p-4 rounded-md bg-red-50 border border-red-200">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Error</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <p><?= htmlspecialchars($error) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form
                method="post"
                action="/master/permissions/create"
                class="shadow sm:rounded-md sm:overflow-hidden bg-white"
                x-data="permissionForm()"
                x-init="init()"
            >
                <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="px-4 py-5 space-y-6 sm:p-6">
                    <div class="grid grid-cols-6 gap-6">
                        <div class="col-span-6 sm:col-span-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">Permission Name</label>
                            <input
                                type="text"
                                name="name"
                                id="name"
                                value="<?= htmlspecialchars($old['name'] ?? '') ?>"
                                placeholder="e.g. Create User"
                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                x-model="name"
                                @input="onNameInput($event.target.value)"
                            >
                            <p class="mt-2 text-sm text-gray-500">Human-readable name for the permission.</p>
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <label for="slug" class="block text-sm font-medium text-gray-700">Slug (Code)</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <input
                                    type="text"
                                    name="slug"
                                    id="slug"
                                    value="<?= htmlspecialchars($old['slug'] ?? '') ?>"
                                    placeholder="e.g. users.create"
                                    class="focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-none rounded-r-md sm:text-sm border-gray-300"
                                    x-model="slug"
                                    @input="onSlugInput($event.target.value)"
                                >
                            </div>
                            <p class="mt-1 text-xs text-gray-400 font-mono" x-text="slug || (name ? generateSlug(name) : '') || 'Slug will be suggested from the permission name'"></p>
                            <p class="mt-2 text-sm text-gray-500">Unique identifier used in code. Keep it lowercase and dot-separated.</p>
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <label for="module" class="block text-sm font-medium text-gray-700">Module</label>
                            <input
                                type="text"
                                name="module"
                                id="module"
                                value="<?= htmlspecialchars($old['module'] ?? '') ?>"
                                placeholder="e.g. User Management"
                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                x-model="module"
                                @input="onModuleInput($event.target.value)"
                            >
                            <?php if (!empty($modules)): ?>
                                <div class="mt-2">
                                    <p class="text-xs text-gray-500 mb-1">Quick select from existing modules:</p>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="mod in suggestions" :key="mod">
                                            <button
                                                type="button"
                                                class="px-2.5 py-1 rounded-full text-xs border border-gray-300 bg-gray-50 text-gray-700 hover:bg-indigo-50 hover:border-indigo-400"
                                                @click="selectModule(mod)"
                                                x-text="mod"
                                            ></button>
                                        </template>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <p class="mt-2 text-sm text-gray-500">Group this permission under a module for better organization.</p>
                        </div>
                    </div>
                </div>
                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                    <a href="/master/permissions" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-3">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Create Permission
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function permissionForm() {
        return {
            name: <?= json_encode($old['name'] ?? '') ?>,
            slug: <?= json_encode($old['slug'] ?? '') ?>,
            module: <?= json_encode($old['module'] ?? '') ?>,
            slugManual: <?= isset($old['slug']) && $old['slug'] !== '' ? 'true' : 'false' ?>,
            suggestions: <?= json_encode(array_values($modules)) ?>,
            init() {
                if (!this.slug && this.name) {
                    this.slug = this.generateSlug(this.name);
                }
            },
            generateSlug(value) {
                return value.toLowerCase().replace(/[^a-z0-9]+/g, ".").replace(/^\.+|\.+$/g, "");
            },
            selectModule(value) {
                this.module = value;
            },
            onNameInput(value) {
                this.name = value;
                if (!this.slugManual) {
                    this.slug = this.generateSlug(value);
                }
            },
            onSlugInput(value) {
                this.slug = value;
                this.slugManual = value.length > 0;
            },
            onModuleInput(value) {
                this.module = value;
            }
        };
    }
</script>
