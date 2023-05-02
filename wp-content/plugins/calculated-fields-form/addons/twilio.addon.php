<?php
/*
....
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPCFF_Twilio' ) )
{
    class CPCFF_Twilio extends CPCFF_BaseAddon
    {
		static public $category = 'External Services';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-twilio-20150403";
		protected $name = "CFF - Twilio";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#twilio-addon';

		public function get_addon_form_settings( $form_id )
		{
			global $wpdb;

			// Insertion in database
			if( isset( $_REQUEST[ 'cpcff_twilio' ] ) )
			{
				$wpdb->delete( $wpdb->prefix.$this->form_twilio_table, array( 'formid' => $form_id ), array( '%d' ) );

				$active = ($_REQUEST['cpcff_twilio_active']) ? 1 : 0;

				$settings = array();
				if(isset($_REQUEST['cpcff_twilio_sid'])) $settings['sid'] = trim($_REQUEST['cpcff_twilio_sid']);
				if(isset($_REQUEST['cpcff_twilio_auth'])) $settings['auth'] = trim($_REQUEST['cpcff_twilio_auth']);
				if(isset($_REQUEST['cpcff_twilio_from'])) $settings['from'] = trim($_REQUEST['cpcff_twilio_from']);
				if(isset($_REQUEST['cpcff_twilio_to_website'])) $settings['to_website'] = trim($_REQUEST['cpcff_twilio_to_website']);
				if(isset($_REQUEST['cpcff_twilio_to_users'])) $settings['to_users'] = trim($_REQUEST['cpcff_twilio_to_users']);
				if(isset($_REQUEST['cpcff_twilio_mssg_website'])) $settings['mssg_website'] = trim($_REQUEST['cpcff_twilio_mssg_website']);
				if(isset($_REQUEST['cpcff_twilio_mssg_users'])) $settings['mssg_users'] = trim($_REQUEST['cpcff_twilio_mssg_users']);

				$wpdb->insert(
					$wpdb->prefix.$this->form_twilio_table,
					array(
						'formid' => $form_id,
						'active' => $active,
						'data'	 => serialize($settings)
					),
					array( '%d', '%d', '%s' )
				);
			}

			$row = $wpdb->get_row(
						$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_twilio_table." WHERE formid=%d", $form_id )
					);

			if(!empty($row))
			{
				$active = $row->active;
				$settings = unserialize($row->data);
			}

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<div id="metabox_twilio_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_twilio_addon_form_settings' ) ); ?>" >
				<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
				<div class="inside">
					<table style="width:100%;" cellspacing="3">
						<tr>
							<td style="white-space:nowrap;width:100px;"><?php _e('Twilio enabled','calculated-fields-form'); ?>:</td>
							<td><input type="checkbox" name="cpcff_twilio_active" <?php print (isset($settings) && !empty($active)) ? 'CHECKED' : ''; ?> /></td>
						</tr>
						<tr>
							<td style="white-space:nowrap;width:100px;"><?php _e('Account SID','calculated-fields-form'); ?>:</td>
							<td><input type="text" name="cpcff_twilio_sid" value="<?php print esc_attr((isset($settings) && !empty($settings['sid'])) ? trim($settings['sid']) : ''); ?>" class="width75" /></td>
						</tr>
						<tr>
							<td style="white-space:nowrap;width:100px;"><?php _e('Authentication Token','calculated-fields-form'); ?>:</td>
							<td><input type="text" name="cpcff_twilio_auth" value="<?php print esc_attr((isset($settings) && !empty($settings['auth'])) ? trim($settings['auth']) : ''); ?>" class="width75" /></td>
						</tr>
						<tr>
							<td style="white-space:nowrap;width:100px;"><?php _e('From Phone Number','calculated-fields-form'); ?>:</td>
							<td><input type="text" name="cpcff_twilio_from" value="<?php print esc_attr((isset($settings) && !empty($settings['from'])) ? trim($settings['from']) : ''); ?>" class="width75" /></td>
						</tr>
						<tr><td colspan="2"><hr /></td></tr>
						<tr>
							<td style="white-space:nowrap;width:100px;"><?php _e('To Phone Number','calculated-fields-form'); ?>:</td>
							<td>
								<input type="text" name="cpcff_twilio_to_website" value="<?php print esc_attr((isset($settings) && !empty($settings['to_website'])) ? trim($settings['to_website']) : ''); ?>" class="width75" /><br />
								<i><?php _e('For sending multiple messages, separate the phone numbers by comma symbols.'); ?></i>
							</td>
						</tr>
						<tr>
							<td style="white-space:nowrap;width:100px;" valign="top"><?php _e('Message','calculated-fields-form'); ?>:</td>
							<td>
								<textarea name="cpcff_twilio_mssg_website" class="width75" rows="6" style="resize:both;"><?php print esc_textarea((isset($settings) && !empty($settings['mssg_website'])) ? trim($settings['mssg_website']) : ''); ?></textarea><br />
								<i><?php _e('It is possible to use the same <a href="https://cff.dwbooster.com/documentation#special-tags" target="_blank">special tags</a> than in notifications emails.', 'calculated-fields-form'); ?></i>
							</td>
						</tr>
						<tr><td colspan="2"><hr /></td></tr>
						<tr><td colspan="2"><h2><?php _e('Copy to the users', 'calculated-fields-form'); ?></h2></td></tr>
						<tr>
							<td style="white-space:nowrap;width:100px;"><?php _e('To Phone fields','calculated-fields-form'); ?>:</td>
							<td>
								<input type="text" name="cpcff_twilio_to_users" value="<?php print esc_attr((isset($settings) && !empty($settings['to_users'])) ? trim($settings['to_users']) : ''); ?>" placeholder="fieldname#" class="width75" /><br />
								<i><?php _e('For sending multiple messages, separate the phone fields by comma symbols.'); ?></i>
							</td>
						</tr>
						<tr>
							<td style="white-space:nowrap;width:100px;" valign="top"><?php _e('Message','calculated-fields-form'); ?>:</td>
							<td>
								<textarea name="cpcff_twilio_mssg_users" class="width75" rows="6" style="resize:both;"><?php print esc_textarea((isset($settings) && !empty($settings['mssg_users'])) ? trim($settings['mssg_users']) : ''); ?></textarea><br />
								<i><?php _e('It is possible to use the same <a href="https://cff.dwbooster.com/documentation#special-tags" target="_blank">special tags</a> than in notifications emails.', 'calculated-fields-form'); ?></i>
							</td>
						</tr>
					</table>
                    <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
				</div>
				<input type="hidden" name="cpcff_twilio" value="1" />
			</div>
			<?php
		}

		/************************ ADDON CODE *****************************/
        /************************ ATTRIBUTES *****************************/

		private $form_twilio_table = 'cp_calculated_fields_form_twilio';
		private $_cpcff_main;
		private $_sid;
		private $_auth;
		private $_from;

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->_cpcff_main = CPCFF_MAIN::instance();
			$this->description = __("The add-on allows to send notification messages (SMS) after submit the forms", 'calculated-fields-form');

            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;

			// Export the lead
			add_action( 'cpcff_process_data', array( &$this, 'put_data' ) );

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

        /************************ PROTECTED METHODS *****************************/

		/**
         * Creates the database tables
         */
        protected function update_database()
		{
			global $wpdb;
			$charset_collate = $wpdb->get_charset_collate();
			$db_queries = array( "CREATE TABLE ".$wpdb->prefix.$this->form_twilio_table." (
					formid INT NOT NULL,
					active INT NOT NULL DEFAULT 0,
					data TEXT,
					UNIQUE KEY formid (formid)
				) $charset_collate;"
			);
			$this->_run_update_database($db_queries);
		} // End update_database

        /************************ PRIVATE METHODS *****************************/

		/**
		 * Checks the format of phone's number and transform it is needed
		 */
		private function _phone_number($phone)
		{
			$phone = preg_replace('/[^\d\+]/', '', $phone);
			$phone = preg_replace('/^00/', '+', $phone);
			if(preg_match('/^\+?[1-9]\d{1,14}$/', $phone)) return $phone;
			return false;
		} // End _validate_phone_number

		/**
		 * Cuts the message to 160 characters
		 */
		private function _message($mssg)
		{
			return substr($mssg, 0, 160);
		} // End _message

		/**
		 * Sends the SMS using twilio
		 */
		private function _send_sms($to, $mssg)
		{
			$to = explode(',', $to);
			$mssg = $this->_message($mssg);
			foreach($to as $phone)
			{
				$phone = $this->_phone_number($phone);
				if(!empty($phone))
				{
					$params = array(
						'From' => $this->_from,
						'To' => $phone,
						'Body' => $mssg
					);
					$args = array(
						'headers' 	=> array(
							'Authorization' => 'Basic '. base64_encode( $this->_sid.':'.$this->_auth )
						),
						'body' 		=> $params,
						'timeout' 	=> 45,
						'sslverify'	=> false,
					);

					$request = wp_remote_post(
						'https://api.twilio.com/2010-04-01/Accounts/'.$this->_sid.'/Messages.json',
						$args
					);

					if(!is_wp_error($request))
					{
						$response_body = wp_remote_retrieve_body($request);
						$body = json_decode($response_body);
						if(!empty($body) && !empty($body->error_code) )
						{
							if(!empty($body->error_message)) error_log( print_r( $body->error_message, true ) );
						}
					}
					else
					{
						error_log( print_r( $request->get_error_message(), true ) );
					}
				}
			}
		} // End send_sms

		/************************ PUBLIC METHODS  *****************************/

		/**
         * Send message to Twilio
         */
        public function	put_data( $params )
		{
			global $wpdb;

			$form_id = @intval( $params[ 'formid' ] );
			$itemnumber = @intval( $params[ 'itemnumber' ] );

			if( $form_id && $itemnumber )
			{
				$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_twilio_table." WHERE formid=%d", $form_id ) );

				if(!empty($row) && ($row->active == '1' || $row->active == 1))
				{
					// Clean data
					foreach( $params as $key => $val )
					{
						try
						{
							$tmp = @json_decode( $val );
							if( !empty($tmp) ) $params[ $key ] = $tmp;
						}
						catch( Exception $err )
						{

						}
					}

					if(
						!empty($row->data) &&
						($data = unserialize($row->data)) !== false
					)
					{
						$this->_sid   = (isset($data['sid']))  ? trim($data['sid'])  : '';
						$this->_auth  = (isset($data['auth'])) ? trim($data['auth']) : '';
						$this->_from  = (isset($data['from'])) ? trim($data['from']) : '';

						if(!empty($this->_sid) && !empty($this->_auth) && !empty($this->_from))
						{
							$submission_obj = CPCFF_SUBMISSIONS::get($itemnumber);
							$fields 		= $this->_cpcff_main->get_form($form_id)->get_fields();
							$fields['ipaddr'] = $submission_obj->ipaddr;

							$to_website = (isset($data['to_website'])) ? trim($data['to_website']) : '';
							$mssg_website   = (isset($data['mssg_website'])) ? trim($data['mssg_website']) : '';

							if(!empty($to_website) && !empty($mssg_website))
							{
								$to_website = preg_replace("/fieldname\d+/i","<%$0_value%>", $to_website);
								$to_website = CPCFF_AUXILIARY::parsing_fields_on_text(
									$fields, $params, $to_website, '', 'plain text', $itemnumber
								);

								$mssg_website = CPCFF_AUXILIARY::parsing_fields_on_text(
									$fields, $params, $mssg_website, $submission_obj->data, 'plain text', $itemnumber
								);

								$this->_send_sms(
									$to_website['text'],
									$mssg_website['text']
								);
							}

							$to_users   = (isset($data['to_users'])) 	? trim($data['to_users']) 	: '';
							$mssg_users = (isset($data['mssg_users'])) 	? trim($data['mssg_users']) : '';

							if(!empty($to_users) && !empty($mssg_users))
							{
								$to_users = preg_replace("/fieldname\d+/i","<%$0_value%>", $to_users);
								$to_users = CPCFF_AUXILIARY::parsing_fields_on_text(
									$fields, $params, $to_users, '', 'plain text', $itemnumber
								);

								$mssg_users = CPCFF_AUXILIARY::parsing_fields_on_text(
									$fields, $params, $mssg_users, $submission_obj->data, 'plain text', $itemnumber
								);

								$this->_send_sms(
									$to_users['text'],
									$mssg_users['text']
								);
							}
						}
					}
				}
			}
		} // End export_lead

		/**
		 *	Delete the form from the addon's table
		 */
        public function delete_form( $formid)
		{
			global $wpdb;
			$wpdb->delete( $wpdb->prefix.$this->form_twilio_table, array('formid' => $formid), '%d' );
		} // delete_form

		/**
		 *	Clone the form's row
		 */
		public function clone_form( $original_form_id, $new_form_id )
		{
			global $wpdb;

			$form_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_twilio_table." WHERE formid=%d", $original_form_id ), ARRAY_A);

			if(!empty($form_row))
			{
				unset($form_row["id"]);
				$form_row["formid"] = $new_form_id;
				$wpdb->insert( $wpdb->prefix.$this->form_twilio_table, $form_row);
			}
		} // End clone_form

		/**
		 *	It is called when the form is exported to export the addons data too.
		 *  Receive an array with the other addons data, and the form's id for filtering.
		 */
		public function export_form($addons_array, $formid)
		{
			global $wpdb;
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_twilio_table." WHERE formid=%d", $formid ), ARRAY_A );
			if(!empty($row))
			{
				$addons_array[ $this->addonID ] = array();
				unset($row['id']);
				unset($row['formid']);
				$addons_array[ $this->addonID ][] = $row;
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
							$wpdb->prefix.$this->form_twilio_table,
							$row
						);
					}
				}
			}
		} // End import_form

    } // End Class

    // Main add-on code
    $cpcff_twilio_obj = new CPCFF_Twilio();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cpcff_twilio_obj);
}
?>