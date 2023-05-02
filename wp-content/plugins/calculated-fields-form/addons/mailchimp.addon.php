<?php
/*
....
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPCFF_MailChimp' ) )
{
    class CPCFF_MailChimp extends CPCFF_BaseAddon
    {
		static public $category = 'External Services';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-mailchimp-20160504";
		protected $name = "CFF - MailChimp";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#mailchimp-addon';

		public function get_addon_form_settings( $form_id )
		{
			global $wpdb;

			// Insertion in database
			if(
				isset( $_REQUEST[ 'cpcff_mailchimp_list_id' ] ) &&
				isset( $_REQUEST[ 'cpcff_mailchimp_api_key' ] )
			)
			{
				$data = array(
					'status_if_new' => 'pending',
					'fields' => array(),
					'groups' => array()
				);

				if(isset($_REQUEST[ 'cpcff_mailchimp_status_if_new' ]) && $_REQUEST[ 'cpcff_mailchimp_status_if_new' ] == 'subscribed') $data['status_if_new'] = 'subscribed';

                if(isset($_REQUEST[ 'cpcff_mailchimp_attr' ]))
                {
                    foreach( $_REQUEST[ 'cpcff_mailchimp_attr' ] as $key => $attr )
                    {
                        $attr = trim( $attr );
                        if( !empty( $attr ) ) $data[ 'fields' ][ $key ] = $attr;
                    }
                }

				if(isset($_REQUEST[ 'cpcff_mailchimp_gpr' ]))
                {
                    foreach( $_REQUEST[ 'cpcff_mailchimp_gpr' ] as $key => $group )
                    {
                        $data[ 'groups' ][ $key ] = array(
                            'title' => $_REQUEST[ 'cpcff_mailchimp_gpr_title' ][ $key ],
                            'structure' => $_REQUEST[ 'cpcff_mailchimp_gpr_structure' ][ $key ]
                        );
                    }
                }

				$data['members_tags'] = array();
				if(isset($_REQUEST['cpcff_mailchimp_members_tags']))
				{
					$tmp_members_tags = trim($_REQUEST['cpcff_mailchimp_members_tags']);
					$tmp_members_tags = explode(',', $tmp_members_tags);
					foreach($tmp_members_tags as $tmp_tag)
					{
						$tmp_tag = trim($tmp_tag);
						if(!empty($tmp_tag)) $data['members_tags'][] = $tmp_tag;
					}
				}

				$wpdb->delete( $wpdb->prefix.$this->form_mailchimp_table, array( 'formid' => $form_id ), array( '%d' ) );
				$wpdb->insert( 	$wpdb->prefix.$this->form_mailchimp_table,
								array(
									'formid' 	=> $form_id,
									'api_key'	=> trim( $_REQUEST[ 'cpcff_mailchimp_api_key' ] ),
									'list_id'   => trim( $_REQUEST[ 'cpcff_mailchimp_list_id' ] ),
									'data'	 	=> serialize( $data )
								),
								array( '%d', '%s', '%s', '%s' )
							);
			}

			$api_key = '';
			$list_id = '';
			$data 	 = array();
			$groups  = array();
			$row 	 = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_mailchimp_table." WHERE formid=%d", $form_id ) );
			$status_if_new = 'pending';
			$members_tags = array();
			if( $row )
			{
				$api_key 	= $row->api_key;
				$list_id 	= $row->list_id;
				if( ( $tmp = @unserialize( $row->data ) ) != false )
				{
					if(isset($tmp['status_if_new']) && $tmp['status_if_new'] == 'subscribed') $status_if_new = 'subscribed';
					$data = ( isset( $tmp[ 'fields' ] ) ) ? $tmp[ 'fields' ] : $tmp;
					if ( isset( $tmp[ 'groups' ] ) ) $groups = $tmp[ 'groups' ];
					if ( isset( $tmp[ 'members_tags' ] ) ) $members_tags = $tmp[ 'members_tags' ];
				}
			}

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<div id="metabox_mailchimp_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_mailchimp_addon_form_settings' ) ); ?>" >
				<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
				<div class="inside">
					<table cellspacing="0" width="100%">
						<tr>
							<td style="white-space:nowrap;width:100px;"><?php _e('API KEY', 'calculated-fields-form');?>:</td>
							<td width="100%"><input type="text" name="cpcff_mailchimp_api_key" value="<?php echo esc_attr( $api_key ); ?>" style="width:100%;" /></td>
							<td><input type="button" value="<?php echo esc_attr( __( 'Get Lists', 'calculated-fields-form' ) ); ?>" onclick="cpcff_mailchimp_getList();" class="button-secondary" style="min-width:150px;" /></td>
						</tr>
						<tr>
							<td style="white-space:nowrap;width:100px;"><?php _e('List ID', 'calculated-fields-form');?>:</td>
							<td width="100%">
								<input type="text" name="cpcff_mailchimp_list_id" readonly value="<?php echo esc_attr( $list_id ); ?>" style="width:100%;" />
							</td>
							<td><input type="button" value="<?php echo esc_attr( __( 'Get Fields and Groups', 'calculated-fields-form' ) ); ?>" onclick="cpcff_mailchimp_getFields();" class="button-secondary" style="min-width:150px;" /></td>
						</tr>
						<tr>
							<td></td>
							<td><div class="cpcff-mailchimp-list-container"></div></td>
							<td></td>
						</tr>
						<tr><td colspan="3"><strong><?php _e('Form Fields', 'calculated-fields-form');?>:</strong></td></tr>
						<tr>
							<td colspan="3">
								<table class="cpcff-mailchimp-fields-container" cellpadding="3">
								<?php
									if( !empty( $data ) )
									{
										foreach( $data as $attr => $value )
										{
											print '<tr>
											<td>'.$attr.'</td>
											<td><input type="text" name="cpcff_mailchimp_attr['.esc_attr($attr).']" value="'.esc_attr( $value ).'" placeholder="fieldname#" /></td>
											</tr>';
										}
									}
									else
									{
										print '<tr><td colspan="2">'.__( 'There are not fields selected', 'calculated-fields-form').'</td></tr>';
									}
								?>
								</table>
							</td>
						</tr>
						<tr><td colspan="3"><strong><?php _e('Groups', 'calculated-fields-form');?>:</strong></td></tr>
						<tr>
							<td colspan="3">
								<table class="cpcff-mailchimp-groups-container">
								<?php
									if( !empty( $groups ) )
									{
										foreach( $groups as $grp => $obj )
										{
											$obj[ 'structure' ] = stripcslashes( $obj[ 'structure' ] );
											print '
											<tr>
												<td valign="top">
													<input type="checkbox" name="cpcff_mailchimp_gpr['.esc_attr($grp).']" CHECKED />'.$obj[ 'title' ].'
													<input type="hidden" name="cpcff_mailchimp_gpr_title['.esc_attr($grp).']" value="'.esc_attr( $obj[ 'title' ] ).'" />
												</td>
												<td class="cpcff-mailchimp-group-container">
													<input type="hidden" name="cpcff_mailchimp_gpr_structure['.esc_attr($grp).']" value="'.esc_attr( $obj[ 'structure' ] ).'" />
													'.$obj[ 'structure' ].'
												</td>
											</tr>';
										}
									}
									else
									{
										print '<tr><td colspan="2">'.__( 'There are not groups selected', 'calculated-fields-form').'</td></tr>';
									}
								?>
								</table>
							</td>
						</tr>
						<tr>
							<td style="white-space:nowrap;width:100px;"><?php _e('Members Tags', 'calculated-fields-form');?>:</td>
							<td colspan="2">
								<input type="text" name="cpcff_mailchimp_members_tags" value="<?php echo esc_attr(implode(',',$members_tags)); ?>" style="width:100%;" />
							</td>
						</tr>
						<tr>
							<td style="white-space:nowrap;width:100px;"><?php _e('Status If New', 'calculated-fields-form');?>:</td>
							<td colspan="2">
								<select name="cpcff_mailchimp_status_if_new">
									<option value="pending" <?php if($status_if_new == 'pending') print 'SELECTED'; ?>><?php print esc_html(__('Pending', 'calculated-fields-form')); ?></option>
									<option value="subscribed" <?php if($status_if_new == 'subscribed') print 'SELECTED'; ?>><?php print esc_html(__('Subscribed', 'calculated-fields-form')); ?></option>
								</select>
							</td>
						</tr>
					</table>
                    <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
				</div>
				<script>
					var cpcff_mailchimp_texts = {
							'invalid_api_key' : '<?php print esc_js( __( 'Invalid API KEY', 'calculated-fields-form' ) ); ?>',
							'select_list' 	  : '<?php print esc_js( __( 'Select a List', 'calculated-fields-form' ) ); ?>',
							'required_list_id': '<?php print esc_js( __( 'Select a List ID', 'calculated-fields-form' ) ); ?>',
							'no_list'		  : '<?php print esc_js( __( 'Create at least a list in your MailChimp account', 'calculated-fields-form' ) ); ?>',
							'no_fields'		  : '<?php print esc_js( __( 'There are not fields selected', 'calculated-fields-form' ) ); ?>',
							'no_groups'		  : '<?php print esc_js( __( 'There are not groups selected', 'calculated-fields-form' ) ); ?>'
						},
						cpcff_mailchimp_nonce = '<?php print esc_js( wp_create_nonce( "cpcff_mailchimp_nonce" ) ); ?>';
				</script>
			</div>
			<?php
		}

		/************************ ADDON CODE *****************************/
        /************************ ATTRIBUTES *****************************/

		private $form_mailchimp_table = 'cp_calculated_fields_form_mailchimp';
		private $mailchimp_url = 'https://%s.api.mailchimp.com/3.0/lists/%s/members';
		private $mailchimp_user = 'cpcff-mailchimp-addon';

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = __("The add-on creates MailChimp List members with the submitted information", 'calculated-fields-form' );
            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;

			// Call actions
			add_action( 'init', array( &$this, 'init' ), 1 );

            add_action( 'cpcff_the_form', array( &$this, 'add_groups' ), 10, 2 );

			// Load resources
            add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_scripts' ), 10 );

			// Create the member
			add_action( 'cpcff_process_data', array( &$this, 'create_member' ) );

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
			$db_queries = array( "CREATE TABLE ".$wpdb->prefix.$this->form_mailchimp_table." (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					formid INT NOT NULL,
					api_key VARCHAR(250) DEFAULT '' NOT NULL,
					list_id VARCHAR(250) DEFAULT '' NOT NULL,
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
				isset($_REQUEST[ 'cpcff_mailchimp_nonce' ]) &&
				wp_verify_nonce( $_REQUEST[ 'cpcff_mailchimp_nonce' ], 'cpcff_mailchimp_nonce' )
			)
			{
				$result = $this->mailchimp_actions( $_REQUEST );
				if( $result !== false )
				{
					print $result;
					exit;
				}
			}
		}

		/**
		 * Modifies the form structure to includes the fields for groups
		 */
		public function add_groups( $form_str, $id )
		{
			global $wpdb;

			$data  = $wpdb->get_var( $wpdb->prepare( "SELECT data FROM ".$wpdb->prefix.$this->form_mailchimp_table." WHERE formid=%d", $id ) );

			if( $data && ( $tmp = @unserialize( $data ) ) != false )
			{
				if ( isset( $tmp[ 'groups' ] ) )
				{
					foreach( $tmp[ 'groups' ] as $key => $group )
					{
						$tmp[ 'groups' ][ $key ][ 'structure' ] = stripcslashes( $group[ 'structure' ] );
					}
					$form_str .='<pre style="display:none;"><script>mailchimp_groups_'.CPCFF_MAIN::$form_counter.'='.json_encode( $tmp[ 'groups' ] ).';</script></pre>';

					// Enqueue the script that insert the groups into the form's structure
					wp_enqueue_script( 'cpcff_mailchimp_addon_js', plugins_url('/mailchimp.addon/js/public_scripts.js',  __FILE__), array( 'jquery' ) );
				}
			}
			return $form_str;
		}

        /**
         * Contact the MailChimp service
         */
		public function mailchimp_actions( $data )
		{
			if( isset( $data[ 'cpcff_mailchimp_action' ]  ) )
			{
				if(
					!empty( $data[ 'api_key' ] ) &&
					preg_match( '/(.*)\-(us\d+)$/i', $data[ 'api_key' ], $parts )
				)
				{
					$args = array(
									'headers' => array( 'Authorization' => 'Basic '.base64_encode( $this->mailchimp_user.':'.$parts[1] )  )
								);
					switch( strtolower( trim( $data[ 'cpcff_mailchimp_action' ] ) ) )
					{
						case 'cpcff_mailchimp_get_lists':
							$url = "https://{$parts[2]}.api.mailchimp.com/3.0/lists?count=300";
							$response = wp_remote_get(
											$url,
											$args
										);
						break;
						case 'cpcff_mailchimp_get_fields':
							if( !empty( $data[ 'list_id' ] ) && ($list_id = trim( $data[ 'list_id' ] ) ) !== '' )
							{
								$results = array();

								// ******** GET FIELDS ***********
								$response_fields = wp_remote_get( "https://{$parts[2]}.api.mailchimp.com/3.0/lists/{$list_id}/merge-fields?count=1000", $args );

								if( !is_wp_error( $response_fields ) && $response_fields['response']['code'] == 200 )
								{
									$_fields = json_decode( $response_fields[ 'body' ] );
									if( $_fields !== false )
									{
										$fields = array();
										foreach( $_fields->merge_fields as $_field )
										{
											$fields[] = $_field->tag;
										}

										if( !empty( $fields ) )
										{
											$results[ 'fields' ] = $fields;
										}
									}	// End if( $_fields !== false )
								}

								// ******** GET GROUPS ***********
								$response_groups = wp_remote_get( "https://{$parts[2]}.api.mailchimp.com/3.0/lists/{$list_id}/interest-categories?count=300", $args );
								if( !is_wp_error( $response_groups ) && $response_groups['response']['code'] == 200 )
								{
									$groups = array();

									$_groups = json_decode( $response_groups[ 'body' ] );
									if( $_groups !== false )
									{

										foreach( $_groups->categories as $_group )
										{
											$group  = array();
											// if( $_group->type == 'hidden' ) continue;

											// ******** GET INTERESTS ***********
											$response_interests = wp_remote_get( "https://{$parts[2]}.api.mailchimp.com/3.0/lists/{$list_id}/interest-categories/".$_group->id."/interests", $args );
											if( !is_wp_error( $response_interests ) && $response_interests['response']['code'] == 200 )
											{
												$_interests = json_decode( $response_interests[ 'body' ] );
												if( $_interests !== false )
												{
													$_output = '';
													switch( strtolower( $_group->type ) )
													{
														case 'hidden':
														case 'checkboxes':
															foreach( $_interests->interests as $_interest )
															{
																$_output .= '<label><input value="'.esc_attr($_interest->id).'" name="'.esc_attr($_group->id).'[]" type="checkbox" /> '.esc_html($_interest->name).'</label><br />';
															}
														break;
														case 'radio':
															foreach( $_interests->interests as $_interest )
															{
																$_output .= '<label><input value="'.esc_attr($_interest->id).'" name="'.esc_attr($_group->id).'" type="radio" /> '.esc_html($_interest->name).'</label><br />';
															}
														break;
														case 'dropdown':
															$_output .= '<select name="'.esc_attr($_group->id).'"><option value="">'.__('Please select...', 'calculated-fields-form').'</option>';
															foreach( $_interests->interests as $_interest )
															{
																$_output .= '<option value="'.esc_attr($_interest->id).'">'.esc_html($_interest->name).'</option>';
															}
															$_output .= '</select>';
														break;
													}  // End switch

													if( !empty( $_output ) )
													{
														$group[ 'title' ] 			= $_group->title;
														$group[ 'id' ] 				= $_group->id;
														$group[ 'interests' ] 		= $_output;
													}
												}
											}

											if( !empty( $group ) ) $groups[] = $group;
										}
									} // End if( $_groups !== false )

									if( !empty( $groups ) )
									{
										$results[ 'groups' ] = $groups;
									}
								}
								if( !empty( $results ) )
								{
									return json_encode( $results );
								}
								else
								{
									return '{"error":"Not field or group detected in the list"}';
								}
								exit;
							}
							else
							{
								return '{"error":"Invalid List Id"}';
							}
						break;
						case 'cpcff_mailchimp_create_member':
							if( !empty( $data[ 'list_id' ] ) && !empty( $data[ 'data' ]) )
							{
								try
								{
									$url = "https://{$parts[2]}.api.mailchimp.com/3.0/lists/".trim( $data[ 'list_id' ] )."/members/".md5( $data[ 'data' ][ 'email_address' ]);
									$args[ 'method' ] = 'PUT';
									$args[ 'headers' ][ 'Content-Type' ] = 'application/json';
									$args[ 'body' ] = json_encode( $data[ 'data' ] );
									$response = wp_remote_request(
													$url,
													$args
												);

									if(!is_wp_error($response))
									{
										$body = wp_remote_retrieve_body($response);
										$arr = json_decode($body, true);
										if($arr !== false)
										{
											if(!empty($arr['id']))
											{
												unset($data['data']['status_if_new']);
												// unset($data['data']['merge_fields']);
												$args['method'] = 'PATCH';
												$args['body'] = json_encode($data['data']);
												$response = wp_remote_request(
													$url,
													$args
												);

												if(!empty($data['data']['tags']))
												{
													// Update tags
													$body = array(
														'tags' => array()
													);

													foreach($data['data']['tags'] as $_tag_name)
													{
														$body[ 'tags' ][] = array(
															'name' => $_tag_name,
															'status' => 'active'
														);
													}

													$url .= '/tags';
													$args['method'] = 'POST';
													$args['body'] = json_encode($body);
													$response = wp_remote_request(
														$url,
														$args
													);
												}
											}
										}
									}
								}
								catch (Exception $err)
								{
									error_log('CFF MailChimp Integration Error: '.$err->getMessage());
								}
							}
						break;
					}

					if( !empty( $response ) )
					{
						if ( wp_remote_retrieve_response_code( $response ) == 200 ) {
							return $response[ 'body' ];
						}
						else
						{
							return '{"error":"Error Connecting with MailChimp"}';
						}
					}
				}
				else
				{
					return '{"error":"Invalid API Key"}';
				}
			}
			return false;
		} // End mailchimp_actions

		/**
         * Enqueue all resources: CSS and JS files, required by the Addon
         */
        public function enqueue_scripts()
        {
			wp_enqueue_script( 'cpcff_mailchimp_addon_js', plugins_url('/mailchimp.addon/js/scripts.js',  __FILE__), array( 'jquery', 'jquery-ui-autocomplete' ) );

        } // End enqueue_scripts

		/**
         * Create the MailChimp member
         */
        public function	create_member( $params )
		{
			global $wpdb;

			$form_id = @intval( $params[ 'formid' ] );
			if( $form_id )
			{
				$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_mailchimp_table." WHERE formid=%d", $form_id ) );
				if( $row && !empty( $row->api_key ) && !empty( $row->list_id ) )
				{
					$data = array(
						'status_if_new' => 'pending',
						'merge_fields' => array()
					);

					if( ( $_data = unserialize( $row->data ) ) !== false )
					{

						if(isset($_data['status_if_new']) && $_data['status_if_new'] == 'subscribed')
							$data['status_if_new'] = 'subscribed';

						$attrs = ( isset ( $_data[ 'fields' ] ) ) ? $_data[ 'fields' ] : $_data;
						foreach( $attrs as $key => $value )
						{
							if( $key == 'email_address' )
								$data[ $key ] = ( isset( $params[ $value ] ) ) ? $params[ $value ] : $value;
							$data[ 'merge_fields' ][ $key ] = ( isset( $params[ $value ] ) ) ? $params[ $value ] : $value;
						}

						$groups = ( isset( $_data[ 'groups' ] ) ) ? $_data[ 'groups' ] : array();
						$interests = array();
						foreach( $groups as $key => $group )
						{
							if( !empty( $_REQUEST[ $key ] ) )
							{
								if( is_array( $_REQUEST[ $key ] ) )
								{
									foreach( $_REQUEST[ $key ] as $value )
									{
										if( strpos( $group[ 'structure' ], $value ) !== false )
										{
											$interests[ $value ] = true;
										}
									}
								}
								else
								{
									if( strpos( $group[ 'structure' ], $_REQUEST[ $key ] ) !== false )
									{
										$interests[ $_REQUEST[ $key ] ] = true;
									}
								}
							}
						}

						if( !empty( $interests ) )
						{
							$data[ 'interests' ] = $interests;
						}

						if(!empty($_data['members_tags']))
						{
							$data[ 'tags' ] = $_data['members_tags'];
						}

						$args = array(
							'api_key' => $row->api_key,
							'list_id' => $row->list_id,
							'cpcff_mailchimp_action' => 'cpcff_mailchimp_create_member',
							'data' => $data
						);


						$this->mailchimp_actions( $args );
					}
				}
			}
		} // End create_member

		/**
		 *	Delete the form from the addon's table
		 */
        public function delete_form( $formid)
		{
			global $wpdb;
			$wpdb->delete( $wpdb->prefix.$this->form_mailchimp_table, array('formid' => $formid), '%d' );
		} // delete_form

		/**
		 *	Clone the form's row
		 */
		public function clone_form( $original_form_id, $new_form_id )
		{
			global $wpdb;

			$form_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_mailchimp_table." WHERE formid=%d", $original_form_id ), ARRAY_A);

			if(!empty($form_row))
			{
				unset($form_row["id"]);
				$form_row["formid"] = $new_form_id;
				$wpdb->insert( $wpdb->prefix.$this->form_mailchimp_table, $form_row);
			}
		} // End clone_form

		/**
		 *	It is called when the form is exported to export the addons data too.
		 *  Receive an array with the other addons data, and the form's id for filtering.
		 */
		public function export_form($addons_array, $formid)
		{
			global $wpdb;
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_mailchimp_table." WHERE formid=%d", $formid ), ARRAY_A );
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
					$wpdb->prefix.$this->form_mailchimp_table,
					$addons_array[$this->addonID]
				);
			}
		} // End import_form

    } // End Class

    // Main add-on code
    $cpcff_mailchimp_obj = new CPCFF_MailChimp();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cpcff_mailchimp_obj);
}
?>