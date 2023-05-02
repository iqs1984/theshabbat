<?php
if ( !defined('CP_AUTH_INCLUDE') )
{
	print 'Direct access not allowed.';
	exit;
}

// Required scripts
require_once CP_CALCULATEDFIELDSF_BASE_PATH.'/inc/cpcff_templates.inc.php';

// Corrects a conflict with W3 Total Cache
if( function_exists( 'w3_instance' ) )
{
	try
	{
		$w3_config = w3_instance( 'W3_Config' );
		$w3_config->set( 'minify.html.enable', false );
	}
	catch( Exception $err )
	{

	}
}

add_filter( 'style_loader_tag', array('CPCFF_AUXILIARY', 'complete_link_tag') );

wp_enqueue_style( 'cpcff_stylepublic', plugins_url('/css/stylepublic.css', CP_CALCULATEDFIELDSF_MAIN_FILE_PATH), array(), CP_CALCULATEDFIELDSF_VERSION );
wp_enqueue_style( 'cpcff_jquery_ui', plugins_url('/vendors/jquery-ui/jquery-ui.min.css', CP_CALCULATEDFIELDSF_MAIN_FILE_PATH), array(), CP_CALCULATEDFIELDSF_VERSION );

$cpcff_main = CPCFF_MAIN::instance();
$form_obj = $cpcff_main->get_form($id);

// Texts
global $cpcff_default_texts_array;
$cpcff_texts_array = $form_obj->get_option('vs_all_texts', $cpcff_default_texts_array);
$cpcff_texts_array = CPCFF_AUXILIARY::array_replace_recursive(
    $cpcff_default_texts_array,
    is_string( $cpcff_texts_array ) ? unserialize( $cpcff_texts_array ) : $cpcff_texts_array
);

$form_data = $form_obj->get_option('form_structure', CP_CALCULATEDFIELDSF_DEFAULT_form_structure);

$form_data = serialize($form_data); // To clone the object to get references to different objects

// Load Vendors Resources
if(strpos($form_data, 'select2') && !wp_script_is('select2'))
{
    wp_enqueue_style( 'cpcff_select2_css', plugins_url('/vendors/select2/select2.min.css', CP_CALCULATEDFIELDSF_MAIN_FILE_PATH), array(), CP_CALCULATEDFIELDSF_VERSION );
    wp_enqueue_script( 'cpcff_select2_js', plugins_url('/vendors/select2/select2.min.js', CP_CALCULATEDFIELDSF_MAIN_FILE_PATH), array(), CP_CALCULATEDFIELDSF_VERSION, true );
}

if(strpos($form_data, 'fdatatableds') && !wp_script_is('datatables'))
{
    wp_enqueue_style( 'cpcff_datatable_css', plugins_url('/vendors/datatables/datatables.min.css', CP_CALCULATEDFIELDSF_MAIN_FILE_PATH), array(), CP_CALCULATEDFIELDSF_VERSION );
    wp_enqueue_script( 'cpcff_datatable_js', plugins_url('/vendors/datatables/datatables.min.js', CP_CALCULATEDFIELDSF_MAIN_FILE_PATH), array(), CP_CALCULATEDFIELDSF_VERSION, true );
}

$form_data = unserialize($form_data); // Complete cloning
if( !empty( $form_data ) )
{
	if(isset($form_data[ 1 ]) && is_object($form_data[ 1 ])) $form_data[ 1 ] = (array) $form_data[ 1 ];
	// PROCESS DATASOURCE FIELDS
	if( !empty( $form_data[ 0 ] ) )
	{
		foreach( $form_data[ 0 ] as $key => $object )
		{
			if( isset( $object->isDataSource ) && $object->isDataSource )
			{
				// Clear the data are not related with the datasource active
				$datasources = get_object_vars( $object->list );
				foreach( $datasources as $ds_key => $ds_obj )
				{
					if( $ds_key != $object->active )
					{
						unset( $object->list->$ds_key );
					}
				}

				if(
					(
						!empty($object->active) &&
						$object->active != 'csv' &&
						$object->active != 'recordset' &&
						$object->active != 'json'
					) ||
					(
						property_exists( $object->list, 'csv' ) &&
						property_exists( $object->list->csv->csvData, 'type' ) &&
						$object->list->csv->csvData->type == 'url'
					)
				)
				{
					// Save the datasource as  transient variable.
					delete_transient('cpcff_db_'.$id.'_'.$object->name);
					set_transient( 'cpcff_db_'.$id.'_'.$object->name, $object->list->{$object->active}, 60*60*24 );
					$datasourceObject = new stdClass;
					$datasourceObject->form = $id;
					$datasourceObject->vars = array();

					// Extract variables if are used
					$dataStr = '';
					switch( $object->active )
					{
						case 'database':
							$queryData = $object->list->database->queryData;
							if( $queryData->active == 'query' )
							{
								$dataStr = $queryData->query;
							}
							else
							{
								$dataStr = $queryData->value.$queryData->text.$queryData->table.$queryData->where.$queryData->orderby.$queryData->limit;
							}
						break;
						case 'acf':
							$dataStr = $object->list->acf->acfData->src_id;
						break;
						case 'csv':
							$dataStr = $object->list->csv->csvData->where;
						break;
						case 'posttype':
							$posttypeData = $object->list->posttype->posttypeData;
							$dataStr = $posttypeData->id.$posttypeData->last;
						break;
						case 'taxonomy':
							$taxonomyData = $object->list->taxonomy->taxonomyData;
							$dataStr = $taxonomyData->id.$taxonomyData->slug;
						break;
						case 'user':
							$userData = $object->list->user->userData;
							if( !$userData->logged )
							{
								$dataStr = $userData->id.$userData->login;
							}
						break;
                        case 'messages':
							$messagesFrom = trim($object->list->messages->messagesData->from);
							$messagesTo   = trim($object->list->messages->messagesData->to);

                            if(preg_match('/(fieldname\d+(\|[rv])?)/', $messagesFrom, $matches))
                                $dataStr .= '<%'.$matches[1].'%>';

                            if(preg_match('/(fieldname\d+(\|[rv])?)/', $messagesTo, $matches))
                                $dataStr .= '<%'.$matches[1].'%>';

                            if(!empty($object->list->messages->messagesData->conditions))
                            {
                                $messagesConditions = trim($object->list->messages->messagesData->conditions);
                                $messagesConditions_list = explode("\n", $messagesConditions);
                                foreach($messagesConditions_list as $messageCondition)
                                {
                                    $messageCondition = trim($messageCondition);
                                    if(empty($messageCondition)) continue;
                                    $messageCondition_components = explode('|', $messageCondition);
                                    if(count($messageCondition_components)<2) continue;
                                    $messageCondition_left_component = trim(array_shift($messageCondition_components));
                                    $messageCondition_right_component = trim(implode('|', $messageCondition_components));
                                    if(empty($messageCondition_left_component)) continue;

                                    if(preg_match('/(fieldname\d+(\|[rv])?)/', $messageCondition_right_component, $matches))
                                        $dataStr .= '<%'.$matches[1].'%>';
                                }
                            }
						break;
					}

					if( preg_match_all( '/<%([^%]+)%>/', $dataStr, $matches ) )
					{
						$datasourceObject->vars = $matches[ 1 ];
					}
					$object->list->{$object->active} = $datasourceObject;
				}
				$form_data[ 0 ][ $key ] = $object;
			}
		}
	}
	$form_data[ 1 ][ 'formid' ]="cp_calculatedfieldsf_pform_".CPCFF_MAIN::$form_counter;
	if( get_option( 'CP_CALCULATEDFIELDSF_FORM_CACHE', false ) )
	{
		$form_cache = $form_obj->get_option('cache', '');
		$form_data[ 1 ][ 'cache' ]  = $form_data[ 1 ][ 'setCache' ] = ( empty( $form_cache ) ) ? true : false;
	}
	else
	{
		$form_data[ 1 ][ 'cache' ]  = $form_data[ 1 ][ 'setCache' ] = false;
	}

	// PROCESS LAYOUT AND CUSTOM STYLES
	if( isset( $form_data[ 1 ] ) && isset( $form_data[ 1 ][ 0 ] ) )
	{
		if(!empty( $form_data[ 1 ][ 0 ]->formtemplate ))
		{
			CPCFF_TEMPLATES::enqueue_template_resources($form_data[ 1 ][ 0 ]->formtemplate);
		}

		if(!empty($form_data[ 1 ][ 0 ]->customstyles))
		{
			print '<style>'.$form_data[ 1 ][ 0 ]->customstyles.'</style>';
		}
	}

	$form_data = apply_filters( 'cpcff_form_data',  $form_data );
?>
	<pre style="display:none !important;"><script type="text/javascript">
	 function doValidate_<?php echo CPCFF_MAIN::$form_counter; ?>(form)
	 {
        window['cff_enabling_form' ] = function(_form){
            if(!(_form instanceof $dexQuery)) _form = $dexQuery(_form);
            _form.validate().settings.ignore = '.ignore,.ignorepb';
            _form.removeData('being-submitted');
            _form.find('.submitbtn-disabled').removeClass('submitbtn-disabled');
            _form.find('.cff-processing-form').remove();
        };
        window['cff_disabling_form'] = function(_form){
            if(!(_form instanceof $dexQuery)) _form = $dexQuery(_form);
            if(cff_form_disabled(_form)) return;
            _form.find('.pbSubmit').addClass('submitbtn-disabled');
            _form.data('being-submitted',1);
            var d = document.createElement('div');
            $dexQuery(d).addClass('cff-processing-form').appendTo(_form.find('#fbuilder'));
        };
        window['cff_form_disabled' ] = function(_form){
            if(!(_form instanceof $dexQuery)) _form = $dexQuery(_form);
            return ('undefined' != typeof _form.data('being-submitted'));
        };
		var form_identifier =  '_<?php echo CPCFF_MAIN::$form_counter; ?>';
		if(typeof cpcff_validation_rules == 'undefined') cpcff_validation_rules = {};
		if(typeof cpcff_validation_rules[form_identifier] == 'undefined') cpcff_validation_rules[form_identifier] = {};
		var $dexQuery = (fbuilderjQuery) ? fbuilderjQuery : jQuery.noConflict(),
			_form = $dexQuery("#cp_calculatedfieldsf_pform"+form_identifier),
			form_disabled = function(){cff_form_disabled(_form);},
			disabling_form = function(){cff_disabling_form(_form);},
			enabling_form = function(){cff_enabling_form(_form);};
		if(form_disabled()) return false;
		_form.validate().settings.ignore = '.ignore';
		var	cpefb_error = !_form.validate().checkForm();
		var	validation_rules = cpcff_validation_rules[form_identifier],
			processing_form = function()
			{
                <?php
				/**
				 * Action called in the generation of javascript code to validate the forms data before submission.
				 * To the function are passed two parameters: the array with submitted data, and the number of form in the page.
				 */
				do_action( 'cpcff_script_after_validation', '_'.CPCFF_MAIN::$form_counter, $id );
				?>
				for(var rule in validation_rules)
				{
					if(!validation_rules[rule]) return;
				}
				_form.find("[name$='_date'][type='hidden']").each(function(){
					var v  	 = $dexQuery(this).val(),
						name = $dexQuery(this).attr( 'name' ).replace('_date', ''),
						e 	 = $dexQuery("[name='"+name+"']"); if( e.length ){ e.val( $dexQuery.trim( e.val().replace( v, '' ) ) ); }
				});
				_form.find("select option[vt]").each(function(){
                    var e = $dexQuery(this);
                    e.attr('cff-val-bk', e.val()).val(e.attr("vt"));
				});
				_form.find("input[vt]").each(function(){
                    var e = $dexQuery(this);
                    e.attr('cff-val-bk', e.val()).val(e.attr("vt"));
				});
				_form.find('.cpcff-recordset,.cff-exclude :input,[id^="form_structure_"]')
				.add(_form.find( '.ignore' )).attr('cff-disabled', 1).prop('disabled', true);
				disabling_form();
                _form[ 0 ].submit();
                setTimeout(function(){
                    _form.find('[cff-val-bk]').each(function(){
                        var e = $dexQuery(this);
                        e.val(e.attr('cff-val-bk')).removeAttr('cff-val-bk');
                    });
                    _form.find('[cff-disabled]').prop('disabled', false).removeAttr('cff-disabled');
                    if(!/^(\s*|_self|_top|_parent)$/i.test(_form.prop('target')))
                    {
                        enabling_form();
                    }
                    $dexQuery(document).trigger('cff-form-submitted', _form);
                }, 4000);
			};
		_form.find('[name="cp_ref_page"]').val(document.location.href);
		validation_rules['fields_validation_error'] = (cpefb_error==0);
        /* 1: Do not submit if the equations are being evaluated */
        validation_rules['no_pending'] = (!(form_identifier in $dexQuery.fbuilder.calculator.processing_queue) || !$dexQuery.fbuilder.calculator.processing_queue[form_identifier]) && !$dexQuery.fbuilder.calculator.thereIsPending(form_identifier);
		if(!validation_rules['no_pending'])
		{
			$dexQuery(document).on('equationsQueueEmpty', function(evt, formId){
				if(formId == form_identifier)
				{
					$dexQuery(document).off('equationsQueueEmpty');
					validation_rules['no_pending']  = true;
					processing_form();
				}
			});
		}
		/* End :1 */
		if (validation_rules['fields_validation_error'])
		{
		<?php
		// CAPTCHA SECTION
		if ($form_obj->get_option('cv_enable_captcha', CP_CALCULATEDFIELDSF_DEFAULT_cv_enable_captcha) != 'false')
		{
		?>  if (_form.find('[id^="hdcaptcha_cp_calculated_fields_form_post_"]').val() == '')
			{
				alert('<?php echo esc_js( $cpcff_texts_array[ 'captcha_required_text' ][ 'text' ] ); ?>');
				return false;
			}
			disabling_form();
			validation_rules['captcha'] = false;
			$dexQuery.ajax({
				type: "GET",
				url:  "<?php echo str_replace('&amp;','&',esc_js(CPCFF_AUXILIARY::site_url(true))); ?>",
				data: {
					ps: form_identifier,
					hdcaptcha_cp_calculated_fields_form_post: _form.find('[id^="hdcaptcha_cp_calculated_fields_form_post_"]').val()
				},
				success:function(result){
					enabling_form();
					if (result == "captchafailed")
					{
						_form.find('[id^="captchaimg_"]').attr('src', _form.find('[id^="captchaimg_"]').attr('src')+'&'+Date());
						alert('<?php echo esc_js( $cpcff_texts_array[ 'incorrect_captcha_text' ][ 'text' ] ); ?>');
						return false;
					}
					else
					{
						validation_rules['captcha'] = true;
						processing_form();
					}
				}
			});
		<?php
		}
		else
		{
		?>
			processing_form();
		<?php
		}
		?>
		}
		else
		{
            _form.valid();
            var page = $dexQuery('.cpefb_error:not(.message):not(.ignore):eq(0)').closest('.pbreak').attr('page')*1;
            gotopage(page, _form);
			enabling_form();
		}
		return false;
	}
	</script></pre>
	<form name="<?php echo $form_data[ 1 ][ 'formid' ]; ?>" id="<?php echo $form_data[ 1 ][ 'formid' ]; ?>" action="<?php echo esc_attr( ( ( $permalink = get_permalink() ) !== false ) ? $permalink : '?'); ?>" method="post" enctype="multipart/form-data" onsubmit="return doValidate_<?php echo CPCFF_MAIN::$form_counter; ?>(this);" class="cff-form <?php
		if(!empty($form_data[1][0]) && !empty($form_data[1][0]->persistence)) echo ' persist-form';
		if(!empty($atts) && !empty($atts['class'])) echo ' '.esc_attr($atts['class']);
	?>">
	<?php
	if( !empty( $form_cache ) )
	{
		// The form is stored in cache, the following section corrects the 	consecutive number to identify the forms on page
		// $form_cache = stripcslashes( $form_cache );
		$form_cache = preg_replace('/[\\n\\r]/', '\\n', $form_cache);
		$form_cache = str_replace( 'data-processed="1"', '', $form_cache );
		$form_cache = preg_replace( '/(fieldname|separator)(\d+)_\d+/', '$1$2_'.CPCFF_MAIN::$form_counter, $form_cache );
		$form_cache = preg_replace( '/field_\d+(\-\d+)/', 'field_'.CPCFF_MAIN::$form_counter.'$1', $form_cache );
		$form_cache = preg_replace( 	'/(form_structure|cp_calculatedfieldsf_pform|fbuilder|formheader|fieldlist|cpcaptchalayer|captchaimg|hdcaptcha_cp_calculated_fields_form_post|hdcaptcha_error|cp_subbtn)_\d+/',
			'$1_'.CPCFF_MAIN::$form_counter,
			$form_cache
		);
		$form_cache = preg_replace( '/ps=_\d+&/', 'ps=_'.CPCFF_MAIN::$form_counter.'&', $form_cache );
		$form_cache = preg_replace( '/value="_\d+"/', 'value="_'.CPCFF_MAIN::$form_counter.'"', $form_cache );
		$form_cache_parts = explode( '<div id="fbuilder">', $form_cache );
		if ( 2 == count( $form_cache_parts ) ) {
			$form_cache_parts[1] = preg_replace( '/<script\b/i', '<script type="cff-script"', $form_cache_parts[1] );
			$form_cache = implode( '<div id="fbuilder">', $form_cache_parts );
		}
		print $form_cache;

		// Prevent to call the server side to create the cache
		print '<pre style="display:none !important;"><script type="text/javascript">form_structure_'.CPCFF_MAIN::$form_counter.'[1]["cached"]=true;form_structure_'.CPCFF_MAIN::$form_counter.'[1]["setCache"]=false;</script></pre>';
	}
	else
	{
		// The form is not cached, or the from's cache is disabled
	?>
		<input type="hidden" name="cp_calculatedfieldsf_pform_psequence" value="_<?php echo CPCFF_MAIN::$form_counter; ?>" /><input type="hidden" name="cp_calculatedfieldsf_pform_process" value="1" /><input type="hidden" name="cp_calculatedfieldsf_id" value="<?php echo $id; ?>" /><input type="hidden" name="cp_ref_page" value="<?php echo esc_attr(CPCFF_AUXILIARY::site_url() ); ?>" /><pre style="display:none !important;"><script type="text/javascript">form_structure_<?php echo CPCFF_MAIN::$form_counter; ?>=<?php print str_replace( array( "\n", "\r" ), " ", ((version_compare(CP_CFF_PHPVERSION,"5.3.0")>=0)?json_encode($form_data, JSON_HEX_QUOT|JSON_HEX_TAG):json_encode($form_data)) ); ?>;</script></pre>
		<div id="fbuilder">
			<?php
				if(
					!empty($form_data) &&
					!empty($form_data[1]) &&
					!empty($form_data[1][0]) &&
					!empty($form_data[1][0]->loading_animation)
				)
				{
					print '<div class="cff-processing-form"></div>';
				}
			?>
			<div id="fbuilder_<?php echo CPCFF_MAIN::$form_counter; ?>">
				<div id="formheader_<?php echo CPCFF_MAIN::$form_counter; ?>"></div>
				<div id="fieldlist_<?php echo CPCFF_MAIN::$form_counter; ?>"></div>
                <div class="clearer"></div>
			</div>
			<div id="cpcaptchalayer_<?php echo CPCFF_MAIN::$form_counter; ?>" class="cpcaptchalayer" style="display:none;">
			<?php if(CPCFF_COUPON::active_coupons($id)) { ?>
				<div class="fields">
					<label><?php echo( $cpcff_texts_array[ 'coupon_code_text' ][ 'text' ] ); ?></label>
					<div class="dfield"><input type="text" name="couponcode" value=""></div>
					<div class="clearer"></div>
				</div>
			<?php } ?>
            <?php $paypal_enabled = $form_obj->get_option('enable_paypal',CP_CALCULATEDFIELDSF_DEFAULT_ENABLE_PAYPAL); ?>
			<div class="fields" id="field-c0" <?php if ($paypal_enabled != '2') echo 'style="display:none"'; ?>>
				<label><?php echo( $cpcff_texts_array[ 'payment_options_text' ][ 'text' ] ); ?></label>
				<div class="dfield">
				 <?php if ($paypal_enabled == '1' || $paypal_enabled == '2') { ?><div><input type="radio" name="bccf_payment_option_paypal" vt="1" value="1" checked> <?php _e( $form_obj->get_option('enable_paypal_option_yes',CP_CALCULATEDFIELDSF_PAYPAL_OPTION_YES), 'calculated-fields-form') ; ?></div><?php } ?>
				 <!--addons-payment-options-->
    			 <?php if ($paypal_enabled == '2') { ?><div><input type="radio" name="bccf_payment_option_paypal" vt="0" value="0"> <?php _e( $form_obj->get_option('enable_paypal_option_no',CP_CALCULATEDFIELDSF_PAYPAL_OPTION_NO), 'calculated-fields-form') ; ?></div><?php } ?>
				</div>
				<div class="clearer"></div>
			</div>
			<!--addons-payment-fields-->
			<?php if ($form_obj->get_option('cv_enable_captcha', CP_CALCULATEDFIELDSF_DEFAULT_cv_enable_captcha) != 'false') { ?>
				<div class="fields">
					<label><?php echo( $cpcff_texts_array[ 'captcha_text' ][ 'text' ] ); ?></label>
					<div class="dfield">
						<img src="<?php echo esc_attr(((get_option('CP_CALCULATEDFIELDSF_CAPTCHA_DIRECT_MODE', false)) ? plugins_url('/captcha/captcha.php', CP_CALCULATEDFIELDSF_MAIN_FILE_PATH) : CPCFF_AUXILIARY::site_url()).'/?cp_calculatedfieldsf=captcha&ps=_'.CPCFF_MAIN::$form_counter.'&inAdmin=1&width='.$form_obj->get_option('cv_width', CP_CALCULATEDFIELDSF_DEFAULT_cv_width).'&height='.$form_obj->get_option('cv_height', CP_CALCULATEDFIELDSF_DEFAULT_cv_height).'&letter_count='.$form_obj->get_option('cv_chars', CP_CALCULATEDFIELDSF_DEFAULT_cv_chars).'&min_size='.$form_obj->get_option('cv_min_font_size', CP_CALCULATEDFIELDSF_DEFAULT_cv_min_font_size).'&max_size='.$form_obj->get_option('cv_max_font_size', CP_CALCULATEDFIELDSF_DEFAULT_cv_max_font_size).'&noise='.$form_obj->get_option('cv_noise', CP_CALCULATEDFIELDSF_DEFAULT_cv_noise).'&noiselength='.$form_obj->get_option('cv_noise_length', CP_CALCULATEDFIELDSF_DEFAULT_cv_noise_length).'&bcolor='.$form_obj->get_option('cv_background', CP_CALCULATEDFIELDSF_DEFAULT_cv_background).'&border='.$form_obj->get_option('cv_border', CP_CALCULATEDFIELDSF_DEFAULT_cv_border).'&font='.$form_obj->get_option('cv_font', CP_CALCULATEDFIELDSF_DEFAULT_cv_font)); ?>"  id="captchaimg_<?php echo CPCFF_MAIN::$form_counter; ?>" alt="security code" border="0" title="<?php echo esc_attr( $cpcff_texts_array[ 'refresh_captcha_text' ][ 'text' ] ) ; ?>" width="<?php echo esc_attr($form_obj->get_option('cv_width', CP_CALCULATEDFIELDSF_DEFAULT_cv_width)); ?>" height="<?php echo esc_attr($form_obj->get_option('cv_height', CP_CALCULATEDFIELDSF_DEFAULT_cv_height)); ?>" class="skip-lazy" />
					</div>
					<div class="clearer"></div>
				</div>
				<div class="fields">
					<label><?php echo( $cpcff_texts_array[ 'security_code_text' ][ 'text' ] ); ?></label>
					<div class="dfield">
						<input type="text" size="20" name="hdcaptcha_cp_calculated_fields_form_post" id="hdcaptcha_cp_calculated_fields_form_post_<?php echo CPCFF_MAIN::$form_counter; ?>" value="" />
						<div class="error message" id="hdcaptcha_error_<?php echo CPCFF_MAIN::$form_counter; ?>" style="display:none;"></div>
					</div>
					<div class="clearer"></div>
				</div>
			<?php } ?>
			<!--add-ons-->
			</div>
			<?php if ($form_obj->get_option('enable_submit','') == '') { ?>
			<div id="cp_subbtn_<?php echo CPCFF_MAIN::$form_counter; ?>" class="cp_subbtn" style="display:none;"><?php _e($button_label); ?></div>
			<?php } ?>
			<div class="clearer"></div>
		</div>
	<?php
	}

	// Protects the form with nonce
	if(@intval(get_option('CP_CALCULATEDFIELDSF_NONCE', false)))
	{
		wp_nonce_field( 'cpcff_form_'.$id.'_'.CPCFF_MAIN::$form_counter, '_cpcff_public_nonce' );
	}

	// Inserts a honeypot field to protect the form against spam bots
	if( ( $honeypot = get_option( 'CP_CALCULATEDFIELDSF_HONEY_POT', '' ) ) != '' )
	{
		echo '<p style="display:none"><textarea name="'.$honeypot.'" cols="100%" rows="10"></textarea><label  for="'.$honeypot.'">'.__( 'If you are a human, do not fill in this field.', 'calculated-fields-form' ).'</label></p>';
	}
	?>
	</form>
<?php
}
?>