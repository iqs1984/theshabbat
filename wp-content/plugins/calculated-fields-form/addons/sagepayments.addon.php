<?php
/*

*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CFF_SagePayments' ) )
{
    class CFF_SagePayments extends CPCFF_BaseAddon
    {
		static public $category = 'Payment Gateways';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-SagePayments-20160706";
		protected $name = "CFF - SagePayments Payment Gateway";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#sagepayment-addon';
        protected $default_pay_label = "Pay with SagePayments";

		public function get_addon_form_settings( $form_id )
		{
			global $wpdb;
			// Insertion in database
			if(
				isset( $_REQUEST[ 'cpabc_SagePayments_id' ] )
			)
			{
			    $wpdb->delete( $wpdb->prefix.$this->form_table, array( 'formid' => $form_id ), array( '%d' ) );
				$wpdb->insert(
								$wpdb->prefix.$this->form_table,
								array(
									'formid' => $form_id,
									'SagePayments_api_username'	 => $_REQUEST["SagePayments_api_username"],
									'T_shipping'	 => $_REQUEST["T_shipping"],
                                    'T_tax'	 => $_REQUEST["T_tax"],
                                    'C_fname'	 => $_REQUEST["C_fname"],
                                    'C_lname'	 => $_REQUEST["C_lname"],
                                    'C_address'	 => $_REQUEST["C_address"],
                                    'C_city'	 => $_REQUEST["C_city"],
                                    'C_state'	 => $_REQUEST["C_state"],
                                    'C_zip'	 => $_REQUEST["C_zip"],
                                    'C_email'	 => $_REQUEST["C_email"],
                                    'C_telephone'	 => $_REQUEST["C_telephone"],
									'enabled'	 => $_REQUEST["SagePayments_enabled"],
                                    'enable_option_yes'	 => $_REQUEST["SagePayments_enable_option_yes"]

								),
								array( '%d', '%s', '%s', '%s',
								             '%s', '%s', '%s',
								             '%s', '%s', '%s',
								             '%s', '%s', '%s', '%s' )
							);
			}


			$rows = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $form_id )
					);
			if (!count($rows))
			{
			    $row["SagePayments_api_username"] = "";
			    $row["enabled"] = "0";
                $row["enable_option_yes"] = $this->default_pay_label;
			} else {
			    $row["SagePayments_api_username"] = $rows[0]->SagePayments_api_username;
			    $row["enabled"] = $rows[0]->enabled;
                $row["T_shipping"] = $rows[0]->T_shipping;
                $row["T_tax"] = $rows[0]->T_tax;
                $row["C_fname"] = $rows[0]->C_fname;
                $row["C_lname"] = $rows[0]->C_lname;
                $row["C_address"] = $rows[0]->C_address;
                $row["C_city"] = $rows[0]->C_city;
                $row["C_state"] = $rows[0]->C_state;
                $row["C_zip"] = $rows[0]->C_zip;
                $row["C_email"] = $rows[0]->C_email;
                $row["C_telephone"] = $rows[0]->C_telephone;
                $row["enable_option_yes"] = $rows[0]->enable_option_yes;
			}

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<div id="metabox_sagepayments_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_sagepayments_addon_form_settings' ) ); ?>" >
				<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
				<div class="inside">
				   <input type="hidden" name="cpabc_SagePayments_id" value="1" />
                   <table class="form-table">
                    <tr valign="top">
                    <th scope="row"><?php _e('Enable SagePayments? (if enabled PayPal Standard is disabled)', 'calculated-fields-form'); ?></th>
                    <td><select name="SagePayments_enabled">
                         <option value="0" <?php if (!$row["enabled"]) echo 'selected'; ?>><?php _e('No', 'calculated-fields-form'); ?></option>
                         <option value="1" <?php if ($row["enabled"] == '1') echo 'selected'; ?>><?php _e('Yes', 'calculated-fields-form'); ?></option>
                         <option value="2" <?php if ($row["enabled"] == '2') echo 'selected'; ?>><?php _e('Optional: This payment method + Pay Later (submit without payment)', 'calculated-fields-form'); ?></option>
                         <option value="3" <?php if ($row["enabled"] == '3') echo 'selected'; ?>><?php _e('Optional: This payment method + Other payment methods (enabled)', 'calculated-fields-form'); ?></option>
                         <option value="4" <?php if ($row["enabled"] == '4') echo 'selected'; ?>><?php _e('Optional: This payment method + Other payment methods  + Pay Later ', 'calculated-fields-form'); ?></option>
                         </select>
                         <div style="margin-top:10px;background:#EEF5FB;border: 1px dotted #888888;padding:10px;width:260px;">
                           <?php _e( 'Label for this payment option', 'calculated-fields-form' ); ?>:<br />
                           <input type="text" name="SagePayments_enable_option_yes" size="40" style="width:250px;" value="<?php echo esc_attr($row['enable_option_yes']); ?>" />
                        </div>
                    </td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Merchant ID (M_id)', 'calculated-fields-form'); ?></th>
                    <td><input type="text" name="SagePayments_api_username" size="20" value="<?php echo esc_attr($row["SagePayments_api_username"]); ?>" /><br />
                        <em>Change this value with M_id received from SagePayments<em></td>
                    </tr>

                   </table>
                   <hr />
                   Optional fields (indicate the fieldname for the related information, ex: fieldname1, fieldname2, ...)
                   <table class="form-table">
                    <tr valign="top">
                    <th scope="row"><?php _e('Shipping/Freight:', 'calculated-fields-form'); ?></th>
                    <td><input type="text" name="T_shipping" size="20" value="<?php echo esc_attr($row["T_shipping"]); ?>" /><br />
                        <em>ID of the field of the form that contains this info. Sample values: fieldname1, fieldname2, ...<em></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Tax:', 'calculated-fields-form'); ?></th>
                    <td><input type="text" name="T_tax" size="20" value="<?php echo esc_attr(@$row["T_tax"]); ?>" /></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('First Name:', 'calculated-fields-form'); ?></th>
                    <td><input type="text" name="C_fname" size="20" value="<?php echo esc_attr(@$row["C_fname"]); ?>" /></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Last Name:', 'calculated-fields-form'); ?></th>
                    <td><input type="text" name="C_lname" size="20" value="<?php echo esc_attr(@$row["C_lname"]); ?>" /></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Address:', 'calculated-fields-form'); ?></th>
                    <td><input type="text" name="C_address" size="20" value="<?php echo esc_attr(@$row["C_address"]); ?>" /></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('City:', 'calculated-fields-form'); ?></th>
                    <td><input type="text" name="C_city" size="20" value="<?php echo esc_attr(@$row["C_city"]); ?>" /></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('State:', 'calculated-fields-form'); ?></th>
                    <td><input type="text" name="C_state" size="20" value="<?php echo esc_attr(@$row["C_state"]); ?>" /></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('ZIP:', 'calculated-fields-form'); ?></th>
                    <td><input type="text" name="C_zip" size="20" value="<?php echo esc_attr(@$row["C_zip"]); ?>" /></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Email:', 'calculated-fields-form'); ?></th>
                    <td><input type="text" name="C_email" size="20" value="<?php echo esc_attr(@$row["C_email"]); ?>" /></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Telephone:', 'calculated-fields-form'); ?></th>
                    <td><input type="text" name="C_telephone" size="20" value="<?php echo esc_attr(@$row["C_telephone"]); ?>" /></td>
                    </tr>
                   </table>
                   <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
				</div>
			</div>
			<?php
		} // end get_addon_form_settings



		/************************ ADDON CODE *****************************/

        /************************ ATTRIBUTES *****************************/

        private $form_table = 'cff_form_SagePayments';
        private $_inserted = false;
		private $_cpcff_main;

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->_cpcff_main = CPCFF_MAIN::instance();
			$this->description = __("The add-on adds support for SagePayments payments", 'calculated-fields-form' );
            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;

			add_action( 'cpcff_process_data_before_insert', array( &$this, 'pp_before_insert' ), 10, 3 );

			add_action( 'cpcff_process_data', array( &$this, 'pp_SagePayments' ), 11, 1 );

			add_action( 'init', array( &$this, 'pp_SagePayments_update_status' ), 10, 0 );

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
					SagePayments_api_username varchar(255) DEFAULT '' NOT NULL ,
					T_shipping varchar(255) DEFAULT '' NOT NULL ,
                    T_tax varchar(255) DEFAULT '' NOT NULL ,
                    C_fname varchar(255) DEFAULT '' NOT NULL ,
                    C_lname varchar(255) DEFAULT '' NOT NULL ,
                    C_address varchar(255) DEFAULT '' NOT NULL ,
                    C_city varchar(255) DEFAULT '' NOT NULL ,
                    C_state varchar(255) DEFAULT '' NOT NULL ,
                    C_zip varchar(255) DEFAULT '' NOT NULL ,
                    C_email varchar(255) DEFAULT '' NOT NULL ,
                    C_telephone varchar(255) DEFAULT '' NOT NULL ,
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
		public function pp_SagePayments($params)
		{
            global $wpdb;

            $rows = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $params["formid"] )
					);

			$payment_option = (isset($_POST["bccf_payment_option_paypal"])?$_POST["bccf_payment_option_paypal"]:$this->addonID);
			if (empty( $rows ) || !$rows[0]->enabled || $payment_option != $this->addonID || floatval($params["final_price"]) == 0)
			    return;

			$form_obj = CPCFF_SUBMISSIONS::get_form($params["itemnumber"]);
			if($form_obj->get_option('paypal_notiemails', '0') == '1')
			    $this->_cpcff_main->send_mails($params['itemnumber']);

            $pro_item_name = $this->_cpcff_main->get_form($params['formid'])->get_option('paypal_product_name', CP_CALCULATEDFIELDSF_DEFAULT_PRODUCT_NAME);
            foreach ($params as $item => $value)
                $pro_item_name = str_replace('<%'.$item.'%>',(is_array($value)?(implode(", ",$value)):($value)),$pro_item_name);

            $urlok = CPCFF_AUXILIARY::site_url().'/?cff_SagePayments_ipncheck=1&itemnumber='.$params["itemnumber"];
            $urlerror = $_POST["cp_ref_page"] ;

            $ppurl = 'https://www.sagepayments.net/eftcart/forms/order.asp';

?>
        <html>
        <head><title>Redirecting to SagePayments...</title></head>
        <body>
        <form method="POST" name="SagePaymentsForm" id="SagePaymentsForm" action="<?php echo $ppurl; ?>">
         <input type="hidden" name="M_id" value="<?php echo $rows[0]->SagePayments_api_username; ?>">
         <input type="hidden" name="P_count" value="1">
         <input type="hidden" name="P_part1" value="<?php echo "REQUEST".$params["itemnumber"]; ?>">
         <input type="hidden" name="P_desc1" value="<?php echo $pro_item_name; ?>">
         <input type="hidden" name="P_qty1" value="1">
         <input type="hidden" name="P_price1" value="<?php echo $params["final_price"]; ?>">
         <input type="hidden" name="Approved_url" value="<?php echo $urlok; ?>">
         <input type="hidden" name="Declined_url" value="<?php echo $urlerror; ?>">
         <input type="hidden" name="T_ordernum" value="<?php echo $params["itemnumber"]; ?>">
         <?php if ($rows[0]->T_shipping) { ?><input type="hidden" name="T_shipping" value="<?php echo $params[$rows[0]->T_shipping]; ?>"><?php } ?>
         <?php if ($rows[0]->T_tax) { ?><input type="hidden" name="T_tax" value="<?php echo $params[$rows[0]->T_tax]; ?>"><?php } ?>
         <?php if ($rows[0]->C_fname) { ?><input type="hidden" name="C_fname" value="<?php echo $params[$rows[0]->C_fname]; ?>"><?php } ?>
         <?php if ($rows[0]->C_lname) { ?><input type="hidden" name="C_lname" value="<?php echo $params[$rows[0]->C_lname]; ?>"><?php } ?>
         <?php if ($rows[0]->C_address) { ?><input type="hidden" name="C_address" value="<?php echo $params[$rows[0]->C_address]; ?>"><?php } ?>
         <?php if ($rows[0]->C_city) { ?><input type="hidden" name="C_city" value="<?php echo $params[$rows[0]->C_city]; ?>"><?php } ?>
         <?php if ($rows[0]->C_state) { ?><input type="hidden" name="C_state" value="<?php echo $params[$rows[0]->C_state]; ?>"><?php } ?>
         <?php if ($rows[0]->C_zip) { ?><input type="hidden" name="C_zip" value="<?php echo $params[$rows[0]->C_zip]; ?>"><?php } ?>
         <?php if ($rows[0]->C_email) { ?><input type="hidden" name="C_email" value="<?php echo $params[$rows[0]->C_email]; ?>"><?php } ?>
         <?php if ($rows[0]->C_telephone) { ?><input type="hidden" name="C_telephone" value="<?php echo $params[$rows[0]->C_telephone]; ?>"><?php } ?>
        </form>
        <script type="text/javascript">document.SagePaymentsForm.submit();</script>
        </body>
        </html>
<?php



            exit;
		} // end pp_SagePayments


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

		public function pp_SagePayments_update_status( )
		{
            if (
				!isset( $_GET['cff_SagePayments_ipncheck'] ) ||
				$_GET['cff_SagePayments_ipncheck'] != '1' ||
				!isset( $_GET["itemnumber"] )
			) return;

			$itemnumber = intval(@$_GET["itemnumber"]);
			$submission = CPCFF_SUBMISSIONS::get($itemnumber);
            if(empty($submission)) return;

			$form_obj = CPCFF_SUBMISSIONS::get_form($itemnumber);
            if ($submission->paid == 0)
            {
                CPCFF_SUBMISSIONS::update($itemnumber,array('paid'=>1));

				$submission->paypal_post['itemnumber'] = $itemnumber;
                do_action( 'cpcff_payment_processed', $submission->paypal_post );

                if ($form_obj->get_option('paypal_notiemails', '0') != '1')
					$this->_cpcff_main->send_mails($itemnumber);
            }

            header("Location: ".CPCFF_AUXILIARY::replace_params_into_url($form_obj->get_option('fp_return_page', CP_CALCULATEDFIELDSF_DEFAULT_fp_return_page), $submission->paypal_post));
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
    $cff_SagePayments_obj = new CFF_SagePayments();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cff_SagePayments_obj);
}

