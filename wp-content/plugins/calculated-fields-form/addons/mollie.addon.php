<?php
/*
Documentation: https://goo.gl/w3kKoH
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPCFF_iDealMollie' ) )
{
    class CPCFF_iDealMollie extends CPCFF_BaseAddon
    {
		static public $category = 'Payment Gateways';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-idealmollie-20151212";
		protected $name = "CFF - iDeal Mollie";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#mollie-addon';
        protected $default_pay_label = "Pay with iDeal/Mollie";

		public function get_addon_form_settings( $form_id )
		{
			global $wpdb;
			$table = $wpdb->prefix.$this->form_table;

			// Insertion in database
			if(
				isset( $_REQUEST[ 'CPCFF_iDealMollie_id' ] )
			)
			{
                $this->add_field_verify($table, "return_error_pending");
			    $wpdb->delete($table, array( 'formid' => $form_id ), array( '%d' ) );
				$wpdb->insert(
								$table,
								array(
									'formid' => $form_id,
									'idealmollie_api_username'	 => $_REQUEST["idealmollie_api_username"],
									'return_error'	 => $_REQUEST["return_error"],
                                    'return_error_pending'	 => $_REQUEST["return_error_pending"],
									'enabled'	 => $_REQUEST["mollie_enabled"],
                                    'enable_option_yes'	 => $_REQUEST["mollie_enable_option_yes"]
								),
								array( '%d', '%s', '%s', '%s','%s', '%s')
							);
			}


			$rows = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM ".$table." WHERE formid=%d", $form_id )
					);
			if (!count($rows))
			{
			    $row["idealmollie_api_username"] = "";
			    $row["return_error"] = "";
                $row["return_error_pending"] = "";
			    $row["enabled"] = "0";
                $row["enable_option_yes"] = $this->default_pay_label;
			} else {
			    $row["idealmollie_api_username"] = $rows[0]->idealmollie_api_username;
			    $row["return_error"] = $rows[0]->return_error;
                $row["return_error_pending"] = $rows[0]->return_error_pending;
			    $row["enabled"] = $rows[0]->enabled;
                $row["enable_option_yes"] = $rows[0]->enable_option_yes;
			}

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<div id="metabox_mollie_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_mollie_addon_form_settings' ) ); ?>" >
				<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
				<div class="inside">
				   <input type="hidden" name="CPCFF_iDealMollie_id" value="1" />
                   <table class="form-table">
                    <tr valign="top">
                    <th scope="row"><?php _e('Enable iDeal-Mollie? (if enabled PayPal Standard is disabled)', 'calculated-fields-form'); ?></th>
                    <td><select name="mollie_enabled">
                         <option value="0" <?php if (!$row["enabled"]) echo 'selected'; ?>><?php _e('No', 'calculated-fields-form'); ?></option>
                         <option value="1" <?php if ($row["enabled"] == '1') echo 'selected'; ?>><?php _e('Yes', 'calculated-fields-form'); ?></option>
                         <option value="2" <?php if ($row["enabled"] == '2') echo 'selected'; ?>><?php _e('Optional: This payment method + Pay Later (submit without payment)', 'calculated-fields-form'); ?></option>
                         <option value="3" <?php if ($row["enabled"] == '3') echo 'selected'; ?>><?php _e('Optional: This payment method + Other payment methods (enabled)', 'calculated-fields-form'); ?></option>
                         <option value="4" <?php if ($row["enabled"] == '4') echo 'selected'; ?>><?php _e('Optional: This payment method + Other payment methods  + Pay Later ', 'calculated-fields-form'); ?></option>
                         </select>
                         <div style="margin-top:10px;background:#EEF5FB;border: 1px dotted #888888;padding:10px;width:260px;">
                           <?php _e( 'Label for this payment option', 'calculated-fields-form' ); ?>:<br />
                           <input type="text" name="mollie_enable_option_yes" size="40" style="width:250px;" value="<?php echo esc_attr($row['enable_option_yes']); ?>" />
                        </div>
                    </td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Mollie API Key', 'calculated-fields-form'); ?></th>
                    <td><input type="text" name="idealmollie_api_username" size="20" value="<?php echo esc_attr($row["idealmollie_api_username"]); ?>" /></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('If payment fails return to this page', 'calculated-fields-form'); ?></th>
                    <td><input type="text" name="return_error" size="20" value="<?php echo esc_attr($row["return_error"]); ?>" /></td>
                    </tr>

                    <tr valign="top">
                    <th scope="row"><?php _e('If payment is still pending return to this page', 'calculated-fields-form'); ?></th>
                    <td><input type="text" name="return_error_pending" size="20" value="<?php echo esc_attr($row["return_error_pending"]); ?>" /></td>
                    </tr>

                   </table>
                   <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
				</div>
			</div>
			<?php
		} // end get_addon_form_settings



		/************************ ADDON CODE *****************************/

        /************************ ATTRIBUTES *****************************/

        private $form_table = 'cp_calculated_fields_form_idealmollie';
        private $_inserted = false;
		private $_cpcff_main;

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->_cpcff_main = CPCFF_MAIN::instance();
			$this->description = __("The add-on adds support for iDeal via Mollie payments", 'calculated-fields-form' );
            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;

			add_action( 'cpcff_process_data_before_insert', array( &$this, 'pp_before_insert' ), 10, 3 );

			add_action( 'cpcff_process_data', array( &$this, 'pp_idealmollie' ), 11, 1 );

			add_action( 'init', array( &$this, 'pp_idealmollie_update_status' ), 10, 0 );
			add_action( 'init', array( &$this, 'pp_idealmollie_return_page' ), 10, 0 );

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
					idealmollie_api_username varchar(255) DEFAULT '' NOT NULL ,
					return_error varchar(255) DEFAULT '' NOT NULL ,
                    return_error_pending varchar(255) DEFAULT '' NOT NULL ,
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
		public function pp_idealmollie($params)
		{
            global $wpdb;

			CP_SESSION::register_event($params[ 'itemnumber' ], $params[ 'formid' ]);

			// documentation: https://goo.gl/w3kKoH

            $rows = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $params["formid"] )
					);
		    $payment_option = (isset($_POST["bccf_payment_option_paypal"])?$_POST["bccf_payment_option_paypal"]:$this->addonID);
			if (empty( $rows ) || !$rows[0]->enabled || $payment_option != $this->addonID || floatval($params["final_price"]) == 0)
			    return;

            $pro_item_name = $this->_cpcff_main->get_form($params['formid'])->get_option('paypal_product_name', CP_CALCULATEDFIELDSF_DEFAULT_PRODUCT_NAME);
            foreach ($params as $item => $value)
                $pro_item_name = str_replace('<%'.$item.'%>',(is_array($value)?(implode(", ",$value)):($value)),$pro_item_name);

			$form_obj = CPCFF_SUBMISSIONS::get_form($params["itemnumber"]);
			if($form_obj->get_option('paypal_notiemails', '0') == '1')
			    $this->_cpcff_main->send_mails($params['itemnumber']);

            $key = $rows[0]->idealmollie_api_username;
            try
            {
                require_once dirname(__FILE__) . "/mollie.addon/src/Mollie/API/Autoloader.php";
                $mollie = new Mollie_API_Client;
                $mollie->setApiKey( $key );
                $order_id = $params["itemnumber"];
                $payment = $mollie->payments->create(array(
		            "amount"       => $params["final_price"],
		            "description"  => $pro_item_name,
		            "webhookUrl"   => CPCFF_AUXILIARY::site_url().'/?cp_idealmollie_ipncheck=1&itemnumber='.$params[ 'itemnumber' ].'&d='.$params["formid"],
		            "redirectUrl"  => CPCFF_AUXILIARY::site_url().'/?cp_idealmollie_ipnreturn=1&itemnumber='.$params[ 'itemnumber' ].'&d='.$params["formid"]
		            ,
		            "metadata"     => array(
		        	    "order_id" => $order_id,
		            ),
	            ));
                header("Location: " . $payment->getPaymentUrl());
            } catch (Exception $e) {
                echo "Error: ".$e->getMessage();
            }
            exit;
		} // end pp_idealmollie


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

		public function pp_idealmollie_return_page( )
		{
            global $wpdb;
            if(
				!isset( $_GET['cp_idealmollie_ipnreturn'] ) ||
				$_GET['cp_idealmollie_ipnreturn'] != '1' ||
				!isset( $_GET["itemnumber"] )
			) return;

            $itemnumber = intval(@$_GET['itemnumber'] );
			$submission = CPCFF_SUBMISSIONS::get($itemnumber);
			if(empty($submission)) return;


			if($submission->paid == 0)
			{
                $rowsmollie = $wpdb->get_row(
					$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", intval(@$_GET["d"]) )
				);
				if(empty($rowsmollie)) return;
                // check if still open
                if ($submission->paypal_post['mollie_trans_id'])
                {
                    try
			        {
                        require_once dirname(__FILE__) . "/mollie.addon/src/Mollie/API/Autoloader.php";
                        $mollie = new Mollie_API_Client;
                        $mollie->setApiKey( $rowsmollie->idealmollie_api_username );
                        $payment  = $mollie->payments->get($submission->paypal_post['mollie_trans_id']);
                        if ($payment->isPending() != TRUE)
                            header('Location: '.$rowsmollie->return_error);
                        else
	                        header('Location: '.($rowsmollie->return_error_pending?$rowsmollie->return_error_pending:$rowsmollie->return_error));
                    }
                    catch (Exception $e)
                    {
                        header('Location: '.$rowsmollie->return_error);
                    }
                }
                else
                    header('Location: '.$rowsmollie->return_error);
			}
			else
			{
				/**
				 * Action called after process the data received by PayPal.
				 * To the function is passed an array with the data collected by the form.
				 */
				$submission->paypal_post['itemnumber'] = $itemnumber;
				$form_obj = CPCFF_SUBMISSIONS::get_form($itemnumber);
                $location = CPCFF_AUXILIARY::replace_params_into_url($form_obj->get_option('fp_return_page', CP_CALCULATEDFIELDSF_DEFAULT_fp_return_page), $submission->paypal_post);
                header("Location: ".$location);
			}
            exit;
		}


		public function pp_idealmollie_update_status( )
		{
            global $wpdb;
            if(
				!isset( $_GET['cp_idealmollie_ipncheck'] ) ||
				$_GET['cp_idealmollie_ipncheck'] != '1' ||
				!isset( $_GET["itemnumber"] )
			) return;

            $itemnumber = intval(@$_GET['itemnumber'] );

            $rowsmollie = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", intval(@$_GET["d"]) )
					);

			if(empty($rowsmollie)) return;

            $submission = CPCFF_SUBMISSIONS::get($itemnumber);
			if(empty($submission)) return;

            $form_obj = CPCFF_SUBMISSIONS::get_form($itemnumber);

			try
			{
                require_once dirname(__FILE__) . "/mollie.addon/src/Mollie/API/Autoloader.php";
                $mollie = new Mollie_API_Client;
                $mollie->setApiKey( $rowsmollie[0]->idealmollie_api_username );
                $payment  = $mollie->payments->get($_POST["id"]);
	            $order_id = $payment->metadata->order_id;
	            if ($payment->isPaid() != TRUE)
	            {
                    $submission->paypal_post["mollie_trans_id"] = $_POST["id"];
                    CPCFF_SUBMISSIONS::update($itemnumber, array( 'paypal_post'=>  $submission->paypal_post ));
	                $location = $rowsmollie[0]->return_error;
	                header( 'Location: '.$location );
	                exit;
	            }
	        } catch (Exception $e) {
                echo "Error: ".$e->getMessage();
                exit;
            }


			if ($submission->paid == 0)
			{
				CPCFF_SUBMISSIONS::update($itemnumber, array( 'paid'=>1, 'mollie_trans_id'=>$_POST["id"] ));
                /**
				 * Action called after process the data received by PayPal.
				 * To the function is passed an array with the data collected by the form.
				 */
				$submission->paypal_post['itemnumber'] = $itemnumber;
				do_action( 'cpcff_payment_processed', $submission->paypal_post );

				if ($form_obj->get_option('paypal_notiemails', '0') != '1')
					$this->_cpcff_main->send_mails($itemnumber);
			}

			$location = CPCFF_AUXILIARY::replace_params_into_url($form_obj->get_option('fp_return_page', CP_CALCULATEDFIELDSF_DEFAULT_fp_return_page), $submission->paypal_post);
            header("Location: ".$location);
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
    $CPCFF_iDealMollie_obj = new CPCFF_iDealMollie();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($CPCFF_iDealMollie_obj);
}


?>