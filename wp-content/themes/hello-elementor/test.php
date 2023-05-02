<?php
    /**
    * Template Name: test page
    */


    echo "hello";

    $nonce = wp_create_nonce();
    echo "<br>".$nonce;
    echo "<br>".wp_create_nonce();
    echo "<br>".wp_nonce_url('https://www.totalmassagegun.com/wp-json/wc/store/cart/add-item');
?>