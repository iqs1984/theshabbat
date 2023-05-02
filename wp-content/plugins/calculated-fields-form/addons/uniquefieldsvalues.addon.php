<?php
/*
....
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPCFF_UniqueFieldsValues' ) )
{
    class CPCFF_UniqueFieldsValues extends CPCFF_BaseAddon
    {
		static public $category = 'Extending Features';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-uniquefieldsvalues-20210817";
		protected $name = "CFF - Unique Fields Values (Experimental)";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#uniquefieldsvalues-addon';

		public function get_addon_form_settings( $form_id )
		{
			global $wpdb;

			// Insertion in database
			if( !empty( $_REQUEST[ 'cpcff_uniquefieldsvalues_addon' ] ) )
			{

				$enabled = isset($_REQUEST['cpcff_uniquefieldsvalues_enabled']) ? 1: 0;

                $settings = array(
                    'condition'     => sanitize_text_field(wp_unslash($_REQUEST['cpcff_uniquefieldsvalues_condition'])),
                    'from'          => sanitize_text_field(wp_unslash($_REQUEST['cpcff_uniquefieldsvalues_from'])),
                    'to'            => sanitize_text_field(wp_unslash($_REQUEST['cpcff_uniquefieldsvalues_to'])),
                    'error_message' => sanitize_textarea_field(wp_unslash($_REQUEST['cpcff_uniquefieldsvalues_error_message']))
                );

				// Refresh database
				$wpdb->delete( $wpdb->prefix.$this->table, array( 'formid' => $form_id ), array( '%d' ) );
				$wpdb->insert( 	$wpdb->prefix.$this->table,
								array(
									'formid' 	=> $form_id,
									'enabled'	=> $enabled,
									'settings'	=> serialize( $settings )
								),
								array( '%d', '%d', '%s' )
							);
			}

			// Read from database and display the fields.
			$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.$this->table." WHERE formid=%d", $form_id));

            // Default values
            $enabled = 0;
			$settings = array(
                'condition'     => '',
                'from'          => '',
                'to'            => '',
                'error_message' => ''
            );

			if( !empty($row) )
			{
				$enabled = $row->enabled;
                $stored_settings = @unserialize($row->settings);
                if($stored_settings) $settings = array_merge($settings, $stored_settings);
            }

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<input type="hidden" name="cpcff_uniquefieldsvalues_addon" value="1" />
			<div id="metabox_uniquefieldsvalues_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_uniquefieldsvalues_addon_form_settings' ) ); ?>" >
				<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
				<div class="inside">
					<table class="form-table">
						<tr valign="top">
							<th scope="row"><?php _e('Enable the Unique Fields Values in the form', 'calculated-fields-form');?></th>
							<td><input type="checkbox" name="cpcff_uniquefieldsvalues_enabled" <?php print($enabled ? 'CHECKED' : ''); ?> aria-label="<?php esc_attr_e('Enabling', 'calculated-fields-form'); ?>" /></td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e('Condition to validate', 'calculated-fields-form');?></th>
							<td><input type="text" name="cpcff_uniquefieldsvalues_condition" value="<?php echo esc_attr($settings['condition']); ?>" class="width75" placeholder="fieldname#" aria-label="<?php esc_attr_e('Condition', 'calculated-fields-form'); ?>" /><br>
                            <em>Ex. fieldname1,fieldname2</em></td>
						</tr>

                        <tr valign="top">
							<th scope="row"><?php _e('Error message', 'calculated-fields-form');?></th>
							<td><textarea name="cpcff_uniquefieldsvalues_error_message" class="width75" rows="5" aria-label="<?php esc_attr_e('Error message', 'calculated-fields-form'); ?>" style="resize:vertical;"><?php print esc_textarea($settings['error_message']); ?></textarea>
                            </td>
						</tr>
                        <tr>
                            <th><?php _e('From (optional)', 'calculated-fields-form'); ?></th>
                            <td><input type="date" name="cpcff_uniquefieldsvalues_from" value="<?php echo esc_attr($settings['from']); ?>" aria-label="<?php esc_attr_e('From', 'calculated-fields-form');?>" pattern="\d{4}-\d{2}-\d{2}" /></td>
                        </tr>
                        <tr>
                            <th><?php _e('To (optional)', 'calculated-fields-form'); ?></th>
                            <td><input type="date" name="cpcff_uniquefieldsvalues_to" value="<?php echo esc_attr($settings['to']); ?>" aria-label="<?php esc_attr_e('To', 'calculated-fields-form');?>" pattern="\d{4}-\d{2}-\d{2}" /></td>
                        </tr>
					</table>
                    <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
				</div>
			</div>
			<?php
		}

		/************************ ADDON CODE *****************************/
        /************************ ATTRIBUTES *****************************/

		private $table = 'cp_calculated_fields_form_unqiue_fields_values';
		private $javascript_code = '';

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = __("The add-on verifies that the values entered by users have not been used in previous submissions. It allows to enter simple and complex verification rules (one or multiple fields separated by comma symbols)", 'calculated-fields-form');

            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;

			if(isset($_POST['cff-uniquefieldsvalues-action']))
            {
                $this->verification();
            }
            else
            {
                // Verifies the code in the submission process
                add_action( 'cpcff_process_data_before_insert', array( $this, 'before_insert' ), 10, 3 );

                // Checks the form's settings and generate the javascript code
                add_filter( 'cpcff_the_form', array( &$this, 'integration' ), 10, 2 );

                // Validate code
                add_action( 'cpcff_script_after_validation', array( &$this, 'validation_code'), 10, 2 );

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
			$db_queries = array( "CREATE TABLE ".$wpdb->prefix.$this->table." (
					formid INT NOT NULL,
					enabled TINYINT DEFAULT 0 NOT NULL ,
					settings MEDIUMTEXT,
					PRIMARY KEY (formid)
				) $charset_collate;"
			);
			$this->_run_update_database($db_queries);
		} // End update_database

        /************************ PRIVATE METHODS *****************************/

        private function _get_settings($formid)
        {
            global $wpdb;
            $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.$this->table." WHERE formid=%d", $formid));
            if(
                !empty($row) &&
                $row->enabled
            )
            {
                $settings = @unserialize($row->settings);
                if(!empty($settings['condition']))
                return $settings;
            }
            return false;
        } // End _get_settings

        private function _verify($formid, $settings, $params, $suffix = '')
        {
            global $wpdb;

            $settings['condition'] = strtolower($settings['condition']);
            if(preg_match_all('/fieldname\d+/i', $settings['condition'], $matches))
            {
                // Initialization
                $fields = [];
                foreach($matches[0] as $field)
                {
                    $fields[$field] = isset($params[$field.$suffix]) ? $params[$field.$suffix] : null;
                }
                $count = count($fields);

                if($count == 0) return false; // Nothing to check

                // Read submissions
                $table_exists = $wpdb->get_results('SHOW TABLES LIKE "'.$wpdb->prefix.'cp_calculated_fields_user_submission"');

                $where = $wpdb->prepare(' WHERE posts.formid=%d', $formid);
                if(!empty($table_exists))
                {
                    $query = 'SELECT posts.id as id, posts.paypal_post as data FROM '.CP_CALCULATEDFIELDSF_POSTS_TABLE_NAME.' posts LEFT JOIN '.$wpdb->prefix.'cp_calculated_fields_user_submission usersposts ON (posts.id=usersposts.submissionid)';
                    $where .= ' AND (usersposts.active=1 OR usersposts.active is NULL)';
                }
                else
                {
                    $query = 'SELECT posts.id as id, posts.paypal_post as data FROM '.CP_CALCULATEDFIELDSF_POSTS_TABLE_NAME.' posts';
                }

                if(!empty($settings['from'])) $query .= $wpdb->prepare(' AND posts.time >= %s', $settings['from']);
                if(!empty($settings['to'])) $query .= $wpdb->prepare(' AND posts.time <= %s', $settings['to']);

                $query .= $where.' ORDER BY posts.id DESC';
                $rows = $wpdb->get_results($query);

                // Check condition
                foreach($rows as $row)
                {
                    $c = 0;
                    $data = unserialize($row->data);
                    if($data)
                    {
                        foreach($fields as $field => $value)
                        {
                            if(!isset($data[$field]) || $data[$field] != $value) break;
                            $c++;
                        }
                    }

                    if(
                        $c == $count &&
                        (   // "cpcff_submission_id" is used by the Users Permissions add-on
                            empty($params['cpcff_submission_id']) ||
                            $params['cpcff_submission_id'] != $row->id
                        )
                    ) return true;
                }
            }

            return false;
        } // End _verify

        /************************ PUBLIC METHODS  *****************************/

        public function verification()
        {
            if(isset($_POST['cff-uniquefieldsvalues-action']))
            {
                if(
                    isset($_POST['cp_calculatedfieldsf_id']) &&
                    is_numeric($_POST['cp_calculatedfieldsf_id'])
                )
                {
                    $formid = intval($_POST['cp_calculatedfieldsf_id']);
                    $settings = $this->_get_settings($formid);
                    if($settings) print json_encode($this->_verify($formid, $settings, $_POST, $_POST['cp_calculatedfieldsf_pform_psequence']));
                    remove_all_actions('shutdown');
                    exit;
                }
            }
        } // End init

        public function before_insert(&$params, &$str, $fields)
        {
            $formid     = $params['formid'];
            $settings   = $this->_get_settings($formid);
            if($settings)
            {
                $result = $this->_verify($formid, $settings, $params);
                if($result)
                {
                    print $settings['error_message'];
                    remove_all_actions('shutdown');
                    exit;
                }
            }
        } // End before_insert

        public function validation_code( $sequence, $formid )
        {
            if(($settings = $this->_get_settings($formid)) != false)
            {
                $c   = CPCFF_MAIN::$form_counter;
                $url = CPCFF_AUXILIARY::site_url(true);
                if(strpos($url, '?') === false) $url = rtrim($url, '/').'/';
            ?>
                if(
					typeof validation_rules['<?php print esc_js($this->addonID); ?>'] == 'undefined' ||
					validation_rules['<?php print esc_js($this->addonID); ?>'] == false
				)
				{
					validation_rules['<?php print esc_js($this->addonID); ?>'] = false;
                    var uniquefieldsvalues_formdata = new FormData(_form[0]);
                    uniquefieldsvalues_formdata.append('cff-uniquefieldsvalues-action', 'verify');
                    $dexQuery.ajax({
                        type: 'POST',
                        url : '<?php print esc_js($url); ?>',
                        data: uniquefieldsvalues_formdata,
                        processData: false,
                        contentType: false,
                        success: function(result){
                            result = JSON.parse(result);
                            enabling_form();
                            if(result)
                            {
                                alert('<?php print esc_js($settings['error_message']); ?>');
                                return false;
                            }
                            else
                            {
                                validation_rules['<?php print esc_js($this->addonID); ?>'] = true;
                                processing_form();
                            }
                        }
                    });
                }
            <?php
            }
        } // End validation_code

		/**
		 * Checks the form's settings and generates the javascript code
		 */
		public function integration( $content, $formid )
		{
            if($this->_get_settings($formid) != false) wp_enqueue_script('jquery-form');
			return $content;
		} // End integration

		/**
		 *	Delete the form from the addon's table
		 */
        public function delete_form( $formid)
		{
			global $wpdb;
			$wpdb->delete( $wpdb->prefix.$this->table, array('formid' => $formid), '%d' );
		} // delete_form

		/**
		 *	Clone the form's row
		 */
		public function clone_form( $original_form_id, $new_form_id )
		{
			global $wpdb;

			$form_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->table." WHERE formid=%d", $original_form_id ), ARRAY_A);

			if(!empty($form_row))
			{
				$form_row["formid"] = $new_form_id;
				$wpdb->insert( $wpdb->prefix.$this->table, $form_row);
			}
		} // End clone_form

		/**
		 *	It is called when the form is exported to export the addons data too.
		 *  Receive an array with the other addons data, and the form's id for filtering.
		 */
		public function export_form($addons_array, $formid)
		{
			global $wpdb;
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->table." WHERE formid=%d", $formid ), ARRAY_A );
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
					$wpdb->prefix.$this->table,
					$addons_array[$this->addonID]
				);
			}
		} // End import_form

	} // End Class

    // Main add-on code
    $cpcff_uniquefieldsvalues_obj = new CPCFF_UniqueFieldsValues();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cpcff_uniquefieldsvalues_obj);
}
?>