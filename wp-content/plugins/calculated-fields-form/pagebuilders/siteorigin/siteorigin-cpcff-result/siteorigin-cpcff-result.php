<?php
/*
Widget Name: Calculated Fields Form Result Shortcode
Description: Insert the results shortcode on page.
Documentation: https://cff.dwbooster.com/documentation#thanks-page
*/

class SiteOrigin_CFF_Result_Shortcode extends SiteOrigin_Widget
{
	function __construct()
	{
		parent::__construct(
			'siteorigin-cff-result-shortcode',
			__('Calculated Fields Form Result Shortcode', 'calculated-fields-form'),
			array(
				'description' 	=> __('Includes the shortcode for the results of the Calculated Fields Form', 'calculated-fields-form'),
				'panels_groups' => array('calculated-fields-form'),
				'help'        	=> 'https://cff.dwbooster.com/documentation#thanks-page',
			),
			array(),
			array(
				'shortcode' => array(
					'type' => 'tinymce',
					'label' => __( 'Calculated Fields Form Result Shortcode', 'calculated-fields-form' ),
					'default' => '[CP_CALCULATED_FIELDS_RESULT]',
					'rows' => 10,
					'default_editor' => 'html'
				)
			),
			plugin_dir_path(__FILE__)
		);
	} // End __construct

	function get_template_name($instance)
	{
        return 'siteorigin-cff-result-shortcode';
    } // End get_template_name

    function get_style_name($instance)
	{
        return '';
    } // End get_style_name

} // End Class SiteOrigin_CFF_Result_Shortcode

// Registering the widget
siteorigin_widget_register('siteorigin-cff-result-shortcode', __FILE__, 'SiteOrigin_CFF_Result_Shortcode');