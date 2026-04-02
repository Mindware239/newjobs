<?php $base = $base ?? '/'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($title ?? 'Reset Password') ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
</head>
<body class="bg-gray-50">
  <div class="max-w-md mx-auto mt-10 bg-white border rounded-xl shadow-sm p-6">
    <h1 class="text-xl font-semibold mb-2">Reset Password</h1>
    <p class="text-sm text-gray-600 mb-4">Enter your email address and we will send you a reset link.</p>
    <div id="ss-msg" class="hidden mb-4 rounded-md p-3 text-sm"></div>
    <form id="ss-forgot" class="space-y-4">
      <label class="block text-sm font-medium">Email</label>
      <input type="email" name="email" id="ss-email" required class="w-full border border-gray-300 rounded-md p-2.5">
      <button id="ss-submit" type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Send Reset Link</button>
    </form>
  </div>
  <script>
    (function(){
      const form = document.getElementById('ss-forgot');
      const btn = document.getElementById('ss-submit');
      const msg = document.getElementById('ss-msg');
      function show(type, text){
        msg.className = 'mb-4 rounded-md p-3 text-sm ' + (type==='ok' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-700');
        msg.textContent = text;
        msg.classList.remove('hidden');
      }
      form.addEventListener('submit', async (e)=>{
        e.preventDefault();
        const email = (document.getElementById('ss-email').value || '').trim();
        if (!email) { show('err','Please enter your email'); return; }
        btn.disabled = true; btn.textContent = 'Sending...';
        try{
          const res = await fetch('/social-services/forgot-password', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-Token': (document.querySelector('meta[name="csrf-token"]')?.content || '')
            },
            body: JSON.stringify({ email })
          });
          const data = await res.json().catch(()=>({}));
          if (res.ok && data && data.success){
            show('ok', data.message || 'If an account exists, a reset link has been sent.');
            if (data.reset_link) {
              // Optionally auto-redirect after short delay
              setTimeout(()=>{ window.location.href = data.reset_link; }, 1200);
            }
          } else {
            show('err', data.error || 'Failed to send reset link');
          }
        } catch (err){
          show('err','Network error: ' + (err?.message || 'Please try again'));
        } finally {
          btn.disabled = false; btn.textContent = 'Send Reset Link';
        }
      });
    })();
  </script>
</body>
</html>
