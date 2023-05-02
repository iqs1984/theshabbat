<?php
/*
....
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPCFF_VerificationCode' ) )
{
    class CPCFF_VerificationCode extends CPCFF_BaseAddon
    {
		static public $category = 'Extending Features';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-verificationcode-20210727";
		protected $name = "CFF - Verification Code (Experimental)";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#verification-code-addon';

		public function get_addon_form_settings( $form_id )
		{
			global $wpdb;

			// Insertion in database
			if( !empty( $_REQUEST[ 'cpcff_verificationcode_addon' ] ) )
			{
				$enabled = isset($_REQUEST['cpcff_verificationcode_enabled']) ? 1: 0;
                $settings = array(
                    'email_field'       => stripcslashes(sanitize_text_field($_REQUEST['cpcff_verificationcode_email_field'])),
                    'email_field_required' => isset($_REQUEST['cpcff_verificationcode_email_field_required']) ? 1 : 0,
                    'dialog_label'      => stripcslashes($this->_sanitize($_REQUEST['cpcff_verificationcode_label'])),
                    'instructions_text' => stripcslashes(CPCFF_AUXILIARY::sanitize($_REQUEST['cpcff_verificationcode_instructions_text'])),
                    'verify_button'     => stripcslashes(sanitize_text_field($_REQUEST['cpcff_verificationcode_verify_button'])),
                    'resend_button'     => stripcslashes(sanitize_text_field($_REQUEST['cpcff_verificationcode_resend_button'])),
                    'sent_text'         => stripcslashes(CPCFF_AUXILIARY::sanitize($_REQUEST['cpcff_verificationcode_sent_text'])),
                    'required_email_text'   => stripcslashes(CPCFF_AUXILIARY::sanitize($_REQUEST['cpcff_verificationcode_required_email_text'])),
                    'required_code_text'    => stripcslashes(CPCFF_AUXILIARY::sanitize($_REQUEST['cpcff_verificationcode_required_code_text'])),
                    'invalid_text'      => stripcslashes(CPCFF_AUXILIARY::sanitize($_REQUEST['cpcff_verificationcode_invalid_text'])),
                    'expired_text'      => stripcslashes(CPCFF_AUXILIARY::sanitize($_REQUEST['cpcff_verificationcode_expired_text'])),
                    'email_subject'     => stripcslashes(sanitize_text_field($_REQUEST['cpcff_verificationcode_email_subject'])),
                    'email_message'     => stripcslashes($_REQUEST['cpcff_verificationcode_email_message'])
                );

				// Refresh database
				$wpdb->delete( $wpdb->prefix.$this->table, array( 'formid' => $form_id ), array( '%d' ) );
				$wpdb->insert( 	$wpdb->prefix.$this->table,
								array(
									'formid' 	=> $form_id,
									'enabled'	=> $enabled,
									'settings'	=> serialize( $settings )
								),
								array( '%d', '%d', '%s' )
							);
			}

			// Read from database and display the fields.
			$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.$this->table." WHERE formid=%d", $form_id));

            // Default values
            $enabled = 0;
			$settings = array(
                'email_field'           => '',
                'email_field_required'  => 1,
                'dialog_label'          => 'We have sent the verification code to your email',
                'instructions_text'     => 'The verification code will be valid for 5 minutes',
                'verify_button'         => 'Verify',
                'resend_button'         => 'Resend Code',
                'sent_text'             => 'We have sent the verification code to your email. Please check your inbox and junk folder',
                'required_email_text'   => 'The email address is required',
                'required_code_text'    => 'The verification code is required',
                'invalid_text'          => 'Invalid verification code',
                'expired_text'          => 'The verification code has expired, please press the resend button',
                'email_subject'         => 'Verification code',
                'email_message'         => "<p>Verification code: <b><%code%></b></p>\n<p>Copy the verification code and paste it into the form's verification dialog box.</p>"
            );

			if( !empty($row) )
			{
				$enabled = $row->enabled;
                $stored_settings = @unserialize($row->settings);
                if($stored_settings) $settings = array_merge($settings, $stored_settings);
            }

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<input type="hidden" name="cpcff_verificationcode_addon" value="1" />
			<div id="metabox_verificationcode_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_verificationcode_addon_form_settings' ) ); ?>" >
				<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
				<div class="inside">
					<p><?php esc_html_e('When the email address entered through the form matches the email address of the registered user, there is no reason to send the verification code.', 'calculated-fields-form'); ?></p>
					<table class="form-table">
						<tr valign="top">
							<th scope="row"><?php _e('Enable the verification code', 'calculated-fields-form');?></th>
							<td><input type="checkbox" name="cpcff_verificationcode_enabled" <?php print($enabled ? 'CHECKED' : ''); ?> aria-label="<?php esc_attr_e('Enabling', 'calculated-fields-form'); ?>" /></td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e('Email field', 'calculated-fields-form');?></th>
							<td><input type="text" name="cpcff_verificationcode_email_field" value="<?php echo esc_attr($settings['email_field']); ?>" class="width75" placeholder="fieldname#" aria-label="<?php esc_attr_e('Email field', 'calculated-fields-form'); ?>" /></td>
						</tr>

                        <tr valign="top">
							<th scope="row"><?php _e('Make field required', 'calculated-fields-form');?></th>
							<td><input type="checkbox" name="cpcff_verificationcode_email_field_required" <?php echo !empty($settings['email_field_required']) ? 'CHECKED' : ''; ?> aria-label="<?php esc_attr_e('Make field required', 'calculated-fields-form'); ?>" /></td>
						</tr>

                        <tr><td colspan="2"><h3 style="padding:0;margin:0;"><?php _e('Verification dialog', 'calculated-fields-form'); ?></h3><hr></td></tr>
						<tr valign="top">
							<th scope="row"><?php _e('Label text', 'calculated-fields-form');?></th>
							<td><input type="text" name="cpcff_verificationcode_label" value="<?php print esc_attr($settings['dialog_label']); ?>" class="width75" aria-label="<?php esc_attr_e('Label text', 'calculated-fields-form'); ?>" /><br>
                            <i><?php _e('You can include the email in the dialog label by entering its field\'s tag. Ex. &lt;%fieldname1%&gt;', 'calculated-fields-form'); ?></i>
                            </td>
						</tr>
                        <tr valign="top">
							<th scope="row"><?php _e('Instructions text', 'calculated-fields-form');?></th>
							<td><textarea name="cpcff_verificationcode_instructions_text" class="width75" rows="5" aria-label="<?php esc_attr_e('Instructions text', 'calculated-fields-form'); ?>" style="resize:vertical;"><?php print esc_textarea($settings['instructions_text']); ?></textarea></td>
						</tr>
                        <tr valign="top">
							<th scope="row"><?php _e('Verify button text', 'calculated-fields-form');?></th>
							<td><input type="text" name="cpcff_verificationcode_verify_button" value="<?php print esc_attr($settings['verify_button']); ?>" class="width75" aria-label="<?php esc_attr_e('Verify button text', 'calculated-fields-form'); ?>" /></td>
						</tr>
                        <tr valign="top">
							<th scope="row"><?php _e('Resend button text', 'calculated-fields-form');?></th>
							<td><input type="text" name="cpcff_verificationcode_resend_button" value="<?php print esc_attr($settings['resend_button']); ?>" class="width75" aria-label="<?php esc_attr_e('Resend button text', 'calculated-fields-form'); ?>" /></td>
						</tr>

                        <tr><td colspan="2"><h3 style="padding:0;margin:0;"><?php _e('Verification email', 'calculated-fields-form'); ?></h3><hr></td></tr>
                        <tr valign="top">
							<th scope="row"><?php _e('Email subject', 'calculated-fields-form');?></th>
							<td><input type="text" name="cpcff_verificationcode_email_subject" value="<?php print esc_attr($settings['email_subject']); ?>" class="width75" aria-label="<?php esc_attr_e('Email subject', 'calculated-fields-form'); ?>" /></td>
						</tr>
                        <tr valign="top">
							<th scope="row"><?php _e('Email message', 'calculated-fields-form');?></th>
							<td><textarea name="cpcff_verificationcode_email_message" class="width75" rows="5" aria-label="<?php esc_attr_e('Email message', 'calculated-fields-form'); ?>" style="resize:vertical;"><?php print esc_textarea($settings['email_message']); ?></textarea><br>
                            <em style="font-size:11px;"><?php _e('The <b><%code%></b> tag would be replaced by the verification code.', 'calculated-fields-form'); ?></em>
                            </td>
						</tr>

                        <tr><td colspan="2"><h3 style="padding:0;margin:0;"><?php _e('Texts', 'calculated-fields-form'); ?></h3><hr></td></tr>
                        <tr valign="top">
							<th scope="row"><?php _e('Sent code text', 'calculated-fields-form');?></th>
							<td><textarea name="cpcff_verificationcode_sent_text" rows="5" class="width75" aria-label="<?php esc_attr_e('Sent code text', 'calculated-fields-form'); ?>" style="resize:vertical;"><?php print esc_textarea($settings['sent_text']); ?></textarea></td>
						</tr>
                        <tr valign="top">
							<th scope="row"><?php _e('Required email text', 'calculated-fields-form');?></th>
							<td><textarea name="cpcff_verificationcode_required_email_text" rows="5" class="width75" aria-label="<?php esc_attr_e('Required email text', 'calculated-fields-form'); ?>" style="resize:vertical;"><?php print esc_textarea($settings['required_email_text']); ?></textarea></td>
						</tr>
                        <tr valign="top">
							<th scope="row"><?php _e('Required code text', 'calculated-fields-form');?></th>
							<td><textarea name="cpcff_verificationcode_required_code_text" rows="5" class="width75" aria-label="<?php esc_attr_e('Required code text', 'calculated-fields-form'); ?>" style="resize:vertical;"><?php print esc_textarea($settings['required_code_text']); ?></textarea></td>
						</tr>
                        <tr valign="top">
							<th scope="row"><?php _e('Invalid code text', 'calculated-fields-form');?></th>
							<td><textarea name="cpcff_verificationcode_invalid_text" rows="5" class="width75" aria-label="<?php esc_attr_e('Invalid code text', 'calculated-fields-form'); ?>" style="resize:vertical;"><?php print esc_textarea($settings['invalid_text']); ?></textarea></td>
						</tr>
                        <tr valign="top">
							<th scope="row"><?php _e('Expired code text', 'calculated-fields-form');?></th>
							<td><textarea name="cpcff_verificationcode_expired_text" rows="5" class="width75" aria-label="<?php esc_attr_e('Expired code text', 'calculated-fields-form'); ?>" style="resize:vertical;"><?php print esc_textarea($settings['expired_text']); ?></textarea></td>
						</tr>
					</table>
                    <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
				</div>
			</div>
			<?php
		}

		/************************ ADDON CODE *****************************/
        /************************ ATTRIBUTES *****************************/

		private $table = 'cp_calculated_fields_form_verification_code';
		private $javascript_code = '';

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = __("The add-on sends a verification code to the user's email and blocks the submission of the form until a valid code is entered", 'calculated-fields-form');

            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;

			if(isset($_POST['cff-verification-code-action']))
            {
                add_action('init', array(&$this, 'init'));
            }
            else
            {
                // Verifies the code in the submission process
                add_action( 'cpcff_process_data_before_insert', array( $this, 'code_verification_on_submission' ), 10, 3 );

                // Checks the form's settings and generate the javascript code
                add_filter( 'cpcff_the_form', array( &$this, 'integration' ), 10, 2 );

                // Validate code
                add_action( 'cpcff_script_after_validation', array( &$this, 'validation_code'), 10, 2 );

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
            }
		} // End __construct

        /************************ PROTECTED METHODS *****************************/

		/**
         * Create the database tables
         */
        protected function update_database()
		{
			global $wpdb;
			$charset_collate = $wpdb->get_charset_collate();
			$db_queries = array( "CREATE TABLE ".$wpdb->prefix.$this->table." (
					formid INT NOT NULL,
					enabled TINYINT DEFAULT 0 NOT NULL ,
					settings MEDIUMTEXT,
					PRIMARY KEY (formid)
				) $charset_collate;"
			);
			$this->_run_update_database($db_queries);
		} // End update_database

        /************************ PRIVATE METHODS *****************************/

        private function _sanitize($v)
        {
            $v = preg_replace('/'.preg_quote('<%').'(fieldname\d+)'.preg_quote('%>').'/', '&lt;%$1%&gt;', $v);
            $v = sanitize_text_field($v);
            $v = preg_replace('/'.preg_quote('&lt;%').'(fieldname\d+)'.preg_quote('%&gt;').'/', '<%$1%>', $v);
            return $v;
        }

        private function _get_settings($formid)
        {
            global $wpdb;
            $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.$this->table." WHERE formid=%d", $formid));
            if(
                !empty($row) &&
                $row->enabled
            )
            {
                $settings = @unserialize($row->settings);
				if($settings !== false)
				{
					$current_user_email = wp_get_current_user()->user_email;
					if($current_user_email !== false) $settings['current_user_email'] = $current_user_email;
					if(!empty($settings['email_field'])) return $settings;
				}
            }
            return false;
        } // End _get_settings

        private function _verify($email, $settings, $verification_code)
        {
            if(empty($email)) return $settings['required_email_text'];

			if(!empty($settings['current_user_email']) && strtolower($settings['current_user_email']) == strtolower($email))
			return 'ok';

            $code = get_transient('cff-verification-code-'.$email);

            if(empty($verification_code)) return $settings['required_code_text'];
            elseif($code === false) return $settings['expired_text'];
            elseif($code != $verification_code) return $settings['invalid_text'];
            return 'ok';

        } // End _verify

        private function _code_verification()
        {
            $response = '';
            if(
                isset($_POST['cff-verification-code-email']) &&
                !empty($_POST['cff-verification-code-form'])
            )
            {
                $email  = sanitize_email($_POST['cff-verification-code-email']);
                $formid = @intval($_POST['cff-verification-code-form']);
                $code   = sanitize_text_field(
                    isset($_POST['cff-verification-code-code'])
                    ? $_POST['cff-verification-code-code']
                    : ''
                );
                $settings = $this->_get_settings($formid);

                if($settings) return $this->_verify($email, $settings, $code);
            }
            return $response;
        } // End _code_verification

        public function _send_code()
        {
            if(
                !empty($_POST['cff-verification-code-email']) &&
                !empty($_POST['cff-verification-code-form'])
            )
            {
                $email = sanitize_email($_POST['cff-verification-code-email']);
                $formid = @intval($_POST['cff-verification-code-form']);
                $settings = $this->_get_settings($formid);

                if($settings)
                {
                    $code = get_transient('cff-verification-code-'.$email);
                    if($code === false)
                    {
                        $code = time();
                    }
                    set_transient('cff-verification-code-'.$email, $code, 5*60);

                    $message = $settings['email_message'];
                    if(strpos($message, '<%code%>') === false) $message .= '<p><b>'.$code.'</b></p>';
                    else $message = str_replace('<%code%>', $code, $message);

                    $headers = array(
                        "Content-Type: text/html; charset=utf-8",
                        "X-Mailer: PHP/" . phpversion()
                    );

                    $cpcff_main = CPCFF_MAIN::instance();
					$form_obj = $cpcff_main->get_form($formid);
                    $from = $form_obj->get_option('fp_from_email', '');
                    if(!empty($from)) $headers[] = "From: ".$from;

                    wp_mail(
                        $email,
                        $settings['email_subject'],
                        $message,
                        $headers
                    );

                    print $settings['sent_text'];
                }
            }
        } // End _send_code

        /************************ PUBLIC METHODS  *****************************/

        public function init()
        {
            switch($_POST['cff-verification-code-action'])
            {
                case 'cff-verification-code-verify':
                    print $this->_code_verification();
                break;
                case 'cff-verification-code-send':
                    $this->_send_code();
                break;
            }
            remove_all_actions('shutdown');
            exit;
        } // End init

        public function code_verification_on_submission(&$params, &$str, $fields)
        {
            $formid     = $params['formid'];
            $settings   = $this->_get_settings($formid);
            if($settings)
            {
                $email_field = $settings['email_field'];
                $email = !empty($params[$email_field]) ? $params[$email_field] : '';

                if(
                    empty($email) &&
                    isset($settings['email_field_required']) &&
                    $settings['email_field_required'] == 0
                ) return;

                $code   = sanitize_text_field(
                    isset($_POST['cff-verification-code-input'])
                    ? $_POST['cff-verification-code-input']
                    : ''
                );

                $result = $this->_verify($email, $settings, $code);
                if($result != 'ok')
                {
                    print $result;
                    remove_all_actions('shutdown');
                    exit;
                }
            }
        } // End code_verification_on_submission

        public function validation_code( $sequence, $formid )
        {
            if(($settings = $this->_get_settings($formid)) != false)
            {
                $c   = CPCFF_MAIN::$form_counter;
                $url = CPCFF_AUXILIARY::site_url(true);
                if(strpos($url, '?') === false) $url = rtrim($url, '/').'/';
            ?>
                if(
					typeof validation_rules['<?php print esc_js($this->addonID); ?>'] == 'undefined' ||
					validation_rules['<?php print esc_js($this->addonID); ?>'] == false
				)
				{
					validation_rules['<?php print esc_js($this->addonID); ?>'] = false;
                    cff_open_verification_code_dialog(
                    <?php print $c; ?>,
                    "<?php echo $url ?>",
                    function(v){
                        validation_rules['<?php print esc_js($this->addonID); ?>'] = v;
                        processing_form();
                    });
                    return;
                }
            <?php
            }
        } // End validation_code

		/**
		 * Checks the form's settings and generates the javascript code
		 */
		public function integration( $content, $formid )
		{
            global $wpdb;

            $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.$this->table." WHERE formid=%d", $formid));
            if(
                !empty($row) &&
                $row->enabled
            )
            {
                $c = CPCFF_MAIN::$form_counter;
				$settings = $this->_get_settings($formid);
                if($settings)
                {
                    if(!empty($settings['email_field']))
                    {
                        $content .= '<script type=text/javascript>
                        if(typeof cff_verification_code_settings == "undefined") cff_verification_code_settings = {};
                        cff_verification_code_settings['.$c.'] = '.json_encode($settings).';
                        cff_verification_code_settings['.$c.']["formid"] = '.$formid.';';
						if(!empty($settings['current_user_email']))
							$content .= 'cff_current_user_email="'.esc_js(strtolower($settings['current_user_email'])).'";';

                        $content .= '</script>';
                        wp_enqueue_style('cff-verification-code-css', plugins_url('/verificationcode.addon/styles.css', __FILE__));
                        wp_enqueue_script('cff-verification-code-js', plugins_url('/verificationcode.addon/scripts.js', __FILE__));
                    }
                }
            }

			return $content;
		} // End integration

		/**
		 *	Delete the form from the addon's table
		 */
        public function delete_form( $formid)
		{
			global $wpdb;
			$wpdb->delete( $wpdb->prefix.$this->table, array('formid' => $formid), '%d' );
		} // delete_form

		/**
		 *	Clone the form's row
		 */
		public function clone_form( $original_form_id, $new_form_id )
		{
			global $wpdb;

			$form_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->table." WHERE formid=%d", $original_form_id ), ARRAY_A);

			if(!empty($form_row))
			{
				$form_row["formid"] = $new_form_id;
				$wpdb->insert( $wpdb->prefix.$this->table, $form_row);
			}
		} // End clone_form

		/**
		 *	It is called when the form is exported to export the addons data too.
		 *  Receive an array with the other addons data, and the form's id for filtering.
		 */
		public function export_form($addons_array, $formid)
		{
			global $wpdb;
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->table." WHERE formid=%d", $formid ), ARRAY_A );
			if(!empty( $row ))
			{
				unset($row['formid']);
				$addons_array[ $this->addonID ] = $row;
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
				$addons_array[$this->addonID]['formid'] = $formid;
				$wpdb->insert(
					$wpdb->prefix.$this->table,
					$addons_array[$this->addonID]
				);
			}
		} // End import_form

	} // End Class

    // Main add-on code
    $cpcff_verificationcode_obj = new CPCFF_VerificationCode();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cpcff_verificationcode_obj);
}
?>