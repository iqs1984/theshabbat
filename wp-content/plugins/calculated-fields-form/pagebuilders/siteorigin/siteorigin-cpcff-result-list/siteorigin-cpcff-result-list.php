<?php
/*
Widget Name: Calculated Fields Form Result List Shortcode
Description: Insert the submissions list shortcode on page.
Documentation: https://cff.dwbooster.com/documentation#events-list
*/

class SiteOrigin_CFF_Result_List_Shortcode extends SiteOrigin_Widget
{
	function __construct()
	{
		parent::__construct(
			'siteorigin-cff-result-list-shortcode',
			__('Calculated Fields Form Result List Shortcode', 'calculated-fields-form'),
			array(
				'description' 	=> __('Includes the shortcode for the list of results of the Calculated Fields Form', 'calculated-fields-form'),
				'panels_groups' => array('calculated-fields-form'),
				'help'        	=> 'https://cff.dwbooster.com/documentation#events-list',
			),
			array(),
			array(
				'shortcode' => array(
					'type' => 'tinymce',
					'label' => __( 'Calculated Fields Form Results List Shortcode', 'calculated-fields-form' ),
					'default' => '[CP_CALCULATED_FIELDS_RESULT_LIST formid=""]',
					'rows' => 10,
					'default_editor' => 'html'
				)
			),
			plugin_dir_path(__FILE__)
		);
	} // End __construct

	function get_template_name($instance)
	{
        return 'siteorigin-cff-result-list-shortcode';
    } // End get_template_name

    function get_style_name($instance)
	{
        return '';
    } // End get_style_name

} // End Class SiteOrigin_CFF_Result_List_Shortcode

// Registering the widget
siteorigin_widget_register('siteorigin-cff-result-list-shortcode', __FILE__, 'SiteOrigin_CFF_Result_List_Shortcode');