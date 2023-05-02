<?php
/*
....
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPCFF_AutocompletePlaces' ) )
{
    class CPCFF_AutocompletePlaces extends CPCFF_BaseAddon
    {
		static public $category = 'External Services';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-autocomplete-places-20160126";
		protected $name = "CFF - Autocomplete Places Integration (photon api)";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#autocomplete-places-addon';

		public function get_addon_settings()
		{
			if( isset( $_REQUEST[ 'cpcff_autocomplete_addon' ] ) )
			{
				check_admin_referer( $this->addonID, '_cpcff_nonce' );
				if(isset($_REQUEST['CP_CFF_AUTOCOMPLETE_PHOTON_URL']))
					$cp_cff_autocomplete_photon_url = trim($_REQUEST['CP_CFF_AUTOCOMPLETE_PHOTON_URL']);
				if(empty($cp_cff_autocomplete_photon_url))
					delete_option( 'CP_CFF_AUTOCOMPLETE_PHOTON_URL' );
				else
					update_option( 'CP_CFF_AUTOCOMPLETE_PHOTON_URL', $cp_cff_autocomplete_photon_url );
			}

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<form method="post" action="<?php print esc_url(admin_url('admin.php?page=cp_calculated_fields_form')); ?>">
				<div id="metabox_autocompleteplaces_addon_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_autocompleteplaces_addon_settings' ) ); ?>" >
					<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
					<div class="inside">
						<p>
						<?php _e('If you have your own <a href="https://github.com/komoot/photon" target="_blank">photon server</a>, enter is URL here', 'calculated-fields-form');?>:<br>
						<input name="CP_CFF_AUTOCOMPLETE_PHOTON_URL" type="text" style="width:100%;" value="<?php print esc_attr( get_option( 'CP_CFF_AUTOCOMPLETE_PHOTON_URL', '' ) ); ?>" />
						<i><?php _e('The add-on uses by default photon.komoot.io', 'calculated-fields-form'); ?></i>
						</p>
						<p><input type="submit" value="Save settings" class="button-secondary" /></p>
					</div>
					<input type="hidden" name="cpcff_autocomplete_addon" value="1" />
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
				isset( $_REQUEST[ 'cpcff_autocomplete_places_field' ] ) &&
				is_array( $_REQUEST[ 'cpcff_autocomplete_places_field' ] )
			)
			{
				$data = array( 'fields' => array() );

				if( !empty( $_REQUEST[ 'cpcff_autocomplete_places_field' ] ) )
				{
					foreach( $_REQUEST[ 'cpcff_autocomplete_places_field' ] as $field )
					{
						$field = trim( $field );
						if( !empty( $field ) ) $data[ 'fields' ][] = $field;
					}
				}

				$wpdb->delete( $wpdb->prefix.$this->form_autocomplete_places_table, array( 'formid' => $form_id ), array( '%d' ) );
				$wpdb->insert( 	$wpdb->prefix.$this->form_autocomplete_places_table,
								array(
									'formid' => $form_id,
									'data'	 => serialize( $data )
								),
								array( '%d', '%s' )
							);
			}

			// Read from database and display the fields.
			$c = 0;
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_autocomplete_places_table." WHERE formid=%d", $form_id ) );
			$fields 	= array();

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<div id="metabox_autocompleteplaces_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_autocompleteplaces_addon_form_settings' ) ); ?>" >
				<style>
					.cpcff-autocomplete-places-field-container{width:100%;}
					.cpcff-autocomplete-places-attribute-container{ clear:both; padding:10px 0;}
				</style>
				<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
				<div class="inside">
					<div class="cpcff-autocomplete-places-field-container">
			<?php
			if( !empty( $row ) )
			{
				// Creates the fields settings
				if( ( $data = @unserialize( $row->data ) ) != false )
				{
					if( is_array( $data ) )
					{
						$fields = (isset($data['fields'])) ? $data['fields']:$data;
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

			$this->_addField( $c );
			$c++;
			?>

					</div>
					<input type="button" value="<?php esc_attr_e('Add field', 'calculated-fields-form');?>" onclick="cpcff_autocomplete_places_addField( this );" class="button-primary "/>
                    <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
				</div>
				<script>
					var cpcff_autocomplete_places_fields_counter = <?php print $c; ?>;
					function cpcff_autocomplete_places_deleteField( e )
					{
						try{
							jQuery( e ).closest( '.cpcff-autocomplete-places-attribute-container' ).remove();
						}catch(err ){}
					}

					function cpcff_autocomplete_places_addField( e )
					{
						try
						{
							var $   = jQuery,
								str = $( '<div class="cpcff-autocomplete-places-attribute-container"><b><?php _e( 'Field name', 'calculated-fields-form'); ?>:</b>&nbsp;<input type="text" name="cpcff_autocomplete_places_field['+cpcff_autocomplete_places_fields_counter+']" placeholder="fieldname#" /><input type="button" value="<?php esc_attr_e('Delete field', 'calculated-fields-form');?>" onclick="cpcff_autocomplete_places_deleteField( this );" class="button-secondary" /><div style="clear:both;"></div><div><em><?php _e( 'Enter the field name to integrate with Autocomplete Places and apply the autocomplete behavior', 'calculated-fields-form'); ?></em></div></div>' );

							$( e ).before( str );
							cpcff_autocomplete_places_fields_counter++;
						}
						catch( err ){}
					}
				</script>
			</div>
			<?php
		}

		/************************ ADDON CODE *****************************/
        /************************ ATTRIBUTES *****************************/

		private $form_autocomplete_places_table = 'cp_calculated_fields_form_autocomplete_places';
		private $javascript_code;

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = __("The add-on allows to integrate the input fields in the form with the photon API to autocomplete the address entered by the users", 'calculated-fields-form');

            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;

            //
            if(isset($_REQUEST['cff-autocomplete-places']))
            {
                try
                {
                    $response = wp_remote_get(
                        'https://photon.komoot.io/api/',
                        [
                            'body' => [
                                'q' => isset($_REQUEST['q']) ? sanitize_text_field($_REQUEST['q']) : '',
                                'limit' => !empty($_REQUEST['limit']) && is_numeric($_REQUEST['limit']) ? intval($_REQUEST['limit']) : 10
                            ]
                        ]
                    );
                    if(!is_wp_error( $response )) print wp_remote_retrieve_body( $response );
                } catch (Exception $err){}
                exit;
            }
			// Checks the form's settings and generate the javascript code
			add_filter( 'cpcff_pre_form', array( &$this, 'generate_javascript' ) );

			// Enqueue public script
			add_filter('cpcff_the_form', array(&$this, 'enqueue_public_resources'), 1, 2);

			// Inserts the javascript code in the page's footer
			add_action( 'wp_footer', array( &$this, 'insert_javascript' ) );
			add_action( 'cpcff_footer', array( &$this, 'insert_javascript' ) );

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
			$db_queries = array( "CREATE TABLE ".$wpdb->prefix.$this->form_autocomplete_places_table." (
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
			<div class="cpcff-autocomplete-places-attribute-container">
				<b><?php _e( 'Field name', 'calculated-fields-form'); ?>:</b>&nbsp;
				<input type="text" name="cpcff_autocomplete_places_field[<?php print $index; ?>]" value="<?php print esc_attr(trim( $field )); ?>" placeholder="fieldname#" />
				<input type="button" value="<?php esc_attr_e('Delete field', 'calculated-fields-form');?>" onclick="cpcff_autocomplete_places_deleteField( this );" class="button-secondary" />
				<div style="clear:both;"></div>
				<div><em><?php _e( 'Enter the field name to integrate with Autocomplete Places and apply the autocomplete behavior', 'calculated-fields-form'); ?></em></div>
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
				!empty( $atts ) &&
				is_array( $atts ) &&
				!empty( $atts[ 'id' ] ) &&
                !defined('REST_REQUEST')
			)
			{
				global $wpdb;
				$instance = '_'.CPCFF_MAIN::$form_counter;

				$data = $wpdb->get_var( $wpdb->prepare( "SELECT data FROM ".$wpdb->prefix.$this->form_autocomplete_places_table." WHERE formid=%d", $atts[ 'id' ] ) );
				if(
					!empty( $data ) &&
					( $data = unserialize( $data ) ) !== false &&
					is_array( $data )
				)
				{
					$fields = (isset($data['fields'])) ? $data['fields'] : $data;
                    $this->javascript_code = 1;
					print '<pre style="display:none;"><script>var cpcff_autocomplete_places_fields = cpcff_autocomplete_places_fields || {};';
					foreach( $fields as $field )
					{
                        print 'cpcff_autocomplete_places_fields["'.esc_js($field.$instance).'"] = "'.esc_js($field.$instance).'";';
					}
                    print '</script></pre>';
				}
			}

			return $atts;
		} // End generate_javascript

		public function enqueue_public_resources($t1, $t2)
		{
			if( !empty( $this->javascript_code ) ) wp_enqueue_script('jquery-ui-autocomplete');
			return $t1;
		} // End enqueue_public_resources

		/**
		 * Inserts the javascript code in the footer section of page
		 */
		public function insert_javascript()
		{
			if( !empty( $this->javascript_code ) )
			{
			?>
			<script>
				var fbuilderjQuery = fbuilderjQuery || jQuery,
                    cpcff_autocomplete_processed_fields = {};

                function cpcff_autocomplete()
                {
					var	fields = cpcff_autocomplete_places_fields || {},
						$ = fbuilderjQuery, c = 0, h = 0, f;

					if(!('autocomplete' in $.fn) && jQuery && 'autocomplete' in jQuery.fn)
						$.fn.autocomplete = jQuery.fn.autocomplete;

					for( var i in fields )
					{
                        h++;
                        if(i in cpcff_autocomplete_processed_fields)
                        {
                            c++;
                            continue;
                        }
                        f = $('#'+i);
						if( f.length )
						{
                            cpcff_autocomplete_processed_fields[i] = i;
                            c++;
							f.autocomplete({
                                appendTo: f.closest('.dfield'),
								source : function( request, response )
								{
									$.ajax({
										'url'		: '<?php

                                        $site_url = CPCFF_AUXILIARY::site_url( true );
                                        $site_url .= (strpos($site_url, '?') === false ? '?' : '&').'cff-autocomplete-places=1';

                                        print str_replace(
                                            "&amp;",
                                            "&",
                                            esc_js(
                                                get_option(
                                                    "CP_CFF_AUTOCOMPLETE_PHOTON_URL",
                                                    $site_url
                                                )
                                            )
                                        );
                                        ?>',
										'format'	: 'json',
										'data'		: {
											'q'		: request.term,
											'limit' : 10
										},
										'success' : function( data )
										{
                                            if(typeof data == 'string') data = JSON.parse(data);
											if('features' in data )
											response(
												$.map(
													data.features,
													function( item )
													{
														var separator = '', formatted = '';
														if(item.properties.name){
															formatted += item.properties.name;
															separator = ', ';
														}
														if (item.properties.street){
															formatted += separator + item.properties.street;
															separator = ', ';
														}
														if (item.properties.housenumber){
															formatted += ' ' + item.properties.housenumber;
															separator = ', ';
														}
														if (item.properties.city && item.properties.city !== item.properties.name){
															formatted += separator + item.properties.city;
															separator = ', ';
														}
														if (item.properties.state){
															formatted += separator + item.properties.state;
															separator = ', ';
														}
														if (item.properties.country){
															formatted += separator + item.properties.country;
															separator = ', ';
														}
														if (item.properties.postcode){
															formatted += separator + item.properties.postcode;
														}
														return {
															label: formatted,
															value: formatted
														};
													}
												)
											);
										}
									});
								},
								minLength: 3,
								delay : 200,
								select: function ( event, ui )
								{
									$(event.target).val(ui.item.value).change();
								}
							});
						}
					}

                    if( c != h )
					{
                        $(document).one('showHideDepEvent', cpcff_autocomplete);
					}
                }
				fbuilderjQuery(window).on('load', cpcff_autocomplete);
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
			$wpdb->delete( $wpdb->prefix.$this->form_autocomplete_places_table, array('formid' => $formid), '%d' );
		} // delete_form

		/**
		 *	Clone the form's row
		 */
		public function clone_form( $original_form_id, $new_form_id )
		{
			global $wpdb;

			$form_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_autocomplete_places_table." WHERE formid=%d", $original_form_id ), ARRAY_A);

			if(!empty($form_row))
			{
				$form_row["formid"] = $new_form_id;
				$wpdb->insert( $wpdb->prefix.$this->form_autocomplete_places_table, $form_row);
			}
		} // End clone_form
		/**
		 *	It is called when the form is exported to export the addons data too.
		 *  Receive an array with the other addons data, and the form's id for filtering.
		 */
		public function export_form($addons_array, $formid)
		{
			global $wpdb;

			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_autocomplete_places_table." WHERE formid=%d", $formid ), ARRAY_A);
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
					$wpdb->prefix.$this->form_autocomplete_places_table,
					$addons_array[$this->addonID]
				);
			}
		} // End import_form

	} // End Class

    // Main add-on code
    $cpcff_autocomplete_places_obj = new CPCFF_AutocompletePlaces();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cpcff_autocomplete_places_obj);
}
?>