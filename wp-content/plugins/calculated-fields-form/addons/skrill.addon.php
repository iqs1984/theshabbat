<?php
/*

*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CFF_Skrill' ) )
{
    class CFF_Skrill extends CPCFF_BaseAddon
    {
		static public $category = 'Payment Gateways';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-Skrill-20160910";
		protected $name = "CFF - Skrill Payments Integration";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#skrill-addon';
        protected $default_pay_label = "Pay with Skrill";

		public function get_addon_form_settings( $form_id )
		{
			global $wpdb;
			// Insertion in database
			if(
				isset( $_REQUEST[ 'cpcff_skrill_id' ] )
			)
			{
			    $wpdb->delete( $wpdb->prefix.$this->form_table, array( 'formid' => $form_id ), array( '%d' ) );
				$wpdb->insert(
								$wpdb->prefix.$this->form_table,
								array(
									'formid' => $form_id,

									'Skrill_api_username'	 => $_REQUEST["Skrill_api_username"],
									'x_receipt_url'	 => $_REQUEST["x_receipt_url"],
									'x_cancel_url'	 => $_REQUEST["x_cancel_url"],
									'currency'	 => $_REQUEST["skrill_currency"],
									'enabled'	 => $_REQUEST["Skrill_enabled"],
                                    'enable_option_yes'	 => $_REQUEST["Skrill_enable_option_yes"]

								),
								array( '%d', '%s', '%s', '%s',
								             '%s', '%s', '%s'
								              )
							);
			}


			$rows = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $form_id )
					);
			if (!count($rows))
			{
			    $row["Skrill_api_username"] = "";
			    $row["enabled"] = "0";
                $row["x_receipt_url"] = '';
                $row["x_cancel_url"] = '';
                $row["currency"] = $rows[0]->currency;
                $row["enable_option_yes"] = $this->default_pay_label;
			} else {
			    $row["enabled"] = $rows[0]->enabled;
			    $row["Skrill_api_username"] = $rows[0]->Skrill_api_username;
                $row["x_receipt_url"] = $rows[0]->x_receipt_url;
                $row["x_cancel_url"] = $rows[0]->x_cancel_url;
                $row["currency"] = $rows[0]->currency;
                $row["enable_option_yes"] = $rows[0]->enable_option_yes;
			}

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<div id="metabox_skrill_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_skrill_addon_form_settings' ) ); ?>" >
				<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
				<div class="inside">
				   <input type="hidden" name="cpcff_skrill_id" value="1" />
                   <table class="form-table">
                    <tr valign="top">
                    <th scope="row"><?php _e('Enable Skrill Payments?', 'calculated-fields-form'); ?></th>
                    <td><select name="Skrill_enabled">
                         <option value="0" <?php if (!$row["enabled"]) echo 'selected'; ?>><?php _e('No', 'calculated-fields-form'); ?></option>
                         <option value="1" <?php if ($row["enabled"] == '1') echo 'selected'; ?>><?php _e('Yes', 'calculated-fields-form'); ?></option>
                         <option value="2" <?php if ($row["enabled"] == '2') echo 'selected'; ?>><?php _e('Optional: This payment method + Pay Later (submit without payment)', 'calculated-fields-form'); ?></option>
                         <option value="3" <?php if ($row["enabled"] == '3') echo 'selected'; ?>><?php _e('Optional: This payment method + Other payment methods (enabled)', 'calculated-fields-form'); ?></option>
                         <option value="4" <?php if ($row["enabled"] == '4') echo 'selected'; ?>><?php _e('Optional: This payment method + Other payment methods  + Pay Later ', 'calculated-fields-form'); ?></option>
                         </select>
                         <div style="margin-top:10px;background:#EEF5FB;border: 1px dotted #888888;padding:10px;width:260px;">
                           <?php _e( 'Label for this payment option', 'calculated-fields-form' ); ?>:<br />
                           <input type="text" name="Skrill_enable_option_yes" size="40" style="width:250px;" value="<?php echo esc_attr($row['enable_option_yes']); ?>" />
                        </div>
                    </td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Skrill Email', 'calculated-fields-form'); ?></th>
                    <td><input type="text" name="Skrill_api_username" size="40" value="<?php echo esc_attr($row["Skrill_api_username"]); ?>" /><br />
                    </tr>

                    <tr valign="top">
                    <th scope="row"><?php _e('Receipt URL:', 'calculated-fields-form'); ?></th>
                    <td><input type="text" name="x_receipt_url" size="70" value="<?php echo esc_attr(@$row["x_receipt_url"]); ?>" /><br />
                        <em>User will return here after payment.</em>
                        </td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Cancel URL:', 'calculated-fields-form'); ?></th>
                    <td><input type="text" name="x_cancel_url" size="70" value="<?php echo esc_attr(@$row["x_cancel_url"]); ?>" /><br />
                        <em>User will return here if payment fails.</em>
                        </td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Currency:', 'calculated-fields-form'); ?></th>
                    <td><input type="text" name="skrill_currency" size="70" value="<?php echo esc_attr(@$row["currency"]); ?>" /><br />
                        <em>Currency code. example: USD, EUR, CAD, GBP ...</em>
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

        private $form_table = 'cff_form_skrill';
        private $_inserted = false;
		private $_cpcff_main;

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->_cpcff_main = CPCFF_MAIN::instance();
			$this->description = __("The add-on adds support for Skrill payments", 'calculated-fields-form' );
            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;

            add_action( 'cpcff_process_data_before_insert', array( &$this, 'pp_before_insert' ), 10, 3 );

			add_action( 'cpcff_process_data', array( &$this, 'pp_Skrill' ), 11, 1 );

			add_action( 'init', array( &$this, 'pp_Skrill_update_status' ), 10, 0 );

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
					Skrill_api_username varchar(255) DEFAULT '' NOT NULL ,
					x_receipt_url varchar(255) DEFAULT '' NOT NULL ,
					x_cancel_url varchar(255) DEFAULT '' NOT NULL ,
					currency varchar(255) DEFAULT '' NOT NULL ,
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
		public function pp_Skrill($params)
		{
            global $wpdb;

            CP_SESSION::register_event($params[ 'itemnumber' ], $params[ 'formid' ]);

            $rows = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $params["formid"] )
					);
			$form_obj = $this->_cpcff_main->get_form($params['formid']);

			if($form_obj->get_option('paypal_notiemails', '0') == '1')
			    $this->_cpcff_main->send_mails($params['itemnumber']);

            $pro_item_name = $form_obj->get_option('paypal_product_name', CP_CALCULATEDFIELDSF_DEFAULT_PRODUCT_NAME);
            foreach ($params as $item => $value)
                $pro_item_name = str_replace('<%'.$item.'%>',(is_array($value)?(implode(", ",$value)):($value)),$pro_item_name);

			$payment_option = (isset($_POST["bccf_payment_option_paypal"])?$_POST["bccf_payment_option_paypal"]:$this->addonID);
			if (empty( $rows ) || !$rows[0]->enabled || $payment_option != $this->addonID || floatval($params["final_price"]) == 0)
			    return;
            $sequence = $params["itemnumber"];
            $timestamp = time();

?>
        <html>
        <head><title>Redirecting to Skrill...</title></head>
        <body>
<form name="ppform3" action="https://pay.skrill.com" method="post">
 <input type="hidden" name="pay_to_email" value="<?php echo $rows[0]->Skrill_api_username; ?>">
 <input type="hidden" name="recipient_description" value="<?php echo $pro_item_name; ?>">
 <input type="hidden" name="return_url" value="<?php echo $rows[0]->x_receipt_url; ?>">
 <input type="hidden" name="cancel_url" value="<?php echo $rows[0]->x_cancel_url; ?>">
 <input type="hidden" name="status_url" value="<?php echo CPCFF_AUXILIARY::site_url().'/?cp_cffskrill_ipncheck=1&itemnumber='.$params[ 'itemnumber' ].'&d='.$params["formid"] ?>">
 <input type="hidden" name="language" value="EN">
 <input type="hidden" name="transaction_id" value="CFF<?php echo substr(md5($_SERVER["HTTP_HOST"]),0,4).mt_rand(1000,9999)."-".$params["itemnumber"]; ?>">
 <input type="hidden" name="merchant_fields" value="item_id,item_name">
 <input type="hidden" name="item_id" value="<?php echo $params["itemnumber"]; ?>" />
 <input type="hidden" name="item_name" value="<?php echo $form_obj->get_option('paypal_product_name', CP_CALCULATEDFIELDSF_DEFAULT_PRODUCT_NAME); ?>">
 <input type="hidden" name="amount" value="<?php echo $params["final_price"]; ?>">
 <input type="hidden" name="currency" value="<?php echo $rows[0]->currency; ?>">
 <input type="hidden" name="detail1_description" value="<?php echo $form_obj->get_option('paypal_product_name', CP_CALCULATEDFIELDSF_DEFAULT_PRODUCT_NAME); ?>">
 <input type="hidden" name="detail1_text" value="">
 <!--<input type="hidden" name="payment_methods" value="<?php if (@$_GET["c"] == '1') echo "ACC,GLU"; else echo "GLU,ACC"; ?>">-->
</form>
<script type="text/javascript">
document.ppform3.submit();
</script>
        </body>
        </html>
<?php



            exit;
		} // end pp_Skrill


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

		public function pp_Skrill_update_status( )
		{
            if (
				!isset($_GET["cp_cffskrill_ipncheck"]) ||
				$_GET["cp_cffskrill_ipncheck"] != '1' ||
				!isset( $_GET["itemnumber"] )
			) return;

			$itemnumber = intval(@$_GET["itemnumber"]);
			$submission = CPCFF_SUBMISSIONS::get($itemnumber);
			if(empty($submission)) return;

            if ($_POST['status'] != "2")
            {
                echo 'Transtaction failed.';
                exit;
            }

            if($submission->paid == 0)
            {
				CPCFF_SUBMISSIONS::update($itemnumber,array('paid'=>1));

				$submission->paypal_post['itemnumber'] = $itemnumber;
                do_action( 'cpcff_payment_processed', $submission->paypal_post );

                $form_obj = CPCFF_SUBMISSIONS::get_form($itemnumber);
				if ($form_obj->get_option('paypal_notiemails', '0') != '1')
					$this->_cpcff_main->send_mails($itemnumber);
            }

            echo 'OK';
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
    $cff_Skrill_obj = new CFF_Skrill();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cff_Skrill_obj);
}

