<?php
/* Template Name: PayNow */ 
get_header();


?>
<div class="row">
<div class="col-md-12">
<?php
 echo do_shortcode('[accept_stripe_payment name="registration" price="25.00" ]'); 
the_content();
?>
</div>
</div>

<?php

get_footer();

?>