<?php
/*
....
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPCFF_Users' ) )
{
    class CPCFF_Users extends CPCFF_BaseAddon
    {
		static public $category = 'Extending Features';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-users-20151013";
		protected $name = "CFF - Users Permissions";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#users-addon';

		public function get_addon_form_settings( $form_id )
		{
			if( isset( $_REQUEST[ 'cpcff_user_registered' ] ) )
			{
				// Save the addon settings
				$settings = array(
					'registered' => ( $_REQUEST[ 'cpcff_user_registered' ] == 1 ) ? 1 : 0,
					'unique'	 => ( empty( $_REQUEST[ 'cpcff_user_unique' ] ) ) ? 0 : 1,
					'messages'	 => array(
						'unique_mssg' 		=> wp_kses_post(stripcslashes( $_REQUEST[ 'cpcff_user_messages' ][ 'unique_mssg' ] )),
						'privilege_mssg' 	=> wp_kses_post(stripcslashes( $_REQUEST[ 'cpcff_user_messages' ][ 'privilege_mssg' ] ))
					),
					'user_ids'	 => ( !empty($_REQUEST[ 'cpcff_user_ids' ]) ) ? array_map('sanitize_text_field', $_REQUEST[ 'cpcff_user_ids' ]) : array(),
					'user_roles' => ( !empty($_REQUEST[ 'cpcff_user_roles' ]) ) ? array_map('sanitize_text_field', $_REQUEST[ 'cpcff_user_roles' ]) : array(),
					'actions'    => array(
						'delete' => ( !empty( $_REQUEST[ 'cpcff_user_actions' ] ) && !empty( $_REQUEST[ 'cpcff_user_actions' ][ 'delete' ] ) ) ? 1 : 0,
						'edit' 	 => ( !empty( $_REQUEST[ 'cpcff_user_actions' ] ) && !empty( $_REQUEST[ 'cpcff_user_actions' ][ 'edit' ] ) ) ? 1 : 0
					),
					'admin_email' => ( isset( $_REQUEST[ 'cpcff_user_admin_email' ]) ) ? 1 : 0,
					'login_form' => ( isset( $_REQUEST[ 'cpcff_user_login_form' ] ) ) ? 1 : 0,
  					'summary' => stripcslashes( trim( $_REQUEST[ 'cpcff_user_summary' ] ) )
				);
				update_option( $this->var_name.'_'.$form_id, $settings );
			}
			else
			{
				$settings = $this->get_form_settings( $form_id, array() );
				if( empty( $settings ) )
				{
					$settings = array(
						'registered' => false,
						'unique'	 => false,
						'messages'	 => array(
							'unique_mssg' 		=> "The form can be submitted only one time per user",
							'privilege_mssg' 	=> "You don't have sufficient privileges to access the form"
						),
						'user_ids'	 => array(),
						'user_roles' => array(),
						'actions'    => array(
							'delete' => 1,
							'edit' 	 => 1
						),
						'admin_email' => 0,
						'login_form' => 0,
						'summary' => ''
					);
				}
			}

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<div id="metabox_users_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_users_addon_form_settings' ) ); ?>" >
				<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
				<div class="inside">
					<table cellspacing="0" class="form-table width100">
						<tr>
							<td style="white-space:nowrap;width:200px; vertical-align:top;font-weight:bold;"><?php _e('Display the form for', 'calculated-fields-form');?>:</td>
							<td>
								<input type="radio" name="cpcff_user_registered" value="1" <?php if( !empty( $settings[ 'registered' ] ) ) print 'CHECKED'; ?> /> <?php _e( 'Registered users only', 'calculated-fields-form' ); ?><br />
								<input type="radio" name="cpcff_user_registered" value="0" <?php if( empty( $settings[ 'registered'  ] ) ) print 'CHECKED'; ?> /> <?php _e( 'Anonymous users', 'calculated-fields-form' ); ?>
							</td>
						</tr>
					</table>
					<h3><?php _e( 'For registered users only', 'calculated-fields-form' ); ?></h3>
					<table cellspacing="0" class="form-table width100">
						<tr>
							<td style="white-space:nowrap;width:200px;vertical-align:top;font-weight:bold;"><?php _e( 'The form may be submitted', 'calculated-fields-form' ); ?>:</td>
							<td>
								<input type="checkbox" name="cpcff_user_unique" value="1" <?php if( !empty( $settings[ 'unique' ] ) ) print 'CHECKED'; ?> /> <?php _e( 'only one time per user', 'calculated-fields-form' );?>
								<i>(<?php _e('This restriction does not affect to the administrators', 'calculated-fields-form'); ?>)</i>
							</td>
						</tr>
						<tr>
							<td colspan="2" style="padding-top:20px;"><b><?php _e('The form will be available only for users with the roles', 'calculated-fields-form');?>:</b></td>
						</tr>
						<tr>
							<td style="white-space:nowrap;width:200px;vertical-align:top;font-weight:bold;"><?php _e( 'Roles', 'calculated-fields-form' ); ?>:</td>
							<td>
								<select MULTIPLE name="cpcff_user_roles[]"  class="width75">
								<?php
									// Get the roles list
									global $wp_roles;
									if ( !isset( $wp_roles ) )
									{
										$wp_roles = new WP_Roles();
									}
									$roles = $wp_roles->get_names();

									foreach( $roles as $_role_value => $_role_name )
									{
										$_selected = '';
										if(
											!empty( $settings[ 'user_roles' ] ) &&
											is_array( $settings[ 'user_roles' ] ) &&
											in_array( $_role_value, $settings[ 'user_roles' ] )
										)
										{
											$_selected = 'SELECTED';
										}
										print '<option value="'.esc_attr($_role_value).'" '.$_selected.'>'.esc_html($_role_name).'</option>';
									}
								?>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan="2" style="padding-top:20px;padding-bottom:20px;">
								<b><?php _e('Or for the specific users', 'calculated-fields-form');?>:</b><br />
								<em><?php _e("The forms are always available for the website's administrators",'calculated-fields-form'); ?></em>
							</td>
						</tr>
						<tr>
							<td style="white-space:nowrap;width:200px;vertical-align:top;font-weight:bold;"><?php _e( 'Users', 'calculated-fields-form' ); ?>:</td>
							<td>
								<select MULTIPLE name="cpcff_user_ids[]" class="width75">
								<?php
									// Get the users list
									$users = get_users( array( 'fields' => array( 'ID', 'display_name' ), 'orderby' => 'display_name' ) );

									foreach( $users as $_user )
									{
										$_selected = '';
										if(
											!empty( $settings[ 'user_ids' ] ) &&
											is_array( $settings[ 'user_ids' ] ) &&
											in_array( $_user->ID, $settings[ 'user_ids' ] )
										)
										{
											$_selected = 'SELECTED';
										}
										print '<option value="'.esc_attr($_user->ID).'" '.$_selected.'>'.esc_html($_user->display_name).'</option>';
									}

								?>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan="2" style="padding-top:20px;padding-bottom:20px;">
								<b><?php _e('Actions allowed over the forms submissions by the users', 'calculated-fields-form');?>:</b><br />
								<?php _e('Uses the corresponding shortcodes to insert the forms submissions in the users profile', 'calculated-fields-form');?>
							</td>
						</tr>
						<tr>
							<td style="white-space:nowrap;width:200px;vertical-align:top;font-weight:bold;"><?php _e( 'Actions', 'calculated-fields-form' ); ?>:</td>
							<td>
								<input type="checkbox" name="cpcff_user_actions[edit]" value="1" <?php if( !empty( $settings[ 'actions' ] ) && !empty( $settings[ 'actions' ][ 'edit' ] ) ) print 'CHECKED'  ?> /> <?php _e('Edit the submitted data (Really is created a new entry, and the previous one is deactivated, but it is yet accessible for the administrators from the messages section)', 'calculated-fields-form'); ?><br />
								<input type="checkbox" name="cpcff_user_actions[delete]" value="1" <?php if( !empty( $settings[ 'actions' ] ) && !empty( $settings[ 'actions' ][ 'delete' ] ) ) print 'CHECKED'  ?> /> <?php _e('Delete the submitted data (The submissions are disabled. The submissions are deleted only from the messages section)', 'calculated-fields-form'); ?>
							</td>
						</tr>
						<tr>
							<td colspan="2"><b><?php _e('Send an email copy to the user after editing by the administrator', 'calculated-fields-form'); ?>:</b></td>
						</tr>
						<tr>
							<td></td>
							<td><input type="checkbox" name="cpcff_user_admin_email" <?php if(!empty($settings['admin_email'])) print 'CHECKED'?> /> <?php _e('After the administrator edits the users submissions through the [CP_CALCULATED_FIELDS_USER_SUBMISSIONS_LIST] shortcode, it is sent a notification email to the users.', 'calculated-fields-form'); ?></td>
						</tr>
						<tr>
							<td colspan="2" style="padding-top:20px;padding-bottom:20px;">
								<b><?php _e('Error messages', 'calculated-fields-form');?>:</b><br />
								<?php _e('The messages are displayed instead of the form: if the user has no sufficient privileges, or if the form may be submitted only one time per registered user, and it has been submitted', 'calculated-fields-form');?>
							</td>
						</tr>
						<tr>
							<td style="white-space:nowrap;width:200px;vertical-align:top;font-weight:bold;"><?php _e( 'Messages', 'calculated-fields-form' ); ?>:</td>
							<td>
								<b><?php _e('The user has no sufficient privileges', 'calculated-fields-form' );?>:</b><br />
								<textarea name="cpcff_user_messages[privilege_mssg]" class="width75" rows="6" ><?php if( !empty( $settings[ 'messages' ] ) && isset( $settings[ 'messages' ][ 'privilege_mssg' ] ) ) print esc_textarea( $settings[ 'messages' ][ 'privilege_mssg' ] ); ?></textarea><br /><br />
								<?php _e('Display a login form for unregistered users', 'calculated-fields-form')?>:
								<input type="checkbox" name="cpcff_user_login_form" <?php print (!empty($settings['login_form'])) ? 'CHECKED' : ''; ?>/><br /><br />
								<b><?php _e('The form has been submitted previously', 'calculated-fields-form' );?>:</b><br />
								<textarea name="cpcff_user_messages[unique_mssg]"  class="width75" rows="6" ><?php if( !empty( $settings[ 'messages' ] ) && isset( $settings[ 'messages' ][ 'unique_mssg' ] ) ) print esc_textarea( $settings[ 'messages' ][ 'unique_mssg' ] ); ?></textarea>
							</td>
						</tr>
					</table>
					<div style="padding-top:20px;padding-bottom:20px;">The add-on includes a new shortcode: <b>[CP_CALCULATED_FIELDS_USER_SUBMISSIONS_LIST]</b>, to display the list of submissions belonging to an user. If the shortcode is inserted without attributes, the list of submissions will include those entries associated to the logged user. This shortcode accepts two attributes: id, for the user's id, and login, for the username (the id attribute has precedence over the login), in whose case the addon will list the submissions of the user selected,  furthermore it is possible restrict the list to a specific form using the attribute: form="#", where # should be replaced by the form's id.</div>
					<table cellspacing="0" class="form-table width100">
						<tr>
							<td style="white-space:nowrap;width:200px; vertical-align:top;font-weight:bold;"><?php _e('Summary', 'calculated-fields-form');?>:</td>
							<td>
								<textarea name="cpcff_user_summary" class="width75" rows="6" ><?php if( !empty( $settings[ 'summary' ] ) ) print esc_textarea( $settings[ 'summary' ] ); ?></textarea><br />
								<i>It is the content of previous shortcode.</i>
							</td>
						</tr>
					</table>
                    <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
				</div>
			</div>
			<?php
		}

		/************************ ADDON CODE *****************************/
        /************************ ATTRIBUTES *****************************/

		private $var_name 			= 'cp_cff_addon_users';
		private $post_user_table 	= 'cp_calculated_fields_user_submission';
		private $events_per_page 	= 10;
		private $forms_settings		= array();
		private $deactivate_id;
		private $user_id;
		private $_logged_user_id;
		private $_is_admin; // True or false if the registered user has administrator role.
		private $_cpcff_main;
		private $users_data;

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			// Initialize properties
			$this->users_data = array();
			$this->_cpcff_main = CPCFF_MAIN::instance();
			$this->description = __("The add-on allows restrict the form to: registered users, users with specific roles, or specific users. Furthermore, allows to associate the submitted information with the submitter, if it is a registered user", 'calculated-fields-form' );

            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;

			// Check for the existence of the 'refresh_opener' parameter
			if( isset( $_REQUEST[ 'refresh_opener' ] ) )
			{
				$this->get_refresh_opener_script();
			}

			// Check if the submission is being edited
			add_action( 'init', array( &$this, 'edit_submission' ), 1 );

			// Check the upload files
			add_action( 'cpcff_process_data_before_insert', array( &$this, 'before_insert' ), 2, 3 );

			// Insert the entry in the database users-submission
			add_action( 'cpcff_process_data', array( &$this, 'insert_update' ) );

			// Decides if includes the form or a message
			add_filter( 'cpcff_the_form', array( &$this, 'the_form' ), 10, 2 );

			// Replace the shortcode with the list of submissions
			add_shortcode( 'CP_CALCULATED_FIELDS_USER_SUBMISSIONS_LIST', array( &$this, 'replace_shortcode' ) );
			add_filter( 'cpcff_results_list_query', array( &$this, 'results_list_query' ), 10, 1 );

			if( is_admin() )
			{
				// Deletes an user-submission entry if the administrator deletes it
				add_action( 'cpcff_delete_submission', array( &$this, 'delete' ), 10, 1 );

				/************************ MESSAGES & CSV SECTION ************************/

				// Insert new headers in the  messages section
				add_action( 'cpcff_messages_filters', array( &$this, 'messages_filters'), 10 );

				// Modifies the query for filtering messages to includes the users information
				add_filter( 'cpcff_messages_query', array( &$this, 'messages_query' ), 10, 1 );
				add_filter( 'cpcff_csv_query', array( &$this, 'csv_query' ), 10, 1 );

				// Insert new headers in the  messages section
				add_action( 'cpcff_messages_list_header', array( &$this, 'messages_header'), 10 );

				// Add the users data to the messages
				add_action( 'cpcff_message_row_data', array( &$this, 'messages_data'), 10, 1 );

				// Add the visual edition button
				add_action( 'cpcff_message_row_buttons', array( &$this, 'visual_edition_button'), 10, 2 );

				// Delete forms
				add_action( 'cpcff_delete_form', array(&$this, 'delete_form') );

				// Clone forms
				add_action( 'cpcff_clone_form', array(&$this, 'clone_form'), 10, 2 );

				// Export addon data
				add_action( 'cpcff_export_addons', array(&$this, 'export_form'), 10, 2 );

				// Import addon data
				add_action( 'cpcff_import_addons', array(&$this, 'import_form'), 10, 2 );
			}
			else
			{
				add_filter( 'cpcff_get_option', array( &$this, 'get_option' ), 10, 3 );
			}
        } // End __construct

        public function __get($property)
        {
            switch($property)
            {
                case 'is_admin':
                    if(!isset($this->_is_admin)) $this->_is_admin = current_user_can('manage_options');
                    return $this->_is_admin;
                case 'logged_user_id':
                    if(!isset($this->_logged_user_id)) $this->_logged_user_id = get_current_user_id();
                    return $this->_logged_user_id;
            }
        }

        /************************ PROTECTED METHODS *****************************/

		/**
         * Creates the database tables
         */
        protected function update_database()
		{
			global $wpdb;
			$charset_collate = $wpdb->get_charset_collate();
			$db_queries = array( "CREATE TABLE ".$wpdb->prefix.$this->post_user_table." (
					submissionid INT NOT NULL,
					userid INT NOT NULL,
					active TINYINT(1) NOT NULL,
					PRIMARY KEY (userid,submissionid)
				) $charset_collate;"
			);
			$this->_run_update_database($db_queries);
		} // End update_database

        /************************ PRIVATE METHODS *****************************/

		private function get_refresh_opener_script()
		{
			?>
			<script>
				window.opener.location.reload();
				window.close();
			</script>
			<?php
			exit;
		} // End get_refresh_opener_script

		/**
		 * Get the forms settings. Checks if the form's settings has been read previously, or get the value from the options
		 * $form_id, integer with the form's id
		 * Returns the form's settings
		 */
		private function get_form_settings( $form_id, $default = false )
		{
			if( empty( $this->forms_settings[ $form_id ] ) )
			{
				$this->forms_settings[ $form_id ] = get_option( $this->var_name.'_'.$form_id, array() );
				if( empty( $this->forms_settings[ $form_id ] ) && $default !== false )
				{
					$this->forms_settings[ $form_id ] = $default;
				}
				elseif( empty( $this->forms_settings[ $form_id ][ 'actions' ] ) )
				{
					$this->forms_settings[ $form_id ][ 'actions' ] = array( 'delete' => false, 'edit' => false );
				}
			}
			return $this->forms_settings[ $form_id ];

		} // End get_form_settings

		/**
		 * Generates an HTML table with all the submissions
		 */
		private function user_messages_list( $events, $forms, $content = '' )
		{
			$cellstyle   = 'border:1px solid #F0F0F0;border-top:0;border-left:0;';
			$actionstyle = 'cursor:pointer;color:#00a0d2;';

			$str = '
			<div id="dex_printable_contents">
				<table cellspacing="0" style="border:0;" class="users-permissions-submissions-list">
					<thead style="padding-left:7px;font-weight:bold;white-space:nowrap;" class="the-header">
						<tr>
							<th  style="'.$cellstyle.'width:40px;">'.__( 'Id', 'calculated-fields-form' ).'</th>
							<th  style="'.$cellstyle.'">'.__( 'Form', 'calculated-fields-form' ).'</th>
							<th  style="'.$cellstyle.'">'.__( 'Date', 'calculated-fields-form' ).'</th>'
							.($this->is_admin // The administrator has access to all submissions
							? '<th  style="'.$cellstyle.'">'.__( 'User', 'calculated-fields-form' ).'</th>'
							: '').
							'<th  style="'.$cellstyle.'border-right:0;">'.__( 'Options', 'calculated-fields-form' ).'</th>
						</tr>
					</thead>
					<tbody id="the-list">
			';

			for( $i = 0; $i < count( $events ); $i++ )
			{
				$this->get_form_settings( $events[ $i ]->formid );

				// Check if the submission will be deleted, and if the form has been configured to allow delete the submissions
				if(
					!empty( $_REQUEST[ 'cpcff_addon_user_delete' ] ) &&
					$_REQUEST[ 'cpcff_addon_user_delete' ] == $events[ $i ]->id &&
					(
						(
							!empty( $this->forms_settings[ $events[ $i ]->formid ] ) &&
							$this->forms_settings[ $events[ $i ]->formid ][ 'actions' ][ 'delete' ] &&
							$this->user_id == $this->logged_user_id
						) ||
						$this->is_admin // The administrator can always delete the entries
					)
				)
				{
					$to_delete = @intval($_REQUEST['cpcff_addon_user_delete']);
					if($this->is_admin) // Delete the entry completely
					{
						CPCFF_SUBMISSIONS::delete($to_delete);
					}
					else // Only deactivate it
					{
						$this->deactivate($to_delete);
					}
					continue;
				}

				// Get user's data
				$user_str = '';
				if(($user_id = @intval($events[$i]->userid)) != 0)
				{
					if(!isset($this->users_data[$user_id]))
					{
						$user_data = get_userdata($user_id);
						if($user_data)
						{
							$this->users_data[$user_id] = '<a href="'.get_edit_user_link( $user_id ).'" target="_blank">'.$user_data->user_nicename.'</a>';
						}
						else
						{
							$this->users_data[$user_id] = '';
						}
					}
					$user_str = $this->users_data[$user_id];
				}

				$str .= '
					<tr class="form-'.$events[ $i ]->formid.' row-1 users-permissions-item">
						<td style="'.$cellstyle.'font-weight:bold;" class="users-permissions-item-id">'.$events[$i]->id.'</td>
						<td style="'.$cellstyle.'" class="users-permissions-item-form">'.( ( !empty( $forms[ $events[ $i ]->formid ] ) ) ? $forms[ $events[ $i ]->formid ][ 'name' ] : '' ).'</td>
						<td style="'.$cellstyle.'" class="users-permissions-item-datetime">'.substr($events[$i]->time,0,16).'</td>'
						.($this->is_admin // The administrator has access to all submissions
							? '<td  style="'.$cellstyle.'">'.$user_str.'</td>'
							: '').
						'<td style="'.$cellstyle.'border-right:0;white-space:nowrap;" class="users-permissions-item-actions">
				';

				// The actions are always available for users with administrator role
				if(
					!empty( $this->forms_settings[ $events[ $i ]->formid ] ) ||
					$this->is_admin
				)
				{
					if( ($this->user_id == $this->logged_user_id && $this->forms_settings[ $events[ $i ]->formid ][ 'actions' ][ 'delete' ]) || $this->is_admin)
					{
						$str .= '<span style="'.$actionstyle.'margin-right:5px;" onclick="cpcff_addon_user_deleteMessage('.$events[$i]->id.')">['.__( 'Delete', 'calculated-fields-form' ).']</span>';
					}

					if( ($this->user_id == $this->logged_user_id && $this->forms_settings[ $events[ $i ]->formid ][ 'actions' ][ 'edit' ]) || $this->is_admin)
					{
						$str .= '<span style="'.$actionstyle.'" onclick="cpcff_addon_user_editMessage('.$events[$i]->id.')">['.__( 'Update', 'calculated-fields-form' ).']</span>';
					}
				}
				$str .= '
						</td>
					</tr>
					<tr class="form-'.$events[ $i ]->formid.' row-2 users-permissions-item">
						<td colspan="4" style="'.$cellstyle.'border-right:0;">';

				$paypal_post = @unserialize( $events[ $i ]->paypal_post );
				if(
					(
						empty( $content ) &&
						empty( $this->forms_settings[ $events[ $i ]->formid ][ 'summary' ] )
					) ||
					$paypal_post == false ||
					empty( $forms[ $events[ $i ]->formid ] )
				)
				{
					$str .= str_replace( array( '\"', "\'", "\n" ), array( '"', "'", "<br />" ), $events[$i]->data );
					// Add links
					if( $paypal_post !== false )
					{
						foreach( $paypal_post as $_key => $_value )
						{
							if( strpos( $_key, '_url' ) )
							{
								if( is_array( $_value ) )
								{
									foreach( $_value as $_url )
									{
										$str .= '<p><a href="'.esc_attr( $_url ).'" target="_blank">'.$_url.'</a></p>';
									}
								}
							}
						}
					}
				}
				else
				{
					if( empty( $forms[ $events[ $i ]->formid ][ 'fields' ] ) )
					{
						$raw_form_str = CPCFF_AUXILIARY::clean_json( $forms[ $events[ $i ]->formid ][ 'structure' ] );
						$form_data = json_decode( $raw_form_str );

						$fields = array();
						foreach($form_data[0] as $item)
						{
							$fields[$item->name] = $item;
						}
						$forms[ $events[ $i ]->formid ][ 'fields' ] = $fields;
					}
					$forms[ $events[ $i ]->formid ][ 'fields' ][ 'ipaddr' ] = $events[ $i ]->ipaddr;
					$forms[ $events[ $i ]->formid ][ 'fields' ][ 'submission_datetime' ] = $events[ $i ]->time;
					$forms[ $events[ $i ]->formid ][ 'fields' ][ 'paid' ] = $events[ $i ]->paid;

					$replaced_values = CPCFF_AUXILIARY::parsing_fields_on_text(
						$forms[ $events[ $i ]->formid ][ 'fields' ],
						$paypal_post,
						do_shortcode(html_entity_decode((!empty ($content) ? $content : $this->forms_settings[ $events[ $i ]->formid ][ 'summary' ]))),
						$events[$i]->data,
						'html',
						$events[ $i ]->id
					);

					$str .= $replaced_values[ 'text' ];
				}

				$str .= '
						</td>
					</tr>
				';
			}

			$str .= '
					</tbody>
				</table>
			</div>
			';

			// The javascript code
			$str .= '
				<script>
					function cpcff_addon_user_deleteMessage( submission )
					{
						if (confirm("'.esc_attr__( 'Do you want to delete the item?', 'calculated-fields-form' ).'"))
						{
							jQuery("#cpcff_addon_user_delete_form").remove();
							jQuery("body").append( "<form id=\'cpcff_addon_user_delete_form\' method=\'POST\'><input type=\'hidden\' name=\'cpcff_addon_user_delete\' value=\'"+submission+"\'></form>" );
							jQuery("#cpcff_addon_user_delete_form").submit();
						}
					}
					function cpcff_addon_user_editMessage( submission )
					{
						var w = screen.width*0.8,
							h = screen.height*0.7,
							l = screen.width/2 - w/2,
							t = screen.height/2 - h/2,
							new_window = window.open("", "formpopup", "resizeable,scrollbars,width="+w+",height="+h+",left="+l+",top="+t);

						jQuery("#cpcff_addon_user_edit_form").remove();
						jQuery("body").append( "<form id=\'cpcff_addon_user_edit_form\' method=\'POST\'  target=\'formpopup\'><input type=\'hidden\' name=\'cpcff_addon_user_edit\' value=\'"+submission+"\'></form>" );
						jQuery("#cpcff_addon_user_edit_form").submit();
					}
				</script>
			';
			return $str;
		} // End user_messages_list

		private function edit_submission_aux( $submission_id, $with_form = 1 )
		{
			// Edit submission. Checks if the submission belongs to the user, and if the user can edit it.
			global $wpdb;

			$str = '';

			// Get logged user
			$user_obj = wp_get_current_user();

			if( $user_obj->ID != 0 )
			{
				if( $this->is_admin )
				{
					$submission = CPCFF_SUBMISSIONS::populate(
						$wpdb->prepare( "SELECT submission.* FROM ".$wpdb->prefix.$this->post_user_table." as submission_user, ".CP_CALCULATEDFIELDSF_POSTS_TABLE_NAME." as submission WHERE submission_user.submissionid=%d AND submission.id=submission_user.submissionid AND submission_user.active=1", $submission_id )
					);
					if(!empty($submission)) $submission = CPCFF_SUBMISSIONS::get($submission_id);
				}
				else
				{
					$submission = CPCFF_SUBMISSIONS::populate(
						$wpdb->prepare( "SELECT submission.* FROM ".$wpdb->prefix.$this->post_user_table." as submission_user, ".CP_CALCULATEDFIELDSF_POSTS_TABLE_NAME." as submission WHERE submission_user.submissionid=%d AND submission_user.userid=%d AND submission.id=submission_user.submissionid AND submission_user.active=1", array( $submission_id, $user_obj->ID ) )
					);
					if(!empty($submission)) $submission = CPCFF_SUBMISSIONS::get($submission_id);
				}

				if( !empty( $submission ) )
				{
					$form_id = $submission->formid;

					// Checks if the user can edit the submitted data, the administrator can always edit the data.
					$this->get_form_settings( $form_id );
					if( $this->forms_settings[ $form_id ][ 'actions' ][ 'edit' ] || $this->is_admin)
					{
						$_form_index = max(class_exists('CPCFF_MAIN') ? CPCFF_MAIN::$form_counter : 1, 1);
						// Get the submitted data and generate a JSON object
						$str .= '<script>if(typeof cpcff_default == "undefined") cpcff_default = {};
						cpcff_default['.$_form_index.'] = '.html_entity_decode(json_encode( $submission->paypal_post )).';
						</script>';
						if( $with_form )
						{
							$html_content = $this->_cpcff_main->public_form( array( 'id' => $form_id ) );
							$str .= $html_content;
						}
					}
				}
			}

			return $str;

		} // End edit_submission_aux
		/************************ PUBLIC METHODS  *****************************/

		/**
		 * Checks if the submission is being edited,
		 * if it corresponds to the logged user,
		 * and if the edition action is associated to the form.
		 * Finally, displays the form with the submissions data.
		 */
		public function edit_submission()
		{
			// Edit submission. Checks if the submission belongs to the user, and if the user can edit it.
			if( isset( $_REQUEST[ 'cpcff_addon_user_edit' ] ) )
			{
				$submission_id = intval( trim( @$_REQUEST[ 'cpcff_addon_user_edit' ] ) );
				$str = $this->edit_submission_aux( $submission_id );
				if( !empty( $str ) )
				{
					$str = preg_replace( '/<\/form>/i', '<input type="hidden" name="cpcff_submission_id" value="'.$submission_id.'"><input type="hidden" name="cpcff_refresh_list" value="1" /></form>', $str );
					$str = str_replace(array('persist-form','"persistence":1', '"autocomplete":1'),array('', '"persistence":0', '"autocomplete":0'),$str);
					ob_start();

					if(
						function_exists('wp_print_styles') &&
						function_exists('wp_print_scripts')
					)
					{
						wp_print_styles();
						wp_print_scripts();
						do_action('cpcff_footer');
					}
					else
					{
						wp_footer();
					}

					$str .= ob_get_contents();
					ob_end_clean();
					remove_all_actions('shutdown');
					wp_die($str.'<style>body{margin:2em !important;max-width:100% !important;box-shadow:none !important;background:white !important}html{background:white !important;}.wp-die-message>*:not(form){visibility: hidden;}</style>', __('Update Form', 'calculated-fields-form'), 200);
				}
				else
				{
					$this->get_refresh_opener_script();
				}
			}
		} // End edit_submission

		/**
		 * Checks the settings, and decides if display the form or the message
		 * $html_content, the HTML code of form, styles and scripts if corresponds
		 * $form_id, integer number_format
		 *
		 * Returns the same $html_content, or a message if the form is not available
		 */
		public function the_form( $html_content, $form_id )
		{
			global $wpdb;

			$settings = $this->get_form_settings( $form_id );
			$user_obj = wp_get_current_user();
			$login_form = '';
			if($user_obj->ID == 0) // Anonymous user
			{
				// The user does not have privileges enough
				if(
					!empty( $settings[ 'registered' ] ) ||
					!empty( $settings[ 'user_roles' ] ) ||
					!empty( $settings[ 'user_ids' ] )
				)
				{
					$error_mssg = 'privilege_mssg';
					if(!empty($settings[ 'login_form' ]))
					{
						$open = '';
						$close = '';
						if(preg_match('/"formtemplate":"([^"]*)"/', $html_content, $match))
						{
							$open = '<div class="'.esc_attr($match[1]).'">';
							$close = '</div>';
							if(preg_match_all('/<link[^>]*>/i', $html_content, $link_tags))
							{
								foreach( $link_tags[0] as $link_tag) $close .= $link_tag;
							}
						}
						$login_form = $open.'<div id="fbuilder">'.wp_login_form(
							array(
								'echo' 		=> false,
								'redirect'  => CPCFF_AUXILIARY::wp_current_url()
							)
						).'</div>'.$close;
					}
				}
				// The form can be submitted only one time per user, check IP
				elseif(!empty( $settings[ 'unique' ] ))
				{
					// Get IP address of the user
					$ip = $_SERVER['REMOTE_ADDR'];

					if(
						$wpdb->get_var(
							$wpdb->prepare(
								'SELECT COUNT(*) FROM '.CP_CALCULATEDFIELDSF_POSTS_TABLE_NAME.' WHERE formid=%d AND ipaddr=%s',
								array($form_id, $ip)
							)
						)
					)
					{
						$error_message = 'unique_mssg';
					}
				}
			}
			else // Registered user
			{
				$roles = $user_obj->roles;

				// The restrictions don't apply to the administrators.
				if( !$this->is_admin )
				{
					// The form is restricted by users and the current user is in the list
					if(
						!empty( $settings[ 'user_ids' ] ) &&
						!in_array( $user_obj->ID, $settings[ 'user_ids' ] ) ||
						!empty( $settings[ 'user_roles' ] ) &&
						!count( array_intersect( $settings[ 'user_roles' ], $roles ) )
					)
					{
						$error_mssg = 'privilege_mssg';
					}
					elseif(
						!empty( $settings[ 'unique' ] ) &&
						( $submission_id = intval( @$wpdb->get_var( $wpdb->prepare( 'SELECT addon.submissionid FROM '.$wpdb->prefix.$this->post_user_table.' as addon, '.CP_CALCULATEDFIELDSF_POSTS_TABLE_NAME.' as submissions WHERE addon.userid=%d AND addon.submissionid=submissions.id AND submissions.formid=%d AND addon.active=1 ORDER BY addon.submissionid DESC', array( $user_obj->ID, $form_id ) ) ) ) ) !== 0
					)
					{
						// Check if the user has permissions for edition and insert the pre-populated form
						if( $settings[ 'actions' ][ 'edit' ] )
						{
							$str  = $this->edit_submission_aux( $submission_id, 0 );
							$html_content = preg_replace( '/<\/form>/i', '<input type="hidden" name="cpcff_submission_id" value="'.$submission_id.'" /></form>', $html_content );
							$html_content = $html_content.$str;
						}
						else
						{
							$error_mssg = 'unique_mssg';
						}
					}
				}
			}

			// There are errors
			if( !empty( $error_mssg ) )
			{
				return do_shortcode(( !empty( $settings[ 'messages' ] ) && !empty( $settings[ 'messages' ][ $error_mssg ] ) ) ? $settings[ 'messages' ][ $error_mssg ] : '').$login_form;
			}

			return $html_content;
		} // End the_form

		/**
		 * Used to modify the URL of the thank you page if the submission is being edited
		 */
		public function get_option( $value, $field, $formid )
		{
			switch( $field )
			{
				case 'fp_return_page':
					if(!empty($_REQUEST['cpcff_refresh_list']))
						$value .= ((strpos($value, '?') === false ) ? '?' : '&').'refresh_opener=1';
				break;
				case 'cv_enable_captcha':
					if($this->is_admin && (isset($_REQUEST['cpcff_refresh_list']) || isset($_REQUEST['cpcff_addon_user_edit'])))
						$value = 'false';
				break;
				break;
				case 'cache':
					if(isset($_REQUEST['cpcff_addon_user_edit']))
						$value = '';
				break;
				case 'enable_paypal':
					if($this->is_admin && (isset($_REQUEST['cpcff_refresh_list']) || isset($_REQUEST['cpcff_addon_user_edit'])))
						$value = 0;
				break;
				case 'fp_destination_emails':
					if($this->is_admin && (isset($_REQUEST['cpcff_refresh_list']) || isset($_REQUEST['cpcff_addon_user_edit'])))
						$value = '';
				break;
				case 'cu_enable_copy_to_user':
					if($this->is_admin && (isset($_REQUEST['cpcff_refresh_list']) || isset($_REQUEST['cpcff_addon_user_edit'])))
					{
						$value = 'false';
						if(isset($_REQUEST['cp_calculatedfieldsf_id']))
						{
							$settings = $this->get_form_settings(@intval($_REQUEST['cp_calculatedfieldsf_id']));
							if($settings && $settings['admin_email']) $value = 'true';
						}
					}
				break;
			}
			return $value;
		} // End get_option

		public function before_insert(&$params, &$str, $fields )
		{
			global $wpdb;
			$user_obj = wp_get_current_user();
			$this->user_id = $user_obj->ID;

			// Option available only for logged users
			if( $this->user_id != 0 )
			{
				if( isset( $_REQUEST[ 'cpcff_submission_id' ] ) )
				{

					$cpcff_submission_id = @intval($_REQUEST[ 'cpcff_submission_id' ] );

					if($cpcff_submission_id)
					{
						$user_submission = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM '.$wpdb->prefix.$this->post_user_table.' WHERE submissionid=%d', $cpcff_submission_id) );

						// deactivate_id, id of the submission row to deactivate
						$this->deactivate_id = $cpcff_submission_id;

						if(
							!empty( $user_submission ) &&
							($this->is_admin || $this->user_id == $user_submission->userid )
						)
						{
							// user_id, id of current user
							$this->user_id = $user_submission->userid;

							// Patch to preserves the files uploaded by previous submissions.
							$_prev = CPCFF_SUBMISSIONS::get($cpcff_submission_id);
							foreach($_POST as $index => $value)
							{
								if(preg_match('/fieldname\d+_\d+_patch/', $index))
								{
									$parts = explode('_', $index);
									$field_name = $parts[0];
									if(isset($_prev->paypal_post[$field_name]))
									{
										$params[$field_name] = $_prev->paypal_post[$field_name];
										if(isset($fields[$field_name]))
										{
											$title = $fields[$field_name]->title;
											$_to_replace = '/'.preg_quote($title.':').'[^\\n](\\n)+/';
											$_replacement = $title.": ".$params[$field_name]."\n"; // FROM \n\n to \n
											$str = preg_replace($_to_replace, $_replacement, $str);
										}
									}
									if(isset($_prev->paypal_post[$field_name.'_links']))
										$params[$field_name.'_links'] = $_prev->paypal_post[$field_name.'_links'];
									if(isset($_prev->paypal_post[$field_name.'_link']))
										$params[$field_name.'_link'] = $_prev->paypal_post[$field_name.'_link'];
									if(isset($_prev->paypal_post[$field_name.'_urls']))
										$params[$field_name.'_urls'] = $_prev->paypal_post[$field_name.'_urls'];
									if(isset($_prev->paypal_post[$field_name.'_url']))
										$params[$field_name.'_url'] = $_prev->paypal_post[$field_name.'_url'];

								}
							}

							if($this->is_admin)
							{
								// Enter the previous ip address and not the administrator ip
								$params[ 'ipaddress' ] = $_prev->paypal_post['ipaddress'];
								remove_all_actions('cpcff_process_data_before_insert');
							}
						}
					}
				}
			}
		} // End before_insert

		/**
         * Associate the submitted information to the user
         */
        public function	insert_update( $params )
		{
			global $wpdb;
			if( isset( $params[ 'itemnumber' ] ) )
			{
				if(!empty($this->deactivate_id)) $this->deactivate($this->deactivate_id);
				if(!empty($this->user_id))
				{
					@$wpdb->insert(
						$wpdb->prefix.$this->post_user_table,
						array( 'submissionid' => $params[ 'itemnumber' ], 'userid' => $this->user_id, 'active' => 1 ),
						array( '%d', '%d',  '%d')
					);
				}

			}
		} // End insert

		/**
         * Deactivate an user-submission entry
         */
        public function	deactivate( $submission_id )
		{
			global $wpdb;
			@$wpdb->update(
				$wpdb->prefix.$this->post_user_table,
				array( 'active' => 0),
				array( 'submissionid' => $submission_id),
				'%d', '%d'
			);
            do_action('cff_deactivate_post', $submission_id);
		} // End deactivate

		/**
         * Delete an user-submission entry
         */
        public function	delete( $submission_id )
		{
			global $wpdb;
			@$wpdb->delete(
				$wpdb->prefix.$this->post_user_table,
				array( 'submissionid' => $submission_id),
				'%d'
			);
		} // End delete

		/**
		 * Replaces the shorcode to display the list of submission related with an user
		 */
		public function replace_shortcode( $atts, $content = '' )
		{
			wp_enqueue_style('cff-users-permissions-addon', plugins_url('/users.addon/css/styles.css', __FILE__));
			if( (!empty( $atts[ 'id' ] ) || !empty( $atts[ 'login' ] )) && $this->logged_user_id )
			{
				if(
					!empty( $atts[ 'id' ] ) &&
					( $_user_id = intval( @$atts[ 'id' ] ) ) !== 0 &&
					get_user_by( 'ID', $_user_id ) !== false
				)
				{
					$user_id = $_user_id;
				}
				elseif(
					!empty( $atts[ 'login' ] ) &&
					( $_user_obj = get_user_by( 'login', trim( $atts[ 'login' ] ) ) ) !== false
				)
				{
					$user_id = $_user_obj->ID;
				}
			}
			else
			{
				$user_id = get_current_user_id();
			}

			if( !empty( $user_id ) )
			{
				$this->user_id = $user_id;
				global $wpdb;
				$formid = !empty($atts['form']) ? @intval($atts['form']) : (!empty($atts['formid']) ? @intval($atts['formid']) : 0);
				$cond = "";

				// $atts['from']
				if(
					(
						isset($atts['from']) &&
						($from = trim($atts['from'])) !== ''
					) ||
					(
						$this->is_admin &&
						isset($_REQUEST['from']) &&
						($from = sanitize_text_field($_REQUEST['from'])) !== ''
					)
				) $cond .= $wpdb->prepare(" AND (`time` >= %s)", $from);

				// $atts['to']
				if(
					(
						isset($atts['to']) &&
						($to = trim($atts['to'])) !== ''
					) ||
					(
						$this->is_admin &&
						isset($_REQUEST['to']) &&
						($to = sanitize_text_field($_REQUEST['to'])) !== ''
					)

				) $cond .= $wpdb->prepare(" AND (`time` <= %s)", $to.' 23:59:59');

				// $atts['order']
				$cond .= " ORDER BY time ";
				if(
					isset($atts['order']) &&
					($order = trim($atts['order'])) !== '' &&
					($order = strtoupper($order)) === 'ASC'
				) 	 $cond .= "ASC";
				else $cond .= "DESC";

				// For pagination
				$current_page = 0;
				$events_per_page = 0;
				$limit = 0;
				$page_links = '';
				$filters = ''; // Used only with the administrator for filtering by forms and users.

				// $atts['limit'] minimum between limit and events per page
				if( isset($atts['limit']) )
				{
					$limit = @intval($atts['limit']);
					$total = $limit;
				}

				// $atts['events_per_page']
				if( isset( $_GET[ 'events_page' ] ) )
				{
					$current_page = @intval( $_GET[ 'events_page' ] );
					unset( $_GET[ 'events_page' ] );
				}

				if(!empty($atts['events_per_page'])) $events_per_page = @intval( $atts['events_per_page'] );
				else $events_per_page = $this->events_per_page;

				$events_per_page = max( $events_per_page, 1 );
				$limit = ($limit) ? min($limit-max($current_page - 1, 0 )*$events_per_page, $events_per_page) : $events_per_page;

				if( !empty($limit) )
				$cond .= $wpdb->prepare(" LIMIT %d,%d", max($current_page - 1, 0 )*$events_per_page, $limit);

				if($this->is_admin) // The administrator can load the entries of every user
				{
					$search = '';
					$_user_id = 0;

					if(isset($_REQUEST['search'])) $search = sanitize_text_field($_REQUEST['search']);
					if(!empty($search))
						$cond = $wpdb->prepare(" AND submission.paypal_post LIKE %s", '%'.$wpdb->esc_like($search).'%').$cond;
					if(isset($_REQUEST['formid']) && is_numeric($_REQUEST['formid'])) $formid = @intval($_REQUEST['formid']);
					if(isset($_REQUEST['userid']) && is_numeric($_REQUEST['userid']) && ($_user_id = @intval($_REQUEST['userid'])) != 0)
						$cond = $wpdb->prepare(" AND user_submission.userid=%d", $_user_id).$cond;

					// GENERATING THE FILTERS

					$filters .= '<form class="cff-users-permissions-filter">';

					// Search
					$filters .= '<div>'.__('Search by', 'calculated-fields-form').': <input type="text" name="search" value="'.esc_attr($search).'" /></div>';

					// Get all forms
					$filters .= '<div>'.__('Form', 'calculated-fields-form').': <select name="formid" ><option value="0">-</option>';
					$forms = $wpdb->get_results( "SELECT id,form_name FROM ".$wpdb->prefix.CP_CALCULATEDFIELDSF_FORMS_TABLE." ORDER BY form_name ASC");
					foreach( $forms as $_form)
					{
						$filters .= '<option value="'.esc_attr($_form->id).'" '.($_form->id == $formid ? 'SELECTED':'').'>'.esc_html($_form->form_name).'</option>';
					}
					$filters .= '</select></div>';

					// Get all users
					$users = get_users( array( 'fields' => array( 'ID', 'display_name' ), 'orderby' => 'display_name' ) );
					$filters .= '<div>'.__('User', 'calculated-fields-form').': <select name="userid"><option value="0">-</option>';
					foreach( $users as $_user )
					{
						$filters .= '<option value="'.esc_attr($_user->ID).'" '.($_user->ID == $_user_id ? 'SELECTED':'').'>'.esc_html($_user->display_name).'</option>';
					}
					$filters .= '</select></div><div><input type="submit" class="button-primary" style="margin-left:10px;" value="'.esc_attr(__('Filter', 'calculated-fields-form')).'" /></div></form>';

					$events = $wpdb->get_results(
						"SELECT SQL_CALC_FOUND_ROWS * FROM ".CP_CALCULATEDFIELDSF_POSTS_TABLE_NAME." AS submission LEFT JOIN ".$wpdb->prefix.$this->post_user_table." AS user_submission ON (submission.id=user_submission.submissionid) WHERE user_submission.active=1 ".((!empty($formid)) ? " AND submission.formid=".$formid : "").$cond
					);
				}
				else // Loads only the entries of logged user
				{
					$events = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT SQL_CALC_FOUND_ROWS * FROM ".CP_CALCULATEDFIELDSF_POSTS_TABLE_NAME." as submission, ".$wpdb->prefix.$this->post_user_table." as user_submission WHERE ".((!empty($formid)) ? "submission.formid=".$formid." AND " : "")." submission.id=user_submission.submissionid AND user_submission.userid=%d AND user_submission.active=1 ".$cond,
							$user_id
						)
					);
				}

				// Get total records for pagination
				$total = (!empty($total)) ? MIN($wpdb->get_var( "SELECT FOUND_ROWS()" ), $total) : $wpdb->get_var("SELECT FOUND_ROWS()");
				$total_pages = ceil($total/$events_per_page);

				if( $total )
				{
					$_forms = $wpdb->get_results( "SELECT id,form_name, form_structure FROM ".$wpdb->prefix.CP_CALCULATEDFIELDSF_FORMS_TABLE." AS formsettings".((!empty($formid)) ? " WHERE formsettings.id=".$formid : "") );
					$forms = array();
					foreach($_forms as $_form )
					{
						$forms[ $_form->id ] = array( 'name' => $_form->form_name, 'structure' => $_form->form_structure );
					}

					$_GET[ 'events_page' ] = '%_%';
					$page_links = paginate_links(
									array(
										'format'       	=> '?events_page=%#%',
										'total'        	=> $total_pages,
										'current'      	=> $current_page,
										'show_all'     	=> True,
										'add_args'     => False
									)
								);

					return 	'<div class="wrap">'.$filters.$page_links.$this->user_messages_list($events, $forms, $content) .$page_links.'</div>';
				}
				else
				{
					return '<div class="wrap">'.$filters.'<div>'.__( 'The list of submissions is empty', 'calculated-fields-form' ).'</div></div>';
				}
			}
			else
			{
				return '';
			}
		} // End replace_shortcode

		/************************ MESSAGES & CSV SECTION ************************/

		/**
         * Modifies the query of messages for including the information of users
         */
        public function	messages_query( $query )
		{
			global $wpdb;

			if( preg_match( '/DISTINCT/i', $query ) == 0 )
			{
				$query = preg_replace( '/SELECT/i', 'SELECT DISTINCT ', $query );
			}

			$query = preg_replace( '/WHERE/i', ' LEFT JOIN ('.$wpdb->prefix.$this->post_user_table.' as user_submission LEFT JOIN '.$wpdb->users.' as user ON user_submission.userid=user.ID) ON user_submission.submissionid='.CP_CALCULATEDFIELDSF_POSTS_TABLE_NAME.'.id WHERE', $query );

			if(
				!empty( $_REQUEST[ 'cpcff_addon_user_username' ] ) &&
				($username = sanitize_text_field(trim($_REQUEST[ 'cpcff_addon_user_username' ]))) !== ''
			)
			{
				$username = '%'.$username.'%';
				$query = preg_replace(
					'/WHERE/i',
					$wpdb->prepare(
						'WHERE (user.user_login LIKE %s OR user.user_nicename LIKE %s) AND ',
						array( $username, $username )
					),
					$query
				);
			}

			return $query;
		} // End messages_query

		/**
         * Modifies the query of CSV for including the information of users and remove the inactive rows
         */
        public function csv_query($query)
		{
			global $wpdb;
			$query = $this->messages_query($query);
			$query = str_replace(' ORDER BY ', ' AND ('.CP_CALCULATEDFIELDSF_POSTS_TABLE_NAME.'.id NOT IN (SELECT submissionid as id FROM '.$wpdb->prefix.$this->post_user_table.' WHERE active = 0)) ORDER BY ', $query);
			return $query;
		} // End csv_query

		/**
		 * Modifies the query of results list to exclude the inactive submissions for update or delete
		 */
		public function results_list_query($query)
		{
			global $wpdb;
			$query = str_replace(' ORDER BY ', ' AND (id NOT IN (SELECT submissionid as id FROM '.$wpdb->prefix.$this->post_user_table.' WHERE active = 0)) ORDER BY ', $query);
			return $query;
		} // End results_list_query

		/**
         * Print new <TH> tags for the header section for the table of messages.
         */
        public function	messages_header()
		{
			print '<TH style="padding-left:7px;font-weight:bold;">'.__( 'Registered User', 'calculated-fields-form' ).'</TH><TH style="padding-left:7px;font-weight:bold;">'.__( 'Status', 'calculated-fields-form' ).'</TH>';
		} // End messages_header

		/**
         * Print new <TD> tags with the users data in the table of messages.
         */
        public function	messages_data( $data )
		{
			$user = '';
			$status = '';
			$css = '';
			$data = (array)$data;
			if( !empty( $data[ 'userid' ] ) )
			{
				$user = '<a href="'.get_edit_user_link( $data[ 'userid' ] ).'" target="_blank">'.$data[ 'display_name' ].'</a>';
				if(intval($data['active']) == 1)
				{
					$status = __('Active', 'calculated-fields-form');
					$css = 'text-align:center;font-weight:bold;background-color:#DCFFFB;';
				}
				else
				{
					$status = __('Disabled by user action', 'calculated-fields-form');
					$css = 'text-align:center;font-weight:bold;background-color:#DC143C;color:white;';
				}
			}
			else
			{
				$status = __('Anonymous user', 'calculated-fields-form');
				$css = 'text-align:center;font-weight:bold;background-color:#FFFFD1;';
			}
			print '<TD>'.$user.'</TD><TD style="'.esc_attr($css).'">'.$status.'</TD>';
		} // End messages_data

		/**
		 * Adds a new button to edit the submission visually
		 */
		public function visual_edition_button($buttons, $data)
		{
			$data = (array)$data;
			if(!empty($data['userid']) && @intval($data['active']))
			{
				$search 	= '<input type="button" name="caldelete_';
				$onclick	= '(function(submission){
					var w = screen.width*0.8,
						h = screen.height*0.7,
						l = screen.width/2 - w/2,
						t = screen.height/2 - h/2,
						new_window = window.open("", "formpopup", "resizeable,scrollbars,width="+w+",height="+h+",left="+l+",top="+t);

					jQuery("#cpcff_addon_user_edit_form").remove();
					jQuery("body").append( "<form id=\'cpcff_addon_user_edit_form\' method=\'GET\'  action=\''.esc_attr(CPCFF_AUXILIARY::site_url()).'\' target=\'formpopup\'><input type=\'hidden\' name=\'cpcff_addon_user_edit\' value=\'"+submission+"\'></form>" );
					jQuery("#cpcff_addon_user_edit_form").submit();
				})('.$data['id'].');';
				$newbutton 	= '<input type="button" name="caldelete_'.$data['id'].'" value="'.esc_attr__('New and Disable', 'calculated-fields-form').'" onclick="'.esc_attr($onclick).'" class="button-secondary" />';
				$buttons = str_replace($search, $newbutton.$search, $buttons);
			}
			return $buttons;
		} // End visual_edition_button

		/**
         * Includes new fields for filtering in the messages section
         */
        public function	messages_filters()
		{
			print '<div style="display:inline-block; white-space:nowrap; margin-right:20px;">'.__( 'Username', 'calculated-fields-form' ).': <input type="text" id="cpcff_addon_user_username" name="cpcff_addon_user_username" value="'.esc_attr( ( !empty( $_REQUEST[ 'cpcff_addon_user_username' ] ) ) ? sanitize_text_field($_REQUEST[ 'cpcff_addon_user_username' ]) : '' ).'" /></div>';

		} // End messages_filters

		/**
		 *	Delete the form from the addon's table
		 */
        public function delete_form($formid)
		{
			delete_option( $this->var_name.'_'.$formid );
		} // delete_form

		/**
		 *	Clone the form's row
		 */
		public function clone_form( $original_form_id, $new_form_id )
		{
			global $wpdb;
			$settings = $this->get_form_settings($original_form_id);
			if(!empty($settings))
			{
				update_option( $this->var_name.'_'.$new_form_id, $settings );
			}
		} // End clone_form

		/**
		 *	It is called when the form is exported to export the addons data too.
		 *  Receive an array with the other addons data, and the form's id for filtering.
		 */
		public function export_form($addons_array, $formid)
		{
			$settings = $this->get_form_settings($formid);
			if(!empty($settings))
			{
				$addons_array[ $this->addonID ] = $settings;
			}
			return $addons_array;
		} // End export_form

		/**
		 *	It is called when the form is imported to import the addons data too.
		 *  Receive an array with all the addons data, and the new form's id.
		 */
		public function import_form($addons_array, $formid)
		{
			if(isset($addons_array[$this->addonID]))
				update_option( $this->var_name.'_'.$formid, $addons_array[$this->addonID] );

		} // End import_form

    } // End Class

    // Main add-on code
    $cpcff_users_obj = new CPCFF_Users();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cpcff_users_obj);
}