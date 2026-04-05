$(document).ready(function() {
    // Hide elements initially
    $('#nav, #header, #header h1, #header h3, #cta').hide();
    
    // Staggered fade in with faster timings
    $('#nav').fadeIn(500);
    
    $('#header').delay(200).fadeIn(500);
    
    $('#header h1').delay(500).fadeIn(500);
    
    $('#header h3').delay(800).fadeIn(500);
    
    $('#cta').delay(1100).fadeIn(500);
});
