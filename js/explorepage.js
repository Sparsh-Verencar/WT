$(document).ready(function () {

    /* ── localStorage helpers ─────────────────── */
    function getBooks() {
        return JSON.parse(localStorage.getItem('books') || '[]');
    }

    function getOrders() {
        return JSON.parse(localStorage.getItem('orders') || '[]');
    }

    function saveOrders(orders) {
        localStorage.setItem('orders', JSON.stringify(orders));
    }

    /* ── Build explore card ───────────────────── */
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
        return $('<div>').text(String(str)).html();
    }

    /* ── Render books from localStorage ─────── */
    function renderCards() {
        var books = getBooks();
        var $grid = $('.book-grid');
        $grid.empty();
        if (books.length === 0) {
            $grid.append('<p style="color:#FFA500;font-weight:900;font-family:Arial Black,sans-serif;padding:1rem;">No books listed yet. Add some from the Book Listing page!</p>');
            return;
        }
        books.forEach(function (book) {
            $grid.append(buildCard(book));
        });
    }

    renderCards();

    /* ── Card click → detail modal ──────────── */
    $(document).on('click', '.book-card', function () {
        var id = $(this).data('id');
        var books = getBooks();
        var book = books.find(function (b) { return b.id === id; });
        if (!book) return;

        $('#detail-img').attr('src', book.image || '../images/1984.png');
        $('#detail-name').text(book.name);
        
        var authorGenreText = '';
        if (book.author || book.genre) {
            authorGenreText = '';
            if (book.author) authorGenreText += 'By ' + book.author;
            if (book.author && book.genre) authorGenreText += ' • ';
            if (book.genre) authorGenreText += book.genre;
            authorGenreText += ' | ';
        }
        
        $('#detail-desc').text(authorGenreText + book.description);
        $('#detail-price').text('Price: \u20B9' + book.price);
        $('#detail-modal').data('current-book-id', id).fadeIn(200);
    });

    /* ── Close modal ─────────────────────────── */
    $('#detail-close, #detail-backdrop').on('click', function () {
        $('#detail-modal').fadeOut(180);
    });

    /* ── Buy ──────────────────────────────────── */
    $('#detail-buy').on('click', function () {
        var id = $('#detail-modal').data('current-book-id');
        var books = getBooks();
        var book = books.find(function (b) { return b.id === id; });
        if (!book) return;

        var orders = getOrders();
        // Avoid duplicate orders
        var alreadyOrdered = orders.some(function (o) { return o.id === id; });
        if (!alreadyOrdered) {
            orders.push(book);
            saveOrders(orders);
        }
        window.location.href = '../pages/myorder.html';
    });

    /* ── Search filter ────────────────────────── */
    $('.search-bar input').on('input', function () {
        var q = $(this).val().toLowerCase().trim();
        $('.book-card').each(function () {
            var text = $(this).find('.book-title').text().toLowerCase();
            $(this).toggle(!q || text.indexOf(q) !== -1);
        });
    });
});
