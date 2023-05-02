<?php
/*
Documentation: https://stripe.com/docs/quickstart
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPCFF_Stripe' ) )
{
    class CPCFF_Stripe extends CPCFF_BaseAddon
    {
		static public $category = 'Payment Gateways';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-stripe-20151212";
		protected $name = "CFF - Stripe";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#stripe-addon';
        protected $default_pay_label = "Pay with Credit Cards";

		public function get_addon_form_settings( $form_id )
		{
			global $wpdb;
			$table = $wpdb->prefix.$this->form_table;

			// Insertion in database
			if(
				isset( $_REQUEST[ 'CPCFF_Stripe_id' ] )
			)
			{
			    // verify needed fields for update
                $this->add_field_verify($table, "stripe_metadata");
                $this->add_field_verify($table, "stripe_integrationtype");
                $this->add_field_verify($table, "stripe_language");
			    $this->add_field_verify($table, "frequency");
			    $this->add_field_verify($table, "trialdays");
			    $this->add_field_verify($table, "planname");
                $this->add_field_verify($table, "askbilling");
                $this->add_field_verify($table, "stripe_mode");
                $this->add_field_verify($table, "stripe_testkey");
                $this->add_field_verify($table, "stripe_testsecretkey");
                $this->add_field_verify($table, "stripe_subtitle");
                $this->add_field_verify($table, "stripe_logoimage");
                $this->add_field_verify($table, "times");
                $this->add_field_verify($table, "times_field");

			    $wpdb->delete( $table, array( 'formid' => $form_id ), array( '%d' ) );
				$wpdb->insert(
                    $table,
                    array(
                        'formid' => $form_id,
                        'stripe_key'	 => $_REQUEST["stripe_key"],
                        'stripe_secretkey'	 => $_REQUEST["stripe_secretkey"],
                        'enabled'	 => $_REQUEST["stripe_enabled"],
                        'frequency'	 => $_REQUEST["stripe_frequency"] == 'field' ?
                                        (
                                            !empty($_REQUEST['stripe_frequency_field']) ?
                                            $_REQUEST['stripe_frequency_field'] : ''
                                        ) : $_REQUEST["stripe_frequency"],
                        'times'	     => $_REQUEST["stripe_recurrent_times"],
                        'times_field'=> $_REQUEST["stripe_recurrent_times_field"],
                        'trialdays'	 => $_REQUEST["stripe_trialdays"],
                        'planname'	 => $_REQUEST["stripe_planname"],
                        'stripe_mode'	 => $_REQUEST["stripe_mode"],
                        'stripe_testkey'	 => $_REQUEST["stripe_testkey"],
                        'stripe_testsecretkey'	 => $_REQUEST["stripe_testsecretkey"],
                        'stripe_subtitle'	 => $_REQUEST["stripe_subtitle"],
                        'stripe_logoimage'	 => $_REQUEST["stripe_logoimage"],
                        'stripe_language'	 => $_REQUEST["stripe_language"],
                        'enable_option_yes'	 => $_REQUEST["stripe_enable_option_yes"],
                        'askbilling'	 => $_REQUEST["askbilling"],
                        'stripe_integrationtype'	 => $_REQUEST["stripe_integrationtype"],
                        'stripe_metadata'	 => $_REQUEST["stripe_metadata"]
                    ),
                    array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
                        '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
                );
			}


			$row = $wpdb->get_row(
						$wpdb->prepare( "SELECT * FROM ".$table." WHERE formid=%d", $form_id ),
                        ARRAY_A
					);
			if (empty($row))
			{
                $row = [];
			    $row["stripe_key"] = "";
			    $row["stripe_secretkey"] = "";
			    $row["enabled"] = "0";
			    $row["frequency"] = "";
			    $row["trialdays"] = "0";
			    $row["planname"] = "";
			    $row["stripe_mode"] = "1";
			    $row["stripe_testkey"] = "";
			    $row["stripe_testsecretkey"] = "";
			    $row["stripe_subtitle"] = "";
			    $row["stripe_logoimage"] = "";
                $row["stripe_language"] = "";
                $row["askbilling"] = 0;
                $row["enable_option_yes"] = $this->default_pay_label;
                $row["stripe_integrationtype"] = "";
                $row["stripe_metadata"] = "";
			}

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<div id="metabox_stripe_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_stripe_addon_form_settings' ) ); ?>" >
				<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
				<div class="inside">
				   <input type="hidden" name="CPCFF_Stripe_id" value="1" />
                   <table class="form-table">
                    <tr valign="top">
                    <th scope="row"><?php _e('Enable Stripe?', 'calculated-fields-form'); ?></th>
                    <td><select name="stripe_enabled" class="width75">
                         <option value="0" <?php if (!$row["enabled"]) echo 'selected'; ?>><?php _e('No', 'calculated-fields-form'); ?></option>
                         <option value="1" <?php if ($row["enabled"]) echo 'selected'; ?>><?php _e('Yes', 'calculated-fields-form'); ?></option>
                         <option value="2" <?php if ($row["enabled"] == '2') echo 'selected'; ?>><?php _e('Optional: This payment method + Pay Later (submit without payment)', 'calculated-fields-form'); ?></option>
                         <option value="3" <?php if ($row["enabled"] == '3') echo 'selected'; ?>><?php _e('Optional: This payment method + Other payment methods (enabled)', 'calculated-fields-form'); ?></option>
                         <option value="4" <?php if ($row["enabled"] == '4') echo 'selected'; ?>><?php _e('Optional: This payment method + Other payment methods  + Pay Later ', 'calculated-fields-form'); ?></option>
                         </select>
                         <div style="margin-top:10px;background:#EEF5FB;border: 1px dotted #888888;padding:10px;width:260px;">
                           <?php _e( 'Label for this payment option', 'calculated-fields-form' ); ?>:<br />
                           <input type="text" name="stripe_enable_option_yes" size="40" style="width:250px;" value="<?php echo esc_attr($row['enable_option_yes']); ?>" />
                        </div>
                    </td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Integration Type', 'calculated-fields-form'); ?></th>
                    <td><select name="stripe_integrationtype" class="width75">
                         <option value="" <?php if (!$row["stripe_integrationtype"]) echo 'selected'; ?>><?php _e('Classic - Valid for NON European Sellers (European Union Sellers)', 'calculated-fields-form'); ?></option>
                         <option value="sca" <?php if ($row["stripe_integrationtype"] == 'sca') echo 'selected'; ?>><?php _e('SCA Ready  - Valid for European Sellers (European Union Sellers)', 'calculated-fields-form'); ?></option>
                         </select>
                    </td>
                    </tr>
                    <th scope="row"><?php _e('Payment mode?', 'calculated-fields-form'); ?></th>
                    <td><select name="stripe_mode" onchange="cffstripe_changemode(this);">
                         <option value="0" <?php if ($row["stripe_mode"] == '0') echo 'selected'; ?>><?php _e('Test Mode', 'calculated-fields-form'); ?></option>
                         <option value="1" <?php if ($row["stripe_mode"] != '0') echo 'selected'; ?>><?php _e('Live/Production Mode', 'calculated-fields-form'); ?></option>
                         </select>
                    </td>
                    </tr>
                    <tr valign="top" id="cffstripe_prod1" <?php if ($row["stripe_mode"] == '0') echo 'style="display:none"'; ?>>
                    <th scope="row">Stripe.com <span style="color:green">Production</span> <a href="https://manage.stripe.com/account/apikeys" target="_blank" title="<?php _e('click to get the your stripe key', 'calculated-fields-form'); ?>"><?php _e('Publishable Key', 'calculated-fields-form'); ?></a></th>
                    <td><input type="text" name="stripe_key" size="20" value="<?php echo esc_attr($row["stripe_key"]); ?>" class="width75" /></td>
                    </tr>
                    <tr valign="top" id="cffstripe_prod2" <?php if ($row["stripe_mode"] == '0') echo 'style="display:none"'; ?>>
                    <th scope="row">Stripe.com <span style="color:green">Production</span> <a href="https://manage.stripe.com/account/apikeys" target="_blank" title="<?php _e('click to get the your stripe secret key', 'calculated-fields-form'); ?>"><?php _e('Secret Key', 'calculated-fields-form'); ?></a></th>
                    <td><input type="text" name="stripe_secretkey" size="20" value="<?php echo esc_attr($row["stripe_secretkey"]); ?>" class="width75" /></td>
                    </tr>
                    <tr valign="top" id="cffstripe_test1" <?php if ($row["stripe_mode"] != '0') echo 'style="display:none"'; ?>>
                    <th scope="row">Stripe.com <span style="color:red">TEST</span> <a href="https://manage.stripe.com/account/apikeys" target="_blank" title="<?php _e('click to get the your stripe key', 'calculated-fields-form'); ?>"><?php _e('Publishable Key', 'calculated-fields-form'); ?></a></th>
                    <td><input type="text" name="stripe_testkey" size="20" value="<?php echo esc_attr($row["stripe_testkey"]); ?>" class="width75" /></td>
                    </tr>
                    <tr valign="top" id="cffstripe_test2" <?php if ($row["stripe_mode"] != '0') echo 'style="display:none"'; ?>>
                    <th scope="row">Stripe.com <span style="color:red">TEST</span> <a href="https://manage.stripe.com/account/apikeys" target="_blank" title="<?php _e('click to get the your stripe secret key', 'calculated-fields-form'); ?>"><?php _e('Secret Key', 'calculated-fields-form'); ?></a></th>
                    <td><input type="text" name="stripe_testsecretkey" size="20" value="<?php echo esc_attr($row["stripe_testsecretkey"]); ?>" class="width75" /></td>
                    </tr>


                    <tr valign="top">
                    <th scope="row"><?php _e('Language?', 'calculated-fields-form'); ?></th>
                    <td><select name="stripe_language" class="width30">
                         <option value="auto" <?php if (!$row["stripe_language"]) echo 'selected'; ?>><?php _e('auto (recommended)', 'calculated-fields-form'); ?></option>
                         <option value="da" <?php if ($row["stripe_language"] == 'da') echo 'selected'; ?>>Danish (da)</option>
                         <option value="nl" <?php if ($row["stripe_language"] == 'nl') echo 'selected'; ?>>Dutch (nl)</option>
                         <option value="en" <?php if ($row["stripe_language"] == 'en') echo 'selected'; ?>>English (en)</option>
                         <option value="fi" <?php if ($row["stripe_language"] == 'fi') echo 'selected'; ?>>Finnish (fi)</option>
                         <option value="fr" <?php if ($row["stripe_language"] == 'fr') echo 'selected'; ?>>French (fr)</option>
                         <option value="de" <?php if ($row["stripe_language"] == 'de') echo 'selected'; ?>>German (de)</option>
                         <option value="it" <?php if ($row["stripe_language"] == 'it') echo 'selected'; ?>>Italian (it)</option>
                         <option value="ja" <?php if ($row["stripe_language"] == 'ja') echo 'selected'; ?>>Japanese (ja)</option>
                         <option value="no" <?php if ($row["stripe_language"] == 'no') echo 'selected'; ?>>Norwegian (no)</option>
                         <option value="zh" <?php if ($row["stripe_language"] == 'zh') echo 'selected'; ?>>Simplified Chinese (zh)</option>
                         <option value="es" <?php if ($row["stripe_language"] == 'es') echo 'selected'; ?>>Spanish (es)</option>
                         <option value="sv" <?php if ($row["stripe_language"] == 'sv') echo 'selected'; ?>>Swedish (sv)</option>
                        </select>
                    </td>
                    </tr>

                    <tr valign="top">
                    <th scope="row"><?php _e('Ask for billing address?', 'calculated-fields-form'); ?></th>
                    <td><select name="askbilling" class="width30">
                         <option value="0" <?php if (!$row["askbilling"]) echo 'selected'; ?>><?php _e('No', 'calculated-fields-form'); ?></option>
                         <option value="1" <?php if ($row["askbilling"]) echo 'selected'; ?>><?php _e('Yes', 'calculated-fields-form'); ?></option>
                         </select>
                    </td>
                    </tr>

                    <tr valign="top">
                    <th scope="row"><?php _e('Payment frequency?', 'calculated-fields-form'); ?></th>
                    <td><select name="stripe_frequency" class="width30">
                         <option value="" <?php if (!$row["frequency"]) echo 'selected'; ?>><?php _e('One time payment', 'calculated-fields-form'); ?></option>
                         <option value="day" <?php if ($row["frequency"] == 'day') echo 'selected'; ?>><?php _e('Daily (subcription)', 'calculated-fields-form'); ?></option>
                         <option value="week" <?php if ($row["frequency"] == 'week') echo 'selected'; ?>><?php _e('Weekly (subscription)', 'calculated-fields-form'); ?></option>
                         <option value="month" <?php if ($row["frequency"] == 'month') echo 'selected'; ?>><?php _e('Monthly (subscription)', 'calculated-fields-form'); ?></option>
                         <option value="year" <?php if ($row["frequency"] == 'year') echo 'selected'; ?>><?php _e('Yearly (subscription)', 'calculated-fields-form'); ?></option>
                         <option value="field" <?php if (preg_match('/fieldname\d+/i',$row['frequency'])) echo 'selected'; ?>><?php _e('From field', 'calculated-fields-form'); ?></option>
                         </select><br>
                         <select name="stripe_frequency_field" class="width30" style="margin-top:10px;display:<?php print preg_match('/fieldname\d+/i',$row['frequency']) ? 'block' : 'none'; ?>" def="<?php print preg_match('/fieldname\d+/i',$row['frequency']) ? esc_attr($row['frequency']) : ''; ?>"></select>
                    </td>
                    </tr>

                    <tr valign="top" class="stripe-recurrent-payment" style="display:none;">
                    <th scope="row"></th>
                    <td>
                        <div style="width: 350px; margin-top: 5px; padding: 5px; background-color: rgb(221, 221, 255); border: 1px dotted black;">
                            <label><?php _e('Number of times', 'calculated-fields-form'); ?></label><br>
                            <select name="stripe_recurrent_times" class="large">
                                <option value="0" <?php if(empty($row['times'])) print 'SELECTED'; ?>><?php print esc_html(__('Unlimited', 'calculated-fields-form')); ?></option>
                                <option value="-1"  <?php if(!empty($row['times']) && $row['times']*1 == -1 ) print 'SELECTED'; ?>><?php print esc_html(__('Get value from a form field', 'calculated-fields-form')); ?></option>
                                <?php
                                    for($i = 2; $i <= 52; $i++)
                                        print '<option value="'.$i.'" '.(!empty($row['times']) && $row['times']*1 == $i ? 'SELECTED' : '').'>'.$i.' '.__('times', 'calculated-fields-form').'</option>';
                                ?>
                            </select>
                            <div class="stripe-recurrent-times-field">
                                <label><?php _e('Field name', 'calculated-fields-form'); ?></label><br>
                                <input type="text" name="stripe_recurrent_times_field" class="large" value="<?php print esc_attr(isset($row['times_field']) ? $row['times_field']: ''); ?>" placeholder="fieldname#" />
                            </div>
                        </div>
                    </td>
                    </tr>

                    <tr valign="top">
                    <th scope="row"><?php _e('Trial period length in days for subscription payments', 'calculated-fields-form'); ?></th>
                    <td><input type="text" name="stripe_trialdays" size="50" value="<?php echo esc_attr($row["trialdays"]); ?>" class="width30" /></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Plan name for subscription payments', 'calculated-fields-form'); ?></th>
                    <td><input type="text" name="stripe_planname" size="20" value="<?php echo esc_attr($row["planname"]); ?>" class="width75" /></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Subtitle for payment panel', 'calculated-fields-form'); ?></th>
                    <td><input type="text" name="stripe_subtitle" size="20" value="<?php echo esc_attr($row["stripe_subtitle"]); ?>" class="width75" /></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('URL of logo image', 'calculated-fields-form'); ?></th>
                    <td><input type="text" name="stripe_logoimage" size="20" value="<?php echo esc_attr($row["stripe_logoimage"]); ?>" class="width75" /><br />
                    <em>* A relative or absolute URL pointing to a square image of your brand or product. The recommended minimum size is 128x128px. The supported image types are: .gif, .jpeg, and .png.</em></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Metadata fields', 'calculated-fields-form'); ?></th>
                    <td><input type="text" name="stripe_metadata" size="80" value="<?php echo esc_attr($row["stripe_metadata"]); ?>" class="width75" /><br />
                    <em>* Comma separated, example: fieldname1, fieldname2, ...</em></td>
                    </tr>
                   </table>
                   <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
				</div>
			</div>
            <script type="text/javascript">
            function cffstripe_changemode(item) {
                if (item.options.selectedIndex == 0)
                {
                    document.getElementById('cffstripe_prod1').style.display = 'none';
                    document.getElementById('cffstripe_prod2').style.display = 'none';
                    document.getElementById('cffstripe_test1').style.display = '';
                    document.getElementById('cffstripe_test2').style.display = '';
                }
                else
                {
                    document.getElementById('cffstripe_prod1').style.display = '';
                    document.getElementById('cffstripe_prod2').style.display = '';
                    document.getElementById('cffstripe_test1').style.display = 'none';
                    document.getElementById('cffstripe_test2').style.display = 'none';
                }
            }
            jQuery(function(){
                var $ = jQuery;
                $(document).on('change', '[name="stripe_frequency"],[name="stripe_integrationtype"]', function(){
                    $('.stripe-recurrent-payment')[($('[name="stripe_frequency"]').val() == '' || $('[name="stripe_integrationtype"]').val() == 'sca')? 'hide' : 'show']();
                });
                $(document).on('change', '[name="stripe_frequency"]', function(){
                    $('[name="stripe_frequency_field"]')[$(this).val() == 'field' ? 'show' : 'hide']();
                });
                $(document).on('change', '[name="stripe_recurrent_times"]', function(){
                    $('.stripe-recurrent-times-field')[$(this).val()*1 == -1 ? 'show' : 'hide']();
                });
                function load_frequency_fields_list()
                {
                    var e = $('[name="stripe_frequency_field"]'),
                        recurrent_field = e.attr('def'),
                        recurrent_str = '',
                        items = cff_form.fBuild.getItems(),
                        item;
                    for(var i in items)
                    {
                        item = items[i];
                        if (item.ftype=="fradio" || item.ftype=="fdropdown" || item.ftype=="fCalculated")
                        {
                            recurrent_str += '<option value="'+cff_esc_attr(item.name)+'" '+( ( item.name == recurrent_field ) ? "selected" : "" )+'>'+cff_esc_attr(item.name+' ('+cff_sanitize(item.title)+')')+'</option>';
                        }
                    }
                    e.html(recurrent_str);
                }
                $(document).on('cff_reloadItems', load_frequency_fields_list);
                load_frequency_fields_list();
                $('[name="stripe_frequency"]').change();
                $('[name="stripe_recurrent_times"]').change();
            });
            </script>
			<?php
		} // end get_addon_form_settings



		/************************ ADDON CODE *****************************/

        /************************ ATTRIBUTES *****************************/

        private $form_table = 'cp_calculated_fields_form_stripe';
		private $form_table_plans = 'cp_calculated_fields_form_stripeplans';
        private $form_table_customers = 'cp_calculated_fields_form_stripecustomers';
        private $_inserted = false;
		private $_cpcff_main;

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->_cpcff_main = CPCFF_MAIN::instance();
			$this->description = __("The add-on adds support for Stripe payments", 'calculated-fields-form' );
            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;

			add_action( 'cpcff_process_data_before_insert', array( &$this, 'pp_stripe' ), 1, 3 );

			add_action( 'cpcff_process_data', array( &$this, 'pp_stripe_redirect' ), 11, 1 );

			add_action( 'init', array( &$this, 'pp_stripe_check_price' ), 1 );

			add_action( 'cpcff_script_after_validation', array( &$this, 'pp_payments_script' ), 10, 2 );

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
			$db_queries[] = "CREATE TABLE ".$wpdb->prefix.$this->form_table." (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					formid INT NOT NULL,
					enabled varchar(10) DEFAULT '0' NOT NULL ,
					stripe_key varchar(255) DEFAULT '' NOT NULL ,
					stripe_secretkey varchar(255) DEFAULT '' NOT NULL ,
					frequency varchar(30) DEFAULT '' NOT NULL ,
					trialdays varchar(20) DEFAULT '' NOT NULL ,
					planname varchar(255) DEFAULT '' NOT NULL ,
                    stripe_mode varchar(10) DEFAULT '' NOT NULL ,
                    stripe_testkey varchar(255) DEFAULT '' NOT NULL ,
                    stripe_testsecretkey varchar(255) DEFAULT '' NOT NULL ,
                    stripe_subtitle varchar(255) DEFAULT '' NOT NULL ,
                    stripe_logoimage varchar(255) DEFAULT '' NOT NULL ,
                    stripe_language varchar(255) DEFAULT '' NOT NULL ,
                    askbilling varchar(10) DEFAULT '0' NOT NULL ,
                    enable_option_yes varchar(255) DEFAULT '' NOT NULL ,
                    stripe_metadata TEXT DEFAULT '' NOT NULL,
					UNIQUE KEY id (id)
				)
				CHARACTER SET utf8
				COLLATE utf8_general_ci;";

			$db_queries[] = "CREATE TABLE ".$wpdb->prefix.$this->form_table_plans." (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					planname varchar(255) DEFAULT '' NOT NULL ,
					planinterval varchar(255) DEFAULT '' NOT NULL ,
					currency varchar(30) DEFAULT '' NOT NULL ,
					amount varchar(20) DEFAULT '' NOT NULL ,
					trial_period_days varchar(20) DEFAULT '' NOT NULL,
					planresult TEXT DEFAULT '' NOT NULL,
					UNIQUE KEY id (id)
				)
				CHARACTER SET utf8
				COLLATE utf8_general_ci;";

			$db_queries[] = "CREATE TABLE ".$wpdb->prefix.$this->form_table_customers." (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					email varchar(255) DEFAULT '' NOT NULL ,
					source varchar(255) DEFAULT '' NOT NULL ,
					customer_id varchar(255) DEFAULT '' NOT NULL ,
					customerresult TEXT DEFAULT '' NOT NULL,
					UNIQUE KEY id (id)
				)
				CHARACTER SET utf8
				COLLATE utf8_general_ci;";

			$this->_run_update_database($db_queries);
		} // end update_database


		/************************ PUBLIC METHODS  *****************************/


		/**
         * Price formatting
         */
        public function fix_price($v, $c)
        {
            $c = strtoupper($c);

            if($this->is_zero_decimal_currency($c)) return ceil($v);

            if($c == 'UGX') $v = ceil($v);

            return $v*100;
        }


		/**
         * Zero decimal currencies
         */
        public function is_zero_decimal_currency($c)
        {
            $c = strtoupper($c);
            return in_array($c, ['BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA','PYG','RWF','VND','VUV','XAF','XOF','XPF']);
        }


		/**
         * Inserts banks and Check if the Optional is enabled in the form, and inserts radiobutton
         */
        public function	insert_payment_fields( $form_code, $id )
		{
            global $wpdb;

			$rows = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $id )
					);

			if (empty( $rows ) || !$rows[0]->enabled || strpos($form_code, 'vt="'.$this->addonID.'"') !== false)
			    return $form_code;


            if ($rows[0]->stripe_integrationtype != 'sca')
            {
                $checkscript = '';
                // $checkscript = '<script type="text/javascript">function cffaddonstripe'.$id.'(){ try { if(document.getElementById("cffaddonidtp'.$id.'").checked) document.getElementById("opfield'.$this->addonID.$id.'").style.display=""; else document.getElementById("opfield'.$this->addonID.$id.'").style.display="none"; } catch (e) {} }setInterval("cffaddonstripe'.$id.'()",200);</script>';
			    $code = '<div id="opfield"'.$this->addonID.$id.'">'.
                          '<script src="https://checkout.stripe.com/checkout.js"></script>'.
                          '      <script>'.
                          '         var cpabc_stripe_handler_paid= false;'.
                          '         var cpabc_stripe_handler_'.CPCFF_MAIN::$form_counter.' = StripeCheckout.configure({'.
                          '           key: \''.($rows[0]->stripe_mode=='0'?$rows[0]->stripe_testkey:$rows[0]->stripe_key).'\','.
                          '           image: \'\','.
                          '           locale: \''.$rows[0]->stripe_language.'\','.
                          '           token: function(token, args) {'.
                          '             document.getElementById("stptok'.$id.'").value = token.id;'.
                          '             cpabc_stripe_handler_paid = true;'.
                          '             doValidate_'.CPCFF_MAIN::$form_counter.'(document.getElementById("cp_calculatedfieldsf_pform_'.CPCFF_MAIN::$form_counter.'"));'.
                          '           }'.
                          '         });'.
                          '</script>'.
                          '<input type="hidden" name="stptok" id="stptok'.$id.'" value="" />'.
			            '</div>'.$checkscript;
            }
            else
                $code = '';

			$form_code = preg_replace( '/<!--addons-payment-fields-->/i', '<!--addons-payment-fields-->'.$code, $form_code );

			// output radio-buttons here
			$form_code = preg_replace( '/<!--addons-payment-options-->/i', '<!--addons-payment-options--><div><input type="radio" name="bccf_payment_option_paypal" id="cffaddonidtp'.$id.'" vt="'.$this->addonID.'" value="'.$this->addonID.'" checked> '.__(($rows[0]->enable_option_yes!=''?$rows[0]->enable_option_yes:$this->default_pay_label) , 'calculated-fields-form').'</div>', $form_code );

            if (($rows[0]->enabled == '2' || $rows[0]->enabled == '4') && !strpos($form_code,'bccf_payment_option_paypal" vt="0') )
			    $form_code = preg_replace( '/<!--addons-payment-options-->/i', '<!--addons-payment-options--><div><input type="radio" name="bccf_payment_option_paypal" vt="0" value="0"> '.__($this->_cpcff_main->get_form($id)->get_option('enable_paypal_option_no',CP_CALCULATEDFIELDSF_PAYPAL_OPTION_NO), 'calculated-fields-form').'</div>', $form_code );

			if (substr_count ($form_code, 'name="bccf_payment_option_paypal"') > 1)
			    $form_code = str_replace( 'id="field-c0" style="display:none">', 'id="field-c0">', $form_code);

            return $form_code;
		} // End insert_payment_fields


		/**
         * process payment
         */
		public function pp_stripe(&$params, &$str, $fields )
		{
            global $wpdb;

			// documentation: https://goo.gl/w3kKoH

            $rows = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $params["formid"] )
					);

			$payment_option = (isset($_POST["bccf_payment_option_paypal"])?$_POST["bccf_payment_option_paypal"]:$this->addonID);
			if (empty( $rows ) || !$rows[0]->enabled || $payment_option != $this->addonID)
			    return;

			$form_obj = $this->_cpcff_main->get_form($params['formid']);
            $currency = $form_obj->get_option('currency', CP_CALCULATEDFIELDSF_DEFAULT_CURRENCY);


            if (!round( $this->fix_price(str_replace(",","",$params["final_price"]), $currency) ,0))
                return;

            if ($rows[0]->stripe_integrationtype == 'sca')
                return;

            $params["payment_option"] = $this->name;

            // **************
			if(!class_exists('\Stripe\Stripe'))
				require_once dirname( __FILE__ ) . '/stripe-php.addon/init.php';
            \Stripe\Stripe::setApiKey( ($rows[0]->stripe_mode=='0'?$rows[0]->stripe_testsecretkey:$rows[0]->stripe_secretkey) );

            // Get the credit card details submitted by the form
            $token = $_POST['stptok'];
            $amount = round( $this->fix_price(str_replace(",","",$params["final_price"]), $currency) ,0);

            if(preg_match('/fieldname\d+/i', $rows[0]->frequency) && !empty($params[$rows[0]->frequency]))
                $rows[0]->frequency = strtolower($params[$rows[0]->frequency]);

            if(in_array($rows[0]->frequency, array('day','week','month','year')))
            {
                // step 1: Identify / create plan
                // ***********************************
                $rowsplan = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table_plans." WHERE planname=%s AND planinterval=%s AND currency=%s AND amount=%s AND trial_period_days=%s",
						   ($rows[0]->stripe_mode=='0'?"test_".$rows[0]->planname:$rows[0]->planname." "), $rows[0]->frequency, $currency, $amount, $rows[0]->trialdays
						 )
					);
				if (!count($rowsplan))
				{
				    $wpdb->insert(
                                $wpdb->prefix.$this->form_table_plans,
                                array(
                                    'planname' => ($rows[0]->stripe_mode=='0'?"test_".$rows[0]->planname:$rows[0]->planname." "),
                                    'planinterval' => $rows[0]->frequency,
                                    'currency' => $currency,
                                    'amount' => $amount,
                                    'trial_period_days' => "".$rows[0]->trialdays
                                ),
                                array ('%s', '%s', '%s', '%s', '%s')
					);
				    $planid = $wpdb->insert_id;
				    try {
                        $plan = \Stripe\Plan::create(
                            array(
                                'product'  => array(
                                    'name' => ($rows[0]->stripe_mode=='0'?"test_".$rows[0]->planname:$rows[0]->planname)
                                ),
                                "id" => "cffplan-".$planid,
                                "interval" => $rows[0]->frequency,
                                "currency" => $currency,
                                "trial_period_days" => $rows[0]->trialdays,
                                "amount" => $amount
                            )
                        );
                        $wpdb->update($wpdb->prefix.$this->form_table_plans,
				                        array('planresult' => serialize($plan)),
                  						array('id' => $planid)
					    );
                    } catch(Exception  $e) {
                        // The card has been declined
                        echo 'Stripe: Failed to create plan. Error message: '. $e->getMessage();
                        exit;
                    }
				}
				else
				    $planid = $rowsplan[0]->id;

                // step 2: Create customer
                // ***********************************
                try {
                    $notifyto = explode( ',', $form_obj->get_option('cu_user_email_field', '') );
                    $notifyto = @$params[ $notifyto[0] ];
                    $customer = \Stripe\Customer::create(array(
                      "email" => $notifyto,
                      "source" => $token
                    ));
                    $wpdb->insert(
				                  $wpdb->prefix.$this->form_table_customers,
				                  array(
				                         'email' => $notifyto."",
				                         'source' => $token,
				                         'customer_id' => $customer->id,
				                         'customerresult' => serialize($customer)
						               ),
						          array ('%s', '%s', '%s', '%s')
					);
                } catch(Exception  $e) {
                    // The card has been declined
                    echo 'Stripe: Failed to create customer. Error message: '. $e->getMessage();
                    exit;
                }

                // Step 3: Subscribe customer to plan
                // ***********************************

                $args = array(
                      "customer" => $customer->id,
                      "plan" => "cffplan-".$planid,
                    );

                if(isset($rows[0]->times) && $rows[0]->times*1)
                {
                    switch($rows[0]->frequency)
                    {
                        case 'day'  : $increment = 24*60*60; break;
                        case 'week' : $increment = 7*24*60*60; break;
                        case 'month': $increment = 30*24*60*60; break;
                        case 'year' : $increment = 365*24*60*60; break;
                    }

                    if($rows[0]->times*1 != -1)
                    {
                        $cancel_at = time()+$rows[0]->times*$increment;
                    }
                    elseif(
                        !empty($rows[0]->times_field) &&
                        !empty($params[$rows[0]->times_field]) &&
                        is_numeric($params[$rows[0]->times_field])
                    )
                    {
                        $cancel_at = time()+intval($params[$rows[0]->times_field])*$increment;
                    }
                    if(!empty($cancel_at)) $args['cancel_at'] = $cancel_at+@intval($rows[0]->trialdays)*24*60*60;
                }

                try {
                    \Stripe\Subscription::create($args);
                } catch(Exception  $e) {
                    if ($e->getStripeCode() == 'authentication_required')
                    {
                        echo 'Transaction failed. SCA Strong Authentication required! If you are the website owner please change the "classic" mode to the "SCA" mode in the settings then <a href="javascript:window.history.back();">go back and try again</a>.';
                        exit;
                    }
                    // The card has been declined
                    echo 'Stripe: Failed to create subscription. Error message: '. $e->getMessage();
                    exit;
                }


            }
            else{ // if no subscription then process one time payment_option
                // Create the charge on Stripe's servers - this will charge the user's card

                $product_item_name = $form_obj->get_option('paypal_product_name', CP_CALCULATEDFIELDSF_DEFAULT_PRODUCT_NAME);
                foreach ($params as $item => $value)
                    $product_item_name = str_replace('<%'.$item.'%>',(is_array($value)?(implode(", ",$value)):($value)),$product_item_name);

                $notifyto = explode( ',', $form_obj->get_option('cu_user_email_field', '') );
                $notifyto = @$params[ $notifyto[0] ];

                try {

                    $query = $wpdb->get_results("SHOW TABLE STATUS LIKE '".$wpdb->prefix.CP_CALCULATEDFIELDSF_POSTS_TABLE_NAME_NO_PREFIX."'");
                    if (count($query))
                        $next_itemnumber = $query[0]->Auto_increment;
                    else
                        $next_itemnumber = "-";

                    $metadata = $this->get_metadata($rows[0]->stripe_metadata, $params);
                    $metadata["Submission ID"] = $next_itemnumber;
                    $charge = \Stripe\Charge::create(array(
                      "amount" => $amount, // amount in cents, again
                      "receipt_email" => $notifyto,
                      "currency" => $currency,
                      "card" => $token,
                      "description" => $product_item_name,
                      "metadata"  => $metadata
                      )
                    );
                } catch(Exception $e) {
                    if ($e->getStripeCode() == 'authentication_required')
                    {
                        echo 'Transaction failed. SCA Strong Authentication required! If you are the website owner please change the "classic" mode to the "SCA" mode in the settings then <a href="javascript:window.history.back();">go back and try again</a>.';
                        exit;
                    }
                    // The card has been declined
                    echo 'Transaction failed. The card has been declined. Please <a href="javascript:window.history.back();">go back and try again</a>.';
                    exit;
                }
            }
            // **************

		} // end pp_stripe

        function pp_stripe_redirect($params)
        {
            global $wpdb;
            $row = $wpdb->get_row(
				$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $params["formid"] )
			);

			$payment_option = (isset($_POST["bccf_payment_option_paypal"])?$_POST["bccf_payment_option_paypal"]:$this->addonID);
			if (
				empty( $row )  ||
				!$row->enabled ||
				$payment_option != $this->addonID ||
                !ceil($params["final_price"]*100)
			) return;

			$form_obj = CPCFF_SUBMISSIONS::get_form($params["itemnumber"]);
			if($form_obj->get_option('paypal_notiemails', '0') == '1')
			    $this->_cpcff_main->send_mails($params['itemnumber']);

            // start: New SCA method
            if ($row->stripe_integrationtype == 'sca')
            {
                $amount = ceil($this->fix_price($params["final_price"], $form_obj->get_option('currency', CP_CALCULATEDFIELDSF_DEFAULT_CURRENCY)));

                if (!class_exists('\Stripe\Stripe'))
                    require_once dirname( __FILE__ ) . '/stripe-php.addon/init.php';
                \Stripe\Stripe::setApiKey( ($row->stripe_mode=='0'?$row->stripe_testsecretkey:$row->stripe_secretkey)  );

                $form_obj = $this->_cpcff_main->get_form($params['formid']);

                if(preg_match('/fieldname\d+/i', $row->frequency) && !empty($params[$row->frequency]))
                    $row->frequency = strtolower($params[$row->frequency]);

                if (in_array( $row->frequency, array('day','week','month','year') ) )
                {
                    // step 1: Identify / create plan
                    // ***********************************
                    $currency = $form_obj->get_option('currency', CP_CALCULATEDFIELDSF_DEFAULT_CURRENCY);
                    $rowsplan = $wpdb->get_results(
    						$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table_plans." WHERE planname=%s AND planinterval=%s AND currency=%s AND amount=%s AND trial_period_days=%s",
    						   ($row->stripe_mode=='0'?"test_".$row->planname.$params["itemnumber"]:$row->planname.$params["itemnumber"]), $row->frequency, $currency, $amount, $row->trialdays
    						 )
    					);
    				if (!count($rowsplan))
    				{
    				    $wpdb->insert(
    				                  $wpdb->prefix.$this->form_table_plans,
    				                  array(
    				                         'planname' => ($row->stripe_mode=='0'?"test_".$row->planname.$params["itemnumber"]:$row->planname.$params["itemnumber"]),
    				                         'planinterval' => $row->frequency,
    				                         'currency' => $form_obj->get_option('currency', CP_CALCULATEDFIELDSF_DEFAULT_CURRENCY),
    				                         'amount' => $amount,
    				                         'trial_period_days' => "".$row->trialdays
    						               ),
    						          array ('%s', '%s', '%s', '%s', '%s')
    					);
    				    $planid = $wpdb->insert_id;
    				    try {
                            $plan = \Stripe\Plan::create(array(
                              'product'        => array(
    				               	'name' => ($row->stripe_mode=='0'?"test_".$row->planname.$params["itemnumber"]:$row->planname.$params["itemnumber"])
    				               ),
                              "id" => "apphbplan-".$planid,
                              "interval" => $row->frequency,
                              "currency" => $form_obj->get_option('currency', CP_CALCULATEDFIELDSF_DEFAULT_CURRENCY),
                              "trial_period_days" => $row->trialdays,
                              "amount" => $amount
                            ));
                            $wpdb->update($wpdb->prefix.$this->form_table_plans,
    				                        array('planresult' => serialize($plan)),
                      						array('id' => $planid)
    					    );
                        } catch(Exception  $e) {
                            // The card has been declined
                            echo 'Stripe: Failed to create plan. Error message: '. $e->getMessage();
                            exit;
                        }
    				}
    				else
    				    $planid = $rowsplan[0]->id;

                    $session = \Stripe\Checkout\Session::create([
                        'payment_method_types' => ['card'],
                        'subscription_data' => (
                            !$row->trialdays ?
                            [
                                'items' => [
                                    [
                                        'plan' => "apphbplan-".$planid,
                                    ]
                                ],
                            ]
                            :
                            [
                                'items' => [
                                    [
                                        'plan' => "apphbplan-".$planid,
                                    ]
                                ],
                                'trial_period_days' => $row->trialdays,
                            ]
                        ),
                        'success_url' => CPCFF_AUXILIARY::site_url().'/?k=1&cp_cffid='.$params["formid"].'&inumber='.$params["itemnumber"].'&cp_cffstripe_ipncheck={CHECKOUT_SESSION_ID}',
                        'cancel_url' => $_POST["cp_ref_page"],
                    ]);

                }
                else
                {
                    $product_item_name = $form_obj->get_option('paypal_product_name', CP_CALCULATEDFIELDSF_DEFAULT_PRODUCT_NAME);
                    foreach ($params as $item => $value)
                        $product_item_name = str_replace('<%'.$item.'%>',(is_array($value)?(implode(", ",$value)):($value)),$product_item_name);

					$notifyto = explode( ',', $form_obj->get_option('cu_user_email_field', '') );
                    $notifyto = @$params[ $notifyto[0] ];

    	            try {

                    $metadata = $this->get_metadata($row->stripe_metadata, $params);
                    $metadata["Submission ID"] = $params["itemnumber"];

                    $session = \Stripe\Checkout\Session::create([
                      'payment_method_types' => ['card'],
				      'customer_email' => $notifyto,
                      'client_reference_id'  => $product_item_name,
                      'payment_intent_data' => array(
                           'description' => $product_item_name,
                           'statement_descriptor' => substr($product_item_name,0,22),
                           'metadata'  => $metadata
                      ),
                      'line_items' => [[
                        'price_data' => [
                          'currency' => $form_obj->get_option('currency', CP_CALCULATEDFIELDSF_DEFAULT_CURRENCY),
                          'unit_amount' => $amount,
                          'product_data' => [
                            'name' => $product_item_name,
                            'description' => $product_item_name,
                          ],
                        ],
                        'quantity' => 1,
                      ]],
                      'mode' => 'payment',
                      'success_url' => CPCFF_AUXILIARY::site_url().'/?k=1&cp_cffid='.$params["formid"].'&inumber='.$params["itemnumber"].'&cp_cffstripe_ipncheck={CHECKOUT_SESSION_ID}',
                      'cancel_url' => $_POST["cp_ref_page"],
                    ]);
						} catch (Exception $e) { echo ($e->getMessage());  exit; }
					//echo 'SCA detected3';exit;
                }

                ?>
<html><head><title>Redirecting to Stripe Checkout</title><body>
<script src="https://js.stripe.com/v3"></script>
<script>
var stripe = Stripe('<?php echo ($row->stripe_mode=='0'?$row->stripe_testkey:$row->stripe_key); ?>');
stripe.redirectToCheckout({
  // Make the id field from the Checkout Session creation API response
  // available to this file, so you can provide it as parameter here
  // instead of the {{CHECKOUT_SESSION_ID}} placeholder.
  sessionId: '<?php echo $session->id; ?>'
}).then(function (result) {
  // If `redirectToCheckout` fails due to a browser or network
  // error, display the localized error message to your customer
  // using `result.error.message`.
  alert(result.error.message);
});
</script>
</body>
</html>
<?php
                exit;
            }
            // end: new SCA method

            $this->completeAndRedirect($params);
        }


		/**
		 * mark the item as paid
		 */
        private function completeAndRedirect($params)
        {
			// mark item as paid
            $form_obj = $this->_cpcff_main->get_form($params['formid']);
			CPCFF_SUBMISSIONS::update($params["itemnumber"], array('paid'=>1));

            do_action( 'cpcff_payment_processed', $params );

			$form_obj = CPCFF_SUBMISSIONS::get_form($params["itemnumber"]);
			if($form_obj->get_option('paypal_notiemails', '0') != '1')
			    $this->_cpcff_main->send_mails($params['itemnumber']);

            $redirect = true;

		    /**
		     * Filters applied to decide if the website should be redirected to the thank you page after submit the form,
		     * pass a boolean as parameter and returns a boolean
		     */
            $redirect = apply_filters( 'cpcff_redirect', $redirect );

            if( $redirect )
            {
                $location = CPCFF_AUXILIARY::replace_params_into_url($form_obj->get_option('fp_return_page', CP_CALCULATEDFIELDSF_DEFAULT_fp_return_page), $params);
                header("Location: ".$location);
                exit;
            }
        }


       private function get_metadata($fields, $params)
       {
           $fields = explode(",",$fields);
           $metadata = array();
           foreach ($fields as $item)
           {
               $key = trim($item);
               if ($key && isset($params[$key]))
                   $metadata[$key] = $params[$key];
           }
           return $metadata;
       }

		/**
		 * log errors
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


        /**
         * script process payment
         */
		public function pp_stripe_check_price()
		{
            global $wpdb;

            if (isset($_GET["cp_cffstripe_ipncheck"]) && $_GET["cp_cffstripe_ipncheck"] != '')
            {
                $row = $wpdb->get_row(
                				$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $_GET["cp_cffid"] )
                			);
                try
                {
                    if (!class_exists('\Stripe\Stripe'))
                        require_once dirname( __FILE__ ) . '/stripe-php.addon/init.php';
                    \Stripe\Stripe::setApiKey( ($row->stripe_mode=='0'?$row->stripe_testsecretkey:$row->stripe_secretkey) );
                    $session = \Stripe\Checkout\Session::retrieve($_GET["cp_cffstripe_ipncheck"]);
                    if ($session->payment_intent)
                        $pintent = \Stripe\PaymentIntent::retrieve($session->payment_intent);
                    else
                        $pintent = \Stripe\Subscription::retrieve($session->subscription);
                    if ($pintent->status == 'succeeded' || $pintent->status == 'active' || $pintent->status == 'trialing')
                    {
                        $submission = CPCFF_SUBMISSIONS::get($_GET["inumber"]);
			            if(empty($submission)) return;
                        $params = $submission->paypal_post;
                        $params["itemnumber"] = $_GET["inumber"];
                        $this->completeAndRedirect($params);
                    }
                    else
                    {
                        echo 'Error: Purchase cannot be verified. Please contact the seller.';
                        exit;
                    }
                }
                catch (Exception $e)
                {
                    die ('Error: Purchase cannot be verified. Please contact the seller. Error code: <strong>'.$e->getMessage().'</strong>');
                }
                exit;
            }

            if (!isset($_POST["cpcff_stripe_getprice"]) || !isset($_POST["formid"]))
                return;

            $form_id = $_POST["formid"];

            $rows = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $form_id )
					);

			if (empty( $rows ) || !$rows[0]->enabled)
			    return;

			$form_obj = $this->_cpcff_main->get_form($form_id);
	        $find_arr = array( ',', '.');
	        $replace_arr = array( '', '.');
			$price = preg_replace( '/[^\d\.\,]/', '', $_POST["ps"] );
			$price = str_replace( $find_arr, $replace_arr, $price );
	        $paypal_base_amount = preg_replace('/[^\d\.\,]/', '', $form_obj->get_option('paypal_base_amount', 0));
	        $paypal_base_amount = str_replace( $find_arr, $replace_arr, $paypal_base_amount );
	        $price = max( $price, $paypal_base_amount );

            // calculate discounts if any
            //---------------------------
            $discount_note = "";
            $coupon = false;
            $codes = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".CP_CALCULATEDFIELDSF_DISCOUNT_CODES_TABLE_NAME." WHERE code=%s AND expires>='".date("Y-m-d")." 00:00:00' AND `form_id`=%d", @$_POST["couponcode"], $form_id  ) );
            if (count($codes))
            {
                $coupon = $codes[0];
                if ($coupon->availability==1)
                {
	        		$coupon->discount = str_replace( $find_arr, $replace_arr, $coupon->discount );
                    $price = round (floatval ($price) - $coupon->discount,2);
                    $discount_note = " (".$form_obj->get_option('currency', CP_CALCULATEDFIELDSF_DEFAULT_CURRENCY)." ".$coupon->discount." discount applied)";
                }
                else
                {
                    $price = round (floatval ($price) - $price*$coupon->discount/100,2);
                    $discount_note = " (".$coupon->discount."% discount applied)";
                }
            }


			echo round(   $this->fix_price($price, $form_obj->get_option('currency', CP_CALCULATEDFIELDSF_DEFAULT_CURRENCY)) ,0);
			exit;

         }



		/**
         * script process payment
         */
		public function pp_payments_script( $form_sequence_id, $form_id )
		{
            global $wpdb;

            $rows = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $form_id )
					);

			if (empty( $rows ) || !$rows[0]->enabled)
			    return;

            if ($rows[0]->stripe_integrationtype == 'sca')
                return;

			$form_obj = $this->_cpcff_main->get_form($form_id);
            $currency = $form_obj->get_option('currency', CP_CALCULATEDFIELDSF_DEFAULT_CURRENCY);

?>
            if (!cpabc_stripe_handler_paid && $dexQuery("input[name='bccf_payment_option_paypal']:checked").val() == '<?php echo $this->addonID; ?>')
            {
                $dexQuery.ajax({
                    type: "POST",
                    url: '<?php echo CPCFF_AUXILIARY::site_url(true); ?>/',
                    data: {
						ps: document.getElementById("<?php echo $form_obj->get_option('request_cost', CP_CALCULATEDFIELDSF_DEFAULT_COST).$form_sequence_id; ?>").value,
						formid: <?php echo $form_id; ?>,
                        couponcode: $dexQuery("input[name='couponcode']").val(),
						cpcff_stripe_getprice: "1"
					},
                    success: function(data)
                    {
                        if (parseFloat(data) > 0)
                        {
                            var product_item_name = '<?php echo str_replace("'","\'",$form_obj->get_option('paypal_product_name', CP_CALCULATEDFIELDSF_DEFAULT_PRODUCT_NAME)); ?>';
                            try{
                                $dexQuery("input").each(function() {
                                    var attrid = ""+$dexQuery(this).attr("id");
                                    attrid = attrid.replace('<?php echo $form_sequence_id; ?>', '');
                                    product_item_name = product_item_name.replace('<'+'%'+attrid+'%>',$dexQuery(this).val());
                                });
                            }
                            catch (e)
                            {
                            }
                            var emailvalue = '';
                            try {
                                emailvalue = document.getElementById("<?php echo $form_obj->get_option('cu_user_email_field', '').$form_sequence_id; ?>").value;
                            } catch (e) {}
                            cpabc_stripe_handler<?php echo $form_sequence_id; ?>.open({
                              name: product_item_name,
                              description: '<?php echo str_replace("'","\'",$rows[0]->stripe_subtitle); ?>',
                              image: '<?php echo str_replace("'","\'",$rows[0]->stripe_logoimage); ?>',
                              currency: '<?php echo $currency; ?>',
                              email: emailvalue,
                              <?php if (@$rows[0]->askbilling) { ?>billingAddress:true,<?php } ?>
                              amount: parseFloat(data)
                            });
                        }
                        else
                        {
                            cpabc_stripe_handler_paid = true;
                            doValidate_<?php echo CPCFF_MAIN::$form_counter; ?>(document.getElementById("cp_calculatedfieldsf_pform_<?php echo CPCFF_MAIN::$form_counter; ?>"));
                        }
                    }
                });
                return false;
            }
<?php
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
    $CPCFF_Stripe_obj = new CPCFF_Stripe();

    CPCFF_ADDONS::add($CPCFF_Stripe_obj);
}