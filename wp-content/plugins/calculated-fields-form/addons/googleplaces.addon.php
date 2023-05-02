<?php
/*
....
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPCFF_GooglePlaces' ) )
{
    class CPCFF_GooglePlaces extends CPCFF_BaseAddon
    {
		static public $category = 'External Services';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-google-places-20160126";
		protected $name = "CFF - Google Places Integration";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#google-places-addon';

		public function get_addon_settings()
		{
			if( isset( $_REQUEST[ 'cpcff_google_places' ] ) )
			{
				check_admin_referer( $this->addonID, '_cpcff_nonce' );
				update_option( 'CP_CFF_GOOGLE_PLACES_API_KEY', trim( $_REQUEST[ 'CP_CFF_GOOGLE_PLACES_API_KEY' ] ) );
			}

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<form method="post" action="<?php print esc_url(admin_url('admin.php?page=cp_calculated_fields_form')); ?>">
				<div id="metabox_googleplaces_addon_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_googleplaces_addon_settings' ) ); ?>" >
					<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
					<div class="inside">
						<p>
						<?php _e('Enter the Google Places API Key', 'calculated-fields-form');?>:<br>
						<input name="CP_CFF_GOOGLE_PLACES_API_KEY" type="text" style="width:100%;" value="<?php print esc_attr( get_option( 'CP_CFF_GOOGLE_PLACES_API_KEY', '' ) ); ?>" />
						</p>
						<p><input type="submit" value="Save settings" class="button-secondary" /></p>
					</div>
					<input type="hidden" name="cpcff_google_places" value="1" />
					<input type="hidden" name="_cpcff_nonce" value="<?php echo wp_create_nonce( $this->addonID ); ?>" />
				</div>
			</form>
			<?php
		}

		public function get_addon_form_settings( $form_id )
		{
			global $wpdb;

			// Insertion in database
			if(
				isset( $_REQUEST[ 'cpcff_google_places_field' ] ) &&
				is_array( $_REQUEST[ 'cpcff_google_places_field' ] ) ||
				isset( $_REQUEST[ 'cpcff_google_places_countries' ] )
			)
			{
				$data = array( 'fields' => array(), 'countries' => array() );

				if( !empty( $_REQUEST[ 'cpcff_google_places_field' ] ) )
				{
					foreach( $_REQUEST[ 'cpcff_google_places_field' ] as $field )
					{
						$field = trim( $field );
						if( !empty( $field ) ) $data[ 'fields' ][] = $field;
					}
				}

				if( isset($_REQUEST[ 'cpcff_google_places_countries' ]) )
				{
					$countries = preg_replace( '/[^A-Za-z\,]/','', $_REQUEST[ 'cpcff_google_places_countries' ]);
					if(!empty($countries))
					{
						$countries = explode(',', $countries);
						foreach( $countries as $index => $country )
						{
							$country = trim( $country );
							if( !empty( $country ) && preg_match( '/^[A-Za-z]{2}$/', $country ) )
								$data['countries'][] = strtoupper($country);
						}
					}
				}

				$wpdb->delete( $wpdb->prefix.$this->form_google_places_table, array( 'formid' => $form_id ), array( '%d' ) );
				$wpdb->insert( 	$wpdb->prefix.$this->form_google_places_table,
								array(
									'formid' => $form_id,
									'data'	 => serialize( $data )
								),
								array( '%d', '%s' )
							);
			}

			// Read from database and display the fields.
			$c = 0;
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_google_places_table." WHERE formid=%d", $form_id ) );
			$fields 	= array();
			$countries 	= array();

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<div id="metabox_googleplaces_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_googleplaces_addon_form_settings' ) ); ?>" >
				<style>
					.cpcff-google-places-field-container{width:100%;}
					.cpcff-google-places-attribute-container{ clear:both; padding:10px 0;}
				</style>
				<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
				<div class="inside">
					<div class="cpcff-google-places-field-container">
			<?php
			if( !empty( $row ) )
			{
				// Creates the fields settings
				if( ( $data = @unserialize( $row->data ) ) != false )
				{
					if( is_array( $data ) )
					{
						$fields = (isset($data['fields'])) ? $data['fields']:$data;
						if(!empty($data['countries']) && is_array($data['countries']))
							$countries =  $data['countries'];
					}
				}
			}

			foreach( $fields as $field )
			{
				$field = trim( $field );
				if( !empty( $field ) )
				{
					$this->_addField( $c, $field );
					$c++;
				}
			}

			foreach( $countries as $index => $country )
			{
				$country = trim( $country );
				if( !empty( $country ) && preg_match( '/^[A-Za-z]{2}$/', $country ) )
					$countries[$index] = strtoupper($country);
			}

			$this->_addField( $c );
			$c++;
			?>

					</div>
					<input type="button" value="<?php esc_attr_e('Add field', 'calculated-fields-form');?>" onclick="cpcff_google_places_addField( this );" class="button-primary" />
					<p><b><?php _e('Limit the searchs to the countries', 'calculated-fields-form'); ?>:</b>
					<input type="text" name="cpcff_google_places_countries" value="<?php print esc_attr(implode(',', $countries)); ?>" /></p>
					<p style="font-style:italic;"><?php _e( 'Used to restrict the places search up to 5 countries. Countries must be passed as a two-character, <a href="https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2" target="_blank">ISO 3166-1 Alpha-2 compatible country code</a>, separated by comma symbols.', 'calculated-fields-form'); ?></p>
                    <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
				</div>
				<script>
					var cpcff_google_places_fields_counter = <?php print $c; ?>;
					function cpcff_google_places_deleteField( e )
					{
						try{
							jQuery( e ).closest( '.cpcff-google-places-attribute-container' ).remove();
						}catch(err ){}
					}

					function cpcff_google_places_addField( e )
					{
						try
						{
							var $   = jQuery,
								str = $( '<div class="cpcff-google-places-attribute-container"><b><?php _e( 'Field name', 'calculated-fields-form'); ?>:</b>&nbsp;<input type="text" name="cpcff_google_places_field['+cpcff_google_places_fields_counter+']" placeholder="fieldname#" /><input type="button" value="<?php esc_attr_e('Delete field', 'calculated-fields-form');?>" onclick="cpcff_google_places_deleteField( this );" class="button-secondary" /><div style="clear:both;"></div><div><em><?php _e( 'Enter the field name to integrate with Google Places and apply the autocomplete behavior', 'calculated-fields-form'); ?></em></div></div>' );

							$( e ).before( str );
							cpcff_google_places_fields_counter++;
						}
						catch( err ){}
					}
				</script>
			</div>
			<?php
		}

		/************************ ADDON CODE *****************************/
        /************************ ATTRIBUTES *****************************/

		private $form_google_places_table = 'cp_calculated_fields_form_google_places';
		private $javascript_code;

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = __("The add-on allows to integrate the input fields in the form with the Google Places API to autocomplete the address entered by the users", 'calculated-fields-form');

            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;

			// Checks the form's settings and generate the javascript code
			add_filter( 'cpcff_pre_form', array( &$this, 'generate_javascript' ) );

			// Inserts the javascript code in the page's footer
			add_action( 'wp_footer', array( &$this, 'insert_javascript' ), 99 );
			add_action( 'cpcff_footer', array( &$this, 'insert_javascript' ), 99 );

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
			$db_queries = array( "CREATE TABLE ".$wpdb->prefix.$this->form_google_places_table." (
					formid INT NOT NULL,
					data text,
					PRIMARY KEY (formid)
				) $charset_collate;"
			);
			$this->_run_update_database($db_queries);
		} // End update_database

        /************************ PRIVATE METHODS *****************************/

		private function _addField( $index, $field = '' )
		{
		?>
			<div class="cpcff-google-places-attribute-container">
				<b><?php _e( 'Field name', 'calculated-fields-form'); ?>:</b>&nbsp;
				<input type="text" name="cpcff_google_places_field[<?php print $index; ?>]" value="<?php print esc_attr(trim( $field )); ?>" placeholder="fieldname#" />
				<input type="button" value="<?php esc_attr_e('Delete field', 'calculated-fields-form');?>" onclick="cpcff_google_places_deleteField( this );" class="button-secondary" />
				<div style="clear:both;"></div>
				<div><em><?php _e( 'Enter the field name to integrate with Google Places and apply the autocomplete behavior', 'calculated-fields-form'); ?></em></div>
			</div>
		<?php
		} // End _addField

		/************************ PUBLIC METHODS  *****************************/

		/**
		 * Checks the form's settings and generates the javascript code
		 */
		public function generate_javascript( $atts )
		{
			if(
				( empty( $_SERVER['REQUEST_URI'] ) || ! preg_match( '/\.xml$/i', $_SERVER['REQUEST_URI'] ) ) &&
                (
					!is_admin() ||
					isset($_POST['preview'])
				) &&
				!empty( $atts ) &&
				is_array( $atts ) &&
				!empty( $atts[ 'id' ] ) &&
                !defined('REST_REQUEST')
			)
			{
				global $wpdb;
				$instance = '_'.CPCFF_MAIN::$form_counter;

				$data = $wpdb->get_var( $wpdb->prepare( "SELECT data FROM ".$wpdb->prefix.$this->form_google_places_table." WHERE formid=%d", $atts[ 'id' ] ) );
				if(
					!empty( $data ) &&
					( $data = unserialize( $data ) ) !== false &&
					is_array( $data )
				)
				{
					$fields = (isset($data['fields'])) ? $data['fields'] : $data;
					$countries = (isset($data['countries']) && is_array($data['countries'])) ? $data['countries'] : array();
                    $this->javascript_code = 1;
                    print '<pre style="display:none;"><script>var cpcff_google_places_fields = cpcff_google_places_fields || {};';
					foreach( $fields as $field )
					{
                        print 'cpcff_google_places_fields["'.esc_js($field.$instance).'"] = {"countries" : '.json_encode($countries).'};';
					}
                    print '</script></pre>';
				}
			}

			return $atts;
		} // End generate_javascript

		/**
		 * Inserts the javascript code in the footer section of page
		 */
		public function insert_javascript()
		{
            if(
				is_admin() &&
				!isset($_POST['preview'])
			) return;
			if( !empty( $this->javascript_code ) )
			{
                $api_key = trim( get_option( 'CP_CFF_GOOGLE_PLACES_API_KEY', '' ) );
				$url = 'http'.( ( is_ssl() ) ? 's' : '' ).'://maps.googleapis.com/maps/api/js?libraries=places'.( ( !empty( $api_key ) ) ? '&key='.$api_key : '' ).'&callback=cff_google_maps_loaded';
			?>
			<script>
				var cpcff_google_places_processed_fields = {},
					cpcff_google_places_flag = 0,
                    fbuilderjQuery = fbuilderjQuery || jQuery;

                function cff_google_maps_loaded()
                {
                    fbuilderjQuery(document).trigger('cff-google-maps-loaded');
                }

				function cpcff_google_places_autocomplete()
				{
					if( cpcff_google_places_flag > 10 ) return;
					cpcff_google_places_flag++;

					var	fields = cpcff_google_places_fields || {},
						autocomplete = {},
						c = 0, h = 0, f;

					for( var i in fields )
					{
                        h++;
						if(i in cpcff_google_places_processed_fields)
                        {
                            c++;
                            continue;
                        }
                        f = document.getElementById(i);
                        if(f)
                        {
                            c++;
                            var args = {};
                            if(
                                typeof fields[i]['countries'] != 'undefined' &&
                                fields[i]['countries'].length
                            ) args = {'componentRestrictions' : {'country': fields[i]['countries']}};

                            cpcff_google_places_processed_fields[ i ] = new google.maps.places.Autocomplete( f, args );
                            google.maps.event.addListener(
                                cpcff_google_places_processed_fields[ i ],
                                'place_changed',
                                (function( f, autocomplete )
                                    {
                                        return function(){
                                            var e = jQuery( f );
                                            try {
                                                var place = autocomplete.getPlace(), v;
                                                for (var i = 0; i < place.address_components.length; i++)
                                                {
                                                    for (var j = 0; j < place.address_components[i].types.length; j++)
                                                    {
                                                        if (place.address_components[i].types[j] == "postal_code")
                                                        {
                                                            if(e.val().indexOf(place.address_components[i].long_name) == -1)
                                                                e.val(e.val()+', '+place.address_components[i].long_name);
                                                        }
                                                    }
                                                }
                                            }catch(err){}
                                            e.trigger( 'change' ).trigger( 'blur' );
                                        };
                                    }
                                )( f, cpcff_google_places_processed_fields[ i ] )
                            );
                        }
					}

					if( c != h )
					{
                        if(fbuilderjQuery)
                        {
                            fbuilderjQuery(document).one('showHideDepEvent', cpcff_google_places_autocomplete);
                        }
					}
				}

				if(
					typeof google != 'undefined' &&
					typeof google[ 'maps' ] != 'undefined'
				)
				{
					cpcff_google_places_autocomplete();
				}
				else
				{
                     if(!('loadingGoogleMaps' in fbuilderjQuery))
                     {
                        fbuilderjQuery.loadingGoogleMaps = true;
                        fbuilderjQuery(document).on('cff-google-maps-loaded', cpcff_google_places_autocomplete);
                        var script=document.createElement('script');
                        script.type  = "text/javascript";
                        script.src= "<?php print $url; ?>";
                        document.body.appendChild(script);
                     }
				}
			</script>
			<?php
			}
		} // End insert_javascript

		/**
		 *	Delete the form from the addon's table
		 */
        public function delete_form( $formid)
		{
			global $wpdb;
			$wpdb->delete( $wpdb->prefix.$this->form_google_places_table, array('formid' => $formid), '%d' );
		} // delete_form

		/**
		 *	Clone the form's row
		 */
		public function clone_form( $original_form_id, $new_form_id )
		{
			global $wpdb;

			$form_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_google_places_table." WHERE formid=%d", $original_form_id ), ARRAY_A);

			if(!empty($form_row))
			{
				$form_row["formid"] = $new_form_id;
				$wpdb->insert( $wpdb->prefix.$this->form_google_places_table, $form_row);
			}
		} // End clone_form
		/**
		 *	It is called when the form is exported to export the addons data too.
		 *  Receive an array with the other addons data, and the form's id for filtering.
		 */
		public function export_form($addons_array, $formid)
		{
			global $wpdb;

			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_google_places_table." WHERE formid=%d", $formid ), ARRAY_A);
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
					$wpdb->prefix.$this->form_google_places_table,
					$addons_array[$this->addonID]
				);
			}
		} // End import_form

	} // End Class

    // Main add-on code
    $cpcff_google_places_obj = new CPCFF_GooglePlaces();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cpcff_google_places_obj);
}
?>