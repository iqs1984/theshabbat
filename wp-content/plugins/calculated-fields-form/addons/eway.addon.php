<?php
/*
Documentation: https://go.eway.io/s/article/PHP-SDK
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPCFF_EWAY' ) )
{
    class CPCFF_EWAY extends CPCFF_BaseAddon
    {
		static public $category = 'Payment Gateways';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-eway-20220107";
		protected $name = "CFF - eWay";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#eway-addon';
        protected $default_pay_label = "Pay with Credit Cards";

		public function get_addon_form_settings( $form_id )
		{
			global $wpdb;
			$table = $wpdb->prefix.$this->form_table;

			// Insertion in database
			if(
				isset( $_REQUEST[ 'CPCFF_eway_id' ] )
			)
			{
			    $settings = isset($_REQUEST['cff_eway']) ? $_REQUEST['cff_eway'] : [];

                $wpdb->delete( $table, array( 'formid' => $form_id ), array( '%d' ) );
				$wpdb->insert(
                    $table,
                    array(
                        'formid'    => $form_id,
                        'enabled'	=> $_REQUEST["eway_enabled"]*1,
                        'settings'  => serialize($settings)
                    ),
                    array( '%d', '%d', '%s')
                );
			}

            // Initialization
            $enabled = 0;
            $settigns = [];

			$row = $this->_get_entry($form_id);

            if(!empty($row))
			{
                $enabled  = $row->enabled;
                if(($settings = unserialize($row->settings)) == false) $settings = [];
			}

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<div id="metabox_eway_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_eway_addon_form_settings' ) ); ?>" >
				<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?> (Responsive Shared Page)</span></h3>
				<div class="inside">
                    <input type="hidden" name="CPCFF_eway_id" value="1" />
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><?php _e('Enable eWay?', 'calculated-fields-form'); ?></th>
                            <td>
                                <select name="eway_enabled" class="width75">
                                    <option value="0" <?php if($enabled == 0) echo 'selected'; ?>><?php _e('No', 'calculated-fields-form'); ?></option>
                                    <option value="1" <?php if($enabled == 1) echo 'selected'; ?>><?php _e('Yes', 'calculated-fields-form'); ?></option>
                                    <option value="2" <?php if($enabled == 2) echo 'selected'; ?>><?php _e('Optional: This payment method + Pay Later (submit without payment)', 'calculated-fields-form'); ?></option>
                                    <option value="3" <?php if($enabled == 3) echo 'selected'; ?>><?php _e('Optional: This payment method + Other payment methods (enabled)', 'calculated-fields-form'); ?></option>
                                    <option value="4" <?php if($enabled == 4) echo 'selected'; ?>><?php _e('Optional: This payment method + Other payment methods  + Pay Later ', 'calculated-fields-form'); ?></option>
                                </select>
                                <div style="margin-top:10px;background:#EEF5FB;border: 1px dotted #888888;padding:10px;width:260px;">
                                   <?php _e( 'Label for this payment option', 'calculated-fields-form' ); ?>:<br />
                                   <input type="text" name="cff_eway[label]" size="40" style="width:250px;" value="<?php echo esc_attr(!empty($settings['label']) ? $settings['label'] : $this->default_pay_label); ?>" />
                                </div>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php _e('Payment mode?', 'calculated-fields-form'); ?></th>
                            <td>
                                <select name="cff_eway[mode]">
                                    <option value="0" <?php if(empty($settings['mode'])) echo 'selected'; ?>><?php _e('Sandbox Mode', 'calculated-fields-form'); ?></option>
                                    <option value="1" <?php if(!empty($settings['mode'])) echo 'selected'; ?>><?php _e('Live/Production Mode', 'calculated-fields-form'); ?></option>
                                </select>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php _e('Integration type', 'calculated-fields-form'); ?></th>
                            <td>
                                <select name="cff_eway[type]">
                                    <option value="shared" <?php if(empty($settings['type']) || $settings['type'] == 'shared') echo 'selected'; ?>><?php _e('Shared Page', 'calculated-fields-form'); ?></option>
                                    <option value="iframe" <?php if(!empty($settings['type']) && $settings['type'] == 'iframe') echo 'selected'; ?>><?php _e('iFrame (client side)', 'calculated-fields-form'); ?></option>
                                </select>
                            </td>
                        </tr>

                        <!-- cff-eway-production -->
                        <tr valign="top" class="cff-eway-production">
                            <th scope="row"><?php _e('eWay Customer API Key', 'calculated-fields-form'); ?></th>
                            <td>
                                <input type="text" name="cff_eway[api_key]" value="<?php if(!empty($settings['api_key'])) echo esc_attr($settings["api_key"]); ?>" class="width75" />
                            </td>
                        </tr>

                        <tr valign="top" class="cff-eway-production">
                            <th scope="row"><?php _e('eWay Customer Password', 'calculated-fields-form'); ?></th>
                            <td>
                                <input type="text" name="cff_eway[password]" value="<?php if(!empty($settings['password'])) echo esc_attr($settings["password"]); ?>" class="width75" />
                            </td>
                        </tr>

                        <!-- cff-eway-sandbox -->
                        <tr valign="top" class="cff-eway-sandbox">
                            <th scope="row"><?php _e('eWay Customer API Key (Sandbox)', 'calculated-fields-form'); ?></th>
                            <td>
                                <input type="text" name="cff_eway[api_key_sandbox]" value="<?php if(!empty($settings['api_key_sandbox'])) echo esc_attr($settings["api_key_sandbox"]); ?>" class="width75" />
                            </td>
                        </tr>

                        <tr valign="top" class="cff-eway-sandbox">
                            <th scope="row"><?php _e('eWay Customer Password (Sandbox)', 'calculated-fields-form'); ?></th>
                            <td>
                                <input type="text" name="cff_eway[password_sandbox]" value="<?php if(!empty($settings['password_sandbox'])) echo esc_attr($settings["password_sandbox"]); ?>" class="width75" />
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php _e('eway Theme', 'calculated-fields-form'); ?></th>
                            <td>
                                <select name="cff_eway[theme]">
                                    <?php
                                        foreach($this->_eway_themes as $theme)
                                        {
                                            print '<option value="'.esc_attr($theme).'" '.(isset($settings['theme']) && $settings['theme'] == $theme ? 'SELECTED' : '').'>'.esc_html($theme).'</option>';
                                        }
                                    ?>
                                </select>
                            </td>
                        </tr>

                        <!-- Payment Details -->
                        <tr valign="top">
                            <th colspan="2" style="border-top:1px solid #DDD;">
                                <h3 style="margin:0;padding:0;"><?php _e('Payment Details (Optional)', 'calculated-fields-form'); ?></h3>
                            </th>
                        </tr>

                        <tr valign="top">
                            <td colspan="2" style="padding:0;">
                                <?php _e('The payment description is taken from the "Product Name" attribute in the "Payment Settings" section.', 'calculated-fields-form'); ?>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php _e('Invoice Number', 'calculated-fields-form'); ?></th>
                            <td>
                                <input type="text" name="cff_eway[payment][InvoiceNumber]" value="<?php if(!empty($settings['payment']) && !empty($settings['payment']['InvoiceNumber'])) print esc_attr($settings['payment']['InvoiceNumber']); ?>" placeholder="fieldname#" class="width75" />
                            </td>
                        </tr>

                        <!-- Customer Details -->
                        <tr valign="top">
                            <th colspan="2" style="border-top:1px solid #DDD;"><h3 style="margin:0;padding:0;"><?php _e('Customer Details (Optional)', 'calculated-fields-form'); ?></h3></th>
                        </tr>

                        <tr valign="top">
                            <td colspan="2" style="padding:0;"><input type="checkbox" name="cff_eway[CustomerReadOnly]" <?php if(!empty($settings['CustomerReadOnly'])) print 'CHECKED'; ?> /><?php _e('Disable Customer Information Fields on the eWay "Responsive Shared Page" <b>(CustomerReadOnly)</b>', 'calculated-fields-form'); ?></td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php _e('Title', 'calculated-fields-form'); ?></th>
                            <td>
                                <input type="text" name="cff_eway[customer][Title]" value="<?php if(!empty($settings['customer']) && !empty($settings['customer']['Title'])) print esc_attr($settings['customer']['Title']); ?>" placeholder="fieldname#" class="width75" />
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php _e('First Name', 'calculated-fields-form'); ?></th>
                            <td>
                                <input type="text" name="cff_eway[customer][FirstName]" value="<?php if(!empty($settings['customer']) && !empty($settings['customer']['FirstName'])) print esc_attr($settings['customer']['FirstName']); ?>" placeholder="fieldname#" class="width75" />
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php _e('Last Name', 'calculated-fields-form'); ?></th>
                            <td>
                                <input type="text" name="cff_eway[customer][LastName]" value="<?php if(!empty($settings['customer']) && !empty($settings['customer']['LastName'])) print esc_attr($settings['customer']['LastName']); ?>" placeholder="fieldname#" class="width75" />
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php _e('Company Name', 'calculated-fields-form'); ?></th>
                            <td>
                                <input type="text" name="cff_eway[customer][CompanyName]" value="<?php if(!empty($settings['customer']) && !empty($settings['customer']['CompanyName'])) print esc_attr($settings['customer']['CompanyName']); ?>" placeholder="fieldname#" class="width75" />
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php _e('Job Description', 'calculated-fields-form'); ?></th>
                            <td>
                                <input type="text" name="cff_eway[customer][JobDescription]" value="<?php if(!empty($settings['customer']) && !empty($settings['customer']['JobDescription'])) print esc_attr($settings['customer']['JobDescription']); ?>" placeholder="fieldname#" class="width75" />
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php _e('Street 1', 'calculated-fields-form'); ?></th>
                            <td>
                                <input type="text" name="cff_eway[customer][Street1]" value="<?php if(!empty($settings['customer']) && !empty($settings['customer']['Street1'])) print esc_attr($settings['customer']['Street1']); ?>" placeholder="fieldname#" class="width75" />
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php _e('Street 2', 'calculated-fields-form'); ?></th>
                            <td>
                                <input type="text" name="cff_eway[customer][Street2]" value="<?php if(!empty($settings['customer']) && !empty($settings['customer']['Street2'])) print esc_attr($settings['customer']['Street2']); ?>" placeholder="fieldname#" class="width75" />
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php _e('City', 'calculated-fields-form'); ?></th>
                            <td>
                                <input type="text" name="cff_eway[customer][City]" value="<?php if(!empty($settings['customer']) && !empty($settings['customer']['City'])) print esc_attr($settings['customer']['City']); ?>" placeholder="fieldname#" class="width75" />
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php _e('State', 'calculated-fields-form'); ?></th>
                            <td>
                                <input type="text" name="cff_eway[customer][State]" value="<?php if(!empty($settings['customer']) && !empty($settings['customer']['State'])) print esc_attr($settings['customer']['State']); ?>" placeholder="fieldname#" class="width75" />
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php _e('Postal Code', 'calculated-fields-form'); ?></th>
                            <td>
                                <input type="text" name="cff_eway[customer][PostalCode]" value="<?php if(!empty($settings['customer']) && !empty($settings['customer']['PostalCode'])) print esc_attr($settings['customer']['PostalCode']); ?>" placeholder="fieldname#" class="width75" />
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php _e('Country', 'calculated-fields-form'); ?></th>
                            <td>
                                <input type="text" name="cff_eway[customer][Country]" value="<?php if(!empty($settings['customer']) && !empty($settings['customer']['Country'])) print esc_attr($settings['customer']['Country']); ?>" placeholder="fieldname#" class="width75" />
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php _e('Email', 'calculated-fields-form'); ?></th>
                            <td>
                                <input type="text" name="cff_eway[customer][Email]" value="<?php if(!empty($settings['customer']) && !empty($settings['customer']['Email'])) print esc_attr($settings['customer']['Email']); ?>" placeholder="fieldname#" class="width75" />
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php _e('Phone', 'calculated-fields-form'); ?></th>
                            <td>
                                <input type="text" name="cff_eway[customer][Phone]" value="<?php if(!empty($settings['customer']) && !empty($settings['customer']['Phone'])) print esc_attr($settings['customer']['Phone']); ?>" placeholder="fieldname#" class="width75" />
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php _e('Mobile', 'calculated-fields-form'); ?></th>
                            <td>
                                <input type="text" name="cff_eway[customer][Mobile]" value="<?php if(!empty($settings['customer']) && !empty($settings['customer']['Mobile'])) print esc_attr($settings['customer']['Mobile']); ?>" placeholder="fieldname#" class="width75" />
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php _e('Comments', 'calculated-fields-form'); ?></th>
                            <td>
                                <input type="text" name="cff_eway[customer][Comments]" value="<?php if(!empty($settings['customer']) && !empty($settings['customer']['Comments'])) print esc_attr($settings['customer']['Comments']); ?>" placeholder="fieldname#" class="width75" />
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php _e('Fax', 'calculated-fields-form'); ?></th>
                            <td>
                                <input type="text" name="cff_eway[customer][Fax]" value="<?php if(!empty($settings['customer']) && !empty($settings['customer']['Fax'])) print esc_attr($settings['customer']['Fax']); ?>" placeholder="fieldname#" class="width75" />
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php _e('Url', 'calculated-fields-form'); ?></th>
                            <td>
                                <input type="text" name="cff_eway[customer][Url]" value="<?php if(!empty($settings['customer']) && !empty($settings['customer']['Url'])) print esc_attr($settings['customer']['Url']); ?>" placeholder="fieldname#" class="width75" />
                            </td>
                        </tr>
                    </table>
                    <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
				</div>
			</div>
            <script type="text/javascript">
            jQuery(function(){
                var $ = jQuery;
                $(document).on('change', '[name="cff_eway[mode]"]', function(){
                    $('.cff-eway-sandbox, .cff-eway-production').hide();
                    if(this.value*1) $('.cff-eway-production').show();
                    else $('.cff-eway-sandbox').show();
                });
                $('[name="cff_eway[mode]"]').change();
            });
            </script>
			<?php
		} // end get_addon_form_settings

		/************************ ADDON CODE *****************************/

        /************************ ATTRIBUTES *****************************/

        private $form_table = 'cp_calculated_fields_form_eway';
		private $_cpcff_main;
        private $_eway_themes;
        private $_eway_form_entries;

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
            $this->_cpcff_main = CPCFF_MAIN::instance();
            $this->description = __('The add-on adds support for eWay payment gateway. It uses "Responsive Shared Page" integration. Only supports one-time payments, not recurring ones', 'calculated-fields-form' );
            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;

            $this->_eway_form_entries = [];

            $this->_eway_themes = ['Bootstrap', 'BootstrapAmelia', 'BootstrapCerulean', 'BootstrapCosmo', 'BootstrapCyborg', 'BootstrapFlatly', 'BootstrapJournal', 'BootstrapReadable', 'BootstrapSimplex', 'BootstrapSlate', 'BootstrapSpacelab', 'BootstrapUnited'];

			$this->init();

            add_filter( 'cpcff_the_form', array( &$this, 'insert_payment_fields'), 99, 2 );

            // Required for iframe integration type
			add_action( 'cpcff_script_after_validation', array( &$this, 'javascript_validation_code'), 1, 2 );

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
			$db_queries[] = "CREATE TABLE ".$wpdb->prefix.$this->form_table." (
					formid INT NOT NULL,
					enabled TINYINT DEFAULT 0 NOT NULL,
					settings TEXT,
					UNIQUE KEY formid (formid)
				)
				CHARACTER SET utf8
				COLLATE utf8_general_ci;";

			$this->_run_update_database($db_queries);
		} // end update_database

        // Get the eway-form row entry and reduce the calls to the database
        private function _get_entry($formid)
        {
            if(empty($this->_eway_form_entries[$formid]))
            {
                global $wpdb;
                $this->_eway_form_entries[$formid] = $wpdb->get_row(
                    $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $formid )
                );
            }
            return $this->_eway_form_entries[$formid];

        } // end _get_entry

        private function _fix_amount($amount, $currency)
        {
            if(
                in_array(
                    $currency,
                    ['BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA','PYG','RWF','VND','VUV','XAF','XOF','XPF']
                )
            ) return round($amount, 0);
            return round($amount*100, 0);
        }

        /************************ EWAY INTEGRATION  *****************************/

        public function _get_shared_payment_url($params)
        {
			$payment_option = isset($_POST["bccf_payment_option_paypal"]) ? $_POST["bccf_payment_option_paypal"] : $this->addonID;

            if($payment_option != $this->addonID) return;

            $row = $this->_get_entry($params["formid"]);

			if(
                empty($row) ||
                !$row->enabled ||
                ($settings = unserialize($row->settings)) == false
            ) return;

            $amount = $params['final_price'];
            if(!$amount) return 'Error: '.__('Empty Price', 'calculated-fields-form');


            require_once dirname(__FILE__).'/eway.addon/vendor/autoload.php';

            if($settings['mode'] == 1)
            {
                $apiKey = $settings['api_key'];
                $apiPassword = $settings['password'];
                $apiEndpoint = \Eway\Rapid\Client::MODE_PRODUCTION;
            }
            else
            {
                $apiKey = $settings['api_key_sandbox'];
                $apiPassword = $settings['password_sandbox'];
                $apiEndpoint = \Eway\Rapid\Client::MODE_SANDBOX;
            }

			$form_obj = $this->_cpcff_main->get_form($params['formid']);

            $product_item_name = $form_obj->get_option('paypal_product_name', CP_CALCULATEDFIELDSF_DEFAULT_PRODUCT_NAME);
            $currency = strtoupper($form_obj->get_option('currency', CP_CALCULATEDFIELDSF_DEFAULT_CURRENCY));

            $amount = $this->_fix_amount($amount, $currency);

            $client = \Eway\Rapid::createClient($apiKey, $apiPassword, $apiEndpoint);

            $transaction = [
                'CustomerIP' => $params[ 'ipaddress' ],
                'RedirectUrl' => CPCFF_AUXILIARY::site_url().'/?cp_calculatedfieldsf_id='.$params["formid"].
                                (isset($params['itemnumber']) ? '&itemnumber='.$params["itemnumber"] : '').
                                '&cff_eway_ipncheck=1',
                'CancelUrl' => $_POST["cp_ref_page"],
                'TransactionType' => \Eway\Rapid\Enum\TransactionType::PURCHASE,
                'Payment' => [
                    'InvoiceDescription' => $product_item_name,
                    'CurrencyCode' => $currency,
                    'TotalAmount' => $amount,
                ]
            ];

            if(!empty($settings['payment']))
            {
                if(
                    !empty($settings['payment']['InvoiceNumber']) &&
                    !empty($params[$settings['payment']['InvoiceNumber']])
                )
                {
                    $transaction['Payment']['InvoiceNumber'] = $params[$settings['payment']['InvoiceNumber']];
                }
            }

            if(!empty($settings['CustomerReadOnly'])) $transaction['CustomerReadOnly'] = true;
            if(!empty($settings['customer']))
            {
                $customer_details = [];
                foreach($settings['customer'] as $attr => $fieldname)
                {
                    if(!empty($fieldname) && !empty($params[$fieldname])) $customer_details[$attr] = $params[$fieldname];
                }
                if(!empty($customer_details)) $transaction['Customer'] = $customer_details;
            }

            if(!empty($settings['theme'])) $transaction['CustomView'] = $settings['theme'];

            // Submit data to eWAY to get a Shared Page URL
            $response = $client->createTransaction(\Eway\Rapid\Enum\ApiMethod::RESPONSIVE_SHARED, $transaction);

            // Check for any errors
            if (!$response->getErrors())
            {
                $sharedURL = $response->SharedPaymentUrl;
                return $sharedURL;
            }
            else
            {
                $err = '';
                foreach ($response->getErrors() as $error)
                {
                    $err .=  "Error: ".\Eway\Rapid::getMessage($error);
                }

                return $err;
            }
        } // End _get_shared_payment_url

        private function _ipn_check($params)
        {
            if(
                !empty($params['formid']) &&
                !empty($params['itemnumber']) &&
                !empty($params['AccessCode'])
            )
            {
                $formid = @intval($params['formid']);
                $itemnumber = @intval($params['itemnumber']);
                $access_code = sanitize_text_field($params['AccessCode']);

                $row = $this->_get_entry($formid);

                if(
                    !empty($row) &&
                    $row->enabled &&
                    ($settings = unserialize($row->settings)) !== false
                )
                {
                    $item_obj = CPCFF_SUBMISSIONS::get($itemnumber);

                    if($item_obj)
                    {
                        require_once dirname(__FILE__).'/eway.addon/vendor/autoload.php';

                        // eWAY Credentials
                        if($settings['mode'] == 1)
                        {
                            $apiKey = $settings['api_key'];
                            $apiPassword = $settings['password'];
                            $apiEndpoint = \Eway\Rapid\Client::MODE_PRODUCTION;
                        }
                        else
                        {
                            $apiKey = $settings['api_key_sandbox'];
                            $apiPassword = $settings['password_sandbox'];
                            $apiEndpoint = \Eway\Rapid\Client::MODE_SANDBOX;
                        }

                        // Create the eWAY Client
                        $client = \Eway\Rapid::createClient($apiKey, $apiPassword, $apiEndpoint);

                        // Query the transaction result.
                        $response = $client->queryTransaction($access_code);

                        $transactionResponse = $response->Transactions[0];

                        // Display the transaction result
                        if ($transactionResponse->TransactionStatus)
                        {
                            // mark item as paid
                            $form_obj = CPCFF_SUBMISSIONS::get_form($itemnumber);

                            $params = $item_obj->paypal_post;
                            $params['txn_id'] = $transactionResponse->TransactionID;
                            CPCFF_SUBMISSIONS::update($itemnumber, array('paid'=>1, 'paypal_post' => $params));

                            $params['itemnumber'] = $itemnumber;
                            do_action( 'cpcff_payment_processed', $params );

                            if($form_obj->get_option('paypal_notiemails', '0') != '1')
                                $this->_cpcff_main->send_mails($itemnumber);

                            $redirect = true;

                            /**
                             * Filters applied to decide if the website should be redirected to the thank you page after submit the form,
                             * pass a boolean as parameter and returns a boolean
                             */
                            $redirect = apply_filters('cpcff_redirect', $redirect);

                            if($redirect)
                            {
                                remove_all_actions('shutdown');
                                die('<script>document.location.href="'.esc_js(
                                    CPCFF_AUXILIARY::replace_params_into_url(
                                        $form_obj->get_option('fp_return_page', CP_CALCULATEDFIELDSF_DEFAULT_fp_return_page),
                                        $params
                                    )).'";</script>');
                            }
                        }
                        else
                        {
                            $errors = explode(', ', $transactionResponse->ResponseMessage);
                            foreach ($errors as $error)
                            {
                                echo "Payment failed: ".\Eway\Rapid::getMessage($error);
                            }
                            remove_all_actions('shutdown');
                            die();
                        }
                    }
                }
            }
        } // End _ipn_check

        /************************ PUBLIC METHODS  *****************************/

        public function init()
        {
            $me = $this;

            if(!is_admin() && isset($_REQUEST['cp_calculatedfieldsf_id']))
            {
                $row = $this->_get_entry($_REQUEST['cp_calculatedfieldsf_id']);

                if(
                    !empty($row) &&
                    $row->enabled*1 &&
                    ($settings = unserialize($row->settings)) !== false
                )
                {
                    if(!empty($settings['type']) && $settings['type'] == 'iframe')
                    {
                        // iFrame integration method
                        if(isset($_REQUEST['cff_eway_get_shared_payment_url']))
                        {
                            if(wp_verify_nonce($_REQUEST['cff_eway_get_shared_payment_url'], __FILE__))
                            {
                                add_action('cpcff_process_data_before_insert', function($params, $b, $c) use ($me){
                                    remove_all_actions('shutdown');
                                    die($me->_get_shared_payment_url($params));
                                }, 0, 3);
                            }
                            else
                            {
                                remove_all_actions('shutdown');
                                die('Error: Invalid data for eWay SharedPaymentUrl request');
                            }
                        }
                        elseif(isset($_REQUEST['cff_eway_transaction_id']))
                        {
                            // IPN Check
                            add_action( 'cpcff_process_data', function($params) use ($me){
                                $params['AccessCode'] = $_REQUEST['cff_eway_transaction_id'];
                                $me->_ipn_check($params);
                            }, 11, 1 );
                        }
                    }
                    else
                    {
                        if(isset($_REQUEST['cff_eway_ipncheck']))
                        {
                            if(isset($_REQUEST['AccessCode']))
                            {
                                add_action('init', function() use ($me){
                                    // IPN Check
                                    $me->_ipn_check(
                                        [
                                            'itemnumber'=>isset($_REQUEST['itemnumber']) ? @intval($_REQUEST['itemnumber']) : 0, 'formid' => isset($_REQUEST['cp_calculatedfieldsf_id']) ? @intval($_REQUEST['cp_calculatedfieldsf_id']) : 0,
                                            'AccessCode' => $_REQUEST['AccessCode']
                                        ]
                                    );
                                });
                            }
                            else
                            {
                                remove_all_actions('shutdown');
                                die('Invalid data for eWay payment processing');
                            }
                        }
                        else // Redirect to Shared Page method
                        {
                            add_action( 'cpcff_process_data', function($params) use ($me){
                                $url = $me->_get_shared_payment_url($params);
                                remove_all_actions('shutdown');
                                die('<script>document.location.href="'.esc_js($url).'";</script>');
                            }, 11, 1 );
                        }
                    }
                }
            }
        } // End init

		/**
         * Inserts banks and Check if the Optional is enabled in the form, and inserts radiobutton
         */
        public function	insert_payment_fields( $form_code, $id )
		{
			$row = $this->_get_entry($id);

            if (
                empty($row) ||
                ($enabled = $row->enabled*1) == 0 ||
                strpos($form_code, 'vt="'.$this->addonID.'"') !== false
            ) return $form_code;

            if(($settings = unserialize($row->settings)) === false) $settings = [];

            // For iframe integration enqueue the required javascript file
            if(!empty($settings['type']) && $settings['type'] == 'iframe')
                wp_enqueue_script('çff_eway_script', '//secure.ewaypayments.com/scripts/eCrypt.min.js', [], CP_CALCULATEDFIELDSF_VERSION);

            // output radio-buttons here
			$form_code = preg_replace(
                '/<!--addons-payment-options-->/i',
                '<!--addons-payment-options--><div><input type="radio" name="bccf_payment_option_paypal" id="cffaddonidtp'.$id.'" vt="'.$this->addonID.'" value="'.$this->addonID.'" checked> '.
                __(!empty($settings['label']) ? $settings['label'] : $this->default_pay_label, 'calculated-fields-form').
                '</div>',
                $form_code
            );

            if(
                ($enabled == 2 || $enabled == 4) &&
                !strpos($form_code,'bccf_payment_option_paypal" vt="0')
            )
            {
			    $form_code = preg_replace(
                    '/<!--addons-payment-options-->/i',
                    '<!--addons-payment-options--><div><input type="radio" name="bccf_payment_option_paypal" vt="0" value="0"> '.
                    __($this->_cpcff_main->get_form($id)->get_option('enable_paypal_option_no',CP_CALCULATEDFIELDSF_PAYPAL_OPTION_NO), 'calculated-fields-form').
                    '</div>',
                    $form_code
                );
            }

			if(substr_count($form_code, 'name="bccf_payment_option_paypal"') > 1)
            {
			    $form_code = str_replace( 'id="field-c0" style="display:none">', 'id="field-c0">', $form_code);
            }

            return $form_code;
		} // End insert_payment_fields

        public function javascript_validation_code($sequence, $formid)
        {
            $row = $this->_get_entry($formid);
            if(
                !empty($row) &&
                $row->enabled*1 &&
                ($settings = unserialize($row->settings)) !== false &&
                !empty($settings['type']) &&
                $settings['type'] == 'iframe'
            )
            {
            ?>
                if(
                    (typeof cff_eway_handler_paid == 'undefined' || !cff_eway_handler_paid) &&
                    $dexQuery("input[name='bccf_payment_option_paypal']:checked").val() == '<?php echo $this->addonID; ?>'
                )
                {
                    var cff_eway_data = _form.find(':input').not('.ignore,[type="file"],.cpcff-recordset,.cff-exclude :input,[id^="form_structure_"]').serializeArray();
                    cff_eway_data.push({'name': 'cff_eway_get_shared_payment_url', 'value': '<?php print esc_js(wp_create_nonce(__FILE__)); ?>'});
                    $dexQuery.ajax({
                        type: "POST",
                        url: '<?php echo CPCFF_AUXILIARY::site_url(true); ?>/',
                        data: cff_eway_data,
                        success: function(data)
                        {
                            if(/error/i.test(data))
                            {
                                alert(data);
                            }
                            else if(/^https/i.test(data))
                            {
                                eCrypt.showModalPayment(
                                    {sharedPaymentUrl:data},
                                    function(result, transactionID, errors)
                                    {
                                        if(result == "Complete")
                                        {
                                            _form.append('<input type="hidden" name="cff_eway_transaction_id" value="'+transactionID+'">');
                                            cff_eway_handler_paid = true;
                                            doValidate_<?php echo CPCFF_MAIN::$form_counter; ?>(_form);
                                        }
                                        else if(result == "Error")
                                        {
                                            alert(errors);
                                        }
                                    }
                                );
                            }
                        }
                    });
                    return false;
                }
            <?php
            }
        } // End javascript_validation_code




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
    $CPCFF_eway_obj = new CPCFF_EWAY();

    CPCFF_ADDONS::add($CPCFF_eway_obj);
}