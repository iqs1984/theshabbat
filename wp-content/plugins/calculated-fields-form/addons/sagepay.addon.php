<?php
/*

*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CFF_SagePay' ) )
{
    class CFF_SagePay extends CPCFF_BaseAddon
    {
		static public $category = 'Payment Gateways';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-SagePay-20160706";
		protected $name = "CFF - SagePay Payment Gateway";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#sagepay-addon';
        protected $default_pay_label = "Pay with SagePay";

		public function get_addon_form_settings( $form_id )
		{
			global $wpdb;
			$table = $wpdb->prefix.$this->form_table;

			// Insertion in database
			if(
				isset( $_REQUEST[ 'cpabc_SagePay_id' ] )
			)
			{
                $this->add_field_verify($table, 'sagep_f_x_first_name');
                $this->add_field_verify($table, 'sagep_f_x_last_name');
                $this->add_field_verify($table, 'sagep_f_x_address');
                $this->add_field_verify($table, 'sagep_f_x_city');
                $this->add_field_verify($table, 'sagep_f_x_state');
                $this->add_field_verify($table, 'sagep_f_x_country');
                $this->add_field_verify($table, 'sagep_f_x_zip');

			    $wpdb->delete( $table, array( 'formid' => $form_id ), array( '%d' ) );
				$wpdb->insert(
								$table,
								array(
									'formid' => $form_id,
									'SagePay_api_username'	 => $_REQUEST["SagePay_api_username"],
									'SagePay_api_password'	 => $_REQUEST["SagePay_api_password"],
									'enabled'	             => $_REQUEST["SagePay_enabled"],
									'paypal_mode'	         => $_REQUEST["SagePay_mode"],
									'sagep_f_x_first_name'	 => $_REQUEST["sagep_f_x_first_name"],
                                    'sagep_f_x_last_name'	 => $_REQUEST["sagep_f_x_last_name"],
                                    'sagep_f_x_address'	     => $_REQUEST["sagep_f_x_address"],
                                    'sagep_f_x_city'	     => $_REQUEST["sagep_f_x_city"],
                                    'sagep_f_x_state'	     => $_REQUEST["sagep_f_x_state"],
                                    'sagep_f_x_country'	     => $_REQUEST["sagep_f_x_country"],
                                    'sagep_f_x_zip'	         => $_REQUEST["sagep_f_x_zip"],
                                    'enable_option_yes'	 => $_REQUEST["SagePay_enable_option_yes"]
								),
								array( '%d', '%s', '%s', '%s', '%s',
								             '%s', '%s', '%s', '%s',
								             '%s', '%s', '%s', '%s' )
							);
			}


			$rows = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM ".$table." WHERE formid=%d", $form_id )
					);
			if (!count($rows))
			{
			    $row["SagePay_api_username"] = "";
			    $row["SagePay_api_password"] = "";
			    $row["enabled"] = "0";
			    $row["paypal_mode"] = "production";
                $row["sagep_f_x_first_name"] = '';
                $row["sagep_f_x_last_name"] = '';
                $row["sagep_f_x_address"] = '';
                $row["sagep_f_x_city"] = '';
                $row["sagep_f_x_state"] = '';
                $row["sagep_f_x_country"] = '';
                $row["sagep_f_x_zip"] = '';
                $row["enable_option_yes"] = $this->default_pay_label;
			} else {
			    $row["SagePay_api_username"] = $rows[0]->SagePay_api_username;
			    $row["SagePay_api_password"] = $rows[0]->SagePay_api_password;
			    $row["enabled"] = $rows[0]->enabled;
			    $row["paypal_mode"] = $rows[0]->paypal_mode;
                $row["sagep_f_x_first_name"] = $rows[0]->sagep_f_x_first_name;
                $row["sagep_f_x_last_name"] = $rows[0]->sagep_f_x_last_name;
                $row["sagep_f_x_address"] = $rows[0]->sagep_f_x_address;
                $row["sagep_f_x_city"] = $rows[0]->sagep_f_x_city;
                $row["sagep_f_x_state"] = $rows[0]->sagep_f_x_state;
                $row["sagep_f_x_country"] = $rows[0]->sagep_f_x_country;
                $row["sagep_f_x_zip"] = $rows[0]->sagep_f_x_zip;
                $row["enable_option_yes"] = $rows[0]->enable_option_yes;
			}

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<div id="metabox_sagepay_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_sagepay_addon_form_settings' ) ); ?>" >
				<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
				<div class="inside">
				   <input type="hidden" name="cpabc_SagePay_id" value="1" />
                   <table class="form-table">
                    <tr valign="top">
                    <th scope="row"><?php _e('Enable SagePay? (if enabled PayPal Standard is disabled)', 'calculated-fields-form'); ?></th>
                    <td><select name="SagePay_enabled">
                         <option value="0" <?php if (!$row["enabled"]) echo 'selected'; ?>><?php _e('No', 'calculated-fields-form'); ?></option>
                         <option value="1" <?php if ($row["enabled"] == '1') echo 'selected'; ?>><?php _e('Yes', 'calculated-fields-form'); ?></option>
                         <option value="2" <?php if ($row["enabled"] == '2') echo 'selected'; ?>><?php _e('Optional: This payment method + Pay Later (submit without payment)', 'calculated-fields-form'); ?></option>
                         <option value="3" <?php if ($row["enabled"] == '3') echo 'selected'; ?>><?php _e('Optional: This payment method + Other payment methods (enabled)', 'calculated-fields-form'); ?></option>
                         <option value="4" <?php if ($row["enabled"] == '4') echo 'selected'; ?>><?php _e('Optional: This payment method + Other payment methods  + Pay Later ', 'calculated-fields-form'); ?></option>
                         </select>
                         <div style="margin-top:10px;background:#EEF5FB;border: 1px dotted #888888;padding:10px;width:260px;">
                           <?php _e( 'Label for this payment option', 'calculated-fields-form' ); ?>:<br />
                           <input type="text" name="SagePay_enable_option_yes" size="40" style="width:250px;" value="<?php echo esc_attr($row['enable_option_yes']); ?>" />
                        </div>
                    </td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Vendor ID', 'calculated-fields-form'); ?></th>
                    <td><input type="text" name="SagePay_api_username" size="20" value="<?php echo esc_attr($row["SagePay_api_username"]); ?>" /><br />
                        <em>Change this value with Vendor ID received from SagePay<em></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e(' SagePay Encrypt Password', 'calculated-fields-form');?></th>
                    <td><input type="text" name="SagePay_api_password" size="40" value="<?php echo esc_attr($row["SagePay_api_password"]); ?>" /><br />
                        <em>Change this value with SagePay Encrypt Password</em></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Mode', 'calculated-fields-form');  ?></th>
                    <td><select name="SagePay_mode">
                         <option value="production" <?php if ($row["paypal_mode"] != 'sandbox') echo 'selected'; ?>><?php _e('Production - real payments processed', 'calculated-fields-form'); ?></option>
                         <option value="sandbox" <?php if ($row["paypal_mode"] == 'sandbox') echo 'selected'; ?>><?php _e('SandBox - Testing sandbox area', 'calculated-fields-form'); ?></option>
                        </select>
                    </td>
                    </tr>
                   </table>
                   <hr />
                   <strong>ID of the fields to forward to Sage Payments (ex: fieldname1, fieldname2, ...):</strong>
                   <table class="form-table">
                    <tr valign="top">
                    <th scope="row"><?php _e('First Name', 'calculated-fields-form'); ?>:</th>
                    <td><input type="text" name="sagep_f_x_first_name" size="40" value="<?php echo esc_attr($row["sagep_f_x_first_name"]); ?>" /><br />
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Last Name', 'calculated-fields-form'); ?>:</th>
                    <td><input type="text" name="sagep_f_x_last_name" size="40" value="<?php echo esc_attr($row["sagep_f_x_last_name"]); ?>" /><br />
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Address', 'calculated-fields-form'); ?>:</th>
                    <td><input type="text" name="sagep_f_x_address" size="40" value="<?php echo esc_attr($row["sagep_f_x_address"]); ?>" /><br />
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('City', 'calculated-fields-form'); ?>:</th>
                    <td><input type="text" name="sagep_f_x_city" size="40" value="<?php echo esc_attr($row["sagep_f_x_city"]); ?>" /><br />
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('State', 'calculated-fields-form'); ?>:</th>
                    <td><input type="text" name="sagep_f_x_state" size="40" value="<?php echo esc_attr($row["sagep_f_x_state"]); ?>" /><br />
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Country', 'calculated-fields-form'); ?>:</th>
                    <td><input type="text" name="sagep_f_x_country" size="40" value="<?php echo esc_attr($row["sagep_f_x_country"]); ?>" /><br />
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Zip/Postal Code', 'calculated-fields-form'); ?>:</th>
                    <td><input type="text" name="sagep_f_x_zip" size="40" value="<?php echo esc_attr($row["sagep_f_x_zip"]); ?>" /><br />
                    </tr>
                   </table>
                   <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
				</div>
			</div>
			<?php
		} // end get_addon_form_settings



		/************************ ADDON CODE *****************************/

        /************************ ATTRIBUTES *****************************/

        private $form_table = 'cff_form_SagePay';
        private $_inserted = false;
		private $_cpcff_main;
        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->_cpcff_main = CPCFF_MAIN::instance();
			$this->description = __("The add-on adds support for SagePay payments", 'calculated-fields-form' );
            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;

			add_action( 'cpcff_process_data_before_insert', array( &$this, 'pp_before_insert' ), 10, 3 );

			add_action( 'cpcff_process_data', array( &$this, 'pp_SagePay' ), 11, 1 );

			add_action( 'init', array( &$this, 'pp_SagePay_update_status' ), 10, 0 );

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
			$db_queries = array( "CREATE TABLE ".$wpdb->prefix.$this->form_table." (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					formid INT NOT NULL,
					enabled varchar(10) DEFAULT '0' NOT NULL ,
					SagePay_api_username varchar(255) DEFAULT '' NOT NULL ,
					SagePay_api_password varchar(255) DEFAULT '' NOT NULL ,
					paypal_mode varchar(255) DEFAULT '' NOT NULL ,
					sagep_f_x_first_name varchar(255) DEFAULT '' NOT NULL ,
					sagep_f_x_last_name varchar(255) DEFAULT '' NOT NULL ,
					sagep_f_x_address varchar(255) DEFAULT '' NOT NULL ,
					sagep_f_x_city varchar(255) DEFAULT '' NOT NULL ,
					sagep_f_x_state varchar(255) DEFAULT '' NOT NULL ,
					sagep_f_x_country varchar(255) DEFAULT '' NOT NULL ,
					sagep_f_x_zip varchar(255) DEFAULT '' NOT NULL ,
                    enable_option_yes varchar(255) DEFAULT '' NOT NULL ,
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
			    $form_code = preg_replace( '/<!--addons-payment-options-->/i', '<!--addons-payment-options--><div><input type="radio" name="bccf_payment_option_paypal" vt="0" value="0"> '.__( $this->_cpcff_main->get_form($id)->get_option('enable_paypal_option_no',CP_CALCULATEDFIELDSF_PAYPAL_OPTION_NO), 'calculated-fields-form').'</div>', $form_code );

			if (substr_count ($form_code, 'name="bccf_payment_option_paypal"') > 1)
			    $form_code = str_replace( 'id="field-c0" style="display:none">', 'id="field-c0">', $form_code);

            return $form_code;
        }


		/**
         * process payment
         */
		public function pp_SagePay($params)
		{
            global $wpdb;

            $rows = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $params["formid"] )
					);

			$payment_option = (isset($_POST["bccf_payment_option_paypal"])?$_POST["bccf_payment_option_paypal"]:$this->addonID);
			if (empty( $rows ) || !$rows[0]->enabled || $payment_option != $this->addonID || floatval($params["final_price"]) == 0)
			    return;

			$form_obj = $this->_cpcff_main->get_form($params['formid']);

			if($form_obj->get_option('paypal_notiemails', '0') == '1')
			    $this->_cpcff_main->send_mails($params['itemnumber']);

            $pro_item_name = $form_obj->get_option('paypal_product_name', CP_CALCULATEDFIELDSF_DEFAULT_PRODUCT_NAME);
            foreach ($params as $item => $value)
                $pro_item_name = str_replace('<%'.$item.'%>',(is_array($value)?(implode(", ",$value)):($value)),$pro_item_name);

            $sagePay = new SagePayCFF();
            $sagePay->encryptPassword = $rows[0]->SagePay_api_password;
            $sagePay->setCurrency( strtoupper($form_obj->get_option('currency', CP_CALCULATEDFIELDSF_DEFAULT_CURRENCY)) );

            $sagePay->setAmount($params["final_price"]);
            $sagePay->setDescription( $pro_item_name );


            if (!empty($params[ $rows[0]->sagep_f_x_last_name ]))
                $sagePay->setBillingSurname( $params[ $rows[0]->sagep_f_x_last_name ] );
            if (!empty($params[ $rows[0]->sagep_f_x_first_name ]))
                $sagePay->setBillingFirstnames( $params[ $rows[0]->sagep_f_x_first_name ] );
            if (!empty($params[ $rows[0]->sagep_f_x_address ]))
                $sagePay->setBillingAddress1(  $params[ $rows[0]->sagep_f_x_address ] );
            if (!empty($params[ $rows[0]->sagep_f_x_zip ]))
                $sagePay->setBillingPostCode( $params[ $rows[0]->sagep_f_x_zip ] );
            if (!empty($params[ $rows[0]->sagep_f_x_city ]))
                $sagePay->setBillingCity( $params[ $rows[0]->sagep_f_x_city ] );
            if (!empty($params[ $rows[0]->sagep_f_x_state ]))
                $sagePay->setBillingState( $params[ $rows[0]->sagep_f_x_state ] );
            if (!empty($params[ $rows[0]->sagep_f_x_country ]))
                $sagePay->setBillingCountry( $this->getISO3166CountryCode($params[ $rows[0]->sagep_f_x_country ]) );
            else
                $sagePay->setBillingCountry( 'GB' );
            $sagePay->setDeliverySameAsBilling();

            $sagePay->setSuccessURL( (CPCFF_AUXILIARY::site_url().'/?cff_SagePay_ipncheck=1&itemnumber='.$params["itemnumber"]).'&tx='.$sagePay->getVendorTxCode() );
            $sagePay->setFailureURL( ($_POST["cp_ref_page"]) );

            if ($rows[0]->paypal_mode  == "sandbox")
                $ppurl = 'https://test.sagepay.com/gateway/service/vspform-register.vsp';
            else
                $ppurl = 'https://live.sagepay.com/gateway/service/vspform-register.vsp';

?>
        <html>
        <head><title>Redirecting to SagePay...</title></head>
        <body>
        <form method="POST" name="SagePayForm" id="SagePayForm" action="<?php echo $ppurl; ?>">
                <input type="hidden" name="VPSProtocol" value= "3.00">
                <input type="hidden" name="TxType" value= "PAYMENT">
                <input type="hidden" name="Vendor" value= "<?php echo $rows[0]->SagePay_api_username; ?>">
                <input type="hidden" name="Crypt" value= "<?php echo $sagePay->getCrypt(); ?>">
                <input type="submit" value="continue to SagePay">
        </form>
        <script type="text/javascript">document.SagePayForm.submit();</script>
        </body>
        </html>
<?php



            exit;
		} // end pp_SagePay


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

		public function pp_SagePay_update_status( )
		{
            if(
				!isset( $_GET['cff_SagePay_ipncheck'] ) ||
				$_GET['cff_SagePay_ipncheck'] != '1' ||
				!isset( $_GET["itemnumber"] )
			) return;

			$itemnumber = intval(@$_GET["itemnumber"]);
			$submission = CPCFF_SUBMISSIONS::get($itemnumber);
            if(empty($submission)) return;

			$form_obj = CPCFF_SUBMISSIONS::get_form($itemnumber);
            if($submission->paid == 0)
            {
                $submission->paypal_post['txcode'] = $_GET["tx"];
				CPCFF_SUBMISSIONS::update($itemnumber,array('paid'=>1,'paypal_post' => serialize($submission->paypal_post)));

				$submission->paypal_post['itemnumber'] = $itemnumber;
                do_action( 'cpcff_payment_processed', $submission->paypal_post );

                if ($form_obj->get_option('paypal_notiemails', '0') != '1')
					$this->_cpcff_main->send_mails($itemnumber);
            }

            header("Location: ".CPCFF_AUXILIARY::replace_params_into_url($form_obj->get_option('fp_return_page', CP_CALCULATEDFIELDSF_DEFAULT_fp_return_page), $submission->paypal_post));
            exit();
		}

        function getISO3166CountryCode($country)
        {
            $C["AFGHANISTAN"] = "AF";
            $C["ÅLAND ISLANDS"] = "AX";
            $C["ALBANIA"] = "AL";
            $C["ALGERIA"] = "DZ";
            $C["AMERICAN SAMOA"] = "AS";
            $C["ANDORRA"] = "AD";
            $C["ANGOLA"] = "AO";
            $C["ANGUILLA"] = "AI";
            $C["ANTARCTICA"] = "AQ";
            $C["ANTIGUA AND BARBUDA"] = "AG";
            $C["ARGENTINA"] = "AR";
            $C["ARMENIA"] = "AM";
            $C["ARUBA"] = "AW";
            $C["AUSTRALIA"] = "AU";
            $C["AUSTRIA"] = "AT";
            $C["AZERBAIJAN"] = "AZ";
            $C["BAHAMAS"] = "BS";
            $C["BAHRAIN"] = "BH";
            $C["BANGLADESH"] = "BD";
            $C["BARBADOS"] = "BB";
            $C["BELARUS"] = "BY";
            $C["BELGIUM"] = "BE";
            $C["BELIZE"] = "BZ";
            $C["BENIN"] = "BJ";
            $C["BERMUDA"] = "BM";
            $C["BHUTAN"] = "BT";
            $C["BOLIVIA"] = "BO";
            $C["BONAIRE"] = "BQ";
            $C["BOSNIA AND HERZEGOVINA"] = "BA";
            $C["BOTSWANA"] = "BW";
            $C["BOUVET ISLAND"] = "BV";
            $C["BRAZIL"] = "BR";
            $C["BRITISH INDIAN OCEAN TERRITORY"] = "IO";
            $C["BRUNEI DARUSSALAM"] = "BN";
            $C["BULGARIA"] = "BG";
            $C["BURKINA FASO"] = "BF";
            $C["BURUNDI"] = "BI";
            $C["CAMBODIA"] = "KH";
            $C["CAMEROON"] = "CM";
            $C["CANADA"] = "CA";
            $C["CAPE VERDE"] = "CV";
            $C["CAYMAN ISLANDS"] = "KY";
            $C["CENTRAL AFRICAN REPUBLIC"] = "CF";
            $C["CHAD"] = "TD";
            $C["CHILE"] = "CL";
            $C["CHINA"] = "CN";
            $C["CHRISTMAS ISLAND"] = "CX";
            $C["COCOS (KEELING) ISLANDS"] = "CC";
            $C["COLOMBIA"] = "CO";
            $C["COMOROS"] = "KM";
            $C["CONGO"] = "CG";
            $C["CONGO"] = "CD";
            $C["COOK ISLANDS"] = "CK";
            $C["COSTA RICA"] = "CR";
            $C["CÔTE D'IVOIRE"] = "CI";
            $C["CROATIA"] = "HR";
            $C["CUBA"] = "CU";
            $C["CURAÇAO"] = "CW";
            $C["CYPRUS"] = "CY";
            $C["CZECH REPUBLIC"] = "CZ";
            $C["DENMARK"] = "DK";
            $C["DJIBOUTI"] = "DJ";
            $C["DOMINICA"] = "DM";
            $C["DOMINICAN REPUBLIC"] = "DO";
            $C["ECUADOR"] = "EC";
            $C["EGYPT"] = "EG";
            $C["EL SALVADOR"] = "SV";
            $C["EQUATORIAL GUINEA"] = "GQ";
            $C["ERITREA"] = "ER";
            $C["ESTONIA"] = "EE";
            $C["ETHIOPIA"] = "ET";
            $C["FALKLAND ISLANDS (MALVINAS)"] = "FK";
            $C["FAROE ISLANDS"] = "FO";
            $C["FIJI"] = "FJ";
            $C["FINLAND"] = "FI";
            $C["FRANCE"] = "FR";
            $C["FRENCH GUIANA"] = "GF";
            $C["FRENCH POLYNESIA"] = "PF";
            $C["FRENCH SOUTHERN TERRITORIES"] = "TF";
            $C["GABON"] = "GA";
            $C["GAMBIA"] = "GM";
            $C["GEORGIA"] = "GE";
            $C["GERMANY"] = "DE";
            $C["GHANA"] = "GH";
            $C["GIBRALTAR"] = "GI";
            $C["GREECE"] = "GR";
            $C["GREENLAND"] = "GL";
            $C["GRENADA"] = "GD";
            $C["GUADELOUPE"] = "GP";
            $C["GUAM"] = "GU";
            $C["GUATEMALA"] = "GT";
            $C["GUERNSEY"] = "GG";
            $C["GUINEA"] = "GN";
            $C["GUINEA-BISSAU"] = "GW";
            $C["GUYANA"] = "GY";
            $C["HAITI"] = "HT";
            $C["HEARD ISLAND AND MCDONALD ISLANDS"] = "HM";
            $C["HOLY SEE (VATICAN CITY STATE)"] = "VA";
            $C["HONDURAS"] = "HN";
            $C["HONG KONG"] = "HK";
            $C["HUNGARY"] = "HU";
            $C["ICELAND"] = "IS";
            $C["INDIA"] = "IN";
            $C["INDONESIA"] = "ID";
            $C["IRAN"] = "IR";
            $C["IRAQ"] = "IQ";
            $C["IRELAND"] = "IE";
            $C["ISLE OF MAN"] = "IM";
            $C["ISRAEL"] = "IL";
            $C["ITALY"] = "IT";
            $C["JAMAICA"] = "JM";
            $C["JAPAN"] = "JP";
            $C["JERSEY"] = "JE";
            $C["JORDAN"] = "JO";
            $C["KAZAKHSTAN"] = "KZ";
            $C["KENYA"] = "KE";
            $C["KIRIBATI"] = "KI";
            $C["DEMOCRATIC PEOPLE'S REPUBLIC OF KOREA"] = "KP";
            $C["REPUBLIC OF KOREA"] = "KR";
            $C["KUWAIT"] = "KW";
            $C["KYRGYZSTAN"] = "KG";
            $C["LAO PEOPLE'S DEMOCRATIC REPUBLIC"] = "LA";
            $C["LATVIA"] = "LV";
            $C["LEBANON"] = "LB";
            $C["LESOTHO"] = "LS";
            $C["LIBERIA"] = "LR";
            $C["LIBYA"] = "LY";
            $C["LIECHTENSTEIN"] = "LI";
            $C["LITHUANIA"] = "LT";
            $C["LUXEMBOURG"] = "LU";
            $C["MACAO"] = "MO";
            $C["MACEDONIA"] = "MK";
            $C["MADAGASCAR"] = "MG";
            $C["MALAWI"] = "MW";
            $C["MALAYSIA"] = "MY";
            $C["MALDIVES"] = "MV";
            $C["MALI"] = "ML";
            $C["MALTA"] = "MT";
            $C["MARSHALL ISLANDS"] = "MH";
            $C["MARTINIQUE"] = "MQ";
            $C["MAURITANIA"] = "MR";
            $C["MAURITIUS"] = "MU";
            $C["MAYOTTE"] = "YT";
            $C["MEXICO"] = "MX";
            $C["MICRONESIA"] = "FM";
            $C["MOLDOVA"] = "MD";
            $C["MONACO"] = "MC";
            $C["MONGOLIA"] = "MN";
            $C["MONTENEGRO"] = "ME";
            $C["MONTSERRAT"] = "MS";
            $C["MOROCCO"] = "MA";
            $C["MOZAMBIQUE"] = "MZ";
            $C["MYANMAR"] = "MM";
            $C["NAMIBIA"] = "NA";
            $C["NAURU"] = "NR";
            $C["NEPAL"] = "NP";
            $C["NETHERLANDS"] = "NL";
            $C["NEW CALEDONIA"] = "NC";
            $C["NEW ZEALAND"] = "NZ";
            $C["NICARAGUA"] = "NI";
            $C["NIGER"] = "NE";
            $C["NIGERIA"] = "NG";
            $C["NIUE"] = "NU";
            $C["NORFOLK ISLAND"] = "NF";
            $C["NORTHERN MARIANA ISLANDS"] = "MP";
            $C["NORWAY"] = "NO";
            $C["OMAN"] = "OM";
            $C["PAKISTAN"] = "PK";
            $C["PALAU"] = "PW";
            $C["PALESTINE"] = "PS";
            $C["PANAMA"] = "PA";
            $C["PAPUA NEW GUINEA"] = "PG";
            $C["PARAGUAY"] = "PY";
            $C["PERU"] = "PE";
            $C["PHILIPPINES"] = "PH";
            $C["PITCAIRN"] = "PN";
            $C["POLAND"] = "PL";
            $C["PORTUGAL"] = "PT";
            $C["PUERTO RICO"] = "PR";
            $C["QATAR"] = "QA";
            $C["RÉUNION"] = "RE";
            $C["ROMANIA"] = "RO";
            $C["RUSSIAN FEDERATION"] = "RU";
            $C["RWANDA"] = "RW";
            $C["SAINT BARTHÉLEMY"] = "BL";
            $C["SAINT HELENA"] = "SH";
            $C["SAINT KITTS AND NEVIS"] = "KN";
            $C["SAINT LUCIA"] = "LC";
            $C["SAINT MARTIN (FRENCH PART)"] = "MF";
            $C["SAINT PIERRE AND MIQUELON"] = "PM";
            $C["SAINT VINCENT AND THE GRENADINES"] = "VC";
            $C["SAMOA"] = "WS";
            $C["SAN MARINO"] = "SM";
            $C["SAO TOME AND PRINCIPE"] = "ST";
            $C["SAUDI ARABIA"] = "SA";
            $C["SENEGAL"] = "SN";
            $C["SERBIA"] = "RS";
            $C["SEYCHELLES"] = "SC";
            $C["SIERRA LEONE"] = "SL";
            $C["SINGAPORE"] = "SG";
            $C["SINT MAARTEN (DUTCH PART)"] = "SX";
            $C["SLOVAKIA"] = "SK";
            $C["SLOVENIA"] = "SI";
            $C["SOLOMON ISLANDS"] = "SB";
            $C["SOMALIA"] = "SO";
            $C["SOUTH AFRICA"] = "ZA";
            $C["SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS"] = "GS";
            $C["SOUTH SUDAN"] = "SS";
            $C["SPAIN"] = "ES";
            $C["SRI LANKA"] = "LK";
            $C["SUDAN"] = "SD";
            $C["SURINAME"] = "SR";
            $C["SVALBARD AND JAN MAYEN"] = "SJ";
            $C["SWAZILAND"] = "SZ";
            $C["SWEDEN"] = "SE";
            $C["SWITZERLAND"] = "CH";
            $C["SYRIAN ARAB REPUBLIC"] = "SY";
            $C["TAIWAN"] = "TW";
            $C["TAJIKISTAN"] = "TJ";
            $C["TANZANIA"] = "TZ";
            $C["THAILAND"] = "TH";
            $C["TIMOR-LESTE"] = "TL";
            $C["TOGO"] = "TG";
            $C["TOKELAU"] = "TK";
            $C["TONGA"] = "TO";
            $C["TRINIDAD AND TOBAGO"] = "TT";
            $C["TUNISIA"] = "TN";
            $C["TURKEY"] = "TR";
            $C["TURKMENISTAN"] = "TM";
            $C["TURKS AND CAICOS ISLANDS"] = "TC";
            $C["TUVALU"] = "TV";
            $C["UGANDA"] = "UG";
            $C["UKRAINE"] = "UA";
            $C["UNITED ARAB EMIRATES"] = "AE";
            $C["UNITED KINGDOM"] = "GB";
            $C["UNITED STATES"] = "US";
            $C["USA"] = "US";
            $C["UNITED STATES MINOR OUTLYING ISLANDS"] = "UM";
            $C["URUGUAY"] = "UY";
            $C["UZBEKISTAN"] = "UZ";
            $C["VANUATU"] = "VU";
            $C["VENEZUELA"] = "VE";
            $C["VIET NAM"] = "VN";
            $C["VIRGIN ISLANDS"] = "VG";
            $C["VIRGIN ISLANDS"] = "VI";
            $C["WALLIS AND FUTUNA"] = "WF";
            $C["WESTERN SAHARA"] = "EH";
            $C["YEMEN"] = "YE";
            $C["ZAMBIA"] = "ZM";
            $C["ZIMBABWE"] = "ZW";

            if ( !empty($C[strtoupper($country)]) )
                return $C[strtoupper($country)];
            else if ( array_search(strtoupper($country),array_values($C)) )
                return strtoupper($country);
            else
                return 'GB';
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
    $cff_SagePay_obj = new CFF_SagePay();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cff_SagePay_obj);
}

if( !class_exists( 'SagePayCFF' ) )
{
/**
 * SagePay Class for Form Integration Method, utilizes Protocol V3
 *
 * @author    Timur Olzhabayev
 * @copyright Copyright (c) 2013, Timur Olzhabayev
 * @license   http://www.opensource.org/licenses/mit-license.php
 */


class SagePayCFF {

        protected $vendorTxCode;
        protected $amount;
        protected $currency;
        protected $description;
        protected $successURL;
        protected $failureURL;
        protected $customerName;
        protected $customerEMail;
        protected $vendorEMail;
        protected $sendEMail;
        protected $eMailMessage;
        protected $billingSurname;
        protected $billingFirstnames;
        protected $billingAddress1;
        protected $billingAddress2;
        protected $billingPostCode;
        protected $billingCountry;
        protected $billingCity;
        protected $billingState;
        protected $billingPhone;
        protected $deliverySurname;
        protected $deliveryFirstnames;
        protected $deliveryAddress1;
        protected $deliveryAddress2;
        protected $deliveryCity;
        protected $deliveryPostCode;
        protected $deliveryCountry;
        protected $deliveryState;
        protected $deliveryPhone;
        protected $basket;
        protected $allowGiftAid;
        protected $applyAVSCV2;
        protected $apply3DSecure;
        protected $billingAgreement;
        protected $basketXML;
        protected $customerXML;
        protected $surchargeXML;
        protected $vendorData;
        protected $referrerID;
        protected $language;
        protected $website;
        public $encryptPassword = "PUTYOURPASSWORDHERE";

        public function __construct() {
                $this->setVendorTxCode($this->createVendorTxCode());
        }

        public function getCrypt() {
                        $cryptString = 'VendorTxCode='.$this->getVendorTxCode();
                        $cryptString.= '&ReferrerID='.$this->getReferrerID();
                        $cryptString.= '&Amount='.$this->getAmount();
                        $cryptString.= '&Currency='.$this->getCurrency();
                        $cryptString.= '&Description='.$this->getDescription();
                        $cryptString.= '&SuccessURL='.$this->getSuccessURL();
                        $cryptString.= '&FailureURL='.$this->getFailureURL();
                        $cryptString.= '&CustomerName='.$this->getCustomerName();
                        $cryptString.= '&CustomerEMail='.$this->getCustomerEMail();
                        $cryptString.= '&VendorEMail='.$this->getVendorEMail();
                        $cryptString.= '&SendEMail='.$this->getSendEMail();
                        $cryptString.= '&eMailMessage='.$this->getEMailMessage();
                        $cryptString.= '&BillingSurname='.$this->getBillingSurname();
                        $cryptString.= '&BillingFirstnames='.$this->getBillingFirstnames();
                        $cryptString.= '&BillingAddress1='.$this->getBillingAddress1();
                        $cryptString.= '&BillingAddress2='.$this->getBillingAddress2();
                        $cryptString.= '&BillingCity='.$this->getBillingCity();
                        $cryptString.= '&BillingPostCode='.$this->getBillingPostCode();
                        $cryptString.= '&BillingCountry='.$this->getBillingCountry();
                        $cryptString.= '&BillingState='.$this->getBillingState();
                        $cryptString.= '&BillingPhone='.$this->getBillingPhone();
                        $cryptString.= '&DeliverySurname='.$this->getDeliverySurname();
                        $cryptString.= '&DeliveryFirstnames='.$this->getDeliveryFirstnames();
                        $cryptString.= '&DeliveryAddress1='.$this->getDeliveryAddress1();
                        $cryptString.= '&DeliveryAddress2='.$this->getDeliveryAddress2();
                        $cryptString.= '&DeliveryCity='.$this->getDeliveryCity();
                        $cryptString.= '&DeliveryPostCode='.$this->getDeliveryPostCode();
                        $cryptString.= '&DeliveryCountry='.$this->getDeliveryCountry();
                        $cryptString.= '&DeliveryState='.$this->getDeliveryState();
                        $cryptString.= '&DeliveryPhone='.$this->getDeliveryPhone();
                        $cryptString.= '&Basket='.$this->getBasket();
                        $cryptString.= '&AllowGiftAid='.$this->getAllowGiftAid();
                        $cryptString.= '&ApplyAVSCV2='.$this->getApplyAVSCV2();
                        $cryptString.= '&Apply3DSecure='.$this->getApply3DSecure();
                        $cryptString.= '&BillingAgreement='.$this->getBillingAgreement();
                        $cryptString.= '&BasketXML='.$this->getBasketXML();
                        $cryptString.= '&CustomerXML='.$this->getCustomerXML();
                        $cryptString.= '&SurchargeXML='.$this->getSurchargeXML();
                        $cryptString.= '&VendorData='.$this->getVendorData();
                        $cryptString.= '&ReferrerID='.$this->getReferrerID();
                        $cryptString.= '&Language='.$this->getLanguage();
                        $cryptString.= '&Website='.$this->getWebsite();


                        return $this->encryptAndEncode($cryptString);

        }



        protected function createVendorTxCode() {
         $timestamp = date("y-m-d-H-i-s", time());
         $random_number = rand(0,32000)*rand(0,32000);
         return "{$timestamp}-{$random_number}";
        }

        public function setVendorTxCode($code) {
                $this->vendorTxCode = $code;
        }
        public function getVendorTxCode() {
                return $this->vendorTxCode;
        }

        public function setAmount($amount) {
                $this->amount = number_format($amount, 2);
        }

        public function getAmount() {
                return $this->amount;
        }

        public function getCurrency() {
                return $this->currency;
        }

        public function setCurrency($currency) {
                $this->currency = strtoupper($currency);
        }

        public function getSuccessURL() {
                return $this->successURL;
        }
        public function setSuccessURL($url) {
                $this->successURL = $url;
        }
        public function getFailureURL() {
                return $this->failureURL;
        }
        public function setFailureURL($url) {
                $this->failureURL = $url;
        }

        public function getDescription() {
                return $this->description;
        }
        public function setDescription($description) {
                $this->description = $description;
        }

        public function getCustomerName() {
                return $this->customerName;
        }
        public function setCustomerName($name) {
                $this->customerName = $name;
        }

        public function getCustomerEMail() {
                return $this->customerEMail;
        }
        public function setCustomerEMail($email) {
                $this->customerEMail = $email;
        }

        public function getVendorEMail() {
                return $this->vendorEMail;
        }
        public function setVendorEMail($email) {
                $this->vendorEMail = $email;
        }

        public function getSendEMail() {
                return $this->sendEMail;
        }
        public function setSendEMail($sendEmail = 1) {
                $this->sendEMail = $sendEmail;
        }

        public function getEMailMessage() {
                return $this->eMailMessage;
        }
        public function setEMailMessage($emailMessage) {
                $this->eMailMessage = $emailMessage;
        }

        public function setBillingFirstnames($billingFirstnames) {
                $this->billingFirstnames = $billingFirstnames;
        }

        public function getBillingFirstnames() {
                return $this->billingFirstnames;
        }

        public function setBillingSurname($billingSurname) {
                $this->billingSurname = $billingSurname;
        }

        public function getBillingSurname() {
                return $this->billingSurname;
        }

        public function setBillingAddress1($billingAddress1) {
                $this->billingAddress1 = $billingAddress1;
        }

        public function getBillingAddress1() {
                return $this->billingAddress1;
        }

        public function setBillingAddress2($billingAddress2) {
                $this->billingAddress2 = $billingAddress2;
        }

        public function getBillingAddress2() {
                return $this->billingAddress2;
        }

        public function setBillingCity($billingCity) {
                $this->billingCity = $billingCity;
        }

        public function getBillingCity() {
                return $this->billingCity;
        }

        public function setBillingPostCode($billingPostCode) {
                $this->billingPostCode = $billingPostCode;
        }

        public function getBillingPostCode() {
                return $this->billingPostCode;
        }

        public function setBillingState($billingState) {
                $this->billingState = $billingState;
        }

        public function getBillingState() {
                return $this->billingState;
        }

        public function getBillingCountry() {
                return $this->billingCountry;
        }
        public function setBillingCountry($countryISO3166) {
                $this->billingCountry = strtoupper($countryISO3166);
        }

        public function setBillingPhone($phone) {
                $this->billingPhone = $phone;
        }

        public function getBillingPhone() {
                return $this->billingPhone;
        }

        public function setDeliverySurname($surname) {
                $this->deliverySurname = $surname;
        }

        public function getDeliverySurname() {
                return $this->deliverySurname;
        }


        public function setDeliveryFirstnames($firstnames) {
                $this->deliveryFirstnames = $firstnames;
        }

        public function getDeliveryFirstnames() {
                return $this->deliveryFirstnames;
        }

        public function setDeliveryAddress1($address) {
                $this->deliveryAddress1 = $address;
        }

        public function getDeliveryAddress1() {
                return $this->deliveryAddress1;
        }

        public function setDeliveryAddress2($address) {
                $this->deliveryAddress2 = $address;
        }

        public function getDeliveryAddress2() {
                return $this->deliveryAddress2;
        }

        public function setDeliveryCity($city) {
                $this->deliveryCity = $city;
        }

        public function getDeliveryCity() {
                return $this->deliveryCity;
        }

        public function setDeliveryPostCode($zip) {
                $this->deliveryPostCode = $zip;
        }

        public function getDeliveryPostCode() {
                return $this->deliveryPostCode;
        }

        public function setDeliveryCountry($country) {
                $this->deliveryCountry = strtoupper($country);
        }

        public function getDeliveryCountry() {
                return $this->deliveryCountry;
        }


        public function setDeliveryState($state) {
                $this->deliveryState = $state;
        }

        public function getDeliveryState() {
                return $this->deliveryState;
        }

        public function setDeliveryPhone($phone) {
                $this->deliveryPhone = $phone;
        }

        public function getDeliveryPhone() {
                return $this->deliveryPhone;
        }

        public function setBasket($basket) {
                $this->basket = $basket;
        }

        public function getBasket() {
                return $this->basket;
        }

        public function setAllowGiftAid($allowGiftAid = 0) {
                $this->allowGiftAid = $allowGiftAid;

        }

        public function getAllowGiftAid() {
                return $this->allowGiftAid;
        }

        public function setApplyAVSCV2($avsCV2 = 0) {
                $this->applyAVSCV2 = $avsCV2;
        }

        public function getApplyAVSCV2() {
                return $this->applyAVSCV2;
        }

        public function setApply3DSecure($apply3DSecure = 0) {
                $this->apply3DSecure = $apply3DSecure;
        }

        public function getApply3DSecure() {
                return $this->apply3DSecure;
        }


        public function setBillingAgreement ($billingAgreement = 0) {
                $this->billingAgreement = $billingAgreement;
        }

        public function getBillingAgreement() {
                return $this->billingAgreement;
        }


        public function setBasketXML ($basketXML) {
                $this->basketXML = $basketXML;
        }

        public function getBasketXML() {
                return $this->basketXML;
        }

        public function setCustomerXML ($customerXML) {
                $this->customerXML = $customerXML;
        }

        public function getCustomerXML() {
                return $this->customerXML;
        }

        public function setSurchargeXML ($surchargeXML) {
                $this->surchargeXML = $surchargeXML;
        }

        public function getSurchargeXML() {
                return $this->surchargeXML;
        }

        public function setVendorData ($vendorData) {
                $this->vendorData = $vendorData;
        }

        public function getVendorData() {
                return $this->vendorData;
        }

        public function setReferrerID ($referrerID) {
                $this->referrerID = $referrerID;
        }

        public function getReferrerID() {
                return $this->referrerID;
        }


        public function setLanguage ($language) {
                $this->language = $language;
        }

        public function getLanguage() {
                return $this->language;
        }


        public function setWebsite ($website) {
                $this->website = $website;
        }

        public function getWebsite() {
                return $this->website;
        }


        public function setDeliverySameAsBilling() {
                $this->setDeliverySurname($this->getBillingSurname());
                $this->setDeliveryFirstnames($this->getBillingFirstnames());
                $this->setDeliveryAddress1($this->getBillingAddress1());
                $this->setDeliveryAddress2($this->getBillingAddress2());
                $this->setDeliveryCity($this->getBillingCity());
                $this->setDeliveryPostCode($this->getBillingPostCode());
                $this->setDeliveryCountry($this->getBillingCountry());
                $this->setDeliveryState($this->getBillingState());
                $this->setDeliveryPhone($this->getBillingPhone());
        }


        public function decode($strIn) {
                $decodedString =  $this->decodeAndDecrypt($strIn);
                parse_str($decodedString, $sagePayResponse);
                return $sagePayResponse;
        }

        protected function encryptAndEncode($strIn) {

            return "@".strtoupper(bin2hex(openssl_encrypt($this->pkcs5_pad($strIn, 16), 'aes-128-cbc', $this->encryptPassword, OPENSSL_RAW_DATA, $this->encryptPassword)));

            //$strIn = $this->pkcs5_pad($strIn, 16);
            //return $this->encrypt ($strIn,$this->encryptPassword,'AES-256-CBC');
        }

        protected function decodeAndDecrypt($strIn) {
                $strIn = substr($strIn, 1);
                $strIn = pack('H*', $strIn);
                //return @mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->encryptPassword, $strIn, MCRYPT_MODE_CBC);
                return $this->decrypt(strIn, $this->encryptPassword, 'AES-256-CBC');
        }


        function encrypt( $data,  $key,  $method)
        {
            $ivSize = openssl_cipher_iv_length($method);
            $iv = openssl_random_pseudo_bytes($ivSize);

            $encrypted = openssl_encrypt($data, $method, $key, OPENSSL_RAW_DATA, $iv);

            // For storage/transmission, we simply concatenate the IV and cipher text
            $encrypted = base64_encode($iv . $encrypted);

            return $encrypted;
        }

        function decrypt( $data, $key, $method )
        {
            $data = base64_decode($data);
            $ivSize = openssl_cipher_iv_length($method);
            $iv = substr($data, 0, $ivSize);
            $data = openssl_decrypt(substr($data, $ivSize), $method, $key, OPENSSL_RAW_DATA, $iv);

            return $data;
        }


        protected function pkcs5_pad($text, $blocksize)        {
                $pad = $blocksize - (strlen($text) % $blocksize);
                return $text . str_repeat(chr($pad), $pad);
        }
}
}

?>