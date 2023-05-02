<?php
/*
....
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPCFF_HubSpot' ) )
{
    class CPCFF_HubSpot extends CPCFF_BaseAddon
    {
		static public $category = 'External Services';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-hubspot-20191123";
		protected $name = "CFF - HubSpot";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#hubspot-addon';

		public function get_addon_form_settings( $form_id )
		{
			global $wpdb;

			// Insertion in database
			if(
				isset( $_REQUEST[ 'cpcff_hubspot' ] )
			)
			{
				$api_key  = isset($_REQUEST['cpcff_hubspot_api_key']) ? sanitize_text_field($_REQUEST['cpcff_hubspot_api_key']) : '';
				$access_token = isset($_REQUEST['cpcff_hubspot_access_token']) ? sanitize_text_field($_REQUEST['cpcff_hubspot_access_token']) : '';

				$fields = array(
					"email" => sanitize_text_field($_REQUEST['cpcff_hubspot_email']),
					"firstname" => sanitize_text_field($_REQUEST['cpcff_hubspot_firstname']),
					"lastname" => sanitize_text_field($_REQUEST['cpcff_hubspot_lastname']),
					"website" => sanitize_text_field($_REQUEST['cpcff_hubspot_website']),
					"company" => sanitize_text_field($_REQUEST['cpcff_hubspot_company']),
					"phone"  => sanitize_text_field($_REQUEST['cpcff_hubspot_phone']),
					"address" => sanitize_text_field($_REQUEST['cpcff_hubspot_address']),
					"city" => sanitize_text_field($_REQUEST['cpcff_hubspot_city']),
					"state" => sanitize_text_field($_REQUEST['cpcff_hubspot_state']),
					"zip" => sanitize_text_field($_REQUEST['cpcff_hubspot_zip'])
				);

                if(!empty($_POST['cpcff_hubspot_additional_attribute']) && is_array($_POST['cpcff_hubspot_additional_attribute']))
                {
                    foreach($_POST['cpcff_hubspot_additional_attribute'] as $key => $value)
                    {
                        $attribute_name  = sanitize_text_field($value);
                        $attribute_field = sanitize_text_field($_POST['cpcff_hubspot_additional_field'][$key]);

                        if(!empty($attribute_name)) $fields[$attribute_name] = $attribute_field;
                    }
                }

				$data  = array(
					'integration_method' => isset( $_REQUEST['cpcff_hubspot_integration_method'] ) && 'app' === $_REQUEST['cpcff_hubspot_integration_method'] ? 'app' : 'key',
					'api_key' => $api_key,
					'access_token' => $access_token,
					'fields' => $fields
				);

				$wpdb->delete( $wpdb->prefix.$this->form_hubspot_table, array( 'formid' => $form_id ), array( '%d' ) );
				$wpdb->insert(
					$wpdb->prefix.$this->form_hubspot_table,
					array(
						'formid'  => $form_id,
						'enabled' => isset($_REQUEST['cpcff_hubspot_enabled']) ? 1 : 0,
						'data'	  => json_encode( $data )
					),
					array( '%d', '%d', '%s' )
				);
			}

			$enabled = false;
			$api_key = '';
			$access_token = '';
			$integration_method = 'key';
			$fields  = array();
			$row  = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_hubspot_table." WHERE formid=%d", $form_id ) );

			if( $row )
			{
				$enabled = $row->enabled;
				$data = json_decode($row->data, true);
				if(!is_null($data))
				{
					$api_key = isset( $data['api_key'] ) ? $data['api_key'] : '';
					$access_token = isset( $data['access_token'] ) ? $data['access_token'] : '';
					$fields = $data['fields'];
					$integration_method = ( isset( $data['integration_method'] ) && $data[ 'integration_method' ] == 'app' ) ? 'app' : 'key';
				}
			}

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<input type="hidden" name="cpcff_hubspot" value="1" />
			<div id="metabox_hubspot_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_hubspot_addon_form_settings' ) ); ?>" >
				<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
				<div class="inside">
					<table cellspacing="0" style="width:100%;">
						<tr>
							<td style="white-space:nowrap;width:200px;"><?php _e('Enabling HubSpot Integration', 'calculated-fields-form');?>:</td>
							<td>
								<input type="checkbox" name="cpcff_hubspot_enabled" <?php echo ( ( $enabled ) ? 'CHECKED' : '' ); ?> />
							</td>
						</tr>
						<tr>
						<tr>
							<td style="white-space:nowrap;width:200px;"><?php _e('Integration method', 'calculated-fields-form');?>:</td>
							<td>
								<label style="margin-right:30px;"><input type="radio" name="cpcff_hubspot_integration_method" value="app" <?php echo $integration_method == 'app' ? 'CHECKED' : ''; ?> /> <?php esc_html_e('Private Apps'); ?></label>
								<label><input type="radio" name="cpcff_hubspot_integration_method" value="key" <?php echo $integration_method == 'key' ? 'CHECKED' : ''; ?> /> <?php esc_html_e('API Key'); ?></label>
							</td>
						</tr>
						<tr class="cff-hubspot-key">
							<td style="white-space:nowrap;width:200px;"><?php _e('HubSpot api key', 'calculated-fields-form');?>:</td>
							<td>
								<input type="text" name="cpcff_hubspot_api_key" value="<?php echo esc_attr( $api_key ); ?>" style="width:80%;" /> <a href="https://knowledge.hubspot.com/integrations/how-do-i-get-my-hubspot-api-key" target="_blank" style="font-weight:bold;font-size:1.5em;">?</a>
							</td>
						</tr>
						<tr class="cff-hubspot-app">
							<td style="white-space:nowrap;width:200px;"><?php _e('HubSpot token access', 'calculated-fields-form');?>:</td>
							<td>
								<input type="text" name="cpcff_hubspot_access_token" value="<?php echo esc_attr( $access_token ); ?>" style="width:80%;" /> <a href="https://developers.hubspot.com/docs/api/migrate-an-api-key-integration-to-a-private-app#create-a-new-private-app" target="_blank" style="font-weight:bold;font-size:1.5em;">?</a>
							</td>
						</tr>
						<tr>
							<td cospan="2"><b><?php _e('Fields relationship', 'calculated-fields-form'); ?></b></td>
						</tr>
						<tr>
							<td><?php _e('Email (required)', 'calculated-fields-form'); ?></td>
							<td><input type="text" placeholder="fieldname#" name="cpcff_hubspot_email" value="<?php print esc_attr((isset($fields['email'])) ? $fields['email'] : ''); ?>"></td>
						</tr>
						<tr>
							<td><?php _e('First name', 'calculated-fields-form'); ?></td>
							<td><input type="text" placeholder="fieldname#" name="cpcff_hubspot_firstname" value="<?php print esc_attr((isset($fields['firstname'])) ? $fields['firstname'] : ''); ?>"></td>
						</tr>
						<tr>
							<td><?php _e('Last name', 'calculated-fields-form'); ?></td>
							<td><input type="text" placeholder="fieldname#" name="cpcff_hubspot_lastname" value="<?php print esc_attr((isset($fields['lastname'])) ? $fields['lastname'] : ''); ?>"></td>
						</tr>
						<tr>
							<td><?php _e('Website', 'calculated-fields-form'); ?></td>
							<td><input type="text" placeholder="fieldname#" name="cpcff_hubspot_website" value="<?php print esc_attr((isset($fields['website'])) ? $fields['website'] : ''); ?>"></td>
						</tr>
						<tr>
							<td><?php _e('Company', 'calculated-fields-form'); ?></td>
							<td><input type="text" placeholder="fieldname#" name="cpcff_hubspot_company" value="<?php print esc_attr((isset($fields['company'])) ? $fields['company'] : ''); ?>"></td>
						</tr>
						<tr>
							<td><?php _e('Phone', 'calculated-fields-form'); ?></td>
							<td><input type="text" placeholder="fieldname#" name="cpcff_hubspot_phone" value="<?php print esc_attr((isset($fields['phone'])) ? $fields['phone'] : ''); ?>"></td>
						</tr>
						<tr>
							<td><?php _e('Address', 'calculated-fields-form'); ?></td>
							<td><input type="text" placeholder="fieldname#" name="cpcff_hubspot_address" value="<?php print esc_attr((isset($fields['address'])) ? $fields['address'] : ''); ?>"></td>
						</tr>
						<tr>
							<td><?php _e('City', 'calculated-fields-form'); ?></td>
							<td><input type="text" placeholder="fieldname#" name="cpcff_hubspot_city" value="<?php print esc_attr((isset($fields['city'])) ? $fields['city'] : ''); ?>"></td>
						</tr>
						<tr>
							<td><?php _e('State', 'calculated-fields-form'); ?></td>
							<td><input type="text" placeholder="fieldname#" name="cpcff_hubspot_state" value="<?php print esc_attr((isset($fields['state'])) ? $fields['state'] : ''); ?>"></td>
						</tr>
						<tr>
							<td><?php _e('Zip', 'calculated-fields-form'); ?></td>
							<td><input type="text" placeholder="fieldname#" name="cpcff_hubspot_zip" value="<?php print esc_attr((isset($fields['zip'])) ? $fields['zip'] : ''); ?>"></td>
						</tr>
                        <?php
                        foreach($fields as $key=>$value)
                        {
                            if(in_array($key, $this->common_fields)) continue;
                            print '
                            <tr>
                                <td>
                                    <input aria-label="'.esc_attr__('Additional attribute', 'calculated-fields-form').'" type="text" value="'.esc_attr($key).'" name="cpcff_hubspot_additional_attribute[]" />
                                </td>
                                <td>
                                    <input aria-label="'.esc_attr__('Field name', 'calculated-fields-form').'" placeholder="fieldname#" type="text" value="'.esc_attr($value).'" name="cpcff_hubspot_additional_field[]" /><input type="button" aria-label="'.esc_attr__('Delete pair', 'calculated-fields-form').'" style="margin-left:10px" class="button-secondary" value="'.esc_attr__('Delete', 'calculated-fields-form').'" onclick="jQuery(this).closest(\'tr\').remove();" />
                                </td>
                            </tr>
                            ';
                        }
                        ?>
                        <tr>
                            <td></td>
                            <td style="padding-top:10px;"><input type="button" arial-label="<?php esc_attr_e('Add new field', 'calculated-fields-form');?>" value="<?php esc_attr_e('Add new field', 'calculated-fields-form');?>" class="button-primary" onclick="cpcff_hubspot_add_new_field(this);" /></td>
                        </tr>
					</table>
                    <script>
                        function cpcff_hubspot_add_new_field(e)
                        {
                            var str = '<tr>'+
                                '<td>'+
                                    '<input aria-label="<?php esc_attr_e('Additional attribute', 'calculated-fields-form'); ?>" type="text" value="" name="cpcff_hubspot_additional_attribute[]" />'+
                                '</td>'+
                                '<td>'+
                                    '<input aria-label="<?php esc_attr_e('Field name', 'calculated-fields-form'); ?>" placeholder="fieldname#" type="text" value="" name="cpcff_hubspot_additional_field[]" /><input type="button" aria-label="<?php esc_attr_e('Delete pair', 'calculated-fields-form'); ?>" style="margin-left:10px;" class="button-secondary" value="<?php esc_attr_e('Delete', 'calculated-fields-form'); ?>" onclick="jQuery(this).closest(\'tr\').remove();" />'+
                                '</td>'+
                            '</tr>';
                            jQuery(e).closest('tr').before(str);
                        }
						function cpcff_hubspot_integration_method()
                        {
							jQuery('.cff-hubspot-key,.cff-hubspot-app').hide();
							jQuery('.cff-hubspot-'+jQuery('[name="cpcff_hubspot_integration_method"]:checked').val()).show();
						}
						jQuery(document).on('change', '[name="cpcff_hubspot_integration_method"]', cpcff_hubspot_integration_method);
						cpcff_hubspot_integration_method();
                    </script>
                    <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
				</div>
			</div>
			<?php
		}

		/************************ ADDON CODE *****************************/
        /************************ ATTRIBUTES *****************************/

		private $form_hubspot_table = 'cp_calculated_fields_form_hubspot';
		private $endpoint;
        private $common_fields;

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->endpoint = array(
				'app' => 'https://api.hubapi.com/crm/v3/objects/contacts',
				'key' => 'https://api.hubapi.com/contacts/v1/contact/createOrUpdate/email/%s/?hapikey=%s'
			);
			$this->description = __("The add-on allows to create/update HubSpot contacts from the website forms", 'calculated-fields-form' );
            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;

            $this->common_fields = [
                "email",
                "firstname",
                "lastname",
                "website",
                "company",
                "phone",
                "address",
                "city",
                "state",
                "zip"
            ];

			// Export the lead
			add_action( 'cpcff_process_data', array( &$this, 'send_to_hubspot' ) );

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
         * Create the database tables
         */
        protected function update_database()
		{
			global $wpdb;
			$charset_collate = $wpdb->get_charset_collate();
			$db_queries = array( "CREATE TABLE ".$wpdb->prefix.$this->form_hubspot_table." (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					formid INT NOT NULL,
					enabled TINYINT DEFAULT 0 NOT NULL,
					data text,
					UNIQUE KEY id (id)
				) $charset_collate;"
			);
			$this->_run_update_database($db_queries);
		} // End update_database

        /************************ PRIVATE METHODS *****************************/

		/************************ PUBLIC METHODS  *****************************/

		/**
         * Send the information to HubSpot
         */
        public function	send_to_hubspot( $params )
		{
			global $wpdb, $wp_version;

			$form_id = @intval( $params[ 'formid' ] );
			if( $form_id )
			{
				$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_hubspot_table." WHERE formid=%d", $form_id ) );
				if( $row && !empty( $row->enabled ) )
				{
					$data = json_decode( $row->data, true);
					if(!empty($data))
					{
						$integration_method = ( isset( $data['integration_method'] ) && $data['integration_method'] == 'app' ) ? 'app' : 'key';

						if ( 'key' === $integration_method ) {
							$api_key = isset( $data['api_key'] ) ? $data['api_key']  : '';
						} else {
							$api_key = isset( $data['access_token'] ) ? $data['access_token']  : '';
						}

						if(!empty($api_key))
						{
							$hubspot_args = array('properties' => array());
							$email = '';

							$fields = (!empty($data['fields']) && is_array($data['fields'])) ? $data['fields'] : array();
							foreach( $fields as $attr => $field )
							{
								$field = trim($field);
								$value = isset($params[ $field ]) ? $params[ $field ] : (preg_match('/fieldname\d+/',$field) ? '' : $field);

								if($attr == 'email') $email = $value;

								if ( $integration_method == 'key' ) {
									if($attr != 'email' && !empty($value))
										$hubspot_args['properties'][] = array(
											'property' => $attr,
											'value'	=> $value
										);
								} else {
									$hubspot_args['properties'][$attr] = $value;
								}
							}

							if(!empty($email))
							{
								if ( $integration_method == 'key' ) {
									$url = sprintf($this->endpoint[$integration_method], $email, $api_key);
									$args = array(
										'body' 		=> json_encode( $hubspot_args ),
										'headers' 	=> array(
											'Content-Type' => 'application/json',
											'user-agent' => 'WordPress-to-HubSpot for calculated-fields-form plugin - WordPress/'.$wp_version.'; '.get_bloginfo( 'url' ),
										),
										'timeout' => 45,
										'sslverify'	=> false,
									);
								} else {

									$url = rtrim($this->endpoint[$integration_method], '/').(!empty($hubspot_args['properties']['email']) ? '/'.$hubspot_args['properties']['email'].'?idProperty=email' : '');

									$response = wp_remote_get(
										$url,
										array(
											'headers' 	=> array(
												'Content-Type' => 'application/json',
												'Authorization'=> 'Bearer '.$api_key,
												'user-agent' => 'WordPress-to-HubSpot for calculated-fields-form plugin - WordPress/'.$wp_version.'; '.get_bloginfo( 'url' ),
											),
											'timeout' => 45,
											'sslverify'	=> false,
										)
									);

									$args = array(
										'method'	=> 'PATCH',
										'body' 		=> json_encode( $hubspot_args ),
										'headers' 	=> array(
											'Content-Type' => 'application/json',
											'Authorization'=> 'Bearer '.$api_key,
											'user-agent' => 'WordPress-to-HubSpot for calculated-fields-form plugin - WordPress/'.$wp_version.'; '.get_bloginfo( 'url' ),
										),
										'timeout' => 45,
										'sslverify'	=> false,
									);

									if ( ! is_wp_error( $response ) )
									{
										if ( 404 == wp_remote_retrieve_response_code($response) ) {
											$args[ 'method' ] = 'POST';
											$url = $this->endpoint[$integration_method];
										}
									}
									else
									{
										$args[ 'method' ] = 'POST';
										$url = $this->endpoint[$integration_method];
									}
								}

								$response = wp_remote_post($url, $args);

								if ( is_wp_error( $response ) )
								{
									$error_message = $response->get_error_message();
									error_log( "CFF_HubSpot Error: $error_message" );
								}
							}
							else
							{
								error_log( "CFF_HubSpot Error: The email is empty" );
							}
						}
					}
				}
			}
		} // End send_to_hubspot

		/**
		 *	Delete the form from the addon's table
		 */
        public function delete_form( $formid)
		{
			global $wpdb;
			$wpdb->delete( $wpdb->prefix.$this->form_hubspot_table, array('formid' => $formid), '%d' );
		} // delete_form

		/**
		 *	Clone the form's row
		 */
		public function clone_form( $original_form_id, $new_form_id )
		{
			global $wpdb;

			$form_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_hubspot_table." WHERE formid=%d", $original_form_id ), ARRAY_A);

			if(!empty($form_row))
			{
				unset($form_row["id"]);
				$form_row["formid"] = $new_form_id;
				$wpdb->insert( $wpdb->prefix.$this->form_hubspot_table, $form_row);
			}
		} // End clone_form

		/**
		 *	It is called when the form is exported to export the addons data too.
		 *  Receive an array with the other addons data, and the form's id for filtering.
		 */
		public function export_form($addons_array, $formid)
		{
			global $wpdb;
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_hubspot_table." WHERE formid=%d", $formid ), ARRAY_A );
			if(!empty( $row ))
			{
				unset($row['id']);
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
					$wpdb->prefix.$this->form_hubspot_table,
					$addons_array[$this->addonID]
				);
			}
		} // End import_form

    } // End Class

    // Main add-on code
    $cpcff_hubspot_obj = new CPCFF_HubSpot();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cpcff_hubspot_obj);
}