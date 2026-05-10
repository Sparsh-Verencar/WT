$(document).ready(function() {
    $('form').hide();
    $('form').fadeIn(800);
    
    setInterval(function() {
        $('.form-badge').animate({ 
            'font-size': '3.8rem'
        }, 300, function() {
            $('.form-badge').animate({ 
                'font-size': '3.5rem'
            }, 300);
        });
    }, 1500);
    
    $('input').on('focus', function() {
        $(this).animate({ 
            'transform': 'scale(1.05)'
        }, 200);
    });
    
    $('input').on('blur', function() {
        $(this).animate({ 
            'transform': 'scale(1)'
        }, 200);
    });
    
    $('button').on('click', function(e) {
        e.preventDefault();
        
        const usernameInput = $('#Username-input');
        const passwordInput = $('input[name="password"]');
        
        if (!usernameInput.val() || !passwordInput.val()) {
            var currentLeft = $('form').position().left;
            for(var i = 0; i < 5; i++) {
                $('form').animate({
                    'margin-left': '-10px'
                }, 50).animate({
                    'margin-left': '10px'
                }, 50);
            }
            $('form').animate({
                'margin-left': '0px'
            }, 50);
            return;
        }
        
        const button = $(this);
        button.text('Logging in...');
        button.prop('disabled', true);
    });
});
