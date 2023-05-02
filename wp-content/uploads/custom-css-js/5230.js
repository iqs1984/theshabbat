<!-- start Simple Custom CSS and JS -->
<script type="text/javascript">

if($.cookie('popup') != 'seen'){
    $.cookie('popup', 'seen', { expires: 365, path: '/' }); // Set it to last a year, for example.
    $j("#popup").delay(2000).fadeIn();
    $j('#popup-close').click(function(e) // You are clicking the close button
        {
        $j('#popup').fadeOut(); // Now the pop up is hiden.
    });
    $j('#popup').click(function(e) 
        {
        $j('#popup').fadeOut(); 
    });
};
</script>
<!-- end Simple Custom CSS and JS -->
