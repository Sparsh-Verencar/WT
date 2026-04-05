$(document).ready(function() {
    // Hide form initially
    $('form').hide();
    
    // Fade in form
    $('form').fadeIn(800);
    
    // Badge pulse and bounce animation
    setInterval(function() {
        $('.form-badge').animate({ 
            'font-size': '3.8rem'
        }, 300, function() {
            $('.form-badge').animate({ 
                'font-size': '3.5rem'
            }, 300);
        });
    }, 1500);
    
    // Input focus scale effect
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
    
    // Button click animation with form validation
    $('button').on('click', function(e) {
        e.preventDefault();
        
        const emailInput = $('#Email-input');
        const passwordInput = $('input[name="password"]');
        
        // Validate inputs
        if (!emailInput.val() || !passwordInput.val()) {
            // Form shake animation
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
        
        // Add loading state to button
        const button = $(this);
        button.text('Logging in...');
        button.prop('disabled', true);
        
        // Simulate login process, then navigate
        setTimeout(function() {
            window.location.href = 'myaccountpage.html';
        }, 1500);
    });
});
