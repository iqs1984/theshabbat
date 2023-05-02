<?php
/*
....
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPCFF_PDF' ) )
{
    class CPCFF_PDF extends CPCFF_BaseAddon
    {
		static public $category = 'Extending Features';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-pdf-20200213";
		protected $name = "CFF - PDF Generator (Experimental)";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#pdf-generator-addon';

		public function get_addon_form_settings( $form_id )
		{
			global $wpdb;

			// Enqueue the required scripts
			wp_enqueue_code_editor( array( 'type' => 'text/html' ) );
			wp_enqueue_script( 'cpcff_p_js', plugins_url('/pdf.addon/code-editor.js', __FILE__), array( 'jquery' ), $this->addonID, true );

			$table = $wpdb->prefix.$this->form_pdf_table;
			// Insertion in database
			if( isset( $_REQUEST[ 'cpcff_pdf' ] ) )
			{
				$this->add_field_verify($table, "protect_file");
				$this->add_field_verify($table, "password");
				$this->add_field_verify($table, "settings", "text");

				$wpdb->delete( $table, array( 'formid' => $form_id ), array( '%d' ) );

				$active = (isset($_REQUEST['cpcff_pdf_active'])) ? 1 : 0;
				$data 	= (isset($_REQUEST['cpcff_pdf_data'])) ? trim($_REQUEST['cpcff_pdf_data']) : '';
				$title 	= (isset($_REQUEST['cpcff_pdf_title'])) ? trim($_REQUEST['cpcff_pdf_title']) : '';
				$valid_options = array('notification-email', 'confirmation-email', 'both-emails', 'none');
				$email 	= (
							isset($_REQUEST['cpcff_pdf_email']) &&
							in_array($_REQUEST['cpcff_pdf_email'], $valid_options)
						)
						? $_REQUEST['cpcff_pdf_email']
						: 'both-emails';
				$protect_file = (isset($_REQUEST['cpcff_pdf_protect_file'])) ? : '';
				$password 	  = sanitize_text_field($_REQUEST['cpcff_pdf_password']);
                $settings     = [
                    'layout' => $_REQUEST['cpcff_pdf_layout'] == 'landscape' ? 'landscape' : 'portrait',
                    'after_payment' => isset($_REQUEST['cpcff_pdf_after_payment_confirmation']) ? 1 : 0
                ];

				$this->_insert_row(
					$table,
					array(
						'formid' 		=> $form_id,
						'active'		=> $active,
						'title'			=> stripslashes($title),
						'data'			=> stripslashes($data),
						'email'			=> $email,
						'protect_file'  => $protect_file,
						'password'  	=> $password,
                        'settings'      => json_encode($settings)
					),
					array( '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s' )
				);
			}

			$row = $wpdb->get_row(
				$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_pdf_table." WHERE formid=%d", $form_id )
			);

			$active = false;
			$data 	= '';
			$title 	= '';
			$email  = 'both-emails';
			$protect_file = '';
			if(!is_null($row))
			{
				$active = $row->active;
				$data 	= $row->data;
				$title 	= $row->title;
				if(!empty($row->email)) $email = $row->email;
				if(!empty($row->protect_file)) $protect_file = $row->protect_file;
                if(empty($row->settings) || ($settings = json_decode($row->settings, true)) == null) $settings = [];
			}

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<div id="metabox_pdf_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_pdf_addon_form_settings' ) ); ?>" >
				<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
				<div class="inside">
					<table cellspacing="3px" class="form-table width100">
						<tr>
							<td>
								<input type="checkbox" name="cpcff_pdf_active" <?php if($active) print 'CHECKED'; ?> />
								<?php _e('Activate the PDF add-on', 'calculated-fields-form'); ?>
							</td>
						</tr>
						<tr>
							<td>
								<b><?php _e('Enter the name of PDF file', 'calculated-fields-form'); ?>:</b><br>
								<input type="text" name="cpcff_pdf_title" class="width75" value="<?php print esc_attr($title); ?>" />
							</td>
						</tr>
						<tr>
							<td>
								<input type="checkbox" name="cpcff_pdf_after_payment_confirmation" <?php if(!empty($settings['after_payment'])) print 'CHECKED'; ?> />
								<?php _e('Generate files after payment confirmation', 'calculated-fields-form'); ?><br>
								<i><?php _e('If the checkbox is ticked, and you want to attach the file to the emails, you should configure the form to send the emails after payment confirmation too.', 'calculated-fields-form'); ?></i>
							</td>
						</tr>
						<tr>
							<td>
								<b><?php _e('Send the PDF file to', 'calculated-fields-form'); ?>:</b><br>
								<input name="cpcff_pdf_email" type="radio" value="notification-email" <?php if($email == 'notification-email') print 'CHECKED'; ?> /> <?php _e('To the email addresses entered through the "Destination emails" attribute', 'calculated-fields-form'); ?><br>
								<input name="cpcff_pdf_email" type="radio" value="confirmation-email" <?php if($email == 'confirmation-email') print 'CHECKED'; ?> /> <?php _e('To the email addresses entered by the user', 'calculated-fields-form'); ?><br>
								<input name="cpcff_pdf_email" type="radio" value="both-emails" <?php if($email == 'both-emails') print 'CHECKED'; ?> /> <?php _e('Both emails', 'calculated-fields-form'); ?><br>
								<input name="cpcff_pdf_email" type="radio" value="none" <?php if($email == 'none') print 'CHECKED'; ?> /> <?php _e('None', 'calculated-fields-form'); ?>
							</td>
						</tr>
						<tr>
							<td>
								<b><?php _e('Enter the HTML code to generate the PDF file', 'calculated-fields-form'); ?>:</b><br>
								<textarea name="cpcff_pdf_data" style="width:100%;" rows="20"><?php print esc_textarea($data); ?></textarea>
								<p><?php _e('It is possible to use the same special tags that in notification emails and thank you pages', 'calculated-fields-form'); ?>: <a href="https://cff.dwbooster.com/documentation#special-tags" target="_blank"><?php _e('READ MORE', 'calculated-fields-form'); ?></a></p>
							</td>
						</tr>
                        <tr>
							<td>
                                <?php _e('Layout', 'calculated-fields-form'); ?>&nbsp;
								<select name="cpcff_pdf_layout">
                                    <option value="portrait" <?php if(empty($settings) || $settings['layout'] == 'portrait') print 'SELECTED'; ?>><?php print esc_html(__('Portrait', 'calculated-fields-form')); ?></option>
                                    <option value="landscape" <?php if(!empty($settings) && $settings['layout'] == 'landscape') print 'SELECTED'; ?>><?php print esc_html(__('Landscape', 'calculated-fields-form')); ?></option>
                                </select>
							</td>
						</tr>
						<tr>
							<td>
								<input type="checkbox" name="cpcff_pdf_protect_file" <?php if($protect_file) print 'CHECKED'; ?> />
								<?php _e('Protect the file from being copied, modified, or printed', 'calculated-fields-form'); ?><br /><br />
								<b><?php _e('Protect the file with password', 'calculated-fields-form'); ?>:</b><br />
								<input type="text" class="width75" name="cpcff_pdf_password" value="<?php print esc_attr(isset($password) ? $password : ''); ?>" />
							</td>
						</tr>

					</table>
                    <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
				</div>
				<input type="hidden" name="cpcff_pdf" value="1" />
			</div>
			<?php
		}

		/************************ ADDON CODE *****************************/
        /************************ ATTRIBUTES *****************************/

		private $form_pdf_table = 'cp_calculated_fields_form_pdf';
		private $submission_id;
		private $pdf_file_name 	= '';

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = __("The add-on generates a PDF file with the information collected by the form, and attach it to the notification and confirmation emails", 'calculated-fields-form');

            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;

            // Generate file
            add_action( 'cpcff_process_data', array( &$this, 'cpcff_process_data_action' ) );
			add_action( 'cpcff_payment_processed', array( &$this, 'cpcff_payment_processed_action' ), 1 );
			add_action( 'cpcff_change_payment_status', array( &$this, 'cpcff_change_payment_status' ), 10, 2 );

            // Attach file
			add_filter( 'cpcff_notification_email_attachments', array( &$this, 'notification_email' ), 1, 4 );
			add_filter( 'cpcff_confirmation_email_attachments', array( &$this, 'confirmation_email' ), 1, 4 );
			add_filter( 'cpcff_custom_tags', array( &$this, 'replace_tags' ), 1, 2);

			if( is_admin() )
			{
				// Include the URL to the PDF file in the messages list
                add_action( 'cpcff_message_additional_details', array(&$this, 'add_link_to_messages_list') );

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
			$db_queries = array( "CREATE TABLE ".$wpdb->prefix.$this->form_pdf_table." (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					formid INT NOT NULL,
					active TINYINT DEFAULT 0 NOT NULL,
					title text DEFAULT '' NOT NULL,
					data LONGTEXT DEFAULT '' NOT NULL,
					email VARCHAR(50) DEFAULT 'both-emails' NOT NULL,
					UNIQUE KEY id (id)
				) $charset_collate;"
			);
			$this->_run_update_database($db_queries);
		} // End update_database

        /************************ PRIVATE METHODS *****************************/

		private function _insert_row($table, $columns, $formats)
		{
			global $wpdb;
			if(!$wpdb->insert($table, $columns, $formats))
			{
				$this->update_database();
				$wpdb->insert($table, $columns, $formats);
			}
		} // End _insert_row

		private function _get_email($form_id)
		{
			global $wpdb;
			$email = 'both-emails';
			$row = $wpdb->get_row(
				$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_pdf_table." WHERE formid=%d", $form_id )
			);

		    if(is_null($row) || !$row->active) return false;
			if(!empty($row->email)) $email = $row->email;

			return $email;
		} // End _get_email

		/************************ PUBLIC METHODS  *****************************/

		/**
		 * Attach the generated PDF file to the notification email
		 */
        public function	notification_email( $files, $params, $form_id, $submission_id )
		{
			$email = $this->_get_email($form_id);
			if($email !== false)
			{
				if($email == 'notification-email' || $email == 'both-emails') add_action('phpmailer_init', array(&$this, 'phpmailer_init'));
                else remove_action( 'phpmailer_init', array(&$this, 'phpmailer_init') );
			}
			return $files;
		} // End notification_email

        /**
		 * Attach the generated PDF file to the email copy to the user
		 */
        public function	confirmation_email( $files, $params, $form_id, $submission_id )
		{
			$email = $this->_get_email($form_id);
			if($email !== false)
			{
				if($email == 'confirmation-email' || $email == 'both-emails') add_action('phpmailer_init', array(&$this, 'phpmailer_init'));
                else remove_action( 'phpmailer_init', array(&$this, 'phpmailer_init') );
			}
			return $files;
		} // End confirmation_email

        /**
         * Process the cpcff_process_data action
         */
		public function cpcff_process_data_action( $params )
		{
			$this->_generate_file( $params, false );
		}

		/**
         * Process the cpcff_payment_processed action
         */
		public function cpcff_payment_processed_action( $params )
		{
			$this->_generate_file( $params, true );
		}

		/**
         * Process the cpcff_change_payment_status action
         */
		public function cpcff_change_payment_status( $submission_id, $payment_status )
		{
            if($payment_status*1)
            {
                if(($submission_obj = CPCFF_SUBMISSIONS::get($submission_id)) != false)
                {
                    $submission_obj->paypal_post['itemnumber'] = $submission_id;
                    $this->_generate_file($submission_obj->paypal_post, true);
                }
            }
        }

		/**
         * Generate the PDF file
         */
        private function _generate_file($params, $payment_processed)
		{
			global $wpdb;

            $form_id = @intval($params['formid']);
            $this->submission_id = @intval($params['itemnumber']);
            if($form_id)
            {
                $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.$this->form_pdf_table." WHERE formid=%d", $form_id));

                if(
                    !is_null($row) &&
                    $row->active &&
                    !empty($row->data)
                )
                {
                    if(empty($row->settings) || ($settings = json_decode($row->settings, true)) == null) $settings = [];
                    if(!empty($settings['after_payment']) && !$payment_processed) return;

                    $submission_obj = CPCFF_SUBMISSIONS::get($this->submission_id);
                    if($submission_obj)
                    {
                        $form_obj = CPCFF_SUBMISSIONS::get_form($this->submission_id);
                        $data = CPCFF_AUXILIARY::parsing_fields_on_text(
                            $form_obj->get_fields(),
                            $params,
                            $row->data,
                            $submission_obj->data,
                            'html',
                            $this->submission_id
                        );

                        if(!empty($row->title))
                        {
                            $file_title = CPCFF_AUXILIARY::parsing_fields_on_text(
                                $form_obj->get_fields(),
                                $params,
                                $row->title,
                                $submission_obj->data,
                                'text',
                                $this->submission_id
                            );
                            $this->pdf_file_name = $file_title['text'];
                        }

                        // Generating the PDF file
                        require_once dirname(__FILE__).'/pdf.addon/vendor/autoload.php';
                        try{

                            $options = new Dompdf\Options();
                            $options->set('isPhpEnabled', TRUE);
                            $options->set('isRemoteEnabled', TRUE);
                            $options->set('defaultFont', 'helvetica');
                            $dompdf = new Dompdf\Dompdf($options);
                            $dompdf->loadHtml($data['text'].'<style>.cpcff-signature{display:block;clear:both;}</style>');
                            $dompdf->setPaper(
                                'A4',
                                (!empty($settings['layout']) && $settings['layout'] == 'landscape') ? 'landscape' : 'portrait'
                            );
                            $dompdf->render();

                            if(!empty($row->protect_file))
                            {
                                $user_password = '';
                                $owner_password = '';
                                if(!empty($row->password))
                                {
                                    $user_password = $row->password;
                                    $owner_password = wp_generate_password();
                                }
                                $dompdf->get_canvas()->get_cpdf()->setEncryption($user_password, $owner_password);
                            }
                            $this->_export_file($dompdf->output());
                        } catch (Exception $err){error_log($err->getMessage());}
                    }
                }
            }
		} // End _generate_file

		private function _get_file_path($submission_id)
		{
			try
			{
				$file_name = md5($submission_id).'.pdf';
				$uploads = wp_upload_dir();
				$path = str_replace('\\', '/', $uploads['basedir']);
				$path = rtrim($path, '/').'/cff-pdf';
				if(!is_dir($path)) mkdir($path);
				return array(
					'path' 	=> $path.'/'.$file_name,
					'url'	=> $uploads['baseurl'].'/cff-pdf/'.$file_name
				);
			}
			catch(Exception $err){}
			return false;
		} // End _get_file_path

		private function _export_file($file_content)
		{
			try
			{
				$file_path = $this->_get_file_path($this->submission_id);
				if($file_path)
				{
					file_put_contents($file_path['path'], $file_content);
				}
			}
			catch(Exception $err){}
		} // End _export_file

		public function replace_tags($text, $submission_id)
		{
			$file_path = $this->_get_file_path($submission_id);
			if($file_path)
			{
				$text = str_replace('<%pdf_generator_url%>', $file_path['url'], $text);
			}
			else
			{
				$text = str_replace('<%pdf_generator_url%>', '', $text);
			}
			return $text;
		} // End replace_tags

		public function phpmailer_init(&$phpmailer)
		{
			if(!empty($this->submission_id))
			{
                $file_path = $this->_get_file_path($this->submission_id);
                if($file_path && file_exists($file_path['path']))
                {
                    $phpmailer->addStringAttachment(
                        file_get_contents($file_path['path']),
                        $this->pdf_file_name.(preg_match('/\.pdf$/i', $this->pdf_file_name) ? '' : '.pdf'),
                        'base64',
                        'application/pdf'
                    );
                }
			}
		} // End phpmailer_init

        public function add_link_to_messages_list($submission)
        {
            $file_path = $this->_get_file_path($submission->id);
			if($file_path && file_exists($file_path['path']))
			{
				print '<br>PDF: <a href="'.esc_attr($file_path['url']).'" target="_blank">'.$file_path['url'].'</a>';
			}
        } // End add_link_to_messages_list
		/**
		 *	Delete the form from the addon's table
		 */
        public function delete_form( $formid)
		{
			global $wpdb;
			$wpdb->delete( $wpdb->prefix.$this->form_pdf_table, array('formid' => $formid), '%d' );
		} // delete_form

		/**
		 *	Clone the form's row
		 */
		public function clone_form( $original_form_id, $new_form_id )
		{
			global $wpdb;

			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_pdf_table." WHERE formid=%d", $original_form_id ), ARRAY_A);

			if(!empty($rows))
			{
				foreach($rows as $row)
				{
					unset($row["id"]);
					$row["formid"] = $new_form_id;
					$wpdb->insert( $wpdb->prefix.$this->form_pdf_table, $row);
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
			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_pdf_table." WHERE formid=%d", $formid ), ARRAY_A );
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
							$wpdb->prefix.$this->form_pdf_table,
							$row
						);
					}
				}
			}
		} // End import_form

    } // End Class

    // Main add-on code
    $cpcff_pdf_obj = new CPCFF_PDF();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cpcff_pdf_obj);
}