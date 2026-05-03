document.addEventListener('DOMContentLoaded', function () {
  const form = document.querySelector('form[action="createAccount.php"]');
  if (!form) return;

  const messageEl = document.getElementById('ajax-message');
  const submitButton = form.querySelector('button[type="submit"]');

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    // simple client-side required check (browser also enforces)
    const username = form.querySelector('input[name="username"]').value.trim();
    const email = form.querySelector('input[name="email"]').value.trim();
    const password = form.querySelector('input[name="password"]').value;
    if (!username || !email || !password) {
      if (messageEl) messageEl.textContent = 'All fields are required.';
      return;
    }

    const fd = new FormData(form);

    if (submitButton) {
      submitButton.disabled = true;
      submitButton.textContent = 'Creating...';
    }
    if (messageEl) {
      messageEl.textContent = 'Creating account...';
    }
    // no special UI: using native alerts

    // Use XMLHttpRequest
    var xhr = new XMLHttpRequest();
    xhr.open(form.method || 'POST', form.action);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.onload = function () {
      var data = null;
      var txt = xhr.responseText || '';
      try { data = JSON.parse(txt || '{}'); } catch (e) { data = { success: false, message: 'Unexpected server response' }; }
      if (data.success) {
        if (data.redirect) { window.location.href = data.redirect; return; }
        if (messageEl) messageEl.textContent = 'Account created.';
        alert('Account created');
      } else {
        if (messageEl) messageEl.textContent = data.message || 'Error creating account.';
        alert(data.message || 'Error creating account');
      }
      if (submitButton) { submitButton.disabled = false; submitButton.textContent = 'Create Account'; }
    };
    xhr.onerror = function () {
      if (messageEl) messageEl.textContent = 'Network error.';
      alert('Network error');
      if (submitButton) { submitButton.disabled = false; submitButton.textContent = 'Create Account'; }
      console.error('CreateAccount AJAX error');
    };
    xhr.send(fd);
  });
});
