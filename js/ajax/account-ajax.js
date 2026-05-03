document.addEventListener('DOMContentLoaded', function () {
  // Intercept any anchor that points to php/logout.php
  document.querySelectorAll('a[href*="php/logout.php"]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      e.preventDefault();
      if (!confirm('Logout?')) return;
      // Use XMLHttpRequest for logout
      var xhr = new XMLHttpRequest();
      xhr.open('GET', a.href);
      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
      xhr.onload = function () {
        var data = null;
        var txt = xhr.responseText || '';
        try { data = JSON.parse(txt || '{}'); } catch (e) { data = null; }
        if (data && data.success) {
          if (data.redirect) window.location.href = data.redirect; else location.reload();
        } else {
          alert(data && data.message ? data.message : 'Logout failed');
        }
      };
      xhr.onerror = function () { console.error('Logout XHR error'); alert('Network error'); };
      xhr.send();
    });
  });

  // Intercept forms that post to deleteAccount.php
  document.querySelectorAll('form[action*="deleteAccount.php"]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (!confirm('Are you sure you want to delete your account? This will remove all your data.')) return;
      const fd = new FormData(form);
      // Use XMLHttpRequest for delete account
      var xhr2 = new XMLHttpRequest();
      xhr2.open('POST', form.action);
      xhr2.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
      xhr2.onload = function () {
        var data = null;
        var txt = xhr2.responseText || '';
        try { data = JSON.parse(txt || '{}'); } catch (e) { data = null; }
        if (data && data.success) {
          if (data.redirect) window.location.href = data.redirect; else window.location.href = '/';
        } else {
          alert(data && data.message ? data.message : 'Delete failed');
        }
      };
      xhr2.onerror = function () { console.error('DeleteAccount XHR error'); alert('Network error'); };
      xhr2.send(fd);
    });
  });
});
