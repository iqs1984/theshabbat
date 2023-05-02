<?php
/*
....
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPCFF_SalesForce' ) )
{
    class CPCFF_SalesForce extends CPCFF_BaseAddon
    {
		static public $category = 'External Services';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-salesforce-20150311";
		protected $name = "CFF - SalesForce";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#salesforce-addon';

		public function get_addon_form_settings( $form_id )
		{
			global $wpdb;

			// Insertion in database
			if(
				isset( $_REQUEST[ 'cpcff_salesforce_oid' ] )
			)
			{
				$data = array();
				foreach( $_REQUEST[ 'cpcff_salesforce_attr' ] as $key => $attr )
				{
					$attr = trim( $attr );
					$value = trim( $_REQUEST[ 'cpcff_salesforce_field' ][ $key ] );
					if( !empty( $attr ) && !empty( $value ) ) $data[ $attr ] = $value;
				}

				$wpdb->delete( $wpdb->prefix.$this->form_salesforce_table, array( 'formid' => $form_id ), array( '%d' ) );
				$wpdb->insert( 	$wpdb->prefix.$this->form_salesforce_table,
								array(
									'formid' => $form_id,
									'oid'	 => trim( $_REQUEST[ 'cpcff_salesforce_oid' ] ),
									'debug'  => ( isset( $_REQUEST[ 'cpcff_salesforce_debug' ] ) ) ? 1 : 0,
									'debugemail' => trim( $_REQUEST[ 'cpcff_salesforce_debug_email' ] ),
									'data'	 => serialize( $data )
								),
								array( '%d', '%s', '%d', '%s', '%s' )
							);
			}

			$oid  	= '';
			$debug 	= 0;
			$debugemail = '';
			$data 	= array();
			$row 	= $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_salesforce_table." WHERE formid=%d", $form_id ) );

			if( $row )
			{
				$oid 	= $row->oid;
				$debug 	= $row->debug;
				$debugemail = $row->debugemail;
				if( ( $tmp = @unserialize( $row->data ) ) != false ) $data = $tmp;
			}

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<div id="metabox_salesforce_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_salesforce_addon_form_settings' ) ); ?>" >
				<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
				<div class="inside">
					<table cellspacing="0" style="width:100%;">
						<tr>
							<td style="white-space:nowrap;width:200px;"><?php _e('Organization ID', 'calculated-fields-form');?>:</td>
							<td><input type="text" name="cpcff_salesforce_oid" value="<?php echo esc_attr( $oid ); ?>" class="width75"></td>
						</tr>
						<tr>
							<td style="white-space:nowrap;width:200px;"><?php _e('Enabling debug', 'calculated-fields-form');?>:</td>
							<td><input type="checkbox" name="cpcff_salesforce_debug" <?php echo ( ( $debug ) ? 'CHECKED' : '' ); ?> ></td>
						</tr>
						<tr>
							<td style="white-space:nowrap;width:200px;"><?php _e('Debug email', 'calculated-fields-form');?>:</td>
							<td><input type="text" name="cpcff_salesforce_debug_email" value="<?php echo esc_attr( $debugemail ); ?>" class="width75" ></td>
						</tr>
						<tr><td colspan="2"><strong><?php _e('Lead Attributes', 'calculated-fields-form');?>:</strong></td></tr>
						<tr>
							<td colspan="2">
								<table>
									<?php
									$c = 1;
									$keys_arr = array_keys( $this->lead_attributes );
									foreach( $data as $attr => $value )
									{
										print '<tr><td style="position:relative;width:200px;">';

										$str = 	'<input type="text" name="cpcff_salesforce_attr['.$c.']" value="'.esc_attr( $attr ).'" placeholder="fieldname#" class="cpcff-salesforce-attribute" />';
										$str .= '<select class="cpcff-autocomplete" style="width:100%;"><option value=""></option>';
										foreach( $this->lead_attributes as $lead_attr_key => $lead_attr_title )
										{
											$str .= '<option value="'.esc_attr( $lead_attr_key ).'" '.( ( $lead_attr_key == $attr ) ? 'SELECTED' : '' ).'>'.$lead_attr_title.'</option>';
										}
										$str .= '</select>';

										print $str;
										print '</td><td><input type="text" name="cpcff_salesforce_field['.$c.']" value="'.esc_attr( $value ).'"><input type="button" value="[ X ]" onclick="cpcff_salesforce_removeAttr( this );" class="button-secondary" /></td></tr>';
										$c++;
									}
									?>
									<tr>
										<td colspan="2">
											<input type="button" value="<?php esc_attr_e('Add attribute', 'calculated-fields-form');?>" onclick="cpcff_salesforce_addAttr( this );" class="button-primary" />
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
                    <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
				</div>
				<script>
					var cpcff_salesforce_attr_counter = <?php print $c; ?>;
					function cpcff_salesforce_addAttr( e )
					{
						try
						{
							var $   = jQuery,
								str = $( '<tr><td style="width:200px;position:relative;"><select name="cpcff_salesforce_attr['+cpcff_salesforce_attr_counter+']" style="width:100%;" class="cpcff-autocomplete"><option value=""></option><?php foreach( $this->lead_attributes as $key => $value ) print '<option value="'.esc_attr( $key ).'">'.$value.'</option>'; ?></select></td><td><input type="text" name="cpcff_salesforce_field['+cpcff_salesforce_attr_counter+']" value="" placeholder="fieldname#" ><input type="button" value="[ X ]" onclick="cpcff_salesforce_removeAttr( this );" class="button-secondary" /></td></tr>' );

							$( e ).closest( 'tr' )
								  .before( str );

							str.find( '.cpcff-autocomplete' ).cpcffautocomplete();

							cpcff_salesforce_attr_counter++;
						}
						catch( err ){}
					}

					function cpcff_salesforce_removeAttr( e )
					{
						try
						{
							var $   = jQuery;
							$( e ).closest( 'tr' ).remove();
						}
						catch( err ){}
					}

				</script>
			</div>
			<?php
		}

		/************************ ADDON CODE *****************************/
        /************************ ATTRIBUTES *****************************/

		private $form_salesforce_table = 'cp_calculated_fields_form_salesforce';
		private $salesforce_url = 'https://webto.salesforce.com/servlet/servlet.WebToLead?encoding=UTF-8';
		private $lead_attributes = array(
			'salutation' 	=> 'Salutation',
			'title' 		=> 'Title',
			'first_name'	=> 'First Name',
			'last_name'		=> 'Last Name',
			'email'			=> 'Email',
			'phone'			=> 'Phone',
			'mobile'		=> 'Mobile',
			'fax'			=> 'Fax',
			'street'		=> 'Street',
			'city'			=> 'City',
			'state'			=> 'State/Province (text only)',
			'state_code'	=> 'State Code',
			'country'		=> 'Country (text only)',
			'country_code'	=> 'Country Code',
			'zip'			=> 'ZIP',
			'URL'			=> 'URL',
			'description'	=> 'Description',
			'company'		=> 'Company',
			'industry'		=> 'Industry',
			'revenue'		=> 'Annual Revenue',
			'employees'		=> 'Employees',
			'lead_source'	=> 'Lead Source',
			'rating'		=> 'Rating',
			'Campaign_ID'	=> 'Campaign ID',
			'member_status'	=> 'Campaign Member Status',
			'emailOptOut'	=> 'Email Opt Out',
			'faxOptOut'		=> 'Fax Opt Out',
			'doNotCall'		=> 'Do Not Call'
		);

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = __("The add-on allows create SalesForce leads with the submitted information", 'calculated-fields-form' );
            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;

			// Load resources
            add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_scripts' ), 10 );

			// Export the lead
			add_action( 'cpcff_process_data', array( &$this, 'export_lead' ) );

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
			$db_queries = array( "CREATE TABLE ".$wpdb->prefix.$this->form_salesforce_table." (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					oid VARCHAR(250) DEFAULT '' NOT NULL,
					formid INT NOT NULL,
					data text,
					debug INT DEFAULT 0 NOT NULL,
					debugemail VARCHAR(250) DEFAULT '' NOT NULL,
					UNIQUE KEY id (id)
				) $charset_collate;"
			);
			$this->_run_update_database($db_queries);
		} // End update_database

        /************************ PRIVATE METHODS *****************************/

		/************************ PUBLIC METHODS  *****************************/

		/**
         * Enqueue all resources: CSS and JS files, required by the Addon
         */
        public function enqueue_scripts()
        {
			wp_enqueue_style( 'cpcff_salesforce_addon_css', plugins_url('/salesforce.addon/css/styles.css', __FILE__ ) );
			wp_enqueue_script( 'cpcff_salesforce_addon_js', plugins_url('/salesforce.addon/js/scripts.js',  __FILE__), array( 'jquery', 'jquery-ui-autocomplete' ) );

        } // End enqueue_scripts

		/**
         * Export the leads to the SalesForce account
         */
        public function	export_lead( $params )
		{
			global $wpdb, $wp_version;

			$form_id = @intval( $params[ 'formid' ] );
			if( $form_id )
			{
				$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_salesforce_table." WHERE formid=%d", $form_id ) );
				if( $row && !empty( $row->oid ) )
				{
					$post = array( 'oid' => $row->oid );
					if( $row->debug ) $post[ 'debug' ] = 1;
					if( !empty( $row->debugemail ) ) $post[ 'debugEmail' ] = $row->debugemail;

					$attrs = unserialize( $row->data );
					foreach( $attrs as $key => $value )
					{
						$value = trim($value);
						$post[ $key ] = (isset($params[ $value ])) ? $params[ $value ] : ((preg_match('/fieldname\d+/',$value)) ? '' : $value);
					}

					$body = preg_replace('/%5B[0-9]+%5D/simU', '', http_build_query($post) ); // remove php style arrays for array values [1]
					$args = array(
						'body' 		=> $body,
						'headers' 	=> array(
							'Content-Type' => 'application/x-www-form-urlencoded',
							'user-agent' => 'WordPress-to-Lead for calculated-fields-form plugin - WordPress/'.$wp_version.'; '.get_bloginfo( 'url' ),
						),
						'timeout' => 45,
						'sslverify'	=> false,
					);

					$result = wp_remote_post( $this->salesforce_url, $args );
				}
			}
		} // End export_lead

		/**
		 *	Delete the form from the addon's table
		 */
        public function delete_form( $formid)
		{
			global $wpdb;
			$wpdb->delete( $wpdb->prefix.$this->form_salesforce_table, array('formid' => $formid), '%d' );
		} // delete_form

		/**
		 *	Clone the form's row
		 */
		public function clone_form( $original_form_id, $new_form_id )
		{
			global $wpdb;

			$form_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_salesforce_table." WHERE formid=%d", $original_form_id ), ARRAY_A);

			if(!empty($form_row))
			{
				unset($form_row["id"]);
				$form_row["formid"] = $new_form_id;
				$wpdb->insert( $wpdb->prefix.$this->form_salesforce_table, $form_row);
			}
		} // End clone_form

		/**
		 *	It is called when the form is exported to export the addons data too.
		 *  Receive an array with the other addons data, and the form's id for filtering.
		 */
		public function export_form($addons_array, $formid)
		{
			global $wpdb;
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_salesforce_table." WHERE formid=%d", $formid ), ARRAY_A );
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
					$wpdb->prefix.$this->form_salesforce_table,
					$addons_array[$this->addonID]
				);
			}
		} // End import_form

    } // End Class

    // Main add-on code
    $cpcff_salesforce_obj = new CPCFF_SalesForce();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cpcff_salesforce_obj);
}
?>