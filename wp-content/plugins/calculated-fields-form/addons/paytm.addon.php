<?php
/*
Documentation: https://goo.gl/w3kKoH
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CFF_PayTM' ) )
{
    class CFF_PayTM extends CPCFF_BaseAddon
    {
		static public $category = 'Payment Gateways';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-paytm-20160706";
		protected $name = "CFF - PayTM Payment Gateway";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#paytm-addon';
        protected $default_pay_label = "Pay with PayTM";

		public function get_addon_form_settings( $form_id )
		{
			global $wpdb;
			// Insertion in database
			if(
				isset( $_REQUEST[ 'cff_paytm_id' ] )
			)
			{

			    $wpdb->delete( $wpdb->prefix.$this->form_table, array( 'formid' => $form_id ), array( '%d' ) );
				$wpdb->insert(
								$wpdb->prefix.$this->form_table,
								array(
									'formid' => $form_id,
									'paytm_api_username'	 => $_REQUEST["paytm_api_username"],
									'paytm_api_password'	 => $_REQUEST["paytm_api_password"],
									'paytm_api_web'	 => $_REQUEST["paytm_api_web"],
									'paytm_api_industry' => $_REQUEST["paytm_api_industry"],
									'enabled'	 => $_REQUEST["paytm_enabled"],
                                    'enable_option_yes'	 => $_REQUEST["paytm_enable_option_yes"],
									'paypal_mode'	 => $_REQUEST["paytm_mode"]
								),
								array( '%d', '%s', '%s','%s', '%s', '%s', '%s', '%s' )
							);
			}


			$rows = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $form_id )
					);
			if (!count($rows))
			{
			    $row["paytm_api_username"] = "";
			    $row["paytm_api_password"] = "";
			    $row["paytm_api_web"] = "";
			    $row["paytm_api_industry"] = "";
			    $row["enabled"] = "0";
			    $row["paypal_mode"] = "production";
                $row["enable_option_yes"] = $this->default_pay_label;
			} else {
			    $row["paytm_api_username"] = $rows[0]->paytm_api_username;
			    $row["paytm_api_password"] = $rows[0]->paytm_api_password;
			    $row["paytm_api_web"] = $rows[0]->paytm_api_web;
			    $row["paytm_api_industry"] = $rows[0]->paytm_api_industry;
			    $row["enabled"] = $rows[0]->enabled;
			    $row["paypal_mode"] = $rows[0]->paypal_mode;
                $row["enable_option_yes"] = $rows[0]->enable_option_yes;
			}

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<div id="metabox_paytm_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_paytm_addon_form_settings' ) ); ?>" >
				<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
				<div class="inside">
				   <input type="hidden" name="cff_paytm_id" value="1" />
                   <table class="form-table">
                    <tr valign="top">
                    <th scope="row"><?php _e('Enable PayTM? (if enabled PayPal Standard is disabled)', 'cpabc'); ?></th>
                    <td><select name="paytm_enabled">
                         <option value="0" <?php if (!$row["enabled"]) echo 'selected'; ?>><?php _e('No', 'cpabc'); ?></option>
                         <option value="1" <?php if ($row["enabled"] == '1') echo 'selected'; ?>><?php _e('Yes', 'calculated-fields-form'); ?></option>
                         <option value="2" <?php if ($row["enabled"] == '2') echo 'selected'; ?>><?php _e('Optional: This payment method + Pay Later (submit without payment)', 'calculated-fields-form'); ?></option>
                         <option value="3" <?php if ($row["enabled"] == '3') echo 'selected'; ?>><?php _e('Optional: This payment method + Other payment methods (enabled)', 'calculated-fields-form'); ?></option>
                         <option value="4" <?php if ($row["enabled"] == '4') echo 'selected'; ?>><?php _e('Optional: This payment method + Other payment methods  + Pay Later ', 'calculated-fields-form'); ?></option>
                         </select>
                         <div style="margin-top:10px;background:#EEF5FB;border: 1px dotted #888888;padding:10px;width:260px;">
                           <?php _e( 'Label for this payment option', 'calculated-fields-form' ); ?>:<br />
                           <input type="text" name="paytm_enable_option_yes" size="40" style="width:250px;" value="<?php echo esc_attr($row['enable_option_yes']); ?>" />
                        </div>
                    </td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Merchant ID', 'cpabc'); ?></th>
                    <td><input type="text" name="paytm_api_username" size="20" value="<?php echo esc_attr($row["paytm_api_username"]); ?>" /><br />
                        <em>Change this value with MID (Merchant ID) received from Paytm<em></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Merchant Key', 'cpabc');?></th>
                    <td><input type="text" name="paytm_api_password" size="40" value="<?php echo esc_attr($row["paytm_api_password"]); ?>" /><br />
                        <em>Change this value with Merchant key downloaded from portal</em></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Website Name', 'cpabc');?></th>
                    <td><input type="text" name="paytm_api_web" size="40" value="<?php echo esc_attr($row["paytm_api_web"]); ?>" /><br />
                        <em>Change this value with Website name received from Paytm</em></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Industry Type ID', 'cpabc');?></th>
                    <td><input type="text" name="paytm_api_industry" size="40" value="<?php echo esc_attr($row["paytm_api_industry"]); ?>" /><br />
                        <em>Change this value with INDUSTRY_TYPE_ID value received from Paytm</em></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Mode', 'cpabc');  ?></th>
                    <td><select name="paytm_mode">
                         <option value="production" <?php if ($row["paypal_mode"] != 'sandbox') echo 'selected'; ?>><?php _e('Production - real payments processed', 'cpabc'); ?></option>
                         <option value="sandbox" <?php if ($row["paypal_mode"] == 'sandbox') echo 'selected'; ?>><?php _e('SandBox - Testing sandbox area', 'cpabc'); ?></option>
                        </select>
                    </td>
                    </tr>
                   </table>
                   <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
				</div>
			</div>
			<?php
		} // end get_addon_form_settings



		/************************ ADDON CODE *****************************/

        /************************ ATTRIBUTES *****************************/

        private $form_table = 'cff_form_paytm';
        private $_inserted = false;
		private $_cpcff_main;
        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->_cpcff_main = CPCFF_MAIN::instance();
			$this->description = __("The add-on adds support for PayTM payments", 'cpabc' );
            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;

			add_action( 'cpcff_process_data_before_insert', array( &$this, 'pp_before_insert' ), 10, 3 );

			add_action( 'cpcff_process_data', array( &$this, 'pp_paytm' ), 11, 1 );

			add_action( 'init', array( &$this, 'pp_paytm_update_status' ), 10, 0 );

			add_filter( 'cpcff_the_form', array( &$this, 'insert_payment_fields'), 99, 2 );

			if( is_admin() )
			{
				// Delete forms
				add_action( 'cpcff_delete_form', array(&$this, 'delete_form') );

				// Clone forms
				add_action( 'cpcff_clone_form', array(&$this, 'clone_form'), 10, 2 );

				// Export addon data
				add_action( 'cpcff_export_addons', array(&$this, 'export_form'), 10, 2 );

				// Import addon data
				add_action( 'cpcff_import_addons', array(&$this, 'import_form'), 10, 2 );
			}

            $this->update_database();

        } // End __construct



        /************************ PRIVATE METHODS *****************************/

		/**
         * Create the database tables
         */
        protected function update_database()
		{
			global $wpdb;
			$charset_collate = $wpdb->get_charset_collate();
            $db_queries = array();
			$db_queries[] = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix.$this->form_table." (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					formid INT NOT NULL,
					enabled varchar(10) DEFAULT '0' NOT NULL ,
					paytm_api_username varchar(255) DEFAULT '' NOT NULL ,
					paytm_api_password varchar(255) DEFAULT '' NOT NULL ,
					paytm_api_web varchar(255) DEFAULT '' NOT NULL ,
					paytm_api_industry varchar(255) DEFAULT '' NOT NULL ,
					paypal_mode varchar(255) DEFAULT '' NOT NULL ,
                    enable_option_yes varchar(255) DEFAULT '' NOT NULL ,
					UNIQUE KEY id (id)
				) $charset_collate;";
            $this->_run_update_database($db_queries);
		} // end update_database


		/************************ PUBLIC METHODS  *****************************/


		/**
         * process before insert
         */
		public function pp_before_insert(&$params, &$str, $fields )
		{
            global $wpdb;

            $rows = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $params["formid"] )
					);

			$payment_option = (isset($_POST["bccf_payment_option_paypal"])?$_POST["bccf_payment_option_paypal"]:$this->addonID);
			if (empty( $rows ) || !$rows[0]->enabled || $payment_option != $this->addonID)
			    return;

			$params["payment_option"] = $this->name;

	    }


		/**
         * Check if the Optional is enabled in the form, and inserts radiobutton
         */
        public function	insert_payment_fields( $form_code, $id )
		{
            global $wpdb;
            $rows = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $id )
					);

			if (empty( $rows ) || $rows[0]->enabled == '0' || strpos($form_code, 'vt="'.$this->addonID.'"') !== false)
			    return $form_code;

			// output radio-buttons here
			$form_code = preg_replace( '/<!--addons-payment-options-->/i', '<div><input type="radio" name="bccf_payment_option_paypal" vt="'.$this->addonID.'" value="'.$this->addonID.'" checked> '.__( ($rows[0]->enable_option_yes!=''?$rows[0]->enable_option_yes:$this->default_pay_label) , 'calculated-fields-form').'</div><!--addons-payment-options-->', $form_code );

            if (($rows[0]->enabled == '2' || $rows[0]->enabled == '4') && !strpos($form_code,'bccf_payment_option_paypal" vt="0') )
			    $form_code = preg_replace( '/<!--addons-payment-options-->/i', '<!--addons-payment-options--><div><input type="radio" name="bccf_payment_option_paypal" vt="0" value="0"> '.__( $this->_cpcff_main->get_form($id)->get_option('enable_paypal_option_no',CP_CALCULATEDFIELDSF_PAYPAL_OPTION_NO), 'calculated-fields-form').'</div>', $form_code );

			if (substr_count ($form_code, 'name="bccf_payment_option_paypal"') > 1)
			    $form_code = str_replace( 'id="field-c0" style="display:none">', 'id="field-c0">', $form_code);

            return $form_code;
        }


		/**
         * process payment
         */
		public function pp_paytm($params)
		{
            global $wpdb;

			// documentation: https://goo.gl/w3kKoH

            $rows = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $params["formid"] )
					);

		    $payment_option = (isset($_POST["bccf_payment_option_paypal"])?$_POST["bccf_payment_option_paypal"]:$this->addonID);
			if (empty( $rows ) || !$rows[0]->enabled || $payment_option != $this->addonID || floatval($params["final_price"]) == 0)
			    return;

			$form_obj = CPCFF_SUBMISSIONS::get_form($params["itemnumber"]);
			if($form_obj->get_option('paypal_notiemails', '0') == '1')
			    $this->_cpcff_main->send_mails($params['itemnumber']);

            $checkSum = "";
            $paramList = array();

            // Create an array having all required parameters for creating checksum.
            $paramList["REQUEST_TYPE"] = 'DEFAULT';
            $paramList["MID"] = $rows[0]->paytm_api_username;
            $paramList["ORDER_ID"] = $params[ 'itemnumber' ];
            $paramList["CUST_ID"] = $params[ 'itemnumber' ];
            $paramList["INDUSTRY_TYPE_ID"] = $rows[0]->paytm_api_industry;
            $paramList["CHANNEL_ID"] = "WEB";
            $paramList["TXN_AMOUNT"] = $params["final_price"];
            $paramList["WEBSITE"] = $rows[0]->paytm_api_web;
            $paramList["CALLBACK_URL"] = (CPCFF_AUXILIARY::site_url().'/?cff_paytm_ipncheck=1&itemnumber='.$params["itemnumber"]);


            $paramList["MOBILE_NO"] = ""; //Mobile number of customer
            /**
            $paramList["EMAIL"] = $params["useremail"]; //Email ID of customer
            $paramList["VERIFIED_BY"] = "EMAIL"; //
            $paramList["IS_USER_VERIFIED"] = "YES"; //
            */

            //Here checksum string will return by getChecksumFromArray() function.
            $paytm = new CFF_PayTMAPI($rows[0]->paytm_api_password, ($rows[0]->paypal_mode == 'sandbox'?'TEST':'PROD'));
            $checkSum = $paytm->getChecksumFromArray($paramList,$rows[0]->paytm_api_password);

?>
<html>
<body>
<form method="post" action="<?php echo $paytm->getTxnURL() ?>" name="f1">
			<?php
			foreach($paramList as $name => $value) {
				echo '<input type="hidden" name="' . $name .'" value="' . $value . '">';
			}
			?>
			<input type="hidden" name="CHECKSUMHASH" value="<?php echo $checkSum ?>">
	</form>
	<script type="text/javascript">
			document.f1.submit();
	</script>
</body>
<?php



            exit;
		} // end pp_paytm


		/**
		 * mark the item as paid
		 */
		private function _log($adarray = array())
		{
			$h = fopen( dirname(__FILE__).'/logs.txt', 'a' );
			$log = "";
			foreach( $_REQUEST as $KEY => $VAL )
			{
				$log .= $KEY.": ".$VAL."\n";
			}
			foreach( $adarray as $KEY => $VAL )
			{
				$log .= $KEY.": ".$VAL."\n";
			}
			$log .= "================================================\n";
			fwrite( $h, $log );
			fclose( $h );
		}

		public function pp_paytm_update_status( )
		{
            global $wpdb;
            if ( !isset( $_GET['cff_paytm_ipncheck'] ) || $_GET['cff_paytm_ipncheck'] != '1' || !isset( $_GET["itemnumber"] ) )
                return;

			$itemnumber = intval(@$_GET["itemnumber"]);
			$submission = CPCFF_SUBMISSIONS::get($itemnumber);
			if(empty($submission)) return;

            $row = $wpdb->get_row(
						$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $submission->formid )
					);
			if(empty($row)) return;

            $paytm = new CFF_PayTMAPI($row->paytm_api_password, ($row->paypal_mode == 'sandbox'?'TEST':'PROD'));
            $ORDER_ID = $_POST["ORDER_ID"];
		    // Create an array having all required parameters for status query.
			$requestParamList = array("MID" => $row->paytm_api_username , "ORDERID" => $ORDER_ID);
		    // Call the PG's getTxnStatus() function for verifying the transaction status.
		    $responseParamList = $paytm->getTxnStatus($requestParamList);

            // log data
            /**
			CPCFF_SUBMISSIONS::update(
				$itemnumber,
				array(
					'data' => $submission->data
							."\n\nPayTM_ResponseCode:".$responseParamList["RESPCODE"]
							."\n\nPayTM_TXNID:".$responseParamList["TXNID"]
							."\n\nPayTM_ORDERID:".$responseParamList["ORDERID"]
				)
			);
            */

			if ($responseParamList["RESPCODE"] != '01')  // transaction failed
			    return;

			$form_obj = CPCFF_SUBMISSIONS::get_form($itemnumber);
            if ($submission->paid == 0)
            {
				CPCFF_SUBMISSIONS::update($itemnumber,array('paid'=>1));

                $submission->paypal_post['itemnumber'] = $itemnumber;
                do_action( 'cpcff_payment_processed', $submission->paypal_post );

                if ($form_obj->get_option('paypal_notiemails', '0') != '1')
					$this->_cpcff_main->send_mails($itemnumber);
            }

			$location = CPCFF_AUXILIARY::replace_params_into_url($form_obj->get_option('fp_return_page', CP_CALCULATEDFIELDSF_DEFAULT_fp_return_page), $submission->paypal_post);
            header("Location: ".$location);
            exit();
		}

		/**
		 *	Delete the form from the addon's table
		 */
        public function delete_form( $formid)
		{
			global $wpdb;
			$wpdb->delete( $wpdb->prefix.$this->form_table, array('formid' => $formid), '%d' );
		} // delete_form

		/**
		 *	Clone the form's row
		 */
		public function clone_form( $original_form_id, $new_form_id )
		{
			global $wpdb;

			$form_rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $original_form_id ), ARRAY_A);

			if(!empty($form_rows))
			{
				foreach($form_rows as $form_row)
				{
					unset($form_row["id"]);
					$form_row["formid"] = $new_form_id;
					$wpdb->insert( $wpdb->prefix.$this->form_table, $form_row);
				}
			}
		} // End clone_form

		/**
		 *	It is called when the form is exported to export the addons data too.
		 *  Receive an array with the other addons data, and the form's id for filtering.
		 */
		public function export_form($addons_array, $formid)
		{
			global $wpdb;
			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $formid ), ARRAY_A );
			if(!empty($rows))
			{
				$addons_array[ $this->addonID ] = array();
				foreach($rows as $row)
				{
					unset($row['id']);
					unset($row['formid']);
					$addons_array[ $this->addonID ][] = $row;
				}
			}
			return $addons_array;
		} // End export_form

		/**
		 *	It is called when the form is imported to import the addons data too.
		 *  Receive an array with all the addons data, and the new form's id.
		 */
		public function import_form($addons_array, $formid)
		{
			global $wpdb;
			if(isset($addons_array[$this->addonID]))
			{
				foreach($addons_array[$this->addonID] as $row)
				{
					if(!empty($row))
					{
						$row['formid'] = $formid;
						$wpdb->insert(
							$wpdb->prefix.$this->form_table,
							$row
						);
					}
				}
			}
		} // End import_form

    } // End Class

    // Main add-on code
    $cff_paytm_obj = new CFF_PayTM();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cff_paytm_obj);
}

if( !class_exists( 'CFF_PayTMAPI' ) )
{

    class CFF_PayTMAPI {

        private $PAYTM_DOMAIN;
        private $PAYTM_REFUND_URL;
        private $PAYTM_STATUS_QUERY_URL;
        private $PAYTM_TXN_URL;

        private $PAYTM_MERCHANT_KEY;

        /**
         * Constructor
         */
        public function __construct($key, $mode = "PROD")
        {
            $this->PAYTM_MERCHANT_KEY = $key;

            $this->PAYTM_DOMAIN = "pguat.paytm.com";
            if ($mode == 'PROD')
                $this->PAYTM_DOMAIN = 'secure.paytm.in';
            $this->PAYTM_REFUND_URL = 'https://'.$this->PAYTM_DOMAIN.'/oltp/HANDLER_INTERNAL/REFUND';
            $this->PAYTM_STATUS_QUERY_URL = 'https://'.$this->PAYTM_DOMAIN.'/oltp/HANDLER_INTERNAL/TXNSTATUS';
            $this->PAYTM_TXN_URL = 'https://'.$this->PAYTM_DOMAIN.'/oltp-web/processTransaction';
        }

        public function getTxnURL()
        {
            return $this->PAYTM_TXN_URL;
        }

        public function encrypt_e($input, $ky) {
        	$key   = html_entity_decode($ky);
        	$iv = "@@@@&&&&####$$$$";
        	$data = openssl_encrypt ( $input , "AES-128-CBC" , $key, 0, $iv );
        	return $data;
        }

        public function decrypt_e($crypt, $ky) {
        	$key   = html_entity_decode($ky);
        	$iv = "@@@@&&&&####$$$$";
        	$data = openssl_decrypt ( $crypt , "AES-128-CBC" , $key, 0, $iv );
        	return $data;
        }

        public function pkcs5_pad_e($text, $blocksize) {
        	$pad = $blocksize - (strlen($text) % $blocksize);
        	return $text . str_repeat(chr($pad), $pad);
        }

        public function pkcs5_unpad_e($text) {
        	$pad = ord($text[strlen($text) - 1]);
        	if ($pad > strlen($text))
        		return false;
        	return substr($text, 0, -1 * $pad);
        }

        public function generateSalt_e($length) {
        	$random = "";
        	srand((double) microtime() * 1000000);

        	$data = "AbcDE123IJKLMN67QRSTUVWXYZ";
        	$data .= "aBCdefghijklmn123opq45rs67tuv89wxyz";
        	$data .= "0FGH45OP89";

        	for ($i = 0; $i < $length; $i++) {
        		$random .= substr($data, (rand() % (strlen($data))), 1);
        	}

        	return $random;
        }

        public function checkString_e($value) {
        	$myvalue = ltrim($value);
        	$myvalue = rtrim($myvalue);
        	if ($myvalue == 'null')
        		$myvalue = '';
        	return $myvalue;
        }

        public function getChecksumFromArray($arrayList, $key, $sort=1) {
        	if ($sort != 0) {
        		ksort($arrayList);
        	}
        	$str = $this->getArray2Str($arrayList);
        	$salt = $this->generateSalt_e(4);
        	$finalString = $str . "|" . $salt;
        	$hash = hash("sha256", $finalString);
        	$hashString = $hash . $salt;
        	$checksum = $this->encrypt_e($hashString, $key);
        	return $checksum;
        }

        public function verifychecksum_e($arrayList, $key, $checksumvalue) {
        	$arrayList = $this->removeCheckSumParam($arrayList);
        	ksort($arrayList);
        	$str = $this->getArray2Str($arrayList);
        	$paytm_hash = $this->decrypt_e($checksumvalue, $key);
        	$salt = substr($paytm_hash, -4);

        	$finalString = $str . "|" . $salt;

        	$website_hash = hash("sha256", $finalString);
        	$website_hash .= $salt;

        	$validFlag = "FALSE";
        	if ($website_hash == $paytm_hash) {
        		$validFlag = "TRUE";
        	} else {
        		$validFlag = "FALSE";
        	}
        	return $validFlag;
        }

        public function getArray2Str($arrayList) {
        	$paramStr = "";
        	$flag = 1;
        	foreach ($arrayList as $key => $value) {
        		if ($flag) {
        			$paramStr .= $this->checkString_e($value);
        			$flag = 0;
        		} else {
        			$paramStr .= "|" . $this->checkString_e($value);
        		}
        	}
        	return $paramStr;
        }

        public function redirect2PG($paramList, $key) {
        	$hashString = getchecksumFromArray($paramList);
        	$checksum = $this->encrypt_e($hashString, $key);
        }

        public function removeCheckSumParam($arrayList) {
        	if (isset($arrayList["CHECKSUMHASH"])) {
        		unset($arrayList["CHECKSUMHASH"]);
        	}
        	return $arrayList;
        }

        public function getTxnStatus($requestParamList) {
        	return $this->callAPI($this->PAYTM_STATUS_QUERY_URL, $requestParamList);
        }

        public function initiateTxnRefund($requestParamList) {
        	$CHECKSUM = $this->getChecksumFromArray($requestParamList,$this->PAYTM_MERCHANT_KEY,0);
        	$requestParamList["CHECKSUM"] = $CHECKSUM;
        	return $this->callAPI($this->PAYTM_REFUND_URL, $requestParamList);
        }

        public function callAPI($apiURL, $requestParamList) {
        	$jsonResponse = "";
        	$responseParamList = array();
        	$JsonData =json_encode($requestParamList);
        	$postData = 'JsonData='.urlencode($JsonData);
        	$ch = curl_init($apiURL);
        	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        	curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        	curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        	'Content-Type: application/json',
        	'Content-Length: ' . strlen($postData))
        	);
        	$jsonResponse = curl_exec($ch);
        	$responseParamList = json_decode($jsonResponse,true);
        	return $responseParamList;
        }

    }

}

?>