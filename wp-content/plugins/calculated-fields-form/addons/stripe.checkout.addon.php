<?php
/*
Documentation: https://stripe.com/docs/quickstart
*/
require_once dirname( __FILE__ ) . '/base.addon.php';

if ( ! class_exists( 'CPCFF_Stripe_Checkout' ) ) {
	class CPCFF_Stripe_Checkout extends CPCFF_BaseAddon {

		public static $category = 'Payment Gateways';

		/************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = 'addon-stripe-checkout-20221004';
		protected $name    = 'CFF - Stripe Checkout';
		protected $description;
		protected $help              = 'https://cff.dwbooster.com/documentation#stripe-checkout-addon';
		protected $default_pay_label = 'Pay with Credit Cards';

		public function get_addon_form_settings( $form_id ) {
			global $wpdb;
			$table = $wpdb->prefix . $this->form_table;

			// Insertion in database
			if (
				isset( $_REQUEST['cpcff_stripe_checkout_id'] )
			) {
				$wpdb->delete( $table, array( 'formid' => $form_id ), array( '%d' ) );
				$settings = array(
					'stripe_key'           => isset( $_REQUEST['stripe_checkout_key'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['stripe_checkout_key'] ) ) : '',
					'stripe_secretkey'     => isset( $_REQUEST['stripe_checkout_secretkey'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['stripe_checkout_secretkey'] ) ) : '',
					'frequency'            => isset( $_REQUEST['stripe_checkout_frequency'] ) && sanitize_text_field( wp_unslash( $_REQUEST['stripe_checkout_frequency'] ) ) == 'field' ?
									(
										! empty( $_REQUEST['stripe_checkout_frequency_field'] ) ?
										wp_unslash( $_REQUEST['stripe_checkout_frequency_field'] ) : ''
									) : ( isset( $_REQUEST['stripe_checkout_frequency'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['stripe_checkout_frequency'] ) ) : '' ),
					'times'                => isset( $_REQUEST['stripe_checkout_recurrent_times'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['stripe_checkout_recurrent_times'] ) ) : 0,
					'times_field'          => isset( $_REQUEST['stripe_checkout_recurrent_times_field'] ) ? $this->after_sanitize( sanitize_text_field( $this->before_sanitize( wp_unslash( $_REQUEST['stripe_checkout_recurrent_times_field'] ) ) ) ) : '',
					'trialdays'            => isset( $_REQUEST['stripe_checkout_trialdays'] ) && is_numeric( sanitize_text_field( wp_unslash( $_REQUEST['stripe_checkout_trialdays'] ) ) ) ? intval( sanitize_text_field( wp_unslash( $_REQUEST['stripe_checkout_trialdays'] ) ) ) : 0,
					'planname'             => isset( $_REQUEST['stripe_checkout_planname'] ) ? $this->after_sanitize( sanitize_text_field( $this->before_sanitize( wp_unslash( $_REQUEST['stripe_checkout_planname'] ) ) ) ) : '',
					'stripe_mode'          => isset( $_REQUEST['stripe_checkout_mode'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['stripe_checkout_mode'] ) ) : 1,
					'stripe_testkey'       => isset( $_REQUEST['stripe_checkout_testkey'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['stripe_checkout_testkey'] ) ) : '',
					'stripe_testsecretkey' => isset( $_REQUEST['stripe_checkout_testsecretkey'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['stripe_checkout_testsecretkey'] ) ) : '',
					'stripe_image'         => isset( $_REQUEST['stripe_checkout_image'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['stripe_checkout_image'] ) ) : '',
					'stripe_language'      => isset( $_REQUEST['stripe_checkout_language'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['stripe_checkout_language'] ) ) : 'EN',
					'enable_option_yes'    => isset( $_REQUEST['stripe_checkout_enable_option_yes'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['stripe_checkout_enable_option_yes'] ) ) : '',
					'askbilling'           => isset( $_REQUEST['stripe_checkout_askbilling'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['stripe_checkout_askbilling'] ) ) : 0,
					'tax_id_collection'    => isset( $_REQUEST['stripe_checkout_tax_id_collection'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['stripe_checkout_tax_id_collection'] ) ) : 0,
					'automatic_tax'        => isset( $_REQUEST['stripe_checkout_automatic_tax'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['stripe_checkout_automatic_tax'] ) ) : 0,
					'stripe_metadata'      => isset( $_REQUEST['stripe_checkout_metadata'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['stripe_checkout_metadata'] ) ) : '',
				);

				$wpdb->insert(
					$table,
					array(
						'formid'   => $form_id,
						'enabled'  => isset( $_REQUEST['stripe_checkout_enabled'] ) && is_numeric( $_REQUEST['stripe_checkout_enabled'] ) ? intval( $_REQUEST['stripe_checkout_enabled'] ) : 0,
						'settings' => json_encode( $settings ),
					),
					array(
						'%d',
						'%d',
						'%s',
					)
				);
			}

			$enabled = 0;

			$row = $wpdb->get_row(
				$wpdb->prepare( 'SELECT * FROM ' . $table . ' WHERE formid=%d', $form_id ),
				ARRAY_A
			);

			if ( ! empty( $row ) ) {
				$settings = $this->initialize_settings( $row['settings'] );
				$enabled  = $row['enabled'];
			} else {
				$settings = $this->initialize_settings();
			}

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<div id="metabox_stripe_checkout_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr( __CLASS__ ); ?> cff-metabox <?php print esc_attr( $cpcff_main->metabox_status( 'metabox_stripe_checkout_addon_form_settings' ) ); ?>" >
				<h3 class='hndle' style="padding:5px;"><span><?php print esc_html( $this->name ); ?></span></h3>
				<div class="inside">
					<input type="hidden" name="cpcff_stripe_checkout_id" value="1" />
					<table class="form-table">
						<tr valign="top">
							<th scope="row"><?php esc_html_e( 'Enable Stripe checkout?', 'calculated-fields-form' ); ?></th>
							<td>
								<select name="stripe_checkout_enabled" class="width75">
									<option value="0" <?php if ( ! $enabled ) {
										echo 'selected';} ?>><?php esc_html_e( 'No', 'calculated-fields-form' ); ?></option>
									<option value="1" <?php if ( 1 == $enabled ) {
										echo 'selected';} ?>><?php esc_html_e( 'Yes', 'calculated-fields-form' ); ?></option>
									<option value="2" <?php if ( 2 == $enabled ) {
										echo 'selected';} ?>><?php esc_html_e( 'Optional: This payment method + Pay Later (submit without payment)', 'calculated-fields-form' ); ?></option>
									<option value="3" <?php if ( 3 == $enabled ) {
										echo 'selected';} ?>><?php esc_html_e( 'Optional: This payment method + Other payment methods (enabled)', 'calculated-fields-form' ); ?></option>
									<option value="4" <?php if ( 4 == $enabled ) {
										echo 'selected';} ?>><?php esc_html_e( 'Optional: This payment method + Other payment methods  + Pay Later ', 'calculated-fields-form' ); ?></option>
								</select>
								<div style="margin-top:10px;background:#EEF5FB;border: 1px dotted #888888;padding:10px;width:260px;">
									<?php esc_html_e( 'Label for this payment option', 'calculated-fields-form' ); ?>:<br />
									<input type="text" name="stripe_checkout_enable_option_yes" size="40" style="width:250px;" value="<?php echo esc_attr( $settings['enable_option_yes'] ); ?>" />
								</div>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Payment mode?', 'calculated-fields-form' ); ?></th>
							<td>
								<select name="stripe_checkout_mode" onchange="cff_stripe_checkout_changemode(this);">
									<option value="0" <?php print empty( $settings['stripe_mode'] ) ? 'SELECTED' : ''; ?>>
										<?php esc_html_e( 'Test Mode', 'calculated-fields-form' ); ?>
									</option>
									<option value="1" <?php print 1 == $settings['stripe_mode'] ? 'SELECTED' : ''; ?>>
										<?php esc_html_e( 'Live/Production Mode', 'calculated-fields-form' ); ?>
									</option>
								</select>
							</td>
						</tr>
						<tr valign="top" <?php print empty( $settings['stripe_mode'] ) ? 'style="display:none"' : ''; ?> class="cff-stripe-checkout-production">
							<th scope="row">Stripe.com <span style="color:green">
								Production</span> <a href="https://manage.stripe.com/account/apikeys" target="_blank" title="<?php esc_attr_e( 'click to get the your stripe key', 'calculated-fields-form' ); ?>"><?php esc_html_e( 'Publishable Key', 'calculated-fields-form' ); ?></a>
							</th>
							<td>
								<input type="text" name="stripe_checkout_key" size="20" value="<?php echo esc_attr( $settings['stripe_key'] ); ?>" class="width75" />
							</td>
						</tr>
						<tr valign="top" <?php print empty( $settings['stripe_mode'] ) ? 'style="display:none"' : ''; ?> class="cff-stripe-checkout-production">
							<th scope="row">
								Stripe.com <span style="color:green">Production</span> <a href="https://manage.stripe.com/account/apikeys" target="_blank" title="<?php esc_attr_e( 'click to get the your stripe secret key', 'calculated-fields-form' ); ?>"><?php esc_html_e( 'Secret Key', 'calculated-fields-form' ); ?></a>
							</th>
							<td>
								<input type="text" name="stripe_checkout_secretkey" size="20" value="<?php echo esc_attr( $settings['stripe_secretkey'] ); ?>" class="width75" />
							</td>
						</tr>
						<tr valign="top" <?php print ! empty( $settings['stripe_mode'] ) ? 'style="display:none"' : ''; ?> class="cff-stripe-checkout-test">
							<th scope="row">
								Stripe.com <span style="color:red">TEST</span> <a href="https://manage.stripe.com/account/apikeys" target="_blank" title="<?php esc_attr_e( 'click to get the your stripe key', 'calculated-fields-form' ); ?>"><?php esc_html_e( 'Publishable Key', 'calculated-fields-form' ); ?></a>
							</th>
							<td>
								<input type="text" name="stripe_checkout_testkey" size="20" value="<?php echo esc_attr( $settings['stripe_testkey'] ); ?>" class="width75" />
							</td>
						</tr>
						<tr valign="top" <?php print ! empty( $settings['stripe_mode'] ) ? 'style="display:none"' : ''; ?> class="cff-stripe-checkout-test">
							<th scope="row">
								Stripe.com <span style="color:red">TEST</span> <a href="https://manage.stripe.com/account/apikeys" target="_blank" title="<?php esc_attr_e( 'click to get the your stripe secret key', 'calculated-fields-form' ); ?>"><?php esc_html_e( 'Secret Key', 'calculated-fields-form' ); ?></a>
							</th>
							<td>
								<input type="text" name="stripe_checkout_testsecretkey" size="20" value="<?php echo esc_attr( $settings['stripe_testsecretkey'] ); ?>" class="width75" />
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<?php esc_html_e( 'Language?', 'calculated-fields-form' ); ?>
							</th>
							<td>
								<select name="stripe_checkout_language" class="width30">
									<option value="auto" <?php print 'auto' == $settings['stripe_language'] ? 'SELECTED' : ''; ?>>
										<?php esc_html_e( 'auto (recommended)', 'calculated-fields-form' ); ?>
									</option>
									<option value="da" <?php print 'da' == $settings['stripe_language'] ? 'SELECTED' : ''; ?>>
										Danish (da)
									</option>
									<option value="nl" <?php print 'nl' == $settings['stripe_language'] ? 'SELECTED' : ''; ?>>
										Dutch (nl)
									</option>
									<option value="en" <?php print 'en' == $settings['stripe_language'] ? 'SELECTED' : ''; ?>>
										English (en)
									</option>
									<option value="fi" <?php print 'fi' == $settings['stripe_language'] ? 'SELECTED' : ''; ?>>
										Finnish (fi)
									</option>
									<option value="fr" <?php print 'fr' == $settings['stripe_language'] ? 'SELECTED' : ''; ?>>
										French (fr)
									</option>
									<option value="de" <?php print 'de' == $settings['stripe_language'] ? 'SELECTED' : ''; ?>>
										German (de)
									</option>
									<option value="it" <?php print 'it' == $settings['stripe_language'] ? 'SELECTED' : ''; ?>>
										Italian (it)
									</option>
									<option value="ja" <?php print 'ja' == $settings['stripe_language'] ? 'SELECTED' : ''; ?>>
										Japanese (ja)
									</option>
									<option value="no" <?php print 'no' == $settings['stripe_language'] ? 'SELECTED' : ''; ?>>
										Norwegian (no)
									</option>
									<option value="zh" <?php print 'zh' == $settings['stripe_language'] ? 'SELECTED' : ''; ?>>
										Simplified Chinese (zh)
									</option>
									<option value="es" <?php print 'es' == $settings['stripe_language'] ? 'SELECTED' : ''; ?>>
										Spanish (es)
									</option>
									<option value="sv" <?php print 'sv' == $settings['stripe_language'] ? 'SELECTED' : ''; ?>>
										Swedish (sv)
									</option>
								</select>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php esc_html_e( 'Ask for billing address?', 'calculated-fields-form' ); ?>
							</th>
							<td>
								<select name="stripe_checkout_askbilling" class="width30">
									<option value="0" <?php print empty( $settings['askbilling'] ) ? 'SELECTED' : ''; ?>>
										<?php esc_html_e( 'No', 'calculated-fields-form' ); ?>
									</option>
									<option value="1" <?php print ! empty( $settings['askbilling'] ) ? 'SELECTED' : ''; ?>>
										<?php esc_html_e( 'Yes', 'calculated-fields-form' ); ?>
									</option>
								</select>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php esc_html_e( 'Ask tax number?', 'calculated-fields-form' ); ?>
							</th>
							<td>
								<select name="stripe_checkout_tax_id_collection" class="width30">
									<option value="0" <?php print empty( $settings['tax_id_collection'] ) ? 'SELECTED' : ''; ?>>
										<?php esc_html_e( 'No', 'calculated-fields-form' ); ?>
									</option>
									<option value="1" <?php print ! empty( $settings['tax_id_collection'] ) ? 'SELECTED' : ''; ?>>
										<?php esc_html_e( 'Yes', 'calculated-fields-form' ); ?>
									</option>
								</select>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php esc_html_e( 'Automatic tax calculation?', 'calculated-fields-form' ); ?>
							</th>
							<td>
								<select name="stripe_checkout_automatic_tax" class="width30">
									<option value="0" <?php print empty( $settings['automatic_tax'] ) ? 'SELECTED' : ''; ?>>
										<?php esc_html_e( 'No', 'calculated-fields-form' ); ?>
									</option>
									<option value="1" <?php print ! empty( $settings['automatic_tax'] ) ? 'SELECTED' : ''; ?>>
										<?php esc_html_e( 'Yes', 'calculated-fields-form' ); ?>
									</option>
								</select>
							</td>
						</tr>

						<tr><td colspan="2"><hr></td></tr>

						<tr valign="top">
							<th scope="row">
								<?php esc_html_e( 'Payment frequency?', 'calculated-fields-form' ); ?>
							</th>
							<td>
								<select name="stripe_checkout_frequency" class="width30">
									<option value="" <?php print empty( $settings['frequency'] ) ? 'SELECTED' : ''; ?>>
										<?php esc_html_e( 'One time payment', 'calculated-fields-form' ); ?>
									</option>
									<option value="day" <?php print 'day' == $settings['frequency'] ? 'SELECTED' : ''; ?>>
										<?php esc_html_e( 'Daily (subcription)', 'calculated-fields-form' ); ?>
									</option>
									<option value="week" <?php print 'week' == $settings['frequency'] ? 'SELECTED' : ''; ?>>
										<?php esc_html_e( 'Weekly (subscription)', 'calculated-fields-form' ); ?>
									</option>
									<option value="month" <?php print 'month' == $settings['frequency'] ? 'SELECTED' : ''; ?>>
										<?php esc_html_e( 'Monthly (subscription)', 'calculated-fields-form' ); ?>
									</option>
									<option value="year" <?php print 'year' == $settings['frequency'] ? 'SELECTED' : ''; ?>>
										<?php esc_html_e( 'Yearly (subscription)', 'calculated-fields-form' ); ?>
									</option>
									<option value="field" <?php print preg_match( '/fieldname\d+/i', $settings['frequency'] ) ? 'SELECTED' : ''; ?>>
										<?php esc_html_e( 'From field', 'calculated-fields-form' ); ?>
									</option>
								</select><br>

								<select name="stripe_checkout_frequency_field" class="width30" style="margin-top:10px;display:<?php print preg_match( '/fieldname\d+/i', $settings['frequency'] ) ? 'block' : 'none'; ?>" def="<?php print esc_attr( $settings['frequency'] ); ?>"></select>
							</td>
						</tr>

						<tr valign="top" class="stripe-checkout-recurrent-payment" style="display:none;">
							<th scope="row"></th>
							<td>
								<div style="width: 350px; margin-top: 5px; padding: 5px; background-color: rgb(221, 221, 255); border: 1px dotted black;">
									<label><?php esc_html_e( 'Number of times', 'calculated-fields-form' ); ?></label><br>
									<select name="stripe_checkout_recurrent_times" class="large">
										<option value="0" <?php print empty( $settings['times'] ) ? 'SELECTED' : ''; ?>>
											<?php esc_html_e( 'Unlimited', 'calculated-fields-form' ); ?>
										</option>
										<option value="-1"  <?php print -1 == $settings['times'] ? 'SELECTED' : ''; ?>>
											<?php esc_html_e( 'Get value from a form field', 'calculated-fields-form' ); ?>
										</option>
										<?php
										for ( $i = 2; $i <= 52; $i++ ) {
											print '<option value="' . esc_attr( $i ) . '" ' . ( $settings['times'] == $i ? 'SELECTED' : '' ) . '>' . esc_html( $i ) . ' ' . esc_html__( 'times', 'calculated-fields-form' ) . '</option>';
										}
										?>
									</select>
									<div class="stripe-checkout-recurrent-times-field">
										<label><?php esc_html_e( 'Field name', 'calculated-fields-form' ); ?></label><br>
										<input type="text" name="stripe_checkout_recurrent_times_field" class="large" value="<?php print esc_attr( $settings['times_field'] ); ?>" placeholder="fieldname#" />
									</div>
								</div>
							</td>
						</tr>

						<tr valign="top" class="stripe-checkout-recurrent-payment" style="display:none;">
							<th scope="row">
								<?php esc_html_e( 'Trial period length in days for subscription payments', 'calculated-fields-form' ); ?>
							</th>
							<td>
								<input type="text" name="stripe_checkout_trialdays" size="50" value="<?php echo esc_attr( $settings['trialdays'] ); ?>" class="width30" />
							</td>
						</tr>

						<tr valign="top" class="stripe-checkout-recurrent-payment" style="display:none;">
							<th scope="row">
								<?php esc_html_e( 'Plan name for subscription payments', 'calculated-fields-form' ); ?>
							</th>
							<td>
								<input type="text" name="stripe_checkout_planname" size="20" value="<?php echo esc_attr( $settings['planname'] ); ?>" class="width75" />
							</td>
						</tr>

						<tr><td colspan="2"><hr></td></tr>

						<tr valign="top">
							<th scope="row">
								<?php esc_html_e( 'URL of product/store image', 'calculated-fields-form' ); ?>
							</th>
							<td>
								<input type="text" name="stripe_checkout_image" size="20" value="<?php echo esc_attr( $settings['stripe_image'] ); ?>" class="width75" /><br />
								<em>* An absolute URL pointing to a square image of your brand or product. The recommended minimum size is 128x128px. The supported image types are: .gif, .jpeg, and .png.</em>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<?php esc_html_e( 'Metadata fields', 'calculated-fields-form' ); ?>
							</th>
							<td>
								<input type="text" name="stripe_checkout_metadata" size="80" value="<?php echo esc_attr( $settings['stripe_metadata'] ); ?>" class="width75" /><br />
								<em>* Comma separated, example: fieldname1, fieldname2, ...</em>
							</td>
						</tr>
					</table>
					<div class="cff-goto-top">
						<a href="#cpformconf"><?php esc_html_e( 'Up to form structure', 'calculated-fields-form' ); ?></a>
					</div>
				</div>
			</div>
			<script type="text/javascript">
				function cff_stripe_checkout_changemode(item) {
					if (item.options.selectedIndex == 0)
					{
						jQuery('.cff-stripe-checkout-production').hide();
						jQuery('.cff-stripe-checkout-test').show();
					}
					else
					{
						jQuery('.cff-stripe-checkout-production').show();
						jQuery('.cff-stripe-checkout-test').hide();
					}
				}
				jQuery(function() {
					var $ = jQuery;
					$(document).on('change', '[name="stripe_checkout_frequency"]', function() {
						$('.stripe-checkout-recurrent-payment')[($('[name="stripe_checkout_frequency"]').val() == '') ? 'hide' : 'show']();
					});
					$(document).on('change', '[name="stripe_checkout_frequency"]', function() {
						$('[name="stripe_checkout_frequency_field"]')[$(this).val() == 'field' ? 'show' : 'hide']();
					});
					$(document).on('change', '[name="stripe_checkout_recurrent_times"]', function() {
						$('.stripe-checkout-recurrent-times-field')[$(this).val()*1 == -1 ? 'show' : 'hide']();
					});
					function load_stripe_checkout_frequency_fields_list()
					{
						var e = $('[name="stripe_checkout_frequency_field"]'),
							recurrent_field = e.attr('def'),
							recurrent_str = '',
							items = cff_form.fBuild.getItems(),
							item;

						for (var i in items) {
							item = items[i];
							if (item.ftype=="fradio" || item.ftype=="fdropdown" || item.ftype=="fCalculated")
							{
								recurrent_str += '<option value="'+cff_esc_attr(item.name)+'" '+( ( item.name == recurrent_field ) ? "selected" : "" )+'>'+cff_esc_attr(item.name+' ('+cff_sanitize(item.title)+')')+'</option>';
							}
						}
						e.html(recurrent_str);
					}
					$(document).on('cff_reloadItems', load_stripe_checkout_frequency_fields_list);
					load_stripe_checkout_frequency_fields_list();
					$('[name="stripe_checkout_frequency"]').change();
					$('[name="stripe_checkout_recurrent_times"]').change();
				});
			</script>
			<?php
		} // end get_addon_form_settings

		/************************ ADDON CODE *****************************/

		/************************ ATTRIBUTES *****************************/

		private $form_table = 'cp_calculated_fields_form_stripe_checkout';
		private $_cpcff_main;

		/************************ CONSTRUCT *****************************/

		public function __construct() {
			$this->_cpcff_main = CPCFF_MAIN::instance();
			$this->description = __( 'The add-on adds support for Stripe Checkout payments', 'calculated-fields-form' );
			// Check if the plugin is active
			if ( ! $this->addon_is_active() ) {
				return;
			}

			add_action( 'cpcff_process_data', array( $this, 'stripe_redirect' ), 11, 1 );

			add_action( 'init', array( $this, 'stripe_check_payment' ), 1 );

			add_filter( 'cpcff_the_form', array( $this, 'insert_payment_fields' ), 99, 2 );

			if ( is_admin() ) {
				// Delete forms
				add_action( 'cpcff_delete_form', array( $this, 'delete_form' ) );

				// Clone forms
				add_action( 'cpcff_clone_form', array( $this, 'clone_form' ), 10, 2 );

				// Export addon data
				add_action( 'cpcff_export_addons', array( $this, 'export_form' ), 10, 2 );

				// Import addon data
				add_action( 'cpcff_import_addons', array( $this, 'import_form' ), 10, 2 );
			}

			$this->update_database();

		} // End __construct

		/************************ PRIVATE METHODS *****************************/

		/**
		 * Create the database tables
		 */
		protected function update_database() {
			global $wpdb;
			$charset_collate = $wpdb->get_charset_collate();
			$db_queries      = array();
			$db_queries[]    = 'CREATE TABLE ' . $wpdb->prefix . $this->form_table . ' (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					formid INT NOT NULL,
					enabled TINYINT DEFAULT 0 NOT NULL,
					settings TEXT,
					UNIQUE KEY id (id)
				)
				CHARACTER SET utf8
				COLLATE utf8_general_ci;';

			$this->_run_update_database( $db_queries );
		} // end update_database

		/**
		 * Initialize the form settings
		 */
		private function initialize_settings( $settings = '' ) {
			$default_settings = array(
				'stripe_mode'          => 1,

				'stripe_key'           => '',
				'stripe_secretkey'     => '',
				'stripe_testkey'       => '',
				'stripe_testsecretkey' => '',

				'planname'             => '',
				'frequency'            => '',
				'times'                => 0,
				'times_field'          => '',
				'trialdays'            => 0,

				'stripe_subtitle'      => '',
				'stripe_image'         => '',
				'stripe_language'      => 'auto',

				'enable_option_yes'    => $this->default_pay_label,

				'askbilling'           => 0,
				'tax_id_collection'    => 0,
				'automatic_tax'        => 0,
				'stripe_metadata'      => '',
			);

			if ( ! empty( $settings ) ) {
				$settings = json_decode( $settings, true );
				if ( is_array( $settings ) ) {
					$processed_settings = array_replace_recursive( $default_settings, $settings );
				}
			}

			return ! empty( $processed_settings ) ? $processed_settings : $default_settings;
		} // End initialize_settings

		/**
		 * Price formatting
		 */
		private function fix_price( $v, $c ) {
			$c = strtoupper( $c );

			if ( $this->is_zero_decimal_currency( $c ) ) {
				return ceil( $v );
			}

			if ( 'UGX' == $c ) {
				$v = ceil( $v );
			}

			return $v * 100;
		} // End fix_price

		/**
		 * Zero decimal currencies
		 */
		private function is_zero_decimal_currency( $c ) {
			$c = strtoupper( $c );
			return in_array( $c, array( 'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'VND', 'VUV', 'XAF', 'XOF', 'XPF' ) );
		} // End is_zero_decimal_currency

		/**
		 * Get metadata attributes to send to Stripe
		 */
		private function get_metadata( $fields, $params ) {
			$fields   = explode( ',', $fields );
			$metadata = array();
			foreach ( $fields as $item ) {
				$key = trim( $item );
				if ( $key && isset( $params[ $key ] ) ) {
					$metadata[ $key ] = $params[ $key ];
				}
			}
			return $metadata;
		} // End get_metadata

		/**
		 * Prevent replacing fields tags
		 */
		private function before_sanitize( $v ) {
			return str_replace( array( '<%', '%>' ), array( '&lt;%', '%&gt;' ), $v );
		} // End before_sanitize

		/**
		 * Prevent replacing fields tags
		 */
		private function after_sanitize( $v ) {
			return str_replace( array( '&lt;%', '%&gt;' ), array( '<%', '%>' ), $v );
		} // End before_sanitize

		/**
		 * log errors
		 */
		private function log( $adarray = array() ) {
			$h   = fopen( dirname( __FILE__ ) . '/logs.txt', 'a' );
			$log = '';
			foreach ( $_REQUEST as $KEY => $VAL ) {
				$log .= $KEY . ': ' . $VAL . "\n";
			}
			foreach ( $adarray as $KEY => $VAL ) {
				$log .= $KEY . ': ' . $VAL . "\n";
			}
			$log .= "================================================\n";
			fwrite( $h, $log );
			fclose( $h );
		} // End log

		/************************ PUBLIC METHODS  *****************************/

		/**
		 * Inserts banks and Check if the Optional is enabled in the form, and inserts radiobutton
		 */
		public function insert_payment_fields( $form_code, $id ) {
			global $wpdb;

			$row = $wpdb->get_row(
				$wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . $this->form_table . ' WHERE formid=%d', $id )
			);

			if ( empty( $row ) || ! $row->enabled || strpos( $form_code, 'vt="' . $this->addonID . '"' ) !== false ) {
				return $form_code;
			}

			$settings = $this->initialize_settings( $row->settings );

			// output radio-buttons here
			$form_code = preg_replace( '/<!--addons-payment-options-->/i', '<!--addons-payment-options--><div><input type="radio" name="bccf_payment_option_paypal" id="cffaddonidtp' . esc_attr( $id ) . '" vt="' . esc_attr( $this->addonID ) . '" value="' . esc_attr( $this->addonID ) . '" checked> ' . esc_html__( ( ! empty( $settings['enable_option_yes'] ) ? $settings['enable_option_yes'] : $this->default_pay_label ), 'calculated-fields-form' ) . '</div>', $form_code );

			if ( ( 2 == $row->enabled || 4 == $row->enabled ) && ! strpos( $form_code, 'bccf_payment_option_paypal" vt="0' ) ) {
				$form_code = preg_replace( '/<!--addons-payment-options-->/i', '<!--addons-payment-options--><div><input type="radio" name="bccf_payment_option_paypal" vt="0" value="0"> ' . esc_html__( $this->_cpcff_main->get_form( $id )->get_option( 'enable_paypal_option_no', CP_CALCULATEDFIELDSF_PAYPAL_OPTION_NO ), 'calculated-fields-form' ) . '</div>', $form_code );
			}

			if ( substr_count( $form_code, 'name="bccf_payment_option_paypal"' ) > 1 ) {
				$form_code = str_replace( 'id="field-c0" style="display:none">', 'id="field-c0">', $form_code );
			}

			return $form_code;
		} // End insert_payment_fields

		/**
		 * Redirect to Stripe to proceed with the payment
		 */
		public function stripe_redirect( $params ) {
			global $wpdb;

			$row = $wpdb->get_row(
				$wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . $this->form_table . ' WHERE formid=%d', $params['formid'] )
			);

			$payment_option = ( isset( $_POST['bccf_payment_option_paypal'] ) ? sanitize_text_field( wp_unslash( $_POST['bccf_payment_option_paypal'] ) ) : $this->addonID );

			if (
				empty( $row ) ||
				! $row->enabled ||
				$payment_option != $this->addonID ||
				empty( $params['final_price'] ) ||
				! is_numeric( $params['final_price'] ) ||
				! ceil( $params['final_price'] * 100 )
			) {
				return;
			}

			$settings = $this->initialize_settings( $row->settings );

			$form_obj = CPCFF_SUBMISSIONS::get_form( $params['itemnumber'] );
			// Send mails before payment
			if ( $form_obj->get_option( 'paypal_notiemails', '0' ) == '1' ) {
				$this->_cpcff_main->send_mails( $params['itemnumber'] );
			}

			if ( ! class_exists( '\Stripe\Stripe' ) ) {
				require_once dirname( __FILE__ ) . '/stripe-php.addon/init.php';
			}

			\Stripe\Stripe::setApiKey( empty( $settings['stripe_mode'] ) ? $settings['stripe_testsecretkey'] : $settings['stripe_secretkey'] );

			if ( preg_match( '/fieldname\d+/i', $settings['frequency'] ) && ! empty( $params[ $settings['frequency'] ] ) ) {
				$settings['frequency'] = strtolower( sanitize_text_field( $params[ $settings['frequency'] ] ) );
			}

			$currency = $form_obj->get_option( 'currency', CP_CALCULATEDFIELDSF_DEFAULT_CURRENCY );
			$amount   = ceil( $this->fix_price( $params['final_price'], $currency ) );

			// Stripe common settings
			$stripe_arguments = array(
				'success_url'                => CPCFF_AUXILIARY::site_url() . '/?k=1&cff_formid=' . $params['formid'] . '&itemnumber=' . $params['itemnumber'] . '&cff_stripe_checkout_sessionid={CHECKOUT_SESSION_ID}',
				'cancel_url'                 => isset( $_POST['cp_ref_page'] ) ? sanitize_text_field( wp_unslash( $_POST['cp_ref_page'] ) ) : CPCFF_AUXILIARY::site_url(),
				'locale'                     => $settings['stripe_language'],
				'billing_address_collection' => empty( $settings['askbilling'] ) ? 'auto' : 'required',
				'line_items'                 => array(
					array(
						'quantity'   => 1,
						'price_data' => array(
							'currency'     => $currency,
							'unit_amount'  => $amount,
							'product_data' => array(),
						),
					),
				),
			);

			if ( ! empty( $settings['stripe_image'] ) ) {
				$stripe_arguments['line_items'][0]['price_data']['product_data']['images'] = array( $settings['stripe_image'] );
			}

			if ( ! empty( $settings['tax_id_collection'] ) ) {
				$stripe_arguments['tax_id_collection'] = array( 'enabled' => true );
			}

			if ( ! empty( $settings['automatic_tax'] ) ) {
				$stripe_arguments['automatic_tax']                               = array( 'enabled' => true );
				$stripe_arguments['line_items'][0]['price_data']['tax_behavior'] = 'exclusive';
			}

			if ( in_array( $settings['frequency'], array( 'day', 'week', 'month', 'year' ) ) ) {
				// RECURRING PAYMENT

				$stripe_arguments['mode'] = 'subscription';
				$product_item_name        = $settings['planname'];

				$stripe_arguments['subscription_data'] = array();
				if ( ! empty( $settings['trialdays'] ) ) {
					$stripe_arguments['subscription_data']['trial_period_days'] = ceil( $settings['trialdays'] );
				}

				$stripe_arguments['line_items'][0]['price_data']['recurring'] = array( 'interval' => $settings['frequency'] );

				if ( ! empty( $settings['times'] ) ) {
					if ( -1 == $settings['times'] ) {
						if (
							preg_match( '/fieldname\d+/i', $settings['times_field'] ) &&
							! empty( $params[ $settings['times_field'] ] ) &&
							is_numeric( $params[ $settings['times_field'] ] )
						) {
							$times = ceil( $params[ $settings['times_field'] ] );
						}
					} else {
						$times = ceil( $settings['times'] );
					}

					if ( ! empty( $times ) ) {
						switch ( $settings['frequency'] ) {
							case 'day':
								$increment = 24 * 60 * 60;
								break;
							case 'week':
								$increment = 7 * 24 * 60 * 60;
								break;
							case 'month':
								$increment = 30 * 24 * 60 * 60;
								break;
							case 'year':
								$increment = 365 * 24 * 60 * 60;
								break;
						}

						$cancel_at                        = time() + $times * $increment + $settings['trialdays'] * 24 * 60 * 60;
						$stripe_arguments['success_url'] .= '&cancel_at=' . $cancel_at;
					}
				}
			} else {
				// ONETIME PAYMENT

				$stripe_arguments['mode'] = 'payment';
				$product_item_name        = $form_obj->get_option( 'paypal_product_name', CP_CALCULATEDFIELDSF_DEFAULT_PRODUCT_NAME );

			}

			foreach ( $params as $item => $value ) {
				$product_item_name = str_replace(
					'<%' . $item . '%>',
					is_array( $value ) ? implode( ', ', $value ) : $value,
					$product_item_name
				);
			}

			if ( empty( $product_item_name ) ) {
				$product_item_name = $params['itemnumber'];
			}

			$product_item_name = sanitize_text_field( $product_item_name );

			/* $stripe_arguments['line_items'][0]['description'] = $product_item_name; */
			$stripe_arguments['line_items'][0]['price_data']['product_data']['name'] = $product_item_name;
			$stripe_arguments['client_reference_id']                                 = $product_item_name;

			$notifyto = explode( ',', $form_obj->get_option( 'cu_user_email_field', '' ) );
			if ( ! empty( $notifyto ) && ! empty( $params[ $notifyto[0] ] ) ) {
				$stripe_arguments['customer_email'] = $params[ $notifyto[0] ];
			}

			$metadata                     = $this->get_metadata( $settings['stripe_metadata'], $params );
			$metadata['Submission ID']    = $params['itemnumber'];
			$stripe_arguments['metadata'] = $metadata;

			try {
				$session = \Stripe\Checkout\Session::create( $stripe_arguments );
			} catch ( Exception $e ) {
				echo wp_kses_post( $e->getMessage() );
				exit;
			}
			?>
<html><head><title>Redirecting to Stripe Checkout</title><body>
<script src="https://js.stripe.com/v3"></script>
<script> var stripe = Stripe('<?php echo esc_js( empty( $settings['stripe_mode'] ) ? $settings['stripe_testkey'] : $settings['stripe_key'] ); ?>'); stripe.redirectToCheckout({sessionId: '<?php echo esc_js( $session->id ); ?>'}).then(function (result) {alert(result.error.message);});</script>
</body>
</html>
			<?php
			exit;
		} // End stripe_redirect

		/**
		 * script process payment
		 */
		public function stripe_check_payment() {
			global $wpdb;

			if (
				! empty( $_REQUEST['cff_stripe_checkout_sessionid'] ) &&
				'' != ( $sessionid = sanitize_text_field( wp_unslash( $_REQUEST['cff_stripe_checkout_sessionid'] ) ) ) &&
				! empty( $_REQUEST['cff_formid'] ) &&
				is_numeric( $_REQUEST['cff_formid'] ) &&
				! empty( $_REQUEST['itemnumber'] ) &&
				is_numeric( $_REQUEST['itemnumber'] )
			) {
				$formid     = intval( $_REQUEST['cff_formid'] );
				$itemnumber = intval( $_REQUEST['itemnumber'] );
				$row        = $wpdb->get_row(
					$wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . $this->form_table . ' WHERE formid=%d', $formid )
				);

				if ( ! empty( $row ) ) {
					try {
						$settings = $this->initialize_settings( $row->settings );
						if ( ! class_exists( '\Stripe\Stripe' ) ) {
							require_once dirname( __FILE__ ) . '/stripe-php.addon/init.php';
						}
						$stripe_secretkey = empty( $settings['stripe_mode'] ) ? $settings['stripe_testsecretkey'] : $settings['stripe_secretkey'];
						\Stripe\Stripe::setApiKey( $stripe_secretkey );
						$session = \Stripe\Checkout\Session::retrieve( $sessionid );

						if ( ! empty( $session->payment_intent ) ) {
							$object = \Stripe\PaymentIntent::retrieve( $session->payment_intent );
						} elseif ( ! empty( $session->subscription ) ) {
							$object = \Stripe\Subscription::retrieve( $session->subscription );
							if (
								! empty( $_REQUEST['cancel_at'] ) &&
								is_numeric( $_REQUEST['cancel_at'] )
							) {
								$stripe    = new \Stripe\StripeClient( $stripe_secretkey );
								$cancel_at = intval( $_REQUEST['cancel_at'] );
								$stripe->subscriptions->update(
									$session->subscription,
									array( 'cancel_at' => $cancel_at )
								);
							}
						}

						if ( ! empty( $object ) && 'succeeded' == $object->status || 'active' == $object->status || 'trialing' == $object->status ) {
							$submission = CPCFF_SUBMISSIONS::get( $itemnumber );
							if ( empty( $submission ) ) {
								return;
							}
							$params               = $submission->paypal_post;
							$params['itemnumber'] = $itemnumber;

							// mark item as paid
							CPCFF_SUBMISSIONS::update( $itemnumber, array( 'paid' => 1 ) );
							do_action( 'cpcff_payment_processed', $params );

							$form_obj = CPCFF_SUBMISSIONS::get_form( $itemnumber );
							if ( $form_obj->get_option( 'paypal_notiemails', '0' ) != '1' ) {
								$this->_cpcff_main->send_mails( $itemnumber );
							}

							$redirect = true;

							/**
							 * Filters applied to decide if the website should be redirected to the thank you page after submit the form,
							 * pass a boolean as parameter and returns a boolean
							 */
							$redirect = apply_filters( 'cpcff_redirect', $redirect );

							if ( $redirect ) {
								$location = CPCFF_AUXILIARY::replace_params_into_url( $form_obj->get_option( 'fp_return_page', CP_CALCULATEDFIELDSF_DEFAULT_fp_return_page ), $params );
								header( 'Location: ' . $location );
								exit;
							}
						} else {
							print 'Error: Purchase cannot be verified. Please contact the seller.';
							exit;
						}
					} catch ( Exception $e ) {
						print 'Error: Purchase cannot be verified. Please contact the seller. Error code: <strong>' . wp_kses_post( $e->getMessage() ) . '</strong>';
						exit;
					}
				}
				print 'Error: Invalid data. Please contact the seller.';
				exit;
			}
		} // End stripe_check_payment

		/**
		 *  Delete the form from the addon's table
		 */
		public function delete_form( $formid ) {
			global $wpdb;
			$wpdb->delete( $wpdb->prefix . $this->form_table, array( 'formid' => $formid ), '%d' );
		} // delete_form

		/**
		 *  Clone the form's row
		 */
		public function clone_form( $original_form_id, $new_form_id ) {
			 global $wpdb;

			$form_row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . $this->form_table . ' WHERE formid=%d', $original_form_id ), ARRAY_A );

			if ( ! empty( $form_row ) ) {
				unset( $form_row['id'] );
				$form_row['formid'] = $new_form_id;
				$wpdb->insert( $wpdb->prefix . $this->form_table, $form_row );
			}
		} // End clone_form

		/**
		 *  It is called when the form is exported to export the addons data too.
		 *  Receive an array with the other addons data, and the form's id for filtering.
		 */
		public function export_form( $addons_array, $formid ) {
			global $wpdb;
			$form_row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . $this->form_table . ' WHERE formid=%d', $formid ), ARRAY_A );

			if ( ! empty( $row ) ) {
				unset( $row['id'] );
				unset( $row['formid'] );
				$addons_array[ $this->addonID ] = $row;
			}
			return $addons_array;
		} // End export_form

		/**
		 *  It is called when the form is imported to import the addons data too.
		 *  Receive an array with all the addons data, and the new form's id.
		 */
		public function import_form( $addons_array, $formid ) {
			global $wpdb;
			if ( isset( $addons_array[ $this->addonID ] ) ) {
				$addons_array[ $this->addonID ]['formid'] = $formid;
				$wpdb->insert(
					$wpdb->prefix . $this->form_table,
					$addons_array[ $this->addonID ]
				);
			}
		} // End import_form

	} // End Class

	// Main add-on code
	$CPCFF_Stripe_Checkout_obj = new CPCFF_Stripe_Checkout();

	CPCFF_ADDONS::add( $CPCFF_Stripe_Checkout_obj );
}
