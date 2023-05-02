<?php
/*
....
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPCFF_PrintFriendly' ) )
{
    class CPCFF_PrintFriendly extends CPCFF_BaseAddon
    {
		static public $category = 'External Services';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-printfriendly-20181021";
		protected $name = "CFF - PrintFriendly";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#printfriendly-addon';

		public function get_addon_settings()
		{
			if( isset( $_REQUEST[ 'cpcff_printfriendly_api_key' ] ) )
			{
				check_admin_referer( $this->addonID, '_cpcff_nonce' );
				update_option( 'cpcff_printfriendly_api_key', trim( $_REQUEST[ 'cpcff_printfriendly_api_key' ] ) );
			}

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<form method="post" action="<?php print esc_url(admin_url('admin.php?page=cp_calculated_fields_form')); ?>">
				<div id="metabox_printfriendly_addon_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_printfriendly_addon_settings' ) ); ?>" >
					<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
					<div class="inside">
						<table cellspacing="0" style="width:100%;">
							<tr>
								<td style="white-space:nowrap;width:200px;"><?php _e('Api Key', 'calculated-fields-form');?>:</td>
								<td>
									<input type="text" name="cpcff_printfriendly_api_key" value="<?php echo get_option( 'cpcff_printfriendly_api_key', '' ); ?>"  style="width:80%;" />
								</td>
							</tr>
						</table>
						<input type="submit" value="Save settings" class="button-secondary" />
					</div>
					<input type="hidden" name="_cpcff_nonce" value="<?php echo wp_create_nonce( $this->addonID ); ?>" />
				</div>
			</form>
			<?php
		} // End get_addon_settings

		public function get_addon_form_settings( $form_id )
		{
			global $wpdb;

			// Enqueue the required scripts
			wp_enqueue_code_editor( array( 'type' => 'text/html' ) );
			wp_enqueue_script( 'cpcff_p_js', plugins_url('/printfriendly.addon/code-editor.js', __FILE__), array( 'jquery' ), $this->addonID, true );

			$table = $wpdb->prefix.$this->form_printfriendly_table;
			// Insertion in database
			if( isset( $_REQUEST[ 'cpcff_friendly' ] ) )
			{
				$wpdb->delete( $table, array( 'formid' => $form_id ), array( '%d' ) );

				$active = (isset($_REQUEST['cpcff_apifriendly_active'])) ? 1 : 0;
				$css 	= (isset($_REQUEST['cpcff_apifriendly_css'])) ? trim($_REQUEST['cpcff_apifriendly_css']) : '';
				$title 	= (isset($_REQUEST['cpcff_apifriendly_title'])) ? trim($_REQUEST['cpcff_apifriendly_title']) : '';
				$data 	= (isset($_REQUEST['cpcff_apifriendly_data'])) ? trim($_REQUEST['cpcff_apifriendly_data']) : '';
				$valid_options = array('notification-email', 'confirmation-email', 'both-emails');
				$email 	= (
							isset($_REQUEST['cpcff_apifriendly_email']) &&
							in_array($_REQUEST['cpcff_apifriendly_email'], $valid_options)
						)
						? $_REQUEST['cpcff_apifriendly_email']
						: 'both-emails';

				$this->_insert_row(
					$table,
					array(
						'formid' 		=> $form_id,
						'active'		=> $active,
						'css'			=> stripslashes($css),
						'title'			=> stripslashes($title),
						'data'			=> stripslashes($data),
						'email'			=> $email
					),
					array( '%d', '%d', '%s', '%s', '%s', '%s' )
				);
			}

			$row = $wpdb->get_row(
				$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_printfriendly_table." WHERE formid=%d", $form_id )
			);

			$active = false;
			$data 	= '';
			$css  	= '';
			$title 	= '';
			$email  = 'both-emails';

			if(!is_null($row))
			{
				$active = $row->active;
				$data 	= $row->data;
				$css 	= $row->css;
				$title 	= $row->title;
				if(!empty($row->email)) $email = $row->email;
			}

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<div id="metabox_printfriendly_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_printfriendly_addon_form_settings' ) ); ?>" >
				<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
				<div class="inside">
					<?php
						// Check if the API Key has been entered.
						$api_key = get_option('cpcff_printfriendly_api_key', '');
						if($api_key == '')
						{
							print '<div class="notice notice-warning"><p>'.__('The PrintFriendly API Key should be entered from the settings page of plugin', 'calculated-fields-form').'</p></div>';
						}
					?>
					<table cellspacing="3px" style="width:100%;">
						<tr>
							<td>
								<input type="checkbox" name="cpcff_apifriendly_active" <?php if($active) print 'CHECKED'; ?> />
								<?php _e('Activate the PrintFriendly add-on', 'calculated-fields-form'); ?>
							</td>
						</tr>
						<tr>
							<td>
								<p><?php _e('Enter the header text of PDF file', 'calculated-fields-form'); ?>:</p>
								<input type="text" name="cpcff_apifriendly_title" style="width:100%;" value="<?php print esc_attr($title); ?>" />
							</td>
						</tr>
						<tr>
							<td>
								<p><?php _e('Send the PDF file to', 'calculated-fields-form'); ?>:</p>
								<input name="cpcff_apifriendly_email" type="radio" value="notification-email" <?php if($email == 'notification-email') print 'CHECKED'; ?> /> <?php _e('To the email addresses entered through the "Destination emails" attribute', 'calculated-fields-form'); ?><br>
								<input name="cpcff_apifriendly_email" type="radio" value="confirmation-email" <?php if($email == 'confirmation-email') print 'CHECKED'; ?> /> <?php _e('To the email addresses entered by the user', 'calculated-fields-form'); ?><br>
								<input name="cpcff_apifriendly_email" type="radio" value="both-emails" <?php if($email == 'both-emails') print 'CHECKED'; ?> /> <?php _e('Both emails', 'calculated-fields-form'); ?>
							</td>
						</tr>
						<tr>
							<td>
								<p><?php _e('Enter the HTML code to generate the PDF file', 'calculated-fields-form'); ?>:</p>
								<textarea name="cpcff_apifriendly_data" style="width:100%;" rows="20"><?php print esc_textarea($data); ?></textarea>
								<p><?php _e('It is possible to use the same special tags that in notification emails and thank you pages', 'calculated-fields-form'); ?>: <a href="https://cff.dwbooster.com/documentation#special-tags" target="_blank"><?php _e('READ MORE', 'calculated-fields-form'); ?></a></p>
							</td>
						</tr>
						<tr>
							<td>
								<p><?php _e('Enter the URL to the CSS file to define the design of PDF file', 'calculated-fields-form'); ?>:</p>
								<input type="text" name="cpcff_apifriendly_css" style="width:100%;" value="<?php print esc_attr($css); ?>" />
							</td>
						</tr>

					</table>
                    <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
				</div>
				<input type="hidden" name="cpcff_friendly" value="1" />
			</div>
			<?php
		}

		/************************ ADDON CODE *****************************/
        /************************ ATTRIBUTES *****************************/

		private $form_printfriendly_table = 'cp_calculated_fields_form_printfriendly';
		private $pdf_file_url;

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = __("The add-on generates a PDF file with the information collected by the form using the PrintFriendly API, and attach it to the notification and confirmation emails", 'calculated-fields-form');

            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;

			// Export the lead
			add_filter( 'cpcff_notification_email_attachments', array( &$this, 'notification_email' ), 1, 4 );
			add_filter( 'cpcff_confirmation_email_attachments', array( &$this, 'confirmation_email' ), 1, 4 );

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
			$db_queries = array( "CREATE TABLE ".$wpdb->prefix.$this->form_printfriendly_table." (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					formid INT NOT NULL,
					active TINYINT DEFAULT 0 NOT NULL,
					css text DEFAULT '' NOT NULL,
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
				$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_printfriendly_table." WHERE formid=%d", $form_id )
			);

			if(is_null($row) || !$row->active) return false;
			if(!empty($row->email)) $email = $row->email;

			return $email;
		} // End _get_email

		private function _get_html_file($postid, $html)
		{
			// Uploads directory
			$base = wp_get_upload_dir();
			if(!empty($base['basedir']))
			{
				$dir = $base['basedir'].'/cff-printfriendly';
				$url = $base['baseurl'].'/cff-printfriendly/cff-printfriendly-'.$postid.'.html';
				if(file_exists($dir) || @mkdir($dir))
				{
					$file_path = $dir.'/cff-printfriendly-'.$postid.'.html';
					if(file_exists($file_path) || @file_put_contents($file_path, '<html><body>'.$html.'</body></html>') !== false)
						return $url;
				}

			}
			return false;
		} // End _get_html_file

		private function _delete_html_file($postid)
		{
			// Uploads directory
			$base = wp_get_upload_dir();
			if(!empty($base['basedir']))
			{
				@unlink($base['basedir'].'/cff-printfriendly/cff-printfriendly-'.$postid.'.html');
			}
		} // End _delete_html_file

        /************************ PUBLIC METHODS  *****************************/

        /**
		 * Attach the generated PDF file to the notification email
		 */
        public function	notification_email( $files, $params, $form_id, $submission_id )
		{
			$email = $this->_get_email($form_id);
			if($email !== false)
			{
				if($email != 'confirmation-email') $this->generate_file( $files, $params, $form_id, $submission_id );
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
				if($email != 'notification-email') $this->generate_file( $files, $params, $form_id, $submission_id );
				else remove_action( 'phpmailer_init', array(&$this, 'phpmailer_init') );
			}
			return $files;
		} // End confirmation_email

        /**
         * Generate the PDF file
         */
        public function	generate_file( $files, $params, $form_id, $submission_id )
		{
			global $wpdb;
			if(!empty($this->pdf_file_url))
			{
				add_action( 'phpmailer_init', array(&$this, 'phpmailer_init') );
			}
			else
			{
				$api_key = get_option( 'cpcff_printfriendly_api_key', '' );
				if( !empty( $form_id ) && !empty($api_key) )
				{
					$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_printfriendly_table." WHERE formid=%d", $form_id ) );

					if(
						!is_null($row) &&
						$row->active &&
						!empty($row->data)
					)
					{
						$submission_obj = CPCFF_SUBMISSIONS::get($submission_id);
						if($submission_obj)
						{
							$form_obj = CPCFF_SUBMISSIONS::get_form($submission_id);
							$data = CPCFF_AUXILIARY::parsing_fields_on_text(
								$form_obj->get_fields(),
								$params,
								$row->data,
								$submission_obj->data,
								'html',
								$submission_id
							);

							$parameters = array(
								'api_key'		=> $api_key,
								'output_type' 	=> 'JSON'
							);

							$html_url = $this->_get_html_file($submission_id, $data['text']);
							if($html_url) $parameters['page_url'] = $html_url;
							else $parameters['html'] = $data['text'];

							if(!empty($row->css)){
								$parameters['css_url'] = $row->css;
								$parameters['pfCustomCSS'] = $row->css;
							}

							if(!empty($row->title))
							{
								$title = CPCFF_AUXILIARY::parsing_fields_on_text(
									$form_obj->get_fields(),
									$params,
									$row->title,
									$submission_obj->data,
									'html',
									$submission_id
								);
								$parameters['header_text'] = $title['text'];
							}

							$args = array(
								'headers' 	=> array('content-type' => 'application/json'),
								'body' 		=> $parameters,
								'timeout' 	=> 45,
								'sslverify'	=> false,
							);

							$response = wp_remote_get(
								'https://api.printfriendly.com/v2/pdf/create',
								$args
							);

							if ( is_wp_error( $response ) )
							{
								$error_message = $response->get_error_message();
								error_log("PrintFriendly error: $error_message");
							}
							else
							{
								$body = wp_remote_retrieve_body($response);
								$obj = json_decode($body);
								if(!empty($obj->error))
									error_log("PrintFriendly error: ".json_encode($obj->error));
								else
								{
									$this->pdf_file_url = $obj->file_url;
									add_action( 'phpmailer_init', array(&$this, 'phpmailer_init') );
								}
							}

							$this->_delete_html_file($submission_id);
						}
					}
				}
			}
			return $files;
		} // End generate_file

		public function phpmailer_init(&$phpmailer)
		{
			if(!empty($this->pdf_file_url))
			{
				$file_name = basename($this->pdf_file_url);
				$response  = wp_remote_get( $this->pdf_file_url, array('sslverify' => false) );
				if(!is_wp_error( $response ))
				{
					$file_content = wp_remote_retrieve_body( $response );
					$phpmailer->addStringAttachment($file_content, $file_name, 'base64', 'application/pdf');
				}
			}
		} // End phpmailer_init

		/**
		 *	Delete the form from the addon's table
		 */
        public function delete_form( $formid)
		{
			global $wpdb;
			$wpdb->delete( $wpdb->prefix.$this->form_printfriendly_table, array('formid' => $formid), '%d' );
		} // delete_form

		/**
		 *	Clone the form's row
		 */
		public function clone_form( $original_form_id, $new_form_id )
		{
			global $wpdb;

			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_printfriendly_table." WHERE formid=%d", $original_form_id ), ARRAY_A);

			if(!empty($rows))
			{
				foreach($rows as $row)
				{
					unset($row["id"]);
					$row["formid"] = $new_form_id;
					$wpdb->insert( $wpdb->prefix.$this->form_printfriendly_table, $row);
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
			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_printfriendly_table." WHERE formid=%d", $formid ), ARRAY_A );
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
							$wpdb->prefix.$this->form_printfriendly_table,
							$row
						);
					}
				}
			}
		} // End import_form

    } // End Class

    // Main add-on code
    $cpcff_printfriendly_obj = new CPCFF_PrintFriendly();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cpcff_printfriendly_obj);
}