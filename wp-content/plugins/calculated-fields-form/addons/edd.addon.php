<?php
/*
....
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPCFF_EasyDigitalDownloads' ) )
{
    class CPCFF_EasyDigitalDownloads extends CPCFF_BaseAddon
    {
		static public $category = 'Third Party Plugins';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-edd-20150309";
		protected $name = "CFF - Easy Digital Downloads";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#edd-addon';

		/************************ ADDON CODE *****************************/
        /************************ ATTRIBUTES *****************************/

        private $form = array(); // Form data
        private $_resources_loaded = false; // Prevent to load the add-on resources multiple times.
		private $_cpcff_main;

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->_cpcff_main = CPCFF_MAIN::instance();
			$this->description = __("The add-on allows integrate the forms with Easy Digital Downloads products", 'calculated-fields-form');

            // Check if the plugin is active
			if(!$this->addon_is_active() || !class_exists('Easy_Digital_Downloads')) return;

			// Addon display
			add_filter('cpcff_get_option', array(&$this, 'edd_get_form_options'), 10, 3);
            add_action('edd_purchase_link_top', array(&$this, 'edd_display_form'), 1, 2);
            add_filter('edd_is_ajax_disabled', array(&$this, 'edd_ajax_disabled'));
			add_filter('edd_straight_to_checkout', array(&$this, 'edd_straight_to_checkout'));
			add_filter('edd_purchase_link_args', array(&$this, 'edd_edit_purchase_link_args'));
			add_filter('edd_download_quantity_disabled', array(&$this, 'edd_download_quantity_disabled'), 10, 2);
			add_filter('edd_item_in_cart', array(&$this, 'edd_item_in_cart'), 10, 3);

			// Filters for the Calculated Fields Form
            add_action('cpcff_redirect', array( &$this, 'edd_cpcff_redirect'), 10);
            add_filter('cpcff_check_nonce', array( &$this, 'edd_check_nonce'), 10);

			// Filters for cart actions
			add_filter('edd_add_to_cart_item', array(&$this, 'edd_add_cart_item_data'));
			add_action('edd_checkout_cart_item_title_after', array(&$this, 'edd_display_cart_item_data'), 10, 2);
			add_filter('edd_cart_item_price', array(&$this, 'edd_display_cart_item_price'), 10, 3);
            add_action('edd_cart_items_before', array( &$this, 'edd_enqueue_cart_resources'));
            add_action('edd_update_payment_status', array(&$this, 'edd_update_payment_status'), 10, 3);

            // Notification emails
            add_filter('cpcff_send_notification_email', array(&$this, 'edd_notification_email'), 10, 3);
            add_filter('cpcff_send_confirmation_email', array(&$this, 'edd_notification_email'), 10, 3);

			if( is_admin() )
			{
				// The init hook
				add_action( 'admin_init', array( &$this, 'edd_init_hook' ), 1 );

				// Delete forms
				add_action( 'cpcff_delete_form', array(&$this, 'edd_delete_form') );
			}

        } // End __construct

        /************************ PRIVATE METHODS *****************************/
        /**
         * Check if the add-on can be applied to the product
         */
        private function apply_addon( $id = false )
        {
            global $post, $edd_download_shortcode_item_i;
			if(!empty($edd_download_shortcode_item_i)) return false; // Prevents to load the form in the [downloads] shortcode.
            $this->form = array();

            if( $id ) $post_id = $id;
            elseif( isset( $_REQUEST[ 'edd_cpcff_product' ] ) ) $post_id = $_REQUEST[ 'edd_cpcff_product' ];
            elseif( isset( $post ) ) $post_id = $post->ID;

            if( isset( $post_id ) )
            {
                $tmp = get_post_meta( $post_id, 'edd_cpcff_form', true );
                if( !empty( $tmp ) ) $this->form[ 'id' ] = $tmp;
            }

            return !empty( $this->form );

        }

		/************************ PUBLIC METHODS  *****************************/

		/**
		 * Forces the disable of AJAX Submission.
		 */
		public function edd_ajax_disabled($v)
		{
			if( $this->apply_addon() ) return true;
			return $v;
		} // End edd_ajax_disabled

		/**
		 * Forces redirection to the checkout page.
		 */
		public function edd_straight_to_checkout($v)
		{
			if( $this->apply_addon() ) return true;
			return $v;
		} // End edd_straight_to_checkout

		/**
		 * Replaces the button text with the label of submit button in the form's settings.
		 */
		public function edd_edit_purchase_link_args($args)
		{
			if($this->apply_addon()) $args['text'] = $this->_cpcff_main->get_form($this->form['id'])->get_option('vs_text_submitbtn', 'Submit');
            return $args;
		} // End edd_edit_purchase_link_args

		/**
		 * Forces to hide the quantity box
		 */
		public function edd_download_quantity_disabled($v, $id)
		{
			if($this->apply_addon($id)) return true;
            return $v;
		} // End edd_download_quantity_disabled

		/**
		 * Forces that every product added to the cart be considered as a new product
		 */
		public function edd_item_in_cart($v, $id, $options)
		{
			if($this->apply_addon($id)) return false;
            return $v;
		} // End edd_item_in_cart

		/**
         * Corrects the form options removing captcha and payment options and modifying the return URL
         */
        public function edd_get_form_options( $value, $field, $formid )
        {
            if( $this->apply_addon() )
            {
                switch( $field )
                {
                    case 'fp_return_page':
                        return edd_get_checkout_uri();
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

        } // End edd_get_form_options

		/**
         * Display the form associated to the product
         */
        public function edd_display_form($id, $args)
        {
            if ( $this->apply_addon() )
			{
				$product = new EDD_Download($id);
				$this->edd_enqueue_scripts();
				$form_content = $this->_cpcff_main->public_form( $this->form );

				// Remove the form tags
                if( preg_match( '/<form[^>]*>/', $form_content, $match ) )
                {
                    $form_content = str_replace( $match[ 0 ], '', $form_content);
                    $form_content = preg_replace( '/<\/form>/', '', $form_content);
                }

                $tmp = get_post_meta( $id, 'edd_cpcff_calculate_price', true );
				$request_cost = !empty($tmp) ? $this->_cpcff_main->get_form($this->form['id'])->get_option('request_cost', false) : false;

				$product_price = $product->price;
				echo '<div class="cpcff-edd-wrapper">'
                     .$form_content
                     .'<script>edd_cpcff_product='.esc_js($id).';edd_cpcff_product_price='.esc_js(@floatval($product_price)).';</script>'
                     .'<input type="hidden" name="edd_cpcff_product" id="edd_cpcff_product" value="'.esc_attr($id).'" />'
                     .'<input type="hidden" name="edd_cpcff_product_price" id="edd_cpcff_product_price" value="'.esc_attr($product_price).'" />'
                     .( ( $request_cost ) ? '<input type="hidden" name="edd_cpcff_field" value="'.$request_cost.'" /><input type="hidden" name="edd_cpcff_form" value="'.$this->form[ 'id' ].'">' : '' )
                     .'</div>';

				echo '<div class="clear"></div>';
			}
        } // End edd_display_form

		/**
         * Avoid redirect the Calculated Fields Form to the thanks page.
         */
        function edd_cpcff_redirect()
        {
			if( isset( $_REQUEST[ 'edd_action' ] ) || isset( $_REQUEST[ 'edd_cpcff_product' ] ) ) return false;
            return true;
        } // edd_cpcff_redirect

		/**
		 * Avoids to include the CFF nonce to use only the nonce of EDD
		 */
		public function edd_check_nonce( $check )
		{
			if( isset( $_REQUEST[ 'edd_action' ] ) || isset( $_REQUEST[ 'edd_cpcff_product' ] ) ) return false;
			return $check;
		} // End edd_check_nonce

        public function edd_notification_email($send, $submission_obj, $form_obj)
        {
            if(
                (isset($_REQUEST['edd_action']) || isset($_REQUEST['edd_cpcff_product'])) &&
                $form_obj->get_option('paypal_notiemails', '0') != '1' &&
                $submission_obj->paid != 1
             )  return false;
            return $send;
        } // End edd_notification_email

        /**
         * Change payment status of submission
         */
        public function edd_update_payment_status($payment_id, $new_status, $old_status)
        {
            $new_status = strtolower($new_status);
            if($new_status == 'publish')
            {
                $payment = edd_get_payment($payment_id);
                $downloads = $payment->downloads;

                /**
                 * Action called after process the payment.
                 */
                try{
                    if(class_exists('CPCFF_SUBMISSIONS'))
                    {
                        foreach($downloads as $download)
                        {
                            if(isset($download['options']) && isset($download['options']['cff-id']))
                            {
                                $submission_id = $download['options']['cff-id']*1;
                                CPCFF_SUBMISSIONS::update($submission_id, array('paid'=>1));
                                $form_obj = CPCFF_SUBMISSIONS::get_form($submission_id);

                                $submission = CPCFF_SUBMISSIONS::get($submission_id);
                                $submission->paypal_post['itemnumber'] = $submission_id;
                                do_action('cpcff_payment_processed', $submission->paypal_post);

                                if($form_obj->get_option('paypal_notiemails', '0') != '1')
                                $this->_cpcff_main->send_mails($submission_id);
                            }
                        }
                    }
                }
                catch(Exception $err){}
            }
        } // End edd_update_payment_status

		/**
		 * Includes the summary of the information collected by the form in the cart and orders, and fixes the product's price
		 */
		public function edd_add_cart_item_data( $item )
		{
			if(
				isset($item['id']) &&
				isset($_REQUEST['edd_cpcff_product']) &&
				$item['id'] == $_REQUEST['edd_cpcff_product'] &&
				($cp_cff_form_data = CP_SESSION::registered_events()) !== false
			)
			{
				$data_id = $cp_cff_form_data[$cp_cff_form_data['latest']];
                if( !empty( $data_id ) )
                {
					$submission = CPCFF_SUBMISSIONS::get($data_id);
					if(!empty($submission))
					{
						$activate_summary = get_post_meta( $item['id'], 'edd_cpcff_activate_summary', true );
						$item['options']['price_id'] = uniqid();
						$item['options']['cff'] = '';
						if( !empty( $activate_summary ) )
						{
							$summary_title = get_post_meta( $item['id'], 'edd_cpcff_summary_title', true );
							if(!empty($summary_title)) $item['options']['cff'] .= "<br />".$summary_title;

							$summary = get_post_meta( $item['id'], 'edd_cpcff_summary', true );
						}
						if( empty( $summary ) ) $summary = '<%INFO%>';
						elseif($summary !== strip_tags($summary)) $summary = str_replace(array("\n","\r"), '', $summary);
						$item['options']['cff'] .= "<br />".preg_replace("/(<br>)+/", '<br>', $this->_cpcff_main->form_result_shortcode( array(), $summary, $data_id));

						$minimum_price = get_post_meta( $item[ 'id' ], 'edd_cpcff_minimum_price', true );
						$data = $submission->paypal_post;
						$price = preg_replace( '/[^\d\.\,]/', '', $data[ 'final_price' ] );
						$price = (!empty($minimum_price)) ? max($price, $minimum_price) : $price;
						$item['options']['cff-price'] = $price;
						$item['options']['cff-id'] = $submission->id;
					}
				}
			}
			return $item;
		} // End edd_add_cart_item_data

		/**
		 * Prints the summary
		 */
		public function edd_display_cart_item_data($item, $key)
		{
			if(isset($item['options']['cff'])) print $item['options']['cff'];
		} // End edd_display_cart_item_data

		/**
		 * Returns the calculated price
		 */
		public function edd_display_cart_item_price($price, $download_id, $options)
		{
			if(isset($options['cff-price'])) return $options['cff-price'];
			return $price;
		} // End edd_display_cart_item_price

		/**
		 * Enqueue the CSS resourcs in the cart page
		 */
		public function edd_enqueue_cart_resources()
        {
            wp_enqueue_style('cpcff_edd_addon_cart_css', plugins_url('/edd.addon/css/styles.cart.css', __FILE__));
        } // End edd_enqueue_cart_resources

        /**
         * Enqueue all resources: CSS and JS files, required by the Addon
         */
        public function edd_enqueue_scripts()
        {
            if( $this->apply_addon() && !$this->_resources_loaded)
            {
				$this->_resources_loaded = true;
				wp_enqueue_style('cpcff_edd_addon_css', plugins_url('/edd.addon/css/styles.css', __FILE__));
				if($GLOBALS['CP_CALCULATEDFIELDSF_DEFAULT_DEFER_SCRIPTS_LOADING'])
				{
					wp_enqueue_script( 'cpcff_edd_addon_js', plugins_url('/edd.addon/js/scripts.js',  __FILE__), array( 'jquery' ) );
				}
				else
				{
					print '<script type="text/javascript" src="'.plugins_url('addons/edd.addon/js/scripts.js', CP_CALCULATEDFIELDSF_MAIN_FILE_PATH).'"></script>';
				}
            }

        } // End edd_enqueue_scripts



        /************************ METHODS FOR PRODUCT PAGE  *****************************/

        public function edd_init_hook()
        {
            add_meta_box('cpcff_edd_metabox', __("Calculated Fields Form", 'calculated-fields-form'), array(&$this, 'edd_metabox_form'), 'download', 'normal', 'high');
            add_action('save_post', array(&$this, 'edd_save_data'), 10, 3);
			add_action('edd_view_order_details_main_before', array(&$this, 'edd_orders_details_area'));
        } // End edd_init_hook

		/**
		 * Integration form to include in the products settings
		 */
        public function edd_metabox_form()
        {
            global $post, $wpdb;

            $id = get_post_meta( $post->ID, 'edd_cpcff_form', true );
            $active = get_post_meta( $post->ID, 'edd_cpcff_calculate_price', true );
            $minimum_price = get_post_meta( $post->ID, 'edd_cpcff_minimum_price', true );
            $activate_summary = get_post_meta( $post->ID, 'edd_cpcff_activate_summary', true );
            $summary_title = get_post_meta( $post->ID, 'edd_cpcff_summary_title', true );
            $summary = get_post_meta( $post->ID, 'edd_cpcff_summary', true );
			?>
            <table class="form-table">
				<tr>
					<td>
						<?php _e('Enter the ID of the form', 'calculated-fields-form');?>:
					</td>
                    <td>
						<select name="edd_cpcff_form">
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
					<td style="white-space:nowrap;">
						<?php _e('Calculate the product price through the form', 'calculated-fields-form');?>:
					</td>
                    <td style="width:100%;">
                        <input type="checkbox" name="edd_cpcff_calculate_price" <?php print( ( !empty( $active ) ) ? 'checked' : '' ); ?> />
					</td>
				</tr>
				<tr>
					<td>
						<?php _e('Minimum price allowed (numbers only)', 'calculated-fields-form');?>:
					</td>
					<td>
						<input type="text" name="edd_cpcff_minimum_price" value="<?php print( esc_attr( ( !empty( $minimum_price ) ) ? $minimum_price : '' ) ); ?>">
                    </td>
                </tr>
				<tr style="border-top:2px solid #DDD;border-left:2px solid #DDD;border-right:2px solid #DDD;">
					<td colspan="2">
						<?php _e('The summary section is optional. It is possible to use the special tags supported by the notification emails.', 'calculated-fields-form');?>
					</td>
				</tr>
				<tr style="border-left:2px solid #DDD;border-right:2px solid #DDD;">
					<td>
						<?php _e('Activate the summary', 'calculated-fields-form');?>:
					</td>
					<td>
						<input type="checkbox" name="edd_cpcff_activate_summary" <?php print( ( !empty( $activate_summary ) ) ? 'CHECKED' : '' ); ?> />
                    </td>
                </tr>
				<tr style="border-left:2px solid #DDD;border-right:2px solid #DDD;">
					<td>
						<?php _e('Summary title', 'calculated-fields-form');?>:
					</td>
					<td>
						<input type="text" name="edd_cpcff_summary_title" value="<?php print( esc_attr( ( !empty( $summary_title ) ) ? $summary_title : '' ) ); ?>" style="width:100%;">
                    </td>
                </tr>
				<tr style="border-bottom:2px solid #DDD;border-left:2px solid #DDD;border-right:2px solid #DDD;">
					<td>
						<?php _e('Summary', 'calculated-fields-form');?>:
					</td>
					<td>
						<textarea name="edd_cpcff_summary" style="resize: vertical; min-height: 70px; width:100%;"><?php print ( esc_textarea( ( !empty( $summary ) ) ? $summary : '' ) ); ?></textarea>
					</td>
                </tr>

            </table>
			<?php

        } // End edd_metabox_form

		/**
		 * Saves the integration settings
		 */
        public function edd_save_data($post_id, $post, $update)
        {
            if( !empty( $post ) && is_object( $post ) && $post->post_type == 'download' )
            {

                if( isset( $_REQUEST[ 'edd_cpcff_form' ] ) )
                {
					delete_post_meta( $post->ID, 'edd_cpcff_form' );
					delete_post_meta( $post->ID, 'edd_cpcff_calculate_price' );
					delete_post_meta( $post->ID, 'edd_cpcff_minimum_price' );
					delete_post_meta( $post->ID, 'edd_cpcff_activate_summary' );
					delete_post_meta( $post->ID, 'edd_cpcff_summary' );
					delete_post_meta( $post->ID, 'edd_cpcff_summary_title' );

                    add_post_meta( $post->ID, 'edd_cpcff_form', $_REQUEST[ 'edd_cpcff_form' ], true );
                    add_post_meta( $post->ID, 'edd_cpcff_minimum_price', trim( $_REQUEST[ 'edd_cpcff_minimum_price' ] ), true );
                    add_post_meta(
                        $post->ID,
                        'edd_cpcff_calculate_price',
                        ( empty( $_REQUEST[ 'edd_cpcff_calculate_price' ] ) ) ? false : true,
                        true
                    );
                    add_post_meta( $post->ID, 'edd_cpcff_activate_summary', ( !empty( $_REQUEST[ 'edd_cpcff_activate_summary' ] ) ) ? 1 : 0, true );
                    add_post_meta( $post->ID, 'edd_cpcff_summary_title', trim( $_REQUEST[ 'edd_cpcff_summary_title' ] ), true );
					add_post_meta( $post->ID, 'edd_cpcff_summary', trim( $_REQUEST[ 'edd_cpcff_summary' ] ), true );
				}
            }
        } // End edd_save_data

		public function edd_orders_details_area($payment_id)
		{
			add_filter( 'edd_has_variable_prices', array(&$this, 'edd_orders_has_variable_prices'), 10, 2 );
		} // End edd_orders_details_area

		public function edd_orders_has_variable_prices($ret, $id )
		{
			if($this->apply_addon($id))
			{
				add_filter( 'edd_get_price_option_name', array(&$this, 'edd_orders_set_variable_name'), 10, 4 );
				return true;
			}
			return $ret;
		} // End edd_orders_has_variable_prices

		public function  edd_orders_set_variable_name($price_name, $download_id, $payment_id, $price_id)
		{
			$payment = new EDD_Payment( $payment_id );
			$cart_items = $payment->cart_details;
			if ( !empty( $cart_items ) )
			{
				foreach($cart_items as $cart_item)
				{
					if(
						$cart_item['id'] == $download_id &&
						isset($cart_item['item_number']['options']['price_id']) &&
						$cart_item['item_number']['options']['price_id'] == $price_id &&
						isset($cart_item['item_number']['options']['cff'])
					)
					{
						return '<span style="color:black; font-weight:normal;">'.$cart_item['item_number']['options']['cff'].'</span>';
					}
				}
			}
			return $price_name;
		} // End edd_orders_set_variable_name

		/**
		 *	Delete the form from the addon's table
		 */
        public function edd_delete_form( $formid)
		{
			global $wpdb;
			$wpdb->delete(
				$wpdb->postmeta,
				array('meta_key' => 'edd_cpcff_form', 'meta_value' => $formid),
				array('%s','%d')
			);
		} // edd_delete_form
    } // End Class

    // Main add-on code
    $cpcff_easydigitaldownloads_obj = new CPCFF_EasyDigitalDownloads();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cpcff_easydigitaldownloads_obj);
}