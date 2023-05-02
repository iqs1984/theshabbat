<?php
/*
....
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPCFF_Emma' ) )
{
    class CPCFF_Emma extends CPCFF_BaseAddon
    {
		static public $category = 'Third Party Plugins';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-emma-20160321";
		protected $name = "CFF - Emma";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#emma-addon';

		public function get_addon_settings()
		{
			if( isset( $_REQUEST[ 'cpcff_emma' ] ) )
			{
				check_admin_referer( $this->addonID, '_cpcff_nonce' );
				update_option( 'cpcff_emma_account_id', trim( $_REQUEST[ 'cpcff_emma_account_id' ] ) );
				update_option( 'cpcff_emma_public_api_key', trim( $_REQUEST[ 'cpcff_emma_public_api_key' ] ) );
				update_option( 'cpcff_emma_private_api_key', trim( $_REQUEST[ 'cpcff_emma_private_api_key' ] ) );
			}

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<form method="post" action="<?php print esc_url(admin_url('admin.php?page=cp_calculated_fields_form')); ?>">
				<div id="metabox_emma_addon_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_emma_addon_settings' ) ); ?>" >
					<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
					<div class="inside">
						<table cellspacing="0" style="width:100%;">
							<tr>
								<td style="white-space:nowrap;width:200px;"><?php _e('Account ID', 'calculated-fields-form');?>:</td>
								<td>
									<input type="text" name="cpcff_emma_account_id" value="<?php echo ( ( $account_id = get_option( 'cpcff_emma_account_id' ) ) !== false ) ? esc_attr($account_id) : ''; ?>"  style="width:80%;" />
								</td>
							</tr>
							<tr>
								<td style="white-space:nowrap;width:200px;"><?php _e('Public API Key', 'calculated-fields-form');?>:</td>
								<td>
									<input type="text" name="cpcff_emma_public_api_key" value="<?php echo ( ( $public_key = get_option( 'cpcff_emma_public_api_key' ) ) !== false ) ? esc_attr($public_key) : ''; ?>"  style="width:80%;" />
								</td>
							</tr>
							<tr>
								<td style="white-space:nowrap;width:200px;"><?php _e('Private API Key', 'calculated-fields-form');?>:</td>
								<td>
									<input type="text" name="cpcff_emma_private_api_key" value="<?php echo ( ( $private_key = get_option( 'cpcff_emma_private_api_key' ) ) !== false ) ? esc_attr($private_key) : ''; ?>"  style="width:80%;" />
								</td>
							</tr>
						</table>
						<input type="submit" name="Save settings" class="button-secondary" />
					</div>
					<input type="hidden" name="cpcff_emma" value="1" />
					<input type="hidden" name="_cpcff_nonce" value="<?php echo wp_create_nonce( $this->addonID ); ?>" />
				</div>
			</form>
			<?php
		}

		public function get_addon_form_settings( $form_id )
		{
			global $wpdb;

			// Insertion in database
			if( isset( $_REQUEST[ 'cpcff_emma' ] ) )
			{
				$wpdb->delete( $wpdb->prefix.$this->form_emma_table, array( 'formid' => $form_id ), array( '%d' ) );

				$data = array(
					'fields' => array(),
					'groups' => array()
				);

				if(
					!empty( $_REQUEST[ 'cpcff_emma_field' ] ) &&
					is_array( $_REQUEST[ 'cpcff_emma_field' ] )
				)
				{
					foreach( $_REQUEST[ 'cpcff_emma_field' ] as $fIndex => $emma_field )
					{
						$emma_field = trim( $emma_field );
						if(
							!empty( $emma_field ) &&
							isset( $_REQUEST[ 'cpcff_emma_form_field' ] ) &&
							!empty( $_REQUEST[ 'cpcff_emma_form_field' ] ) &&
							!empty( $_REQUEST[ 'cpcff_emma_form_field' ][ $fIndex ] )
						)
						{
							$form_field = trim( $_REQUEST[ 'cpcff_emma_form_field' ][ $fIndex ] );

							if( !empty( $form_field ) )
							{
								$data['fields'][ $emma_field ] = $form_field;
							}
						}
					}
				}

				if(
					!empty( $_REQUEST[ 'cpcff_emma_group_id' ] ) &&
					is_array( $_REQUEST[ 'cpcff_emma_group_id' ] )
				)
				{
					foreach( $_REQUEST[ 'cpcff_emma_group_id' ] as $gIndex => $emma_group )
					{
						$emma_group = trim( $emma_group );
						if( !empty( $emma_group ) )
						{
							$data['groups'][ $emma_group ] = (isset($_REQUEST['cpcff_emma_group_name']) && is_array($_REQUEST['cpcff_emma_group_name']) && isset($_REQUEST['cpcff_emma_group_name'][$gIndex])) ? $_REQUEST['cpcff_emma_group_name'][$gIndex] : '';
						}
					}
				}

				$wpdb->insert(
					$wpdb->prefix.$this->form_emma_table,
					array(
						'formid' 	=> $form_id,
						'data'		=> serialize( $data )
					),
					array( '%d', '%s' )
				);
			}

			$row = $wpdb->get_row(
				$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_emma_table." WHERE formid=%d", $form_id )
			);
			$data = array();

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<div id="metabox_emma_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_emma_addon_form_settings' ) ); ?>" >
				<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
				<div class="inside">
					<table cellspacing="3px" style="width:100%;" class="cpcff_emma">
						<?php
						$emma_email_field = '';
						$emma_fields = '';
						if(!empty($row))
						{
							$data = $row->data;
							if(
								empty($data) ||
								($data = unserialize($data)) == false
							)
							{
								$data = array();
							}
						}
						if( !empty($data) && !empty($data['fields']))
						{

							foreach( $data['fields'] as $emma_field => $form_field )
							{
								if($emma_field == 'email')
								{
									$emma_email_field = $form_field;
									continue;
								}
								$emma_fields .= '
									<tr>
										<td>'.__('Field', 'calculated-fields-form').':</td>
										<td>
											<input type="text" name="cpcff_emma_field[]" value="'.esc_attr($emma_field).'" readonly />
											<input type="text" name="cpcff_emma_form_field[]" value="'.esc_attr($form_field).'" placeholder="fieldname#" />
											<input type="button" value="[ X ]" onclick="cpcff_emma_removeField( this );" class="button-secondary" />
										</td>
									</tr>
								';
							}
						}
						?>
						<tr>
							<td><?php _e('Field', 'calculated-fields-form');?>(<?php _e('Required', 'calculated-fields-form');?>):</td>
							<td>
								<input type="text" name="cpcff_emma_field[]" value="email" readonly />
								<input type="text" name="cpcff_emma_form_field[]" value="<?php esc_attr_e($emma_email_field); ?>" placeholder="fieldname#" />
								<input type="button" value="[ X ]" onclick="cpcff_emma_removeField( this );" class="button-secondary" />
							</td>
						</tr>
						<?php print $emma_fields; ?>
						<tr><td colspan="2"><input type="button" class="button-primary" value="<?php esc_attr_e(__('Get Fields', 'calculated-fields-form')); ?>" onclick="cpcff_emma_getFields( this );" /></td></tr>
						<?php
						if( !empty($data) && !empty($data['groups']))
						{
							foreach( $data['groups'] as $group_id => $group_name )
							{
								print '
									<tr>
										<td>'.__('Group', 'calculated-fields-form').':</td>
										<td>
											<input type="hidden" name="cpcff_emma_group_id[]" value="'.esc_attr($group_id).'" />
											<input type="text" name="cpcff_emma_group_name[]" value="'.esc_attr($group_name).'" readonly />
											<input type="button" value="[ X ]" onclick="cpcff_emma_removeGroup( this );" class="button-secondary" />
										</td>
									</tr>
								';
							}
						}
						?>
						<tr><td colspan="2"><input type="button" class="button-primary" value="<?php esc_attr_e(__('Get Groups', 'calculated-fields-form')); ?>" onclick="cpcff_emma_getGroups( this );" /></td></tr>
					</table>
                    <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
				</div>
				<input type="hidden" name="cpcff_emma" value="1" />
				<script>
					var cpcff_emma_nonce = '<?php print esc_js( wp_create_nonce( "cpcff_emma_nonce" ) ); ?>';

					function cpcff_emma_escattr(s)
					{
						return ('' + s)
							.replace(/&/g, '&amp;')
							.replace(/'/g, '&apos;')
							.replace(/"/g, '&quot;')
							.replace(/</g, '&lt;')
							.replace(/>/g, '&gt;');
					}

					function cpcff_emma_getFields( e )
					{
						try
						{
							var $ = jQuery,
								url= document.location.href,
								e = $(e);

							$.getJSON(
								url,
								{
									'cpcff_emma_nonce'  : cpcff_emma_nonce,
									'cpcff_emma_action' : 'cpcff_emma_get_fields'
								},
								function(data)
								{
									if( typeof data[ 'error' ] != 'undefined' )
									{
										alert( data[ 'error' ] );
									}
									else
									{
										for( var i in data )
										{
											if( $('.cpcff_emma [value="'+data[i]['shortcut_name']+'"]').length ) continue;
											$(e).closest( 'tr' )
												.before(
													'<tr><td><?php print esc_js(__( 'Field', 'calculated-fields-form' )); ?>'+((data[i]['required']) ? '(<?php print esc_js(__( 'Required', 'calculated-fields-form' )); ?>)' : '')+':</td><td><input type="text" name="cpcff_emma_field[]" value="'+cpcff_emma_escattr(data[i]['shortcut_name'])+'" readonly /> <input type="text" name="cpcff_emma_form_field[]" value="" placeholder="fieldname#" /> <input type="button" value="[ X ]" onclick="cpcff_emma_removeField( this );" class="button-secondary" /></td></tr>'
												);
										}

									}
								}
							);
						}
						catch( err ){
							if(typeof console != 'undefined' ) console.log(err);
						}
					}

					function cpcff_emma_getGroups( e )
					{
						try
						{
							var $ = jQuery,
								url= document.location.href,
								e = $(e);

							$.getJSON(
								url,
								{
									'cpcff_emma_nonce'  : cpcff_emma_nonce,
									'cpcff_emma_action' : 'cpcff_emma_get_groups'
								},
								function(data)
								{
									if( typeof data[ 'error' ] != 'undefined' )
									{
										alert( data[ 'error' ] );
									}
									else
									{
										for( var i in data )
										{
											if( $('.cpcff_emma [value="'+data[i]['member_group_id']+'"]').length ) continue;
											$(e).closest( 'tr' )
												.before(
													'<tr><td><?php print esc_js(__( 'Group', 'calculated-fields-form' )); ?>'+':</td><td><input type="hidden" name="cpcff_emma_group_id[]" value="'+cpcff_emma_escattr(data[i]['member_group_id'])+'" /> <input type="text" name="cpcff_emma_group_name[]" value="'+cpcff_emma_escattr(data[i]['group_name'])+'" /> <input type="button" value="[ X ]" onclick="cpcff_emma_removeField( this );" class="button-secondary" /></td></tr>'
												);
										}

									}
								}
							);
						}
						catch( err ){
							if(typeof console != 'undefined' ) console.log(err);
						}
					}

					function cpcff_emma_removeField( e )
					{
						try
						{
							jQuery( e ).closest( 'tr' ).remove();
						}
						catch( err ){}
					}
					window['cpcff_emma_removeGroup'] = cpcff_emma_removeField;
				</script>
			</div>
			<?php
		}

		/************************ ADDON CODE *****************************/
        /************************ ATTRIBUTES *****************************/

		private $form_emma_table = 'cp_calculated_fields_form_emma';

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = __("The add-on submits the collected information to the Emma service to add a new member", 'calculated-fields-form');

            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;

			// Create the Emma member
			add_action( 'cpcff_process_data', array( &$this, 'cpcff_process_data_action' ) );

			if( is_admin() )
			{
				add_action( 'init', array(&$this, 'init'), 1 );

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
			$db_queries = array( "CREATE TABLE ".$wpdb->prefix.$this->form_emma_table." (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					formid INT NOT NULL,
					data text,
					UNIQUE KEY id (id)
				) $charset_collate;"
			);
			$this->_run_update_database($db_queries);
		} // End update_database

        /************************ PRIVATE METHODS *****************************/
        /************************ PUBLIC METHODS  *****************************/

		public function init()
		{
			if(
				isset($_REQUEST['cpcff_emma_action']) &&
				isset($_REQUEST['cpcff_emma_nonce']) &&
				wp_verify_nonce( $_REQUEST[ 'cpcff_emma_nonce' ], 'cpcff_emma_nonce' )
			)
			{
				print $this->get_data( $_REQUEST['cpcff_emma_action'] );
				exit;
			}
		} // init

        /**
         * Process the cpcff_process_data action
         */
		public function cpcff_process_data_action( $params )
		{
			$this->put_data( $params );
		}

		/**
         * Put data to webhooks URLs
         */
        public function	put_data( $params )
		{
			global $wpdb;

			$form_id = @intval( $params[ 'formid' ] );
			if( !empty( $form_id ) )
			{
				$cpcff_emma_public_api_key = get_option('cpcff_emma_public_api_key','');
				$cpcff_emma_private_api_key = get_option('cpcff_emma_private_api_key','');
				$cpcff_emma_account_id = get_option('cpcff_emma_account_id','');

				if(
					empty($cpcff_emma_public_api_key) ||
					empty($cpcff_emma_private_api_key) ||
					empty($cpcff_emma_account_id)
				) return;

				$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_emma_table." WHERE formid=%d", $form_id ) );

				if(!empty($row))
				{
					$processed_params = array( 'fields' => array() );

					if( !empty( $row->data ) && ($data = @unserialize( $row->data ) ) !== false )
					{
						if(!empty($data['fields']))
						{
							foreach( $data['fields'] as $emma_field => $form_field )
							{
								$form_field = trim($form_field);
								if($emma_field == 'email')
								{
									$processed_params['email'] = (isset($params[$form_field])) ? $params[ $form_field ] : ((preg_match('/fieldname\d+/', $form_field)) ? '' : $form_field);
								}
								else
								{
									$processed_params['fields'][ $emma_field ] = (isset($params[$form_field])) ? $params[ $form_field ] : ((preg_match('/fieldname\d+/', $form_field)) ? '' : $form_field);
								}
							}

							if( empty($processed_params[ 'email' ]) ) return;
						}
						else
						{
							return;
						}

						if(!empty($data['groups']))
						{
							$groups_ids = array_keys($data['groups']);
							$groups_ids = array_filter($groups_ids);
							if( !empty($groups_ids) ) $processed_params['group_ids'] = $groups_ids;
						}
					}

					$auth = base64_encode( $cpcff_emma_public_api_key.':'.$cpcff_emma_private_api_key );

					$args = array(
						'headers' 	=> array('Authorization' => "Basic $auth", 'content-type' => 'application/json'),
						'body' 		=> json_encode( $processed_params ),
						'timeout' 	=> 45,
						'sslverify'	=> false
					);
					$response = wp_remote_post(
						"https://api.e2ma.net/".$cpcff_emma_account_id."/members/add",
						$args
					);
				}
			}
		} // End put_data

		public function get_data( $action )
		{
			$cpcff_emma_public_api_key = get_option('cpcff_emma_public_api_key','');
			$cpcff_emma_private_api_key = get_option('cpcff_emma_private_api_key','');
			$cpcff_emma_account_id = get_option('cpcff_emma_account_id','');

			if(
				empty($cpcff_emma_public_api_key) ||
				empty($cpcff_emma_private_api_key) ||
				empty($cpcff_emma_account_id)
			)
			{
				return '{"error":"'.esc_js(__('The account id, public key and private key must be defined in the settings page of the plugin', 'calculated-fields-form')).'"}';
			}

			$auth = base64_encode( $cpcff_emma_public_api_key.':'.$cpcff_emma_private_api_key );
			$args = array(
				'headers' 	=> array( 'Authorization' => "Basic $auth" ),
				'timeout' 	=> 45,
				'sslverify'	=> false
			);

			$action = strtolower($action);
			switch( $action )
			{
				case 'cpcff_emma_get_fields':
					$response      = wp_remote_get( "https://api.e2ma.net/".get_option('cpcff_emma_account_id','')."/fields", $args );
				break;
				case 'cpcff_emma_get_groups':
					$response      = wp_remote_get( "https://api.e2ma.net/".get_option('cpcff_emma_account_id','')."/groups", $args );
				break;
			}

			if( is_wp_error( $response ) )
			{
				return '{"error":"'.esc_js($response->get_error_message()).'"}';
			}

			return wp_remote_retrieve_body( $response );
		} // End get_data

		/**
		 *	Delete the form from the addon's table
		 */
        public function delete_form( $formid)
		{
			global $wpdb;
			$wpdb->delete( $wpdb->prefix.$this->form_emma_table, array('formid' => $formid), '%d' );
		} // delete_form

		/**
		 *	Clone the form's row
		 */
		public function clone_form( $original_form_id, $new_form_id )
		{
			global $wpdb;

			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_emma_table." WHERE formid=%d", $original_form_id ), ARRAY_A);

			if(!empty($row))
			{
				unset($row["id"]);
				$row["formid"] = $new_form_id;
				$wpdb->insert( $wpdb->prefix.$this->form_emma_table, $row);
			}
		} // End clone_form

		/**
		 *	It is called when the form is exported to export the addons data too.
		 *  Receive an array with the other addons data, and the form's id for filtering.
		 */
		public function export_form($addons_array, $formid)
		{
			global $wpdb;
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_emma_table." WHERE formid=%d", $formid ), ARRAY_A );
			if(!empty( $row ))
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
			if(!empty($addons_array[$this->addonID]))
			{
				$row = $addons_array[$this->addonID];
				$row['formid'] = $formid;
				$wpdb->insert(
					$wpdb->prefix.$this->form_emma_table,
					$row
				);
			}
		} // End import_form

    } // End Class

    // Main add-on code
    $cpcff_emma_obj = new CPCFF_Emma();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cpcff_emma_obj);
}
?>