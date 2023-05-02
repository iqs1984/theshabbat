<?php
/*
....
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPCFF_CSVGenerator' ) )
{
    class CPCFF_CSVGenerator extends CPCFF_BaseAddon
    {
		static public $category = 'Extending Features';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-csv-generator-20190126";
		protected $name = "CFF - CSV Generator";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#csv-generator-addon';

		public function get_addon_form_settings( $formid )
		{
			$this->formid = $formid;

			if( isset( $_POST[ 'cpcff_csvgenerator' ] ) )
			{
				// Save the addon settings
				$this->settings = array();
				$cpcff_csvgenerator = stripslashes_deep($_POST['cpcff_csvgenerator']);

				$this->settings['enabled'] 	= (isset($cpcff_csvgenerator['enabled'])) ? 1 : 0;
				$this->settings['after_payment'] = (isset($cpcff_csvgenerator['after_payment'])) ? 1 : 0;
				$this->settings['location'] = (isset($cpcff_csvgenerator['location'])) ? trim($cpcff_csvgenerator['location']) : '';
				$this->settings['action'] 	= (isset($cpcff_csvgenerator['action']) && $cpcff_csvgenerator['action'] == 'new') ? 'new' : 'append';
				$this->settings['file'] 	= (isset($cpcff_csvgenerator['file'])) ? trim($cpcff_csvgenerator['file']) : '';
				$this->settings['own_email'] 	= (isset($cpcff_csvgenerator['own_email'])) ? 1 : 0;
				$this->settings['user_email'] 	= (isset($cpcff_csvgenerator['user_email'])) ? 1 : 0;
                $this->settings['fields']   = (isset($cpcff_csvgenerator['fields']) && $cpcff_csvgenerator['fields'] == 'some') ? 'some' : 'all';
                $this->settings['fields_list'] = [];
                if(isset($cpcff_csvgenerator['fields_list']) && is_array($cpcff_csvgenerator['fields_list']))
                {
                   foreach($cpcff_csvgenerator['fields_list'] as $field)
                   {
                       $field = trim($field);
                       if(preg_match('/fieldname\d+/', $field)) $this->settings['fields_list'][] = $field;
                   }
                }
                update_option($this->settings_var.$this->formid, $this->settings);
			}

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<div id="metabox_csv_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_csv_addon_form_settings' ) ); ?>" >
				<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
				<div class="inside">
					<table cellspacing="0" class="form-table width100">
						<tr>
							<th valign="top"><?php _e('Enabled', 'calculated-fields-form');?>:</th>
							<td style="padding-bottom:10px;">
								<input type="checkbox" name="cpcff_csvgenerator[enabled]" <?php
									if($this->_get_attr('enabled', false)) print "CHECKED";
								?> /> <em><?php _e('Enabling the CSV generator in this form', 'calculated-fields-form'); ?></em>
							</td>
						</tr>
						<tr>
							<th valign="top"></th>
							<td style="padding-bottom:10px;">
								<input type="checkbox" name="cpcff_csvgenerator[after_payment]" <?php
									if($this->_get_attr('after_payment', false)) print "CHECKED";
								?> /> <?php _e('Generate files after payment confirmation (this feature applies only with payment confirmations from payment gateways)', 'calculated-fields-form'); ?><br><br>
                                <em><?php _e('If the checkbox is ticked, and you want to attach the file to the emails, you should configure the form to send the emails after payment confirmation too.', 'calculated-fields-form')?></em>
							</td>
						</tr>
						<tr>
							<th valign="top"><?php _e('Directory', 'calculated-fields-form');?>:</th>
							<td style="padding-bottom:10px;">
								<input type="text" name="cpcff_csvgenerator[location]" value="<?php print esc_attr($this->_get_attr('location')); ?>" required class="width75" /><br>
								<em><?php _e('Enter the directory where storing the CSV file. This directory must be into the /wp-content/uploads directory. If the directory does not exists, the plugin creates it', 'calculated-fields-form'); ?></em>
							</td>
						</tr>
						<tr>
							<th valign="top"><?php _e('File', 'calculated-fields-form');?>:</th>
							<td style="padding-bottom:10px;">
								<input type="radio" name="cpcff_csvgenerator[action]" value="new" <?php
									if($this->_get_attr('action', 'new') == 'new') print "CHECKED";
									?> />
								<em><?php _e('create a new file CSV file per submission. The files names are generated with the format &lt;submission id&gt;.csv', 'calculated-fields-form'); ?></em>
								<hr />
								<input type="radio" name="cpcff_csvgenerator[action]" value="append" <?php
									if($this->_get_attr('action', 'new') == 'append') print "CHECKED";
									?> />
								<em><?php _e('append the data to an existent CSV file', 'calculated-fields-form'); ?></em><br /><br />
								<input type="text" name="cpcff_csvgenerator[file]" placeholder="<?php esc_attr_e('file name', 'calculated-fields-form'); ?>" value="<?php print esc_attr($this->_get_attr('file', '')); ?>" class="width75" /><br />
								<?php _e('If the file does not exists the plugin creates it', 'calculated-fields-form'); ?>
							</td>
						</tr>
                        <tr>
							<th valign="top"><?php _e('Include fields','calculated-fields-form'); ?>:</th>
							<td>
                                <table width="100%" border="0">
                                    <tr>
                                        <td width="50%" style="vertical-align:top;">
                                            <label>
                                                <input type="radio" name="cpcff_csvgenerator[fields]" <?php if($this->_get_attr('fields', 'all') == 'all') print 'CHECKED'; ?> value="all" />
                                                <?php _e('Send every field to the CSV file', 'calculated-fields-form'); ?>
                                            </label>
                                        </td>
                                        <td width="50%" style="vertical-align:top;">
                                            <label>
                                                <input type="radio" name="cpcff_csvgenerator[fields]" <?php if($this->_get_attr('fields', 'all') == 'some') print 'CHECKED'; ?> value="some" />
                                                <?php _e('Send only the following fields'); ?>
                                            </label>
                                            <select style="width:100%;margin:10px 0;" name="cpcff_csvgenerator[fields_list][]" multiple size="10">
                                            </select>
                                            <p><i><?php _e('Some attributes, such as form ID, submission ID and submission date, are sent to the CSV file in addition to the list of fields.'); ?></i></p>
                                        </td>
                                    </tr>
                                </table>
							</td>
						</tr>
						<tr>
							<th valign="top"><?php _e('Send file by email','calculated-fields-form'); ?>:</th>
							<td>
								<input type="checkbox" name="cpcff_csvgenerator[own_email]" <?php if($this->_get_attr('own_email', false)) print 'CHECKED'; ?> /> <?php _e('To the email addresses entered through the "Destination emails" attribute', 'calculated-fields-form'); ?><br />
								<input type="checkbox" name="cpcff_csvgenerator[user_email]" <?php if($this->_get_attr('user_email', false)) print 'CHECKED'; ?> /> <?php _e('To the email addresses entered by the user', 'calculated-fields-form'); ?><br />
							</td>
						</tr>
					</table>
					<div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
				</div>
			</div>
			<script>
			jQuery(function(){
				var $ = jQuery,
                    _enabled 		= $('[name="cpcff_csvgenerator[enabled]"]'),
					_action_new 	= $('[name="cpcff_csvgenerator[action]"][value="new"]'),
					_action_append 	= $('[name="cpcff_csvgenerator[action]"][value="append"]'),
					_location 		= $('[name="cpcff_csvgenerator[location]"]'),
					_file 			= $('[name="cpcff_csvgenerator[file]"]');
				_enabled.change(function(){
					_file.removeProp('required').removeAttr('required');
					_location.removeProp('required').removeAttr('required');
					if(this.checked)
					{
						_location.prop('required', true).attr('required', 'required');
						if(_action_append.is(':checked')) _file.prop('required', true).attr('required', 'required');
					}
				});
				_action_append.change(function(){
					_file.removeProp('required').removeAttr('required');
					if(this.checked) _file.prop('required', true).attr('required', 'required');
				});
				_enabled.change();
                function update_fields_list()
                {
                    if('cff_form' in  window)
                    {
                        var e = $('[name*="cpcff_csvgenerator[fields_list]"]'),
                            items = window['cff_form'].fBuild.getItems(),
                            fields_list = <?php print json_encode($this->_get_attr('fields_list', [])); ?>;
                        e.html('');
                        for(var i in items)
                        {
                            if(
                                !/(fsectionbreak)|(fpagebreak)|(fsummary)|(fcontainer)|(ffieldset)|(fdiv)|(fmedia)|(fbutton)|(fhtml)|(frecordsetds)|(fcommentarea)/i.test(items[i].ftype) &&
                                (!('exclude' in items[i]) || !items[i]['exclude'])
                            )
                            {
                                e.append(
                                    $('<option>').attr(
                                                    'SELECTED',
                                                    (fields_list.length && fields_list.indexOf(items[i]['name']) != -1) ? true : false
                                                 ).val(items[i]['name'])
                                                 .text(items[i]['name']+' ('+items[i]['title']+')')
                                );
                            }
                        }

                    }
                }
                $(document).on('cff_reloadItems', function(evt, items){update_fields_list();});
                update_fields_list();
			});
			</script>
			<?php
		}

		/************************ ADDON CODE *****************************/
        /************************ ATTRIBUTES *****************************/

		private $settings_var = 'cpcff_csvgenerator_';
		private $settings;
		private $formid;

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = __("The add-on allows to generate CSV files dynamically with the submissions", 'calculated-fields-form' );

			// Check if the plugin is active
			if( !$this->addon_is_active() ) return;

			add_filter( 'cpcff_notification_email_attachments', array( &$this, 'attach_file_to_notification_email' ), 1, 4 );
			add_filter( 'cpcff_confirmation_email_attachments', array( &$this, 'attach_file_to_confirmation_email' ), 1, 4 );
			add_filter( 'cpcff_custom_tags', array( &$this, 'replace_tags' ), 1, 2);

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

			add_action( 'cpcff_process_data', array( &$this, 'cpcff_process_data_action' ) );
            add_action( 'cpcff_payment_processed', array( &$this, 'cpcff_payment_processed_action' ), 1 );
        } // End __construct

        /************************ PROTECTED METHODS *****************************/
		/************************ PRIVATE METHODS *****************************/

		/**
		 * Read the form's settings from the database and unserialize it
		 */
		private function _get_attr( $attr, $default = '' )
		{
			if(!isset($this->settings)) $this->settings = get_option($this->settings_var.$this->formid, array());
			if(isset($this->settings[$attr])) return $this->settings[$attr];
			return $default;
		} // End  _get_attr

		/**
		 * Returns canonicalized absolute pathname
		 * Realpath is not a solution because the path can no exists
		 */
		private function _get_absolute_path( $path )
		{
			$path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
			$parts = explode(DIRECTORY_SEPARATOR, $path);
			$absolutes = array();
			foreach ($parts as $part)
			{
				if ('.' == $part) continue;
				if ('..' == $part) array_pop($absolutes);
				else $absolutes[] = $part;
			}
			return implode(DIRECTORY_SEPARATOR, $absolutes);
		} // End _get_absolute_path

		private function _get_file_path($itemnumber, $get_url = false)
		{
			$path = $this->_get_attr('location', '');
			$uploads = wp_upload_dir();
			if($uploads)
			{
				$base  = $this->_get_absolute_path(rtrim($uploads['basedir'], '/'));
				$url   = rtrim( $uploads['baseurl'], '/' ).'/'.trim($path, '/');
				$path  = $this->_get_absolute_path($base.'/'.trim($path, '/'));

				if(strpos($path, $base) === 0 || apply_filters('cff-csv-absolute-path', false))
				{
					if($this->_get_attr('action', 'new') == 'new') $file = $itemnumber.'.csv';
					else $file 	= $this->_get_attr('file', '');
					$file = $this->_sanitize_file_name($file);
					if(!empty($file))
					{
						$path = rtrim($path, '/').'/'.$file;
						$url  = rtrim($url, '/').'/'.$file;
						$url = str_replace('\\', '/', $url);
						if(file_exists($path)) return $get_url ? $url : $path;
					}
				}
			}
			return false;
		} // End _get_file_path

		/**
		 * Remove all invalid characters from the file's name
		 */
		private function _sanitize_file_name($file)
		{
			$file = str_replace(array('\\', '/'), '', $file);
			$file = sanitize_file_name($file);
			return $file;
		} // End _get_file_name

		/**
		 *  Add a value to the row in the index corresponding to the column, or add the colunm to the columns list.
		 *  @param $value, value to include add to the row
		 *  @param $values, reference to the row
		 *	@param $column, column name for the value
		 *	@param $columns, reference to the file columns
		 */
		private function _add_value($value, &$values, $column, &$columns)
		{
			$index = array_search($column, $columns);
			if($index === false){$index = count($columns); $columns[] = $column;}
			$values[$index] = preg_replace("/(\r|\n)/", " ", is_array($value) ? implode(", ", $value) : $value);
		} // _add_value

		private function _lock($path,$file,$alt)
		{
			$path_file = $path.'/'.$file;
			if(get_transient($file))
			{
				$path_file = $path.'/'.$alt.'.csv';
			}
			set_transient($file, true, 30);
			return $path_file;
		} // End _lock

		private function _unlock($file)
		{
			delete_transient($file);
		} // End _unlock

		/**
		 * Generate the CSV file
		 * @param $data, associative array with the information collected by the form.
		 * @param $file, the path to the CSV file (the file can exists or not)
		 */
		private function _generate_file($data, $path, $file)
		{
			global $wpdb;

			$item = $wpdb->get_row($wpdb->prepare('SELECT * FROM '.CP_CALCULATEDFIELDSF_POSTS_TABLE_NAME.' WHERE id=%d', $data['itemnumber']));

			if($item)
			{
				try
				{
					$path_file = $this->_lock($path,$file, $item->id);
					$headers = array();
					$rows = array();

					if(file_exists($path_file) && ($file_content = file_get_contents($path_file)) !== false)
					{
						$file_content = preg_replace("/\r\n|\n\r|\n|\r/", "\n", $file_content);
						$csv_lines = explode( "\n", $file_content );
						$headline = true;
						foreach($csv_lines as $line)
						{
							if(empty($line)) continue;
							if($headline)
							{
								$headers = str_getcsv($line, ',');
								$headline = false;
							}
							else
							{
								$rows[] = str_getcsv($line, ',');
							}
						}
					}

					// The CSV file does not exists or could not be read
					if(empty($headers))
					{
						$headers = array("Form ID", "Submission ID", "Time", "IP Address", "email", "Paid", "Final Price", "Coupon");
					}

					$toExclude = array( 'fcommentarea', 'fsectionbreak', 'fpagebreak', 'fsummary', 'fmedia', 'ffieldset', 'fdiv', 'fbutton', 'frecordsetds', 'fhtml' );

                    $fields = $this->_get_attr('fields', 'all');
                    $fields_set = $this->_get_attr('fields_list', []);

					// Read the form's structure
					$cpcff_main = CPCFF_MAIN::instance();
					$form_obj = $cpcff_main->get_form($data['formid']);
					$form_data = $form_obj->get_option( 'form_structure', CP_CALCULATEDFIELDSF_DEFAULT_form_structure );
					$fields_list = cp_calculatedfieldsf_sorting_fields_in_containers( $form_data[ 0 ] );

					$values = array(); // The new row

					$this->_add_value($item->formid, $values, 'Form ID', $headers);
					$this->_add_value($item->id, $values, 'Submission ID', $headers);
					$this->_add_value($item->time, $values, 'Time', $headers);
					$this->_add_value($item->ipaddr, $values, 'IP Address', $headers);
					$this->_add_value($item->notifyto, $values, 'email', $headers);
					$this->_add_value(($item->paid ? 'Yes' : 'No'), $values, 'Paid', $headers);
					$this->_add_value(@$data['final_price'], $values, 'Final Price', $headers);
					$this->_add_value(@$data['coupon'], $values, 'Coupon', $headers);

					for( $i = 0; $i < count( $fields_list ); $i++ )
					{
						$field = $fields_list[ $i ];
						$fieldType = strtolower( $field->ftype );

						if(
                            !in_array($fieldType, $toExclude) &&
                            ($fields == 'all' || in_array($field->name, $fields_set))
                        )
						{
							// Add to the row only the submitted fields
							$fieldName = $field->name;
							if(isset($data[$fieldName]))
							{
								$columnName = ( !empty( $field->shortlabel ) ) ? $field->shortlabel : ( ( !empty( $field->title ) ) ? $field->title : $fieldName );
								$this->_add_value(@$data[$fieldName], $values, $columnName, $headers);
							}
						}
					}

					// Fill the missing indexes in the array
					$m = max(array_keys($values));
					for($i = 0; $i<=$m; $i++)
					{
						$values[$i] = isset($values[$i]) ? $values[$i] : '';
					}
					ksort($values);
					$rows[] = $values; // Add the new row to the CSV rows

					// Write the CSV file
					$handle = fopen("php://output", "w");
					ob_start();
					fputcsv($handle, $headers);
					foreach($rows as $row)
					{
						fputcsv($handle, $row);
					}
					$csvContent = ob_get_clean();
					file_put_contents($path_file, $csvContent, LOCK_EX);
					$this->_unlock($file);
				}
				catch(Exception $err)
				{
					// Error log: Error generating the CSV file
					error_log('CFF CSV Generator: There was an error generating the CSV file: '.$err->getMessage());
					return false;
				}
				return true;
			}
			else return false;

		} // End _generate_file

		/************************ PUBLIC METHODS  *****************************/

        /**
         * Process the cpcff_process_data action
         */
		public function cpcff_process_data_action( $params )
		{
			$this->_generate_csv( $params, false );
		}

		/**
         * Process the cpcff_payment_processed action
         */
		public function cpcff_payment_processed_action( $params )
		{
			$this->_generate_csv( $params, true );
		}

		/**
		 * Generate the CSV file
		 */
		public function _generate_csv($params, $payment_processed)
		{
			$this->formid = $params['formid'];
			$enabled = $this->_get_attr('enabled', false);
			if($enabled)
			{
                $after_payment = $this->_get_attr('after_payment', false);

                if(
                    ($after_payment && !$payment_processed) ||
                    (!$after_payment && $payment_processed)
                ) return;

				$path = $this->_get_attr('location', '');
				$uploads = wp_upload_dir();
				if($uploads)
				{
					$base  = $this->_get_absolute_path(rtrim($uploads['basedir'], '/'));
					$path  = $this->_get_absolute_path($base.'/'.trim($path, '/'));

					if(strpos($path, $base) === 0 || apply_filters('cff-csv-absolute-path', false)) // Check if the directory where store the file is in the Uploads directory
					{
						if(wp_mkdir_p($path)) // Create the directory if does not exists
						{
							$action = $this->_get_attr('action', 'new');
							if($action == 'new') $file 	= $params['itemnumber'].'.csv';
							else $file 	= $this->_get_attr('file', '');
							$file = $this->_sanitize_file_name($file);
							if(!empty($file))
							{
								$response = $this->_generate_file($params, $path, $file); // Generates the CSV file
								if($response == false)
								{
									// Error log: file could not be generated
									error_log('CFF CSV Generator: The CSV file could not be generated');
								}
							}
							else
							{
								// Error log: file is empty
								error_log('CFF CSV Generator: The file name is empty');
							}
						}
						else
						{
							// Error log: the directory cannot be created
							error_log('CFF CSV Generator: The directory for storing the CSV files cannot be created');
						}
					}
					else
					{
						// Error log: the container directory is not into the uploads one
						error_log('CFF CSV Generator: The directory where store the CSV files is not into the UPLOADS directory');
					}
				}
				else
				{
					// Error log: no uploads directory
					error_log('CFF CSV Generator: The UPLOADS directory cannot be located');
				}
			}
		} // End _generate_csv

		public function replace_tags($text, $submission_id)
		{
			$url = $this->_get_file_path($submission_id, true);
			if($url)
			{
				$text = str_replace('<%csv_generator_url%>', $url, $text);
			}
			else
			{
				$text = str_replace('<%csv_generator_url%>', '', $text);
			}
			return $text;
		} // End replace_tags

		public function attach_file_to_confirmation_email($files, $params, $form_id, $submission_id)
		{
			$this->formid = $form_id;
			if($this->_get_attr('enabled', false) && $this->_get_attr('user_email', 0))
			{
				if(($file = $this->_get_file_path($submission_id)) !== false) $files[] = $file;
			}
			return $files;
		} // End attach_file_to_confirmation_email

		public function attach_file_to_notification_email($files, $params, $form_id, $submission_id)
		{
			$this->formid = $form_id;
			if($this->_get_attr('enabled', false) && $this->_get_attr('own_email', 0))
			{
				if(($file = $this->_get_file_path($submission_id)) !== false) $files[] = $file;
			}
			return $files;
		} // End attach_file_to_notification_email

		/**
		 *	Delete the form from the addon's table
		 */
        public function delete_form( $formid)
		{
			delete_option( $this->settings_var.$formid);
		} // delete_form

		/**
		 *	Clone the form's row
		 */
		public function clone_form( $original_formid, $new_formid )
		{
			$settings = get_option($this->settings_var.$original_formid);
			if($settings !== false) update_option($this->settings_var.$new_formid, $settings);
		} // End clone_form

		/**
		 *	It is called when the form is exported to export the addons data too.
		 *  Receive an array with the other addons data, and the form's id for filtering.
		 */
		public function export_form($addons_array, $formid)
		{
			$settings = get_option($this->settings_var.$formid);
			if($settings !== false)
			{
				$addons_array[ $this->addonID ] = $settings;
			}
			return $addons_array;
		} // End export_form

		/**
		 *	It is called when the form is imported to import the addons data too.
		 *  Receive an array with all the addons data, and the new form's id.
		 */
		public function import_form($addons_array, $formid)
		{
			if(isset($addons_array[$this->addonID]))
			{
				update_option($this->settings_var, $addons_array[$this->addonID]);
			}
		} // End import_form

		/**
		 * WARNING :: Remove all options created by the plugin
		 */
		public function complete_uninstall()
		{
			global $wpdb;
			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
					$this->settings_var."%"
				)
			);
		} // End complete_uninstall
    } // End Class

    // Main add-on code
    $cpcff_csvgenerator_obj = new CPCFF_CSVGenerator();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cpcff_csvgenerator_obj);
}