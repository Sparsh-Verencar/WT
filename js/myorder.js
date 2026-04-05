$(document).ready(function () {

    /* ── localStorage helpers ─────────────────── */
    function getOrders() {
        return JSON.parse(localStorage.getItem('orders') || '[]');
    }

    function escHtml(str) {
        return $('<div>').text(String(str)).html();
    }

    /* ── Build order card ─────────────────────── */
    function buildCard(book) {
        var imgTag = book.image
            ? '<img src="' + book.image + '" alt="Book-img">'
            : '<img src="../images/1984.png" alt="Book-img">';

        return $(
            '<div class="book-card" data-id="' + book.id + '">' +
                '<div class="img-placeholder">' + imgTag + '</div>' +
                '<p class="book-title">' +
                    escHtml(book.name) + '<br>' +
                    escHtml(book.description) + '<br>' +
                    escHtml(book.price) +
                '</p>' +
            '</div>'
        );
    }

    /* ── Render orders ───────────────────────── */
    function renderOrders() {
        var orders = getOrders();
        var $grid = $('.book-grid');
        $grid.empty();

        if (orders.length === 0) {
            $grid.append('<p style="color:#FFA500;font-weight:900;font-family:Arial Black,sans-serif;padding:1rem;">No orders yet. Buy books from the Explore page!</p>');
            return;
        }

        orders.forEach(function (book) {
            $grid.append(buildCard(book));
        });
    }

    renderOrders();

    /* ── Search filter ────────────────────────── */
    $('.search-bar input').on('input', function () {
        var q = $(this).val().toLowerCase().trim();
        $('.book-card').each(function () {
            var text = $(this).find('.book-title').text().toLowerCase();
            $(this).toggle(!q || text.indexOf(q) !== -1);
        });
    });
});
