<?php
/*

*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CFF_AuthNetSIM' ) )
{
    class CFF_AuthNetSIM extends CPCFF_BaseAddon
    {
		static public $category = 'Payment Gateways';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-AuthNetSIM-20160910";
		protected $name = "CFF - Authorize.net Server Integration Method";
		protected $description;
        protected $default_pay_label = "Pay with Authorize.net";
        protected $help = 'https://cff.dwbooster.com/documentation#authorize-addon';

		public function get_addon_form_settings( $form_id )
		{
			global $wpdb;
			// Insertion in database
			if(
				isset( $_REQUEST[ 'cpabc_AuthNetSIM_id' ] )
			)
			{

			    $wpdb->delete( $wpdb->prefix.$this->form_table, array( 'formid' => $form_id ), array( '%d' ) );
				$wpdb->insert(
								$wpdb->prefix.$this->form_table,
								array(
									'formid' => $form_id,

									'AuthNetSIM_api_username'	 => $_REQUEST["AuthNetSIM_api_username"],
									'AuthNetSIM_api_key'    	 => $_REQUEST["AuthNetSIM_api_key"],
									'x_receipt_url'	             => $_REQUEST["x_receipt_url"],
									'x_cancel_url'	             => $_REQUEST["x_cancel_url"],
									'mode'	                     => $_REQUEST["AuthNetSIM_mode"],
									'enabled'                    => $_REQUEST["AuthNetSIM_enabled"],
									'authnet_f_x_first_name'	 => $_REQUEST["authnet_f_x_first_name"],
                                    'authnet_f_x_last_name'	     => $_REQUEST["authnet_f_x_last_name"],
                                    'authnet_f_x_company'	     => $_REQUEST["authnet_f_x_company"],
                                    'authnet_f_x_address'	     => $_REQUEST["authnet_f_x_address"],
                                    'authnet_f_x_city'	         => $_REQUEST["authnet_f_x_city"],
                                    'authnet_f_x_state'	         => $_REQUEST["authnet_f_x_state"],
                                    'authnet_f_x_country'	     => $_REQUEST["authnet_f_x_country"],
                                    'authnet_f_x_zip'	         => $_REQUEST["authnet_f_x_zip"],
                                    'authnet_f_x_email'	         => $_REQUEST["authnet_f_x_email"],
                                    'authnet_f_x_phone'	         => $_REQUEST["authnet_f_x_phone"],
                                    'authnet_f_x_fax'	         => $_REQUEST["authnet_f_x_fax"],
                                    'enable_option_yes'	         => $_REQUEST["AuthNetSIM_enable_option_yes"]

								),
								array( '%d', '%s', '%s', '%s', '%s', '%s', '%s',
								             '%s', '%s', '%s', '%s', '%s',
								             '%s', '%s', '%s', '%s', '%s',
								             '%s', '%s'
								              )
							);
			}


			$rows = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $form_id )
					);
			if (!count($rows))
			{
			    $row["AuthNetSIM_api_username"] = "";
			    $row["AuthNetSIM_api_key"] = "";
			    $row["enabled"] = "0";
                $row["x_receipt_url"] = '';
                $row["x_cancel_url"] = '';
                $row["mode"] = 'test';
                $row["authnet_f_x_first_name"] = '';
                $row["authnet_f_x_last_name"] = '';
                $row["authnet_f_x_company"] = '';
                $row["authnet_f_x_address"] = '';
                $row["authnet_f_x_city"] = '';
                $row["authnet_f_x_state"] = '';
                $row["authnet_f_x_country"] = '';
                $row["authnet_f_x_zip"] = '';
                $row["authnet_f_x_email"] = '';
                $row["authnet_f_x_phone"] = '';
                $row["authnet_f_x_fax"] = '';
                $row["enable_option_yes"] = $this->default_pay_label;
			} else {
			    $row["enabled"] = $rows[0]->enabled;
			    $row["AuthNetSIM_api_username"] = $rows[0]->AuthNetSIM_api_username;
                $row["AuthNetSIM_api_key"] = $rows[0]->AuthNetSIM_api_key;
                $row["x_receipt_url"] = $rows[0]->x_receipt_url;
                $row["x_cancel_url"] = $rows[0]->x_cancel_url;
                $row["mode"] = $rows[0]->mode;
                $row["authnet_f_x_first_name"] = $rows[0]->authnet_f_x_first_name;
                $row["authnet_f_x_last_name"] = $rows[0]->authnet_f_x_last_name ;
                $row["authnet_f_x_company"] = $rows[0]->authnet_f_x_company;
                $row["authnet_f_x_address"] = $rows[0]->authnet_f_x_address;
                $row["authnet_f_x_city"] = $rows[0]->authnet_f_x_city;
                $row["authnet_f_x_state"] = $rows[0]->authnet_f_x_state;
                $row["authnet_f_x_country"] = $rows[0]->authnet_f_x_country;
                $row["authnet_f_x_zip"] = $rows[0]->authnet_f_x_zip;
                $row["authnet_f_x_email"] = $rows[0]->authnet_f_x_email;
                $row["authnet_f_x_phone"] = $rows[0]->authnet_f_x_phone;
                $row["authnet_f_x_fax"] = $rows[0]->authnet_f_x_fax;
                $row["enable_option_yes"] = $rows[0]->enable_option_yes;
			}

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<div id="metabox_authnet_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_authnet_addon_form_settings' ) ); ?>" >
				<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
				<div class="inside">
				   <input type="hidden" name="cpabc_AuthNetSIM_id" value="1" />
                   <table class="form-table">
                    <tr valign="top">
                    <th scope="row"><?php _e('Enable Authorize.net SIM?', 'calculated-fields-form'); ?></th>
                    <td><select name="AuthNetSIM_enabled">
                         <option value="0" <?php if (!$row["enabled"]) echo 'selected'; ?>><?php _e('No', 'calculated-fields-form'); ?></option>
                         <option value="1" <?php if ($row["enabled"] == '1') echo 'selected'; ?>><?php _e('Yes', 'calculated-fields-form'); ?></option>
                         <option value="2" <?php if ($row["enabled"] == '2') echo 'selected'; ?>><?php _e('Optional: This payment method + Pay Later (submit without payment)', 'calculated-fields-form'); ?></option>
                         <option value="3" <?php if ($row["enabled"] == '3') echo 'selected'; ?>><?php _e('Optional: This payment method + Other payment methods (enabled)', 'calculated-fields-form'); ?></option>
                         <option value="4" <?php if ($row["enabled"] == '4') echo 'selected'; ?>><?php _e('Optional: This payment method + Other payment methods  + Pay Later ', 'calculated-fields-form'); ?></option>
                         </select>
                        <div style="margin-top:10px;background:#EEF5FB;border: 1px dotted #888888;padding:10px;width:260px;">
                           <?php _e( 'Label for this payment option', 'calculated-fields-form' ); ?>:<br />
                           <input type="text" name="AuthNetSIM_enable_option_yes" size="40" style="width:250px;" value="<?php echo esc_attr($row['enable_option_yes']); ?>" />
                        </div>
                    </td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Mode:', 'calculated-fields-form'); ?></th>
                    <td><select name="AuthNetSIM_mode">
                         <option value="production" <?php if ($row["mode"] != 'test') echo 'selected'; ?>><?php _e('Prodution', 'calculated-fields-form'); ?></option>
                         <option value="test" <?php if ($row["mode"] == 'test') echo 'selected'; ?>><?php _e('Test', 'calculated-fields-form'); ?></option>
                         </select>
                    </td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('API Username', 'calculated-fields-form'); ?></th>
                    <td><input type="text" name="AuthNetSIM_api_username" size="40" value="<?php echo esc_attr($row["AuthNetSIM_api_username"]); ?>" /><br />
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('API Key', 'calculated-fields-form'); ?></th>
                    <td><input type="text" name="AuthNetSIM_api_key" size="40" value="<?php echo esc_attr($row["AuthNetSIM_api_key"]); ?>" /><br />
                    </tr>

                    <tr valign="top">
                    <th scope="row"><?php _e('Receipt URL:', 'calculated-fields-form'); ?></th>
                    <td><input type="text" name="x_receipt_url" size="70" value="<?php echo esc_attr(@$row["x_receipt_url"]); ?>" /><br />
                        <em><strong>User will return here after payment. <span style="color:#ff0000">You must also configure the receipt link URL (and relay response URL) with this URL in the Merchant Interface.</span></strong></em>
                        </td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Cancel URL:', 'calculated-fields-form'); ?></th>
                    <td><input type="text" name="x_cancel_url" size="70" value="<?php echo esc_attr(@$row["x_cancel_url"]); ?>" /><br />
                        <em>User will return here if payment fails.</em>
                        </td>
                    </tr>
                   </table>
                   <hr />
                   <strong>ID of the fields to forward to Authorize.net (ex: fieldname1, fieldname2, ...):</strong>
                   <table class="form-table">
                    <tr valign="top">
                    <th scope="row"><?php _e('First Name', 'calculated-fields-form'); ?>:</th>
                    <td><input type="text" name="authnet_f_x_first_name" size="40" value="<?php echo esc_attr($row["authnet_f_x_first_name"]); ?>" /><br />
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Last Name', 'calculated-fields-form'); ?>:</th>
                    <td><input type="text" name="authnet_f_x_last_name" size="40" value="<?php echo esc_attr($row["authnet_f_x_last_name"]); ?>" /><br />
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Company', 'calculated-fields-form'); ?>:</th>
                    <td><input type="text" name="authnet_f_x_company" size="40" value="<?php echo esc_attr($row["authnet_f_x_company"]); ?>" /><br />
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Address', 'calculated-fields-form'); ?>:</th>
                    <td><input type="text" name="authnet_f_x_address" size="40" value="<?php echo esc_attr($row["authnet_f_x_address"]); ?>" /><br />
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('City', 'calculated-fields-form'); ?>:</th>
                    <td><input type="text" name="authnet_f_x_city" size="40" value="<?php echo esc_attr($row["authnet_f_x_city"]); ?>" /><br />
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('State/Province', 'calculated-fields-form'); ?>:</th>
                    <td><input type="text" name="authnet_f_x_state" size="40" value="<?php echo esc_attr($row["authnet_f_x_state"]); ?>" /><br />
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Country', 'calculated-fields-form'); ?>:</th>
                    <td><input type="text" name="authnet_f_x_country" size="40" value="<?php echo esc_attr($row["authnet_f_x_country"]); ?>" /><br />
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Zip/Postal Code', 'calculated-fields-form'); ?>:</th>
                    <td><input type="text" name="authnet_f_x_zip" size="40" value="<?php echo esc_attr($row["authnet_f_x_zip"]); ?>" /><br />
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Email', 'calculated-fields-form'); ?>:</th>
                    <td><input type="text" name="authnet_f_x_email" size="40" value="<?php echo esc_attr($row["authnet_f_x_email"]); ?>" /><br />
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Phone', 'calculated-fields-form'); ?>:</th>
                    <td><input type="text" name="authnet_f_x_phone" size="40" value="<?php echo esc_attr($row["authnet_f_x_phone"]); ?>" /><br />
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Fax', 'calculated-fields-form'); ?>:</th>
                    <td><input type="text" name="authnet_f_x_fax" size="40" value="<?php echo esc_attr($row["authnet_f_x_fax"]); ?>" /><br />
                    </tr>
                   </table>
                   <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
				</div>
			</div>
			<?php
		} // end get_addon_form_settings



		/************************ ADDON CODE *****************************/

        /************************ ATTRIBUTES *****************************/

        private $form_table = 'cff_form_AuthNetSIM';
        private $_inserted = false;
		private $_cpcff_main;

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->_cpcff_main = CPCFF_MAIN::instance();
			$this->description = __("The add-on adds support for Authorize.net Server Integration Method payments", 'calculated-fields-form' );
            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;

            add_action( 'cpcff_process_data_before_insert', array( &$this, 'pp_before_insert' ), 10, 3 );

			add_action( 'cpcff_process_data', array( &$this, 'pp_AuthNetSIM' ), 11, 1 );

			add_action( 'init', array( &$this, 'pp_AuthNetSIM_update_status' ), 10, 0 );

			add_filter( 'cpcff_the_form', array( &$this, 'insert_payment_fields'), 99, 2 );

            add_filter( 'cpcff_additional_tags', array( &$this, 'register_tags'), 99, 1 );

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
			$db_queries = array( "CREATE TABLE ".$wpdb->prefix.$this->form_table." (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					formid INT NOT NULL,
					enabled varchar(10) DEFAULT '0' NOT NULL ,
					AuthNetSIM_api_username varchar(255) DEFAULT '' NOT NULL ,
					AuthNetSIM_api_key varchar(255) DEFAULT '' NOT NULL ,
					x_receipt_url varchar(255) DEFAULT '' NOT NULL ,
					x_cancel_url varchar(255) DEFAULT '' NOT NULL ,
					mode varchar(255) DEFAULT '' NOT NULL ,
					authnet_f_x_first_name varchar(255) DEFAULT '' NOT NULL ,
					authnet_f_x_last_name varchar(255) DEFAULT '' NOT NULL ,
					authnet_f_x_company varchar(255) DEFAULT '' NOT NULL ,
					authnet_f_x_address varchar(255) DEFAULT '' NOT NULL ,
					authnet_f_x_city varchar(255) DEFAULT '' NOT NULL ,
					authnet_f_x_state varchar(255) DEFAULT '' NOT NULL ,
					authnet_f_x_country varchar(255) DEFAULT '' NOT NULL ,
					authnet_f_x_zip varchar(255) DEFAULT '' NOT NULL ,
					authnet_f_x_email  varchar(255) DEFAULT '' NOT NULL ,
					authnet_f_x_phone varchar(255) DEFAULT '' NOT NULL ,
					authnet_f_x_fax varchar(255) DEFAULT '' NOT NULL ,
					enable_option_yes varchar(255) DEFAULT '' NOT NULL ,
                    enable_option_no varchar(255) DEFAULT '' NOT NULL ,
                    enable_option_paypal varchar(255) DEFAULT '' NOT NULL ,
					UNIQUE KEY id (id)
				) $charset_collate;"
			);
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
			    $form_code = preg_replace( '/<!--addons-payment-options-->/i', '<!--addons-payment-options--><div><input type="radio" name="bccf_payment_option_paypal" vt="0" value="0"> '.__($this->_cpcff_main->get_form($id)->get_option('enable_paypal_option_no',CP_CALCULATEDFIELDSF_PAYPAL_OPTION_NO), 'calculated-fields-form').'</div>', $form_code );

			if (substr_count ($form_code, 'name="bccf_payment_option_paypal"') > 1)
			    $form_code = str_replace( 'id="field-c0" style="display:none">', 'id="field-c0">', $form_code);

            return $form_code;
        }


		/**
         * process payment
         */
		public function pp_AuthNetSIM($params)
		{
            global $wpdb;

			CP_SESSION::register_event($params[ 'itemnumber' ], $params[ 'formid' ]);

            $rows = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $params["formid"] )
					);

		    $payment_option = (isset($_POST["bccf_payment_option_paypal"])?$_POST["bccf_payment_option_paypal"]:$this->addonID);

			if (empty( $rows ) || !$rows[0]->enabled || $payment_option != $this->addonID || floatval($params["final_price"]) == 0)
			    return;

			$form_obj = CPCFF_SUBMISSIONS::get_form($params["itemnumber"]);
			if($form_obj->get_option('paypal_notiemails', '0') == '1')
			    $this->_cpcff_main->send_mails($params['itemnumber']);

            if ($rows[0]->mode == 'test')
                $ppurl = 'https://test.authorize.net/gateway/transact.dll';
            else
                $ppurl = 'https://secure2.authorize.net/gateway/transact.dll';

            $sequence = $params["itemnumber"];
            $timestamp = time();

            if( phpversion() >= '5.1.2' )
            {
                $fingerprint = hash_hmac("md5", $rows[0]->AuthNetSIM_api_username . "^" . $sequence . "^" . $timestamp . "^" . $params["final_price"] . "^", $rows[0]->AuthNetSIM_api_key);
            }
            else
            {
                $fingerprint = bin2hex(mhash(MHASH_MD5, $rows[0]->AuthNetSIM_api_username . "^" . $sequence . "^" . $timestamp . "^" . $params["final_price"] . "^", $rows[0]->AuthNetSIM_api_key));
            }

            $pro_item_name = $this->_cpcff_main->get_form($params['formid'])->get_option('paypal_product_name', CP_CALCULATEDFIELDSF_DEFAULT_PRODUCT_NAME);
            foreach ($params as $item => $value)
                $pro_item_name = str_replace('<%'.$item.'%>',(is_array($value)?(implode(", ",$value)):($value)),$pro_item_name);

?>
        <html>
        <head><title>Redirecting to AuthNetSIM...</title></head>
        <body>
        <form method="POST" name="AuthNetSIMForm" id="AuthNetSIMForm" action="<?php echo $ppurl; ?>">
<INPUT TYPE=HIDDEN name="x_fp_sequence" value="<?php echo $sequence; ?>" />
<INPUT TYPE=HIDDEN name="x_fp_timestamp" value="<?php echo $timestamp; ?>" />
<INPUT TYPE=HIDDEN name="x_fp_hash" value="<?php echo $fingerprint; ?>" />
<INPUT TYPE=HIDDEN NAME="x_login" VALUE="<?php echo $rows[0]->AuthNetSIM_api_username; ?>">
<INPUT TYPE=HIDDEN NAME="x_version" VALUE="3.1">
<INPUT TYPE=HIDDEN NAME="x_method" VALUE="CC">
<INPUT TYPE=HIDDEN NAME="x_show_form" VALUE="PAYMENT_FORM">
<INPUT TYPE=HIDDEN NAME="x_invoice_num" VALUE="CFF-<?php echo $params["itemnumber"]; ?>">
<INPUT TYPE=HIDDEN NAME="x_description" VALUE="<?php echo esc_attr($pro_item_name); ?>">
<INPUT TYPE=HIDDEN NAME="x_cust_id" VALUE="cust<?php echo $params["itemnumber"]; ?>">
<INPUT TYPE=HIDDEN NAME="x_amount" VALUE="<?php echo $params["final_price"]; ?>">
<INPUT TYPE=HIDDEN NAME="x_relay_response" VALUE="FALSE" />
<INPUT TYPE=HIDDEN NAME="x_relay_url" VALUE="<?php echo $rows[0]->x_receipt_url; ?>" />
<INPUT TYPE=HIDDEN NAME="x_receipt_link_method" VALUE="POST">
<INPUT TYPE=HIDDEN NAME="x_receipt_link_text" VALUE="Please click here to complete the payment process.">
<INPUT TYPE=HIDDEN NAME="x_receipt_link_URL" VALUE="<?php echo $rows[0]->x_receipt_url; ?>">
<INPUT TYPE=HIDDEN NAME="x_cancel_URL" VALUE="<?php echo $rows[0]->x_cancel_url; ?>">
<?php if ($rows[0]->authnet_f_x_first_name != '') { ?><INPUT TYPE=HIDDEN NAME="x_first_name" VALUE="<?php echo $params[$rows[0]->authnet_f_x_first_name]; ?>"><?php } ?>`
<?php if ($rows[0]->authnet_f_x_last_name  != '') { ?><INPUT TYPE=HIDDEN NAME="x_last_name" VALUE="<?php echo $params[$rows[0]->authnet_f_x_last_name]; ?>"><?php } ?>
<?php if ($rows[0]->authnet_f_x_company    != '') { ?><INPUT TYPE=HIDDEN NAME="x_company" VALUE="<?php echo $params[$rows[0]->authnet_f_x_company]; ?>"><?php } ?>
<?php if ($rows[0]->authnet_f_x_address    != '') { ?><INPUT TYPE=HIDDEN NAME="x_address" VALUE="<?php echo $params[$rows[0]->authnet_f_x_address]; ?>"><?php } ?>
<?php if ($rows[0]->authnet_f_x_city       != '') { ?><INPUT TYPE=HIDDEN NAME="x_city" VALUE="<?php echo $params[$rows[0]->authnet_f_x_city]; ?>"><?php } ?>
<?php if ($rows[0]->authnet_f_x_state      != '') { ?><INPUT TYPE=HIDDEN NAME="x_state" VALUE="<?php echo $params[$rows[0]->authnet_f_x_state]; ?>"><?php } ?>
<?php if ($rows[0]->authnet_f_x_country    != '') { ?><INPUT TYPE=HIDDEN NAME="x_country" VALUE="<?php echo $params[$rows[0]->authnet_f_x_country]; ?>"><?php } ?>
<?php if ($rows[0]->authnet_f_x_zip        != '') { ?><INPUT TYPE=HIDDEN NAME="x_zip" VALUE="<?php echo $params[$rows[0]->authnet_f_x_zip]; ?>"><?php } ?>
<?php if ($rows[0]->authnet_f_x_email      != '') { ?><INPUT TYPE=HIDDEN NAME="x_email" VALUE="<?php echo $params[$rows[0]->authnet_f_x_email]; ?>"><?php } ?>
<?php if ($rows[0]->authnet_f_x_phone      != '') { ?><INPUT TYPE=HIDDEN NAME="x_phone" VALUE="<?php echo $params[$rows[0]->authnet_f_x_phone]; ?>"><?php } ?>
<?php if ($rows[0]->authnet_f_x_fax        != '') { ?><INPUT TYPE=HIDDEN NAME="x_fax" VALUE="<?php echo $params[$rows[0]->authnet_f_x_fax]; ?>"><?php } ?>
        </form>
        <script type="text/javascript">document.AuthNetSIMForm.submit();</script>
        </body>
        </html>
<?php



            exit;
		} // end pp_AuthNetSIM


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


        public function register_tags( $tags )
        {
            $tags[] = 'x_trans_id';
            $tags[] = 'x_invoice_num';
            return $tags;
        }


		public function pp_AuthNetSIM_update_status( )
		{
            if (
				!isset($_POST["x_response_code"]) ||
				$_POST["x_response_code"] != '1' ||
				substr($_POST["x_invoice_num"],0,4) != 'CFF-'
			)
			{
				if (isset($_POST["x_response_code"]) && substr($_POST["x_invoice_num"],0,4) == 'CFF-')
				{
					global $wpdb;
                    $rows = $wpdb->get_results(
						        $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", substr($_POST["x_invoice_num"],4) )
					        );
					echo '<html><body><script type="text/javascript">document.location="'.$rows[0]->x_cancel_url.'";</script></body></html>';
					//echo 'payment failed';
					exit;
				}
				else
				    return;
			}

            $_GET['itemnumber'] = substr($_POST["x_invoice_num"],4);
			$itemnumber = $_GET['itemnumber'];

            $submission = CPCFF_SUBMISSIONS::get($itemnumber);
            if (empty( $submission )) return;

            $params = $submission->paypal_post;
			$params['itemnumber'] = $itemnumber;
            $params["x_trans_id"] = $_POST["x_trans_id"];
            $params["x_invoice_num"] = $_POST["x_invoice_num"];

            $submission = CPCFF_SUBMISSIONS::get($itemnumber);
			if(empty($submission)) return;

            $form_obj = CPCFF_SUBMISSIONS::get_form($itemnumber);
            if($submission->paid == 0)
            {
			    CPCFF_SUBMISSIONS::update($itemnumber, array('paid'=>1, 'paypal_post' => $params));

                do_action( 'cpcff_payment_processed', $params );

			    if ($form_obj->get_option('paypal_notiemails', '0') != '1')
			    	$this->_cpcff_main->send_mails($itemnumber);
            }
            echo '<html><body><script type="text/javascript">document.location="'.CPCFF_AUXILIARY::replace_params_into_url($form_obj->get_option('fp_return_page', CP_CALCULATEDFIELDSF_DEFAULT_fp_return_page), $params).'";</script></body></html>';
            exit;
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
    $cff_AuthNetSIM_obj = new CFF_AuthNetSIM();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cff_AuthNetSIM_obj);
}

