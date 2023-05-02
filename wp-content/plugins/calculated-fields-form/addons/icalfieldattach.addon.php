<?php
/*
....
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPCFF_iCalFieldAttach' ) )
{
    class CPCFF_iCalFieldAttach extends CPCFF_BaseAddon
    {
		static public $category = 'Extending Features';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-cfficalattachment-20180630";
		protected $name = "CFF - iCal Export Attached";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#ical-addon';
        protected $generated_file = '';

		public function get_addon_form_settings( $form_id )
		{
			global $wpdb;

			// Insertion in database
			if(
				isset( $_REQUEST[ 'icalacff_observe_day_light' ] )
			)
			{
				$wpdb->delete( $wpdb->prefix.$this->form_table, array( 'formid' => $form_id ), array( '%d' ) );
				$wpdb->insert( 	$wpdb->prefix.$this->form_table,
								array(
									'formid' => $form_id,

									'observe_day_light'	 => stripcslashes($_REQUEST["icalacff_observe_day_light"]),
									'ical_daylight_zone'	 => stripcslashes($_REQUEST["icalacff_ical_daylight_zone"]),
									'cal_time_zone_modify'	 => stripcslashes($_REQUEST["icalacff_cal_time_zone_modify"]),
                                    'sourcefield'	 => trim(stripcslashes($_REQUEST["icalacff_sourcefield"])),
                                    'base_summary'	 => stripcslashes($_REQUEST["icalacff_base_summary"]),
                                    'base_description'	 => stripcslashes($_REQUEST["icalacff_base_description"])
								),
								array( '%d', '%s', '%s', '%s', '%s', '%s', '%s' )
							);
			}

			$rows = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $form_id )
					);
			if (!count($rows))
			{
			    $row["observe_day_light"] = "true";
			    $row["ical_daylight_zone"] = "EUROPE";
                $row["cal_time_zone_modify"] = '';
                $row["sourcefield"] = '';
                $row["base_summary"] = 'Booking for <%fieldname1%>';
                $row["base_description"] = 'Booking for <%fieldname1%>';
			} else {
			    $row["observe_day_light"] = $rows[0]->observe_day_light;
			    $row["ical_daylight_zone"] = $rows[0]->ical_daylight_zone;
                $row["cal_time_zone_modify"] = $rows[0]->cal_time_zone_modify;
                $row["sourcefield"] = $rows[0]->sourcefield;
                $row["base_summary"] = $rows[0]->base_summary;
                $row["base_description"] = $rows[0]->base_description;
			}

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<div id="metabox_ical_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_ical_addon_form_settings' ) ); ?>" >
				<h3 class='hndle' style="padding:5px;"><span><?php print esc_html($this->name); ?></span></h3>
				<div class="inside">
                   <table class="form-table width100">
                    <tr valign="top">
                    <th scope="row"><?php _e('ID of the field that contains the date', 'calculated-fields-form'); ?></th>
                    <td><input type="text" name="icalacff_sourcefield" value="<?php echo esc_attr($row["sourcefield"]); ?>" placeholder="fieldname#" class="width75" /><br />
                         <em>* Must be a date field. Example: fieldname1 </em>
                    </td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('iCal entry summary', 'calculated-fields-form'); ?></th>
                    <td><textarea name="icalacff_base_summary" rows="3" class="width75"><?php echo esc_textarea($row["base_summary"]); ?></textarea>
                         <br />
                         <em>* Note: You can get the field IDs/tags from the form builder.</em>
                    </td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('iCal entry description', 'calculated-fields-form'); ?></th>
                    <td><textarea name="icalacff_base_description" rows="3" class="width75"><?php echo esc_textarea($row["base_description"]); ?></textarea>
                         <br />
                         <em>* Note: You can get the field IDs/tags from the form builder.</em>
                    </td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('iCal timezone difference vs server time', 'calculated-fields-form'); ?></th>
                    <td><select name="icalacff_cal_time_zone_modify" class="width20">
                          <option value="">- none -</option>
                          <?php for ($i=-23;$i<24; $i++) { ?>
                           <option value="<?php $text = " ".($i<=0?"":"+").$i." hours"; echo esc_attr($text); ?>" <?php if ($row["cal_time_zone_modify"] == $text) echo ' selected'; ?>><?php echo esc_html($text); ?></option>
                          <?php } ?>
                         </select>
                         <br /><em>Note: Current server time is <?php echo date("Y-m-d H:i");?></em>
                    </td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Observe daylight saving time?', 'calculated-fields-form'); ?></th>
                    <td><select name="icalacff_observe_day_light">
                          <option value="true" <?php if ($row["observe_day_light"] == '' || $row["observe_day_light"] == 'true') echo ' selected'; ?>>Yes</option>
                          <option value="false" <?php if ($row["observe_day_light"] == 'false') echo ' selected'; ?>>No</option>
                         </select>
                    </td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Daylight saving time zone', 'calculated-fields-form'); ?></th>
                    <td><select name="icalacff_ical_daylight_zone" class="width20">
                          <option value="EUROPE" <?php if ($row["ical_daylight_zone"] == '' || $row["ical_daylight_zone"] == 'EUROPE') echo ' selected'; ?>>Europe</option>
                          <option value="USA" <?php if ($row["ical_daylight_zone"] == 'USA') echo ' selected'; ?>>USA</option>
                         </select>
                    </td>
                    </tr>
                  </table>
                  <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
				</div>
			</div>
			<?php
		}

		/************************ ADDON CODE *****************************/
        /************************ ATTRIBUTES *****************************/

		private $form_table = 'cp_calculated_fields_form_icalfieldattachment';

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = __("The add-on allows to attach an iCal file with the date of a field", 'calculated-fields-form' );
            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;

			// attach the iCal file
            add_filter( 'cpcff_notification_email_attachments', array( &$this, 'attach_ical_file' ), 10, 4 );
            add_filter( 'cpcff_confirmation_email_attachments', array( &$this, 'attach_ical_file' ), 10, 4 );

            add_action( 'cpcff_notification_email_sent', array(&$this, 'delete_ical_file'), 10, 1 );

            add_action( 'cpcff_user_email_sent', array(&$this, 'delete_ical_file'), 10, 1 );

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
			$db_queries = array( "CREATE TABLE ".$wpdb->prefix.$this->form_table." (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					formid INT NOT NULL,
					cal_time_zone_modify varchar(255) DEFAULT '' NOT NULL ,
                    observe_day_light varchar(255) DEFAULT '' NOT NULL ,
                    ical_daylight_zone varchar(255) DEFAULT '' NOT NULL ,
                    sourcefield varchar(200) DEFAULT '' NOT NULL ,
                    base_summary TEXT DEFAULT '' NOT NULL ,
                    base_description TEXT DEFAULT '' NOT NULL ,
					UNIQUE KEY id (id)
				) $charset_collate;"
			);
			$this->_run_update_database($db_queries);
		} // End update_database

        /************************ PRIVATE METHODS *****************************/

		/************************ PUBLIC METHODS  *****************************/


		/**
         * Delete temporal file for iCal attachment
         */
        function delete_ical_file ($submission_id)
        {
            if (file_exists($this->generated_file))
                @unlink($this->generated_file);
        }


		/**
         * Generate iCal attachment
         */
        function attach_ical_file( $attachments, $params, $form_id, $submission_id)
        {
            global $wpdb;

            $icalSettings = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $form_id )
					);

            $data = $params;
            $ct = 0;
            if(!empty($data[ $icalSettings[0]->sourcefield]))
            {
                $buffer  = "BEGIN:VCALENDAR\n";
                $buffer .= "PRODID:-//CodePeople//Calculated Fields Form Plugin for WordPress//EN\n";
                $buffer .= "VERSION:2.0\n";

				$date = $data[ $icalSettings[0]->sourcefield];
				$form_obj = new CPCFF_FORM($form_id);
				$form_fields = $form_obj->get_fields();
				if(isset($form_fields[$icalSettings[0]->sourcefield]))
				{
					$field = $form_fields[$icalSettings[0]->sourcefield];
					if($field->ftype == 'fdate')
					{
						$date_parts = explode($field->dseparator, $date);
						switch($field->dformat)
						{
							case 'dd/mm/yyyy':
								$date = implode('-', $date_parts);
								break;
							case 'yyyy/dd/mm':
								$date_parts_sub = explode(' ', $date_parts[2]);
								$t = $date_parts_sub[0];
								$date_parts_sub[0] = $date_parts[1];
								$date_parts[1] = $t;
								$date_parts[2] = implode(' ', $date_parts_sub);
							case 'mm/dd/yyyy':
							case 'yyyy/mm/dd':
								$date = implode('/', $date_parts);
								break;
						}
					}
				}
				$orgdatetime = strtotime($date);
				if (date( "H:i", $orgdatetime) == '00:00')
                    $datetime = date( "Y-m-d", $orgdatetime);
                else
                    $datetime = date( "Y-m-d H:i", $orgdatetime);

                $summary = $icalSettings[0]->base_summary;
                $description = $icalSettings[0]->base_description;
                foreach ($data as $item => $value)
                {
                    $summary = str_replace('<%'.$item.'%>', (is_array($value) ? implode(', ', $value) : $value), $summary);
                    $description = str_replace('<%'.$item.'%>', (is_array($value) ? implode(', ', $value) : $value), $description);
                }

                $submissiontime = time();
                if ($icalSettings[0]->observe_day_light && strlen($datetime) > 10)
                {
                    $full_date = gmdate("Ymd",strtotime($datetime.$icalSettings[0]->cal_time_zone_modify));
                    $year = substr($full_date,0,4);
                    if (strtoupper($icalSettings[0]->ical_daylight_zone) == 'EUROPE')
                    {
                        $dst_start = strtotime('last Sunday GMT', strtotime("1 April $year GMT"));
                        $dst_stop = strtotime('last Sunday GMT', strtotime("1 November $year GMT"));
                    } else { // USA
                        $dst_start = strtotime('first Sunday GMT', strtotime("1 April $year GMT"));
                        $dst_stop = strtotime('last Sunday GMT', strtotime("1 November $year GMT"));
                    }
                    if ($full_date >= gmdate("Ymd",$dst_start) && $full_date < gmdate("Ymd",$dst_stop))
                        $datetime = date("Y-m-d H:i",strtotime($datetime." -1 hour"));
                }

                $buffer .= "BEGIN:VEVENT\n";
                if (strlen($datetime) > 10)
                {
                    $buffer .= "DTSTART:".gmdate("Ymd",strtotime($datetime.$icalSettings[0]->cal_time_zone_modify))."T".gmdate("His",strtotime($datetime.$icalSettings[0]->cal_time_zone_modify))."Z\n";
                    $buffer .= "DTEND:".gmdate("Ymd",strtotime($datetime.$icalSettings[0]->cal_time_zone_modify))."T".gmdate("His",strtotime($datetime.$icalSettings[0]->cal_time_zone_modify))."Z\n";
                }
                else
                {
                    $buffer .= "DTSTART;VALUE=DATE:".gmdate("Ymd",strtotime($datetime.$icalSettings[0]->cal_time_zone_modify))."\n";
                    $buffer .= "DTEND;VALUE=DATE:".gmdate("Ymd",strtotime($datetime.$icalSettings[0]->cal_time_zone_modify." +1 day"))."\n";
                }
                $buffer .= "DTSTAMP:".gmdate("Ymd",$submissiontime)."T".gmdate("His",$submissiontime)."Z\n";
                $buffer .= "UID:uid".$submission_id.'_'.$ct."@".$_SERVER["SERVER_NAME"]."\n";
                $buffer .= "DESCRIPTION:".str_replace("<br>",'\n',str_replace("<br />",'\n',str_replace("\r",'',str_replace("\n",'\n',$description)) ))."\n";
                $buffer .= "LAST-MODIFIED:".gmdate("Ymd",$submissiontime)."T".gmdate("His",$submissiontime)."Z\n";
                $buffer .= "LOCATION:\n";
                $buffer .= "SEQUENCE:0\n";
                $buffer .= "STATUS:CONFIRMED\n";
                $buffer .= "SUMMARY:".str_replace("\n",'\n',$summary)."\n";
                $buffer .= "TRANSP:OPAQUE\n";
                $buffer .= "END:VEVENT\n";

                $buffer .= 'END:VCALENDAR';

                $filename1 = sanitize_file_name('Date'.'_'.$submission_id.'_admin.ics');
                $filename1 = WP_CONTENT_DIR . '/uploads/'.$filename1;
                $handle = fopen($filename1, 'w');
                fwrite($handle,$buffer);
                fclose($handle);
                $attachments[] = $filename1;

                $this->generated_file = $filename1;
            }

            return $attachments;
        } // End attach_ical_file


		/**
		 *	Delete the form from the addon's table
		 */
        public function delete_form( $formid)
		{
			global $wpdb;
			$wpdb->delete( $wpdb->prefix.$this->form_table, array('formid' => $formid), '%d' );
		} // delete_form

		/**
		 *	Clone the form's row
		 */
		public function clone_form( $original_form_id, $new_form_id )
		{
			global $wpdb;

			$form_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $original_form_id ), ARRAY_A);

			if(!empty($form_row))
			{
				unset($form_row["id"]);
				$form_row["formid"] = $new_form_id;
				$wpdb->insert( $wpdb->prefix.$this->form_table, $form_row);
			}
		} // End clone_form

		/**
		 *	It is called when the form is exported to export the addons data too.
		 *  Receive an array with the other addons data, and the form's id for filtering.
		 */
		public function export_form($addons_array, $formid)
		{
			global $wpdb;
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $formid ), ARRAY_A );
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
					$wpdb->prefix.$this->form_table,
					$addons_array[$this->addonID]
				);
			}
		} // End import_form

    } // End Class

    // Main add-on code
    $cpcff_icalfieldattach_obj = new CPCFF_iCalFieldAttach();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cpcff_icalfieldattach_obj);
}
?>