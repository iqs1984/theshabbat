<?php
/*
....
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPCFF_DropBox' ) )
{
    class CPCFF_DropBox extends CPCFF_BaseAddon
    {
		static public $category = 'External Services';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-dropbox-20161228";
		protected $name = "CFF - DropBox Integration";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#dropbox-addon';

		public function get_addon_settings()
		{
			if( isset( $_REQUEST[ 'cpcff_dropbox' ] ) )
			{
				check_admin_referer( $this->addonID, '_cpcff_nonce' );

				$this->remove_local  = ( isset( $_REQUEST['CP_CFF_DROPBOX_REMOVE_LOCAL'] ) ) ? true : false;
				update_option( 'CP_CFF_DROPBOX_REMOVE_LOCAL',  $this->remove_local );

				$app_key = sanitize_text_field( wp_unslash( $_REQUEST['CP_CFF_DROPBOX_APP_KEY'] ) );
				$app_secret = sanitize_text_field( wp_unslash( $_REQUEST['CP_CFF_DROPBOX_APP_SECRET'] ) );

				if ( $app_key != $this->app_key || $app_secret != $this->app_secret ) {
					$this->app_key = $app_key;
					$this->app_secret = $app_secret;
					update_option( 'CP_CFF_DROPBOX_APP_KEY',  $this->app_key );
					update_option( 'CP_CFF_DROPBOX_APP_SECRET',  $this->app_secret );

					if ( ! empty( $this->app_key ) && ! empty( $this->app_secret ) ) {
						?>
						<script>
							document.location.href= '<?php print 'https://www.dropbox.com/oauth2/authorize?client_id=' . urlencode( $this->app_key ) .'&token_access_type=offline&response_type=code&redirect_uri=' . urlencode( $this->redirect_url ); ?>';
						</script>
						<?php
						exit;
					}

				}
			}

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<form method="post" action="<?php print esc_url(admin_url('admin.php?page=cp_calculated_fields_form')); ?>">
				<div id="metabox_dropbox_addon_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_dropbox_addon_settings' ) ); ?>" >
					<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
					<div class="inside">
						<p>
							<?php esc_html_e( 'Redirect URI', 'calculated-fields-form' ); ?>: <span style="color:red"><?php print esc_html( $this->redirect_url ); ?></span>
						</p>
						<p>
							<?php _e('Enter the App Key', 'calculated-fields-form');?>:<br>
							<input name="CP_CFF_DROPBOX_APP_KEY" type="text" style="width:100%;" value="<?php print esc_attr( $this->app_key ); ?>" />
						</p>
						<p>
							<?php _e('Enter the App Secret', 'calculated-fields-form');?>:<br>
							<input name="CP_CFF_DROPBOX_APP_SECRET" type="text" style="width:100%;" value="<?php print esc_attr( $this->app_secret ); ?>" />
						</p>

						<p>
							To get the DropBox App Key and Secret, please, <a href="https://www.dropbox.com/developers/apps/create" target="_blank">CLICK HERE</a> <a href="javascript:void(0);" onclick="jQuery('.CP_CFF_DROPBOX_HELP').show();">[?]</a>
						</p>
						<div class="CP_CFF_DROPBOX_HELP" style="border:1px solid #F0AD4E;background:#FBE6CA;padding:10px;display:none;">
							<div style="text-align:right;"><a href="javascript:void(0);" onclick="jQuery('.CP_CFF_DROPBOX_HELP').hide();">[x]</a></div>
							<div>
								<p>Access your account, to the reserved area where configure an App: <a href="https://www.dropbox.com/developers/apps/create" target="_blank">https://www.dropbox.com/developers/apps/create</a></p>
								<p>
									<img src="<?php print esc_attr(plugins_url('/dropbox.addon/assets/step1.png', __FILE__)); ?>" style="width:50%;" />
								</p>
								<p>
									<ol>
										<li>Select the "Dropbox API" option.</li>
										<li>Select the "App folder" option.</li>
										<li>Enter the application name.</li>
										<li>Press the "Create app" button.</li>
									</ol>
								</p>
								<p>In the next screen enter the <b>Redirect URI</b></p>
								<p style="color:red"><?php print esc_html( $this->redirect_url ); ?></p>
								<p>And copy the App Key and Secret and enter them in the add-on attributes.</p>
								<p>
									<img src="<?php print esc_attr(plugins_url('/dropbox.addon/assets/step3.png', __FILE__)); ?>" style="width:50%;" />
								</p>
								<p>Finally, select the required persmissions from the permissions tab.</p>
								<p>
									<img src="<?php print esc_attr(plugins_url('/dropbox.addon/assets/step2.png', __FILE__)); ?>" style="width:50%;" />
								</p>
							</div>
						</div>
						<p>
							<input type="checkbox" name="CP_CFF_DROPBOX_REMOVE_LOCAL" <?php if( $this->remove_local ) print 'CHECKED'; ?> />&nbsp; <?php _e( 'Delete the local copy of file', 'calculated-fields-form' ); ?>
						</p>
						<p><input type="submit" value="<?php _e('Save settings', 'calculated-fields-form'); ?>" class="button-secondary" /></p>
					</div>
					<input type="hidden" name="cpcff_dropbox" value="1" />
					<input type="hidden" name="_cpcff_nonce" value="<?php echo wp_create_nonce( $this->addonID ); ?>" />
				</div>
			</form>
			<?php
		}

		/************************ ADDON CODE *****************************/
        /************************ ATTRIBUTES *****************************/

		private $_prefix 	        = 'cpcff-dropbox-add-on-form-';
		private $redirect_url      = '';
        private $dropbox_token 		= '';
        private $refresh_token 		= '';
        private $app_key 			= '';
        private $app_secret 		= '';
		private $remove_local 		= false;
		private $expires_in			= 0;

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = __("The add-on allows to copy/move the uploaded files to DropBox", 'calculated-fields-form');

            // Check if the plugin is active
			if ( !$this->addon_is_active() ) return;

			$this->remove_local  = get_option('CP_CFF_DROPBOX_REMOVE_LOCAL', false);
			$this->dropbox_token = get_option('CP_CFF_DROPBOX_ACCESS_TOKEN', '');
			$this->refresh_token = get_option('CP_CFF_DROPBOX_REFRESH_TOKEN', '');
			$this->app_key       = get_option('CP_CFF_DROPBOX_APP_KEY', '');
			$this->app_secret    = get_option('CP_CFF_DROPBOX_APP_SECRET', '');
			$this->redirect_url  = CPCFF_AUXILIARY::wp_url() . '/admin.php?page=cp_calculated_fields_form&cff-dropbox-action=token';
			$this->expires_in    = get_option('CP_CFF_DROPBOX_TOKEN_EXPIRES_IN', 0);

			add_action( 'cpcff_file_uploaded', array( &$this, 'uploaded_file' ), 9, 2 );
			add_action( 'init', array( $this, 'generate_tokens' ) );

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
		/************************ PRIVATE METHODS *****************************/
		private function _get_access_token($grant_code = '')
        {
            // if $grant_code is different from empty text it is generating a refresh token-time
            if ( ! empty( $this->app_key ) && ! empty( $this->app_secret ) ) {
				if ( !empty($grant_code ) ) // Get refresh token
                {
                    $args = [
                        'headers' => [
                            'Authorization' => 'Basic '.base64_encode($this->app_key.':'.$this->app_secret),
                            'Content-Typ'   => 'application/x-www-form-urlencoded'
                        ],
                        'body' => [
                            'grant_type'    => 'authorization_code',
                            'code'          => $grant_code,
                            'redirect_uri'  => $this->redirect_url
                        ]
                    ];
                } elseif ( '' != $this->refresh_token ) { // Get access token fro refresh one
                    $args = [
                        'headers' => [
                            'Authorization' => 'Basic '.base64_encode($this->app_key.':'.$this->app_secret),
                            'Content-Typ'   => 'application/x-www-form-urlencoded'
                        ],
                        'body' => [
                            'grant_type'    => 'refresh_token',
                            'refresh_token' => $this->refresh_token
                        ]
                    ];
                }

                if(!empty($args))
                {
                    $response = wp_remote_post(
                        'https://api.dropbox.com/oauth2/token',
                        $args
                    );

                    if(is_wp_error($response))
                    {
                        $this->error_message = $response->get_error_message();
                    }
                    else
                    {
                        $body = wp_remote_retrieve_body($response);
                        $json = json_decode($body);

                        if(!is_null($json))
                        {
							if(isset($json->refresh_token))
							{
								$this->refresh_token = sanitize_text_field( wp_unslash( $json->refresh_token ) );
								update_option( 'CP_CFF_DROPBOX_REFRESH_TOKEN', $this->refresh_token );
							}
							if(isset($json->access_token))
							{
								$this->dropbox_token = sanitize_text_field( wp_unslash( $json->access_token ) );
								update_option( 'CP_CFF_DROPBOX_ACCESS_TOKEN', $this->dropbox_token );
							}
							if(isset($json->expires_in))
							{
								$this->expires_in = time()+intval( sanitize_text_field( wp_unslash( $json->expires_in ) ) );
								update_option( 'CP_CFF_DROPBOX_TOKEN_EXPIRES_IN', $this->expires_in );
							}
						}
                        else
                        {
                            $this->error_message = __('Invalid response from Dropbox', 'calculated-fields-form');
                        }
                    }
                }
            }
        } // End _get_access_token

        public function generate_tokens()
        {
            if(
                is_admin() &&
				current_user_can( 'manage_options' ) &&
				! empty( $_REQUEST['cff-dropbox-action' ] ) &&
				'token' == sanitize_text_field( wp_unslash( $_REQUEST['cff-dropbox-action' ] ) ) &&
				! empty( $_REQUEST['code'] )
            ) {
				$this->_get_access_token(sanitize_text_field($_REQUEST['code']));
			}
        } // End generate_tokens

		public function check_expiration() {

			if ( $this->expires_in*1 <= time() ) {
				$this->_get_access_token();
			}
		} // End check_expiration

        private function _upload_file( $file )
		{
			if( !empty( $file ) && file_exists( $file ) )
			{

				$file_size = filesize( $file );
				$upload_dir = wp_upload_dir();
				$path = str_replace( '\\', '/', substr( $file, strlen( $upload_dir['basedir'] ) ) );

				$this->check_expiration();

				$response = wp_remote_post(
					'https://content.dropboxapi.com/2/files/upload',
					array(
						'headers' => array(
							'Authorization' => 'Bearer '.$this->dropbox_token,
							'Content-Type' => 'application/octet-stream',
							'Dropbox-API-Arg' => json_encode(
								array(
									"path"=> $path,
									"mode" => "add",
									"autorename" => true,
									"mute" => false
								)
							)
						),
						'sslverify'	=> false,
						'body' => @file_get_contents( $file )
					)
				);

				if( !is_wp_error( $response ) )
				{
					$json = json_decode(wp_remote_retrieve_body($response));
					if(
						!empty( $json ) &&
						!property_exists( $json, 'error')
					)
					{
						if( $json->size !== $file_size ) $this->_delete_file( $json->path_lower );
						else
						{
							$url = $this->_get_shared_link($json->path_lower);
							if($url !== false) $json->url = $url;
							else $json->url = $json->path_lower;
							return $json;
						}
					}
				}
				return false;
			}
		} // End _upload_file

		private function _get_shared_link($path)
		{
			$this->check_expiration();

			$url = "https://api.dropboxapi.com/2/sharing/create_shared_link_with_settings";
			$response = wp_remote_post(
				$url,
				array(
					'headers' => array(
						'Authorization' => 'Bearer '.$this->dropbox_token,
						'Content-Type' => 'application/json'
					),
					'sslverify'	=> false,
					'body' => json_encode(array("path"=> $path, "settings" => array("requested_visibility"=>"public")))
				)
			);

			if( !is_wp_error( $response ) )
			{
				$json = json_decode(wp_remote_retrieve_body($response));
				if(!empty( $json ))
				{
					if(!property_exists( $json, 'error')) return $json->url;
					$error = (array)$json->error;
					if($error['.tag'] == 'shared_link_already_exists')
					{
						$url = "https://api.dropboxapi.com/2/sharing/list_shared_links";
						$response = wp_remote_post(
							$url,
							array(
								'headers' => array(
									'Authorization' => 'Bearer '.$this->dropbox_token,
									'Content-Type' => 'application/json'
								),
								'sslverify'	=> false,
								'body' => json_encode(array("path"=> $path))
							)
						);
						if( !is_wp_error( $response ) )
						{
							$json = json_decode(wp_remote_retrieve_body($response));
							if(!empty( $json ))
							{
								return $json->links[0]->url;
							}
						}
					}
				}
			}

			return false;
		} // End _get_shared_link

		private function _delete_file( $path )
		{
			$this->check_expiration();

			$delete_url = "https://api.dropboxapi.com/2/files/delete";
			$response = wp_remote_post(
				$delete_url,
				array(
					'headers' => array(
						'Authorization' => 'Bearer '.$this->dropbox_token,
						'Content-Type' => 'application/json'
					),
					'sslverify'	=> false,
					'body' => json_encode(array("path"=> $path))
				)
			);
		} // End _delete_file

	 	/************************ PUBLIC METHODS  *****************************/
        public function get_addon_form_settings( $form_id )
		{
			// Insertion in database
			if( isset( $_REQUEST[ 'cpcff_dropbox' ] ) )
			{
                update_option($this->_prefix.$form_id, isset($_REQUEST['cpcff_dropbox_enabled']) ? 1 : 0);
			}

			$cpcff_main = CPCFF_MAIN::instance();
            ?>
			<div id="metabox_dropbox_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_dropbox_addon_form_settings' ) ); ?>" >
				<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
				<div class="inside">
                    <input type="checkbox" name="cpcff_dropbox_enabled" <?php
                        if(get_option($this->_prefix.$form_id, -1) != 0) print 'CHECKED';
                    ?> />
                    <?php
                    _e('Enable the integration between the "CFF - DropBox" add-on and the current form', 'calculated-fields-form');
                    ?>
				</div>
				<input type="hidden" name="cpcff_dropbox" value="1" />
			</div>
			<?php
		} // End get_addon_form_settings

		public function uploaded_file( $fileData, $filesParams )
		{
            // 0 disabled, 1 or -1 enabled
            if(!empty($filesParams['formid']) && get_option($this->_prefix.$filesParams['formid'], -1) == 0) return;

			$response = $this->_upload_file( $fileData[ 'file' ] );
			if(
				$response !== false &&
				$this->remove_local
			)
			{
				$pos = count( $filesParams[ 'names' ] ) - 1;
				$filesParams[ 'links'][ $pos ] = str_replace('?dl=0', '?dl=1', $response->url);
				$filesParams[ 'urls'][ $pos ]  = str_replace('?dl=0', '?dl=1', $response->url);
				wp_delete_file( $fileData[ 'file' ] );
				remove_all_actions( 'cpcff_file_uploaded' );
				add_action( 'cpcff_file_uploaded', array( &$this, 'uploaded_file' ), 9, 2 );
			}
		} // End uploaded_file

        /**
		 *	Delete the form from the addon's table
		 */
        public function delete_form( $formid)
		{
            delete_option($this->_prefix.$formid);
		} // delete_form

		/**
		 *	Clone the form's row
		 */
		public function clone_form( $original_form_id, $new_form_id )
		{
			update_option(
                $this->_prefix.$new_form_id,
                get_option($this->_prefix.$original_form_id, -1)
            );
		} // End clone_form

		/**
		 *	It is called when the form is exported to export the addons data too.
		 *  Receive an array with the other addons data, and the form's id for filtering.
		 */
		public function export_form($addons_array, $formid)
		{
			$addons_array[ $this->addonID ] = get_option($this->_prefix.$formid, -1);
			return $addons_array;
		} // End export_form

		/**
		 *	It is called when the form is imported to import the addons data too.
		 *  Receive an array with all the addons data, and the new form's id.
		 */
		public function import_form($addons_array, $formid)
		{
			if(isset($addons_array[$this->addonID]))
                update_option($this->_prefix.$formid, @intval($addons_array[$this->addonID]));
		} // End import_form

	} // End Class

    // Main add-on code
    $cpcff_google_places_obj = new CPCFF_DropBox();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cpcff_google_places_obj);
}