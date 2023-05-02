<?php
/*
....
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPCFF_Signature' ) )
{
    class CPCFF_Signature extends CPCFF_BaseAddon
    {
		static public $category = 'Extending Features';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-signature-20161025";
		protected $name = "CFF - Signature Fields";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#signature-addon';

		public function get_addon_form_settings( $form_id )
		{
			global $wpdb;

			// Insertion in database
			if( !empty( $_REQUEST[ 'cpcff_signature_addon' ] ) )
			{

				// Fields
				$fields = array();
				if(
					isset( $_REQUEST[ 'cpcff_signature_field' ] ) &&
					is_array( $_REQUEST[ 'cpcff_signature_field' ] )
				)
				{
					foreach( $_REQUEST[ 'cpcff_signature_field' ] as $field )
					{
						$field = trim( $field );
						if( !empty( $field ) ) $fields[] = $field;
					}
				}

				// Settings
				$settings = array_merge( array(), $this->default_settings );

				if(
					isset($_REQUEST[ 'cpcff_signature_color' ]) &&
					( $cpcff_signature_color = trim($_REQUEST[ 'cpcff_signature_color' ]) ) != ''
				) $settings[ 'color' ] = $cpcff_signature_color;

				if(
					isset($_REQUEST[ 'cpcff_signature_line_thickness' ]) &&
					( $cpcff_signature_line_thickness = trim($_REQUEST[ 'cpcff_signature_line_thickness' ]) ) != ''
				) $settings[ 'thickness' ] = $cpcff_signature_line_thickness;

				$settings[ 'guideline' ] = ( isset($_REQUEST[ 'cpcff_signature_guideline' ]) ) ? 1 : 0;

				if(
					isset($_REQUEST[ 'cpcff_signature_guideline_color' ]) &&
					( $cpcff_signature_guideline_color = trim($_REQUEST[ 'cpcff_signature_guideline_color' ]) ) != ''
				) $settings[ 'guidelineColor' ] = $cpcff_signature_guideline_color;

				// Refresh database
				$wpdb->delete( $wpdb->prefix.$this->form_signature_table, array( 'formid' => $form_id ), array( '%d' ) );
				$wpdb->insert( 	$wpdb->prefix.$this->form_signature_table,
								array(
									'formid' 	=> $form_id,
									'fields'	=> serialize( $fields ),
									'settings'	=> serialize( $settings )
								),
								array( '%d', '%s', '%s' )
							);
			}

			// Read from database and display the fields.
			$c = 0;
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_signature_table." WHERE formid=%d", $form_id ) );

			$fields = array();
			$settings = array_merge( array(), $this->default_settings );

			if( !empty($row) )
			{
				if( ( $tmp_fields = @unserialize( $row->fields ) ) != false && is_array( $tmp_fields ) ) $fields = $tmp_fields;
				if( ( $tmp_settings = @unserialize( $row->settings ) ) != false && is_array( $tmp_settings ) ) $settings = array_merge( $settings, $tmp_settings );
			}

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<input type="hidden" name="cpcff_signature_addon" value="1" />
			<div id="metabox_signature_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_signature_addon_form_settings' ) ); ?>" >
				<style>
					.cpcff-signature-field-container{width:100%;}
					.cpcff-signature-attribute-container{ clear:both; padding:10px 0;}
				</style>
				<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
				<div class="inside">
					<div class="cpcff-signature-field-container">
					<?php
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
					<input type="button" value="<?php esc_attr_e('Add field', 'calculated-fields-form');?>" onclick="cpcff_signature_addField( this );" class="button-secondary" />
					<h3><?php _e( 'Signature Settings', 'calculated-fields-form' ); ?></h3>
					<table class="form-table">
						<tr valign="top">
							<th scope="row"><?php _e( 'Color', 'calculated-fields-form' );?></th>
							<td><input type="text" name="cpcff_signature_color" value="<?php echo esc_attr( $settings[ 'color' ] ); ?>" class="width50" /></td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e( 'Line thickness', 'calculated-fields-form' );?></th>
							<td><input type="text" name="cpcff_signature_line_thickness" value="<?php echo esc_attr( $settings[ 'thickness' ] ); ?>" class="width50" /></td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e( 'Show guideline', 'calculated-fields-form' );?></th>
							<td><input type="checkbox" name="cpcff_signature_guideline" <?php if( $settings['guideline'] ) echo 'CHECKED'; ?> /></td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e( 'Guideline color', 'calculated-fields-form' );?></th>
							<td><input type="text" name="cpcff_signature_guideline_color" value="<?php echo esc_attr( $settings[ 'guidelineColor' ] ); ?>" class="width50" /></td>
						</tr>
					</table>
                    <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
				</div>
				<script>
					var cpcff_signature_fields_counter = <?php print $c; ?>;
					function cpcff_signature_deleteField( e )
					{
						try{
							jQuery( e ).closest( '.cpcff-signature-attribute-container' ).remove();
						}catch(err ){}
					}

					function cpcff_signature_addField( e )
					{
						try
						{
							var $   = jQuery,
								str = $( '<div class="cpcff-signature-attribute-container"><b><?php _e( 'Field name', 'calculated-fields-form'); ?>:</b>&nbsp;<input type="text" name="cpcff_signature_field['+cpcff_signature_fields_counter+']" placeholder="fieldname#" class="width30" /><input type="button" value="<?php esc_attr_e('Delete field', 'calculated-fields-form');?>" onclick="cpcff_signature_deleteField( this );" class="button-secondary"/><div style="clear:both;"></div><div><em><?php _e( 'Enter the field name to be reaplaced by the signature', 'calculated-fields-form'); ?></em></div></div>' );

							$( e ).before( str );
							cpcff_signature_fields_counter++;
						}
						catch( err ){}
					}
				</script>
			</div>
			<?php
		}

		/************************ ADDON CODE *****************************/
        /************************ ATTRIBUTES *****************************/

		private $form_signature_table = 'cp_calculated_fields_form_signature';
		private $javascript_code = '';
		private $style_code = '';
		private $signature_images = array();
		private $default_settings = array(
			'color' => '#000000',
			'background' => '#FFFFFF',
			'thickness' => 2,
			'guideline'	=> 0,
			'guidelineColor' => '#000000',
			'scale' => 1
		);

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = __("The add-on allows to replace form fields with \"Signature\" fields", 'calculated-fields-form');

            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;

			// Checks the form's settings and generate the javascript code
			add_filter( 'cpcff_pre_form', array( &$this, 'generate_javascript' ) );

			// Inserts the styles code in the page's header
			add_filter( 'cpcff_the_form', array( $this, 'insert_css' ), 99, 2 );

			// Inserts the javascript code in the page's footer
			add_action( 'wp_footer', array( &$this, 'insert_javascript' ), 99 );
			add_action( 'cpcff_footer', array( &$this, 'insert_javascript' ), 99 );

			add_filter( 'wp_mail', array(&$this, 'wp_mail') );

			add_action( 'phpmailer_init', array(&$this, 'phpmailer_init') );

			if( is_admin() )
			{
				add_action( 'cpcff_messages_filters', array( &$this, 'messages_list'), 99 );

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
			$db_queries = array( "CREATE TABLE ".$wpdb->prefix.$this->form_signature_table." (
					formid INT NOT NULL,
					fields text,
					settings text,
					PRIMARY KEY (formid)
				) $charset_collate;"
			);
			$this->_run_update_database($db_queries);
		} // End update_database

        /************************ PRIVATE METHODS *****************************/

		private function _addField( $index, $field = '' )
		{
		?>
			<div class="cpcff-signature-attribute-container">
				<b><?php _e( 'Field name', 'calculated-fields-form'); ?>:</b>&nbsp;
				<input type="text" name="cpcff_signature_field[<?php print $index; ?>]" value="<?php print trim( $field ); ?>" placeholder="fieldname#" class="width30" />
				<input type="button" value="<?php esc_attr_e('Delete field', 'calculated-fields-form');?>" onclick="cpcff_signature_deleteField( this );" class="button-secondary" />
				<div style="clear:both;"></div>
				<div><em><?php _e( 'Enter the field name to be reaplaced by the signature', 'calculated-fields-form'); ?></em></div>
			</div>
		<?php
		} // End _addField

		/************************ PUBLIC METHODS  *****************************/

		public function wp_mail($atts)
		{
			$this->signature_images = array();
			$reg_exp= '/"(data\:)?image\/svg\+xml\;base64\,([^"]+)"/';
			if(preg_match_all($reg_exp, $atts[ 'message' ], $_match_all))
			{
				$this->signature_images = $_match_all;
				foreach($this->signature_images[0] as $counter => $signature_image)
				{
					$atts[ 'message' ] = str_replace($signature_image, '"cid:'.$counter.'-signature-uid"' ,$atts[ 'message' ]);
				}
			}
			return $atts;
		} // End wp_mail

		public function phpmailer_init(&$phpmailer)
		{
			if(!empty($this->signature_images) && isset($this->signature_images[2]))
			{
				foreach($this->signature_images[2] as $counter => $signature_image)
				{
					$uid = $counter.'-signature-uid';
					$file_code = base64_decode($signature_image);
					$name = "signature$counter";
					if(class_exists('Imagick'))
					{
						$im = new Imagick();
						$im->readImageBlob($file_code);
						$im->setImageFormat("png24");
						$file_code = $im->getImageBlob();
						$name .= ".png";
					}
					else
					{
						$name .= ".svg";
					}
					$phpmailer->addStringEmbeddedImage($file_code, $uid, $name);
				}
			}
		} // End phpmailer_init

		/**
		 * Checks the form's settings and generates the javascript code
		 */
		public function generate_javascript( $atts )
		{
			if(
                (
					!is_admin() ||
					isset($_POST['preview'])
				) &&
				!empty( $atts ) &&
				is_array( $atts ) &&
				!empty( $atts[ 'id' ] )
			)
			{
				global $wpdb;
				$instance = '_'.CPCFF_MAIN::$form_counter;

				$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_signature_table." WHERE formid=%d", $atts[ 'id' ] ) );
				if(
					!empty( $row ) &&
					!empty( $row->fields ) &&
					( $fields = @unserialize( $row->fields ) ) !== false &&
					is_array( $fields )
				)
				{
					// Fields
					$this->style_code = '<style>';
					foreach( $fields as $field )
					{
						$this->javascript_code .= 'fields["'.esc_js($field.$instance).'"]=1;';
						$this->style_code .= '#' . $field.$instance . '{display:none !important;}';
					}
					$this->style_code .= '</style>';

					//Settings
					$settings = array_merge( array(), $this->default_settings );
					if(
						!empty( $row->settings ) &&
						( $tmp_settings = @unserialize( $row->settings ) ) !== false &&
						is_array( $tmp_settings )
					)
					{
						$settings = array_merge( $settings, $tmp_settings );
					}

					$this->javascript_code .= 'settings["'.esc_js($instance).'"]={};';
					foreach( $settings as $setting_name => $setting_value )
					{
						$this->javascript_code .= 'settings["'.esc_js($instance).'"]["'.esc_js($setting_name).'"]="'.esc_js($setting_value).'";';
					}
				}
			}

			return $atts;
		} // End generate_javascript

		public function insert_css( $content, $formid) {
			// Include styles
			$content = $this->style_code . $content;
			return $content;
		} // End insert_css

		/**
		 * Inserts the javascript code in the footer section of page
		 */
		public function insert_javascript()
		{
			if( !empty( $this->javascript_code ) )
			{
			?>
				<script>if(typeof jQuery == 'undefined' && typeof fbuilderjQuery != 'undefined' ) jQuery = fbuilderjQuery;</script>
			<?php
				// Load required scripts
				echo '<link href="'.plugins_url('/signature.addon/jquery.signature.css', __FILE__ ).'"  rel="stylesheet" type="text/css" />';
				echo '<!--[if IE]><script src="'.plugins_url('/signature.addon/excanvas.js', __FILE__ ).'"></script><![endif]-->';
				echo '<script src="'.plugins_url('/signature.addon/jquery.signature.min.js', __FILE__ ).'"></script>';
				echo '<script src="'.plugins_url('/signature.addon/jquery.ui.touch-punch.min.js', __FILE__ ).'"></script>';
				echo '<script src="'.plugins_url('/signature.addon/jquery.base64.js', __FILE__ ).'"></script>';

			?>
			<script>
				fbuilderjQuery(window).on(
					'load',
					function()
					{
						var $ = fbuilderjQuery,
							cpcff_signature = {},
							fields = {},
							settings = {},
							field, form, size, default_val;

						window[ 'resize_signature_field' ] = function(fname){
								$('[id$="'+(('undefined' != typeof fname) ? fname : '')+'_signature"]').each(function(){
									var e = $(this), c = e.find('canvas'),  w = Math.floor(e.width()), h = Math.floor(e.height());
									if(c.length && w && h && e.signature('isEmpty'))
									{
										var c = c[0],
											tmpCanvas = document.createElement( 'canvas' ),
											tmpCtx = tmpCanvas.getContext('2d'),
											ctx;

										if(c.width != w) c.width = w;
										if(c.height != h) c.height = h;

										tmpCanvas.width = w;
										tmpCanvas.height = h;

										tmpCtx.drawImage(c, 0, 0);
										ctx = c.getContext('2d');
										try{
											ctx.drawImage(tmpCanvas, 0, 0, tmpCanvas.width, tmpCanvas.height, 0, 0, w, h);
										}catch(err){}

										var parts = /(_\d+)_signature/.exec(e.attr('id'));
										if(parts != null )
										{
											var f = parts[1];
											ctx.strokeStyle = settings[f].color;
											ctx.lineWidth = settings[f].thickness;
											ctx.lineCap = 'round';
											ctx.lineJoin = 'round';
											if (settings[f]['guideline']*1)
											{
												ctx.save();
												ctx.strokeStyle = settings[f]['guidelineColor'];
												ctx.lineWidth = 1;
												ctx.beginPath();
												ctx.moveTo(10, h - 50);
												ctx.lineTo(w - 10, h - 50);
												ctx.stroke();
												ctx.restore();
											}
										}
									}
								});
							};
						<?php
						print $this->javascript_code;
						?>
                        var processed_fields = {};
                        (function generate_signature()
                        {
                            var c = 0, h = 0;
                            for( var i in fields )
                            {
                                h++;
                                if(i in processed_fields){c++; continue;}

                                form = /_\d+$/.exec(i);
                                field = $( '#'+i );
                                if(
                                    form == null ||
                                    field.length == 0 ||
                                    typeof settings[ form ] == 'undefined'
                                ) continue;
                                processed_fields[i] = 1;
                                c++;
                                default_val = field.val();
                                form = form[0];
                                size = ( field.hasClass( 'large' ) ) ? 'large' : ( ( field.hasClass( 'medium' ) ) ? 'medium' : 'small' );
                                field.hide()
                                    .before( '<div id="'+i+'_signature" class="'+size+'" style="height:200px;"></div>' );

                                field.after( '<div id="'+i+'_signature_clear" style="position:absolute;z-index:999;padding:5px;border: 1px solid '+settings[form]['color']+'; color:'+settings[form]['color']+';margin:5px;top:0;font-family:Helvetica, Arial, Sans-Serif;cursor:pointer;" class="cff-signature-clear">X</div>');
                                settings[ form ][ 'guideline' ] *= 1;
                                settings[ form ][ 'change' ] = function(event, ui) {
                                    var s 	 = $(this),
                                        data = s.signature( 'toDataURL' ),
                                        svg  = s.signature( 'toSVG' ),
                                        rect = $( svg ).find( 'rect' ),
                                        img = '<img src="'+data+'" class="cpcff-signature" width="'+rect.attr('width')+'" height="'+rect.attr('height')+'" />';
                                    $('#'+s.attr('id').replace('_signature', '')).val(img).change().valid();
                                };
                                $('[id="'+i+'_signature"]').data('settings', settings[form]);
                                if(/<img/i.test(default_val)) $('[id="'+i+'_signature"]').append(default_val.replace('<img ', '<img style="max-width:100% !important;" '));
                                else $('[id="'+i+'_signature"]').signature( settings[ form ] );
                                $('[name="'+i+'"]').bind('depEvent', function(){resize_signature_field(this.name);});
                            }

                            if( c != h )
                            {
                                $(document).one('showHideDepEvent', generate_signature);
                            }
                        })();
						$(document).on(
							'cff-gotopage',
							function()
							{
								setTimeout(resize_signature_field, 200);
							}
						);

						$( document ).on(
							'click',
							'[id$="_signature_clear"]',
							function()
							{
								var signature = $('#'+$(this).attr('id').replace(/_clear$/, '' ));
								if(signature.find('img').length)
								{
									signature.find('img').remove();
									signature.signature( signature.data('settings'));
								}
								else signature.signature( 'clear' );
								try{
									$('#'+$(this).attr('id').replace(/_signature_clear$/, '' )).val('');
								}
								catch(err){}
							}
						);

						$(window).resize( function(){
							setTimeout( resize_signature_field,500);
						});
					}
				);
			</script>
			<?php
			}
		} // End insert_javascript

		public function messages_list()
		{
			print '<style>.cpcff-signature{max-width: 100%;}</style>';
		} // End messages_list

		/**
		 *	Delete the form from the addon's table
		 */
        public function delete_form( $formid)
		{
			global $wpdb;
			$wpdb->delete( $wpdb->prefix.$this->form_signature_table, array('formid' => $formid), '%d' );
		} // delete_form

		/**
		 *	Clone the form's row
		 */
		public function clone_form( $original_form_id, $new_form_id )
		{
			global $wpdb;

			$form_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_signature_table." WHERE formid=%d", $original_form_id ), ARRAY_A);

			if(!empty($form_row))
			{
				$form_row["formid"] = $new_form_id;
				$wpdb->insert( $wpdb->prefix.$this->form_signature_table, $form_row);
			}
		} // End clone_form

		/**
		 *	It is called when the form is exported to export the addons data too.
		 *  Receive an array with the other addons data, and the form's id for filtering.
		 */
		public function export_form($addons_array, $formid)
		{
			global $wpdb;
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_signature_table." WHERE formid=%d", $formid ), ARRAY_A );
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
					$wpdb->prefix.$this->form_signature_table,
					$addons_array[$this->addonID]
				);
			}
		} // End import_form

	} // End Class

    // Main add-on code
    $cpcff_signature_obj = new CPCFF_Signature();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cpcff_signature_obj);
}
?>