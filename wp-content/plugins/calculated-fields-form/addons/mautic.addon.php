<?php
/*
....
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPCFF_Mautic' ) )
{
    class CPCFF_Mautic extends CPCFF_BaseAddon
    {
		static public $category = 'External Services';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-mautic-20191016";
		protected $name = "CFF - Mautic";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#mautic-addon';

		public function get_addon_form_settings( $form_id )
		{
			global $wpdb;

			// Insertion in database
			if(
				isset( $_REQUEST[ 'cpcff_mautic' ] )
			)
			{
				$url   = sanitize_text_field(isset($_REQUEST['cpcff_mautic_url']) ? $_REQUEST['cpcff_mautic_url'] : '' );
				$form  = isset($_REQUEST['cpcff_mautic_form']) ? @intval(sanitize_text_field($_REQUEST['cpcff_mautic_form'])) : '';

				$fields = array();
				if(isset($_REQUEST[ 'cpcff_mautic_attr' ]))
				{
					foreach( $_REQUEST[ 'cpcff_mautic_attr' ] as $key => $attr )
					{
						$attr  = trim( $attr );
						$field = trim( $_REQUEST[ 'cpcff_mautic_field' ][ $key ] );
						if( !empty( $attr ) && !empty( $field ) ) $fields[ $attr ] = $field;
					}
				}

				$data  = array(
					'url' => $url,
					'form' => $form,
					'fields' => $fields
				);

				$wpdb->delete( $wpdb->prefix.$this->form_mautic_table, array( 'formid' => $form_id ), array( '%d' ) );
				$wpdb->insert(
					$wpdb->prefix.$this->form_mautic_table,
					array(
						'formid'  => $form_id,
						'enabled' => isset($_REQUEST['cpcff_mautic_enabled']) ? 1 : 0,
						'data'	  => serialize( $data )
					),
					array( '%d', '%d', '%s' )
				);
			}

			$enabled = false;
			$form 	 = '';
			$url 	 = '';
			$fields  = array();
			$row  = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_mautic_table." WHERE formid=%d", $form_id ) );

			if( $row )
			{
				$enabled = $row->enabled;
				if( ( $tmp = @unserialize( $row->data ) ) != false )
				{
					if(!empty($tmp['url'])) $url = $tmp['url'];
					if(!empty($tmp['form'])) $form = $tmp['form'];
					if(!empty($tmp['fields'])) $fields = $tmp['fields'];
				}
			}

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<input type="hidden" name="cpcff_mautic" value="1" />
			<div id="metabox_mautic_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_mautic_addon_form_settings' ) ); ?>" >
				<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
				<div class="inside">
					<table cellspacing="0" style="width:100%;">
						<tr>
							<td style="white-space:nowrap;width:200px;"><?php _e('Enabling Mautic Integration', 'calculated-fields-form');?>:</td>
							<td>
								<input type="checkbox" name="cpcff_mautic_enabled" <?php echo ( ( $enabled ) ? 'CHECKED' : '' ); ?> />
							</td>
						</tr>
						<tr>
						<tr>
							<td style="white-space:nowrap;width:200px;"><?php _e('Mautic url', 'calculated-fields-form');?>:</td>
							<td>
								<input type="text" name="cpcff_mautic_url" value="<?php echo esc_attr( $url ); ?>" style="width:100%;" />
							</td>
						</tr>
						<tr>
							<td style="white-space:nowrap;width:200px;"><?php _e('Mautic form id', 'calculated-fields-form');?>:</td>
							<td><input type="number" name="cpcff_mautic_form" value="<?php echo esc_attr( $form ); ?>" ></td>
						</tr>
						<tr><td colspan="2"><strong><?php _e('Fields relationship', 'calculated-fields-form');?>:</strong></td></tr>
						<tr>
							<td colspan="2">
								<table>
									<?php
									$c = 1;
									foreach( $fields as $attr => $field )
									{
										print '
											<tr><td>
											<input type="text" name="cpcff_mautic_attr['.$c.']" value="'.esc_attr( $attr ).'" placeholder="Mautic field" class="cpcff-mautic-attribute" />
											</td><td>
											<input type="text" name="cpcff_mautic_field['.$c.']" value="'.esc_attr( $field ).'" placeholder="fieldname#">
											</td><td>
											<input type="button" value="[ X ]" onclick="cpcff_mautic_removeAttr( this );" class="button-secondary" /></td></tr>
											</td></tr>
										';
										$c++;
									}
									?>
									<tr>
										<td colspan="2">
											<input type="button" value="<?php esc_attr_e('Add attribute', 'calculated-fields-form');?>" onclick="cpcff_mautic_addAttr( this );" class="button-primary" />
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
                    <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
				</div>
				<script>
					var cpcff_mautic_attr_counter = <?php print $c; ?>;
					function cpcff_mautic_addAttr( e )
					{
						try
						{
							var $   = jQuery,
								str = $( '<tr><td><input type="text" name="cpcff_mautic_attr['+cpcff_mautic_attr_counter+']" value="" placeholder="Mautic field" ></td><td><input type="text" name="cpcff_mautic_field['+cpcff_mautic_attr_counter+']" value="" placeholder="fieldname#"></td><td><input type="button" value="[ X ]" onclick="cpcff_mautic_removeAttr( this );" class="button-secondary" /></td></tr>' );

							$( e ).closest( 'tr' ).before( str );
							cpcff_mautic_attr_counter++;
						}
						catch( err ){}
					}

					function cpcff_mautic_removeAttr( e )
					{
						try
						{
							jQuery( e ).closest( 'tr' ).remove();
						}
						catch( err ){}
					}

				</script>
			</div>
			<?php
		}

		/************************ ADDON CODE *****************************/
        /************************ ATTRIBUTES *****************************/

		private $form_mautic_table = 'cp_calculated_fields_form_mautic';

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = __("The add-on allows to integrate Mautic forms with the website forms", 'calculated-fields-form' );
            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;

			// Export the lead
			add_action( 'cpcff_process_data', array( &$this, 'send_to_mautic' ) );

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
			$db_queries = array( "CREATE TABLE ".$wpdb->prefix.$this->form_mautic_table." (
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
         * Send the information to Mautic
         */
        public function	send_to_mautic( $params )
		{
			global $wpdb, $wp_version;

			$form_id = @intval( $params[ 'formid' ] );
			if( $form_id )
			{
				$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_mautic_table." WHERE formid=%d", $form_id ) );
				if( $row && !empty( $row->enabled ) )
				{
					$data = unserialize( $row->data );
					if($data != false)
					{
						$baseurl = !empty($data['url']) ? esc_url($data['url']) : '';
						$form = !empty($data['form']) ? @intval($data['form']) : '';
						$fields = (!empty($data['fields']) && is_array($data['fields'])) ? $data['fields'] : array();
						foreach( $fields as $attr => $field )
						{
							$field = trim($field);
							$fields[ $attr ] = isset($params[ $field ]) ? $params[ $field ] : (preg_match('/fieldname\d+/',$field) ? '' : $field);
						}

						if(
							!empty($baseurl) &&
							!empty($form) &&
							!empty($fields)
						)
						{
							$url = path_join($baseurl, 'form/submit?formId='.$form);
							$fields['formId'] = $form;
							$fields['return'] = $baseurl;
							$args = array(
								'body' 		=> array( 'mauticform' => $fields ),
								'headers' 	=> array(
									'Content-Type' => 'application/x-www-form-urlencoded',
									'user-agent' => 'WordPress-to-Mautic for calculated-fields-form plugin - WordPress/'.$wp_version.'; '.get_bloginfo( 'url' ),
								),
								'timeout' => 45,
								'sslverify'	=> false,
							);

							$response = wp_remote_post( $url, $args );
							if ( is_wp_error( $response ) )
							{
								$error_message = $response->get_error_message();
								error_log( "CFF_Mautic Error: $error_message" );
								error_log( "      posted url: $url" );
							}
						}
					}
				}
			}
		} // End send_to_mautic

		/**
		 *	Delete the form from the addon's table
		 */
        public function delete_form( $formid)
		{
			global $wpdb;
			$wpdb->delete( $wpdb->prefix.$this->form_mautic_table, array('formid' => $formid), '%d' );
		} // delete_form

		/**
		 *	Clone the form's row
		 */
		public function clone_form( $original_form_id, $new_form_id )
		{
			global $wpdb;

			$form_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_mautic_table." WHERE formid=%d", $original_form_id ), ARRAY_A);

			if(!empty($form_row))
			{
				unset($form_row["id"]);
				$form_row["formid"] = $new_form_id;
				$wpdb->insert( $wpdb->prefix.$this->form_mautic_table, $form_row);
			}
		} // End clone_form

		/**
		 *	It is called when the form is exported to export the addons data too.
		 *  Receive an array with the other addons data, and the form's id for filtering.
		 */
		public function export_form($addons_array, $formid)
		{
			global $wpdb;
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_mautic_table." WHERE formid=%d", $formid ), ARRAY_A );
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
					$wpdb->prefix.$this->form_mautic_table,
					$addons_array[$this->addonID]
				);
			}
		} // End import_form

    } // End Class

    // Main add-on code
    $cpcff_mautic_obj = new CPCFF_Mautic();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cpcff_mautic_obj);
}
?>