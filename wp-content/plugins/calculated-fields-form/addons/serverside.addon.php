<?php
/*
....
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPCFF_ServerSideEquations' ) )
{
    class CPCFF_ServerSideEquations extends CPCFF_BaseAddon
    {
		static public $category = 'Extending Features';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-server-side-equations-20180313";
		protected $name = "CFF - Server Side Equations";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#server-side-equations-addon';

		public function get_addon_settings()
		{
			if( isset( $_REQUEST['CP_CFF_SERVER_SIDE_EQUATIONS'] ) )
			{
				check_admin_referer( $this->addonID, '_cpcff_nonce' );
				try
				{
                    update_option('CP_CFF_SERVER_SIDE_EQUATIONS_DELAY', isset($_REQUEST['CP_CFF_SERVER_SIDE_EQUATIONS_DELAY']) ? 'init' : '');
					$equations_code = $_REQUEST['CP_CFF_SERVER_SIDE_EQUATIONS'];
					$equations_code = trim($equations_code);
					$equations_code = stripslashes($equations_code);
                    $this->_get_file_path(true);
                    file_put_contents($this->equations_file, $equations_code);
					$message = __('Equations updated', 'calculated-fields-form');
					$class = 'success';
				}
				catch(Exception $err)
				{
					$message = $err->getMessage();
					$class = 'error';
				}
			}

			$this->enqueue_admin_resources();

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<a id="cp-cff-server-side-equations-section"></a>
			<form method="post" action="<?php print esc_url(admin_url('admin.php?page=cp_calculated_fields_form#cp-cff-server-side-equations-section')); ?>">
				<div id="metabox_serverside_addon_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_serverside_addon_settings' ) ); ?>" >
					<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
					<div class="inside">
						<?php
							if(!empty($message)) print '<div class="form-builder-'.$class.'-messages"><p class="'.$class.'-text">'.$message.'</p></div>';
						?>
                        <p><label><input type="checkbox" name="CP_CFF_SERVER_SIDE_EQUATIONS_DELAY" value="init" <?php
                        print get_option('CP_CFF_SERVER_SIDE_EQUATIONS_DELAY', '') == 'init' ? 'CHECKED' : '';
                        ?> /><?php _e('Delay the evaluation of equations until the <b>init</b> action.', 'calculated-fields-form')?></label></p>
						<p><?php _e('Define the server side equations', 'calculated-fields-form');?>:</p>
						<div><textarea name="CP_CFF_SERVER_SIDE_EQUATIONS" id="CP_CFF_SERVER_SIDE_EQUATIONS" style="width:100%;min-height:300px;"><?php
							if(file_exists($this->equations_file)) print esc_textarea(file_get_contents($this->equations_file));
						?></textarea>
						</div>
						<p><?php print (__('Or the file can be edited directly' , 'calculated-fields-form').': <b>'.$this->equations_file.'</b>'); ?></p>
						<p><input type="submit" value="<?php esc_attr_e(__('Save equations','calculated-fields-form')); ?>" class="button-secondary" /></p>
					</div>
					<input type="hidden" name="_cpcff_nonce" value="<?php echo wp_create_nonce( $this->addonID ); ?>" />
				</div>
			</form>
			<?php
		}

		public function get_addon_form_settings( $form_id )
		{
			if(isset($_REQUEST['cff_server_side_equation_form_settings']))
			{
				$this->_set_settings(
					$form_id,
					array(
						'active' => isset($_REQUEST['cff_server_side_equation_request_cost_active']) ? true : false,
						'equation' => !empty($_REQUEST['cff_server_side_equation_request_cost']) ? sanitize_text_field(stripslashes_deep($_REQUEST['cff_server_side_equation_request_cost'])) : ''
					)
				);
			}
			$settings = $this->_get_settings($form_id);

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<div id="metabox_serverside_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_serverside_addon_form_settings' ) ); ?>">
				<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
				<div class="inside">
					<table cellspacing="3px" class="form-table width100">
						<tr>
							<td>
								<input type="checkbox" name="cff_server_side_equation_request_cost_active" <?php if($settings['active']) print 'CHECKED'; ?> />
								<?php _e('Calculate the cost with a server-side equation after submitting the form.', 'calculated-fields-form'); ?>
							</td>
						</tr>
						<tr>
							<td>
								<p><b><?php _e('Call the Server Side Equation', 'calculated-fields-form'); ?></b></p>
								<input type="text" name="cff_server_side_equation_request_cost" value="<?php print esc_attr($settings['equation']); ?>" class="width100" />
								<p><i><?php _e('The server-side equation call requires the format: <b>equation_name(fieldname1, fieldname2, fieldname3)</b><br> The server-side equation should be defined from the settings page of the plugin.', 'calculated-fields-form', 'calculated-fields-form'); ?></i></p>
							</td>
						</tr>
					</table>
					<input type="hidden" name="cff_server_side_equation_form_settings" value="1" />
                    <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
				</div>
			</div>
			<?php
		} // End get_addon_form_settings

		/************************ ADDON CODE *****************************/
		/************************ ATTRIBUTES *****************************/

		private $blog_id;
		private $equations_file;
		private $equations_file_template = 'server-side-equations-template.php';

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = __("The add-on allows to define server side equations called from the public forms with the SERVER_SIDE operation", 'calculated-fields-form');

            $this->_get_file_path();

			// Check if the plugin is active
			if( !$this->addon_is_active() ) return;

			if(isset($_REQUEST['cff_server_side_equation']))
            {
                if(get_option('CP_CFF_SERVER_SIDE_EQUATIONS_DELAY', '') == 'init')
                    add_action('init', array($this, 'exec_equation'));
                else $this->exec_equation();
            }

			// Enqueue admin scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_operations_list' ), 1, 10 );

			// Enqueue public script
			add_filter('cpcff_the_form', array(&$this, 'enqueue_public_resources'), 1, 2);

			// Check if there is defined an equation to evaluate the request cost
			add_action( 'cpcff_price', array( &$this, 'request_cost' ), 1, 2 );
		} // End __construct

        /************************ PROTECTED METHODS *****************************/

		/**
         * Creates the database tables
         */
        protected function update_database()
		{
			$blog_id = CPCFF_AUXILIARY::blog_id();
			if( !file_exists($this->equations_file) )
			{
				$source = dirname(__FILE__).'/serverside.addon/'.$this->equations_file_template;
				if( file_exists($source) ) copy($source,$this->equations_file);
			}

		} // End update_database

		/************************ PRIVATE METHODS *****************************/

        private function _get_file_path($in_uploads_directory = false)
        {
			$this->blog_id = CPCFF_AUXILIARY::blog_id();

            $this->equations_file = dirname(__FILE__).'/serverside.addon/server-side-equations_'.$this->blog_id.'.php';

            // Never backup the file in the wp-content/uploads/calculated-fields-form directory
            if(class_exists('CPCFF_INSTALLER')) CPCFF_INSTALLER::add_to_backup_list($this->equations_file);

			$base_path = wp_upload_dir();
			if(
				!$base_path['error']
			)
			{
                if(
                    file_exists($base_path['basedir'].'/calculated-fields-form') ||
                    @mkdir($base_path['basedir'].'/calculated-fields-form') === true
                )
                {
                    if(
                        file_exists($base_path['basedir'].'/calculated-fields-form/server-side-equations_'.$this->blog_id.'.php') ||
                        $in_uploads_directory  ||
                        !file_exists($this->equations_file)
                    )
                    {
                        $this->equations_file = $base_path['basedir'].'/calculated-fields-form/server-side-equations_'.$this->blog_id.'.php';
                    }
                }
            }
            return $this->equations_file;
        } // End _get_file_path

		private function _get_settings($form_id)
		{
			$default_settings = array(
				'active' => false,
				'equation' => ''
			);

			return get_option('cff_server_side_equation_request_cost_'.$form_id, $default_settings);
		} // End _get_settings

		private function _set_settings($form_id, $settings)
		{
			update_option(
				'cff_server_side_equation_request_cost_'.$form_id,
				$settings
			);
		} // End _set_settings

		private function _exec_equation($equation_name, $parameters)
		{
			$result = new stdClass;
			if(file_exists($this->equations_file))
			{
				require_once $this->equations_file;
				if(isset($GLOBALS['SERVER_SIDE_EQUATIONS']) && is_array($GLOBALS['SERVER_SIDE_EQUATIONS']))
				{
					if(!empty($GLOBALS['SERVER_SIDE_EQUATIONS'][$equation_name]))
					{
						$parameters = array_values($parameters);
						try
						{
							$equation_result = call_user_func_array($GLOBALS['SERVER_SIDE_EQUATIONS'][$equation_name], $parameters);
							$result->result = $equation_result;
						}
						catch(Exception $err)
						{
							$result->error = $err->getMessage();
						}
					}
					else
					{
						$result->error = 'Non existent equation';
					}
				}
				else
				{
					$result->error = 'There are no equations';
				}
			}
			else
			{
				$result->error = 'The equations file does not exists';
			}
			return $result;
		} // End _exec_equation

		/************************ PUBLIC METHODS  *****************************/

		/**
		 * Checks if the request cost should be evaluated after submit the form with a server side equation and evaluate it
		 */
		public function request_cost($price, $params)
		{
			if(!empty($params['formid']))
			{
				$settings = $this->_get_settings($params['formid']);
				if(
					!empty($settings['active']) &&
					isset($settings['equation']) &&
					($eq = trim($settings['equation'])) != ''
				)
				{
					$eq_name;
					$eq_params = array();

					$tokens = token_get_all('<?php '.$eq);
					foreach($tokens as $token)
					{
						if(is_array($token))
						{
							$token_name = token_name($token[0]);
							if( $token_name == 'T_OPEN_TAG' ||  $token_name == 'T_WHITESPACE' ) continue;
							if( $token_name == 'T_STRING' && empty($eq_name)) $eq_name = $token[1];
							else
							{
								if(isset($params[$token[1]])) $eq_params[] = $params[$token[1]];
								elseif( $token_name == 'T_CONSTANT_ENCAPSED_STRING' ) $eq_params[] = trim($token[1], '\'"');
								else $eq_params[] = $token[1];
							}
						}
					}

					if(!empty($eq_name))
					{
						$result = $this->_exec_equation($eq_name, $eq_params);
						if(isset($result->result)) $price = $result->result;
					}
				}
			}

			return $price;
		} // End request_cost

		/**
		 * Import the equations file and execute the corresponding equation
		 */
		public function exec_equation()
		{
			$equation_name = $_REQUEST['cff_server_side_equation'];
			unset($_REQUEST['cff_server_side_equation']);
			$result = $this->_exec_equation($equation_name, $_REQUEST);
			print json_encode( $result );
			exit;
		} // End exec_equation

		/**
		 * Enqueue admin scripts
		 */
		public function enqueue_operations_list( $hook )
		{
			if (
				isset($_GET['page']) &&
				'cp_calculated_fields_form' == $_GET['page']
			)
			{
				wp_enqueue_script('cpcff_server_side_addon_admin', plugins_url('/serverside.addon/admin.js', __FILE__), array( 'jquery' ));
			}
		} // End enqueue_operations_list

		/**
		 * Enqueue public scripts and css
		 */
		public function enqueue_public_resources( $t1, $t2 )
		{
			$dep = [];
            if ($GLOBALS['CP_CALCULATEDFIELDSF_DEFAULT_DEFER_SCRIPTS_LOADING'])
            {
                wp_enqueue_script( 'jquery' );
                $dep[] = 'jquery';
            }
			wp_enqueue_script('cpcff_server_side_addon_public', plugins_url('/serverside.addon/public.js', __FILE__), $dep);
			return $t1;
		} // End enqueue_public_resources

		/**
		 * Enqueue admin scripts and css
		 */
		public function enqueue_admin_resources()
		{
			// Enqueue code editor and settings for manipulating PHP.
			if(!function_exists('wp_enqueue_code_editor')) return false;
			$settings = wp_enqueue_code_editor(
				array(
					'type' => 'application/x-httpd-php'
				)
			);

			// Bail if user disabled CodeMirror.
			if(false === $settings) return false;

			wp_add_inline_script(
				'code-editor',
				sprintf(
					'jQuery( function() { wp.codeEditor.initialize( "CP_CFF_SERVER_SIDE_EQUATIONS", %s ); } );',
					wp_json_encode( $settings )
				)
			);

			return true;
		} // End enqueue_admin_resources

		/**
		 *	Delete the form from the addon's table
		 */
        public function delete_form( $formid)
		{
			delete_option('cff_server_side_equation_request_cost_'.$formid);
		} // delete_form

		/**
		 *	Clone the form's row
		 */
		public function clone_form( $original_form_id, $new_form_id )
		{
			$settings = $this->_get_settings($original_form_id);
			$this->_set_settings($new_form_id, $settings);
		} // End clone_form

		/**
		 *	It is called when the form is exported to export the addons data too.
		 *  Receive an array with the other addons data, and the form's id for filtering.
		 */
		public function export_form($addons_array, $formid)
		{
			$settings = $this->_get_settings($formid);
			$addons_array[ $this->addonID ] = $settings;
			return $addons_array;
		} // End export_form

		/**
		 *	It is called when the form is imported to import the addons data too.
		 *  Receive an array with all the addons data, and the new form's id.
		 */
		public function import_form($addons_array, $formid)
		{
			if(isset($addons_array[$this->addonID]))
				$this->_set_settings($formid, $addons_array[$this->addonID]);
		} // End import_form

	} // End Class

    // Main add-on code
    $cpcff_server_side_equations_obj = new CPCFF_ServerSideEquations();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cpcff_server_side_equations_obj);
}