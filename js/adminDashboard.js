$(document).ready(function() {
    // Add simple animations to cards
    $('.stat-card').hover(
        function() {
            $(this).animate({ 'transform': 'scale(1.05)', 'marginTop': '-5px' }, 200);
        },
        function() {
            $(this).animate({ 'transform': 'scale(1)', 'marginTop': '0px' }, 200);
        }
    );
});
