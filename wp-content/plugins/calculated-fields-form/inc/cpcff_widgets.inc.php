<?php
/**
 * Widgets Classes and related code
 *
 * @package CFF.
 * @since 1.0.177
 */

// WIDGET CODE BELOW
// ***********************************************************************
if(!class_exists( 'CPCFF_WIDGET' ) )
{
	/**
	 * Widget class to insert the forms on sidebars extends the WP_Widget class.
	 *
	 * @since  1.0.178
	 */
	class CPCFF_WIDGET extends WP_Widget
	{
		/**
		 * Object of the main plugin's class
		 * Instance property.
		 *
		 * @var object $_cpcff_main
		 */
		private $_cpcff_main;

		/**
		 * Class construct
		 *
		 * @param object $_cpcff_main instance of the CPCFF_MAIN class
		 */
		function __construct()
		{
			$this->_cpcff_main = CPCFF_MAIN::instance();
			$widget_ops = array('classname' => 'CP_calculatedfieldsf_Widget', 'description' => 'Displays a form integrated with Paypal' );
			parent::__construct('CP_calculatedfieldsf_Widget', 'Calculated Fields Form', $widget_ops);
		}

		function form($instance)
		{
			global $wpdb;

			$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'formid' => '', 'class_name' => '', 'attrs' => '' ) );
			$title 	= $instance['title'];
			$formid = $instance['formid'];
			$class_name = $instance['class_name'];
			$attrs 		= $instance['attrs'];

			?>
			<div>
				<label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label>
				<label for="<?php echo $this->get_field_id('formid'); ?>">Form ID: <input class="widefat cff-form-id" id="<?php echo $this->get_field_id('formid'); ?>" name="<?php echo $this->get_field_name('formid'); ?>" type="text" value="<?php echo esc_attr($formid); ?>" /></label>
				<label for="<?php echo $this->get_field_id('class_name'); ?>">Class Name: <input class="widefat" id="<?php echo $this->get_field_id('class_name'); ?>" name="<?php echo $this->get_field_name('class_name'); ?>" type="text" value="<?php echo esc_attr($class_name); ?>" /></label>
				<p><i><?php _e('Enter a class name to be assigned to the form (optional)', 'calculated-fields-form'); ?></i></p>
				<label for="<?php echo $this->get_field_id('attrs'); ?>">Additional Attributes: <input class="widefat" id="<?php echo $this->get_field_id('attrs'); ?>" name="<?php echo $this->get_field_name('attrs'); ?>" type="text" value="<?php echo esc_attr($attrs); ?>" /></label>
				<p><i><?php _e('Pass additional attributes to the form. Ex: attr_name="attr_value" (optional)', 'calculated-fields-form'); ?></i></p>
			<?php
				if ( current_user_can( 'manage_options' ) )
				{
					$url = CPCFF_AUXILIARY::editor_url();
			?>
					<p><input type="button" name="cff-button-edit-form" class="button-primary" value="<?php print esc_attr(__('Edit form', 'calculated-fields-form')); ?>" /></p>
					<script>
						if(typeof jQuery != 'undefined')
						{
							jQuery(document).on('click', '[name="cff-button-edit-form"]', function(){
								var form_id = jQuery.trim(jQuery('.cff-form-id:visible').val())*1;
								if(!isNaN(form_id) && form_id)
								window.open(
									'<?php print $url; ?>'+form_id,
									'_blank'
								);
							});
						}
					</script>
			<?php
				}
			?>
			</div>
			<?php
		}

		function update($new_instance, $old_instance)
		{
			$instance = $old_instance;
			$instance['title'] 		= $new_instance['title'];
			$instance['formid'] 	= $new_instance['formid'];
			$instance['class_name'] = $new_instance['class_name'];
			$instance['attrs'] 		= $new_instance['attrs'];
			return $instance;
		}

		function widget($args, $instance)
		{
			extract($args, EXTR_SKIP);

			echo $before_widget;
			$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
			$formid = empty($instance['formid']) ? 0 : @intval($instance['formid']);
			$class_name = (isset($instance['class_name'])) ? trim($instance['class_name']) : '';
			$attrs = (isset($instance['attrs'])) ? trim($instance['attrs']) : '';

			if (!empty($title))
			  echo $before_title . $title . $after_title;

			if(!empty($formid))
			{
				$shortcode 	= '[CP_CALCULATED_FIELDS id="'.esc_attr($formid).'"';
				if(!empty($class_name)) $shortcode .= ' class="'.esc_attr($class_name).'"';
				if(!empty($attrs)) $shortcode .= ' '.$attrs;
				$shortcode .= ']';

				print do_shortcode($shortcode);
			}
			echo $after_widget;
		}
	} // End CPCFF_WIDGET
}

if(!class_exists('CPCFF_DASHBOART_WIDGET'))
{
	/**
	 * Class to publish a dashboard widget CPCFF_DASHBOART_WIDGET
	 *
	 * @since  1.0.178
	 */
	class CPCFF_DASHBOART_WIDGET
	{
		private $_cpcff_main; // Main object it is not used for now.

		/**
		 * Class construct
		 *
		 * @param object $_cpcff_main instance of the CPCFF_MAIN class
		 */
		public function __construct()
		{
			$this->_cpcff_main = CPCFF_MAIN::instance();

			if( !current_user_can( 'manage_options' ) ) return;
			wp_add_dashboard_widget(
				'cp_calculatedfieldsf_dashboard_widgets',
				'Calculated Fields Form Activity',
				array($this, 'dashboard_widget')
			);
		} // End __construct

		/**
		 * Generates html code to display in the dashboard with the information collected by the form.
		 *
		 * Prints the HTML code directly to the browser.
		 */
		public function dashboard_widget()
		{
			global $wpdb;

			$styleA = 'style="border-right:1px solid rgb(238, 238, 238);border-bottom:1px solid rgb(238, 238, 238);"';
			$styleB = 'style="border-bottom:1px solid rgb(238, 238, 238);"';
			$styleC = 'style="color:#FF0000;"';
			$styleD = 'style="font-weight:bold;"';
			?>
			<div style="max-height:400px; overflow-y:auto;">
			<table style="width:100%;">
				<tr>
					<th align="left" <?php echo $styleA; ?>><?php _e( 'ID', 'calculated-fields-form' ); ?></th>
					<th align="left" <?php echo $styleA; ?>><?php _e( 'Form', 'calculated-fields-form' ); ?></th>
					<th align="left" <?php echo $styleA; ?>><?php _e( 'Date', 'calculated-fields-form' ); ?></th>
					<th align="left" <?php echo $styleA; ?>><?php _e( 'Email', 'calculated-fields-form' ); ?></th>
					<th align="left" <?php echo $styleB; ?>><?php _e( 'Payment Info', 'calculated-fields-form' ); ?></th>
				</tr>
			<?php

			/* TO-DO: This method should be analyzed after moving other functions to the main class . */
			$query = "SELECT ftable.form_name, ptable.* FROM ".$wpdb->prefix.CP_CALCULATEDFIELDSF_FORMS_TABLE." as ftable INNER JOIN ".CP_CALCULATEDFIELDSF_POSTS_TABLE_NAME." as ptable ON (ptable.formid=ftable.id) WHERE ptable.time > CURDATE() - INTERVAL 7 DAY ORDER BY ptable.time DESC;";

			$query = apply_filters('cpcff_results_list_query', $query);
			$query = str_replace('id NOT IN', 'ptable.id NOT IN', $query); // Fixes the queries modified by the Users Permissions

			$submissions_result = $wpdb->get_results( $query );

			foreach( $submissions_result as $row )
			{
				// Add links
				$paypal_post = @unserialize( $row->paypal_post );
				$_urls = '';

				if( $paypal_post !== false )
				{
					foreach( $paypal_post as $_key => $_value )
					{
						if( strpos( $_key, '_url' ) )
						{
							if( is_array( $_value ) )
							{
								foreach( $_value as $_url )
								{
									$_urls .= '<p><a href="'.esc_attr( $_url ).'" target="_blank">'.$_url.'</a></p>';
								}
							}
						}
					}
				}

				echo '
				<tr>
					<td align="left" '.$styleA.'><span '.$styleD.'>'.$row->id.'</span></td>
					<td align="left" '.$styleA.'>
						<a href="admin.php?page=cp_calculated_fields_form&cal='.$row->formid.'&list=1&r='.rand().'&_cpcff_nonce='.wp_create_nonce( 'cff-submissions-list' ).'">'.$row->form_name.'</a>
					</td>
					<td align="left" '.$styleA.'>'.$row->time.'</td>
					<td align="left" '.$styleA.'>'.$row->notifyto.'</td>
					<td align="left" '.$styleB.'>'.( ( $row->paid ) ? __('Paid', 'calculated-fields-form' ) : '<span '.$styleC.'>'.__('Not Paid', 'calculated-fields-form' ).'</span>' ).'</td>
				</tr>
				<tr>
					<td colspan="5"  '.$styleB.' >
					<div class="cff-event-data">
					'.preg_replace(
							'/\n+/',
							'<br>',
							str_replace(
							array( "\'", '\"' ),
							array( "'", '"' ),
							$row->data)
					 ).$_urls.'
					</div>
					</td>
				</tr>
				';
			}
			?>
			</table>
			</div>
			<?php
		} // End dashboard_widget
	} // End CPCFF_DASHBOART_WIDGET
}