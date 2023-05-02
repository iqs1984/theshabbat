<?php
/*
....
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPCFF_Analytics' ) )
{
    class CPCFF_Analytics extends CPCFF_BaseAddon
    {
		static public $category = 'External Services';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-analytics-20160809";
		protected $name = "CFF - Google Analytics";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#google-analytics-addon';

		public function get_addon_form_settings( $form_id )
		{
			global $wpdb;

			// Insertion in database
			if( isset( $_REQUEST[ 'cpcff_analytics' ] ) )
			{
				$wpdb->delete( $wpdb->prefix.$this->form_analytics_table, array( 'formid' => $form_id ), array( '%d' ) );
				$data = array();

				// PROPERTY ID
				$property = '';
				if(
					!empty( $_REQUEST[ 'cpcff_analytics_property' ] )
				)
				{
					$property = trim( $_REQUEST[ 'cpcff_analytics_property' ] );
				}

				// FIELDS
				$fields = array();
				if(
					!empty( $_REQUEST[ 'cpcff_analytics_field' ] ) &&
					is_array( $_REQUEST[ 'cpcff_analytics_field' ])
				)
				{
					foreach( $_REQUEST[ 'cpcff_analytics_field' ] as $index => $field )
					{
						$field = trim( $field );
						if( !empty( $field ) )
						{
							$fieldData = array(
								'label' 	=> false,
								'only_one' 	=> false
							);

							if( isset( $_REQUEST[ 'cpcff_analytics_field_send_label' ][ $index ] ) ) $fieldData['label'] 	= true;
							if( isset( $_REQUEST[ 'cpcff_analytics_field_only_one'   ][ $index ] ) ) $fieldData['only_one'] = true;

							$fields[ $field ] = $fieldData;
						}
					}
				}

				$data[ 'fields' ] = $fields;

				// EVENTS
				$events = array();

				if(
					!empty( $_REQUEST[ 'cpcff_analytics_load_event' ] )
				)
				{
					$events[ 'load' ] = 1;
				}

				if(
					!empty( $_REQUEST[ 'cpcff_analytics_next_page_event' ] )
				)
				{
					$events[ 'next_page' ] = 1;
				}

				if(
					!empty( $_REQUEST[ 'cpcff_analytics_previous_page_event' ] )
				)
				{
					$events[ 'previous_page' ] = 1;
				}

				if(
					!empty( $_REQUEST[ 'cpcff_analytics_submit_event' ] )
				)
				{
					$events[ 'submit' ] = 1;
				}

				$data[ 'events' ] = $events;

				// Exceptions
				$exceptions = array();

				if(
					!empty( $_REQUEST[ 'cpcff_analytics_captcha_error' ] )
				)
				{
					$exceptions[ 'captcha' ] = 1;
				}

				$data[ 'exceptions' ] = $exceptions;
				$wpdb->insert(
					$wpdb->prefix.$this->form_analytics_table,
					array(
						'formid' 	=> $form_id,
						'property'	=> $property,
						'data'		=> serialize( $data )
					),
					array( '%d', '%s', '%s' )
				);
			}

			$row = $this->get_form_analytics( $form_id );

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<div id="metabox_analytics_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_analytics_addon_form_settings' ) ); ?>" >
				<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
				<div class="inside">
					<table cellspacing="3px" style="width:100%;">
						<?php
						$data = array();
						$property = '';

						if( !empty( $row ) )
						{
							$property = $row->property;
							if( ( $data_tmp = @unserialize( $row->data ) ) !== false ) $data  = $data_tmp;
						}
						?>
						<tr>
							<td><?php _e( 'Property ID', 'calculated-fields-form' ); ?>: <input type="text" name="cpcff_analytics_property" value="<?php print $property; ?>" style="width:100%;" placeholder="UA-XXXXX-Y or G-XXXXXXX" /></td>
						</tr>
						<tr>
							<td>
								<h4><?php _e( 'Send Fields', 'calculated-fields-form' ); ?></h4>
								<table class="cpcff-analytics">
									<tr>
										<th><?php _e('Field', 'calculated-fields-form'); ?></th><th><?php _e('Additional information to send', 'calculated-fields-form'); ?></th><th></th>
									</tr>
						<?php
							$counter = 0;
							if( !empty( $data[ 'fields' ] ) )
							{
								foreach( $data[ 'fields' ] as $field_name => $field_data )
								{
									print '
										<tr>
											<td>
												<input type="text" name="cpcff_analytics_field['.$counter.']" value="'.esc_attr( $field_name ).'" />
											</td>
											<td>
												<label>
													<input type="checkbox" name="cpcff_analytics_field_send_label['.$counter.']" '.( ( !empty( $field_data[ 'label' ] ) ) ? 'CHECKED' : '').' placeholder="fieldname#" />
													'.__( 'Send the field\'s label', 'calculated-fields-form' ).'
												</label>
												<label>
													<input type="checkbox" name="cpcff_analytics_field_only_one['.$counter.']" '.( ( !empty( $field_data[ 'only_one' ] ) ) ? 'CHECKED' : '').' />
													'.__( 'Report only the first time the field gets the focus', 'calculated-fields-form' ).'
												</label>
											</td>
											<td>
												<input type="button" value="[X]" class="cpcff-analytics-delete-field-btn button-secondary" />
											</td>
										</tr>
									';
									$counter++;
								}
							}
						?>
									<tr>
										<td colspan="3">
											<input type="button" value="<?php _e( 'Add field', 'calculated-fields-form' ); ?>" class="cpcff-analytics-add-field-btn button-primary" />
										</td>
									</tr>
									<tr>
										<td colspan="3">
											<div class="cpcff-analytics-note">
												<?php _e('Sends a hit of <b>"Event"</b> type for every onfocus event triggered by the field. The hits can include the field\'s label, and it is possible to decide if send only one hit by field, or for each <b>"onfocus"</b> event in the field. The hits are sent with the event\'s category: <b>"form"</b>, and the event action: <b>"focus"</b>, the label of the event includes the form\'s id, the field\'s name, and the field\'s label, if the corresponding option was ticked.', 'calculated-fields-form'); ?>
											</div>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td>
								<h4><?php _e( 'Send Events', 'calculated-fields-form' ); ?></h4>
								<table class="cpcff-analytics">
									<tr>
										<th><?php _e('Event', 'calculated-fields-form'); ?></th>
									</tr>
									<tr>
										<td>
											<label>
												<input type="checkbox" name="cpcff_analytics_load_event" <?php
													echo ( !empty($data[ 'events' ]) && !empty($data[ 'events' ][ 'load' ]) ) ? 'CHECKED' : '';
												?> /> <?php _e( 'Record the "Load Form" event', 'calculated-fields-form' ); ?>
											</label>
										</td>
									</tr>
									<tr>
										<td>
											<label>
												<input type="checkbox" name="cpcff_analytics_next_page_event" <?php
													echo ( !empty($data[ 'events' ]) && !empty($data[ 'events' ][ 'next_page' ]) ) ? 'CHECKED' : '';
												?> /> <?php _e( 'Record the "Next Page" event', 'calculated-fields-form' ); ?>
											</label>
										</td>
									</tr>
									<tr>
										<td>
											<label>
												<input type="checkbox" name="cpcff_analytics_previous_page_event" <?php
													echo ( !empty($data[ 'events' ]) && !empty($data[ 'events' ][ 'previous_page' ]) ) ? 'CHECKED' : '';
												?> /> <?php _e( 'Record the "Previous Page" event', 'calculated-fields-form' ); ?>
											</label>
										</td>
									</tr>
									<tr>
										<td>
											<label>
												<input type="checkbox" name="cpcff_analytics_submit_event" <?php
													echo ( !empty($data[ 'events' ]) && !empty($data[ 'events' ][ 'submit' ]) ) ? 'CHECKED' : '';
												?> /> <?php _e( 'Record the "Submit" event', 'calculated-fields-form' ); ?>
											</label>
										</td>
									</tr>
									<tr>
										<td>
											<div class="cpcff-analytics-note">
												<?php _e('Sends a hit of <b>"Event"</b> type if the form is loaded, or for every action: <b>"Next Page"</b>, <b>"Previous Page"</b>, or <b>"Submit"</b>. The hits are sent with the event\'s category: <b>"form"</b>, and the events\' actions: <b>"load"</b>, <b>"next page"</b>, <b>"previous page"</b>, and <b>"submit"</b>, respectively. Each event includes a label with the form\'s id, and in case of next and previous page events, the page number. The events <b>"next page"</b> and <b>"previous page"</b> allow to know the pages that are reached by the users, and identify problems in the form\'s structure. The <b>"submit"</b> event allows to know how many users complete the form, comparing the amount of <b>"submit"</b> events with the <b>"load"</b> events.', 'calculated-fields-form'); ?>
											</div>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td>
								<h4><?php _e( 'Send Exceptions', 'calculated-fields-form' ); ?></h4>
								<table class="cpcff-analytics">
									<tr>
										<th><?php _e('Exception', 'calculated-fields-form'); ?></th>
									</tr>
									<tr>
										<td>
											<label>
												<input type="checkbox" name="cpcff_analytics_captcha_error" <?php
													echo ( !empty($data[ 'exceptions' ]) && !empty($data[ 'exceptions' ][ 'captcha' ]) ) ? 'CHECKED' : '';
												?> /> <?php _e( 'Record the "Captcha code" errors', 'calculated-fields-form' ); ?>
											</label>
										</td>
									</tr>
									<tr>
										<td>
											<div class="cpcff-analytics-note">
												<?php _e('Sends a hit of <b>"Exception"</b> type in every failed submission by an incorrect <b>"CAPTCHA"</b> code. It allows to know if the <b>"CAPTCHA"</b> images are difficult to read, and modify its settings to solve the issue.', 'calculated-fields-form'); ?>
											</div>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
                    <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
				</div>
				<input type="hidden" name="cpcff_analytics" value="1" />
				<style>
					.cpcff-analytics-note{
						border:1px solid #F0AD4E;
						background:#FBE6CA;
						padding:10px;
					}
					.cpcff-analytics{
						border-collapse: separate;
						border: 1px solid #DADADA;
						width:100%;
					}

					.cpcff-analytics label{ display:block; clear:both;}
					.cpcff-analytics th,
					.cpcff-analytics td{ text-align:left; vertical-align:top; padding: 10px;}

					.cpcff-analytics input[type=text]{width:100%;}
					.cpcff-analytics-delete-field-btn{padding-left:10px; padding-right: 10px;}
				</style>
				<script>
					var cpcff_analytics_field_counter = <?php print $counter; ?>;
					jQuery( document ).ready(
						function($)
						{
							$( document ).on( 'click', '.cpcff-analytics-delete-field-btn', function(){ $( this ).closest( 'tr' ).remove(); } );

							$( document ).on( 'click', '.cpcff-analytics-add-field-btn', function(){
								cpcff_analytics_field_counter++;
								$( this ).closest( 'tr' ).before( '<tr><td><input type="text" name="cpcff_analytics_field['+cpcff_analytics_field_counter+']" value="" placeholder="fieldname#" /></td><td><label><input type="checkbox" name="cpcff_analytics_field_send_label['+cpcff_analytics_field_counter+']" /><?php echo esc_js( __( 'Send the field\'s label', 'calculated-fields-form' ) ); ?></label><label><input type="checkbox" name="cpcff_analytics_field_only_one['+cpcff_analytics_field_counter+']" /><?php echo esc_js( __( 'Report only the first time the field gets the focus', 'calculated-fields-form' ) ); ?></label></td><td><input type="button" value="[X]" class="cpcff-analytics-delete-field-btn button-secondary" /></td></tr>' );
							} );

							$( '.cpcff-analytics-add-field-btn' ).click();
						}
					);
				</script>
			</div>
			<?php
		}

		/************************ ADDON CODE *****************************/
        /************************ ATTRIBUTES *****************************/

		private $form_analytics_table 	 = 'cp_calculated_fields_form_analytics';
		private $analytics_inserted_flag = false;
		private $analytics_form_inserted_flag = array();

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			// To exclude from optimizers
            $this->optimizer_file = ['analytics.addon'];
            $this->optimizer_inline = ['cpcff_analytics_settings', 'GoogleAnalyticsObject'];

            $this->description = __("The add-on records the usage statistics of the form with the Google Analytics service", 'calculated-fields-form');

            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;

			add_filter( 'cpcff_the_form', array( &$this, 'cpcff_add_google_analytics_filter' ), 10, 2 );
			add_action( 'cpcff_script_after_validation', array( &$this, 'cpcff_add_submit_event_action' ), 10, 2 );

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
			$db_queries = array( "CREATE TABLE ".$wpdb->prefix.$this->form_analytics_table." (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					formid INT NOT NULL,
					property VARCHAR(250) DEFAULT '' NOT NULL,
					data text,
					UNIQUE KEY id (id)
				) $charset_collate;"
			);
			$this->_run_update_database($db_queries);
		} // End update_database

        /************************ PRIVATE METHODS *****************************/

		private function get_form_analytics( $form_id )
		{
			global $wpdb;
			return $wpdb->get_row(
						$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_analytics_table." WHERE formid=%d", $form_id )
					);
		}
        /************************ PUBLIC METHODS  *****************************/

		public function cpcff_add_google_analytics_filter( $form_content, $form_id )
		{
			$row = $this->get_form_analytics( $form_id );

			if(
				!empty( $row ) &&
				!empty( $row->property ) &&
				!empty( $row->data ) &&
				($data = @unserialize( $row->data ) ) !== false
			)
			{
				if( empty( $this->analytics_form_inserted_flag[ $form_id ] ) )
				{
					$this->analytics_form_inserted_flag[ $form_id ] = true;

					$data[ 'property' ] = $row->property;
					$form_content = "<script>var cpcff_analytics_settings = cpcff_analytics_settings || {};cpcff_analytics_settings[".$form_id."]=".json_encode($data).";</script>".$form_content;
				}

				if( !$this->analytics_inserted_flag )
				{
					$this->analytics_inserted_flag = true;

					wp_enqueue_script( 'cpcff_analytics_js', plugins_url('/analytics.addon/js/scripts.js', __FILE__), array( 'jquery' ), $this->addonID, true );

					if(preg_match('/^g\-/i', $row->property))
					{
						$form_content = '<script async src="https://www.googletagmanager.com/gtag/js?id='.esc_js($row->property).'"></script><script>window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag(\'js\', new Date());gtag(\'config\', \''.esc_js($row->property).'\');</script>'.$form_content;
					}
					else
					{
						$form_content = "<script>(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');</script>".$form_content;
					}
				}


			}

			return $form_content;
		} // End cpcff_add_google_analytics_filter

		public function cpcff_add_submit_event_action( $sequence, $form_id )
		{
			$row = $this->get_form_analytics( $form_id );

			if(
				!empty( $row ) &&
				!empty( $row->property ) &&
				!empty( $row->data ) &&
				($data = @unserialize( $row->data ) ) !== false &&
				!empty( $data[ 'events' ] ) &&
				!empty( $data[ 'events' ][ 'submit' ] )
			)
			{
				print 'if(window.ga && ga.create && ("cpcff_analytics_submit_event" in window) && !cpcff_analytics_submit_event( form, '.$form_id.' )) return false;';
			}
		} // End cpcff_add_submit_event_action

		/**
		 *	Delete the form from the addon's table
		 */
        public function delete_form( $formid)
		{
			global $wpdb;
			$wpdb->delete( $wpdb->prefix.$this->form_analytics_table, array('formid' => $formid), '%d' );
		} // End delete_form

		/**
		 *	Clone the form's row
		 */
		public function clone_form( $original_form_id, $new_form_id )
		{
			global $wpdb;

			$form_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_analytics_table." WHERE formid=%d", $original_form_id ), ARRAY_A);

			if(!empty($form_row))
			{
				unset($form_row["id"]);
				$form_row["formid"] = $new_form_id;
				$wpdb->insert( $wpdb->prefix.$this->form_analytics_table, $form_row);
			}
		} // End clone_form

		/**
		 *	It is called when the form is exported to export the addons data too.
		 *  Receive an array with the other addons data, and the form's id for filtering.
		 */
		public function export_form($addons_array, $formid)
		{
			$row = $this->get_form_analytics($formid);
			if(!empty( $row ))
			{
				$row = (array)$row;
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
					$wpdb->prefix.$this->form_analytics_table,
					$addons_array[$this->addonID]
				);
			}
		} // End import_form

    } // End Class

    // Main add-on code
    $cpcff_analytics_obj = new CPCFF_Analytics();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cpcff_analytics_obj);
}
?>