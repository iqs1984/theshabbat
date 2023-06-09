<?php
/**
 * Form class with database interaction, data, and methods.
 *
 * @package CFF.
 * @since 1.0.179
 */
require_once dirname(__FILE__).'/cpcff_revisions.inc.php';

if(!class_exists('CPCFF_FORM'))
{
	/**
	 * Class to create create, save, and read the forms data.
	 *
	 * @since  1.0.179
	 */
	class CPCFF_FORM
	{
		/**
		 * Form's id
		 * Instance property.
		 *
		 * @var integer $_id
		 */
		private $_id;

		/**
		 * Form's settings
		 * Instance property.
		 *
		 * @var array $_settings. Associative array with the form's settings.
		 */
		private $_settings;

		/**
		 * Form's fields
		 * Instance property.
		 *
		 * @var array $_fields. Associative array with the form's fields.
		 */
		private $_fields;

		/**
		 * Instance of the CPCFF_REVISIONS object to interact with the form's revisions.
		 */
		private $_revisions_obj;
		/*********************************** PUBLIC METHODS  ********************************************/

		/**
		 * Constructs a CPCFF_FORM object.
		 *
		 * @param integer $id the form's id.
		 */
		public function __construct( $id )
		{
			$this->_id 		 = $id;
			$this->_settings = array();
			$this->_fields 	 = array();
			$this->_revisions_obj = new CPCFF_REVISIONS($this);
		} // End __construct

		/**
		 * Creates a new form with the default data, and the name passed as parameter,
		 * and returns an instance of the CPCFF_FORM class.
		 *
		 * @param string $form_name. The form's name displayed in the settings page of the plugin.
		 *
		 * @return object.
		 */
		static public function create_default( $form_name, $category_name = '', $form_template = 0 )
		{
			global $wpdb, $cpcff_default_texts_array;

			$_form_structure = CP_CALCULATEDFIELDSF_DEFAULT_form_structure;
			// Get form structure from server !!!
			if ( ! empty( $form_template ) ) {
				$response = wp_remote_get(
					'https://cff.dwbooster.com/forms/forms/'.$form_template.'.cpfm',
					array(
						'sslverify' => false
					)
				);

				if ( ! is_wp_error( $response ) ) {
					$_form_structure_tmp = wp_remote_retrieve_body( $response );
					if ( !empty( $_form_structure_tmp ) ) {
						$_form_structure = $_form_structure_tmp;
					}
				}
			}

			if(
				$wpdb->insert(
					$wpdb->prefix.CP_CALCULATEDFIELDSF_FORMS_TABLE,
					array(
						// Form name
						'form_name' => stripcslashes($form_name),

						// Form structure
						'form_structure' => $_form_structure,

						// Notification email
						'fp_from_email' => CP_CALCULATEDFIELDSF_DEFAULT_fp_from_email,
						'fp_destination_emails' => CP_CALCULATEDFIELDSF_DEFAULT_fp_destination_emails,
						'fp_subject' => CP_CALCULATEDFIELDSF_DEFAULT_fp_subject,
						'fp_inc_additional_info' => CP_CALCULATEDFIELDSF_DEFAULT_fp_inc_additional_info,
						'fp_return_page' => CP_CALCULATEDFIELDSF_DEFAULT_fp_return_page,
						'fp_message' => CP_CALCULATEDFIELDSF_DEFAULT_fp_message,

						// Notification email copy to the user
						'cu_enable_copy_to_user' => CP_CALCULATEDFIELDSF_DEFAULT_cu_enable_copy_to_user,
						'cu_user_email_field' => CP_CALCULATEDFIELDSF_DEFAULT_cu_user_email_field,
						'cu_subject' => CP_CALCULATEDFIELDSF_DEFAULT_cu_subject,
						'cu_message' => CP_CALCULATEDFIELDSF_DEFAULT_cu_message,

						// Activate validation and validation's texts
						'vs_use_validation' => CP_CALCULATEDFIELDSF_DEFAULT_vs_use_validation,
						'vs_text_is_required' => CP_CALCULATEDFIELDSF_DEFAULT_vs_text_is_required,
						'vs_text_is_email' => CP_CALCULATEDFIELDSF_DEFAULT_vs_text_is_email,
						'vs_text_datemmddyyyy' => CP_CALCULATEDFIELDSF_DEFAULT_vs_text_datemmddyyyy,
						'vs_text_dateddmmyyyy' => CP_CALCULATEDFIELDSF_DEFAULT_vs_text_dateddmmyyyy,
						'vs_text_number' => CP_CALCULATEDFIELDSF_DEFAULT_vs_text_number,
						'vs_text_digits' => CP_CALCULATEDFIELDSF_DEFAULT_vs_text_digits,
						'vs_text_max' => CP_CALCULATEDFIELDSF_DEFAULT_vs_text_max,
						'vs_text_min' => CP_CALCULATEDFIELDSF_DEFAULT_vs_text_min,
						'vs_all_texts' => serialize($cpcff_default_texts_array),

						// Paypal settings
						'enable_paypal' => CP_CALCULATEDFIELDSF_DEFAULT_ENABLE_PAYPAL,
						'paypal_email' => CP_CALCULATEDFIELDSF_DEFAULT_PAYPAL_EMAIL,
						'request_cost' => 'fieldname1',
						'paypal_product_name' => CP_CALCULATEDFIELDSF_DEFAULT_PRODUCT_NAME,
						'currency' => CP_CALCULATEDFIELDSF_DEFAULT_CURRENCY,
						'paypal_language' => CP_CALCULATEDFIELDSF_DEFAULT_PAYPAL_LANGUAGE,

						// Captcha settings
						'cv_enable_captcha' => CP_CALCULATEDFIELDSF_DEFAULT_cv_enable_captcha,
						'cv_width' => CP_CALCULATEDFIELDSF_DEFAULT_cv_width,
						'cv_height' => CP_CALCULATEDFIELDSF_DEFAULT_cv_height,
						'cv_chars' => CP_CALCULATEDFIELDSF_DEFAULT_cv_chars,
						'cv_font' => CP_CALCULATEDFIELDSF_DEFAULT_cv_font,
						'cv_min_font_size' => CP_CALCULATEDFIELDSF_DEFAULT_cv_min_font_size,
						'cv_max_font_size' => CP_CALCULATEDFIELDSF_DEFAULT_cv_max_font_size,
						'cv_noise' => CP_CALCULATEDFIELDSF_DEFAULT_cv_noise,
						'cv_noise_length' => CP_CALCULATEDFIELDSF_DEFAULT_cv_noise_length,
						'cv_background' => CP_CALCULATEDFIELDSF_DEFAULT_cv_background,
						'cv_border' => CP_CALCULATEDFIELDSF_DEFAULT_cv_border,
						'cv_text_enter_valid_captcha' => CP_CALCULATEDFIELDSF_DEFAULT_cv_text_enter_valid_captcha,

                        'enable_submit' => CP_CALCULATEDFIELDSF_DEFAULT_display_submit_button,
                        'category' => $category_name
					),
					array(
						'%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'
					)
				)
			)
			{
				return new self($wpdb->insert_id);
			}
			return false;
		} // End create_default

        /**
         * Sanitize the form's structure. Fields titltes and instructions for users,
         * form's title and description. etc.
         */
        static public function sanitize_structure($structure)
        {
            if(is_array($structure))
            {
                if(is_array($structure[0]))
                {
                    foreach($structure[0] as $index => $obj)
                    {
                        if(isset($obj->title))
                            $structure[0][$index]->title = CPCFF_AUXILIARY::sanitize($structure[0][$index]->title);
                        if(isset($obj->userhelp))
                            $structure[0][$index]->userhelp = CPCFF_AUXILIARY::sanitize($structure[0][$index]->userhelp);
                    }
                }

                if(is_array($structure[1]) && count($structure[1]))
                {
                    if(is_object($structure[1][0]))
                    {
                        if(isset($structure[1][0]->title))
                            $structure[1][0]->title = CPCFF_AUXILIARY::sanitize($structure[1][0]->title);
                        if(isset($structure[1][0]->description))
                            $structure[1][0]->description = CPCFF_AUXILIARY::sanitize($structure[1][0]->description);
                    }
                }
            }
            return $structure;
        } // End sanitize_structure

		/**
		 * Clones the current form.
		 *
		 * @return mixed, a new instance of the CPCFF_FORM or false.
		 */
		public function clone_form()
		{
			global $wpdb;

			$row = $this->_get_settings();
			if(!empty($row))
			{
				unset($row["id"]);
				$row["form_name"] = 'Cloned: '.$row["form_name"];
				if($wpdb->insert( $wpdb->prefix.CP_CALCULATEDFIELDSF_FORMS_TABLE, $row)) return new self($wpdb->insert_id);
			}
			return false;
		} // End clone_form

		/**
		 * Returns the id of current form.
		 *
		 * @return integer
		 */
		public function get_id()
		{
			return $this->_id;
		} // end get_id

		/**
		 * Reads the corresponding attribute in the form's settings.
		 *
		 * Reads the attribute in the form's settings, and if it does not exists returns the default value.
		 * Applies the filter cpcff_get_option
		 *
		 * @param string, name of the attribute to read.
		 * @param mixes, default value of the attribute.
		 * @return mixed, it depends of the option to read.
		 */
		public function get_option( $option, $default, $extra = '' )
		{
			// Initialize the value with the default values
			$value = $default;
			$this->_get_settings();

			if(isset($this->_settings[$option])) $value = @$this->_settings[$option];

			// If the form's structure is a JSON text decodes it.
			if(
				$option == 'form_structure'  &&
				!is_array( $value )
			)
			{
				$form_data = CPCFF_AUXILIARY::json_decode( $value, 'normal' );
				if(!is_null($form_data))
				{
					$value = $this->_settings['form_structure'] = $form_data;
				}
			}
			// Adds to the thank you page URL a random attribute to prevent the page be cached
			elseif(
				$option == 'fp_return_page' &&
				!is_admin() &&
				get_option( 'CP_CALCULATEDFIELDSF_TYPC', CP_CALCULATEDFIELDSF_TYPC )
			)
			{
				$extra = CPCFF_AUXILIARY::encrypt($extra);
				if(!empty($value)){
					$parts = explode('#', $value);
					$parts[0] .= (strpos($parts[0], '?') === false ? '?' : '&').'cff_no_cache='.base64_encode($extra);
					$value = implode('#', $parts);
				}
			}
			// If the texts where not defined previously or the thank you page is empty populate them with the default values
			elseif (
				(
					$option == 'vs_all_texts' ||
					$option == 'fp_return_page'
				) &&
				empty( $value )
			)
			{
				$value = $default;
			}
			elseif(
				'fp_attach_static' == $option ||
				'cu_attach_static' == $option
			)
			{
				if ( isset( $this->_settings['extra'][ $option ] ) ) {
					$value = $this->_settings['extra'][ $option ];
				} else $value = $default;
			}
			/**
			 * Filters applied before returning a form option,
			 * use three parameters: The value of option, the name of option and the form's id
			 * returns the new option's value
			 */
			if(!is_admin()) $value = apply_filters( 'cpcff_get_option', $value, $option, $this->_id );

			return $value;
		} // End get_option

		/**
		 * Returns the list of fields in the forms.
		 *
		 * @return array, associative array of objects where the fields' names are the indices and fields' structures the values.
		 */
		public function get_fields()
		{
			if(!empty($this->_fields)) return $this->_fields;

			$form_structure = $this->get_option('form_structure', array());
			if(!empty($form_structure[0]))
			{
				foreach($form_structure[0] as $field)
					$this->_fields[$field->name] = $field;
			}
			return $this->_fields;
		} // End get_fields

		public function save_settings( $params )
		{
			global $wpdb, $cpcff_default_texts_array;

            foreach($params as $i => $v)
                if($i != 'form_structure')
                    $params[$i] = CPCFF_AUXILIARY::stripscript_recursive($v);

			$extra = array();

			if( isset( $params['fp_attach_static'] ) ) $extra['fp_attach_static'] = trim( $params['fp_attach_static'] );
			if( isset( $params['cu_attach_static'] ) ) $extra['cu_attach_static'] = trim( $params['cu_attach_static'] );

			$data = array(
				'form_structure' => (isset($params['form_structure'])) ? $params['form_structure'] : CP_CALCULATEDFIELDSF_DEFAULT_form_structure,

				// Notification email
				'fp_from_email' => (isset($params['fp_from_email']))? trim($params['fp_from_email']) : '',
				'fp_destination_emails' => (isset($params['fp_destination_emails'])) ? trim($params['fp_destination_emails']) : '',
				'fp_subject' => (isset($params['fp_subject'])) ? $params['fp_subject'] : '',
				'fp_inc_additional_info' => (isset($params['fp_inc_additional_info']) && $params['fp_inc_additional_info'] == 'true') ? 'true' : 'false',
				'fp_inc_attachments' => (isset($params['fp_inc_attachments']) && $params['fp_inc_attachments'] == 1) ? 1 : 0,
				'fp_return_page' => (isset($params['fp_return_page'])) ? trim($params['fp_return_page']) : '',
				'fp_message' => (isset($params['fp_message'])) ? $params['fp_message'] : '',
				'fp_emailformat' => (isset($params['fp_emailformat']) && $params['fp_emailformat'] == 'text') ? 'text' : 'html',

				// Notification email copy to the user
				'cu_enable_copy_to_user' => (isset($params['cu_enable_copy_to_user']) && $params['cu_enable_copy_to_user'] == 'true') ? 'true' : 'false',
				'cu_user_email_field' => (!empty($params['cu_user_email_field']) && is_array($params['cu_user_email_field']))? implode( ',', $params[ 'cu_user_email_field' ] ) : '',
				'cu_subject' => (isset($params['cu_subject'])) ? $params['cu_subject'] : '',
				'cu_message' => (isset($params['cu_message'])) ? $params['cu_message'] : '',
				'cu_emailformat' => (isset($params['cu_emailformat']) && $params['cu_emailformat'] == 'text') ? 'text' : 'html',
				'fp_emailfrommethod' => (isset($params['fp_emailfrommethod']) && $params['fp_emailfrommethod'] == 'customer') ? 'customer' : 'fixed',

				// PayPal settings
				'enable_paypal' => (isset($params["enable_paypal"])) ? $params["enable_paypal"] : 0,
				'enable_submit' => (isset($params["enable_submit"])) ? $params["enable_submit"] : CP_CALCULATEDFIELDSF_DEFAULT_display_submit_button,
				'paypal_notiemails' => (isset($params["paypal_notiemails"])) ? $params["paypal_notiemails"] : 0,
				'paypal_email' => (isset($params["paypal_email"])) ? trim($params["paypal_email"]) : '',
				'request_cost' => (isset($params["request_cost"])) ? trim($params["request_cost"]) : '',
				'paypal_product_name' => (isset($params["paypal_product_name"])) ? $params["paypal_product_name"] : '',
				'currency' => (isset($params["currency"])) ? trim($params["currency"]) : 'USD',
				'paypal_language' => (isset($params["paypal_language"])) ? trim($params["paypal_language"]) : 'EN',
				'paypal_mode' => (isset($params["paypal_mode"])) ? $params["paypal_mode"] : 'production',
                'donationlayout' => (isset($params["donationlayout"])) ? $params["donationlayout"] : '',
				'paypal_recurrent' => (isset($params["paypal_recurrent"])) ? (($params["paypal_recurrent"] == 'field') ? ((!empty($params["paypal_recurrent_field"])) ? $params["paypal_recurrent_field"] : 0) : $params["paypal_recurrent"]) : 0,
				'paypal_recurrent_setup' => (isset($params["paypal_recurrent_setup"])) ? $params["paypal_recurrent_setup"] : '',
				'paypal_recurrent_setup_days' => (isset($params["paypal_recurrent_setup_days"])) ? $params["paypal_recurrent_setup_days"] : '',
                'paypal_recurrent_times' => (isset($params["paypal_recurrent_times"])) ? $params["paypal_recurrent_times"] : '',
                'paypal_recurrent_times_field' => (isset($params["paypal_recurrent_times_field"])) ? $params["paypal_recurrent_times_field"] : '',
				'paypal_identify_prices' => (isset($params['paypal_identify_prices'])) ? $params['paypal_identify_prices'] : '0',
				'paypal_zero_payment' => (isset($params["paypal_zero_payment"])) ? $params["paypal_zero_payment"] : '0',
				'paypal_base_amount' => (isset($params["paypal_base_amount"])) ? trim($params["paypal_base_amount"]) : '',
				'paypal_address' => (isset($params["paypal_address"])) ? $params["paypal_address"] : 1,

				'enable_paypal_option_yes' => (isset($params['enable_paypal_option_yes'])) ? $params['enable_paypal_option_yes'] : CP_CALCULATEDFIELDSF_PAYPAL_OPTION_YES,
				'enable_paypal_option_no' => (isset($params['enable_paypal_option_no'])) ? $params['enable_paypal_option_no'] : CP_CALCULATEDFIELDSF_PAYPAL_OPTION_NO,

				// Texts
				'vs_use_validation' => CP_CALCULATEDFIELDSF_DEFAULT_vs_use_validation,
				'vs_text_is_required' => (isset($params['vs_text_is_required'])) ? $params['vs_text_is_required'] : CP_CALCULATEDFIELDSF_DEFAULT_vs_text_is_required,
				'vs_text_is_email' => (isset($params['vs_text_is_email'])) ? $params['vs_text_is_email'] : CP_CALCULATEDFIELDSF_DEFAULT_vs_text_is_email,
				'vs_text_datemmddyyyy' => (isset($params['vs_text_datemmddyyyy'])) ? $params['vs_text_datemmddyyyy'] : CP_CALCULATEDFIELDSF_DEFAULT_vs_text_datemmddyyyy,
				'vs_text_dateddmmyyyy' => (isset($params['vs_text_dateddmmyyyy'])) ? $params['vs_text_dateddmmyyyy'] : CP_CALCULATEDFIELDSF_DEFAULT_vs_text_dateddmmyyyy,
				'vs_text_number' => (isset($params['vs_text_number'])) ? $params['vs_text_number'] : CP_CALCULATEDFIELDSF_DEFAULT_vs_text_number,
				'vs_text_digits' => (isset($params['vs_text_digits'])) ? $params['vs_text_digits'] : CP_CALCULATEDFIELDSF_DEFAULT_vs_text_digits,
				'vs_text_max' => (isset($params['vs_text_max'])) ? $params['vs_text_max'] : CP_CALCULATEDFIELDSF_DEFAULT_vs_text_max,
				'vs_text_min' => (isset($params['vs_text_min'])) ? $params['vs_text_min'] : CP_CALCULATEDFIELDSF_DEFAULT_vs_text_min,
				'vs_text_submitbtn' => (isset($params['vs_text_submitbtn'])) ? $params['vs_text_submitbtn'] : 'Submit',
				'vs_text_previousbtn' => (isset($params['vs_text_previousbtn'])) ? $params['vs_text_previousbtn'] : 'Previous',
				'vs_text_nextbtn' => (isset($params['vs_text_nextbtn'])) ? $params['vs_text_nextbtn'] : 'Next',
				'vs_all_texts' => (isset($params['vs_all_texts'])) ? serialize($params['vs_all_texts']) : serialize($cpcff_default_texts_array),

				// Captcha settings
				'cv_enable_captcha' => (isset($params['cv_enable_captcha'])) ? $params['cv_enable_captcha'] : CP_CALCULATEDFIELDSF_DEFAULT_cv_enable_captcha,
				'cv_width' => (isset($params['cv_width'])) ? $params['cv_width'] : CP_CALCULATEDFIELDSF_DEFAULT_cv_width,
				'cv_height' => (isset($params['cv_height'])) ? $params['cv_height'] : CP_CALCULATEDFIELDSF_DEFAULT_cv_height,
				'cv_chars' => (isset($params['cv_chars'])) ? $params['cv_chars'] : CP_CALCULATEDFIELDSF_DEFAULT_cv_chars,
				'cv_font' => (isset($params['cv_font'])) ? $params['cv_font'] : CP_CALCULATEDFIELDSF_DEFAULT_cv_font,
				'cv_min_font_size' => (isset($params['cv_min_font_size'])) ? $params['cv_min_font_size'] : CP_CALCULATEDFIELDSF_DEFAULT_cv_min_font_size,
				'cv_max_font_size' => (isset($params['cv_max_font_size'])) ? $params['cv_max_font_size'] : CP_CALCULATEDFIELDSF_DEFAULT_cv_max_font_size,
				'cv_noise' => (isset($params['cv_noise'])) ? $params['cv_noise'] : CP_CALCULATEDFIELDSF_DEFAULT_cv_noise,
				'cv_noise_length' => (isset($params['cv_noise_length'])) ? $params['cv_noise_length'] : CP_CALCULATEDFIELDSF_DEFAULT_cv_noise_length,
				'cv_background' => (isset($params['cv_background'])) ? $params['cv_background'] : CP_CALCULATEDFIELDSF_DEFAULT_cv_background,
				'cv_border' => (isset($params['cv_border'])) ? $params['cv_border'] : CP_CALCULATEDFIELDSF_DEFAULT_cv_border,
				'cv_text_enter_valid_captcha' => (isset($params['cv_text_enter_valid_captcha'])) ? $params['cv_text_enter_valid_captcha'] : CP_CALCULATEDFIELDSF_DEFAULT_cv_text_enter_valid_captcha,
				'cache' => '',
                'category' => trim(isset($params['calculated-fields-form-category']) ? $params['calculated-fields-form-category'] : ''),
				'extra' => json_encode( $extra )
			);

			$updated_rows =  $wpdb->update (
						$wpdb->prefix.CP_CALCULATEDFIELDSF_FORMS_TABLE,
						$data,
						array( 'id' => $this->_id ),
						array( '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ),
						array( '%d' )
				);

			// Revisions
			update_option('CP_CALCULATEDFIELDSF_REVISIONS_IN_PREVIEW', isset($params['cff-revisions-in-preview']) ? true : false);

			if(
				$updated_rows !== false &&
				(
					!isset($params['preview']) ||
					get_option('CP_CALCULATEDFIELDSF_REVISIONS_IN_PREVIEW')
				) &&
				get_option('CP_CALCULATEDFIELDSF_DISABLE_REVISIONS', CP_CALCULATEDFIELDSF_DISABLE_REVISIONS) == 0
			) $this->_revisions_obj->create_revision();
			return $updated_rows;
		} // End save_settings

		/**
		 * Updates the form's name
		 *
		 * @param string $form_name
		 *
		 * @return bool
		 */
		public function update_name($form_name)
		{
			global $wpdb;
			return $wpdb->update(
				$wpdb->prefix.CP_CALCULATEDFIELDSF_FORMS_TABLE,
				array('form_name'=>$form_name),
				array('id' => $this->_id),
				array('%s'),
				array('%d')
			);
		} // End update_name

		/**
		 * Gets the correspond rown in the database
		 */
		public function get_raw_data()
		{
			global $wpdb;
			$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.CP_CALCULATEDFIELDSF_FORMS_TABLE." WHERE id=%d", $this->_id), ARRAY_A);
			return $row;
		} // End get_raw_data

		/**
		 * Returns an instance of the CPCFF_REVISIONS revisions object
		 */
		public function get_revisions()
		{
			return $this->_revisions_obj;
		} // End get_revisions

		/**
		 * Creates or replace the property $this->_settings with the data stored in the revisions
		 */
		public function apply_revision($revision_id)
		{
			$this->_get_settings();
			$revision_data = $this->_revisions_obj->data($revision_id);
			$this->_fields = array();
			$this->_settings = CPCFF_AUXILIARY::array_replace_recursive($this->_settings,$revision_data);
		} // End apply_revision
		/**
		 * Deletes the current form.
		 *
		 * @return mixed the number of deleted columns or false.
		 */
		public function delete_form()
		{
			global $wpdb;
			$this->_revisions_obj->delete_form();
			return $wpdb->delete(
				$wpdb->prefix.CP_CALCULATEDFIELDSF_FORMS_TABLE,
				array('id' => $this->_id),
				array('%d')
			);
		}

		/**
		 * Exports the form to a .cpfm file.
		 *
		 * Sends to the browser a serialize object with the form's structure and the add-ons related settings.
		 * Sends the corresponding headers to manage the data as an attachment, and exit the PHP.
		 * Applies the filter "cpcff_export_addons"
		 *
		 */
		public function export_form()
		{
			$form = '';
			$row = $this->_get_settings();

			if(!empty($row))
			{
				$row["form_name"] = 'Exported: '.$row["form_name"];
				$addons_array = array();

				/**
				 *	Passes the array with the addons data and the form's id.
				 */
				$addons_array = apply_filters('cpcff_export_addons', $addons_array, $row['id']);
				$row[ 'addons' ] = $addons_array;
				unset($row["id"]);
				$form = serialize($row);
			}
			$dt = date('Y-m-d_His');
			header("Content-type: application/octet-stream");
			header("Content-Disposition: attachment; filename=export_".$this->_id."_".$dt.".cpfm");

			echo $form;
			exit;
		} // End export_form
		/*********************************** PRIVATE METHODS  ********************************************/

		/**
		 * Returns the form's settings.
		 * Checks if the settings were read previously, before reading the data from database.
		 *
		 * @since 1.0.184
		 *
		 * @return array, associative array with the database row.
		 */
		private function _get_settings()
		{
			if(empty($this->_settings))
			{
				$row = $this->get_raw_data();
				if(!empty($row)){
					$this->_settings = $row;
					if( ! empty( $this->_settings['extra'] ) && false != ( $extra = json_decode( $this->_settings['extra'], true ) ) ) {
						$this->_settings['extra'] = $extra;
					}
					else $this->_settings['extra'] = array();
				}
			}
			return $this->_settings;
		} // End _get_settings
	} // End CPCFF_FORM
}