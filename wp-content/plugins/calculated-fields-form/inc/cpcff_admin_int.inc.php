<?php

if ( !is_admin() )
{
    print 'Direct access not allowed.';
    exit;
}

// Required scripts
require_once CP_CALCULATEDFIELDSF_BASE_PATH.'/inc/cpcff_templates.inc.php';

check_admin_referer( 'cff-form-settings', '_cpcff_nonce' );

// Load resources
wp_enqueue_media();
if(function_exists('wp_enqueue_code_editor')) wp_enqueue_code_editor( array( 'type' => 'text/html' ) );
wp_enqueue_style('cff-chosen-css', plugins_url('/vendors/chosen/chosen.min.css', CP_CALCULATEDFIELDSF_MAIN_FILE_PATH));
wp_enqueue_script('cff-chosen-js', plugins_url('/vendors/chosen/chosen.jquery.min.js', CP_CALCULATEDFIELDSF_MAIN_FILE_PATH),array("jquery"));

if (!defined('CP_CALCULATEDFIELDSF_ID'))
    define ('CP_CALCULATEDFIELDSF_ID',intval($_GET["cal"]));

$cpcff_main = CPCFF_MAIN::instance();
$form_obj = $cpcff_main->get_form(intval($_GET["cal"]));

if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_POST['cpcff_revision_to_apply'] ) )
{
	$revision_id = @intval($_POST['cpcff_revision_to_apply']);
	if($revision_id)
	{
		$form_obj->apply_revision($revision_id);
	}
}

if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_POST['cp_calculatedfieldsf_post_options'] ) )
    echo "<div id='setting-error-settings_updated' class='updated settings-error'> <p><strong>".__( 'Settings saved', 'calculated-fields-form' )."</strong></p></div>";

global $cpcff_default_texts_array;
$cpcff_texts_array = $form_obj->get_option( 'vs_all_texts', $cpcff_default_texts_array );
$cpcff_texts_array = CPCFF_AUXILIARY::array_replace_recursive(
        $cpcff_default_texts_array,
        ( is_string( $cpcff_texts_array ) && is_array( unserialize( $cpcff_texts_array ) ) )
            ? unserialize( $cpcff_texts_array )
            : ( ( is_array( $cpcff_texts_array ) ) ? $cpcff_texts_array : array() )
    );
?>
<div class="wrap">
<h1 class="cff-form-name"><?php
	print __( 'Calculated Fields Form', 'calculated-fields-form' ).' <span class="cff-form-name-shortcode">(<b>'.__('Form', 'calculated-fields-form').' '.CP_CALCULATEDFIELDSF_ID.' - '.$form_obj->get_option( 'form_name', '').'</b>) Shortcode: [CP_CALCULATED_FIELDS id="'.CP_CALCULATEDFIELDSF_ID.'"]</span>';

	if(get_option('CP_CALCULATEDFIELDSF_DIRECT_FORM_ACCESS', CP_CALCULATEDFIELDSF_DIRECT_FORM_ACCESS))
	{
		$url = CPCFF_AUXILIARY::site_url();
		$url .= (strpos($url, '?') === false) ? '?'	: '&';
		$url .= 'cff-form='.CP_CALCULATEDFIELDSF_ID;
		print '<br><span style="font-size:14px;font-style:italic;">'.__('Direct form URL', 'calculated-fields-form').': <a href="'.esc_attr($url).'" target="_blank">'.$url.'</a></span>';
	}
?></h1>
<input type="button" name="backbtn" value="<?php esc_attr_e( 'Back to items list...', 'calculated-fields-form' ); ?>" onclick="document.location='admin.php?page=cp_calculated_fields_form';" class="button-secondary" />
&nbsp;&nbsp;|&nbsp;&nbsp;<a href="<?php print esc_attr('admin.php?page=cp_calculated_fields_form&cal='.CP_CALCULATEDFIELDSF_ID.'&list=1&r='.rand().'&_cpcff_nonce='.wp_create_nonce( 'cff-submissions-list' )); ?>"><?php _e( 'Go to submissions', 'calculated-fields-form' ); ?></a>
<br /><br />

<form method="post" action="" id="cpformconf" name="cpformconf" class="cff_form_builder">
<input type="hidden" name="_cpcff_nonce" value="<?php echo wp_create_nonce( 'cff-form-settings' ); ?>" />
<input name="cp_calculatedfieldsf_post_options" type="hidden" value="1" />
<input name="cp_calculatedfieldsf_id" type="hidden" value="<?php echo CP_CALCULATEDFIELDSF_ID; ?>" />


<div id="normal-sortables" class="meta-box-sortables">

 <h2><?php _e( 'Form Settings', 'calculated-fields-form' ); ?>:</h2>
 <!-- Form category -->
 <div class="postbox" >
    <div class="inside">
        <b><?php _e('Form Category', 'calculated-fields-form'); ?></b>
        <input type="text" name="calculated-fields-form-category" class="width75" value="<?php print esc_attr($form_obj->get_option('category', '')); ?>" list="calculated-fields-form-categories" />
        <datalist id="calculated-fields-form-categories">
            <?php
                print $cpcff_main->get_categories('DATALIST');
            ?>
        </datalist>
    </div>
 </div>
 <hr />
 <div><?php _e( '* Different form styles available on the tab Form Settings &gt;&gt; Form Template', 'calculated-fields-form' ); ?></div>
 <div id="metabox_form_structure" class="postbox" >
  <div class="hndle">
	<h3 style="padding:5px;display:inline-block;"><span><?php _e( 'Form Builder', 'calculated-fields-form' ); ?></span></h3>
	<div class="cff-revisions-container">
		<?php
		if(get_option('CP_CALCULATEDFIELDSF_DISABLE_REVISIONS', CP_CALCULATEDFIELDSF_DISABLE_REVISIONS) == 0):
		_e('Revisions','calculated-fields-form');
		?>
			<select name="cff_revision_list">

				<?php
					print '<option value="0">'.esc_html(__('Select a revision', 'calculated-fields-form')).'</option>';
					$revisions_obj = $form_obj->get_revisions();
					$revisions = $revisions_obj->revisions_list();
					foreach($revisions as $revision_id => $revision_data)
					{
						print '<option value="'.esc_attr($revision_id).'">'.esc_html($revision_data['time']).'</option>';
					}
				?>
			</select>
			<input type="button" name="cff_apply_revision" value="<?php print esc_attr('Load Revision', 'calculated-fields-form'); ?>" class="button" style="float:none;" />
		<?php
		endif;
		?>
		<input type="button" name="previewbtn" id="previewbtn2" class="button-primary" value="<?php esc_attr_e( 'Preview', 'calculated-fields-form' ); ?>" onclick="fbuilderjQuery.fbuilder.preview( this );" title="<?php esc_attr_e("Saves the form's structure only, and opens a preview windows", 'calculated-fields-form');?>" />
        <input type="button" name="cff_fields_list" class="button-secondary" value="&#9776;" title="<?php esc_attr_e( 'Fields List', 'calculated-fields-form'); ?>" onclick="fbuilderjQuery.fbuilder.printFields();" />
	</div>
  </div>
  <div class="inside">
     <div class="form-builder-error-messages"><?php
        global $cff_structure_error;
        if( !empty( $cff_structure_error ) )
        {
            echo $cff_structure_error;
        }
     ?></div>
	 <input type="hidden" name="form_structure" id="form_structure" value="<?php print esc_attr(preg_replace('/&quot;/i', '&amp;quot;', json_encode($form_obj->get_option( 'form_structure', CP_CALCULATEDFIELDSF_DEFAULT_form_structure )))); ?>" />
     <input type="hidden" name="templates" id="templates" value="<?php print esc_attr( json_encode( CPCFF_TEMPLATES::load_templates() ) ); ?>" />
     <link href="<?php print esc_attr(plugins_url('/vendors/jquery-ui/jquery-ui.min.css', CP_CALCULATEDFIELDSF_MAIN_FILE_PATH)); ?>" type="text/css" rel="stylesheet" property="stylesheet" />
	 <pre style="display:none;">
     <script type="text/javascript">
		try
		{
			function calculatedFieldsFormReady()
			{
				/* Revisions code */
				$calculatedfieldsfQuery('[name="cff_apply_revision"]').click(
					function(){
						var revision = $calculatedfieldsfQuery('[name="cff_revision_list"]').val();
						if(revision*1)
						{
							result = window.confirm('<?php print esc_js(__('The action will load the revision selected, the data are not stored will be lose. Do you want continue?', 'calculated-fields-form'));?>');
							if(result)
							{
								$calculatedfieldsfQuery('<form method="post" action="" id="cpformconf" name="cpformconf" class="cff_form_builder"><input type="hidden" name="_cpcff_nonce" value="<?php echo wp_create_nonce( 'cff-form-settings' ); ?>" /><input name="cp_calculatedfieldsf_id" type="hidden" value="<?php echo CP_CALCULATEDFIELDSF_ID; ?>" /><input type="hidden" name="cpcff_revision_to_apply" value="'+revision+'"></form>').appendTo('body').submit();
							}
						}
					}
				);
				/* Coupon code */
				$calculatedfieldsfQuery("#dex_dc_expires").datepicker({ dateFormat: 'yy-mm-dd' });

				$calculatedfieldsfQuery('#dex_nocodes_availmsg').load('<?php echo CPCFF_AUXILIARY::wp_url(); ?>/?cp_calculated_fields_form_post=loadcoupons&inAdmin=1&dex_item=<?php echo CP_CALCULATEDFIELDSF_ID; ?>');

				$calculatedfieldsfQuery('#dex_dc_subccode').click (
					function() {
					   var 	code = $calculatedfieldsfQuery('#dex_dc_code').val(),
							discount = $calculatedfieldsfQuery('#dex_dc_discount').val(),
							discounttype = $calculatedfieldsfQuery('#dex_dc_discounttype').val(),
							discounttimes = $calculatedfieldsfQuery('#dex_dc_discounttimes').val(),
							expires = $calculatedfieldsfQuery('#dex_dc_expires').val();

					   if (code == '') { alert('Please enter a code'); return; }
					   if (parseInt(discount)+"" != discount) { alert('Please numeric discount percent'); return; }
					   if (expires == '') { alert('Please enter an expiration date for the code'); return; }
					   var params = '&cff_add_coupon=1&cff_coupon_expires='+encodeURIComponent(expires)+'&cff_discount='+encodeURIComponent(discount)+'&cff_discounttype='+encodeURIComponent(discounttype)+'&cff_discounttimes='+encodeURIComponent(discounttimes)+'&cff_coupon_code='+encodeURIComponent(code);
					   $calculatedfieldsfQuery('#dex_nocodes_availmsg').load('<?php echo CPCFF_AUXILIARY::wp_url(); ?>/?cp_calculated_fields_form_post=loadcoupons&inAdmin=1&dex_item=<?php echo CP_CALCULATEDFIELDSF_ID; ?>'+params);
					   $calculatedfieldsfQuery('#dex_dc_code').val();
					}
				);

				window[ 'dex_delete_coupon' ] = function (id)
				{
					$calculatedfieldsfQuery('#dex_nocodes_availmsg').load('<?php echo CPCFF_AUXILIARY::wp_url(); ?>/?cp_calculated_fields_form_post=loadcoupons&inAdmin=1&dex_item=<?php echo CP_CALCULATEDFIELDSF_ID; ?>&cff_delete_coupon=1&cff_coupon_code='+id);
				}

				// Form builder code

				var f;
				function run_fbuilder($)
				{
					f = $("#fbuilder").fbuilder();
					window['cff_form'] = f;
					f.fBuild.loadData( "form_structure", "templates" );
				};

				if(!('fbuilder' in $calculatedfieldsfQuery.fn))
				{
					$calculatedfieldsfQuery.getScript(
						location.protocol + '//' + location.host + location.pathname+'?page=cp_calculated_fields_form&cp_cff_resources=admin',
						function(){run_fbuilder(fbuilderjQuery);}
					);
				}
				else
				{
					run_fbuilder($calculatedfieldsfQuery);
				}

				$calculatedfieldsfQuery(".itemForm").click(function() {
				   f.fBuild.addItem($calculatedfieldsfQuery(this).attr("id"));
				})
                .draggable({
					connectToSortable: '#fbuilder #fieldlist',
					delay: 100,
					helper: function() {
						var $ = $calculatedfieldsfQuery,
                            e = $(this),
							width = e.outerWidth(),
							text = e.text(),
							type = e.attr('id'),
							el = $('<div class="cff-button-drag '+type+'">');

						return el.html( text ).css( 'width', width ).attr('data-control',type);
					},
					revert: 'invalid',
					cancel: false,
					scroll: false,
					opacity: 1,
					containment: 'document',
                    stop: function(){$calculatedfieldsfQuery('.ctrlsColumn .itemForm').removeClass('button-primary');}
				});
			};
		}
		catch( err ){}
        try{$calculatedfieldsfQuery = jQuery.noConflict();} catch ( err ) {}
	    if (typeof $calculatedfieldsfQuery == 'undefined')
        {
			 if(window.addEventListener){
				window.addEventListener('load', function(){
					try{$calculatedfieldsfQuery = jQuery.noConflict();} catch ( err ) {return;}
					calculatedFieldsFormReady();
				});
			}else{
				window.attachEvent('onload', function(){
					try{$calculatedfieldsfQuery = jQuery.noConflict();} catch ( err ) {return;}
					calculatedFieldsFormReady();
				});
			}
	    }
		else
		{
			$calculatedfieldsfQuery(document).ready( calculatedFieldsFormReady );
		}
     </script>
	 </pre>
     <div style="background:#fafafa;" class="form-builder">

         <div class="column ctrlsColumn">
             <div id="tabs">
				<span class="ui-icon ui-icon-triangle-1-e expand-shrink"></span>
     			<ul>
     				<li><a href="#tabs-1"><?php _e( 'Add a Field', 'calculated-fields-form' ); ?></a></li>
     				<li><a href="#tabs-2"><?php _e( 'Field Settings', 'calculated-fields-form' ); ?></a></li>
     				<li><a href="#tabs-3"><?php _e( 'Form Settings', 'calculated-fields-form' ); ?></a></li>
     			</ul>
     			<div id="tabs-1"></div>
     			<div id="tabs-2"></div>
     			<div id="tabs-3"></div>
     		</div>
         </div>
         <div class="columnr dashboardColumn padding10" id="fbuilder">
             <div id="formheader"></div>
             <div id="fieldlist"></div>
         </div>
         <div class="clearer"></div>

     </div>

  </div>
 </div>

 <p class="submit">
	<input type="submit" name="save" id="save2" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'calculated-fields-form' ); ?>"  title="Saves the form's structure and settings and creates a revision" onclick="<?php
        if(!CPCFF_AutoUpdateClss::valid()) print 'alert(\''.esc_js(CPCFF_AutoUpdateClss::message(true)).'\');'
    ?>" />
	<input type="button" name="previewbtn" id="previewbtn" class="button-primary" value="<?php esc_attr_e( 'Preview', 'calculated-fields-form' ); ?>" onclick="fbuilderjQuery.fbuilder.preview( this );" title="Saves the form's structure only, and opens a preview windows" />
	<?php
	if(get_option('CP_CALCULATEDFIELDSF_DISABLE_REVISIONS', CP_CALCULATEDFIELDSF_DISABLE_REVISIONS) == 0):
	?>
		| <input type="checkbox" name="cff-revisions-in-preview" <?php if(get_option('CP_CALCULATEDFIELDSF_REVISIONS_IN_PREVIEW', true)) print 'CHECKED'; ?> />
	<?php
	_e('Generate revisions in the form preview as well', 'calculated-fields-form');
	endif;
	?>
 </p>
 <div style="margin-bottom:20px;">
    <a href="#metabox_define_texts"><?php _e('Texts definition', 'calculated-fields-form'); ?></a>&nbsp;|&nbsp;
    <a href="#metabox_define_validation_texts"><?php _e('Error texts', 'calculated-fields-form'); ?></a>&nbsp;|&nbsp;
    <a href="#metabox_submit_thank"><?php _e('Submit button and thank you page', 'calculated-fields-form'); ?></a>&nbsp;|&nbsp;
    <a href="#metabox_payment_settings"><?php _e('General payment settings', 'calculated-fields-form'); ?></a>&nbsp;|&nbsp;
    <a href="#metabox_paypal_integration"><?php _e('PayPal integration', 'calculated-fields-form'); ?></a>&nbsp;|&nbsp;
    <a href="#metabox_notification_email"><?php _e('Notification email', 'calculated-fields-form'); ?></a>&nbsp;|&nbsp;
    <a href="#metabox_email_copy_to_user"><?php _e('Email copy to user', 'calculated-fields-form'); ?></a>&nbsp;|&nbsp;
    <a href="#metabox_captcha_settings"><?php _e('Captcha settings', 'calculated-fields-form'); ?></a>&nbsp;|&nbsp;
    <a href="#metabox_addons_section"><?php _e('Add ons', 'calculated-fields-form'); ?></a>
 </div>
 <div id="metabox_define_texts" class="postbox cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_define_texts' ) ); ?>" >
  <h3 class='hndle' style="padding:5px;"><span><?php _e( 'Define Texts', 'calculated-fields-form' ); ?></span></h3>
  <div class="inside">
     <table class="form-table">
        <tr valign="top">
        <th scope="row"><?php _e( 'Submit button label (text)', 'calculated-fields-form' ); ?>:</th>
        <td><input type="text" name="vs_text_submitbtn" class="width75" value="<?php $label = esc_attr($form_obj->get_option('vs_text_submitbtn', 'Submit')); echo ($label==''?'Submit':$label); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row"><?php _e( 'Previous button label (text)', 'calculated-fields-form' ); ?>:</th>
        <td><input type="text" name="vs_text_previousbtn" class="width75" value="<?php $label = esc_attr($form_obj->get_option('vs_text_previousbtn', 'Previous')); echo ($label==''?'Previous':$label); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row"><?php _e( 'Next button label (text)', 'calculated-fields-form' ); ?>:</th>
        <td><input type="text" name="vs_text_nextbtn" class="width75" value="<?php $label = esc_attr($form_obj->get_option('vs_text_nextbtn', 'Next')); echo ($label==''?'Next':$label); ?>" /></td>
        </tr>
        <tr valign="top">
        <td colspan="2"><?php _e( ' - The  <em style="font-size:11px;">class="pbSubmit"</em> can be used to modify the button styles.', 'calculated-fields-form' ); ?> <br />
        <?php _e( '- The styles can be applied into the "Customize Form Appearance" attribute in the "Form Settings" tab', 'calculated-fields-form' ); ?> <b>&#10548;</b> <br />
        <?php _e( '- For general CSS styles modifications to the form and samples <a href="https://cff.dwbooster.com/faq#q82" target="_blank">check this FAQ</a>.', 'calculated-fields-form' ); ?></td>
		</tr>
        <?php
         // Display all other text fields
         foreach( $cpcff_texts_array as $cpcff_text_index => $cpcff_text_attr )
         {
			if( $cpcff_text_index !== 'errors' && isset($cpcff_text_attr[ 'label' ]) )
			{
				print '
				<tr valign="top">
					<th scope="row">'.$cpcff_text_attr[ 'label' ].':</th>
					<td><input type="text" name="cpcff_text_array['.$cpcff_text_index.'][text]" class="width75" value="'. esc_attr( $cpcff_text_attr[ 'text' ] ).'" /></td>
				</tr>
				';
			}
         }
        ?>
     </table>
     <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
  </div>
 </div>

 <div id="metabox_define_validation_texts" class="postbox cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_define_validation_texts' ) ); ?>" >
  <h3 class='hndle' style="padding:5px;"><span><?php _e( 'Validation Settings', 'calculated-fields-form' ); ?></span></h3>
  <div class="inside">
     <table class="form-table">
        <tr valign="top">
        <th scope="row"><?php _e( '"is required" text', 'calculated-fields-form' ); ?>:</th>
        <td><input type="text" name="vs_text_is_required" class="width75" value="<?php echo esc_attr($form_obj->get_option('vs_text_is_required', CP_CALCULATEDFIELDSF_DEFAULT_vs_text_is_required)); ?>" /></td>
        </tr>
         <tr valign="top">
        <th scope="row"><?php _e( '"is email" text', 'calculated-fields-form' ); ?>:</th>
        <td><input type="text" name="vs_text_is_email" class="width75" value="<?php echo esc_attr($form_obj->get_option('vs_text_is_email', CP_CALCULATEDFIELDSF_DEFAULT_vs_text_is_email)); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row"><?php _e( '"is valid captcha" text', 'calculated-fields-form' ); ?>:</th>
        <td><input type="text" name="cv_text_enter_valid_captcha" class="width75" value="<?php echo esc_attr($form_obj->get_option('cv_text_enter_valid_captcha', CP_CALCULATEDFIELDSF_DEFAULT_cv_text_enter_valid_captcha)); ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row"><?php _e( '"is valid date (mm/dd/yyyy)" text', 'calculated-fields-form' ); ?>:</th>
        <td><input type="text" name="vs_text_datemmddyyyy" class="width75" value="<?php echo esc_attr($form_obj->get_option('vs_text_datemmddyyyy', CP_CALCULATEDFIELDSF_DEFAULT_vs_text_datemmddyyyy)); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row"><?php _e( '"is valid date (dd/mm/yyyy)" text', 'calculated-fields-form' ); ?>:</th>
        <td><input type="text" name="vs_text_dateddmmyyyy" class="width75" value="<?php echo esc_attr($form_obj->get_option('vs_text_dateddmmyyyy', CP_CALCULATEDFIELDSF_DEFAULT_vs_text_dateddmmyyyy)); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row"><?php _e( '"is number" text', 'calculated-fields-form' ); ?>:</th>
        <td><input type="text" name="vs_text_number" class="width75" value="<?php echo esc_attr($form_obj->get_option('vs_text_number', CP_CALCULATEDFIELDSF_DEFAULT_vs_text_number)); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row"><?php _e( '"only digits" text', 'calculated-fields-form' ); ?>:</th>
        <td><input type="text" name="vs_text_digits" class="width75" value="<?php echo esc_attr($form_obj->get_option('vs_text_digits', CP_CALCULATEDFIELDSF_DEFAULT_vs_text_digits)); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row"><?php _e( '"under maximum" text', 'calculated-fields-form' ); ?>:</th>
        <td><input type="text" name="vs_text_max" class="width75" value="<?php echo esc_attr($form_obj->get_option('vs_text_max', CP_CALCULATEDFIELDSF_DEFAULT_vs_text_max)); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row"><?php _e( '"over minimum" text', 'calculated-fields-form' ); ?>:</th>
        <td><input type="text" name="vs_text_min" class="width75" value="<?php echo esc_attr($form_obj->get_option('vs_text_min', CP_CALCULATEDFIELDSF_DEFAULT_vs_text_min)); ?>" /></td>
        </tr>
		<?php
		// Display all other text fields
		if( !empty( $cpcff_texts_array[ 'errors' ] ) )
		{
			foreach( $cpcff_texts_array[ 'errors' ] as $cpcff_text_index => $cpcff_text_attr )
			{
				if(isset($cpcff_text_attr[ 'label' ]))
                {
                    print '
                    <tr valign="top">
                        <th scope="row">'.$cpcff_text_attr[ 'label' ].':</th>
                        <td><input type="text" name="cpcff_text_array[errors]['.$cpcff_text_index.'][text]" class="width75" value="'. esc_attr( $cpcff_text_attr[ 'text' ] ).'" /></td>
                    </tr>
                    ';
                }
			}
		}
        ?>
     </table>
     <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
  </div>
 </div>

 <h2><?php _e( 'Form Processing and Payment Settings', 'calculated-fields-form' ); ?>:</h2>
 <hr />

<div id="metabox_submit_thank" class="postbox cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_submit_thank' ) ); ?>" >
	<h3 class='hndle' style="padding:5px;"><span><?php _e( 'Submit Button and Thank You Page', 'calculated-fields-form' ); ?></span></h3>
	<div class="inside">
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e( 'Display submit button?', 'calculated-fields-form' ); ?></th>
				<td>
					<?php
                    $option = $form_obj->get_option('enable_submit',CP_CALCULATEDFIELDSF_DEFAULT_display_submit_button);
                    ?>
					<select name="enable_submit">
					<option value=""<?php if ($option == '') echo ' selected'; ?>><?php _e( 'Yes', 'calculated-fields-form' ); ?></option>
					<option value="no"<?php if ($option == 'no') echo ' selected'; ?>><?php _e( 'No', 'calculated-fields-form' ); ?></option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e( 'Thank you page (after sending the message)', 'calculated-fields-form' ); ?></th>
				<td><input type="text" name="fp_return_page" class="width75" value="<?php echo esc_attr($form_obj->get_option('fp_return_page', CP_CALCULATEDFIELDSF_DEFAULT_fp_return_page)); ?>" /></td>
			</tr>
		</table>
        <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
	</div>
</div>

 <div id="metabox_payment_settings" class="postbox cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_payment_settings' ) ); ?>">
  <h3 class='hndle' style="padding:5px;"><span><?php _e( 'Payment Settings', 'calculated-fields-form' ); ?></span></h3>
  <div class="inside">

    <table class="form-table">
        <tr valign="top">
        <th scope="row"><?php _e( 'Request cost', 'calculated-fields-form' ); ?></th>
        <td><select name="request_cost" id="request_cost" def="<?php echo esc_attr($form_obj->get_option('request_cost', '')); ?>" class="width75"></select></td>
        </tr>


        <tr valign="top">
        <th scope="row"><?php _e( 'Currency', 'calculated-fields-form' ); ?></th>
        <td><input type="text" name="currency" value="<?php echo esc_attr($form_obj->get_option('currency',CP_CALCULATEDFIELDSF_DEFAULT_CURRENCY)); ?>" /><br>
		<b>USD</b> (<?php esc_html_e('United States dollar', 'calculated-fields-form'); ?>), <b>EUR</b> (Euro), <b>GBP</b> (<?php esc_html_e('Pound sterling', 'calculated-fields-form'); ?>), ... (<a href="https://developer.paypal.com/docs/api/reference/currency-codes/" target="_blank"><?php esc_html_e('PayPal Currency Codes', 'calculated-fields-form');?></a>)
		</td>
        </tr>

        <tr valign="top">
        <th scope="row"><?php _e( 'Base amount', 'calculated-fields-form' ); ?>:</th>
        <td><input type="text" name="paypal_base_amount" value="<?php echo esc_attr($form_obj->get_option( 'paypal_base_amount', '0.01' ) ); ?>" /><br><i style="font-size:11px;"><?php _e( 'Minimum amount to charge. If the final price is lesser than this number, the base amount will be applied.', 'calculated-fields-form' ); ?></i>
        </td>
        </tr>

        <tr valign="top">
        <th scope="row"><?php _e( 'Product name', 'calculated-fields-form' ); ?></th>
        <td><input type="text" name="paypal_product_name" class="width75" value="<?php echo esc_attr($form_obj->get_option('paypal_product_name',CP_CALCULATEDFIELDSF_DEFAULT_PRODUCT_NAME)); ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row"><?php _e( 'Label for the "<strong>Pay later</strong>" option (for optional payments)', 'calculated-fields-form' ); ?>:</th>
        <td><input type="text" name="enable_paypal_option_no" class="width75" value="<?php echo esc_attr($form_obj->get_option('enable_paypal_option_no',CP_CALCULATEDFIELDSF_PAYPAL_OPTION_NO)); ?>" /></th>
        </th>
        </tr>

        <tr valign="top">
        <td colspan="2">
			<strong><?php _e( 'Discount Codes', 'calculated-fields-form' ); ?></strong><br /><br />
			<div id="dex_nocodes_availmsg"><?php _e( 'Loading...', 'calculated-fields-form' ); ?></div>
		</td>
		</tr>

        <tr valign="top">
		<td colspan="2">
			<strong><?php _e( 'Add new discount code', 'calculated-fields-form' ); ?>:</strong><br />
			<table class="form-table cff-add-discount">
				<tr>
					<td><?php _e( 'Code', 'calculated-fields-form' ); ?>:</td>
					<td><?php _e( 'Discount', 'calculated-fields-form' ); ?>:</td>
					<td><?php _e( 'Can be used', 'calculated-fields-form' ); ?>:</td>
					<td><?php _e( 'Valid until', 'calculated-fields-form' ); ?>:</td>
					<td></td>
				</tr>
				<tr>
					<td><input type="text" name="dex_dc_code" id="dex_dc_code" value="" /></td>
					<td>
						<nobr>
						<input type="text" size="3" name="dex_dc_discount" id="dex_dc_discount"  value="25" /><select name="dex_dc_discounttype" id="dex_dc_discounttype"><option value="0"><?php _e( 'Percent', 'calculated-fields-form' ); ?></option><option value="1"><?php _e( 'Fixed Value', 'calculated-fields-form' ); ?></option></select>
						</nobr>
					</td>
					<td>
						<select name="dex_dc_discounttimes" id="dex_dc_discounttimes">
						   <?php
								$times = 0;
								$times_suffix = '';
								while ( $times <= 10000) {
									print '<option value="' . esc_attr( $times ) . '">' . ( $times ? $times . $times_suffix : esc_html__( 'Unlimited', 'calculated-fields-form' ) ). '</option>';
									$times_suffix = ' ' . esc_html__( 'Times', 'calculated-fields-form' );
									if ( 500 <= $times ) {
										$times += 50;
										$times_suffix = '';
									} elseif ( 20 <= $times ) {
										$times += 10;
									} else {
										$times++;
									}
								}
						   ?>
						</select>
					</td>
					<td><input type="text"  size="10" name="dex_dc_expires" id="dex_dc_expires" value="" /></td>
					<td><input type="button" name="dex_dc_subccode" id="dex_dc_subccode" value="<?php esc_attr_e( 'Add', 'calculated-fields-form' ); ?>" class="button-secondary" /></td>
				</tr>
		   </table>
           <em style="font-size:11px;"><?php _e( 'Note: Expiration date based in server time. Server time now is', 'calculated-fields-form' ); ?> <?php echo date("Y-m-d H:i"); ?></em>
		   <hr />
        </td>
        </tr>

        <tr valign="top">
        <th scope="row"><?php _e( 'When should be sent the notification-confirmation emails?', 'calculated-fields-form' ); ?></th>
        <td><select name="paypal_notiemails" class="width75">
             <option value="0" <?php if ($form_obj->get_option('paypal_notiemails','0') != '0') echo 'selected'; ?>><?php _e( 'When paid: AFTER receiving the payment notification', 'calculated-fields-form' ); ?></option>
             <option value="1" <?php if ($form_obj->get_option('paypal_notiemails','1') == '1') echo 'selected'; ?>><?php _e( 'Always: BEFORE receiving the payment notification', 'calculated-fields-form' ); ?></option>
            </select>
        </td>
        </tr>

     </table>
     <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
  </div>
</div>

 <div id="metabox_paypal_integration" class="postbox cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_paypal_integration' ) ); ?>">
  <h3 class='hndle' style="padding:5px;"><span><?php _e( 'Paypal Payment Configuration', 'calculated-fields-form' ); ?></span></h3>
  <div class="inside">

    <table class="form-table">
        <tr valign="top">
        <th scope="row"><?php _e( 'Enable Paypal Payments?', 'calculated-fields-form' ); ?></th>
        <td><select name="enable_paypal">
             <option value="0" <?php if (!$form_obj->get_option('enable_paypal',CP_CALCULATEDFIELDSF_DEFAULT_ENABLE_PAYPAL)) echo 'selected'; ?> ><?php _e( 'No', 'calculated-fields-form' ); ?></option>
             <option value="1" <?php if ($form_obj->get_option('enable_paypal',CP_CALCULATEDFIELDSF_DEFAULT_ENABLE_PAYPAL) == '1') echo 'selected'; ?> ><?php _e( 'Yes', 'calculated-fields-form' ); ?></option>
             <option value="2" <?php if ($form_obj->get_option('enable_paypal',CP_CALCULATEDFIELDSF_DEFAULT_ENABLE_PAYPAL) == '2') echo 'selected'; ?> ><?php _e( 'Optional: This payment method + Pay Later', 'calculated-fields-form' ); ?></option>
            </select>
            <br /><i style="font-size:11px;"><?php _e( 'Note: If "Optional" is selected, a radiobutton will appear in the form to select if the payment will be made with PayPal or not.', 'calculated-fields-form' ); ?></i>
            <div id="cff_paypal_options_label" style="margin-top:10px;background:#EEF5FB;border: 1px dotted #888888;padding:10px;width:260px;">
              <?php _e( 'Label for the "<strong>Pay with PayPal</strong>" option', 'calculated-fields-form' ); ?>:<br />
              <input type="text" name="enable_paypal_option_yes" size="70" style="width:250px;" value="<?php echo esc_attr($form_obj->get_option('enable_paypal_option_yes',CP_CALCULATEDFIELDSF_PAYPAL_OPTION_YES)); ?>" />
            </div>
         </td>
        </tr>

        <tr valign="top">
        <th scope="row"><?php _e( 'Paypal Mode', 'calculated-fields-form' ); ?></th>
        <td><select name="paypal_mode" class="width75">
             <option value="production" <?php if ($form_obj->get_option('paypal_mode',CP_CALCULATEDFIELDSF_DEFAULT_PAYPAL_MODE) != 'sandbox') echo 'selected'; ?>><?php _e( 'Production - real payments processed', 'calculated-fields-form' ); ?></option>
             <option value="sandbox" <?php if ($form_obj->get_option('paypal_mode',CP_CALCULATEDFIELDSF_DEFAULT_PAYPAL_MODE) == 'sandbox') echo 'selected'; ?>><?php _e( 'SandBox - PayPal testing sandbox area', 'calculated-fields-form' ); ?></option>
            </select>
        </td>
        </tr>

        <tr valign="top">
        <th scope="row"><?php _e( 'Enable donation layout?', 'calculated-fields-form' ); ?></th>
        <td><select name="donationlayout">
             <option value="0" <?php if ($form_obj->get_option('donationlayout','') != '1') echo 'selected'; ?>><?php _e( 'No', 'calculated-fields-form' ); ?></option>
             <option value="1" <?php if ($form_obj->get_option('donationlayout','') == '1') echo 'selected'; ?>><?php _e( 'Yes', 'calculated-fields-form' ); ?></option>
            </select>
		</td>
        </tr>

        <tr valign="top">
        <th scope="row"><?php _e( 'Paypal email', 'calculated-fields-form' ); ?></th>
        <td><input type="text" name="paypal_email" class="width75" value="<?php echo esc_attr($form_obj->get_option('paypal_email',CP_CALCULATEDFIELDSF_DEFAULT_PAYPAL_EMAIL)); ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row"><?php _e( 'A $0 amount to pay means', 'calculated-fields-form' ); ?>:</th>
        <td><select name="paypal_zero_payment" class="width75">
             <option value="0" <?php if ($form_obj->get_option('paypal_zero_payment',CP_CALCULATEDFIELDSF_DEFAULT_PAYPAL_ZERO_PAYMENT) != '1') echo 'selected'; ?>><?php _e( 'Let the user enter any amount at PayPal (ex: for a donation)', 'calculated-fields-form' ); ?></option>
             <option value="1" <?php if ($form_obj->get_option('paypal_zero_payment',CP_CALCULATEDFIELDSF_DEFAULT_PAYPAL_ZERO_PAYMENT) == '1') echo 'selected'; ?>><?php _e( 'Don\'t require any payment. Form is submitted skiping the PayPal page.', 'calculated-fields-form' ); ?></option>
			</select>
        </td>
        </tr>

        <tr valign="top">
        <th scope="row"><?php _e( 'Paypal language', 'calculated-fields-form' ); ?></th>
        <td><input type="text" name="paypal_language" value="<?php echo esc_attr($form_obj->get_option('paypal_language',CP_CALCULATEDFIELDSF_DEFAULT_PAYPAL_LANGUAGE)); ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row"><?php _e( 'Payment frequency', 'calculated-fields-form' ); ?></th>
        <td><select name="paypal_recurrent" id="paypal_recurrent" onchange="cfwpp_update_recurrent();" class="width75">
             <option value="0" <?php if ($form_obj->get_option('paypal_recurrent',CP_CALCULATEDFIELDSF_DEFAULT_PAYPAL_RECURRENT) == '0' ||
                                         $form_obj->get_option('paypal_recurrent',CP_CALCULATEDFIELDSF_DEFAULT_PAYPAL_RECURRENT) == ''
                                        ) echo 'selected'; ?>><?php _e( 'One time payment (default option, user is billed only once)', 'calculated-fields-form' ); ?></option>
             <option value="1" <?php if ($form_obj->get_option('paypal_recurrent',CP_CALCULATEDFIELDSF_DEFAULT_PAYPAL_RECURRENT) == '1') echo 'selected'; ?>><?php _e( 'Bill the user every 1 month', 'calculated-fields-form' ); ?></option>
             <option value="3" <?php if ($form_obj->get_option('paypal_recurrent',CP_CALCULATEDFIELDSF_DEFAULT_PAYPAL_RECURRENT) == '3') echo 'selected'; ?>><?php _e( 'Bill the user every 3 months', 'calculated-fields-form' ); ?></option>
             <option value="6" <?php if ($form_obj->get_option('paypal_recurrent',CP_CALCULATEDFIELDSF_DEFAULT_PAYPAL_RECURRENT) == '6') echo 'selected'; ?>><?php _e( 'Bill the user every 6 months', 'calculated-fields-form' ); ?></option>
             <option value="12" <?php if ($form_obj->get_option('paypal_recurrent',CP_CALCULATEDFIELDSF_DEFAULT_PAYPAL_RECURRENT) == '12') echo 'selected'; ?>><?php _e( 'Bill the user every 12 months', 'calculated-fields-form' ); ?></option>
			 <option value="field" <?php if ( strpos( $form_obj->get_option('paypal_recurrent',CP_CALCULATEDFIELDSF_DEFAULT_PAYPAL_RECURRENT),'field' ) !== false ) echo 'selected'; ?>><?php _e( 'From field', 'calculated-fields-form' ); ?></option>
            </select>
			<select name="paypal_recurrent_field" style="display:<?php echo ( ( strpos( $form_obj->get_option('paypal_recurrent',CP_CALCULATEDFIELDSF_DEFAULT_PAYPAL_RECURRENT),'field' ) !== false ) ? 'inline-block' : 'none' ); ?>; margin:10px 0;" def="<?php echo esc_attr($form_obj->get_option('paypal_recurrent',CP_CALCULATEDFIELDSF_DEFAULT_PAYPAL_RECURRENT)); ?>" class="width75">
			</select>

			<!-- START:: added for first recurrent period -->
			<div id="cfwpp_setupfee" style="width:350px;margin-top:5px;padding:5px;background-color:#ddddff;display:none;border:1px dotted black;">
				<?php _e( 'First period price (ex: include setup fee here if any)', 'calculated-fields-form' ); ?>:<br />
				<input type="text" name="paypal_recurrent_setup" class="large" value="<?php echo esc_attr($form_obj->get_option('paypal_recurrent_setup','')); ?>" /><br />
				<?php _e( 'First period interval in days', 'calculated-fields-form' ); ?>:<br />
				<input type="text" name="paypal_recurrent_setup_days" class="large" value="<?php echo esc_attr($form_obj->get_option('paypal_recurrent_setup_days','15')); ?>" />
                <!-- recurrent times: start -->
                <?php $selnum = $form_obj->get_option('paypal_recurrent_times','0'); ?>
                <br /><?php _e('Number of times that subscription payments recur (keep "Unlimited" in most cases)','calculated-fields-form'); ?>:<br />
                <select name="paypal_recurrent_times" id="paypal_recurrent_times"  onchange="cfwpp_update_recurrent_field();" class="large" >
                   <option value="0" <?php if ($selnum == '0') echo 'selected'; ?>><?php _e('Unlimited','calculated-fields-form'); ?></option>
                   <option value="-1" <?php if ($selnum == '-1') echo 'selected'; ?>><?php _e('Get value from a form field','calculated-fields-form'); ?></option>
                   <?php for ($kt=2; $kt<=52; $kt++) { ?>
                     <option value="<?php echo $kt; ?>" <?php if ($selnum == $kt."") echo 'selected'; ?>><?php echo $kt; ?> <?php _e('times','calculated-fields-form'); ?></option>
                   <?php } ?>
                </select>
                <div id="recurrfield" style="display:none">
                  <?php _e( 'Which field?', 'calculated-fields-form' ); ?>:<br />
				  <input type="text" placeholder="fieldname#" name="paypal_recurrent_times_field" class="large" value="<?php echo esc_attr($form_obj->get_option('paypal_recurrent_times_field','')); ?>" />
                </div>
                <!-- recurrent times: end -->
            </div>
            <script type="text/javascript">
				var cff_metabox_nonce = '<?php print esc_js( wp_create_nonce( 'cff-metabox-status' ) ); ?>';
				function cfwpp_update_recurrent() {
					var f = document.getElementById( 'paypal_recurrent' );
					if( f.options[ f.options.selectedIndex ].value != '0' )
					{
						document.getElementById( 'cfwpp_setupfee' ).style.display = '';
					}
					else
					{
						document.getElementById( 'cfwpp_setupfee' ).style.display = 'none';
					}
				}
				cfwpp_update_recurrent();
                function cfwpp_update_recurrent_field() {
					var f = document.getElementById( 'paypal_recurrent_times' );
					if( f.options[ f.options.selectedIndex ].value == "-1" )
					{
						document.getElementById( 'recurrfield' ).style.display = '';
					}
					else
					{
						document.getElementById( 'recurrfield' ).style.display = 'none';
					}
				}
				cfwpp_update_recurrent_field();
            </script>
            <!-- END:: added for first recurrent period -->
        </td>
        </tr>
		<tr valign="top">
        <th scope="row"><?php _e( 'Paypal prompt buyers for shipping address', 'calculated-fields-form' ); ?></th>
        <td>
			<?php $paypal_address = $form_obj->get_option('paypal_address', 1); ?>
			<select name="paypal_address" class="width75">
				<option value="1" <?php if($paypal_address == 1) print 'SELECTED'; ?>><?php _e('Do not prompt for an address', 'calculated-fields-form'); ?></option>
				<option value="0" <?php if($paypal_address == 0) print 'SELECTED'; ?>><?php _e('Prompt for an address, but do not require one', 'calculated-fields-form'); ?></option>
				<option value="2" <?php if($paypal_address == 2) print 'SELECTED'; ?>><?php _e('Prompt for an address and require one', 'calculated-fields-form'); ?></option>
			</select>
		</td>
        </tr>
     </table>
     <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
  </div>
 </div>

 <div style="border:1px solid #F0AD4E;background:#FBE6CA;padding:10px;color:#3c434a;margin-bottom:20px;">
	<p><?php
		esc_html_e(
			'If you or your users do not receive the notification emails, they are probably being blocked by the web server. If so, install any of the SMTP connection plugins distributed through the WordPress directory, and configure it to use your hosting provider\'s SMTP server.',
			'calculated-fields-form'
		);
	?></p>
	<p><a href="https://wordpress.org/plugins/search/SMTP+Connection/" target="_blank">https://wordpress.org/plugins/search/SMTP+Connection/</a></p>
 </div>

 <div id="metabox_notification_email" class="postbox cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_notification_email' ) ); ?>" >
  <h3 class='hndle' style="padding:5px;"><span><?php _e( 'Form Processing / Email Settings', 'calculated-fields-form' ); ?></span></h3>
  <div class="inside">
     <table class="form-table">

        <tr valign="top">
        <th scope="row"><?php _e( 'Send email "From"', 'calculated-fields-form' ); ?> </th>
        <td>
          <?php $option = $form_obj->get_option('fp_emailfrommethod', "fixed"); ?>
           <select name="fp_emailfrommethod" class="width75">
             <option value="fixed"<?php if ($option == 'fixed') echo ' selected'; ?>><?php _e( 'From fixed email address indicated below - Recommended option', 'calculated-fields-form' ); ?></option>
             <option value="customer"<?php if ($option == 'customer') echo ' selected'; ?>><?php _e( 'From the email address indicated by the customer', 'calculated-fields-form' ); ?></option>
            </select><br />
            <i style="font-size:11px;">
            <?php _e( '* If you select "from fixed..." the customer email address will appear in the "to" address when you hit "reply", this is the recommended setting to avoid mail server restrictions.', 'calculated-fields-form' ); ?>
            <br />
            <?php _e( '* If you select "from customer email" then the customer email will appear also visually when you receive the email, but this isn\'t supported by all hosting services, so this option isn\'t recommended in most cases.', 'calculated-fields-form' ); ?>
            </i>
        </td>
        </tr>


        <tr valign="top">
        <th scope="row"><?php _e( '"From" email', 'calculated-fields-form');?></th>
        <td><input type="text" name="fp_from_email" class="width75" value="<?php echo esc_attr($form_obj->get_option('fp_from_email', CP_CALCULATEDFIELDSF_DEFAULT_fp_from_email)); ?>" /><br><b><em style="font-size:11px;">Ex: admin@<?php echo str_replace('www.','',$_SERVER["HTTP_HOST"]); ?></em></b><br><em style="font-size:11px;"><?php _e( 'This email is required if the "From fixed email address" option is selected, or it is enabled the email copy to the user.', 'calculated-fields-form' ); ?></em></td>
        </tr>
        <tr valign="top">
        <th scope="row"><?php _e( 'Destination emails (comma separated)', 'calculated-fields-form' ); ?></th>
        <td><input type="text" name="fp_destination_emails" class="width75" value="<?php echo esc_attr($form_obj->get_option('fp_destination_emails', CP_CALCULATEDFIELDSF_DEFAULT_fp_destination_emails)); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row"><?php _e( 'Email subject', 'calculated-fields-form' ); ?></th>
        <td><input type="text" name="fp_subject" class="width75" value="<?php echo esc_attr($form_obj->get_option('fp_subject', CP_CALCULATEDFIELDSF_DEFAULT_fp_subject)); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row"><?php _e( 'Include additional information?', 'calculated-fields-form' ); ?></th>
        <td>
          <?php $option = $form_obj->get_option('fp_inc_additional_info', CP_CALCULATEDFIELDSF_DEFAULT_fp_inc_additional_info); ?>
          <select name="fp_inc_additional_info">
           <option value="true"<?php if ($option == 'true') echo ' selected'; ?>><?php _e( 'Yes', 'calculated-fields-form' ); ?></option>
           <option value="false"<?php if ($option == 'false') echo ' selected'; ?>><?php _e( 'No', 'calculated-fields-form' ); ?></option>
          </select>&nbsp;<em style="font-size:11px;"><?php _e('If the "No" option is selected the plugin won\'t capture the IP address of users.','calculated-fields-form'); ?></em>
        </td>
        </tr>
		<tr valign="top">
        <th scope="row"><?php _e( 'Include attachments?', 'calculated-fields-form' ); ?></th>
        <td>
		<select name="fp_inc_attachments">
             <option value="0" <?php if ($form_obj->get_option('fp_inc_attachments',0) != '1') echo 'selected'; ?>><?php _e( 'No', 'calculated-fields-form' ); ?></option>
             <option value="1" <?php if ($form_obj->get_option('fp_inc_attachments',0) == '1') echo 'selected'; ?>><?php _e( 'Yes', 'calculated-fields-form' ); ?></option>
            </select>
		</td>
        </tr>
        <tr valign="top">
        <th scope="row"><?php _e( 'Email format?', 'calculated-fields-form' ); ?></th>
        <td>
          <?php $option = $form_obj->get_option('fp_emailformat', CP_CALCULATEDFIELDSF_DEFAULT_email_format); ?>
          <select name="fp_emailformat" class="width75">
           <option value="text"<?php if ($option != 'html') echo ' selected'; ?>><?php _e( 'Plain Text (default)', 'calculated-fields-form' ); ?></option>
           <option value="html"<?php if ($option == 'html') echo ' selected'; ?>><?php _e( 'HTML (use html in the textarea below)', 'calculated-fields-form' ); ?></option>
          </select>
        </td>
        </tr>
        <tr valign="top">
        <th scope="row"><?php _e( 'Message', 'calculated-fields-form' ); ?></th>
        <td><textarea type="text" name="fp_message" rows="6" class="width75"><?php echo $form_obj->get_option('fp_message', CP_CALCULATEDFIELDSF_DEFAULT_fp_message); ?></textarea><br><em style="font-size:11px;"><?php _e( 'In case to select HTML in the Email Format, use <strong>&lt;br&gt;</strong> tags for the changes of lines.', 'calculated-fields-form' ); ?></em></td>
        </tr>
		<tr valign="top">
        <th scope="row"><?php _e( 'Attach static file', 'calculated-fields-form' ); ?></th>
        <td>
			<input type="text" name="fp_attach_static" class="width75" value="<?php echo esc_attr( $form_obj->get_option( 'fp_attach_static', '' ) ); ?>" />
			<input type="button" value="<?php echo esc_attr__( 'Select file', 'calculated-fields-form' ) ?>" class="button-secondary cff-attach-file" />
			<br><em style="font-size:11px;"><?php _e( 'Enter the path to a static file you wish to attach to the notification email.', 'calculated-fields-form' ); ?></em></td>
        </tr>
     </table>
     <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
  </div>
 </div>

 <div id="metabox_email_copy_to_user" class="postbox cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_email_copy_to_user' ) ); ?>" >
  <h3 class='hndle' style="padding:5px;"><span><?php _e( 'Email Copy to User', 'calculated-fields-form' ); ?></span></h3>
  <div class="inside">
     <table class="form-table">
        <tr valign="top">
        <th scope="row"><?php _e( 'Send confirmation/thank you message to user?', 'calculated-fields-form' ); ?></th>
        <td>
          <?php $option = $form_obj->get_option('cu_enable_copy_to_user', CP_CALCULATEDFIELDSF_DEFAULT_cu_enable_copy_to_user); ?>
          <select name="cu_enable_copy_to_user">
           <option value="true"<?php if ($option == 'true') echo ' selected'; ?>><?php _e( 'Yes', 'calculated-fields-form' ); ?></option>
           <option value="false"<?php if ($option == 'false') echo ' selected'; ?>><?php _e( 'No', 'calculated-fields-form' ); ?></option>
          </select>
        </td>
        </tr>
        <tr valign="top">
        <th scope="row"><?php _e( 'Email field on the form', 'calculated-fields-form' ); ?></th>
        <td>
			<select id="cu_user_email_field" name="cu_user_email_field[]" def="<?php echo esc_attr($form_obj->get_option('cu_user_email_field', CP_CALCULATEDFIELDSF_DEFAULT_cu_user_email_field)); ?>" MULTIPLE class="width75"></select><br/>
			<em style="font-size:11px;">
			<?php _e( 'The list allows multi selection. The email fields should be marked explicitly. If the email field isn\'t marked it wont be taken in account.', 'calculated-fields-form' ); ?>
			</em>
		</td>
        </tr>
        <tr valign="top">
        <th scope="row"><?php _e( 'Email subject', 'calculated-fields-form' ); ?></th>
        <td><input type="text" name="cu_subject" class="width75" value="<?php echo esc_attr($form_obj->get_option('cu_subject', CP_CALCULATEDFIELDSF_DEFAULT_cu_subject)); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row"><?php _e( 'Email format?', 'calculated-fields-form' ); ?></th>
        <td>
          <?php $option = $form_obj->get_option('cu_emailformat', CP_CALCULATEDFIELDSF_DEFAULT_email_format); ?>
          <select name="cu_emailformat" class="width75">
           <option value="text"<?php if ($option != 'html') echo ' selected'; ?>><?php _e( 'Plain Text (default)', 'calculated-fields-form' ); ?></option>
           <option value="html"<?php if ($option == 'html') echo ' selected'; ?>><?php _e( 'HTML (use html in the textarea below)', 'calculated-fields-form' ); ?></option>
          </select>
        </td>
        </tr>
        <tr valign="top">
        <th scope="row"><?php _e( 'Message', 'calculated-fields-form' ); ?></th>
        <td><textarea type="text" name="cu_message" rows="6" class="width75"><?php echo $form_obj->get_option('cu_message', CP_CALCULATEDFIELDSF_DEFAULT_cu_message); ?></textarea><br><em style="font-size:11px;"><?php _e( 'In case to select HTML in the Email Format, use <strong>&lt;br&gt;</strong> tags for the changes of lines.', 'calculated-fields-form' ); ?></em></td>
        </tr>
		<tr valign="top">
        <th scope="row"><?php _e( 'Attach static file', 'calculated-fields-form' ); ?></th>
        <td>
			<input type="text" name="cu_attach_static" class="width75" value="<?php echo esc_attr( $form_obj->get_option( 'cu_attach_static', '' ) ); ?>" />
			<input type="button" value="<?php echo esc_attr__( 'Select file', 'calculated-fields-form' ) ?>" class="button-secondary cff-attach-file" />
			<br><em style="font-size:11px;"><?php _e( 'Enter the path to a static file you wish to attach to the copy to user email.', 'calculated-fields-form' ); ?></em></td>
        </tr>
     </table>
     <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
  </div>
 </div>

 <div id="metabox_captcha_settings" class="postbox cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_captcha_settings' ) ); ?>" >
  <h3 class='hndle' style="padding:5px;"><span><?php _e( 'Captcha Verification', 'calculated-fields-form' ); ?></span></h3>
  <div class="inside">
     <table class="form-table">
        <tr valign="top">
        <th scope="row"><?php _e( 'Use Captcha Verification?', 'calculated-fields-form' ); ?></th>
        <td colspan="5">
          <?php $option = $form_obj->get_option('cv_enable_captcha', CP_CALCULATEDFIELDSF_DEFAULT_cv_enable_captcha); ?>
          <select name="cv_enable_captcha">
           <option value="true"<?php if ($option == 'true') echo ' selected'; ?>><?php _e( 'Yes', 'calculated-fields-form' ); ?></option>
           <option value="false"<?php if ($option == 'false') echo ' selected'; ?>><?php _e( 'No', 'calculated-fields-form' ); ?></option>
          </select>
        </td>
        </tr>

        <tr valign="top">
         <th scope="row"><?php _e( 'Width', 'calculated-fields-form' ); ?>:</th>
         <td><input type="text" name="cv_width" size="10" value="<?php echo esc_attr($form_obj->get_option('cv_width', CP_CALCULATEDFIELDSF_DEFAULT_cv_width)); ?>"  onblur="generateCaptcha();"  /></td>
         <th scope="row"><?php _e( 'Height', 'calculated-fields-form' ); ?>:</th>
         <td><input type="text" name="cv_height" size="10" value="<?php echo esc_attr($form_obj->get_option('cv_height', CP_CALCULATEDFIELDSF_DEFAULT_cv_height)); ?>" onblur="generateCaptcha();"  /></td>
         <th scope="row"><?php _e( 'Chars', 'calculated-fields-form' ); ?>:</th>
         <td><input type="text" name="cv_chars" size="10" value="<?php echo esc_attr($form_obj->get_option('cv_chars', CP_CALCULATEDFIELDSF_DEFAULT_cv_chars)); ?>" onblur="generateCaptcha();"  /></td>
        </tr>

        <tr valign="top">
         <th scope="row"><?php _e( 'Min font size', 'calculated-fields-form' ); ?>:</th>
         <td><input type="text" name="cv_min_font_size" size="10" value="<?php echo esc_attr($form_obj->get_option('cv_min_font_size', CP_CALCULATEDFIELDSF_DEFAULT_cv_min_font_size)); ?>" onblur="generateCaptcha();"  /></td>
         <th scope="row"><?php _e( 'Max font size', 'calculated-fields-form' ); ?>:</th>
         <td><input type="text" name="cv_max_font_size" size="10" value="<?php echo esc_attr($form_obj->get_option('cv_max_font_size', CP_CALCULATEDFIELDSF_DEFAULT_cv_max_font_size)); ?>" onblur="generateCaptcha();"  /></td>
         <td colspan="2" rowspan="">
           <?php _e( 'Preview', 'calculated-fields-form' ); ?>:<br />
             <br />
            <img src="<?php echo (get_option('CP_CALCULATEDFIELDSF_CAPTCHA_DIRECT_MODE', false)) ?  plugins_url('/captcha/captcha.php', CP_CALCULATEDFIELDSF_MAIN_FILE_PATH) : CPCFF_AUXILIARY::wp_url(); ?>/?cp_calculatedfieldsf=captcha&inAdmin=1"  id="captchaimg" alt="security code" border="0" class="skip-lazy" />
         </td>
        </tr>

        <tr valign="top">
         <th scope="row"><?php _e( 'Noise', 'calculated-fields-form' ); ?>:</th>
         <td><input type="text" name="cv_noise" size="10" value="<?php echo esc_attr($form_obj->get_option('cv_noise', CP_CALCULATEDFIELDSF_DEFAULT_cv_noise)); ?>" onblur="generateCaptcha();" /></td>
         <th scope="row"><?php _e( 'Noise Length', 'calculated-fields-form' ); ?>:</th>
         <td><input type="text" name="cv_noise_length" size="10" value="<?php echo esc_attr($form_obj->get_option('cv_noise_length', CP_CALCULATEDFIELDSF_DEFAULT_cv_noise_length)); ?>" onblur="generateCaptcha();" /></td>
        </tr>

        <tr valign="top">
         <th scope="row"><?php _e( 'Background', 'calculated-fields-form' ); ?>:</th>
         <td><input type="text" name="cv_background" size="10" value="<?php echo esc_attr($form_obj->get_option('cv_background', CP_CALCULATEDFIELDSF_DEFAULT_cv_background)); ?>" onblur="generateCaptcha();" /></td>
         <th scope="row"><?php _e( 'Border', 'calculated-fields-form' ); ?>:</th>
         <td><input type="text" name="cv_border" size="10" value="<?php echo esc_attr($form_obj->get_option('cv_border', CP_CALCULATEDFIELDSF_DEFAULT_cv_border)); ?>" onblur="generateCaptcha();" /></td>
        </tr>

        <tr valign="top">
         <th scope="row"><?php _e( 'Font', 'calculated-fields-form' ); ?>:</th>
         <td>
            <select name="cv_font" onchange="generateCaptcha();" >
              <option value="font-1.ttf"<?php if ("font-1.ttf" == $form_obj->get_option('cv_font', CP_CALCULATEDFIELDSF_DEFAULT_cv_font)) echo " selected"; ?>>Font 1</option>
              <option value="font-2.ttf"<?php if ("font-2.ttf" == $form_obj->get_option('cv_font', CP_CALCULATEDFIELDSF_DEFAULT_cv_font)) echo " selected"; ?>>Font 2</option>
              <option value="font-3.ttf"<?php if ("font-3.ttf" == $form_obj->get_option('cv_font', CP_CALCULATEDFIELDSF_DEFAULT_cv_font)) echo " selected"; ?>>Font 3</option>
              <option value="font-4.ttf"<?php if ("font-4.ttf" == $form_obj->get_option('cv_font', CP_CALCULATEDFIELDSF_DEFAULT_cv_font)) echo " selected"; ?>>Font 4</option>
            </select>
         </td>
        </tr>
     </table>
     <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
  </div>
 </div>
 <a id="metabox_addons_section"></a>
 <?php
	// Prints the addons settings related with the form's id
	CPCFF_ADDONS::print_form_settings(CP_CALCULATEDFIELDSF_ID);
	do_action('cpcff_form_settings', CP_CALCULATEDFIELDSF_ID);
 ?>

<div id="metabox_basic_settings" class="postbox" >
  <h3 class='hndle' style="padding:5px;"><span><?php _e( 'Note', 'calculated-fields-form' ); ?></span></h3>
  <div class="inside">
   <?php _e( 'To display the form in a post/page, enter your shortcode in the post/page content:', 'calculated-fields-form' ); ?>
   <?php print '<b>[CP_CALCULATED_FIELDS id="'.CP_CALCULATEDFIELDSF_ID.'"]</b>';    ?><br />
   <?php _e( 'The CFF plugin implements widgets and blocks to allow inserting the form visually with the most popular page builders such as Gutenberg Editor, Classic Editor, Elementor, Site Origin, Visual Composer, Beaver Builder, Divi, and for the other page builders insert the shortcode directly.', 'calculated-fields-form' ); ?>
   <br /><br />
  </div>
</div>
</div>
<p class="submit">
	<input type="submit" name="save" id="save1" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'calculated-fields-form' ); ?>" title="Saves the form's structure and settings and creates a revision" onclick="<?php
        if(!CPCFF_AutoUpdateClss::valid()) print 'alert(\''.esc_js(CPCFF_AutoUpdateClss::message(true)).'\');'
    ?>" />
</p>

[<a href="https://cff.dwbooster.com/customization" target="_blank"><?php _e( 'Request Custom Modifications', 'calculated-fields-form' ); ?></a>] | [<a href="https://cff.dwbooster.com/" target="_blank"><?php _e( 'Help', 'calculated-fields-form' ); ?></a>]
</form>
</div>
<script type="text/javascript">
	function generateCaptcha()
	{
	   var 	d=new Date(),
			f = document.cpformconf,
			qs = "&width="+f.cv_width.value;

	   qs += "&height="+f.cv_height.value;
	   qs += "&letter_count="+f.cv_chars.value;
	   qs += "&min_size="+f.cv_min_font_size.value;
	   qs += "&max_size="+f.cv_max_font_size.value;
	   qs += "&noise="+f.cv_noise.value;
	   qs += "&noiselength="+f.cv_noise_length.value;
	   qs += "&bcolor="+f.cv_background.value;
	   qs += "&border="+f.cv_border.value;
	   qs += "&font="+f.cv_font.options[f.cv_font.selectedIndex].value;
	   qs += "&rand="+d;

	   document.getElementById("captchaimg").src= "<?php echo (get_option('CP_CALCULATEDFIELDSF_CAPTCHA_DIRECT_MODE', false)) ?  plugins_url('/captcha/captcha.php', CP_CALCULATEDFIELDSF_MAIN_FILE_PATH) : CPCFF_AUXILIARY::wp_url(); ?>/?cp_calculatedfieldsf=captcha&inAdmin=1"+qs;
	}

	generateCaptcha();
</script>