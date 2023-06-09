<?php
/**
 * Main class with main actions and filters: CPCFF_MAIN class
 *
 * @package CFF.
 * @since 1.0.170
 */

if(!class_exists('CPCFF_MAIN'))
{
	/**
	 * Class that defines the main actions and filters, and plugin's functionalities.
	 *
	 * @since  1.0.170
	 */
	class CPCFF_MAIN
	{
		/**
		 * Counter of forms in a same page
		 * Metaclass property.
		 *
		 * @since 1.0.170
		 * @var int $form_counter
		 */
		public static $form_counter = 0;

		/**
		 * Instance of the CPCFF_MAIN class
		 * Metaclass property to implement a singleton.
		 *
		 * @since 1.0.179
		 * @var object $_instance
		 */
		private static $_instance;

		/**
		 * Identifies if the class was instanciated from the public website or WordPress
		 * Instance property.
		 *
		 * @sinze 1.0.170
		 * @var bool $_is_admin
		 */
		private $_is_admin = false;

		/**
		 * Plugin URL
		 * Instance property.
		 *
		 * @sinze 1.0.170
		 * @var string $_plugin_url
		 */
		private $_plugin_url;

		/**
		 * Flag to know if the public resources were included
		 * Instance property.
		 *
		 * @sinze 1.0.170
		 * @var bool $_are_resources_loaded default false
		 */
		private $_are_resources_loaded = false;

		/**
		 * Forms list.
		 * List of instances of the CPCFF_FORM class.
		 * Instance property.
		 *
		 * @sinze 1.0.179
		 * @var object $_active_form
		 */
		private $_forms = array();

		/**
		 * Instance of the CPCFF_AMP class to manage the forms in AMP pages
		 * Instance property.
		 *
		 * @sinze 1.0.230
		 * @var object $_amp
		 */
		private $_amp;

		/**
		 * Constructs a CPCFF_MAIN object, and define the hooks to the filters and actions.
		 * The constructor is private because this class is a singleton
		 */
		private function __construct()
		{
			require_once CP_CALCULATEDFIELDSF_BASE_PATH.'/inc/cpcff_form.inc.php';
			require_once CP_CALCULATEDFIELDSF_BASE_PATH.'/inc/cpcff_submissions.inc.php';
			require_once CP_CALCULATEDFIELDSF_BASE_PATH.'/inc/cpcff_amp.inc.php';

			// Initializes the $_is_admin property
			$this->_is_admin = is_admin();

			// Initializes the $_plugin_url property
			$this->_plugin_url = plugin_dir_url(CP_CALCULATEDFIELDSF_MAIN_FILE_PATH);

			// Plugin activation/deactivation
			$this->_activate_deactivate();

			// Load the language file, and the addons
			add_action( 'plugins_loaded', array($this, 'plugins_loaded') );

			// Instanciate the AMP object
			$this->_amp = new CPCFF_AMP($this);

			// Run the initialization code
			add_action( 'init', array($this, 'init'), 1 );

			// Run the initialization code of widgets
			add_action( 'widgets_init', array($this, 'widgets_init'), 1 );

			// Integration with Page Builders
			require_once CP_CALCULATEDFIELDSF_BASE_PATH.'/inc/cpcff_page_builders.inc.php';
			CPCFF_PAGE_BUILDERS::run();

		} // End __construct

		/**
		 * Returns the instance of the singleton.
		 *
		 * @since 1.0.179
		 * @return object self::$_instance
		 */
		public static function instance()
		{
			if(!isset(self::$_instance))
			{
				self::$_instance = new self();
			}
			return self::$_instance;
		} // End instance

		/**
		 * Loads the primary resources, previous to the plugin's initialization
		 *
		 * Loads resources like the laguages files, add ons, etc.
		 *
		 * @return void.
		 */
		public function plugins_loaded()
		{
            // Fix different troubleshoots
			$this->troubleshoots();

			// Load the language file
			$this->_textdomain();

			// Load the add ons
			require_once CP_CALCULATEDFIELDSF_BASE_PATH.'/inc/cpcff_addons.inc.php'; // Loads the addons.
			CPCFF_ADDONS::load();

			// Load controls scripts
			$this->_load_controls_scrips();
		} // End plugins_loaded

		/**
		 * Initializes the plugin, runs as soon as possible.
		 *
		 * Initilize the plugin's sections, intercepts the submissions, generates the resources etc.
		 *
		 * @return void.
		 */
		public function init()
		{
			CPCFF_AUXILIARY::clean_transients_hook(); // Set the hook for clearing the expired transients

			if ( $this->_is_admin ) // Initializes the WordPress modules.
			{
				// Checks if is being loaded the coupons list
				CPCFF_COUPON::settings_actions($_GET);

				if(
					false === ($CP_CALCULATEDFIELDSF_VERSION = get_option('CP_CALCULATEDFIELDSF_VERSION')) ||
					$CP_CALCULATEDFIELDSF_VERSION != CP_CALCULATEDFIELDSF_VERSION
				)
				{
					if(class_exists('CPCFF_INSTALLER')) CPCFF_INSTALLER::install(is_multisite());
					update_option('CP_CALCULATEDFIELDSF_VERSION', CP_CALCULATEDFIELDSF_VERSION);
				}

				// Update metabox status if corresponds
				$this->update_metabox_status();

				// Adds the plugin links in the plugins sections
				add_filter( 'plugin_action_links_'.CP_CALCULATEDFIELDSF_BASE_NAME, array($this, 'links' ) );

				// Creates the menu entries in the WordPress menu.
				add_action( 'admin_menu', array( $this, 'admin_menu' ) );
				add_action( 'admin_head', array( $this, 'admin_menu_styles' ), 11 );

				// Displays the shortcode insertion buttons.
				add_action( 'media_buttons', array( $this, 'media_buttons' ) );

				// Loads the admin resources
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_resources' ), 1 );
			}
			else // Initializes the public modules.
			{
				// Checks if it is being validated the coupon code from the public form.
				CPCFF_COUPON::check_from_web($_REQUEST);
			}
			$this->_define_shortcodes();
			add_action('cpcff_clear_cache', array( 'CPCFF_AUXILIARY', 'clear_js_cache') );
			add_action('wp_enqueue_scripts', array($this, 'enqueue_loader') );
		} // End init

		/**
		 * Registers the widgets.
		 *
		 * Registers the widget to include the forms on sidebars, and for loading the data collected by the forms in the dashboard.
		 *
		 * @since 1.0.178
		 *
		 * @return void.
		 */
		public function widgets_init()
		{
			// Includes the widgets code
			require_once CP_CALCULATEDFIELDSF_BASE_PATH.'/inc/cpcff_widgets.inc.php';

			$this->register_widgets();

			// Load the dashboard widgets
			add_action( 'wp_dashboard_setup', array($this, 'dashboard_widgets') );

			// Replace the shortcodes into the text widgets.
			if(!$this->_is_admin) add_filter('widget_text', 'do_shortcode');
		} // End widgets_init

		/**
		 * Adds the plugin's links in the plugins section.
		 *
		 * Links for accessing to the help, settings, developers website, etc.
		 *
		 * @param array $links.
		 *
		 * @return array.
		 */
		public function links( $links )
		{
			array_unshift(
				$links,
				'<a href="https://cff.dwbooster.com/customization" target="_blank">'.__('Request custom changes').'</a>',
				'<a href="admin.php?page=cp_calculated_fields_form">'.__('Settings').'</a>',
				'<a href="https://cff.dwbooster.com/documentation" target="_blank">'.__('Help').'</a>'
			);
			return $links;
		} // End links

		/**
		 * Prints the buttons for inserting the different shortcodes into the pages/posts contents.
		 *
		 * Prints the HTML code that appears beside the media button with the icons and code to insert the shortcodes:
		 *
		 * - CP_CALCULATED_FIELDS
		 * - CP_CALCULATED_FIELDS_RESULT
		 * - CP_CALCULATED_FIELDS_VAR
		 *
		 * @return void.
		 */
		public function media_buttons()
		{
			print '<a href="javascript:cp_calculatedfieldsf_insertForm();" title="'.esc_attr__('Insert Calculated Fields Form', 'calculated-fields-form' ).'"><img src="'.$this->_plugin_url.'images/cp_form.gif" alt="'.esc_attr__('Insert Calculated Fields Form', 'calculated-fields-form' ).'" /></a><a href="javascript:cp_calculatedfieldsf_insertForm(true);" title="'.esc_attr__('Insert Calculated Fields Form Results', 'calculated-fields-form' ).'"><img src="'.$this->_plugin_url.'images/cp_form_result.gif" alt="'.esc_attr__('Insert Calculated Fields Form Results', 'calculated-fields-form' ).'" /></a><a href="javascript:cp_calculatedfieldsf_insert_results_list();" title="'.esc_attr__('Insert Calculated Fields Form Results List', 'calculated-fields-form' ).'"><img src="'.$this->_plugin_url.'images/cp_form_result_list.gif" alt="'.esc_attr__('Insert Calculated Fields Form Results', 'calculated-fields-form' ).'" /></a><a href="javascript:cp_calculatedfieldsf_insertVar();" title="'.esc_attr__('Create a JavaScript var from POST, GET, SESSION, or COOKIE var', 'calculated-fields-form' ).'"><img src="'.$this->_plugin_url.'images/cp_var.gif" alt="'.esc_attr__('Create a JavaScript var from POST, GET, SESSION, or COOKIE var', 'calculated-fields-form' ).'" /></a>';
		} // End media_buttons

		/**
		 * Generates the entries in the WordPress menu.
		 *
		 * @return void.
		 */
		public function admin_menu()
		{
			global $submenu;

			// Settings page
			add_options_page('Calculated Fields Form Options', 'Calculated Fields Form (Platinum)', apply_filters('cpcff_forms_edition_capability', 'manage_options'), 'cp_calculated_fields_form', array($this, 'admin_pages') );

			// Menu option
			add_menu_page( 'Calculated Fields Form Options', 'Calculated Fields Form (Platinum)', apply_filters('cpcff_forms_edition_capability', 'manage_options'), 'cp_calculated_fields_form', array($this, 'admin_pages') );

			// Submenu options
			add_submenu_page( 'cp_calculated_fields_form', 'Calculated Fields Form', 'All Forms', apply_filters('cpcff_forms_edition_capability', 'manage_options'), "cp_calculated_fields_form", array($this, 'admin_pages') );

			add_submenu_page( 'cp_calculated_fields_form', 'Calculated Fields Form - New Form', 'Add New', apply_filters('cpcff_forms_edition_capability', 'manage_options'), "cp_calculated_fields_form_sub_new", array($this, 'admin_pages') );

			add_submenu_page( 'cp_calculated_fields_form', 'Calculated Fields Form - Entries', 'Entries', apply_filters('cpcff_forms_edition_capability', 'manage_options'), "cp_calculated_fields_form_sub_entries", array($this, 'admin_pages') );

			add_submenu_page( 'cp_calculated_fields_form', 'Calculated Fields Form - Addons', 'Addons', apply_filters('cpcff_forms_edition_capability', 'manage_options'), "cp_calculated_fields_form_sub_addons", array($this, 'admin_pages') );

			add_submenu_page( 'cp_calculated_fields_form', 'Calculated Fields Form - Troubleshoot Area & General Settings', 'Troubleshoot Area & General Settings', apply_filters('cpcff_forms_edition_capability', 'manage_options'), "cp_calculated_fields_form_sub_troubleshoots_settings", array($this, 'admin_pages') );

			add_submenu_page( 'cp_calculated_fields_form', 'Calculated Fields Form - Import & Export Forms', 'Import & Export Forms', apply_filters('cpcff_forms_edition_capability', 'manage_options'), "cp_calculated_fields_form_sub_import_export", array($this, 'admin_pages') );

			add_submenu_page( 'cp_calculated_fields_form', 'Marketplace', 'Marketplace', apply_filters('cpcff_forms_edition_capability', 'manage_options'), "cp_calculated_fields_form_sub_marketplace", array($this, 'admin_pages') );

			add_submenu_page( 'cp_calculated_fields_form', 'Documentation', 'Documentation', apply_filters('cpcff_forms_edition_capability', 'manage_options'), "cp_calculated_fields_form_sub_documentation", array($this, 'admin_pages') );

			// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
			if ( ! empty( $submenu ) && is_array( $submenu ) && ! empty( $submenu["cp_calculated_fields_form"] ) ) {
				foreach ( $submenu["cp_calculated_fields_form"] as $index => $item ) {
					if ( 'cp_calculated_fields_form_sub_marketplace' == $item[2] ) {
						if ( isset( $item[4] ) ) {
							$submenu["cp_calculated_fields_form"][ $index ][4] .= ' calculated-fields-form-submenu-marketplace';
						} else {
							$submenu["cp_calculated_fields_form"][ $index ][] = 'calculated-fields-form-submenu-marketplace';
						}
					}
				}
			}
			// phpcs:enable WordPress.WP.GlobalVariablesOverride.Prohibited

		} // End admin_menu

		public function admin_menu_styles() {
			$styles = '';

			$styles .= 'a.calculated-fields-form-submenu-marketplace { background-color: #f0db4f !important; color: #323330 !important; font-weight: 600 !important; }';

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			printf( '<style>%s</style>', $styles );
		} // End admin_menu_styles

		/**
		 * Loads the corresponding pages in the WordPress or redirects the user to the external URLs.
		 *
		 * Loads the webpage with the list of forms, addons activation, general settings, etc.
		 * or redirects to external webpages like plugin's documentation
		 *
		 * @since 1.0.181
		 */
		public function admin_pages()
		{
			// Settings page of the plugin
			if( isset($_GET["cal"]) && $_GET["cal"] != ''
			)
			{
				// Settings page of the plugin
				if (isset($_GET["list"]) && $_GET["list"] == '1')
					@include_once CP_CALCULATEDFIELDSF_BASE_PATH . '/inc/cpcff_admin_int_message_list.inc.php';
				else // Forms builder
					@include_once CP_CALCULATEDFIELDSF_BASE_PATH . '/inc/cpcff_admin_int.inc.php';
			}
			else
			{
				// Redirecting outer website
				if (isset($_GET["page"]) &&$_GET["page"] == 'cp_calculated_fields_form_sub_documentation')
				{
					if(@wp_redirect('https://cff.dwbooster.com/documentation')) exit;
				}
				elseif (isset($_GET["page"]) && $_GET["page"] == 'cp_calculated_fields_form_sub_marketplace')
				{
					if(@wp_redirect('https://cff-bundles.dwbooster.com')) exit;
				}
				else
					@include_once CP_CALCULATEDFIELDSF_BASE_PATH . '/inc/cpcff_admin_int_list.inc.php';
			}
		} // End admin_pages

		/**
		 * Registers the associated widgets
		 *
		 * Checks if the corresponding class exists and registers it as widget.
		 *
		 * @since 1.0.178
		 *
		 * @return void.
		 */
		public function register_widgets()
		{
			if(class_exists('CPCFF_WIDGET')) register_widget('CPCFF_WIDGET');
		} // End register_widgets

		/**
		 * Loads the dashboard widgets
		 *
		 * Checks if the corresponding class exists and creates it.
		 *
		 * @since 1.0.178
		 *
		 * @return void.
		 */
		public function dashboard_widgets()
		{
			if(class_exists('CPCFF_DASHBOART_WIDGET')) new CPCFF_DASHBOART_WIDGET();
		} // End dashboard_widgets

		/**
		 * Loads the javascript and style files.
		 *
		 * Checks if there is the settings page of the plugin for loading the corresponding JS and CSS files,
		 * or if it is a post or page the script for inserting the shortcodes in the content's editor.
		 *
		 * @since 1.0.171
		 *
		 * @param string $hook.
		 * @return void.
		 */
		public function admin_resources( $hook )
		{
			if ( isset($_GET['page']) )
			{
				if(
					'cp_calculated_fields_form_sub_documentation' == $_GET["page"] ||
					'cp_calculated_fields_form_sub_marketplace' == $_GET["page"]
				)
				{

					$redirect_url = '';
					$cpcff_redirect = array();
					switch ( $_GET['page'] ) {
						case 'cp_calculated_fields_form_sub_documentation':
							$cpcff_redirect['url'] = 'https://cff.dwbooster.com/documentation';
							break;
						case 'cp_calculated_fields_form_sub_marketplace':
							$cpcff_redirect['url'] = 'https://cff-bundles.dwbooster.com';
							break;
					}
					wp_enqueue_script( 'cp_calculatedfieldsf_redirect_script', plugins_url( '/js/redirect_script.js', CP_CALCULATEDFIELDSF_MAIN_FILE_PATH ), array(), CP_CALCULATEDFIELDSF_VERSION );
					wp_localize_script(
						'cp_calculatedfieldsf_redirect_script',
						'cpcff_redirect',
						$cpcff_redirect
					);

				} elseif ( 'cp_calculated_fields_form_sub_entries' == $_GET["page"] ) {

					print "<script>document.location = 'admin.php?page=cp_calculated_fields_form&list=1&search&dfrom&dto&cal=0&ds=Filter&_cpcff_nonce=". esc_js( wp_create_nonce( 'cff-submissions-list' ) ) . "';</script>";

				} elseif (
					in_array( $_GET['page'], array( 'cp_calculated_fields_form', 'cp_calculated_fields_form_sub_new', 'cp_calculated_fields_form_sub_entries', 'cp_calculated_fields_form_sub_troubleshoots_settings', 'cp_calculated_fields_form_sub_import_export', 'cp_calculated_fields_form_sub_addons' ) )
				) {

					wp_deregister_script( 'tribe-events-bootstrap-datepicker' );
					wp_register_script('tribe-events-bootstrap-datepicker', plugins_url('/js/nope.js', CP_CALCULATEDFIELDSF_MAIN_FILE_PATH));

					wp_enqueue_script( "jquery" );

					if(function_exists('wp_enqueue_media')) wp_enqueue_media();

					wp_enqueue_script( "jquery-ui-core" );
					wp_enqueue_script( "jquery-ui-sortable" );
					wp_enqueue_script( "jquery-ui-tabs" );
					wp_enqueue_script( "jquery-ui-droppable" );
					wp_enqueue_script( "jquery-ui-button" );
					wp_enqueue_script( "jquery-ui-datepicker" );
					wp_deregister_script('query-stringify');
					wp_register_script('query-stringify', plugins_url('/vendors/jQuery.stringify.js', CP_CALCULATEDFIELDSF_MAIN_FILE_PATH), array( 'jquery' ), 'pro');
					wp_enqueue_script( "query-stringify" );

					//ULR to the admin resources
					$admin_resources = admin_url( "admin.php?page=cp_calculated_fields_form&cp_cff_resources=admin" );
					wp_enqueue_script( 'cp_calculatedfieldsf_builder_script', $admin_resources, array("jquery","jquery-ui-core","jquery-ui-sortable","jquery-ui-tabs","jquery-ui-droppable","jquery-ui-button", "jquery-ui-accordion","jquery-ui-datepicker","query-stringify"), CP_CALCULATEDFIELDSF_VERSION );

					wp_enqueue_script( 'cp_calculatedfieldsf_builder_library_script', plugins_url('/js/library.js', CP_CALCULATEDFIELDSF_MAIN_FILE_PATH), array("cp_calculatedfieldsf_builder_script"), CP_CALCULATEDFIELDSF_VERSION );

					wp_localize_script('cp_calculatedfieldsf_builder_library_script', 'cpcff_forms_library_config', array(
						'version' => 'plat',
						'website_url' => 'admin.php?page=cp_calculated_fields_form&a=1&_cpcff_nonce=' . wp_create_nonce( 'cff-add-form' )
					));

					wp_enqueue_script( 'cp_calculatedfieldsf_builder_script_caret', plugins_url('/vendors/jquery.caret.js', CP_CALCULATEDFIELDSF_MAIN_FILE_PATH),array("jquery"), CP_CALCULATEDFIELDSF_VERSION );
					wp_enqueue_style('cp_calculatedfieldsf_builder_style', plugins_url('/css/style.css', CP_CALCULATEDFIELDSF_MAIN_FILE_PATH), array(), CP_CALCULATEDFIELDSF_VERSION);
					wp_enqueue_style('cp_calculatedfieldsf_builder_library_style', plugins_url('/css/stylelibrary.css', CP_CALCULATEDFIELDSF_MAIN_FILE_PATH), array('cp_calculatedfieldsf_builder_style'), CP_CALCULATEDFIELDSF_VERSION);
					wp_enqueue_style('jquery-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css', array(), CP_CALCULATEDFIELDSF_VERSION);

				}
			}

			// Checks if it is a page or post
			if( 'post.php' == $hook  || 'post-new.php' == $hook )
			{
				wp_enqueue_script( 'cp_calculatedfieldsf_script', plugins_url('/js/cp_calculatedfieldsf_scripts.js', CP_CALCULATEDFIELDSF_MAIN_FILE_PATH) );
			}
		} // End admin_resources

		public function metabox_status( $metabox_id )
		{
			$statuses = get_option( 'cff-metaboxes-statuses', [] );
			return ( ! empty( $statuses ) && is_array( $statuses ) && isset( $statuses[ $metabox_id ] ) && $statuses[ $metabox_id ] == 0) ? 'cff-metabox-closed' : 'cff-metabox-opened';
		} // End metabox_status

		private function update_metabox_status()
		{
			if(
				! empty( $_POST['cff-metabox-nonce'] ) &&
				wp_verify_nonce( $_POST['cff-metabox-nonce'], 'cff-metabox-status' ) &&
				isset( $_POST['cff-metabox-id'] ) &&
				isset( $_POST['cff-metabox-action'] )
			)
			{
				$metabox_id = sanitize_text_field( wp_unslash( $_POST['cff-metabox-id'] ) );
				$metabox_action = sanitize_text_field( wp_unslash( $_POST['cff-metabox-action'] ) );

				if( ! empty( $metabox_id ) ) {
					$statuses = get_option( 'cff-metaboxes-statuses', [] );
					if( empty( $statuses ) || ! is_array( $statuses ) )
					{
						$statuses = [];
					}
					$statuses[$metabox_id] = $metabox_action == 'open' ? 1 : 0;
					update_option( 'cff-metaboxes-statuses', $statuses );
				}
			}
		} // End update_metabox_status

		public function form_preview( $atts )
		{
			if(isset($atts['shortcode_atts']))
			{
				error_reporting(E_ERROR|E_PARSE);
				global  $wp_styles, $wp_scripts;
				if(!empty($wp_scripts)) $wp_scripts->reset();
				$message = $this->public_form($atts['shortcode_atts']);
				ob_start();
				if(!empty($wp_styles))  $wp_styles->do_items();
				if(!empty($wp_scripts)) $wp_scripts->do_items();
				if(class_exists('Error'))
				{
					try{ wp_footer(); } catch(Error $err) {}
				}
				$message .= ob_get_contents();
				ob_end_clean();
				$page_title = (!empty($atts['page_title'])) ? $atts['page_title'] : '';
				remove_all_actions('shutdown');
				if(!empty($atts['wp_die']))
				{
					wp_die($message.'<style>body{margin:2em !important;max-width:100% !important;box-shadow:none !important;background:white !important}html{background:white !important;}.wp-die-message>*:not(form){visibility: hidden;}  .pac-container, .ui-tooltip, .ui-tooltip *,.ui-datepicker,.ui-datepicker *{visibility: visible;}</style>'.apply_filters('cpcff_form_preview_resources', ''), $page_title, 200);
				}
				elseif(!empty($atts['page']))
				{
					print '<!DOCTYPE html><html><head profile="http://gmpg.org/xfn/11"><meta name="robots" content="noindex,follow" /><meta charset="utf-8" /><meta name="viewport" content="width=device-width, initial-scale=1"></head><body>';
					print $message;
					print '<style>body>*:not(form){visibility: hidden;} .pac-container, .ui-tooltip, .ui-tooltip *,.ui-datepicker,.ui-datepicker *{visibility: visible;}</style>'.apply_filters('cpcff_form_preview_resources', '').'</body></html>';
					exit;
				}
				else
				{
					print $message;
					exit;
				}
			}
		} // End form_preview

		public function enqueue_loader()
		{
			global $post;

			if(!empty($post) && has_shortcode($post->post_content, 'CP_CALCULATED_FIELDS'))
				wp_enqueue_style( 'cpcff_loader', plugins_url('/css/loader.css', CP_CALCULATEDFIELDSF_MAIN_FILE_PATH), array(), CP_CALCULATEDFIELDSF_VERSION );
		} // End enqueue_loader

		/**
		 * Returns the public version of the form wih its resources.
		 *
		 * The method calls the filters: cpcff_pre_form, and cpcff_the_form
		 * @since 1.0.171
		 * @param array $atts includes the attributes required to identify the form, and create the variables.
		 * @return string $content a text with the public version of the form and resources.
		 */
		public function public_form( $atts )
		{
			// If the website is being visited by crawler, display empty text.
			if( CPCFF_AUXILIARY::is_crawler() ) return '';
			if( empty($atts) ) $atts = array();
			if(!$this->_is_admin && $this->_amp->is_amp())
			{
				$content = $this->_amp->get_iframe($atts);
			}
			else
			{
				global $wpdb, $cpcff_default_texts_array;

				if( empty( $atts[ 'id' ] ) ) // if was not passed the form's id get all.
				{
					$myrow = $wpdb->get_row( "SELECT * FROM ".$wpdb->prefix.CP_CALCULATEDFIELDSF_FORMS_TABLE );
				}
				else
				{
					$myrow = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.CP_CALCULATEDFIELDSF_FORMS_TABLE." WHERE id=%d",$atts[ 'id' ] ) );
				}

				if( empty( $myrow ) ) return ''; // The form does not exists, or there are no forms.
				$atts[ 'id' ] = $myrow->id; // If was not passed the form's id, uses the if of first form.
				$id = $atts[ 'id' ]; // Alias for the $atts[ 'id' ] variable.

				// Initializing the $form_counter
                if(!isset($GLOBALS['codepeople_form_sequence_number'])) $GLOBALS['codepeople_form_sequence_number'] = 0;
                $GLOBALS['codepeople_form_sequence_number']++;
                self::$form_counter = $GLOBALS['codepeople_form_sequence_number']; // Current form

				/**
				 * Filters applied before generate the form,
				 * is passed as parameter an array with the forms attributes, and return the list of attributes
				 */
				$atts = apply_filters( 'cpcff_pre_form',  $atts );

				ob_start();

				// Constant defined to protect the "inc/cpcff_public_int.inc.php" file against direct accesses.
				if ( !defined('CP_AUTH_INCLUDE') ) define('CP_AUTH_INCLUDE', true);

				$this->_public_resources($id); // Load form scripts and other resources

				/* TO-DO: This method should be analyzed after moving other functions to the main class . */
				$button_label = $this->get_form($id)->get_option('vs_text_submitbtn', 'Submit');
				$button_label = ($button_label==''?'Submit':$button_label);
				@include CP_CALCULATEDFIELDSF_BASE_PATH . '/inc/cpcff_public_int.inc.php';

				$content = ob_get_contents();

				// The attributes excepting "id" are converted in javascript variables with a global scope
				if( count( $atts ) > 1 )
				{
					$content .= '<script>';
					foreach( $atts as $i => $v )
					{
						if( $i != 'id' && $i != 'class' && !is_numeric( $i ) )
						{
							$nV = ( is_numeric( $v ) ) ? $v : json_encode( $v ); // Sanitizing the attribute's value
							$content .= $i.'='.$nV.';';
							$content .= 'if(typeof '.$i.'_arr == "undefined") '.$i.'_arr={}; '.$i.'_arr["_'.self::$form_counter.'"]='.$nV.';';
						}
					}
					$content .= '</script>';
				}
				ob_end_clean();

				/**
				 * Filters applied after generate the form,
				 * is passed as parameter the HTML code of the form with the corresponding <LINK> and <SCRIPT> tags,
				 * and returns the HTML code to includes in the webpage
				 */
				$content = apply_filters( 'cpcff_the_form', $content,  $atts[ 'id' ] );
			}

			return $content;
		} // End  public_form

		/**
		 * Replaces the shortcode of form's result with the summary of submitted data.
		 *
		 * Applies the "cpcff_summary" filter.
		 *
		 * @since 1.0.181
		 *
		 * @param array $atts the attributes defined in the summary shortcode.
		 * @param string $content text enclosed between the summary shortcodes. An empty text by default.
		 * @param integer $id the form's id.
		 *
		 * @return string, the HTML structure with the summary of collected data or an empty text.
		 */
		public function form_result_shortcode($atts, $content = "", $id = 0)
		{
			if( CPCFF_AUXILIARY::is_crawler() ) return '';

			global $wpdb;
			$output = '';

			$atts = shortcode_atts( array( 'fields' => '', 'formid' => '', 'if_latest' => 0, 'if_paid' => 0), $atts );

			// If the shortcode is limited to specific forms
			$atts['formid'] = preg_replace(array('/[^\d\,]/', '/^\,+/', '/\,+$/'), '', $atts['formid']);
			if(!empty($atts['formid'])) $atts['formid'] = explode(',', $atts['formid']);

			if(empty($id) || !is_numeric($id))
			{
                if(!empty($_REQUEST['cff_no_cache']))
                {
                    $id = CPCFF_AUXILIARY::decrypt(base64_decode($_REQUEST['cff_no_cache']));
                }

				if(
                    (empty($id) || !is_numeric($id)) &&
                    ($cp_cff_form_data = CP_SESSION::registered_events()) !== false
                )
				{
					$latest = $cp_cff_form_data['latest'];
					if(!empty($atts['formid']))
					{
						// The shortcode is related to one or multiple forms
						if(in_array($latest, $atts['formid']))
						{
							$id = $cp_cff_form_data[$latest];
						}
						elseif(empty($atts['if_latest']))
						{
							$interception = array_intersect(array_keys($cp_cff_form_data), $atts['formid']);
							if(count($interception)) $id = $cp_cff_form_data[array_shift($interception)];
						}
					}
					// There is not a formid attribute in the shortcode, uses the latest submission
					else $id = $cp_cff_form_data[$latest];
				}
			}

			if(!empty($id))
			{
				$content = html_entity_decode( $content );
				$submission_obj = CPCFF_SUBMISSIONS::get($id);
				if( $submission_obj && (!$atts['if_paid'] || $submission_obj->paid*1))
				{
					$form_obj = CPCFF_SUBMISSIONS::get_form($id);

					if(
						empty($atts['formid']) ||
						in_array($form_obj->get_id(), $atts['formid'])
					)
					{
						// If the result will include only some fields or it has a specific format,
						// will be required the form's structure
						if( !empty( $atts[ 'fields' ] ) || !empty( $content ) )
						{
							$fields = $form_obj->get_fields();
						}

						// Use the stored summary
						if( empty( $fields ) )
						{
							$output = '<p>'.str_replace(
									array('&lt;', '&gt;', '\"', "\'"),
									array('<', '>', '"', "'" ),
									preg_replace( "/\n+/", "<br />", $submission_obj->data )
								).'</p>';
						}
						// Create a custom summary
						else
						{
							$fields[ 'ipaddr' ] = $submission_obj->ipaddr;
							$fields[ 'submission_datetime' ] = $submission_obj->time;
							$fields[ 'paid' ] = $submission_obj->paid;
							// Create the summary with the "fields" attribute.
							$atts[ 'fields' ] = explode( ",", str_replace( " ", "", $atts[ 'fields' ] ) );
							foreach ($atts['fields'] as $field )
							{
								// The field exits in the form's structure.
								if( isset( $fields[$field] ) )
								{
									// The field was submitted
									if(isset($submission_obj->paypal_post[$field]))
									{
										if(is_array($submission_obj->paypal_post[ $field ]))
										{
											$submission_obj->paypal_post[ $field ] = implode(',', $submission_obj->paypal_post[$field]);
										}
										$output .= "<p>{$fields[ $field ]->title} {$submission_obj->paypal_post[ $field ]}</p>";
									}
									// It is a textual field
									elseif(in_array($fields[ $field ]->ftype, array('fSectionBreak', 'fCommentArea')))
									{
										if($fields[ $field ]->ftype == 'fCommentArea')
											$output .= "<p><strong>".$fields[ $field ]->title."</strong>";
										else
											$output .= "<p>".$fields[ $field ]->title;

										if(!empty($fields[ $field ]->userhelp))
											$output .= "<br /><pan class='uh'>".$fields[ $field ]->userhelp."</span>";
										$output .= "</p>";
									}
								}
							}

							// Replaces the shortcode's content.
							if( $content != '' )
							{
								$content = do_shortcode($content);
								$replaced_values = CPCFF_AUXILIARY::parsing_fields_on_text(
									$fields,
									$submission_obj->paypal_post,
									$content,
									$submission_obj->data,
									'html',
									$id
								);
								$output .= $replaced_values[ 'text' ];
							}
						}
					}
				}
			}

			// Applies the filter before return.
			return apply_filters( 'cpcff_summary', $output, $id );

		} // End form_result_shortcode

		/**
		 * Replaces the shortcode of form's results list with the summary of submitted data.
		 *
		 * Calls the "form_result_shortcode" method for each submission.
		 *
		 * @since 10.0.331
		 *
		 * @param array $atts the attributes defined in the summary shortcode.
		 * @param string $content text enclosed between the summary shortcodes. An empty text by default.
		 *
		 * @return string, the HTML structure with the list of summaries of collected data or an empty text.
		 */
		public function form_result_list_shortcode($atts, $content = "")
		{
			if( CPCFF_AUXILIARY::is_crawler() ) return '';

			global $wpdb;
			$output = '';
			$cond = "1=1";

			// $atts['role']
			if(
				isset($atts['role']) &&
				($roles = trim($atts['role'])) !== ''
			)
			{
				$roles = explode(',', $roles);
				$no_role = true;
				$user = wp_get_current_user();

				if ( in_array( 'administrator', (array) $user->roles ))
				{
					$no_role = false;
				}
				else
				{
					foreach($roles as $role)
					{
						$role = trim($role);
						if(empty($role)) continue;
						if ( in_array( $role, (array) $user->roles ))
						{
							$no_role = false;
							break;
						}
					}
				}

				if($no_role) return '';
			}

			// $atts['submission']
			if(
				isset($atts['submission']) &&
				($ids = preg_replace('/[^\,\d]/', '', $atts['submission'])) !== ''
			)
			{
				$ids = explode(',', $ids);
				foreach( $ids as $_key => $_id)
				{
					$_id = trim($_id);
					$_id = @intval($_id);
					if(empty($_id)) unset($ids[$_key]);
					else $ids[$_key] = $_id;

				}
				if(!empty($ids)) $cond .= " AND id IN (".implode(',', $ids).")";
			}

			// $atts['formid'] required
			if(
				isset($atts['formid'])
			)
			{
				$formid = preg_replace('/[^\d\,]/', '', $atts['formid']);
				$formid = trim($formid, ',');
				if(!empty($formid)) $cond .= " AND formid IN (".$formid.")";
			}

			if(empty($formid) && empty($ids)) return '';

			// $atts['if_paid']
			if(
				isset($atts['if_paid']) &&
				$atts['if_paid']*1 == 1
			) $cond .= " AND paid=1";

			// $atts['from']
			if(
				isset($atts['from']) &&
				($from = trim($atts['from'])) !== ''
			) $cond .= $wpdb->prepare(" AND (`time` >= %s)", $from);

			// $atts['to']
			if(
				isset($atts['to']) &&
				($to = trim($atts['to'])) !== ''
			) $cond .= $wpdb->prepare(" AND (`time` <= %s)", $to.' 23:59:59');

			// $atts['order']
			$cond .= " ORDER BY time ";
			if(
				isset($atts['order']) &&
				($order = trim($atts['order'])) !== '' &&
				($order = strtoupper($order)) === 'ASC'
			) 	 $cond .= "ASC";
			else $cond .= "DESC";

			// For pagination
			$current_page = 0;
			$events_per_page = 0;
			$limit = 0;
			$page_links = '';

			// $atts['limit'] minimum between limit and events per page
			if( isset($atts['limit']) )
			{
				$limit = @intval($atts['limit']);
				$total = $limit;
			}

			// $atts['events_per_page']
			if( isset($atts['events_per_page']) )
			{
				if( isset( $_GET[ 'events_page' ] ) )
				{
					$current_page = @intval( $_GET[ 'events_page' ] );
					unset( $_GET[ 'events_page' ] );
				}
				$events_per_page = max(1,@intval( $atts['events_per_page'] ));
				$limit = ($limit) ? min($limit-max($current_page - 1, 0 )*$events_per_page, $events_per_page) : $events_per_page;
			}

			if( !empty($limit) )
				$cond .= $wpdb->prepare(" LIMIT %d,%d", max($current_page - 1, 0 )*$events_per_page, $limit);

			$query = "SELECT SQL_CALC_FOUND_ROWS * FROM ".CP_CALCULATEDFIELDSF_POSTS_TABLE_NAME." WHERE ".$cond;
			$query = apply_filters('cpcff_results_list_query', $query);

			$submissions = CPCFF_SUBMISSIONS::populate($query);
			if(!empty($submissions))
			{
				if($events_per_page)
				{
					// Get total records for pagination
					$total = ($total) ? MIN($wpdb->get_var( "SELECT FOUND_ROWS()" ), $total) : $wpdb->get_var( "SELECT FOUND_ROWS()" );
					$total_pages = ceil($total/$events_per_page);

					$page_links = paginate_links(
									array(
										'format'       	=> '?events_page=%#%',
										'total'        	=> $total_pages,
										'current'      	=> $current_page,
										'show_all'     	=> True,
										'add_args'      => False
									)
								);
				}

				if(
					isset($atts['layout']) &&
					$atts['layout'] == 'table' &&
					(
						!empty($atts['table_fields']) ||
						!empty($content)
					)
				)
				{

                    if(!wp_script_is('datatables'))
                    {
                        wp_enqueue_style( 'cpcff_datatable_css', plugins_url('/vendors/datatables/datatables.min.css', CP_CALCULATEDFIELDSF_MAIN_FILE_PATH), array(), CP_CALCULATEDFIELDSF_VERSION );
                        wp_enqueue_script( 'cpcff_datatable_js', plugins_url('/vendors/datatables/datatables.min.js', CP_CALCULATEDFIELDSF_MAIN_FILE_PATH), array(), CP_CALCULATEDFIELDSF_VERSION );
                    }

                    $table_id = uniqid('cff_events_');

					$lang = '';
					if(!empty($atts['table_language_url']))
					{
						$lang =  sanitize_text_field($atts['table_language_url']);
						$lang =  '{"language":{"url": "'.esc_js($lang).'"}}';
					}

                    $output .= '<script>jQuery(function(){jQuery("#'.$table_id.'").DataTable('.$lang.');});</script>';
					$output .= '<table id="'.$table_id.'" class="display" style="width:100%">';

					if(empty($atts['table_head'])) $atts['table_head'] = array();
					else{
						$atts['table_head'] = sanitize_text_field($atts['table_head']);
						$atts['table_head'] = explode(',', $atts['table_head']);
					}

					$thead = '';
					$tbody = '';

					if ( ! empty( $atts['table_fields'] ) ) {
						$atts['table_fields'] = sanitize_text_field($atts['table_fields']);
						$atts['table_fields'] = preg_replace('/\s+/', '', $atts['table_fields']);
						$atts['table_fields'] = explode(',', $atts['table_fields']);

						foreach($atts['table_fields'] as $index => $field)
						{
							$th = isset($atts['table_head'][$index]) ? trim($atts['table_head'][$index]) : $field;
							$thead .= '<th>'.esc_html($th).'</th>';
							$field  = '<%'.$field.(preg_match('/^fieldname/', $field) ? '_value' : '').'%>';
							$tbody .= '<td>'.$field.'</td>';
						}
					} else {
						foreach($atts['table_head'] as $column)
						{
							$thead .= '<th>'.esc_html($column).'</th>';
						}
						$tbody = $content;
					}

					// THEADER
					$output .= '<thead>';
					$output .= '<tr>'.$thead.
                    (!empty($atts['view_details']) ? '<th data-orderable="false" style="width:100px;"></th>' : '').
                    '</tr>';
					$output .= '</thead>';

					// TBODY
					$output .= '<tbody>';

					unset($atts['fields']);

					foreach($submissions as $submission)
					{
						$output.='<tr>'.$this->form_result_shortcode($atts, $tbody, $submission->id).
                        (!empty($atts['view_details']) ?
                        '<td style="width:100px;white-stace:nowrap;">
                            <div style="text-align:center;"><a href="javascript:void(0);" onclick="cff_open_details(this);">'.__('[details]', 'calculated-fields-form').'</a></div>
                            <div class="cff_submission_details" style="display:none;">
                                <div><span class="cff_submission_details_close" onclick="cff_close_details(this);"></span></div>
                                <div class="cff_submission_details_content">'.
                                    $this->form_result_shortcode($atts, !empty($content) && !empty($atts['table_fields']) ? $content : '<%INFO%>', $submission->id).
                                '</div>
                            </div>
                        </td>' : '').
                        '</tr>';
					}
					$output .= '</tbody>';
					$output .= '</table>';
                    if(!empty($atts['view_details']))
                    {
                        $output .= '<script>
                        function cff_open_details(e){
                            var $ = jQuery, e = $(e);
                            $(".cff_submission_details").removeClass("cff_submission_details_active");
                            e.closest("td").find(".cff_submission_details").addClass("cff_submission_details_active");
                        }
                        function cff_close_details(e){
                            jQuery(e).closest(".cff_submission_details").removeClass("cff_submission_details_active");
                        }
                        </script>
                        <style>
                        .cff_submission_details_active{display:block !important;position:fixed;background:white;width:90%;max-width:650px;z-index:9999;left:50%;top:50%;transform: translate(-50%, -50%);padding:0 20px 20px 20px;max-height:440px;-webkit-box-shadow: 0px 0px 5px 0px rgba(50, 50, 50, 0.5);-moz-box-shadow:    0px 0px 5px 0px rgba(50, 50, 50, 0.5);box-shadow:0px 0px 5px 0px rgba(50, 50, 50, 0.5);}
                        .cff_submission_details_content{overflow-y: auto;overflow-x:auto;max-height: 350px;}
                        .cff_submission_details>div:first-child{text-align:right;}
                        .cff_submission_details_close{cursor:pointer;}
                        .cff_submission_details_close::before{content:"X"; font-family:Arial, Helvetica, sans-serif;;}
                        </style>';
                    }
				}
				else
				{
					foreach($submissions as $submission)
					{
						$output.=$this->form_result_shortcode($atts, $content, $submission->id);
					}
				}
			}

			return $page_links.$output.$page_links;
		} // End form_result_list_shortcode

		/**
		 * Creates a javascript variable, from: Post, Get, Session or Cookie or directly.
		 *
		 * If the webpage is visited from a crawler or search engine spider, the shortcode is replaced by an empty text.
		 *
		 * @since 1.0.175
		 * @param array $atts includes the records:
		 *				- name, the variable's name.
		 *				- value, to create a variable splicitly with the value passed as attribute.
		 *				- from, identifies the variable source (POST, GET, SESSION or COOKIE), it is optional.
		 *				- default_value, used in combination with the from attribute to populate the variable
		 *								 with the default value of the source does not exist.
		 *
		 * @return string <script> tag with the variable's definition.
		 */
		public function create_variable_shortcode( $atts )
		{
			if(
				!CPCFF_AUXILIARY::is_crawler() && // Checks for crawlers or search engine spiders
				!empty($atts[ 'name' ]) &&
				($var = trim($atts[ 'name' ])) != ''
			)
			{
				if( isset( $atts[ 'value' ] ) )
				{
					$value = json_encode( $atts[ 'value' ] );
				}
				else
				{
					$from = '_';
					if( isset($atts['from'])) $from .= strtoupper(trim($atts['from']));
					if( in_array( $from, array( '_POST', '_GET', '_SESSION', '_COOKIE' ) ) )
					{
						if( isset( $GLOBALS[ $from ][ $var ] ) ) $value = json_encode($GLOBALS[ $from ][ $var ]);
						elseif( isset( $atts[ 'default_value' ] ) ) $value = json_encode($atts[ 'default_value' ]);
					}
					else
					{
						if( isset( $_POST[ $var ] ) ) 				$value = json_encode($_POST[ $var ]);
						elseif( isset( $_GET[ $var ] ) ) 			$value = json_encode($_GET[ $var ]);
						elseif( isset( $_SESSION[ $var ] ) )		$value = json_encode($_SESSION[ $var ]);
						elseif( isset( $_COOKIE[ $var ] ) ) 		$value = json_encode($_COOKIE[ $var ]);
						elseif( isset( $atts[ 'default_value' ] ) ) $value = json_encode($atts[ 'default_value' ]);
					}
				}
				if(isset( $value ))
				{
					return '
					<script>
						try{
						window["'.esc_js($var).'"]='.$value.';
						}catch( err ){}
					</script>
					';
				}
			}
			return '';
		} // End create_variable_shortcode

        /**
         * Return the list of categories associted with the forms
         */
        public function get_categories($html = '', &$current = NULL)
        {
            global $wpdb;
            $categories = $wpdb->get_results('SELECT DISTINCT category FROM '.$wpdb->prefix.CP_CALCULATEDFIELDSF_FORMS_TABLE.' WHERE category IS NOT NULL AND category <> ""', ARRAY_A);

            if(empty($html)) return $categories;

            $output = '';
            $flag = false;

            if(!empty($categories))
            {
                foreach($categories as $category)
                {
                    $selected = '';

                    if($current === $category['category'])
                    {
                        $selected = 'SELECTED';
                        $flag = true;
                    }

                    if($html == 'SELECT')
                    {
                        $output .= '<option value="'.esc_attr($category['category']).'" '.$selected.' >'.esc_html($category['category']).'</option>';
                    }
                    else // DATALIST
                    {
                        $output .= '<option value="'.esc_attr($category['category']).'">';
                    }
                }
            }

            if(!$flag) $current = '';

            return $output;
        } // End get_categories

		/**
		 * Returns an instance of the active form
		 *
		 * If there is not an active form generates the instance.
		 *
		 * @since 1.0.179
		 * @return object
		 */
		public function get_form( $id )
		{
			if(!isset($this->_forms[$id]))
			{
				$this->_forms[$id] = new CPCFF_FORM($id);
			}
			return $this->_forms[$id];
		} // End get_active_form

		/**
		 * Creates a new form calling the static method CPCFF_FORM::create_default
		 *
		 * @since 1.0.179
		 *
		 * @param string $form_name, the name of form.
		 * @return mixed, an instance of the created form or false.
		 */
		public function create_form($form_name, $category_name = '', $form_template = 0)
		{
			$form = CPCFF_FORM::create_default($form_name, $category_name, $form_template);
			if($form) $this->_forms[$form->get_id()] = $form;
			return $form;
		} // End create_form

		/**
		 * Deletes the form.
		 * The methods throw the cpcff_delete_form hook after delete the form.
		 *
		 * @since 1.0.179
		 * @param integer $id, the form's id.
		 * @return mixed, the number of delete rows or false.
		 */
		public function delete_form( $id )
		{
			$deleted = $this->get_form($id)->delete_form();
			if($deleted)
			{
				do_action( 'cpcff_delete_form', $id);
				unset( $this->_forms[$id]);
			}
			return $deleted;
		} // End delete_form

		/**
		 * Clones a form.
		 *
		 * @since 1.0.179
		 * @param integer $id, the form's id.
		 * @return mixed, an instance of cloned form or false.
		 */
		public function clone_form($id)
		{
			if(!isset($this->_forms[$id])) $this->_forms[$id] = new CPCFF_FORM($id);
			$cloned_form = $this->_forms[$id]->clone_form();
			if($cloned_form)
			{
				/**
				 * Passes as parameter the original form's id, and the new form's id
				 */
				do_action( 'cpcff_clone_form', $id, $cloned_form->get_id());
			}
			return $cloned_form;
		} // End clone_form

		public function send_mails($submission_id, $payer_email = '')
		{
			require_once CP_CALCULATEDFIELDSF_BASE_PATH.'/inc/cpcff_mail.inc.php';
			if(empty($this->mail_obj)) $this->mail_obj = new CPCFF_MAIL();
			$this->mail_obj->send_notification_email($submission_id);
			$this->mail_obj->send_confirmation_email($submission_id, $payer_email);
		} // End get_email_sender

		/*********************************** PRIVATE METHODS  ********************************************/

		/**
		 * Defines the activativation/deactivation hooks, and new blog hook.
		 *
		 * Requires the cpcff_install_uninstall.inc.php file with the activate/deactivate code, and the code to run with new blogs.
		 *
		 * @sinze 1.0.171
		 * @return void.
		 */
		private function _activate_deactivate()
		{
			require_once CP_CALCULATEDFIELDSF_BASE_PATH.'/inc/cpcff_install_uninstall.inc.php';
			register_activation_hook(CP_CALCULATEDFIELDSF_MAIN_FILE_PATH,array('CPCFF_INSTALLER','install'));
			register_deactivation_hook(CP_CALCULATEDFIELDSF_MAIN_FILE_PATH,array('CPCFF_INSTALLER','uninstall'));
			add_action('wpmu_new_blog', array('CPCFF_INSTALLER', 'new_blog'), 10, 6);
		} // End _activate_deactivate

		/**
		 * Loads the language file.
		 *
		 * Loads the language file associated to the plugin, and creates the textdomain.
		 *
		 * @return void.
		 */
		private function _textdomain()
		{
			load_plugin_textdomain( 'calculated-fields-form', FALSE, dirname( CP_CALCULATEDFIELDSF_BASE_NAME ) . '/languages/' );
		} // End _textdomain

		/**
		 * Loads the controls scripts.
		 *
		 * Checks if there is defined the "cp_cff_resources" parameter, and loads the public or admin scripsts for the controls.
		 * If the scripsts are loaded the plugin exits the PHP execution.
		 *
		 * @return void.
		 */
		private function _load_controls_scrips()
		{
			if( isset( $_REQUEST[ 'cp_cff_resources' ] ) )
			{
				if(!defined('WP_DEBUG') || true != WP_DEBUG)
				{
					error_reporting(E_ERROR|E_PARSE);
				}
				// Set the corresponding header
				if(!headers_sent())
				{
					header("Content-type: application/javascript");
				}

				if(!$this->_is_admin || $_REQUEST[ 'cp_cff_resources' ] == 'public')
				{
					require_once CP_CALCULATEDFIELDSF_BASE_PATH.'/js/fbuilder-loader-public.php';
				}
				else
				{
					require_once CP_CALCULATEDFIELDSF_BASE_PATH.'/js/fbuilder-loader-admin.php';
				}
				remove_all_actions('shutdown');
				exit;
			}
		} // End _load_controls_scrips

		/**
		 * Defines the shortcodes used by the plugin's code:
		 *
		 * - CP_CALCULATED_FIELDS
		 * - CP_CALCULATED_FIELDS_RESULT
		 * - CP_CALCULATED_FIELDS_VAR
		 *
		 * @return void.
		 */
		private function _define_shortcodes()
		{
			add_shortcode( 'CP_CALCULATED_FIELDS', array($this,'public_form') );
			add_shortcode( 'CP_CALCULATED_FIELDS_RESULT', array($this,'form_result_shortcode') );
			add_shortcode( 'CP_CALCULATED_FIELDS_RESULT_LIST', array($this,'form_result_list_shortcode') );
			add_shortcode( 'CP_CALCULATED_FIELDS_VAR', array($this,'create_variable_shortcode') );
		} // End _define_shortcodes

		/**
		 * Returns a JSON object with the configuration object.
		 *
		 * Uses the global variable $cpcff_default_texts_array, defined in the "config/cpcff_config.cfg.php"
		 *
		 * @sinze 1.0.171
		 * @param int $formid the form's id.
		 * @return string $json
		 */
		private function _get_form_configuration( $formid )
		{
			global $cpcff_default_texts_array;
			$form_obj = $this->get_form($formid);
			$previous_label = $form_obj->get_option('vs_text_previousbtn', 'Previous');
			$previous_label = ( $previous_label=='' ? 'Previous' : $previous_label );
			$next_label = $form_obj->get_option('vs_text_nextbtn', 'Next');
			$next_label = ( $next_label == '' ? 'Next' : $next_label );

			$cpcff_texts_array = $form_obj->get_option('vs_all_texts', $cpcff_default_texts_array);
			$cpcff_texts_array = CPCFF_AUXILIARY::array_replace_recursive(
				$cpcff_default_texts_array,
				( is_string( $cpcff_texts_array ) && is_array( unserialize( $cpcff_texts_array ) ) )
					? unserialize( $cpcff_texts_array )
					: ( ( is_array( $cpcff_texts_array ) ) ? $cpcff_texts_array : array() )
			);

			$obj = array(
					"pub"=>true,
					"identifier"=>'_'.self::$form_counter,
					"messages"=> array(
						"required" => $form_obj->get_option('vs_text_is_required', CP_CALCULATEDFIELDSF_DEFAULT_vs_text_is_required),
						"email" => $form_obj->get_option('vs_text_is_email', CP_CALCULATEDFIELDSF_DEFAULT_vs_text_is_email),
						"datemmddyyyy" => $form_obj->get_option('vs_text_datemmddyyyy', CP_CALCULATEDFIELDSF_DEFAULT_vs_text_datemmddyyyy),
						"dateddmmyyyy" => $form_obj->get_option('vs_text_dateddmmyyyy', CP_CALCULATEDFIELDSF_DEFAULT_vs_text_dateddmmyyyy),
						"number" => $form_obj->get_option('vs_text_number', CP_CALCULATEDFIELDSF_DEFAULT_vs_text_number),
						"digits" => $form_obj->get_option('vs_text_digits', CP_CALCULATEDFIELDSF_DEFAULT_vs_text_digits),
						"max" => $form_obj->get_option('vs_text_max', CP_CALCULATEDFIELDSF_DEFAULT_vs_text_max),
						"min" => $form_obj->get_option('vs_text_min', CP_CALCULATEDFIELDSF_DEFAULT_vs_text_min),
						"previous" => $previous_label,
						"next" => $next_label,
						"pageof" => $cpcff_texts_array[ 'page_of_text' ][ 'text' ],
						"discount" => $cpcff_texts_array[ 'discount_text' ][ 'text' ],
						"audio_tutorial" => $cpcff_texts_array[ 'audio_tutorial_text' ][ 'text' ],
						"minlength" => $cpcff_texts_array[ 'errors' ][ 'minlength' ][ 'text' ],
						"maxlength" => $cpcff_texts_array[ 'errors' ][ 'maxlength' ][ 'text' ],
						"equalTo" => $cpcff_texts_array[ 'errors' ][ 'equalTo' ][ 'text' ],
						"accept" => $cpcff_texts_array[ 'errors' ][ 'accept' ][ 'text' ],
						"upload_size" => $cpcff_texts_array[ 'errors' ][ 'upload_size' ][ 'text' ],
						"phone" => $cpcff_texts_array[ 'errors' ][ 'phone' ][ 'text' ],
						"currency" => $cpcff_texts_array[ 'errors' ][ 'currency' ][ 'text' ]
					)
				);
				return json_encode( $obj );
		} // End _get_form_configuration

		/**
		 * Loads the javascript and style files used by the public forms.
		 *
		 * Checks if the plugin was configured for loading HTML tags directly, or to use the WordPress functions.
		 *
		 * @since 1.0.171
		 * @param int $formid the form's id.
		 * @return void.
		 */
		private function _public_resources( $formid )
		{
			if(
                get_option( 'CP_CALCULATEDFIELDSF_USE_CACHE', CP_CALCULATEDFIELDSF_USE_CACHE ) &&
                file_exists( CP_CALCULATEDFIELDSF_BASE_PATH.'/js/cache/all.js' )
            )
            {
                $public_js_path = plugins_url('/js/cache/all.js', CP_CALCULATEDFIELDSF_MAIN_FILE_PATH);
            }

            if(empty($public_js_path))
            {
                global $cff_backend_script_generator, $cff_script_generator_min;

                $cff_backend_script_generator = 1;
                $cff_script_generator_min = get_option( 'CP_CALCULATEDFIELDSF_USE_CACHE', CP_CALCULATEDFIELDSF_USE_CACHE );
                include_once CP_CALCULATEDFIELDSF_BASE_PATH.'/js/fbuilder-loader-public.php';
            }

            if(
                file_exists( CP_CALCULATEDFIELDSF_BASE_PATH.'/js/cache/all.js' )
            )
            {
                $public_js_path = plugins_url('/js/cache/all.js', CP_CALCULATEDFIELDSF_MAIN_FILE_PATH);
            }
            else
            {
                $public_js_path = CPCFF_AUXILIARY::wp_current_url().((strpos(CPCFF_AUXILIARY::wp_current_url(),'?') === false) ? '?' : '&').'cp_cff_resources=public&min='.get_option('CP_CALCULATEDFIELDSF_USE_CACHE', CP_CALCULATEDFIELDSF_USE_CACHE);
            }

			$config_json = $this->_get_form_configuration($formid);

			if ($GLOBALS['CP_CALCULATEDFIELDSF_DEFAULT_DEFER_SCRIPTS_LOADING'])
			{
				wp_enqueue_script( "jquery" );
				wp_enqueue_script( "jquery-ui-core" );
				wp_enqueue_script( "jquery-ui-button" );
				wp_enqueue_script( "jquery-ui-widget" );
				wp_enqueue_script( "jquery-ui-position" );
				wp_enqueue_script( "jquery-ui-tooltip" );
				wp_enqueue_script( "jquery-ui-datepicker" );
				wp_enqueue_script( "jquery-ui-slider" );

				wp_deregister_script('query-stringify');
				wp_register_script('query-stringify', plugins_url('/vendors/jQuery.stringify.js', CP_CALCULATEDFIELDSF_MAIN_FILE_PATH), array('jquery'), 'pro');

				wp_deregister_script('cp_calculatedfieldsf_validate_script');
				wp_register_script('cp_calculatedfieldsf_validate_script', plugins_url('/vendors/jquery.validate.js', CP_CALCULATEDFIELDSF_MAIN_FILE_PATH), array('jquery'), 'pro');
				wp_enqueue_script( 'cp_calculatedfieldsf_builder_script', $public_js_path, array("jquery","jquery-ui-core","jquery-ui-button","jquery-ui-widget","jquery-ui-position","jquery-ui-tooltip","query-stringify","cp_calculatedfieldsf_validate_script", "jquery-ui-datepicker", "jquery-ui-slider"), CP_CALCULATEDFIELDSF_VERSION, true );

				wp_localize_script('cp_calculatedfieldsf_builder_script', 'cp_calculatedfieldsf_fbuilder_config_'.self::$form_counter, array('obj' => $config_json));
			}
			else
			{
				// This code won't be used in most cases. This code is for preventing problems in wrong WP themes and conflicts with third party plugins.
				if( !$this->_are_resources_loaded ) // Load the resources only one time
				{
					global $wp_version;
					$this->_are_resources_loaded = true; // Resources loaded

					$includes_url = includes_url();

					// Used for compatibility with old versions of WordPress
					$prefix_ui = (@file_exists(CP_CALCULATEDFIELDSF_BASE_PATH.'/../../../wp-includes/js/jquery/ui/jquery.ui.core.min.js')) ? 'jquery.ui.' : '';

					if(!wp_script_is('jquery', 'done'))
						print '<script type="text/javascript" src="'.$includes_url.'js/jquery/jquery.js"></script>';
					if(!wp_script_is('jquery-ui-core', 'done'))
						print '<script type="text/javascript" src="'.$includes_url.'js/jquery/ui/'.$prefix_ui.'core.min.js"></script>';
					if(!wp_script_is('jquery-ui-datepicker', 'done'))
						print '<script type="text/javascript" src="'.$includes_url.'js/jquery/ui/'.$prefix_ui.'datepicker.min.js"></script>';

					if(version_compare($wp_version,'5.5.4', '<'))
					{
						if(!wp_script_is('jquery-ui-widget', 'done'))
							print '<script type="text/javascript" src="'.$includes_url.'js/jquery/ui/'.$prefix_ui.'widget.min.js"></script>';
						if(!wp_script_is('jquery-ui-position', 'done'))
							print '<script type="text/javascript" src="'.$includes_url.'js/jquery/ui/'.$prefix_ui.'position.min.js"></script>';
					}

					if(!wp_script_is('jquery-ui-tooltip', 'done'))
						print '<script type="text/javascript" src="'.$includes_url.'js/jquery/ui/'.$prefix_ui.'tooltip.min.js"></script>';
					if(!wp_script_is('jquery-ui-mouse', 'done'))
						print '<script type="text/javascript" src="'.$includes_url.'js/jquery/ui/'.$prefix_ui.'mouse.min.js"></script>';
					if(!wp_script_is('jquery-ui-slider', 'done'))
						print '<script type="text/javascript" src="'.$includes_url.'js/jquery/ui/'.$prefix_ui.'slider.min.js"></script>';
				?>
					<script type='text/javascript'> if( typeof fbuilderjQuery == 'undefined' && typeof jQuery != 'undefined' ) fbuilderjQuery = jQuery.noConflict( );</script>
					<script type='text/javascript' src='<?php echo plugins_url('vendors/jquery.validate.js', CP_CALCULATEDFIELDSF_MAIN_FILE_PATH); ?>'></script>
					<script type='text/javascript' src='<?php echo plugins_url('vendors/jQuery.stringify.js', CP_CALCULATEDFIELDSF_MAIN_FILE_PATH); ?>'></script>
					<script type='text/javascript' src='<?php echo $public_js_path.(( strpos( $public_js_path, '?' ) == false ) ? '?' : '&' ).'ver='.CP_CALCULATEDFIELDSF_VERSION; ?>'></script>
				<?php
				}
				?>
				<pre style="display:none !important;"><script type='text/javascript'><?php
					print 'cp_calculatedfieldsf_fbuilder_config_'.self::$form_counter.'={"obj":'.$config_json.'};';
				?></script></pre>
				<?php
			}
		} // End _public_resources

		/** TROUBLESHOOTS SECTION **/
		public function compatibility_warnings()
		{
			require_once CP_CALCULATEDFIELDSF_BASE_PATH.'/inc/cpcff_compatibility.inc.php';
			return CPCFF_COMPATIBILITY::warnings();
		} // End compatibility_warnings

		private function troubleshoots()
		{
			if(!$this->_is_admin)
			{
				if(get_option('CP_CALCULATEDFIELDSF_OPTIMIZATION_PLUGIN', CP_CALCULATEDFIELDSF_OPTIMIZATION_PLUGIN)*1)
                {
                    // Solves a conflict caused by the "Speed Booster Pack" plugin
                    add_filter('option_sbp_settings', 'CPCFF_MAIN::speed_booster_pack_troubleshoot');

                    // Solves a conflict caused by the "Autoptimize" plugin
                    if(class_exists('autoptimizeOptionWrapper'))
                    {
                        $GLOBALS['CP_CALCULATEDFIELDSF_DEFAULT_DEFER_SCRIPTS_LOADING'] = true;
                        add_filter('cpcff_pre_form', function($atts){
                            add_filter('autoptimize_js_include_inline', function($p){return false;});
                            add_filter('autoptimize_filter_js_noptimize', function($p1, $p2){return true;}, 10, 2);
                            add_filter('autoptimize_filter_html_noptimize', function($p1, $p2){return true;}, 10, 2);
                            return $atts;
                        });
                    }

                    // Solves conflicts with "LiteSpeed Cache" plugin
                    if(function_exists('run_litespeed_cache'))
                    {
                        add_action('the_post', 'CPCFF_MAIN::litespeed_control_set_nocache');
                    }

                    // Solves a conflict caused by the "WP Rocket" plugin
                    add_filter( 'rocket_exclude_js', 'CPCFF_MAIN::rocket_exclude_js' );
                    add_filter( 'rocket_exclude_defer_js', 'CPCFF_MAIN::rocket_exclude_js' );
                    add_filter( 'rocket_delay_js_exclusions', 'CPCFF_MAIN::rocket_exclude_js' );

                    // Some "WP Rocket" functions can be use with "WP-Optimize"
                    add_filter( 'wp-optimize-minify-blacklist', 'CPCFF_MAIN::rocket_exclude_js' );
                    add_filter( 'wp-optimize-minify-default-exclusions', 'CPCFF_MAIN::rocket_exclude_js' );
                }
                add_filter( 'rocket_excluded_inline_js_content', 'CPCFF_MAIN::rocket_exclude_inline_js' );
                add_filter( 'rocket_defer_inline_exclusions', 'CPCFF_MAIN::rocket_exclude_inline_js' );
                add_filter( 'rocket_delay_js_exclusions', 'CPCFF_MAIN::rocket_exclude_inline_js' );

				// For Breeze conflicts
				if ( defined( 'BREEZE_VERSION' ) ) {
					add_filter( 'breeze_filter_html_before_minify', 'CPCFF_MAIN::breeze_check_content', 10 );
					add_filter( 'breeze_html_after_minify', 'CPCFF_MAIN::breeze_return_content', 10 );
				}
            }
		} // End troubleshoots

        public static function litespeed_control_set_nocache(&$post)
        {
            try
            {
                if(
                    is_object($post) &&
                    isset($post->post_content) &&
                    stripos($post->post_content, '[CP_CALCULATED_FIELDS') !== false
                ) do_action( 'litespeed_control_set_nocache', 'nocache CFF Form' );
            }
            catch(Exception $err){error_log($err->getMessage());}
            return $post;
        } // End litespeed_control_set_nocache

		public static function speed_booster_pack_troubleshoot($option)
		{
			if(is_array($option) && isset($option['jquery_to_footer'])) unset($option['jquery_to_footer']);
			return $option;
		} // End speed_booster_pack_troubleshoot

		public static function rocket_exclude_js($excluded_js)
		{
			$excluded_js[] = '/jquery.js';
			$excluded_js[] = '/jquery.min.js';
            $excluded_js[] = '/jquery/';
			$excluded_js[] = '/calculated-fields-form/';

			$excluded_js[] = '/jquery/(.*)';
			$excluded_js[] = '(.*)/jquery.js';
			$excluded_js[] = '(.*)/jquery.min.js';
			$excluded_js[] = '(.*)/jquery/(.*)';
			$excluded_js[] = '(.*)/calculated-fields-form/(.*)';
			return $excluded_js;
		} // End rocket_exclude_js

        public static function rocket_exclude_inline_js($excluded_js = [])
		{
			$excluded_js[] = 'form_structure_';
			$excluded_js[] = 'fbuilderjQuery';
			$excluded_js[] = 'fbuilderjQuery(.*)';
			$excluded_js[] = '(.*)fbuilderjQuery(.*)';
			$excluded_js[] = 'doValidate_';
			$excluded_js[] = 'cpcff_default';
			$excluded_js[] = 'cp_calculatedfieldsf_fbuilder_config_';
			$excluded_js[] = 'form_structure(.*)';
			$excluded_js[] = 'doValidate(.*)';
			$excluded_js[] = 'cp_calculatedfieldsf_fbuilder_config(.*)';
			return $excluded_js;
		} // End rocket_exclude_inline_js

		public static function breeze_check_content( $content ) {
			if ( strpos( $content, 'form_structure_' ) !== false || strpos( $content, 'cp_calculatedfieldsf_fbuilder_config_' ) !== false ) {
				global $cff_breeze_content_bk;
				$cff_breeze_content_bk = $content;
			}
			return $content;
		} // End breeze_check_content

		public static function breeze_return_content( $content ) {
			global $cff_breeze_content_bk;
			if( ! empty( $cff_breeze_content_bk ) ) {
				$content = $cff_breeze_content_bk;
				unset( $cff_breeze_content_bk );
			}
			return $content;
		} // End breeze_return_content
	} // End CPCFF_MAIN
}