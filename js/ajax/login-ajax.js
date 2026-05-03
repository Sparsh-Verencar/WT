document.addEventListener('DOMContentLoaded', function () {
  const form = document.querySelector('form[action="login.php"]');
  if (!form) return;

  const messageEl = document.getElementById('ajax-message');
  const submitButton = form.querySelector('button[type="submit"]');

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    const email = form.querySelector('input[name="email"]').value.trim();
    const password = form.querySelector('input[name="password"]').value;
    if (!email || !password) {
      if (messageEl) messageEl.textContent = 'All fields are required.';
      return;
    }

    const fd = new FormData(form);

    if (submitButton) {
      submitButton.disabled = true;
      submitButton.textContent = 'Logging in...';
    }
    if (messageEl) {
      messageEl.textContent = 'Logging in...';
    }
    // no special UI: using native alerts

    // Use XMLHttpRequest for AJAX
    var xhr = new XMLHttpRequest();
    xhr.open(form.method || 'POST', form.action);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.onload = function () {
      var data = null;
      var txt = xhr.responseText || '';
      try { data = JSON.parse(txt || '{}'); } catch (e) { data = { success: false, message: 'Unexpected server response' }; }
      if (!data) data = { success: false, message: 'Empty response' };
      if (!data) data = { success: false, message: 'Empty response' };
      if (data.success) {
        if (data.redirect) { window.location.href = data.redirect; return; }
        if (messageEl) messageEl.textContent = 'Logged in.';
        alert('Logged in');
      } else {
        if (messageEl) messageEl.textContent = data.message || 'Login failed.';
        alert(data.message || 'Login failed');
      }
      if (submitButton) { submitButton.disabled = false; submitButton.textContent = 'Login'; }
    };
    xhr.onerror = function () {
      if (messageEl) messageEl.textContent = 'Network error.';
      alert('Network error');
      if (submitButton) { submitButton.disabled = false; submitButton.textContent = 'Login'; }
      console.error('Login AJAX error');
    };
    xhr.send(fd);
  });
});
