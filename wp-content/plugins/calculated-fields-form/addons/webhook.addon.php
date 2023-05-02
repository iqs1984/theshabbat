<?php
/*
....
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPCFF_WebHook' ) )
{
    class CPCFF_WebHook extends CPCFF_BaseAddon
    {
		static public $category = 'External Services';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-webhook-20150403";
		protected $name = "CFF - WebHook";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#webhook-addon';

		public function get_addon_form_settings( $form_id )
		{
			global $wpdb;

			// Insertion in database
			if( isset( $_REQUEST[ 'cpcff_webhook' ] ) )
			{
				$wpdb->delete( $wpdb->prefix.$this->form_webhook_table, array( 'formid' => $form_id ), array( '%d' ) );
				if( isset( $_REQUEST[ 'cpcff_webhook_url' ] ) )
				{
					$updated_database = $this->_backward_compatibiliy();
					foreach( $_REQUEST[ 'cpcff_webhook_url' ] as $key => $url )
					{
						$url = trim( $url );
						if( !empty( $url ) )
						{
							$data = array(
								'all_fields' => (isset($_REQUEST[ 'cpcff_webhook_all_fields' ][$key])) ? true : false,
								'process_after_payment' => (isset($_REQUEST[ 'cpcff_webhook_process_after_payment' ][$key])) ? true : false,
								'fields' => array()
							);
							if(
								isset($_REQUEST['cpcff_webhook_field']) &&
								isset($_REQUEST['cpcff_webhook_field'][$key]) &&
								is_array($_REQUEST['cpcff_webhook_field'][$key])
							)
							{
								foreach( $_REQUEST['cpcff_webhook_field'][$key] as $field_key=>$webhook_field )
								{
									$webhook_field = trim($webhook_field);
									if(
										!empty($webhook_field) &&
										isset($_REQUEST['cpcff_webhook_form_field'][$key][$field_key]) &&
										($form_field = trim($_REQUEST['cpcff_webhook_form_field'][$key][$field_key])) != ''
									)
									{
										$data['fields'][$webhook_field]=$form_field;
									}
								}
							}
							if($updated_database)
							{
								$wpdb->insert(
									$wpdb->prefix.$this->form_webhook_table,
									array(
										'formid' => $form_id,
										'url'	 => $url,
										'data'	 => serialize($data)
									),
									array( '%d', '%s', '%s' )
								);
							}
							else
							{
								$wpdb->insert(
									$wpdb->prefix.$this->form_webhook_table,
									array(
										'formid' => $form_id,
										'url'	 => $url
									),
									array( '%d', '%s' )
								);
							}
						}
					}
				}
			}

			$rows = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_webhook_table." WHERE formid=%d", $form_id )
					);

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<div id="metabox_webhook_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_webhook_addon_form_settings' ) ); ?>" >
				<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
				<div class="inside">
					<table style="width:100%;" cellspacing="3">
						<?php
							$cpcff_webhook_url_counter = 0;
							$cpcff_webhook_fields_counter = 0;
							foreach( $rows as $row )
							{
								$fields = array();
								$all_fields = true;
								$process_after_payment = false;
								if(
									isset($row->data) &&
									($data = unserialize($row->data)) !== false
								)
								{
									$all_fields = $data['all_fields'];
									if(!empty($data['fields']) && is_array($data['fields'])) $fields = $data['fields'];
									if(
										isset( $data[ 'process_after_payment' ] ) &&
										$data[ 'process_after_payment' ] === true
									)
									$process_after_payment = true;
								}
								print '
									<tr data-webhook="'.$cpcff_webhook_url_counter.'">
										<td style="border:1px solid #DADADA;">
											<table cellpadding="3px" width="100%">
												<tr>
													<td style="white-space:nowrap;width:100px;">'.__('WebHook URL','calculated-fields-form').':</td>
													<td><input type="text" name="cpcff_webhook_url['.$cpcff_webhook_url_counter.']" value="'.esc_attr( $row->url ).'" class="width100" /></td>
												</tr>
												<tr>
													<td colspan="2">'.__( 'Calling the WebHook after processing the payment', 'calculated-fields-form').': <input type="checkbox" name="cpcff_webhook_process_after_payment['.$cpcff_webhook_url_counter.']" '.(($process_after_payment) ? 'CHECKED' : '').' /></td>
												</tr>
												<tr>
													<td colspan="2">'.__( "Send all form's fields plus the next ones", "calculated-fields-form" ).': <input name="cpcff_webhook_all_fields['.$cpcff_webhook_url_counter.']" type="checkbox" '.(($all_fields) ? 'CHECKED': '').' /></td>
												</tr>
												<tr>
													<td colspan="2">
														<table class="fields-container">';

								foreach($fields as $key => $value)
								{
									print '
															<tr data-webhook-field="'.$cpcff_webhook_fields_counter.'">
																<td style="white-space:nowrap;width:100px;">'.__('Field', 'calculated-fields-form').':</td>
																<td>
																	<input type="text" name="cpcff_webhook_field['.$cpcff_webhook_url_counter.']['.$cpcff_webhook_fields_counter.']" value="'.esc_attr( $key ).'" placeholder="WebHook Field" />
																	<input type="text" name="cpcff_webhook_form_field['.$cpcff_webhook_url_counter.']['.$cpcff_webhook_fields_counter.']" value="'.esc_attr( $value ).'" placeholder="Form Field" />
																	<input type="button" value="[ X ]" onclick="cpcff_webhook_removeField('.$cpcff_webhook_fields_counter.');" class="button-secondary" />
																</td>
															</tr>';
									$cpcff_webhook_fields_counter++;
								}
								print'
														</table>
													</td>
												</tr>
												<tr>
													<td colspan="2">
														<input type="button" value="'.__('Add field', 'calculated-fields-form').'" onclick="cpcff_webhook_addField('.$cpcff_webhook_url_counter.');" class="button-primary" />
														<input type="button" value="'.__('Remove URL', 'calculated-fields-form').'" onclick="cpcff_webhook_removeURL('.$cpcff_webhook_url_counter.');" class="button-secondary" style="margin-left:10px;" />
													</td>
												</tr>
											</table>
										</td>
									</tr>
								';
								$cpcff_webhook_url_counter++;
							}
						?>
						<tr>
							<td colspan="2" style="padding-top:10px;">
								<input type="button" value="<?php esc_attr_e('Add new URL', 'calculated-fields-form');?>" onclick="cpcff_webhook_addURL( this );" class="button-primary" />
							</td>
						</tr>
					</table>
                    <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
				</div>
				<input type="hidden" name="cpcff_webhook" value="1" />
				<script>
					var cpcff_webhook_url_counter = <?php print $cpcff_webhook_url_counter; ?>,
						cpcff_webhook_fields_counter = <?php print $cpcff_webhook_fields_counter; ?>;

					function cpcff_webhook_addURL( e )
					{
						try
						{
							jQuery( e )
							.closest( 'tr' )
							.before(
								'<tr data-webhook="'+cpcff_webhook_url_counter+'"><td style="border:1px solid #DADADA;">'+
								'<table cellpadding="3px" width="100%">'+
								'<tr><td style="white-space:nowrap;width:100px;"><?php print esc_js(__('WebHook URL', 'calculated-fields-form')); ?>:</td>'+
								'<td><input name="cpcff_webhook_url['+cpcff_webhook_url_counter+']" value="" type="text" class="width100"></td></tr>'+
								'<tr><td colspan="2"><?php print esc_js(__( 'Calling the WebHook after processing the payment', 'calculated-fields-form')); ?>: <input type="checkbox" name="cpcff_webhook_process_after_payment['+cpcff_webhook_url_counter+']" /></td></tr>'+
								'<tr><td colspan="2"><?php print esc_js(__( "Send all form's fields plus the next ones", "calculated-fields-form" )); ?>: <input name="cpcff_webhook_all_fields['+cpcff_webhook_url_counter+']" type="checkbox" checked /></td></tr>'+
								'<tr><td colspan="2"><table class="fields-container"></table></td></tr>'+
								'<tr><td colspan="2"><input type="button" value="<?php print esc_js(__('Add field', 'calculated-fields-form')); ?>" onclick="cpcff_webhook_addField('+cpcff_webhook_url_counter+');" class="button-primary" /><input type="button" value="<?php print esc_js(__('Remove URL', 'calculated-fields-form')); ?>" onclick="cpcff_webhook_removeURL('+cpcff_webhook_url_counter+');" class="button-secondary" style="margin-left:10px;" /></td></tr>'+
								'</table></td></tr>'
							);
						}
						catch( err ){}
						cpcff_webhook_url_counter++;
					}

					function cpcff_webhook_removeURL( id )
					{
						jQuery( '[data-webhook="'+id+'"]' ).remove();
					}

					function cpcff_webhook_addField( id )
					{
						jQuery( '[data-webhook="'+id+'"] .fields-container' ).append(
							'<tr data-webhook-field="'+cpcff_webhook_fields_counter+'">'+
							'<td style="white-space:nowrap;width:100px;"><?php print esc_js(__('Field', 'calculated-fields-form')); ?>:</td>'+
							'<td>'+
							'<input type="text" name="cpcff_webhook_field['+id+']['+cpcff_webhook_fields_counter+']" value="" placeholder="WebHook Field" />'+
							'<input type="text" name="cpcff_webhook_form_field['+id+']['+cpcff_webhook_fields_counter+']" value="" placeholder="Form Field" />'+
							'<input type="button" value="[ X ]" onclick="cpcff_webhook_removeField('+cpcff_webhook_fields_counter+');" class="button-secondary" /></td></tr>'
						);
						cpcff_webhook_fields_counter++;
					}

					function cpcff_webhook_removeField( id )
					{
						jQuery( '[data-webhook-field="'+id+'"]' ).remove();
					}
				</script>
			</div>
			<?php
		}

		/************************ ADDON CODE *****************************/
        /************************ ATTRIBUTES *****************************/

		private $form_webhook_table = 'cp_calculated_fields_form_webhook';

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = __("The add-on allows put the submitted information to a webhook URL, and integrate the forms with the Zapier service", 'calculated-fields-form');

            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;

			// Export the lead
			add_action( 'cpcff_process_data', array( &$this, 'cpcff_process_data_action' ) );
			add_action( 'cpcff_payment_processed', array( &$this, 'cpcff_payment_processed_action' ) );

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
			$db_queries = array( "CREATE TABLE ".$wpdb->prefix.$this->form_webhook_table." (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					formid INT NOT NULL,
					url VARCHAR(250) DEFAULT '' NOT NULL,
					data TEXT,
					UNIQUE KEY id (id)
				) $charset_collate;"
			);
			$this->_run_update_database($db_queries);
		} // End update_database

        /************************ PRIVATE METHODS *****************************/

		private function _backward_compatibiliy()
		{
			global $wpdb;
			$charset_collate = $wpdb->get_charset_collate();
			$charset_collate_column = preg_replace('/default/i', ' ', $charset_collate);

			// For compatibility with previous versions of the add-on
			$row = 	$wpdb->get_row("SHOW COLUMNS FROM `".$wpdb->prefix.$this->form_webhook_table."` LIKE 'data'");
			if( empty($row) )
				return $wpdb->query("ALTER TABLE `".$wpdb->prefix.$this->form_webhook_table."` ADD COLUMN data TEXT $charset_collate_column");
			return true;
		}

		/************************ PUBLIC METHODS  *****************************/

		/**
         * Process the cpcff_process_data action
         */
		public function cpcff_process_data_action( $params )
		{
			$this->put_data( $params, false );
		}

		/**
         * Process the cpcff_payment_processed action
         */
		public function cpcff_payment_processed_action( $params )
		{
			$this->put_data( $params, true );
		}

		/**
         * Put data to webhooks URLs
         */
        public function	put_data( $params, $payment_processed )
		{
			global $wpdb;

			$form_id = @intval( $params[ 'formid' ] );
			if( $form_id )
			{
				$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_webhook_table." WHERE formid=%d", $form_id ) );

				if(!empty($rows) && is_array( $params ))
				{
					$form_obj = CPCFF_SUBMISSIONS::get_form($params['itemnumber']);
					$submission_obj = CPCFF_SUBMISSIONS::get($params['itemnumber']);

					$fields = $form_obj->get_fields();
					$fields[ 'ipaddr' ] = $submission_obj->ipaddr;
					$fields[ 'submission_datetime' ] = $submission_obj->time;
					$fields[ 'paid' ] = $submission_obj->paid;

					foreach( $params as $key => $val )
					{
						try
						{
							$tmp = is_array($val) ?
								array_map(
									function($v){
										$tmp = @json_decode($v);
										return empty($tmp) ? $v : $tmp;
									}, $val
								) :
								@json_decode($val);

							if( !empty($tmp) ) $params[ $key ] = $tmp;
						}
						catch( Exception $err )
						{

						}
					}

					foreach( $rows as $row )
					{
						if(
							!empty($row->data) &&
							($data = unserialize($row->data)) !== false
						)
						{
							if(
								!empty( $data[ 'process_after_payment' ] ) &&
								$payment_processed == false ||
								empty( $data[ 'process_after_payment' ] ) &&
								$payment_processed == true
							)
							{
								continue;
							}

							unset($data[ 'process_after_payment' ]);

							if($data['all_fields']) $selected_params = $params;
							if(!empty($data['fields']) && is_array($data['fields']))
							{
								foreach( $data['fields'] as $field => $value )
								{
									$value = trim($value);
									$selected_params[ $field ] = (isset($params[$value])) ? $params[ $value ] : ((preg_match('/fieldname\d+/', $value)) ? '' : $value);
									if($selected_params[ $field ] == $value && preg_match('/^<%.+%>$/', $value))
									{
										$replacement = CPCFF_AUXILIARY::parsing_fields_on_text(
											$fields,
											$params,
											$value,
											'',
											'html',
											$params['itemnumber']
										);

										$selected_params[ $field ] = $replacement['text'];
									}
								}
							}
						}

						if(empty($selected_params)) $selected_params = $params;
						$args = array(
							'headers' 	=> array('content-type' => 'application/json'),
							'body' 		=> json_encode( $selected_params ),
							'timeout' 	=> 45,
							'sslverify'	=> false,
						);

						$result = wp_remote_post( $row->url, $args );
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
			$wpdb->delete( $wpdb->prefix.$this->form_webhook_table, array('formid' => $formid), '%d' );
		} // delete_form

		/**
		 *	Clone the form's row
		 */
		public function clone_form( $original_form_id, $new_form_id )
		{
			global $wpdb;

			$form_rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_webhook_table." WHERE formid=%d", $original_form_id ), ARRAY_A);

			if(!empty($form_rows))
			{
				foreach($form_rows as $form_row)
				{
					unset($form_row["id"]);
					$form_row["formid"] = $new_form_id;
					$wpdb->insert( $wpdb->prefix.$this->form_webhook_table, $form_row);
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
			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_webhook_table." WHERE formid=%d", $formid ), ARRAY_A );
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
							$wpdb->prefix.$this->form_webhook_table,
							$row
						);
					}
				}
			}
		} // End import_form

    } // End Class

    // Main add-on code
    $cpcff_webhook_obj = new CPCFF_WebHook();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cpcff_webhook_obj);
}
?>