<?php
/*
Plugin Name: Calculated Fields Form
Plugin URI: https://cff.dwbooster.com
Description: Create forms with field values calculated based in other form field values.
Version: 10.1.166
Text Domain: calculated-fields-form
Author: CodePeople
Author URI: https://cff.dwbooster.com
License: https://cff.dwbooster.com/terms
*/

if(!defined('WP_DEBUG') || true != WP_DEBUG)
{
	error_reporting(E_ERROR|E_PARSE);
}

require_once 'inc/cpcff_session.inc.php';
// Start Session
if(get_option('CP_CALCULATEDFIELDSF_EARLY_SESSION', 0)*1) CP_SESSION::session_start();

// Defining main constants
define('CP_CALCULATEDFIELDSF_VERSION', '10.1.166' );
define('CP_CALCULATEDFIELDSF_MAIN_FILE_PATH', __FILE__ );
define('CP_CALCULATEDFIELDSF_BASE_PATH', dirname( CP_CALCULATEDFIELDSF_MAIN_FILE_PATH ) );
define('CP_CALCULATEDFIELDSF_BASE_NAME', plugin_basename( CP_CALCULATEDFIELDSF_MAIN_FILE_PATH ) );

require_once 'inc/cpcff_auxiliary.inc.php';
require_once 'config/cpcff_config.cfg.php';

require_once 'inc/cpcff_banner.inc.php';
require_once 'inc/cpcff_main.inc.php';

// Global variables
CPCFF_MAIN::instance(); // Main plugin's object

require_once 'inc/cpcff_auto_update.inc.php'; // Checks the updates
require_once 'inc/cpcff_discounts.inc.php';
require_once 'inc/cpcff_data_source.inc.php';
require_once 'inc/cpcff_form_cache.inc.php';

add_action( 'init', 'cp_calculated_fields_form_check_posted_data', 1 );
add_action( 'init', 'cp_calculated_fields_form_direct_form_access', 1 );

// functions
//------------------------------------------
function cp_calculated_fields_form_direct_form_access()
{
	if(
		get_option('CP_CALCULATEDFIELDSF_DIRECT_FORM_ACCESS', CP_CALCULATEDFIELDSF_DIRECT_FORM_ACCESS) &&
		!empty($_GET['cff-form']) &&
		@intval($_GET['cff-form'])
	)
	{
		$cpcff_main = CPCFF_MAIN::instance();
		$cpcff_main->form_preview(
			array(
				'shortcode_atts' => array('id' => @intval($_GET['cff-form'])),
				'page_title' => 'CFF',
				'page' => true
			)
		);
	}
} // End cp_calculated_fields_form_direct_form_access

function cp_calculated_fields_form_check_posted_data() {

    global $wpdb;

	$cpcff_main = CPCFF_MAIN::instance();

	if(!empty($_GET['cp_calculatedfieldsf_ipncheck']))
		cp_calculatedfieldsf_check_IPN_verification($_GET['cp_calculatedfieldsf_ipncheck']);

    if (isset( $_GET['cp_calculatedfieldsf'] ) && $_GET['cp_calculatedfieldsf'] == 'captcha' )
    {
        @include_once dirname( __FILE__ ) . '/captcha/captcha.php';
		remove_all_actions('shutdown');
        exit;
    }

    if (isset( $_GET['cp_calculatedfieldsf_csv'] ) && is_admin() )
    {
		check_admin_referer( 'cff-submissions-list', '_cpcff_nonce' );
        cp_calculatedfieldsf_export_csv();
        return;
    }

    if (isset( $_GET['cp_calculatedfieldsf_export'] ) && is_admin() )
    {
		check_admin_referer( 'cff-export-form', '_cpcff_nonce' );
		$cpcff_main->get_form(intval(@$_GET['name']))->export_form();
		return;
    }

    if ( isset($_SERVER['REQUEST_METHOD']) && 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_POST['cp_calculatedfieldsf_post_options'] ) && is_admin() )
    {
        cp_calculatedfieldsf_save_options();
        if(
			isset($_POST['preview']) &&
			isset($_POST['cp_calculatedfieldsf_id'])
		)
		{
			$cpcff_main->form_preview(
				array(
					'shortcode_atts' => array('id' => @intval($_POST['cp_calculatedfieldsf_id'])),
					'page_title' => __('Form Preview', 'calculated-fields-form'),
					'wp_die' => 1
				)
			);
		}
		return;
    }

	if ( ( isset($_SERVER['REQUEST_METHOD']) && 'POST' != $_SERVER['REQUEST_METHOD'] ) || ! isset( $_POST['cp_calculatedfieldsf_pform_process'] ) )
	    if ( ( isset($_SERVER['REQUEST_METHOD']) && 'GET' != $_SERVER['REQUEST_METHOD'] ) || !isset( $_GET['hdcaptcha_cp_calculated_fields_form_post'] ) )
		    return;

	if(function_exists('wp_doing_ajax') && wp_doing_ajax() && apply_filters( 'cff_no_ajax', true ) ) return;

    define("CP_CALCULATEDFIELDSF_ID",@$_POST["cp_calculatedfieldsf_id"]);
	$form_obj = $cpcff_main->get_form(CP_CALCULATEDFIELDSF_ID);

	$sequence = '';
    if (isset($_GET["ps"]))
		$sequence = CPCFF_AUXILIARY::sanitize($_GET["ps"]);
	elseif(isset($_POST["cp_calculatedfieldsf_pform_psequence"]))
		$sequence = CPCFF_AUXILIARY::sanitize($_POST["cp_calculatedfieldsf_pform_psequence"]);

    if (!isset($_GET['hdcaptcha_cp_calculated_fields_form_post']) || $_GET['hdcaptcha_cp_calculated_fields_form_post'] == '') $_GET['hdcaptcha_cp_calculated_fields_form_post'] = isset($_POST['hdcaptcha_cp_calculated_fields_form_post']) ? $_POST['hdcaptcha_cp_calculated_fields_form_post'] : NULL;

    if (
			/**
			 * Filters applied for checking if the form's submission is valid or not
			 * returns a boolean
			 */
			!apply_filters( 'cpcff_valid_submission', true) ||
			(
				($form_obj->get_option('cv_enable_captcha', CP_CALCULATEDFIELDSF_DEFAULT_cv_enable_captcha) != 'false') &&
				( (strtolower($_GET['hdcaptcha_cp_calculated_fields_form_post']) != strtolower(CP_SESSION::get_var('rand_code'.$sequence))) ||
				  (CP_SESSION::get_var('rand_code'.$sequence) == '') )
			)
       )
    {
        echo 'captchafailed';
		remove_all_actions('shutdown');
        exit;
    }

	// if this isn't the real post (it was the captcha verification) then echo ok and exit
    if ( 'POST' != $_SERVER['REQUEST_METHOD'] || ! isset( $_POST['cp_calculatedfieldsf_pform_process'] ) )
	{
	    echo 'ok';
		remove_all_actions('shutdown');
        exit;
	}

	// Defines the $params array
	$params = array(
		'formid'   => CP_CALCULATEDFIELDSF_ID
	);

	// Check the honeypot
	if( ( $honeypot = get_option( 'CP_CALCULATEDFIELDSF_HONEY_POT', '' ) ) !== '' && !empty( $_REQUEST[ $honeypot ] ) )
	{
		exit;
	}

	// Check the nonce
	// Filters applied to decide if the nonce should be checked or not
	if (
		@intval(apply_filters('cpcff_check_nonce', get_option('CP_CALCULATEDFIELDSF_NONCE', false))) &&
		(
			empty($_REQUEST['_cpcff_public_nonce']) ||
			!wp_verify_nonce($_REQUEST['_cpcff_public_nonce'], 'cpcff_form_'.CP_CALCULATEDFIELDSF_ID.$sequence)
		)
	)
	{
		_e( 'Failed security check', 'calculated-fields-form' );
		exit;
	}

    // get form info
    //---------------------------
    $paypal_zero_payment = $form_obj->get_option('paypal_zero_payment',CP_CALCULATEDFIELDSF_DEFAULT_PAYPAL_ZERO_PAYMENT);
    require_once(ABSPATH . "wp-admin" . '/includes/file.php');

	$form_data = $form_obj->get_option( 'form_structure', CP_CALCULATEDFIELDSF_DEFAULT_form_structure );
	$fields = array();
	$choicesTxt = array();	   // List of choices texts in fields where exits
    $choicesVal = array(); // List of choices vals  in fields where exits

    foreach ($form_data[0] as $item)
        //if (!isset($item->hidefield) ||$item->hidefield != '1')
        {
            $fields[$item->name] = $item;
			if( property_exists( $item, 'choicesVal' ) && property_exists( $item, 'choices' ) )
			{
				$choicesTxt[$item->name] = $item->choices;
				$choicesVal[$item->name] = $item->choicesVal;
			}

            if ($item->ftype == 'fPhone' && isset($_POST[$item->name.$sequence])) // join fields for phone fields
            {
				$_POST[$item->name.$sequence] = '';
                for($i=0; $i<=substr_count($item->dformat," ")+($item->countryComponent ? 1 : 0); $i++)
                {
                    $_POST[$item->name.$sequence] .= CPCFF_AUXILIARY::sanitize($_POST[$item->name.$sequence."_".$i]!=''?($i==0?'':'-').$_POST[$item->name.$sequence."_".$i]:'');
                    unset($_POST[$item->name.$sequence."_".$i]);
                }
            }
        }

	// grab posted data
    //---------------------------
    $buffer = "";

    foreach ($_POST as $item => $value)
	{
		$fieldname = str_replace($sequence,'',$item);
        if ( array_key_exists($fieldname, $fields) )
        {
			// Check if the field is required and it is empty
			if(
				property_exists($fields[$fieldname],'required') &&
				!empty($fields[$fieldname]->required) &&
				$value === ''
			)
			{
				_e('At least a required field is empty', 'calculated-fields-form');
				exit;
			}

			// Processing the title and value to include in the summary
			$_title = $fields[$fieldname]->title;
			$_value = CPCFF_AUXILIARY::sanitize(is_array($value)?(implode(", ",$value)):($value));
			$_title = preg_replace( array('/^\s+/', '/\s*\:*\s*$/'), '', $_title);
			$_value = preg_replace( '/^\s*\:*\s*/', '', $_value);

            $buffer .= stripcslashes($_title . ": ". $_value) . "\n"; // FROM \n\n to \n
			$value = (is_array($value)) ? array_map('stripcslashes', $value) : stripcslashes($value);
            $params[$fieldname] = (is_array($value)) ? array_map('CPCFF_AUXILIARY::sanitize', $value) : CPCFF_AUXILIARY::sanitize($value);
        }
	}

	foreach ($_FILES as $item => $value)
	{
		$item = str_replace( $sequence,'',$item );
		if(isset($fields[$item]) &&  ( $fields[$item]->ftype == 'ffile' || $fields[$item]->ftype == 'frecordav' ))
        {
			$files_names_arr = array();
			$files_links_arr = array();
			$files_urls_arr  = array();
			for( $f = 0; $f < count( $value[ 'name' ] ); $f++ )
			{
				if( !empty( $value[ 'name' ][ $f ] ) )
				{
					$uploaded_file = array(
						'name' 		=> $value[ 'name' ][ $f ],
						'type' 		=> $value[ 'type' ][ $f ],
						'tmp_name' 	=> $value[ 'tmp_name' ][ $f ],
						'error' 	=> $value[ 'error' ][ $f ],
						'size' 		=> $value[ 'size' ][ $f ],
					);
					if( CPCFF_AUXILIARY::check_uploaded_file( $uploaded_file ) )
					{
						$movefile = wp_handle_upload( $uploaded_file, array( 'test_form' => false ) );
						if ( empty( $movefile[ 'error' ] ) )
						{
							$files_links_arr[] = $movefile["file"];
							$files_urls_arr[]  = $movefile["url"];
							$files_names_arr[] = sanitize_text_field($uploaded_file[ 'name' ]);

							/**
							 * Action called when the file is uploaded, the file's data is passed as parameter
							 */
							do_action(
								'cpcff_file_uploaded',
								$movefile,
								array(
									'names' => &$files_names_arr,
									'links' => &$files_links_arr,
									'urls'  => &$files_urls_arr,
                                    'formid'=> $form_obj->get_id(),
									'params'=> &$params,
									'item'  => $item,
									'index' => $f
								)
							);

							$params[ $item."_link" ][ $f ] = end($files_links_arr);
							$params[ $item."_url" ][ $f ]  = end($files_urls_arr);
						}
					}
				}
			}

			$joinned_files_names = implode( ", ", $files_names_arr );
			$buffer .= $fields[ $item ]->title . ": ". $joinned_files_names . "\n"; // FROM \n\n to \n
			$params[ $item ] = $joinned_files_names;
			$params[ $item."_links"] = implode( "\n",  $files_links_arr ); // FROM \n\n to \n
			$params[ $item."_urls"] = implode( "\n",  $files_urls_arr ); // FROM \n\n to \n
		}
	}

	if(count($params) < 2)
	{
		_e( 'The form is empty', 'calculated-fields-form' );
		exit;
	}

    $buffer_A = $buffer;

	$find_arr = array( ',', '.');
	$replace_arr = array( '', '.');
    $prefix = '';
    $suffix = '';

	// get base price
	$request_cost = $form_obj->get_option('request_cost', CP_CALCULATEDFIELDSF_DEFAULT_COST);
	if(
		!empty($request_cost) &&
		!empty($fields) &&
		!empty($fields[ $request_cost ])
	)
	{
		$price_item = $fields[ $request_cost ];

		if( $price_item->ftype == 'fCalculated' )
		{
            if(!empty($price_item->prefix)) $prefix = $price_item->prefix;
            if(!empty($price_item->suffix)) $suffix = $price_item->suffix;
			$find_arr[ 0 ] = $price_item->groupingsymbol;
			$find_arr[ 1 ] = $price_item->decimalsymbol;
		}
		elseif( $price_item->ftype == 'fcurrency' )
		{
            if(!empty($price_item->currencySymbol)) $prefix = $price_item->currencySymbol;
            if(!empty($price_item->currencyText)) $suffix = $price_item->currencyText;
			$find_arr[ 0 ] = $price_item->thousandSeparator;
			$find_arr[ 1 ] = $price_item->centSeparator;
		}
		elseif( $price_item->ftype == 'fnumber' || $price_item->ftype == 'fnumberds' )
		{
			$find_arr[ 0 ] = $price_item->thousandSeparator;
			$find_arr[ 1 ] = $price_item->decimalSymbol;
		}
	}

    $price = ! empty( $_POST[ $request_cost.$sequence ] ) ? @$_POST[ $request_cost.$sequence ] : 0;
    $price = preg_replace( array('/^'.preg_quote($prefix).'/','/'.preg_quote($suffix).'$/','/[^\d\.\,]/'), '', $price );
	$price = str_replace( $find_arr, $replace_arr, $price );
	$price = apply_filters('cpcff_price', $price, $params);
	$paypal_base_amount = preg_replace( '/[^\d\.\,]/', '', $form_obj->get_option( 'paypal_base_amount', 0 ) );
	$paypal_base_amount = str_replace( $find_arr, $replace_arr, $paypal_base_amount );
	$price = max( $price, $paypal_base_amount );

    // calculate discounts if any
    //---------------------------
	$price 			= CPCFF_COUPON::apply_discount(CP_CALCULATEDFIELDSF_ID, isset($_POST["couponcode"]) ? $_POST["couponcode"] : NULL, $price);
    $discount_note 	= CPCFF_COUPON::$discount_note;
    $coupon 		= CPCFF_COUPON::$coupon_applied;

    $params["final_price"] = $price;
    $params["couponcode"] = ($coupon?$coupon->code:"");
    $params["coupon"] = ($coupon?$coupon->code.$discount_note:"");

    if (!isset($_POST["bccf_payment_option_paypal"]) || @$_POST["bccf_payment_option_paypal"] == '0')
        $params["payment_option"] = $form_obj->get_option('enable_paypal_option_no',CP_CALCULATEDFIELDSF_PAYPAL_OPTION_NO);
    else if(@$_POST["bccf_payment_option_paypal"] == '1')
        $params["payment_option"] = $form_obj->get_option('enable_paypal_option_yes',CP_CALCULATEDFIELDSF_PAYPAL_OPTION_YES);

	$to = $form_obj->get_option('cu_user_email_field', CP_CALCULATEDFIELDSF_DEFAULT_cu_user_email_field);
	$to = explode( ',', $to );
	$to_arr = array();
	foreach( $to as $index => $value )
	{
		if(
			isset($params[$value]) &&
			($_email = trim($params[$value])) != ''
		) $to_arr[] = sanitize_email($_email);
	};

	$ipaddr = ('true' == $form_obj->get_option('fp_inc_additional_info', CP_CALCULATEDFIELDSF_DEFAULT_fp_inc_additional_info)) ? $_SERVER['REMOTE_ADDR'] : '';
	$params[ 'ipaddress' ] = $ipaddr;
    $params[ 'from_page' ] = !empty($_REQUEST['cp_ref_page']) ? $_REQUEST['cp_ref_page'] : (!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');
	$params['submissiondate_mmddyyyy'] = current_time('m/d/Y H:i:s');
	$params['submissiondate_ddmmyyyy'] = current_time('d/m/Y H:i:s');

	/**
	 * Action called before insert the data into database.
	 * To the function is passed an array with submitted data.
	 */
	do_action_ref_array( 'cpcff_process_data_before_insert', array(&$params, &$buffer_A, $fields) );

    // insert into database
    //---------------------------------
	$item_number = CPCFF_SUBMISSIONS::insert(
		array(
			'formid' => CP_CALCULATEDFIELDSF_ID,
			'time' => current_time('mysql'),
			'ipaddr' => $ipaddr,
			'notifyto' => implode( ',', $to_arr ),
			'paypal_post' => $params,
			'data' =>$buffer_A .($coupon?"\nCoupon code:".$coupon->code.$discount_note:"") // FROM \n\n to \n
		)
	);

    if (!$item_number)
    {
        _e( 'Error saving data! Please try again.', 'calculated-fields-form' );
        _e( '<br /><br />Error debug information: ', 'calculated-fields-form' );
		$wpdb->print_error();
        exit;
    }

	$params[ 'itemnumber' ] = $item_number;
	CP_SESSION::register_event($params[ 'itemnumber' ], $params['formid']);

	// Includes the code to insert the data into third party database or table
	$base_path = wp_upload_dir();
	if(
		!$base_path['error'] &&
		file_exists($base_path['basedir'].'/calculated-fields-form/cp_calculatedfieldsf_insert_in_database.php')
	)
	{
		@include_once $base_path['basedir'].'/calculated-fields-form/cp_calculatedfieldsf_insert_in_database.php';
	}
	else
	{
		@include_once dirname( __FILE__ ).'/cp_calculatedfieldsf_insert_in_database.php';
	}

	/**
	 * Action called after inserted the data into database.
	 * To the function is passed an array with submitted data.
	 */
	do_action_ref_array( 'cpcff_process_data', array(&$params) );

    $paypal_optional = ($form_obj->get_option('enable_paypal',CP_CALCULATEDFIELDSF_DEFAULT_ENABLE_PAYPAL) == '2');

    if ( ( (floatval($params["final_price"]) >= 0 && !$paypal_zero_payment) || (floatval($params["final_price"]) > 0 && $paypal_zero_payment) )
          &&
          $form_obj->get_option('enable_paypal',CP_CALCULATEDFIELDSF_DEFAULT_ENABLE_PAYPAL)
          &&
          ( !$paypal_optional || (@$_POST["bccf_payment_option_paypal"] == '1') )
          && (@$_POST["bccf_payment_option_paypal"] != '0')
        )
    {
        if ($form_obj->get_option('paypal_mode',CP_CALCULATEDFIELDSF_DEFAULT_PAYPAL_MODE) == "sandbox")
            $ppurl = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        else
            $ppurl = 'https://www.paypal.com/cgi-bin/webscr';
        if ($form_obj->get_option('paypal_notiemails', '0') == '1')
            $cpcff_main->send_mails($item_number);
?>
<html>
<head><title>Redirecting to Paypal...</title></head>
<body>
<form action="<?php echo $ppurl; ?>" name="ppform3" method="post">
<input type="hidden" name="charset" value="utf-8" />
<input type="hidden" name="business" value="<?php echo esc_attr($form_obj->get_option('paypal_email', CP_CALCULATEDFIELDSF_DEFAULT_PAYPAL_EMAIL)); ?>" />
<?php
$paypal_item_name = $form_obj->get_option('paypal_product_name', CP_CALCULATEDFIELDSF_DEFAULT_PRODUCT_NAME).(@$_POST["services"]?": ".trim($services_formatted[1]):"").$discount_note;
foreach ($params as $item => $value)
    $paypal_item_name = str_replace('<%'.$item.'%>',(is_array($value)?(implode(", ",$value)):($value)),$paypal_item_name);
?>
<input type="hidden" name="item_name" value="<?php echo esc_attr($paypal_item_name); ?>" />
<input type="hidden" name="item_number" value="<?php echo esc_attr($item_number); ?>" />
<input type="hidden" name="email" value="<?php echo esc_attr(@$_POST[$to[0].$sequence]); ?>" />

<?php
$paypal_recurrent = $form_obj->get_option('paypal_recurrent',CP_CALCULATEDFIELDSF_DEFAULT_PAYPAL_RECURRENT);
$paypal_recurrent_setup = trim($form_obj->get_option('paypal_recurrent_setup',''));
$paypal_recurrent_setup_days = trim($form_obj->get_option('paypal_recurrent_setup_days','15'));

$paypal_recurrent_setup = preg_replace('/<%(fieldname\d+)%>/i', '$1', $paypal_recurrent_setup);
$paypal_recurrent_setup_days = preg_replace('/<%(fieldname\d+)%>/i', '$1', $paypal_recurrent_setup_days);

if(preg_match('/^fieldname\d+$/', $paypal_recurrent_setup))
{
   if(isset($params[$paypal_recurrent_setup]))
       $paypal_recurrent_setup = $params[$paypal_recurrent_setup];
   else
       $paypal_recurrent_setup = '';
}

if(preg_match('/^fieldname\d+$/', $paypal_recurrent_setup_days))
{
   if(isset($params[$paypal_recurrent_setup_days]))
       $paypal_recurrent_setup_days = $params[$paypal_recurrent_setup_days];
   else
       $paypal_recurrent_setup_days = 15;
}

$paypal_recurrent_times = intval($form_obj->get_option('paypal_recurrent_times','0'));
if($paypal_recurrent_times == -1 && $form_obj->get_option('paypal_recurrent_times_field',''))
	$paypal_recurrent_times = @intval($params[$form_obj->get_option('paypal_recurrent_times_field','')]);

if( strpos( $paypal_recurrent, 'field' ) !== false )
{
	if(
		!empty( $params[ $paypal_recurrent ] ) &&
		!empty( $choicesTxt[ $paypal_recurrent ] ) &&
		!empty( $choicesVal[ $paypal_recurrent ] ) &&
		( $index = array_search( $params[ $paypal_recurrent ], $choicesTxt[ $paypal_recurrent ] ) ) !== false
	) $paypal_recurrent = $choicesVal[ $paypal_recurrent ][ $index ];
}

$paypal_recurrent = intval( $paypal_recurrent );

if ( $paypal_recurrent == 0 ) { ?>
<input type="hidden" name="cmd" value="<?php if ($form_obj->get_option('donationlayout','') == '1') echo '_donations'; else echo '_xclick'; ?>" />
<input type="hidden" name="bn" value="NetFactorSL_SI_Custom" />
<input type="hidden" name="amount" value="<?php echo esc_attr($params["final_price"]); ?>" />
<?php } else { ?>
<?php if ($paypal_recurrent_setup != '') { ?>
<input type="hidden" name="a1" value="<?php echo esc_attr($paypal_recurrent_setup); ?>">
<input type="hidden" name="p1" value="<?php echo esc_attr($paypal_recurrent_setup_days); ?>">
<input type="hidden" name="t1" value="D">
<?php } ?>
<?php if ($paypal_recurrent_times) { ?>
<input type="hidden" name="srt" value="<?php echo $paypal_recurrent_times; ?>">
<?php } ?>
<input type="hidden" name="cmd" value="_xclick-subscriptions">
<input type="hidden" name="bn" value="NetFactorSL_SI_Custom">
<input type="hidden" name="a3" value="<?php echo esc_attr($params["final_price"]); ?>">
<input type="hidden" name="p3" value="<?php echo esc_attr($paypal_recurrent); ?>">
<input type="hidden" name="t3" value="M">
<input type="hidden" name="src" value="1">
<input type="hidden" name="sra" value="1">
<?php } ?>
<input type="hidden" name="page_style" value="Primary" />
<input type="hidden" name="no_shipping" value="<?php echo esc_attr($form_obj->get_option('paypal_address', 1)); ?>" />
<input type="hidden" name="return" value="<?php echo esc_url(CPCFF_AUXILIARY::replace_params_into_url($form_obj->get_option('fp_return_page', CP_CALCULATEDFIELDSF_DEFAULT_fp_return_page), $params)); ?>">
<input type="hidden" name="cancel_return" value="<?php echo esc_url($_POST["cp_ref_page"]); ?>" />
<input type="hidden" name="no_note" value="1" />
<input type="hidden" name="currency_code" value="<?php echo esc_attr(strtoupper($form_obj->get_option('currency', CP_CALCULATEDFIELDSF_DEFAULT_CURRENCY))); ?>" />
<input type="hidden" name="lc" value="<?php echo esc_attr($form_obj->get_option('paypal_language', CP_CALCULATEDFIELDSF_DEFAULT_PAYPAL_LANGUAGE)); ?>" />
<input type="hidden" name="notify_url" value="<?php echo esc_url(CPCFF_AUXILIARY::site_url().'/?cp_calculatedfieldsf_ipncheck='.$item_number); ?>" />
<input type="hidden" name="ipn_test" value="1" />
<input class="pbutton" type="hidden" value="Buy Now" /></div>
</form>
<script type="text/javascript">
document.ppform3.submit();
</script>
</body>
</html>
<?php
        exit();
    }
    else
    {
        $cpcff_main->send_mails($item_number);
        $redirect = true;

		/**
		 * Filters applied to decide if the website should be redirected to the thank you page after submit the form,
		 * pass a boolean as parameter and returns a boolean
		 */
        $redirect = apply_filters( 'cpcff_redirect', $redirect );

        if( $redirect )
        {
            $location = $form_obj->get_option('fp_return_page', CP_CALCULATEDFIELDSF_DEFAULT_fp_return_page, $item_number);

			if ( ! headers_sent() ) {
				header( "Location: ".CPCFF_AUXILIARY::replace_params_into_url( $location, $params ) );
			} else {
				print '<script>document.location.href="' . CPCFF_AUXILIARY::replace_params_into_url( $location, $params ) . '";</script>';
			}

			remove_all_actions('shutdown');
            exit;
        }
    }
}

function cp_calculatedfieldsf_check_IPN_verification( $item_number ) {
    global $wpdb;

	$cpcff_main  = CPCFF_MAIN::instance();
	$item_number = intval(@$item_number);

    $item_name = $_POST['item_name'];
    // $item_number = $_POST['item_number'];
    $payment_status = $_POST['payment_status'];
    $payment_amount = $_POST['mc_gross'];
    $payment_currency = $_POST['mc_currency'];
    $receiver_email = $_POST['receiver_email'];
    $payer_email = $_POST['payer_email'];
    $payment_type = $_POST['payment_type'];
/**
	if ($payment_status != 'Completed' && $payment_type != 'echeck')
	    return;

	if ($payment_type == 'echeck' && $payment_status != 'Pending')
	    return;
*/
	$str = '';
    if (isset($_POST['txn_id'])) $str .= "Transaction ID: ".$_POST["txn_id"]."\n";
    if (isset($_POST["first_name"])) $str .= "Buyer: ".$_POST["first_name"]." ".$_POST["last_name"]."\n";
	if (isset($_POST["subscr_id"])) $str .= "Subscription id: ".$_POST["subscr_id"]."\n";
    if (isset($_POST["payer_email"])) $str .= 'Payer email: '.$_POST["payer_email"]."\n";
	if (isset($_POST["residence_country"])) $str .= 'Country code: '.$_POST["residence_country"]."\n";
	if (isset($_POST["payer_status"])) $str .= 'Payer status: '.$_POST["payer_status"]."\n";
	if (isset($_POST["protection_eligibility"])) $str .= 'Protection eligibility: '.$_POST["protection_eligibility"]."\n";

	if (isset($_POST["item_name"])) $str .= 'Item: '.$_POST["item_name"]."\n";
	if (isset($_POST["payment_gross"]) && isset($_POST["mc_currency"]) && isset($_POST["payment_fee"]))
	     $str .= 'Payment: '.$_POST["payment_gross"]." ".$_POST["mc_currency"]." (Fee: ".$_POST["payment_fee"].")"."\n";
	else if (isset($_POST["mc_gross"]) && isset($_POST["mc_currency"]) && isset($_POST["mc_fee"]))
	     $str .= 'Payment: '.$_POST["mc_gross"]." ".$_POST["mc_currency"]." (Fee: ".$_POST["mc_fee"].")"."\n";
	if (isset($_POST["payment_date"])) $str .= 'Payment date: '.$_POST["payment_date"];
	if (isset($_POST["payment_type"])) $str .= 'Payment type/status: '.$_POST["payment_type"]."/".$_POST["payment_status"]."\n";
	if (isset($_POST["business"])) $str .= 'Business: '.$_POST["business"]."\n";
	if (isset($_POST["receiver_email"])) $str .= 'Receiver email: '.$_POST["receiver_email"]."\n";

	$submission = CPCFF_SUBMISSIONS::get($item_number);
	if($submission)
	{
		$params = $submission->paypal_post;
		$form_obj = CPCFF_SUBMISSIONS::get_form($item_number);
		if($submission->paid == 0)
		{
			$params[ 'paypal_data' ] = $str;
			if (isset($_POST["subscr_id"])) $params[ 'subscr_id' ] = $_POST["subscr_id"];
			if (isset($_POST["txn_id"])) $params[ 'txn_id' ] = $_POST["txn_id"];
			CPCFF_SUBMISSIONS::update($item_number, array('paid'=>1, 'paypal_post'=>$params));

			echo 'OK - processed';
			/**
			 * Action called after process the data received by PayPal.
			 * To the function is passed an array with the data collected by the form.
			 */
			$params['itemnumber'] = $item_number;
			do_action( 'cpcff_payment_processed', $params );

			if ($form_obj->get_option('paypal_notiemails', '0') != '1')
				$cpcff_main->send_mails($item_number, $payer_email);
		}
	}
	else
		echo 'OK - already processed';
	exit;
}

function cp_calculatedfieldsf_sorting_fields_in_containers( $fields_list )
{
	$new_fields_list = array();
	while( count( $fields_list ) )
	{
		$field = array_shift( $fields_list );
		$fieldType = strtolower( $field->ftype );

		if( $fieldType == 'ffieldset' || $fieldType == 'fdiv' )
		{
			$fields = $field->fields;
			if( !empty( $fields ) )
			{
				$tmp_list = array();
				$tmp_counter = 0;
				foreach( $fields as $index => $fieldName )
				{
					for( $i = 0; $i < count( $fields_list ); $i++ )
					{
						if( $fieldName == $fields_list[ $i ]->name )
						{
							$tmp_list[ $tmp_counter ] = array_splice( $fields_list, $i, 1 );
							$tmp_list[ $tmp_counter ] = $tmp_list[ $tmp_counter ][ 0 ];
							$tmp_counter++;
							break;
						}
					}
				}
				$fields_list = array_merge( $tmp_list, $fields_list );
			}
		}
		else
		{
			$new_fields_list[] = $field;
		}
	}
	return $new_fields_list;
}

function cp_calculatedfieldsf_export_csv ()
{
	$toExclude = array( 'fcommentarea', 'fsectionbreak', 'fpagebreak', 'fsummary', 'fmedia', 'ffieldset', 'fdiv', 'fbutton', 'frecordsetds', 'fhtml' );

    if (!is_admin())
        return;

    global $wpdb;

	$cpcff_main = CPCFF_MAIN::instance();

	$form_id = @intval($_GET["cal"]);
	$form_obj = $cpcff_main->get_form($form_id);

    $headers = array( "Form ID",  "Submission ID",  "Time",  "IP Address",  "email",  "Paid",  "Final Price",  "Coupon" );
	$fields = array( 0, 1, 2, 3, 4, 5, 6, 7 );
    $values = array();
	$form_data = $form_obj->get_option( 'form_structure', CP_CALCULATEDFIELDSF_DEFAULT_form_structure );
	$fields_list = cp_calculatedfieldsf_sorting_fields_in_containers( $form_data[ 0 ] );

	// Get headers and fields
	for( $i = 0; $i < count( $fields_list ); $i++ )
	{
		$field = $fields_list[ $i ];
		$fieldType = strtolower( $field->ftype );
		if( !in_array( $fieldType, $toExclude ) )
		{
			$fields[]  = $field->name;
			$headers[] = ( !empty( $field->shortlabel ) ) ? $field->shortlabel : ( ( !empty( $field->title ) ) ? $field->title : $field->name );
		}
	}

    $headers = apply_filters('cpcff_export_csv_header', $headers);
    $fields = apply_filters('cpcff_export_csv_row', $fields);

	// Get rows
    $cond = '';
    if ($_GET["search"] != '')
		$cond .= $wpdb->prepare(" AND (data LIKE %s OR paypal_post LIKE %s)", '%'.$_GET["search"].'%', '%'.$_GET["search"].'%');
    if ($_GET["dfrom"] != '')
		$cond .= $wpdb->prepare(" AND (`time` >= %s)", $_GET["dfrom"]);
    if ($_GET["dto"] != '')
		$cond .= $wpdb->prepare(" AND (`time` <= %s)", $_GET["dto"].' 23:59:59');
    if ($form_id != 0)
		$cond .= $wpdb->prepare(" AND formid=%d", $form_id);

	$events_query = "SELECT * FROM `".CP_CALCULATEDFIELDSF_POSTS_TABLE_NAME."` WHERE 1=1 ".$cond." ORDER BY `time` DESC";
	/**
	 * Allows modify the query of messages, passing the query as parameter
	 * returns the new query
	 */
	$events_query = apply_filters( 'cpcff_csv_query', $events_query );
	$events = CPCFF_SUBMISSIONS::populate($events_query);

    foreach ($events as $item)
    {

        $data = array();
        $data = @unserialize( $item->paypal_post );
		if( $data === false ) continue;

        $value = array( $item->formid, $item->id, $item->time, $item->ipaddr, $item->notifyto, ( $item->paid ? "Yes" : "No" ), @$data[ "final_price" ], @$data[ "coupon" ] );

        unset($data["final_price"]);
        unset($data["coupon"]);

		$value = array_merge( $value, $data );
		$values[] = $value;
    }

    header("Content-type: application/octet-stream");
    header("Content-Disposition: attachment; filename=export.csv");

	// Print headers
	$column_separator = '';
    foreach ( $headers as $header ) {
        echo $column_separator.'"'.str_replace( '"', '""', ( $header ) ).'"';
		$column_separator = ',';
	}

	// Print rows
    foreach ( $values as $item )
    {
		echo "\n";
		$column_separator = '';
        foreach( $fields as $field )
		{
			if ( !isset( $item[ $field ] ) )
                $item[ $field ] = '';

			if( isset( $item[ $field.'_url' ] ) )
				$field.='_url';

            if ( is_array( $item[ $field ] ) )
                $item[ $field ] = implode( ',', $item[ $field ] );

			$item[ $field ] = preg_replace('/[\n\r\t]/', ' ', $item[ $field ]);
            echo $column_separator . '"' . str_replace( '"', '""', ( $item[ $field ] ) ) . '"';
			$column_separator = ',';
		}
    }
	remove_all_actions('shutdown');
    exit;
}

function cp_calculatedfieldsf_save_options()
{
	check_admin_referer( 'cff-form-settings', '_cpcff_nonce' );
    global $wpdb;
    if (!defined('CP_CALCULATEDFIELDSF_ID'))
        define ('CP_CALCULATEDFIELDSF_ID',$_POST["cp_calculatedfieldsf_id"]);

    $error_occur = false;
	if( isset( $_POST[ 'form_structure' ] ) )
    {
		// Remove bom characters
		$_POST[ 'form_structure' ] = CPCFF_AUXILIARY::clean_bom($_POST[ 'form_structure' ]);
		$form_structure_obj = CPCFF_AUXILIARY::json_decode( $_POST[ 'form_structure' ] );
		if( !empty( $form_structure_obj ) )
		{
			global $cpcff_default_texts_array;
			$cpcff_text_array = '';

			$_POST = CPCFF_AUXILIARY::stripcslashes_recursive($_POST);
			if( isset( $_POST[ 'cpcff_text_array' ] ) ) $_POST['vs_all_texts'] = $_POST[ 'cpcff_text_array' ];

			$cpcff_main = CPCFF_MAIN::instance();
			if( $cpcff_main->get_form($_POST["cp_calculatedfieldsf_id"])->save_settings($_POST) === false )
			{
				global $cff_structure_error;
				$cff_structure_error = __('<div class="error-text">The data cannot be stored in database because has occurred an error with the database structure. Please, go to the plugins section and Deactivate/Activate the plugin to be sure the structure of database has been checked, and corrected if needed. If the issue persist, please <a href="https://cff.dwbooster.com/contact-us">contact us</a></div>', 'calculated-fields-form' );
			}
		}
		else
		{
			$error_occur = true;
		}
    }
    else
    {
		$error_occur = true;
    }

	if( $error_occur )
	{
		global $cff_structure_error;
        $cff_structure_error = __('<div class="error-text">The data cannot be stored in database because has occurred an error with the form structure. Please, try to save the data again. If have been copied and pasted data from external text editors, the data can contain invalid characters. If the issue persist, please <a href="https://cff.dwbooster.com/contact-us">contact us</a></div>', 'calculated-fields-form' );
	}
}