$(document).ready(function () {

    /* ── localStorage helpers ─────────────────── */
    function getBooks() {
        return JSON.parse(localStorage.getItem('books') || '[]');
    }

    function saveBooks(books) {
        localStorage.setItem('books', JSON.stringify(books));
    }

    /* ── Build a card HTML string ─────────────── */
    function buildCard(book) {
        var imgTag = book.image
            ? '<img src="' + book.image + '" alt="Book-img">'
            : '<img src="../images/1984.png" alt="Book-img">';

        var authorGenre = '';
        if (book.author || book.genre) {
            authorGenre = '<small style="color:#888;">';
            if (book.author) authorGenre += 'By ' + escHtml(book.author);
            if (book.author && book.genre) authorGenre += ' • ';
            if (book.genre) authorGenre += escHtml(book.genre);
            authorGenre += '</small><br>';
        }

        return $(
            '<div class="book-card" data-id="' + book.id + '">' +
                '<div class="buttons">' +
                    '<button class="edit-btn">Edit</button>' +
                    '<button class="del-btn">Delete</button>' +
                '</div>' +
                '<div class="img-placeholder">' + imgTag + '</div>' +
                '<p class="book-title">' +
                    escHtml(book.name) + '<br>' +
                    authorGenre +
                    escHtml(book.description) + '<br>' +
                    '\u20B9' + escHtml(book.price) +
                '</p>' +
            '</div>'
        );
    }

    function escHtml(str) {
        return $('<div>').text(str).html();
    }

    /* ── Render all cards from storage ───────── */
    function renderCards() {
        var books = getBooks();
        $('.book-card').remove();          // clear existing cards
        var $addCard = $('.add-card');
        books.forEach(function (book) {
            $addCard.after(buildCard(book));
        });
    }

    /* ── Seed static cards into localStorage (first visit only) ── */
    if (!localStorage.getItem('books')) {
        var seed = [];
        // The 6 static cards already in the HTML will be replaced by a clean slate.
        // Seed with 0 books so the user starts fresh (the static markup is removed on render).
        saveBooks(seed);
    }
    renderCards();

    /* ── ADD modal open ───────────────────────── */
    $(document).on('click', '.add-card', function () {
        openModal('add');
    });

    /* ── EDIT modal open ──────────────────────── */
    $(document).on('click', '.edit-btn', function (e) {
        e.stopPropagation();
        var $card = $(this).closest('.book-card');
        var id = $card.data('id');
        var books = getBooks();
        var book = books.find(function (b) { return b.id === id; });
        if (book) { openModal('edit', book); }
    });

    /* ── DELETE ───────────────────────────────── */
    $(document).on('click', '.del-btn', function (e) {
        e.stopPropagation();
        var $card = $(this).closest('.book-card');
        var id = $card.data('id');
        var books = getBooks().filter(function (b) { return b.id !== id; });
        saveBooks(books);
        $card.fadeOut(180, function () { $(this).remove(); });
    });

    /* ── SEARCH ───────────────────────────────── */
    $('.search-bar input').on('input', function () {
        var q = $(this).val().toLowerCase().trim();
        $('.book-card').each(function () {
            var text = $(this).find('.book-title').text().toLowerCase();
            $(this).toggle(!q || text.indexOf(q) !== -1);
        });
    });

    /* ── Modal plumbing ───────────────────────── */
    var $modal = $('#book-modal');
    var editingId = null;
    var currentImageData = null;

    function openModal(mode, book) {
        editingId = (mode === 'edit') ? book.id : null;
        currentImageData = (mode === 'edit') ? (book.image || null) : null;

        $('#modal-title').text(mode === 'add' ? 'Add Book' : 'Edit Book');
        $('#input-name').val(mode === 'edit' ? book.name : '');
        $('#input-author').val(mode === 'edit' ? (book.author || '') : '');
        $('#input-genre').val(mode === 'edit' ? (book.genre || '') : '');
        $('#input-desc').val(mode === 'edit' ? book.description : '');
        $('#input-price').val(mode === 'edit' ? book.price : '');
        $('#input-image').val('');

        if (mode === 'edit' && book.image) {
            $('#modal-img-preview').attr('src', book.image).show();
        } else {
            $('#modal-img-preview').hide().attr('src', '');
        }

        $modal.fadeIn(200);
    }

    /* Image preview */
    $('#input-image').on('change', function () {
        var file = this.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            currentImageData = e.target.result;
            $('#modal-img-preview').attr('src', currentImageData).show();
        };
        reader.readAsDataURL(file);
    });

    /* Close modal */
    $('#modal-close, #modal-backdrop').on('click', function () {
        $modal.fadeOut(180);
    });

    /* Save */
    $('#modal-save').on('click', function () {
        var name   = $.trim($('#input-name').val());
        var author = $.trim($('#input-author').val());
        var genre  = $.trim($('#input-genre').val());
        var desc   = $.trim($('#input-desc').val());
        var price  = $.trim($('#input-price').val());

        if (!name) { alert('Book name is required.'); return; }

        var books = getBooks();

        if (editingId) {
            /* Update existing */
            books = books.map(function (b) {
                if (b.id === editingId) {
                    return {
                        id: b.id,
                        name: name,
                        author: author,
                        genre: genre,
                        description: desc,
                        price: price,
                        image: currentImageData || b.image
                    };
                }
                return b;
            });
            saveBooks(books);
            /* Update card in-place */
            var updated = books.find(function (b) { return b.id === editingId; });
            var $card = $('.book-card[data-id="' + editingId + '"]');
            var authorGenre = '';
            if (updated.author || updated.genre) {
                authorGenre = '<small style="color:#888;">';
                if (updated.author) authorGenre += 'By ' + escHtml(updated.author);
                if (updated.author && updated.genre) authorGenre += ' • ';
                if (updated.genre) authorGenre += escHtml(updated.genre);
                authorGenre += '</small><br>';
            }
            $card.find('.book-title').html(
                escHtml(updated.name) + '<br>' +
                authorGenre +
                escHtml(updated.description) + '<br>' +
                '\u20B9' + escHtml(updated.price)
            );
            if (currentImageData) {
                $card.find('.img-placeholder img').attr('src', currentImageData);
            }
        } else {
            /* Add new */
            var newBook = {
                id: Date.now(),
                name: name,
                author: author,
                genre: genre,
                description: desc,
                price: price,
                image: currentImageData || null
            };
            books.push(newBook);
            saveBooks(books);
            var $newCard = buildCard(newBook);
            $newCard.hide().insertAfter('.add-card').fadeIn(200);
        }

        $modal.fadeOut(180);
    });
});
