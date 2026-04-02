<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Uploader Login</title>
    <link href="/css/output.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center">
    <div x-data="bulkLogin()" class="bg-white rounded-xl shadow p-8 w-full max-w-md">
        <h1 class="text-2xl font-bold mb-6">Bulk Uploader Login</h1>
        <template x-if="error"><div class="mb-4 text-sm text-red-600" x-text="error"></div></template>
        <form @submit.prevent="submitLogin" class="space-y-4">
            <input type="hidden" name="_token" :value="csrfToken">
            <div>
                <label class="block text-sm font-semibold mb-1">Username</label>
                <input type="text" name="username" x-model="form.username" class="w-full px-4 py-2 border rounded-lg" required>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Password</label>
                <input type="password" name="password" x-model="form.password" class="w-full px-4 py-2 border rounded-lg" required>
            </div>
            <button type="submit" :disabled="loading" class="w-full py-2 bg-blue-600 text-white rounded-lg disabled:opacity-50">
                <span x-show="!loading">Login</span>
                <span x-show="loading">Verifying...</span>
            </button>
        </form>
    </div>

<script>
function bulkLogin() {
    return {
        loading: false,
        error: '<?= isset($error) ? addslashes($error) : '' ?>',
        csrfToken: '<?= $_SESSION['csrf_token'] ?? '' ?>',
        form: { username: '', password: '' },

        async submitLogin() {
            this.loading = true;
            this.error = '';
            try {
                const response = await fetch('/bulk/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        ...this.form,
                        _token: this.csrfToken
                    })
                });
                const data = await response.json();
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    if (data.refresh_csrf && data.csrf_token) {
                        this.csrfToken = data.csrf_token;
                    }
                    this.error = data.error || 'Login failed';
                }
            } catch (e) {
                this.error = 'A network error occurred. Please try again.';
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
</body>
</html>
