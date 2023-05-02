<?php
/*********************************************************************************************************************************
*
*     PAY ATTENTION: As this file is edited manually, it is recommended create a backup
*     of it, to prevent lose your code in future updates. The plugin includes its own backup
*     mechanisms, but it can fail depending on your web sever settings.
*
*	  An alternative to keeping the file safe is to create a copy in
*     wp-content/uploads/calculated-field-form/server-side-equations_1.php
*     and the plugin would use this copy instead.
*
*     Structure of server side equations:
*
*     $GLOBALS['SERVER_SIDE_EQUATIONS']['<equation name>'] = function(<equation parameters>){<equation code>};
*
*     Remember you should programming the equations using PHP Script and not Javascript,
*     these equations are executed in the server side.
*
*********************************************************************************************************************************/

$GLOBALS['SERVER_SIDE_EQUATIONS'] = array();

/********* INCLUDE YOUR CODE FROM HERE **************/

$GLOBALS['SERVER_SIDE_EQUATIONS']['equation_1'] = function($p1,$p2){return @floatval($p1)+@floatval($p2);}; // Equation to add two numbers
$GLOBALS['SERVER_SIDE_EQUATIONS']['equation_2'] = function($p1,$p2){return @floatval($p1)*@floatval($p2);}; // Equation to multiply two numbers