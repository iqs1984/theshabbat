<?php
/*
....
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPCFF_WebMerge' ) )
{
    class CPCFF_WebMerge extends CPCFF_BaseAddon
    {
		static public $category = 'External Services';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-webmerge-20160321";
		protected $name = "CFF - WebMerge";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#webmerge-addon';

		public function get_addon_form_settings( $form_id )
		{
			global $wpdb;

			// Insertion in database
			if( isset( $_REQUEST[ 'cpcff_webmerge' ] ) )
			{
				$wpdb->delete( $wpdb->prefix.$this->form_webmerge_table, array( 'formid' => $form_id ), array( '%d' ) );

				if( !empty( $_REQUEST[ 'cpcff_webmerge_url' ] ) && is_array( $_REQUEST[ 'cpcff_webmerge_url' ] ) )
				{
					foreach( $_REQUEST[ 'cpcff_webmerge_url' ] as $dIndex => $url )
					{
						$url = trim( $url );
						if( !empty( $url ) )
						{
							$data = array( 'process_after_payment' => false );
							if( !empty( $_REQUEST[ 'cpcff_webmerge_process_after_payment' ][ $dIndex ] ) )
								$data[ 'process_after_payment' ] = true;

							if(
								isset( $_REQUEST[ 'cpcff_webmerge_field' ] ) &&
								!empty( $_REQUEST[ 'cpcff_webmerge_field' ][ $dIndex ] ) &&
								is_array( $_REQUEST[ 'cpcff_webmerge_field' ][ $dIndex ] )
							)
							{
								foreach( $_REQUEST[ 'cpcff_webmerge_field' ][ $dIndex ] as $fIndex => $merge_field )
								{
									$merge_field = trim( $merge_field );
									if(
										!empty( $merge_field ) &&
										isset( $_REQUEST[ 'cpcff_webmerge_form_field' ] ) &&
										!empty( $_REQUEST[ 'cpcff_webmerge_form_field' ][ $dIndex ] ) &&
										!empty( $_REQUEST[ 'cpcff_webmerge_form_field' ][ $dIndex ][ $fIndex ] )
									)
									{
										$form_field = trim( $_REQUEST[ 'cpcff_webmerge_form_field' ][ $dIndex ][ $fIndex ] );

										if( !empty( $form_field ) )
										{
											$data[ $merge_field ] = $form_field;
										}
									}
								}
							}

							$wpdb->insert(
								$wpdb->prefix.$this->form_webmerge_table,
								array(
									'formid' 	=> $form_id,
									'url'		=> $url,
									'data'		=> serialize( $data )
								),
								array( '%d', '%s', '%s' )
							);
						}
					}
				}
			}

			$rows = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_webmerge_table." WHERE formid=%d", $form_id )
					);

			$cpcff_main = CPCFF_MAIN::instance();
			?>

			<div id="metabox_webmerge_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_webmerge_addon_form_settings' ) ); ?>" >
				<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
				<div class="inside">
					<table cellspacing="3px" style="width:100%;">
						<?php
							for( $i = 0; $i < count($rows); $i++ )
							{
								$process_after_payment = false;
								$row = $rows[ $i ];
								$data = @unserialize( $row->data );
								print '
									<tr class="webmerge-document" data-counter="'.$i.'">
										<td style="border:1px solid #DADADA;">
											<table cellpadding="3px" width="100%">
												<tr>
													<td class="white-space:nowrap;">'.__( 'Document URL', 'calculated-fields-form' ).':</td>
													<td style="width:100%;"><input type="text" name="cpcff_webmerge_url['.$i.']" value="'.esc_attr( $row->url ).'" class="width100" ></td>
												</tr>
								';
								if( $data !== false )
								{
									if( isset( $data[ 'process_after_payment' ] ) )
									{
										if( $data[ 'process_after_payment' ] === true ) $process_after_payment = true;
										unset( $data[ 'process_after_payment' ] );
									}

								}
								print '
									<tr>
										<td colspan="2">'.__( 'Calling to WebMerge after processing the payment', 'calculated-fields-form').': <input type="checkbox" name="cpcff_webmerge_process_after_payment['.$i.']" '.(($process_after_payment) ? 'CHECKED' : '').' /></td>
									</tr>
								';
								if( $data !== false )
								{
									$counter = 0;
									foreach( $data as $webmerge_field => $form_field )
									{
										print '
											<tr class="webmerge-field" data-counter="'.$counter.'">
												<td>'.__('Field', 'calculated-fields-form').':</td>

												<td>
													<input type="text" name="cpcff_webmerge_field['.$i.']['.$counter.']" value="'.$webmerge_field.'" placeholder="'.esc_attr(__( 'WebMerge Field', 'calculated-fields-form' )).'" />

													<input type="text" name="cpcff_webmerge_form_field['.$i.']['.$counter.']" value="'.$form_field.'" placeholder="'.esc_attr(__( 'Form Field', 'calculated-fields-form' )).'" />

													<input type="button" value="[ X ]" onclick="cpcff_webmerge_removeField( this );" class="button-secondary" />
												</td>
											</tr>
										';
										$counter++;
									}
								}

								print '
												<tr><td colspan="2"><input type="button" class="button-primary" value="'.esc_attr(__('Add Field', 'calculated-fields-form')).'" onclick="cpcff_webmerge_addField( this );" /><input type="button" class="button-secondary" value="'.esc_attr(__('Remove Document', 'calculated-fields-form')).'" onclick="cpcff_webmerge_removeDocument( this );" style="margin-left:10px;" /></td></tr>
											</table>
										</td>
									</tr>
								';
							}
						?>
						<tr>
							<td style="padding-top:10px;">
								<input type="button" class="button-primary" value="<?php esc_attr_e('Add a New Document', 'calculated-fields-form');?>" onclick="cpcff_webmerge_addDocument(this);" />
							</td>
						</tr>
					</table>
                    <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
				</div>
				<input type="hidden" name="cpcff_webmerge" value="1" />
				<script>
					function cpcff_webmerge_addDocument( e )
					{
						try
						{
							var $ = jQuery,
								r = $( '.webmerge-document:last' );
								c = (r.length) ? r.data( 'counter' )+1 : 0;

							$(e).closest( 'tr' )
								.before(
									'<tr class="webmerge-document" data-counter="'+c+'"><td style="border:1px solid #DADADA;"><table cellpadding="3px;" width="100%"><tr><td style="white-space:nowrap;"><?php _e( 'Document URL', 'calculated-fields-form' ); ?>:</td><td style="width:100%;"><input type="text" name="cpcff_webmerge_url['+c+']" value="" class="width100" ></td></tr><tr><td colspan="2"><?php _e( 'Calling to WebMerge after processing the payment', 'calculated-fields-form');?>: <input type="checkbox" name="cpcff_webmerge_process_after_payment['+c+']" /></td></tr><tr><td colspan="2"><input type="button" class="button-primary" value="<?php esc_attr_e(__('Add Field', 'calculated-fields-form')); ?>" onclick="cpcff_webmerge_addField( this );" /><input type="button" class="button-secondary" value="<?php esc_attr_e(__('Remove Document', 'calculated-fields-form')); ?>" onclick="cpcff_webmerge_removeDocument( this );" style="margin-left:10px;" /></td></tr></table></td></tr>'
								);
						}
						catch( err ){}
					}

					function cpcff_webmerge_removeDocument( e )
					{
						try
						{
							jQuery( e ).closest( '.webmerge-document' ).remove();
						}
						catch( err ){}
					}

					function cpcff_webmerge_addField( e )
					{
						try
						{
							var $ = jQuery,
								e = $(e),
								i = e.closest( '.webmerge-document' ).data( 'counter' ),
								r = e.closest( 'table' ).find( '.webmerge-field' ).last();
								c = (r.length) ? r.data( 'counter' )+1 : 0;

							$(e).closest( 'tr' )
								.before(
									'<tr class="webmerge-field" data-counter="'+c+'"><td><?php _e( 'Field', 'calculated-fields-form' ); ?>:</td><td><input type="text" name="cpcff_webmerge_field['+i+']['+c+']" value="" placeholder="<?php esc_attr_e(__( 'WebMerge Field', 'calculated-fields-form' )); ?>" /><input type="text" name="cpcff_webmerge_form_field['+i+']['+c+']" value="" placeholder="<?php esc_attr_e(__( 'Form Field', 'calculated-fields-form' )); ?>" /><input type="button" value="[ X ]" onclick="cpcff_webmerge_removeField( this );" class="button-secondary" /></td></tr>'
								);
						}
						catch( err ){}
					}

					function cpcff_webmerge_removeField( e )
					{
						try
						{
							jQuery( e ).closest( '.webmerge-field' ).remove();
						}
						catch( err ){}
					}

				</script>
			</div>
			<?php
		}

		/************************ ADDON CODE *****************************/
        /************************ ATTRIBUTES *****************************/

		private $form_webmerge_table = 'cp_calculated_fields_form_webmerge';

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = __("The add-on submits the collected information to the WebMerge service, to generate a document (PDF, or Word Document)", 'calculated-fields-form');

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
			$db_queries = array( "CREATE TABLE ".$wpdb->prefix.$this->form_webmerge_table." (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					formid INT NOT NULL,
					url VARCHAR(250) DEFAULT '' NOT NULL,
					data text,
					UNIQUE KEY id (id)
				) $charset_collate;"
			);
			$this->_run_update_database($db_queries);
		} // End update_database

        /************************ PRIVATE METHODS *****************************/
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
			if( !empty( $form_id ) )
			{
				$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_webmerge_table." WHERE formid=%d", $form_id ) );

				foreach( $rows as $row )
				{
					$processed_params = array();

					if( !empty( $row->data ) && ($data = @unserialize( $row->data ) ) !== false )
					{
						if(
							!empty($data['process_after_payment']) &&
							$payment_processed == false ||
							empty($data['process_after_payment']) &&
							$payment_processed == true
						) continue;

						unset($data[ 'process_after_payment' ]);

						foreach( $data as $merge_field => $form_field )
						{
							$form_field = trim($form_field);
							$processed_params[ $merge_field ] = (isset($params[$form_field])) ? $params[ $form_field ] : ((preg_match('/fieldname\d+/', $form_field)) ? '' : $form_field);
						}
					}

					if(empty($processed_params)) $processed_params = $params;
					$args = array(
						'headers' 	=> array('content-type' => 'application/json'),
						'body' 		=> json_encode( $processed_params ),
						'timeout' 	=> 45,
						'sslverify'	=> false,
					);

					$result = wp_remote_post( $row->url, $args );
				}
			}
		} // End export_lead

		/**
		 *	Delete the form from the addon's table
		 */
        public function delete_form( $formid)
		{
			global $wpdb;
			$wpdb->delete( $wpdb->prefix.$this->form_webmerge_table, array('formid' => $formid), '%d' );
		} // delete_form

		/**
		 *	Clone the form's row
		 */
		public function clone_form( $original_form_id, $new_form_id )
		{
			global $wpdb;

			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_webmerge_table." WHERE formid=%d", $original_form_id ), ARRAY_A);

			if(!empty($rows))
			{
				foreach($rows as $row)
				{
					unset($row["id"]);
					$row["formid"] = $new_form_id;
					$wpdb->insert( $wpdb->prefix.$this->form_webmerge_table, $row);
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
			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_webmerge_table." WHERE formid=%d", $formid ), ARRAY_A );
			if(!empty( $rows ))
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
							$wpdb->prefix.$this->form_webmerge_table,
							$row
						);
					}
				}
			}
		} // End import_form

    } // End Class

    // Main add-on code
    $cpcff_webmerge_obj = new CPCFF_WebMerge();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cpcff_webmerge_obj);
}
?>