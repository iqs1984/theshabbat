<?php
add_action( 'admin_init', 'cpcff_active_auto_update', 1 );
if( !function_exists( 'cpcff_active_auto_update' ) )
{
	function cpcff_active_auto_update()
	{
		$plugin_data 		= get_plugin_data( CP_CALCULATEDFIELDSF_MAIN_FILE_PATH );
		$plugin_version 	= $plugin_data[ 'Version' ];
		$plugin_slug 		= CP_CALCULATEDFIELDSF_BASE_NAME;
		$plugin_remote_path = 'https://wordpress.dwbooster.com/updates/update.php';
		$admin_action		= 'cpcff_register_user';
		new CPCFF_AutoUpdateClss( $plugin_version, $plugin_remote_path, $plugin_slug, $admin_action );
	}
}

//-------------------Auto-Update-Class-----------------
if( !class_exists( 'CPCFF_AutoUpdateClss' ) )
{
	class CPCFF_AutoUpdateClss
	{
		private $error = '';
		/**
		 * The plugin current version
		 * @var string
		 */
		public $current_version;

		/**
		 * The plugin remote update path
		 * @var string
		 */
		public $update_path;

		/**
		 * Plugin Slug (plugin_directory/plugin_file.php)
		 * @var string
		 */
		public $plugin_slug;

		/**
		 * Plugin name (plugin_file)
		 * @var string
		 */
		public $slug;

		/**
		 * Registered buyer
		 * @var string
		 */
		public $registered_buyer;

		/**
		 * Initialize a new instance of the WordPress Auto-Update class
		 * @param string $current_version
		 * @param string $update_path
		 * @param string $plugin_slug
		 * @param string $admin_action
		 */
		function __construct( $current_version, $update_path, $plugin_slug, $admin_action )
		{
			// Set the class public variables
			$this->current_version = $current_version;
			$this->update_path = $update_path;
			$this->plugin_slug = $plugin_slug;
			list( $t1, $t2 ) = explode( '/', $plugin_slug );
			$this->slug = str_replace( '.php', '', $t2 );

			// define the alternative API for updating checking
			add_filter( 'pre_set_site_transient_update_plugins', array( &$this, 'check_update' ) );

			// Define the alternative response for information checking
			add_filter( 'plugins_api', array( &$this, 'check_info' ), 10, 3 );

			// Allows to use external resources host
			add_filter( 'http_request_host_is_external', array( &$this, 'allow_external_host' ), 10, 3 );

			// Adds an action to display a form to register the plugin
			add_action( $admin_action, array( &$this, 'register_plugin' ) );

			// Get the registered buyer
			$this->registered_buyer = trim( get_option( $this->slug.'buyer_email', '' ) );
		}

		/**
		 * Allows register the plugin with the email use for selling it
		 */
		public function register_plugin()
		{
			$field = $this->slug.'buyer_email';
			$message = '';
			if( isset( $_REQUEST[ $field ] ) || (!empty($this->registered_buyer) && !get_transient('cff_valid_license')))
			{
				$this->registered_buyer = sanitize_email( isset($_REQUEST[ $field ]) ?  $_REQUEST[ $field ] : $this->registered_buyer);

				update_option( $field,  $this->registered_buyer );
				$arg = new stdClass();
				$arg->slug = $this->slug;
                if(empty($this->registered_buyer))
                {
                    $message = '<br /><span style="font-weight:bold;color:#FF0000;" class="cp-blink-me">'.__('The buyer email address is required to register the plugin.', 'calculated-fields-form').'</span>';
                }
				elseif($this->check_info( false, 'plugin_information', $arg ) === false)
				{
					if( empty( $this->error ) ) {
						delete_transient('cff_valid_license');
						$message = '<br /><span style="font-weight:bold;color:#FF0000;">'.__('The email address is not associated to the plugin. You must enter the same email address used to purchase the plugin.', 'calculated-fields-form').'<br><span class="cp-blink-me">'.__('You must wait a minute before trying again.', 'calculated-fields-form').'</span></span>';
					} else {
						$message = '<br /><span style="font-weight:bold;color:#FF0000;">'.__('An error occurred in the plugin registration process:', 'calculated-fields-form').' '.$this->error.'</span>';
					}
				}
				else
				{
                    set_transient('cff_valid_license', 1, 2*7*60*60);
					$message = '<br /><span style="font-weight:bold;color:#46b450;">'.__('Valid email', 'calculated-fields-form').'</span>';
				}
			}
            elseif(!empty($this->registered_buyer) && get_transient('cff_valid_license'))
            {
                $message = '<br /><span style="font-weight:bold;color:#46b450;">'.__('Valid email', 'calculated-fields-form').'</span>';
            }
            elseif(!empty($this->registered_buyer))
            {
                delete_transient('cff_valid_license');
                $message = '<br /><span style="font-weight:bold;color:#FF0000;">'.__('The email address is not associated to the plugin. You must enter the same email address used to purchase the plugin.', 'calculated-fields-form').'</span>';
            }
            else
            {
                delete_transient('cff_valid_license');
                $message = '<br /><span style="font-weight:bold;color:#FF0000;" class="cp-blink-me">'.__('The buyer email address is required to register the plugin.', 'calculated-fields-form').'</span>';
            }
			print $message.'<br /><input aria-label="Email address" type="text" id="'.$field.'" name="'.$field.'" value="'.esc_attr( $this->registered_buyer ).'" class="width50" />';
		}

        static public function valid(){return !is_admin() || get_transient('cff_valid_license');}

        static public function message($reminder = false){
            return $reminder
            ? __('Remember to register your copy of the plugin from the plugin settings page.', 'calculated-fields-form')
            : __('To activate the advanced features, register your copy of the plugin.', 'calculated-fields-form');
        }

		/**
		 * Add our self-hosted autoupdate plugin to the filter transient
		 *
		 * @param $transient
		 * @return object $ transient
		 */
		public function check_update( $transient )
		{
			if( empty( $transient->checked ) )
			{
				return $transient;
			}

			// Get the remote version
			$remote_version = $this->getRemote_version();

			// If a newer version is available, add the update
			if( version_compare( $this->current_version, $remote_version, '<' ) )
			{
				$obj = new stdClass();
				$obj->slug = $this->slug;
				$obj->new_version = $remote_version;
				$obj->url = $this->update_path.'?user='.$this->registered_buyer.'&slug='.$this->slug;
				$obj->package = $obj->url;
				$transient->response[ $this->plugin_slug ] = $obj;
				if(class_exists('CPCFF_INSTALLER')) CPCFF_INSTALLER::uninstall();
			}
			return $transient;
		}

		/**
		 * Add our self-hosted description to the filter
		 *
		 * @param boolean $false
		 * @param array $action
		 * @param object $arg
		 * @return bool|object
		 */
		public function check_info( $false, $action, $arg )
		{
			if( array_key_exists( 'slug' , (array) $arg ) && array_key_exists( 'slug' , (array) $this ) && $arg->slug === $this->slug )
			{
				$information = $this->getRemote_information();
				return $information;
			}
			return $false;
		}

		function allow_external_host( $allow, $host, $url )
		{
			$allow = true;
			return $allow;
		}

		/**
		 * Return the remote version
		 * @return string $remote_version
		 */
		public function getRemote_version()
		{
			if( !empty( $this->registered_buyer ) )
			{
				$request = wp_remote_post( $this->update_path, array( 'body' => array( 'action' => 'version', 'user' => $this->registered_buyer, 'slug' => $this->slug ) ) );
				if( !is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200 )
				{
					return $request[ 'body' ];
				}
			}
			return false;
		}

		/**
		 * Get information about the remote version
		 * @return bool|object
		 */
		public function getRemote_information()
		{
			if( !empty( $this->registered_buyer ) )
			{
				$args = array( 'body' => array( 'action' => 'info', 'user' => $this->registered_buyer, 'slug' => $this->slug ) );
				$response = wp_remote_post( $this->update_path, $args );
				if(is_wp_error( $response ))
				{
					// try again but with the sslverify set in false
					$args['sslverify'] = false;
					$response = wp_remote_post( $this->update_path, $args );
				}
				if( !is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) === 200 )
				{
					if ( 'false' !== $response['body'] ) {
						$info = unserialize( $response['body'] );
						if($info !== false) return $info;
						$this->error = 'Invalid serialized object';
					}
				}
				else
				{
					$this->error = $response->get_error_message();
				}
			}
			return false;
		}

		/**
		 * Return the status of the plugin licensing
		 * @return boolean $remote_license
		 */
		public function getRemote_license()
		{
			if( !empty( $this->registered_buyer ) )
			{
				$request = wp_remote_post( $this->update_path, array( 'body' => array( 'action' => 'license', 'user' => $this->registered_buyer, 'slug' => $this->slug ) ) );

				if( !is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200 )
				{
					return $request['body'];
				}
			}
			return false;
		}
	} // End CPCFF_AutoUpdateClss Class
}