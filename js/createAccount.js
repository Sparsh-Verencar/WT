$(document).ready(function() {
    // Hide form initially
    $('form').hide();
    
    // Fade in form
    $('form').fadeIn(800);
    
    // Badge rotate and scale animation
    setInterval(function() {
        $('.form-badge').animate({ 
            'font-size': '3.3rem'
        }, 300, function() {
            $('.form-badge').animate({ 
                'font-size': '3rem'
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
        
        const usernameInput = $('input[name="username"]');
        const emailInput = $('input[name="email"]');
        const passwordInput = $('input[name="password"]');
        
        // Validate inputs
        if (!usernameInput.val() || !emailInput.val() || !passwordInput.val()) {
            // Form shake animation
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
        button.text('Creating Account...');
        button.prop('disabled', true);
        
        // Simulate account creation, then navigate
        setTimeout(function() {
            window.location.href = 'myaccountpage.html';
        }, 1500);
    });
});
