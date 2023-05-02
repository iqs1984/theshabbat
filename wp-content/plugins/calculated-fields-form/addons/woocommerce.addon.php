<?php
/*
....
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPCFF_WooCommerce' ) )
{
    class CPCFF_WooCommerce extends CPCFF_BaseAddon
    {
		static public $category = 'Third Party Plugins';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-woocommerce-20150309";
		protected $name = "CFF - WooCommerce";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#woocommerce-addon';

		/************************ ADDON CODE *****************************/
        /************************ ATTRIBUTES *****************************/

        private $form = array(); // Form data
        private $_resources_loaded = false; // Prevent to load the add-on resources multiple times.
		private $_cpcff_main;

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
            // To exclude from optimizers
            $this->optimizer_file = ['woocommerce.addon'];
            $this->optimizer_inline = ['cpcff_default', 'woocommerce_cpcff_product'];

			$this->_cpcff_main = CPCFF_MAIN::instance();
			$this->description = __("The add-on allows integrate the forms with WooCommerce products", 'calculated-fields-form');

            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;

			// Check if WooCommerce is active in the website
            $active_plugins = (array) get_option( 'active_plugins', array() );

            if ( is_multisite() )
            {
                $active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
            }

            if( !( in_array( 'woocommerce/woocommerce.php', $active_plugins ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins ) ) )
            {
                return;
            }

            // Call the parent construct
            parent::__construct();

            // Load resources, css and js
			add_filter( 'woocommerce_loop_add_to_cart_link', array(&$this,'replacing_add_to_cart_button'), 10, 2 );
			add_action( 'woocommerce_before_single_product', array( &$this, 'enqueue_scripts' ), 10 );
            add_action( 'woocommerce_before_cart', array( &$this, 'enqueue_cart_resources' ), 10 );
            add_action( 'woocommerce_before_checkout_form', array( &$this, 'enqueue_cart_resources' ), 10 );

            // Addon display
            add_action('woocommerce_before_add_to_cart_button', array(&$this, 'display_form'), 10);

			add_filter('woocommerce_is_purchasable', array($this, 'is_purchasable'), 10, 2);

            // Corrects the form options
            add_filter( 'cpcff_get_option', array( &$this, 'get_form_options' ), 10, 3 );

            // Filters for cart actions
			add_filter('woocommerce_add_cart_item_data', array(&$this, 'add_cart_item_data'), 50, 2);
			add_filter('woocommerce_add_cart_item_data', array(&$this, 'add_cart_item_data'), 50, 4);
			add_filter('woocommerce_get_item_data', array(&$this, 'get_cart_item_data'), 50, 2);
			add_filter('woocommerce_get_cart_item_from_session', array(&$this, 'get_cart_item_from_session'), 50, 2);
            add_filter('woocommerce_add_cart_item', array(&$this, 'add_cart_item'), 50, 1);
			add_action('woocommerce_new_order_item', array(&$this, 'add_order_item_meta'), 50, 3);
			// add_filter('woocommerce_order_get_items', array(&$this, 'get_order_items'), 10 );

			// Add to cart with AJAX
			add_filter(
				'cff_no_ajax',
				function( $stop ) {
					if( !empty( $_REQUEST['wc-ajax'] ) ) {
						return false;
					}
					return $stop;
				}
			);

            add_filter('woocommerce_after_order_itemmeta', array(&$this, 'extra_order_item_details'), 10 );
            add_filter('woocommerce_order_status_completed', array(&$this, 'order_status_completed'), 10 );

			add_filter('woocommerce_order_item_meta_start', array(&$this, 'order_item_meta_start'), 10, 4 );
			add_filter('woocommerce_order_item_meta_end', array(&$this, 'order_item_meta_end'), 10, 4 );

			add_filter('woocommerce_before_order_itemmeta', array(&$this, 'order_item_meta_start'), 10, 3 );
			add_filter('woocommerce_after_order_itemmeta', array(&$this, 'order_item_meta_end'), 10, 3 );

            add_filter('woocommerce_order_item_permalink', array(&$this, 'order_item_permalink'), 10, 3 );

            // Filters for the Calculated Fields Form
            add_action( 'cpcff_redirect', array( &$this, 'cpcff_redirect'), 10 );
            add_filter( 'cpcff_check_nonce', array( &$this, 'check_nonce'), 10 );
            add_filter( 'cpcff_send_confirmation_email', array(&$this, 'notification_email'), 10, 3);

			// The init hook
			add_action( 'admin_init', array( &$this, 'init_hook' ), 1 );

            add_filter('kses_allowed_protocols', function($protocols){
                    $protocols[] = 'data';
                    return $protocols;
                });

			if( is_admin() )
			{
				// Delete forms
				add_action( 'cpcff_delete_form', array(&$this, 'delete_form') );
			}

        } // End __construct

        /************************ PRIVATE METHODS *****************************/
        /**
         * Check if the add-on can be applied to the product
         */
        private function apply_addon( $id = false )
        {
            global $post;

            $this->form = array();

            if($id) $post_id = $id;
            elseif(isset($_REQUEST['woocommerce_cpcff_product'])) $post_id = $_REQUEST['woocommerce_cpcff_product'];
            elseif( isset( $post ) ) $post_id = $post->ID;

            if( isset($post_id) && get_post_type($post_id) == 'product')
			{
				$tmp = get_post_meta($post_id, 'woocommerce_cpcff_form', true);
				if(empty($tmp) && empty(get_post_meta($post_id, 'woocommerce_cpcff_exclude_global_form', true))) $tmp = get_option('woocommerce_cpcff_form');
			}
			if(!empty($tmp)) $this->form['id'] = $tmp;

            return !empty($this->form);
        }

		/************************ PUBLIC METHODS  *****************************/

		public function add_cart_item_data( $cart_item_meta, $product_id, $variation_id = 0, $quantity = 0 ) {
			if(
				!isset( $cart_item_meta[ 'cp_cff_form_data' ] ) &&
				( $cp_cff_form_data = CP_SESSION::registered_events() ) !== false &&
                $this->apply_addon($product_id)
			)
            {
                $cart_item_meta[ 'cp_cff_form_data' ] = $cp_cff_form_data[$cp_cff_form_data['latest']];
            }
            return $cart_item_meta;

        } // End add_cart_item_data

        public function get_cart_item_from_session( $cart_item, $values ) {
			if( isset( $values[ 'cp_cff_form_data' ] ) ) {
				$cart_item['cp_cff_form_data'] = $values['cp_cff_form_data'];
                $this->add_cart_item( $cart_item );
			}
			return $cart_item;

		} // End get_cart_item_from_session

		function get_cart_item_data( $values, $cart_item ) {
			// Adjust price if required based in the cpcff_data
			if( isset($cart_item[ 'cp_cff_form_data' ] ) )
            {
                $data_id = $cart_item[ 'cp_cff_form_data' ];
                if( !empty( $data_id ) )
                {
					$submission = CPCFF_SUBMISSIONS::get($data_id);
					if(!empty($submission))
					{
						$data = $submission->data;
						$activate_summary = $this->_get_post_meta( $cart_item[ 'product_id' ], 'woocommerce_cpcff_activate_summary', true );
						if( !empty( $activate_summary ) )
						{
							$summary_title = $this->_get_post_meta( $cart_item[ 'product_id' ], 'woocommerce_cpcff_summary_title', true );
							$summary = $this->_get_post_meta( $cart_item[ 'product_id' ], 'woocommerce_cpcff_summary', true );

							if(!empty($summary))
							{
								if( empty( $summary_title ) ) $summary_title = '';
								if($summary !== strip_tags($summary)) $summary = str_replace(array("\n","\r"), '', $summary);

								$result = ($this->_cpcff_main->form_result_shortcode( array(), $summary, $data_id));
								$values[] = array( 'name' => ( ( !empty( $summary_title ) ) ? $summary_title : '' ) , 'value' => $result );
							}
						}
						else
						{
							$result = ($this->_cpcff_main->form_result_shortcode( array(), '<%INFO if_not_empty%>', $data_id));
							$values[] = array( 'name' => '' , 'value' => $result );
						}
					}
				}
            }
			CP_SESSION::unset_var( 'cp_cff_form_data' );
			return $values;
        } // End add_cart_item

        //Helper function, used when an item is added to the cart as well as when an item is restored from session.
		function add_cart_item( $cart_item ) {
			if(!empty($_REQUEST['cp_cff_wc']))
			{
				$items = WC()->cart->get_cart();
				if(isset($items[$_REQUEST['cp_cff_wc']]))
				{
					WC()->cart->remove_cart_item($_REQUEST['cp_cff_wc']);
				}
			}

			// Adjust price if required based in the cpcff_data
			if( isset($cart_item[ 'cp_cff_form_data' ] ) )
            {
				// Modify the hyperlink of name product name and thumbnail in the cart page.
				add_filter( 'woocommerce_cart_item_permalink', array( &$this, 'woocommerce_cart_item_permalink' ), 10, 3 );

                $tmp = $this->_get_post_meta( $cart_item[ 'product_id' ], 'woocommerce_cpcff_calculate_price', true );
                if( !empty( $tmp ) )
                {
					$minimum_price = $this->_get_post_meta( $cart_item[ 'product_id' ], 'woocommerce_cpcff_minimum_price', true );
					$title_field = $this->_get_post_meta( $cart_item[ 'product_id' ], 'woocommerce_cpcff_title_field', true );
					$weight_field = $this->_get_post_meta( $cart_item[ 'product_id' ], 'woocommerce_cpcff_weight_field', true );
					$length_field = $this->_get_post_meta( $cart_item[ 'product_id' ], 'woocommerce_cpcff_length_field', true );
					$width_field = $this->_get_post_meta( $cart_item[ 'product_id' ], 'woocommerce_cpcff_width_field', true );
					$height_field = $this->_get_post_meta( $cart_item[ 'product_id' ], 'woocommerce_cpcff_height_field', true );

                    $data_id = $cart_item[ 'cp_cff_form_data' ];
					$submission = CPCFF_SUBMISSIONS::get($data_id);
					if(!empty($submission))
					{
						$paypal_data = $submission->paypal_post;
						$price = preg_replace( '/[^\d\.\,]/', '', $paypal_data[ 'final_price' ] );
						$price = (!empty($minimum_price) && is_numeric($minimum_price)) ? max($price, $minimum_price) : $price;
						$cart_item[ 'data' ]->set_price($price);

						if(
							!empty($title_field)
						)
						{
							$title_field = trim($title_field);
							if(isset($paypal_data[$title_field]))
							{
								$title = $paypal_data[$title_field];
								if(!empty($title))
									$cart_item[ 'data' ]->set_name($title);
							}
						}

						if(
							!empty($weight_field)
						)
						{
							$weight_field = trim($weight_field);
							if(isset($paypal_data[$weight_field]))
							{
								$weight = $paypal_data[$weight_field];
								$weight = preg_replace('/[^\d\.]/','',$weight);
								$cart_item[ 'data' ]->set_weight(floatval(@$weight));
							}
						}

						if(
							!empty($length_field)
						)
						{
							$length_field = trim($length_field);
							if(isset($paypal_data[$length_field]))
							{
								$length = $paypal_data[$length_field];
								$length = preg_replace('/[^\d\.]/','',$length);
								$cart_item[ 'data' ]->set_length(floatval(@$length));
							}
						}

						if(
							!empty($height_field)
						)
						{
							$height_field = trim($height_field);
							if(isset($paypal_data[$height_field]))
							{
								$height = $paypal_data[$height_field];
								$height = preg_replace('/[^\d\.]/','',$height);
								$cart_item[ 'data' ]->set_height(floatval(@$height));
							}
						}

						if(
							!empty($width_field)
						)
						{
							$width_field = trim($width_field);
							if(isset($paypal_data[$width_field]))
							{
								$width = $paypal_data[$width_field];
								$width = preg_replace('/[^\d\.]/','',$width);
								$cart_item[ 'data' ]->set_width(floatval(@$width));
							}
						}

						/** Modifies the prices defined by FANCY PRODUCT DESIGNER **/
						if(isset( $cart_item['fpd_data']) && isset( $cart_item['fpd_data']['fpd_product_price']))
							$cart_item['fpd_data']['fpd_product_price'] = $price;

						/** Modifies the prices defined by WOOCOMMERCE PRODUCT ADD-ONS ULTIMATE **/
						if(isset($cart_item['product_extras']) && isset($cart_item['product_extras']['price_with_extras'])) $cart_item['product_extras']['price_with_extras'] = $price;

						if(isset($cart_item['product_extras']) && isset($cart_item['product_extras']['original_price'])) $cart_item['product_extras']['original_price'] = $price;

						if( property_exists( $cart_item[ 'data' ], 'regular_price') )
							$cart_item[ 'data' ]->regular_price = $price;
						if( method_exists($cart_item[ 'data' ], 'set_regular_price') )
							$cart_item[ 'data' ]->set_regular_price($price);
						if( method_exists($cart_item[ 'data' ], 'set_price') )
							$cart_item[ 'data' ]->set_price($price);
					}
				}
            }
            return $cart_item;

		} // End add_cart_item

		function woocommerce_cart_item_permalink( $permalink, $cart_item, $cart_item_key )
		{
			$add = '';
			if( !empty( $cart_item[ 'cp_cff_form_data' ] ) )
			{
				$add = ((strpos($permalink, '?') === false ) ? '?' : '&' ).'cp_cff_wc='.$cart_item_key;
			}
			return $permalink.$add;
		} // End woocommerce_cart_item_permalink

        /**
         * Avoid redirect the Calculated Fields Form to the thanks page.
         */
        function cpcff_redirect()
        {
			if( isset( $_REQUEST[ 'product' ] ) || isset( $_REQUEST[ 'woocommerce_cpcff_product' ] ) ) return false;
            return true;
        }

        /**
         * Check if send or not the notification email
         */
        function notification_email($send_flag, $submission_obj, $form_obj)
        {
            if(
                isset($_REQUEST['woocommerce_cpcff_product']) &&
                $form_obj->get_option('paypal_notiemails', '0') != '1'
            ) return false;
            return $send_flag;
        } // End notification_email

        public function get_order_items( $data )
		{
			foreach( $data as $k => $d )
			{
				if( isset( $d[ 'item_meta_array' ] ) )
				{
					foreach( $d[ 'item_meta_array' ] as $k1 => $d1 )
					{
						if( $d1->key == __( 'Data' ) )
						{
							$data[ $k ][ 'item_meta_array' ][ $k1 ]->value = strip_tags( preg_replace( '/\\s+\\-\\s+$/', '', str_replace('<br />', ' - ', $d1->value ) ) );
						}
					}
				}
			}

			return $data;
		} // End get_order_items

		public function order_status_completed( $id )
		{
			$order = new WC_Order( $id );
			$items = $order->get_items();
			foreach( $items as $item_id => $item )
			{
				$extra_details = get_post_meta( $item_id, 'woocommerce_cpcff_order_details', true );

				if(
                    !empty( $extra_details ) &&
                    !empty( $extra_details['cff_params'] ) &&
                    !empty( $extra_details['cff_params']['itemnumber'] )
                )
				{
					/**
					 * Action called after process the payment.
					 */
                    try{
                        if(class_exists('CPCFF_SUBMISSIONS') && CPCFF_SUBMISSIONS::get($extra_details['cff_params']['itemnumber']))
                        {
							CPCFF_SUBMISSIONS::update($extra_details['cff_params']['itemnumber'], array('paid'=>1));
							$form_obj = CPCFF_SUBMISSIONS::get_form($extra_details['cff_params']['itemnumber']);
							if($form_obj->get_option('paypal_notiemails', '0') != '1')
                               $this->_cpcff_main->send_mails($extra_details['cff_params']['itemnumber']);
                        }
                    }
                    catch(Exception $err){}
					do_action( 'cpcff_payment_processed', $extra_details[ 'cff_params' ] );
				}
			}

		} // End order_status_completed

		function order_item_meta_start($item_id, $item, $order, $bool = false)
		{
			$extra_details = get_post_meta( $item_id, 'woocommerce_cpcff_order_details', true );
			if(!empty($extra_details))
				add_filter('sanitize_text_field', array(&$this, 'sanitize_text_field'), 10, 2 );
		} // End order_item_meta_start

		function order_item_meta_end($item_id, $item, $order, $bool = false)
		{
			remove_filter('sanitize_text_field', array(&$this, 'sanitize_text_field'), 10, 2 );
		} // End display_item_meta

		function order_item_permalink($permalink, $item, $order)
		{
            $item_id = $item->get_id();
            $extra_details = get_post_meta( $item_id, 'woocommerce_cpcff_order_details', true );
			if(!empty( $extra_details )) $permalink .= (strpos($permalink, '?') === false ? '?' : '&').'cp_cff_wc='.$item_id;
            return $permalink;
		} // End display_item_meta

		function sanitize_text_field($formmated, $plain)
		{
			$allowed_tags = wp_kses_allowed_html( 'post' );
			return wp_kses($plain, $allowed_tags);
		} // End sanitize_text_field

		/**
		 * Includes extra details in the order items
		 */
		public function extra_order_item_details( $item_id )
		{
			$extra_details = get_post_meta( $item_id, 'woocommerce_cpcff_order_details', true );

			if( empty( $extra_details ) || empty( $extra_details[ 'data' ] )) return;
			?>
			<div class="order_data_column">
				<h4><?php _e( 'Extra Details' ); ?></h4>
				<div><?php echo $extra_details[ 'data' ]; ?></div>
			</div>
            <?php
		} // End extra_order_item_details

		public function add_order_item_meta( $item_id, $values, $cart_item_key )
        {
			$metadata = '';
            if(!isset($values->legacy_values)) return;
			$legacy_values = $values->legacy_values;
            if( $this->apply_addon( $legacy_values['product_id'] ) && ! empty( $legacy_values[ 'cp_cff_form_data' ] ) )
            {
				$data_id = $legacy_values[ 'cp_cff_form_data' ];
				$woocommerce_cpcff_order_details = array();
			    $data = CPCFF_SUBMISSIONS::get($data_id);
				if(!empty($data))
				{
					$data = clone $data;
					$dataArr = $data->paypal_post;
                    $dataArr['itemnumber'] = $data_id;
					$woocommerce_cpcff_order_details[ 'cff_params' ] = $dataArr;

					foreach( $dataArr as $fieldname => $value )
					{
						if( strpos( $fieldname, '_url' ) !== false )
						{
							$_fieldname = str_replace( '_url', '', $fieldname );
							$_value     = $dataArr[ $_fieldname ];
							$_values 	= explode( ',', $_value );
							$_replacement = array();

							if( is_array($value) && count( $_values ) == count( $value ) )
							{
								foreach( $_values as $key => $_fileName )
								{
									$_fileName = trim( $_fileName );
									$_replacement[] = '<a href="'.$value[ $key ].'" target="_blank">'.$_fileName.'</a>';
								}
							}
							if( !empty( $_replacement ) )
							{
								$data->data = str_replace( $_value, implode( ', ', $_replacement ) , $data->data );
							}
						}
					}
					$data->data = preg_replace( "/\n+/", "<br />", $data->data );

					// If was defined a summary associated to the product add it as metadata,
					$activate_summary = $this->_get_post_meta( $legacy_values[ 'product_id' ], 'woocommerce_cpcff_activate_summary', true );
					if( !empty( $activate_summary ) )
					{
						$summary = $this->_get_post_meta( $legacy_values[ 'product_id' ], 'woocommerce_cpcff_summary', true );
						if ( ! empty( $summary ) ) {
							if($summary !== strip_tags($summary)) $summary = str_replace(array("\n","\r"), '', $summary);
							$metadata_label = $this->_get_post_meta( $legacy_values[ 'product_id' ], 'woocommerce_cpcff_summary_title', true );
							if(!empty($metadata_label)) $metadata_label = trim($metadata_label);
							$metadata = $this->_cpcff_main->form_result_shortcode( array(), $summary, $data_id);
						}
						$woocommerce_cpcff_order_details['data'] = $data->data;
					}
					else
					{
						$metadata = $data->data;
					}
				}

				add_post_meta( $item_id, 'woocommerce_cpcff_order_details', $woocommerce_cpcff_order_details, true );
				if(!empty($metadata))
					wc_add_order_item_meta( $item_id, __((!empty($metadata_label)) ? $metadata_label : 'Data'), $metadata, true );
            }

        } // End add_order_item_meta


		// Allows to include the form, even if the price of product was left in blank, but the product has a form assigned.
		public function is_purchasable($purchasable, $product)
		{
			if(!$purchasable)
			{
				$purchasable = $product->exists() && ('publish' === $product->get_status() || current_user_can('edit_post', $product->get_id())) && $this->apply_addon($product->get_id());
			}

			return $purchasable;
		} // End is_purchasable

		function replacing_add_to_cart_button( $button, $product  ) {
			$read_more_button = $this->_get_post_meta( $product->get_id(), 'woocommerce_cpcff_read_more', true );
			if(!empty($read_more_button))
			{
				$button_text = __("View Product", "woocommerce");
				$button = '<a class="button" href="' . $product->get_permalink() . '">' . $button_text . '</a>';
			}
			return $button;
		}

        /**
         * Display the form associated to the product
         */
        public function display_form()
        {
            global $post, $woocommerce;

            if ( $this->apply_addon() ) {
				$this->enqueue_scripts();
				// Product id
				$id = $post->ID;

				if(class_exists('WC_Product'))
				{
					$product = new WC_Product($id);
				}
				elseif(function_exists('get_product'))
				{
					$product = get_product($id);
				}
				if(empty($product)) return;

				// Initialize attributes like: width, height, length and weight
				$title_field = $this->_get_post_meta( $id, 'woocommerce_cpcff_title_field', true );
				$weight_field = $this->_get_post_meta( $id, 'woocommerce_cpcff_weight_field', true );
				$length_field = $this->_get_post_meta( $id, 'woocommerce_cpcff_length_field', true );
				$width_field  = $this->_get_post_meta( $id, 'woocommerce_cpcff_width_field', true );
				$height_field = $this->_get_post_meta( $id, 'woocommerce_cpcff_height_field', true );
				$quantity_field = $this->_get_post_meta( $id, 'woocommerce_cpcff_quantity_field', true );

				$init_form = array();

				if(preg_match('/^fieldname\d+$/', $weight_field) && $product->has_weight())
					$init_form[$weight_field] = $product->get_weight();

				if($product->has_dimensions())
				{
					if(preg_match('/^fieldname\d+$/', $length_field))
						$init_form[$length_field] = $product->get_length();

					if(preg_match('/^fieldname\d+$/', $height_field))
						$init_form[$height_field] = $product->get_height();

					if(preg_match('/^fieldname\d+$/', $width_field))
						$init_form[$width_field] = $product->get_width();
				}

                $form_content = $this->_cpcff_main->public_form( $this->form );
				$main_class = get_class($this->_cpcff_main);
				$pform_psequence = $main_class::$form_counter;

				// Initialize form fields
				if(
					(
						($cp_cff_form_data = CP_SESSION::registered_events()) !== false &&
						!empty( $_REQUEST[ 'cp_calculatedfieldsf_id' ] ) &&
						!empty( $_REQUEST[ 'cp_calculatedfieldsf_pform_psequence' ] )
					) ||
					(
						!empty( $_REQUEST[ 'cp_cff_wc' ] )
					)
				)
				{
					if( !empty( $_REQUEST[ 'cp_calculatedfieldsf_pform_psequence' ] ) )
						$pform_psequence = sanitize_text_field($_REQUEST['cp_calculatedfieldsf_pform_psequence']);

					if(!empty($_REQUEST['cp_cff_wc']))
					{
                        $cp_cff_wc = sanitize_text_field($_REQUEST['cp_cff_wc']);

                        // Get from cart
						$cart = WC()->cart->get_cart();
						if(
                            !empty($cart[$cp_cff_wc]) &&
                            !empty($cart[$cp_cff_wc]['cp_cff_form_data'])
                        )
						{
							$result = CPCFF_SUBMISSIONS::get($cart[$cp_cff_wc]['cp_cff_form_data']);
							$quantity = $cart[$cp_cff_wc]['quantity'];
						}
                        elseif(function_exists('wc_get_order_id_by_order_item_id'))
                        {
                            // Get from order
                            $order_id = wc_get_order_id_by_order_item_id($cp_cff_wc);
                            if($order_id)
                            {
                                $order = wc_get_order($order_id);
                                if($order)
                                {
                                    $customer_id = $order->get_customer_id();
                                    if($customer_id && $customer_id == get_current_user_id())
                                    {
                                        $extra_details = get_post_meta($cp_cff_wc, 'woocommerce_cpcff_order_details', true);

                                        if(
                                            !empty($extra_details) &&
                                            !empty($extra_details['cff_params']) &&
                                            !empty($extra_details['cff_params']['itemnumber'])
                                        )
                                        {
                                            $result = CPCFF_SUBMISSIONS::get($extra_details['cff_params']['itemnumber']);
                                            $quantity = $order->get_item($cp_cff_wc)->get_quantity();
                                        }
                                    }
                                }
                            }
                        }
					}

					if(
						empty($result) &&
						$cp_cff_form_data !== false &&
						!empty( $_REQUEST[ 'cp_calculatedfieldsf_id' ] )
					)
					{
						global $wpdb;
						$rows = CPCFF_SUBMISSIONS::populate(
							$wpdb->prepare( "SELECT * FROM ".CP_CALCULATEDFIELDSF_POSTS_TABLE_NAME." AS form_data WHERE form_data.id=%d AND form_data.formid=%d", $cp_cff_form_data[$cp_cff_form_data['latest']],  $_REQUEST[ 'cp_calculatedfieldsf_id' ] )
						);
						if(!empty($rows)) $result = CPCFF_SUBMISSIONS::get($cp_cff_form_data[$cp_cff_form_data['latest']]);
					}

					if( !empty( $result ) )
					{
						$submitted_data = (is_string( $result->paypal_post )) ? unserialize( $result->paypal_post ) : $result->paypal_post;
						foreach( $submitted_data as $key => $val )
						{
							/* if( preg_match( '/^fieldname\d+(_url(s)?)?$/', $key ) ) */
							if( preg_match( '/^fieldname\d+$/', $key ) )
							{
								$init_form[ $key ] = $val;
							}
						}
					}
				}
				if(!empty($init_form))
				{
				?>
					<script>
						cpcff_default  = ( typeof cpcff_default != 'undefined' ) ? cpcff_default : {};
						cpcff_default[ <?php
							echo preg_replace(
								'/[^\d]/',
								'',
								$pform_psequence
							);
						?> ] = <?php echo html_entity_decode(json_encode( $init_form )); ?>;
					</script>
				<?php
				}
				CP_SESSION::unset_var( 'cp_cff_form_data' );

                // Remove the form tags
                if( preg_match( '/<form[^>]*>/', $form_content, $match ) )
                {
                    $form_content = str_replace( $match[ 0 ], '', $form_content);
                    $form_content = preg_replace( '/<\/form>/', (!empty($_REQUEST['cp_cff_wc']) ? '<input type="hidden" name="cp_cff_wc" value="'.esc_attr($_REQUEST['cp_cff_wc']).'" />'.(!empty($quantity) ? '<pre style="display:none;"><script>jQuery(window).on("load",function(){jQuery(\'[name="quantity"]\').val('.$quantity.')});</script></pre>' : '') : ''), $form_content);
                }

                $tmp = $this->_get_post_meta( $post->ID, 'woocommerce_cpcff_calculate_price', true );
				$visual_price_field = $this->_get_post_meta( $post->ID, 'woocommerce_cpcff_visual_price_field', true );
				$request_cost = !empty($tmp) ? (!empty($visual_price_field) ? trim($visual_price_field) : $this->_cpcff_main->get_form($this->form['id'])->get_option('request_cost', false)) : false;

				$product_price = $product->get_price();
				echo '<div class="cpcff-woocommerce-wrapper">'
                     .$form_content
                     .( ( method_exists( $woocommerce, 'nonce_field' ) ) ? $woocommerce->nonce_field('add_to_cart') : '' )
					 .'<script>woocommerce_cpcff_product='.esc_js($id).';woocommerce_cpcff_product_price='.esc_js(@floatval($product_price)).';</script>'
                     .'<input type="hidden" name="woocommerce_cpcff_product" id="woocommerce_cpcff_product" value="'.$id.'" />'
                     .'<input type="hidden" name="woocommerce_cpcff_product_price" id="woocommerce_cpcff_product_price" value="'.esc_attr($product_price).'" />'
                     .'<input type="hidden" name="add-to-cart" value="'.$id.'" />'
                     .( ( $request_cost ) ? '<input type="hidden" name="woocommerce_cpcff_field" value="'.$request_cost.'" /><input type="hidden" name="woocommerce_cpcff_form" value="'.$this->form[ 'id' ].'">' : '' )
                     .( ( $quantity_field ) ? '<input type="hidden" name="woocommerce_cpcff_quantity_field" value="'.$quantity_field.'" />' : '' )
                     .'</div>';

			}

			echo '<div class="clear"></div>';

        } // End display_form

		public function check_nonce( $check )
		{
			if(isset($_REQUEST['woocommerce_cpcff_product'])) return false;
			return $check;
		} // End check_nonce

        /**
         * Enqueue all resources: CSS and JS files, required by the Addon
         */
        public function enqueue_scripts()
        {
            if( $this->apply_addon() && !$this->_resources_loaded)
            {
				$this->_resources_loaded = true;
				wp_enqueue_style('cpcff_woocommerce_addon_css', plugins_url('/woocommerce.addon/css/styles.css', __FILE__));
				if($GLOBALS['CP_CALCULATEDFIELDSF_DEFAULT_DEFER_SCRIPTS_LOADING'])
				{
					wp_enqueue_script( 'cpcff_woocommerce_addon_js', plugins_url('/woocommerce.addon/js/scripts.js',  __FILE__), array( 'jquery' ) );
				}
				else
				{
					print '<script type="text/javascript" src="'.plugins_url('addons/woocommerce.addon/js/scripts.js', CP_CALCULATEDFIELDSF_MAIN_FILE_PATH).'"></script>';
				}
            }

        } // End enqueue_scripts

        public function enqueue_cart_resources()
        {
            wp_enqueue_style ( 'cpcff_woocommerce_addon_cart_css', plugins_url('/woocommerce.addon/css/styles.cart.css', __FILE__) );
        } // End enqueue_cart_resources

        /**
         * Corrects the form options
         */
        public function get_form_options( $value, $field, $id )
        {
            if( $this->apply_addon() && !is_admin() )
            {
                switch( $field )
                {
                    case 'fp_return_page':
                        return $_SERVER[ 'REQUEST_URI' ];
                    case 'cv_enable_captcha':
                        return 'false';
                    break;
                    case 'cache':
                        return '';
                    case 'enable_paypal':
                        return 0;
                }
            }
            return $value;

        } // End get_form_options

        /************************ METHODS FOR PRODUCT PAGE  *****************************/
		public function get_addon_settings()
		{
			if( isset( $_REQUEST[ 'cff-woocommerce-settings' ] ) )
			{
				check_admin_referer( $this->addonID, '_cpcff_nonce' );
				try
				{
					$message = __('Successful integration !!!', 'calculated-fields-form');
					$class = 'success';

					update_option('woocommerce_cpcff_form', trim($_REQUEST['woocommerce_cpcff_form']));
                    update_option('woocommerce_cpcff_title_field', trim($_REQUEST['woocommerce_cpcff_title_field']));
                    update_option('woocommerce_cpcff_weight_field', trim($_REQUEST['woocommerce_cpcff_weight_field']));
                    update_option('woocommerce_cpcff_length_field', trim($_REQUEST['woocommerce_cpcff_length_field']));
                    update_option('woocommerce_cpcff_width_field', trim($_REQUEST['woocommerce_cpcff_width_field']));
                    update_option('woocommerce_cpcff_height_field', trim($_REQUEST['woocommerce_cpcff_height_field']));
                    update_option('woocommerce_cpcff_quantity_field', trim($_REQUEST['woocommerce_cpcff_quantity_field']));
                    update_option('woocommerce_cpcff_visual_price_field', trim($_REQUEST['woocommerce_cpcff_visual_price_field']));
                    update_option('woocommerce_cpcff_minimum_price', trim($_REQUEST['woocommerce_cpcff_minimum_price']));
                    update_option('woocommerce_cpcff_calculate_price', !empty($_REQUEST['woocommerce_cpcff_calculate_price']));
                    update_option('woocommerce_cpcff_read_more', !empty($_REQUEST['woocommerce_cpcff_read_more']));
                    update_option('woocommerce_cpcff_activate_summary', (!empty($_REQUEST['woocommerce_cpcff_activate_summary'])) ? 1 : 0);
                    update_option('woocommerce_cpcff_summary_title', trim($_REQUEST['woocommerce_cpcff_summary_title']));
					update_option('woocommerce_cpcff_summary', trim($_REQUEST['woocommerce_cpcff_summary']));
				}
				catch(Exception $err)
				{
					$message = $err->getMessage();
					$class = 'error';
				}
			}

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<a id="cp-cff-woocommerce-section"></a>
			<form method="post" action="<?php print esc_url(admin_url('admin.php?page=cp_calculated_fields_form#cp-cff-woocommerce-section')); ?>">
				<div id="metabox_woocommerce_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_woocommerce_addon_form_settings' ) ); ?>" >
					<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
					<div class="inside">
						<?php
							if(!empty($message)) print '<div class="form-builder-'.$class.'-messages"><p class="'.$class.'-text">'.$message.'</p></div>';
						?>
						<p><?php _e('<b>Integrate a form with all the products in the store at once.</b><br>It is possible to integrate the forms into the products directly through the products\' settings. The integration from the product settings takes precedence.', 'calculated-fields-form');?></p>
						<?php
							$settings = array(
								'woocommerce_cpcff_form' => get_option('woocommerce_cpcff_form'),
								'woocommerce_cpcff_calculate_price' => get_option('woocommerce_cpcff_calculate_price'),
								'woocommerce_cpcff_minimum_price' => get_option('woocommerce_cpcff_minimum_price', ''),
								'woocommerce_cpcff_title_field' => get_option('woocommerce_cpcff_title_field', ''),
								'woocommerce_cpcff_weight_field' => get_option('woocommerce_cpcff_weight_field', ''),
								'woocommerce_cpcff_length_field' => get_option('woocommerce_cpcff_length_field', ''),
								'woocommerce_cpcff_width_field' => get_option('woocommerce_cpcff_width_field', ''),
								'woocommerce_cpcff_height_field' => get_option('woocommerce_cpcff_height_field', ''),
								'woocommerce_cpcff_quantity_field' => get_option('woocommerce_cpcff_quantity_field', ''),
								'woocommerce_cpcff_visual_price_field' => get_option('woocommerce_cpcff_visual_price_field', ''),
								'woocommerce_cpcff_read_more' => get_option('woocommerce_cpcff_read_more'),
								'woocommerce_cpcff_activate_summary' => get_option('woocommerce_cpcff_activate_summary'),
								'woocommerce_cpcff_summary_title' => get_option('woocommerce_cpcff_summary_title', ''),
								'woocommerce_cpcff_summary' => get_option('woocommerce_cpcff_summary', '')
							);

							$this->_settings($settings);

						?>
						<input type="hidden" name="cff-woocommerce-settings" value="1" />
						<input type="hidden" name="_cpcff_nonce" value="<?php echo wp_create_nonce( $this->addonID ); ?>" />
						<p><input type="submit" value="<?php esc_attr_e(__('Save settings','calculated-fields-form')); ?>" class="button-secondary" /></p>
                        <div style="border:1px solid #F0AD4E;background:#FBE6CA;padding:10px;margin:10px 0;font-size:1.3em;">
                            <div><?php _e('For additional WooCommerce resources', 'calculated-fields-form'); ?> <a href="https://cff-bundles.dwbooster.com/?filtering=1&category[]=woocommerce" target="_blank" style="font-weight:bold;"><?php _e('Click Here', 'calculated-fields-form'); ?></a></div>
                        </div>
					</div>
				</div>
			</form>
			<?php

		}
        /************************ METHODS FOR PRODUCT PAGE  *****************************/

        public function init_hook()
        {
            add_meta_box('cpcff_woocommerce_metabox', __("Calculated Fields Form", 'calculated-fields-form'), array(&$this, 'metabox_form'), 'product', 'normal', 'high');
            add_action('save_post', array(&$this, 'save_data'), 10, 3);
        } // End init_hook

		private function _get_post_meta($id, $name, $single=true)
		{
			$form = get_post_meta($id, 'woocommerce_cpcff_form', true);
			if(empty($form) && empty(get_post_meta($id, 'woocommerce_cpcff_exclude_global_form', true)))
			{
				return get_option($name);
			}
			return get_post_meta($id, $name, $single);
		}
		private function _settings($settings)
		{
			global $wpdb;

            $id = $settings['woocommerce_cpcff_form'];
            $active = $settings['woocommerce_cpcff_calculate_price'];
            $minimum_price = $settings['woocommerce_cpcff_minimum_price'];
			$title_field = $settings['woocommerce_cpcff_title_field'];
			$weight_field = $settings['woocommerce_cpcff_weight_field'];
			$length_field = $settings['woocommerce_cpcff_length_field'];
			$width_field = $settings['woocommerce_cpcff_width_field'];
			$height_field = $settings['woocommerce_cpcff_height_field'];
			$quantity_field = $settings['woocommerce_cpcff_quantity_field'];
			$visual_price_field = $settings['woocommerce_cpcff_visual_price_field'];
			$read_more_button = $settings['woocommerce_cpcff_read_more'];
            $activate_summary = $settings['woocommerce_cpcff_activate_summary'];
            $summary_title = $settings['woocommerce_cpcff_summary_title'];
            $summary = $settings['woocommerce_cpcff_summary'];
			?>
            <style>.width50{min-width:50%;}</style>
            <table class="form-table">
				<tr>
					<td style="white-space:nowrap;">
						<?php _e('Enter the ID of the form', 'calculated-fields-form');?>:
					</td>
                    <td>
						<select name="woocommerce_cpcff_form" class="width50">
							<option value=""><?php print esc_html( __( 'Select a form', 'calculated-fields-form' ) ); ?></option>
						<?php
							$forms_list = $wpdb->get_results( "SELECT id, form_name FROM ".$wpdb->prefix.CP_CALCULATEDFIELDSF_FORMS_TABLE );
							foreach( $forms_list as $form )
							{
								$selected = ( !empty( $id ) && $form->id == $id ) ? 'SELECTED' : '';
								print '<option value="'.$form->id.'" '.$selected.'>'.esc_html( $form->form_name ).' ('.$form->id.')</option>';
							}
						?>
						</select>
                    </td>
                </tr>
                <tr>
					<td colspan="2">
						<input type="checkbox" name="woocommerce_cpcff_calculate_price" <?php print( ( !empty( $active ) ) ? 'checked' : '' ); ?> /> <?php _e('Calculate the product price through the form', 'calculated-fields-form');?>
					</td>
				</tr>
				<tr>
					<td>
						<?php _e('Minimum price allowed (numbers only)', 'calculated-fields-form');?>:
					</td>
					<td>
						<input type="text" name="woocommerce_cpcff_minimum_price" value="<?php print( esc_attr( ( !empty( $minimum_price ) ) ? $minimum_price : '' ) ); ?>"  class="width50">
                    </td>
                </tr>
				<tr>
					<td style="white-space:nowrap;vertical-align:top;">
						<?php _e('Field for cart title', 'calculated-fields-form');?>:
					</td>
                    <td style="width:100%;">
                        <input type="text" name="woocommerce_cpcff_title_field" value="<?php print esc_attr( $title_field ); ?>" placeholder="fieldname#" class="width50" /><br />
						<em><?php _e('Enter the field\'s name for the product\'s title in the cart', 'calculated-fields-form');?></em>
					</td>
				</tr>
				<tr>
					<td style="white-space:nowrap;vertical-align:top;">
						<?php _e('Field for weight', 'calculated-fields-form');?>:
					</td>
                    <td style="width:100%;">
                        <input type="text" name="woocommerce_cpcff_weight_field" value="<?php print esc_attr( $weight_field ); ?>" placeholder="fieldname#" class="width50" /><br />
						<em><?php _e('If the product\'s weight is determined through the form', 'calculated-fields-form');?></em>
					</td>
				</tr>
				<tr>
					<td style="white-space:nowrap;vertical-align:top;">
						<?php _e('Field for length', 'calculated-fields-form');?>:
					</td>
                    <td style="width:100%;">
                        <input type="text" name="woocommerce_cpcff_length_field" value="<?php print esc_attr( $length_field ); ?>" placeholder="fieldname#" class="width50" /><br />
						<em><?php _e('If the product\'s length is determined through the form', 'calculated-fields-form');?></em>
					</td>
				</tr>
				<tr>
					<td style="white-space:nowrap;vertical-align:top;">
						<?php _e('Field for width', 'calculated-fields-form');?>:
					</td>
                    <td style="width:100%;">
                        <input type="text" name="woocommerce_cpcff_width_field" value="<?php print esc_attr( $width_field ); ?>" placeholder="fieldname#" class="width50" /><br />
						<em><?php _e('If the product\'s width is determined through the form', 'calculated-fields-form');?></em>
					</td>
				</tr>
				<tr>
					<td style="white-space:nowrap;vertical-align:top;">
						<?php _e('Field for height', 'calculated-fields-form');?>:
					</td>
                    <td style="width:100%;">
                        <input type="text" name="woocommerce_cpcff_height_field" value="<?php print esc_attr( $height_field ); ?>" placeholder="fieldname#" class="width50" /><br />
						<em><?php _e('If the product\'s height is determined through the form', 'calculated-fields-form');?></em>
					</td>
				</tr>
				<tr>
					<td style="white-space:nowrap;vertical-align:top;">
						<?php _e('Field for quantity', 'calculated-fields-form');?>:
					</td>
                    <td style="width:100%;">
                        <input type="text" name="woocommerce_cpcff_quantity_field" value="<?php print esc_attr( $quantity_field ); ?>" placeholder="fieldname#" class="width50" /><br />
						<em><?php _e('Field for quantity in the form', 'calculated-fields-form');?></em>
					</td>
				</tr>
				<tr>
					<td style="white-space:nowrap;vertical-align:top;">
						<?php _e('Field for visual price', 'calculated-fields-form');?>:
					</td>
                    <td style="width:100%;">
                        <input type="text" name="woocommerce_cpcff_visual_price_field" value="<?php print esc_attr( $visual_price_field ); ?>" placeholder="fieldname#" class="width50" /><br />
						<em><?php _e('For displaying a price in the product\'s page, different to price calculated by the Request Cost field', 'calculated-fields-form');?></em>
					</td>
				</tr>
				<tr>
					<td style="vertical-align:top;" colspan="2">
					    <input type="checkbox" name="woocommerce_cpcff_read_more" <?php print ( !empty($read_more_button) ) ? 'CHECKED' : ''; ?> />
						<?php _e('Replace the "Add to Cart" button by "View Product" in the shop and archive pages', 'calculated-fields-form');?>
					</td>
				</tr>
				<tr style="border-top:2px solid #DDD;border-left:2px solid #DDD;border-right:2px solid #DDD;">
					<td colspan="2">
						<p>* <?php _e('The summary section is optional. It is possible to use the special tags supported by the notification emails.', 'calculated-fields-form');?></p>
						<p>* <?php _e('If "Active summary" is unticked, all data collected by the form is added to the cart and order.', 'calculated-fields-form');?></p>
						<p>* <?php _e('If "Active summary" is ticked and the "Summary" attribute is left empty, the add-on does not include the information collected by the form into the cart and order.', 'calculated-fields-form');?></p>
					</td>
				</tr>
				<tr style="border-left:2px solid #DDD;border-right:2px solid #DDD;">
					<td>
						<?php _e('Activate the summary', 'calculated-fields-form');?>:
					</td>
					<td>
						<input type="checkbox" name="woocommerce_cpcff_activate_summary" <?php print( ( !empty( $activate_summary ) ) ? 'CHECKED' : '' ); ?> />
                    </td>
                </tr>
				<tr style="border-left:2px solid #DDD;border-right:2px solid #DDD;">
					<td>
						<?php _e('Summary title', 'calculated-fields-form');?>:
					</td>
					<td>
						<input type="text" name="woocommerce_cpcff_summary_title" value="<?php print( esc_attr( ( !empty( $summary_title ) ) ? $summary_title : '' ) ); ?>" style="width:100%;">
                    </td>
                </tr>
				<tr style="border-bottom:2px solid #DDD;border-left:2px solid #DDD;border-right:2px solid #DDD;">
					<td>
						<?php _e('Summary', 'calculated-fields-form');?>:
					</td>
					<td>
						<textarea name="woocommerce_cpcff_summary" style="resize: vertical; min-height: 70px; width:100%;"><?php print ( esc_textarea( ( !empty( $summary ) ) ? $summary : '' ) ); ?></textarea>
					</td>
                </tr>

            </table>
            <div style="margin-top:20px; padding:20px; background-color:#fef8ee; border-left: 3px solid #f0b849;"><?php _e('Please, assign a regular price to the product, even if the final one is calculated by the form, or WooCommerce will not include an add to cart button on the product page.', 'calculated-fields-form'); ?></div>
			<?php
		}

        public function metabox_form()
        {
            global $post;

            $settings = array(
				'woocommerce_cpcff_form' => get_post_meta($post->ID, 'woocommerce_cpcff_form', true),
				'woocommerce_cpcff_calculate_price' => get_post_meta($post->ID, 'woocommerce_cpcff_calculate_price', true),
				'woocommerce_cpcff_minimum_price' => get_post_meta($post->ID, 'woocommerce_cpcff_minimum_price', true),
				'woocommerce_cpcff_title_field' => get_post_meta($post->ID, 'woocommerce_cpcff_title_field', true),
				'woocommerce_cpcff_weight_field' => get_post_meta($post->ID, 'woocommerce_cpcff_weight_field', true),
				'woocommerce_cpcff_length_field' => get_post_meta($post->ID, 'woocommerce_cpcff_length_field', true),
				'woocommerce_cpcff_width_field' => get_post_meta($post->ID, 'woocommerce_cpcff_width_field', true),
				'woocommerce_cpcff_height_field' => get_post_meta($post->ID, 'woocommerce_cpcff_height_field', true),
				'woocommerce_cpcff_quantity_field' => get_post_meta($post->ID, 'woocommerce_cpcff_quantity_field', true),
				'woocommerce_cpcff_visual_price_field' => get_post_meta($post->ID, 'woocommerce_cpcff_visual_price_field', true),
				'woocommerce_cpcff_read_more' => get_post_meta($post->ID, 'woocommerce_cpcff_read_more', true),
				'woocommerce_cpcff_activate_summary' => get_post_meta($post->ID, 'woocommerce_cpcff_activate_summary', true),
				'woocommerce_cpcff_summary_title' => get_post_meta($post->ID, 'woocommerce_cpcff_summary_title', true),
				'woocommerce_cpcff_summary' => get_post_meta($post->ID, 'woocommerce_cpcff_summary', true)
			);
			$exclude = get_post_meta($post->ID, 'woocommerce_cpcff_exclude_global_form', true);
			?>
			<table class="form-table">
				<tr>
					<td style="white-space:nowrap;">
						<input type="checkbox" name="woocommerce_cpcff_exclude_global_form" <?php print((!empty($exclude)) ? 'CHECKED' : ''); ?> /> <?php _e('Exclude global form from this product.', 'calculated-fields-form');?>
					</td>
                </tr>
			</table>
			<?php
			$this->_settings($settings);
        } // End metabox_form

        public function save_data($post_id, $post, $update)
        {
            if( !empty( $post ) && is_object( $post ) && $post->post_type == 'product' )
            {

                if( isset( $_REQUEST[ 'woocommerce_cpcff_form' ] ) )
                {
					delete_post_meta( $post->ID, 'woocommerce_cpcff_form' );
					delete_post_meta( $post->ID, 'woocommerce_cpcff_calculate_price' );
					delete_post_meta( $post->ID, 'woocommerce_cpcff_minimum_price' );
					delete_post_meta( $post->ID, 'woocommerce_cpcff_title_field' );
					delete_post_meta( $post->ID, 'woocommerce_cpcff_weight_field' );
					delete_post_meta( $post->ID, 'woocommerce_cpcff_length_field' );
					delete_post_meta( $post->ID, 'woocommerce_cpcff_width_field' );
					delete_post_meta( $post->ID, 'woocommerce_cpcff_height_field' );
					delete_post_meta( $post->ID, 'woocommerce_cpcff_quantity_field' );
					delete_post_meta( $post->ID, 'woocommerce_cpcff_activate_summary' );
					delete_post_meta( $post->ID, 'woocommerce_cpcff_summary' );
					delete_post_meta( $post->ID, 'woocommerce_cpcff_summary_title' );
					delete_post_meta( $post->ID, 'woocommerce_cpcff_visual_price_field' );
					delete_post_meta( $post->ID, 'woocommerce_cpcff_read_more' );
					delete_post_meta( $post->ID, 'woocommerce_cpcff_exclude_global_form' );

                    add_post_meta( $post->ID, 'woocommerce_cpcff_form', $_REQUEST[ 'woocommerce_cpcff_form' ], true );
                    add_post_meta( $post->ID, 'woocommerce_cpcff_title_field', trim( $_REQUEST[ 'woocommerce_cpcff_title_field' ] ), true );
                    add_post_meta( $post->ID, 'woocommerce_cpcff_weight_field', trim( $_REQUEST[ 'woocommerce_cpcff_weight_field' ] ), true );
                    add_post_meta( $post->ID, 'woocommerce_cpcff_length_field', trim( $_REQUEST[ 'woocommerce_cpcff_length_field' ] ), true );
                    add_post_meta( $post->ID, 'woocommerce_cpcff_width_field', trim( $_REQUEST[ 'woocommerce_cpcff_width_field' ] ), true );
                    add_post_meta( $post->ID, 'woocommerce_cpcff_height_field', trim( $_REQUEST[ 'woocommerce_cpcff_height_field' ] ), true );
                    add_post_meta( $post->ID, 'woocommerce_cpcff_quantity_field', trim( $_REQUEST[ 'woocommerce_cpcff_quantity_field' ] ), true );
                    add_post_meta( $post->ID, 'woocommerce_cpcff_visual_price_field', trim( $_REQUEST[ 'woocommerce_cpcff_visual_price_field' ] ), true );
                    add_post_meta( $post->ID, 'woocommerce_cpcff_minimum_price', trim( $_REQUEST[ 'woocommerce_cpcff_minimum_price' ] ), true );
                    add_post_meta($post->ID,'woocommerce_cpcff_calculate_price',!empty($_REQUEST['woocommerce_cpcff_calculate_price']), true);
                    add_post_meta($post->ID, 'woocommerce_cpcff_read_more', !empty($_REQUEST['woocommerce_cpcff_read_more']), true);
                    add_post_meta( $post->ID, 'woocommerce_cpcff_activate_summary', ( !empty( $_REQUEST[ 'woocommerce_cpcff_activate_summary' ] ) ) ? 1 : 0, true );
                    add_post_meta( $post->ID, 'woocommerce_cpcff_summary_title', trim( $_REQUEST[ 'woocommerce_cpcff_summary_title' ] ), true );
					add_post_meta( $post->ID, 'woocommerce_cpcff_summary', trim( $_REQUEST[ 'woocommerce_cpcff_summary' ] ), true );
					add_post_meta($post->ID, 'woocommerce_cpcff_exclude_global_form', !empty($_REQUEST['woocommerce_cpcff_exclude_global_form']), true);
				}
            }
        }

		/**
		 *	Delete the form from the addon's table
		 */
        public function delete_form( $formid)
		{
			global $wpdb;
			$wpdb->delete(
				$wpdb->postmeta,
				array('meta_key' => 'woocommerce_cpcff_form', 'meta_value' => $formid),
				array('%s','%d')
			);
		} // delete_form
    } // End Class

    // Main add-on code
    $cpcff_woocommerce_obj = new CPCFF_WooCommerce();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cpcff_woocommerce_obj);
}
?>