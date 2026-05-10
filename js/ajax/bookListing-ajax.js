document.addEventListener('DOMContentLoaded', function () {
  const searchInput = document.getElementById('search-input');
  const grid = document.querySelector('.book-grid');
  const modalForm = document.querySelector('form[action="bookListing.php"][enctype]');

  function createCard(book) {
    const div = document.createElement('div');
    div.className = 'book-card';
    div.setAttribute('data-id', book.id);

    const buttons = document.createElement('div');
    buttons.className = 'buttons';
    buttons.style.display = 'flex';
    buttons.style.gap = '5px';

    if (book.status && book.status === 'sold') {
      const sold = document.createElement('span');
      sold.style.cssText = 'background: red; color: white; padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 12px; display:inline-block; font-family: Arial Black, sans-serif;';
      sold.textContent = 'SOLD';
      buttons.appendChild(sold);
    } else {
      const edit = document.createElement('a');
      edit.href = 'bookListing.php?edit=' + encodeURIComponent(book.id);
      edit.style.cssText = 'background:#FF006E; color:#fff; padding:5px 10px; border-radius:5px; font-size: 13.3333px; text-decoration:none; display:inline-block;';
      edit.textContent = 'Edit';
      buttons.appendChild(edit);
    }

    const delForm = document.createElement('form');
    delForm.style.display = 'inline';
    delForm.innerHTML = '<input type="hidden" name="action" value="delete_book"><input type="hidden" name="book_id" value="' + book.id + '"><button type="submit" class="del-btn-php" style="background:#FF006E; color:#fff; border:none; padding:5px 10px; border-radius:5px; cursor:pointer; font-size: 13.3333px;">Delete</button>';

    // Intercept delete submit
    delForm.addEventListener('submit', function (e) {
      e.preventDefault();
      if (!confirm('Are you sure you want to delete this book?')) return;
      var fd = new FormData(delForm);
      var xhr = new XMLHttpRequest();
      xhr.open('POST', 'bookListing.php');
      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
      xhr.responseType = 'json';
      xhr.onload = function () {
        var data = xhr.response;
        if (!data) {
          try { data = JSON.parse(xhr.responseText || '{}'); } catch (e) { data = null; }
        }
        if (data && data.success) {
          var toRemove = grid.querySelector('.book-card[data-id="' + data.book_id + '"]');
          if (toRemove) toRemove.remove();
        } else {
          alert(data && data.message ? data.message : 'Delete failed');
        }
      };
      xhr.onerror = function () { console.error('Delete XHR error'); alert('Network error'); };
      xhr.send(fd);
    });

    buttons.appendChild(delForm);

    const imgWrap = document.createElement('div');
    imgWrap.className = 'img-placeholder';
    const img = document.createElement('img');
    img.src = book.image_path || '../images/1984.png';
    img.alt = 'Book-img';
    imgWrap.appendChild(img);

    const p = document.createElement('p');
    p.className = 'book-title';
    p.innerHTML = (book.title ? escapeHtml(book.title) : '') + '<br>' + (book.description ? escapeHtml(book.description) : '') + '<br>' + (book.price ? '\u20B9' + escapeHtml(book.price) : '');

    div.appendChild(buttons);
    div.appendChild(imgWrap);
    div.appendChild(p);
    return div;
  }

  function escapeHtml(str) {
    return (str + '').replace(/[&<>"']/g, function (c) {
      return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c];
    });
  }

  // Debounced search
  let timeout = null;
  if (searchInput) {
    searchInput.addEventListener('input', function () {
      clearTimeout(timeout);
      timeout = setTimeout(function () {
        const q = searchInput.value.trim();
          var xhr = new XMLHttpRequest();
          var url = 'bookListing.php?ajax=1&q=' + encodeURIComponent(q);
          xhr.open('GET', url);
          xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
          xhr.onload = function () {
            var data = null;
            var txt = xhr.responseText || '';
            try { data = JSON.parse(txt || '{}'); } catch (e) { data = null; }
            if (!data || !data.success) return;
            var addCard = grid.querySelector('.add-card');
            grid.innerHTML = '';
            if (addCard) grid.appendChild(addCard);
            data.books.forEach(function (b) {
              var card = createCard(b);
              grid.appendChild(card);
            });
          };
          xhr.onerror = function () { console.error('Search XHR error'); };
          xhr.send();
      }, 300);
    });
  }

  // Intercept modal add/edit form
  if (modalForm) {
    modalForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var fd = new FormData(modalForm);
      var xhr = new XMLHttpRequest();
      xhr.open('POST', 'bookListing.php');
      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
      xhr.onload = function () {
        var data = null;
        var txt = xhr.responseText || '';
        try { data = JSON.parse(txt || '{}'); } catch (e) { data = null; }
        if (!data) return;
        if (data.success && data.book) {
          var existing = grid.querySelector('.book-card[data-id="' + data.book.id + '"]');
          var card = createCard({
            id: data.book.id,
            title: data.book.title,
            description: data.book.description,
            price: data.book.price,
            image_path: data.book.image_path
          });
          if (existing) {
            existing.replaceWith(card);
          } else {
            var addCard = grid.querySelector('.add-card');
            if (addCard) grid.insertBefore(card, addCard.nextSibling);
            else grid.prepend(card);
          }
          var backdrop = document.getElementById('modal-backdrop');
          var modal = document.getElementById('book-modal');
          if (backdrop) backdrop.style.display = 'none';
          if (modal) modal.style.display = 'none';
        } else {
          alert(data && data.message ? data.message : 'Save failed');
        }
      };
      xhr.onerror = function () { console.error('Save XHR error'); alert('Network error'); };
      xhr.send(fd);
    });
  }

});
