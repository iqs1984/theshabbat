<?php
if ( !is_admin() )
{
	print 'Direct access not allowed.';
    exit;
}

$_GET['u'] = (isset($_GET['u'])) ? intval(@$_GET['u']) : 0;
$_GET['c'] = (isset($_GET['c'])) ? intval(@$_GET['c']) : 0;
$_GET['d'] = (isset($_GET['d'])) ? intval(@$_GET['d']) : 0;

global $wpdb;
$cpcff_main = CPCFF_MAIN::instance();

$message = "";

if(isset($_GET['orderby']))
{
	update_option('CP_CALCULATEDFIELDSF_FORMS_LIST_ORDERBY', $_GET['orderby'] == 'form_name' ? 'form_name' : 'id');
}

$cp_default_template = CP_CALCULATEDFIELDSF_DEFAULT_template;
$cp_default_submit = CP_CALCULATEDFIELDSF_DEFAULT_display_submit_button;
$cp_default_captcha = CP_CALCULATEDFIELDSF_DEFAULT_cv_enable_captcha;

if( isset($_REQUEST['cp_default_template']) )
{
    check_admin_referer( 'cff-default-settings', '_cpcff_nonce' );

    $cp_default_template = sanitize_text_field($_REQUEST['cp_default_template']);
    $cp_default_submit = isset($_REQUEST['cp_default_submit']) ? '' : 'no';
    $cp_default_captcha = isset($_REQUEST['cp_default_captcha']) ? 'true' : 'false';

    // Update default settings
    update_option('CP_CALCULATEDFIELDSF_DEFAULT_template', $cp_default_template);
    update_option('CP_CALCULATEDFIELDSF_DEFAULT_display_submit_button', $cp_default_submit);
    update_option('CP_CALCULATEDFIELDSF_DEFAULT_cv_enable_captcha', $cp_default_captcha);

    if(isset($_REQUEST['cp_default_existing_forms']))
    {
        $myrows = $wpdb->get_results( "SELECT id,form_structure,enable_submit,cv_enable_captcha FROM ".$wpdb->prefix.CP_CALCULATEDFIELDSF_FORMS_TABLE);
        foreach ($myrows as $item)
        {
            $form_structure = preg_replace('/"formtemplate"\s*\:\s*"[^"]*"/', '"formtemplate":"'.esc_js($cp_default_template).'"', $item->form_structure);

            $wpdb->update(
                $wpdb->prefix.CP_CALCULATEDFIELDSF_FORMS_TABLE,
                array(
                    'form_structure' => $form_structure,
                    'enable_submit' => $cp_default_submit,
                    'cv_enable_captcha' => $cp_default_captcha,
                ),
                array(
                    'id' => $item->id
                ),
                array('%s','%s','%s'),
                array('%d')
            );
        }
    }
    $message = __( "Default settings updated", 'calculated-fields-form' );;
}

if( isset( $_GET[ 'b' ] ) && $_GET[ 'b' ] == 1 )
{
	check_admin_referer( 'cff-activate-deactivate-addons', '_cpcff_nonce' );
	// Refreshes active addons
	CPCFF_ADDONS::refresh_actives((!empty($_GET['cpcff_addons_active_list']) && is_array($_GET['cpcff_addons_active_list'])) ? $_GET['cpcff_addons_active_list'] : array());
}

if (isset($_GET['a']) && $_GET['a'] == '1')
{
	check_admin_referer( 'cff-add-form', '_cpcff_nonce' );
	$new_form = $cpcff_main->create_form(
        isset($_GET["name"]) ? sanitize_text_field(stripcslashes($_GET["name"])) : '',
        isset($_GET["category"]) ? sanitize_text_field(stripcslashes($_GET["category"])) : '',
		isset($_GET['ftpl']) ? sanitize_text_field(wp_unslash($_GET['ftpl'])) : 0
    );
    // Update the default category
    $cff_current_form_category = get_option('calculated-fields-form-category', '');
    if(!empty($cff_current_form_category))
        update_option('calculated-fields-form-category', sanitize_text_field(stripcslashes($_GET["category"])));

	$message = __( "Item added", 'calculated-fields-form' );
	if($new_form)
	{
		print "<script>document.location = 'admin.php?page=cp_calculated_fields_form&cal=".$new_form->get_id()."&r=".rand()."&_cpcff_nonce=".wp_create_nonce( 'cff-form-settings' )."';</script>";
	}
}
else if (!empty($_GET['u']))
{
	check_admin_referer( 'cff-update-form', '_cpcff_nonce' );
	$cpcff_main->get_form($_GET['u'])->update_name((isset($_GET["name"])) ? sanitize_text_field(stripcslashes($_GET["name"])) : '');
    $message = __( "Item updated", 'calculated-fields-form' );
}
else if (!empty($_GET['d']))
{
	// Deleting Form
	check_admin_referer( 'cff-delete-form', '_cpcff_nonce' );
	$cpcff_main->delete_form($_GET['d']);
	$message = __( "Item deleted", 'calculated-fields-form' );
} else if (!empty($_GET['c']))
{
	// Cloning Form
	check_admin_referer( 'cff-clone-form', '_cpcff_nonce' );
	if($cpcff_main->clone_form(@intval($_GET['c'])) !== false) $message = __( "Item duplicated/cloned", 'calculated-fields-form' );
	else $message = __( "Duplicate/Clone Error, the form cannot be cloned", 'calculated-fields-form' );
} else if (isset($_GET['ac']) && $_GET['ac'] == 'st')
{
	check_admin_referer( 'cff-update-general-settings', '_cpcff_nonce' );
    update_option( 'CP_CFF_LOAD_SCRIPTS', 			  		(isset($_GET["scr"]) && $_GET["scr"]=="1"? "0":"1")  );
    update_option( 'CP_CALCULATEDFIELDSF_DISABLE_REVISIONS',(isset($_GET["dr"]) && $_GET["dr"]=="1" ? 1 : 0)  );
    update_option( 'CP_CALCULATEDFIELDSF_USE_CACHE',  		(isset($_GET["jsc"]) && $_GET["jsc"]=="1" ? 1 : 0)  );
    update_option( 'CP_CALCULATEDFIELDSF_OPTIMIZATION_PLUGIN',(isset($_GET["optm"]) && $_GET["optm"]=="1" ? 1 : 0)  );
    update_option( 'CP_CALCULATEDFIELDSF_CAPTCHA_DIRECT_MODE',(isset($_GET["cdm"]) && $_GET["cdm"]=="1" ? 1 : 0)  );
    update_option( 'CP_CALCULATEDFIELDSF_FORM_CACHE', 		(isset($_GET["fmc"]) && $_GET["fmc"]=="1" ? 1 : 0)  );
    update_option( 'CP_CALCULATEDFIELDSF_EXCLUDE_CRAWLERS', (isset($_GET["ecr"]) && $_GET["ecr"]=="1" ? 1 : 0)  );
    update_option( 'CP_CALCULATEDFIELDSF_EMAIL_HEADERS', 	(isset($_GET["ehr"]) && $_GET["ehr"]=="1" ? 1 : 0)  );
    update_option( 'CP_CALCULATEDFIELDSF_ENCODING_EMAIL', 	(isset($_GET["em"]) && $_GET["em"]=="1" ? 1 : 0)  );
    update_option( 'CP_CALCULATEDFIELDSF_EARLY_SESSION', 	(isset($_GET["es"]) && $_GET["es"]=="1" ? 1 : 0)  );
    update_option( 'CP_CALCULATEDFIELDSF_DIRECT_FORM_ACCESS',(isset($_GET["df"]) && $_GET["df"]=="1" ? 1 : 0)  );
    update_option( 'CP_CALCULATEDFIELDSF_AMP', 				(isset($_GET["amp"]) && $_GET["amp"]=="1" ? 1 : 0)  );
    update_option( 'CP_CALCULATEDFIELDSF_TYPC', 			(isset($_GET["typc"]) && $_GET["typc"]=="1" ? 1 : 0)  );
    update_option( 'CP_CALCULATEDFIELDSF_NONCE', 			(isset($_GET["nc"]) && $_GET["nc"]=="1" ? 1 : 0)  );
    update_option( 'CP_CALCULATEDFIELDSF_HONEY_POT', 	     sanitize_text_field(trim( $_GET["hp"] )) );

	$public_js_path = CP_CALCULATEDFIELDSF_BASE_PATH.'/js/cache/all.js';
	try{
		if( get_option( 'CP_CALCULATEDFIELDSF_USE_CACHE', CP_CALCULATEDFIELDSF_USE_CACHE ) == false )
		{
			if( file_exists( $public_js_path ) )
			{
				unlink( $public_js_path );
			}
		}
		else
		{
			if(!file_exists($public_js_path))
			{
				wp_remote_get(CPCFF_AUXILIARY::wp_url().((strpos(CPCFF_AUXILIARY::wp_url(),'?') === false) ? '/?' : '&').'cp_cff_resources=public&min=1', array('sslverify' => false));
			}
		}
	}catch( Exception $err ){}

    if (!empty($_GET["chs"]))
    {
        $target_charset = $_GET["chs"];
		if( !in_array($target_charset, array('utf8_general_ci', 'utf8mb4_general_ci', 'latin1_swedish_ci')) ) $target_charset = 'utf8_general_ci';

        $tables = array( $wpdb->prefix.CP_CALCULATEDFIELDSF_FORMS_TABLE, $wpdb->prefix.CP_CALCULATEDFIELDSF_POSTS_TABLE_NAME_NO_PREFIX );
        foreach ($tables as $tab)
        {
            $myrows = $wpdb->get_results( "DESCRIBE {$tab}" );
            foreach ($myrows as $item)
	        {
	            $name = $item->Field;
		        $type = $item->Type;
		        if (preg_match("/^varchar\((\d+)\)$/i", $type, $mat) || !strcasecmp($type, "CHAR") || !strcasecmp($type, "TEXT") || !strcasecmp($type, "MEDIUMTEXT"))
		        {
	                $wpdb->query("ALTER TABLE {$tab} CHANGE {$name} {$name} {$type} COLLATE {$target_charset}");
	            }
	        }
        }
    }
    $message = __( "Troubleshoot settings updated", 'calculated-fields-form' );
}
else if (isset($_POST["cp_fileimport"]) && $_POST["cp_fileimport"] == 1)
{
    check_admin_referer( 'cff-import-form', '_cpcff_nonce' );
    $filename = $_FILES['cp_filename']['tmp_name'];
	if(!empty($filename) && file_exists($filename))
	{
        if(($handle = fopen($filename, "r")) !== false)
        {
            $contents = fread($handle, filesize($filename));
            if($contents)
            {
                $contents = preg_replace('/^[\t\r\n\s]*/', '', $contents);
                $contents = preg_replace('/[\t\r\n\s]*$/', '', $contents);
                $contents = CPCFF_AUXILIARY::clean_bom($contents);
                $contents_php = unserialize($contents);
                if($contents_php !== false)
                {
                    $addons_array = (!empty($contents_php['addons'])) ? $contents_php['addons'] : array();
                    unset($contents_php['addons']);

                    if($wpdb->insert( $wpdb->prefix.CP_CALCULATEDFIELDSF_FORMS_TABLE, $contents_php ))
                    {
                        /**
                         *	Passes the array with the addons data and the form's id.
                         */
                        do_action('cpcff_import_addons', $addons_array, $wpdb->insert_id);
                        $message = __( "Import action executed.", 'calculated-fields-form' );
                    }
                    else
                    {
                        $message = __( "Error message: ", 'calculated-fields-form' ).$wpdb->last_error;
                        $message .= '<br><span style="color:#d63638;font-size:1.2em;">'.__( "Please, install the latest plugin update", 'calculated-fields-form' ).'</span>';
                    }
                }
                else
                {
                    $message = __( "The file's content is not a valid serialized PHP object.", 'calculated-fields-form' );
                }
            }
            else
            {
                $message = __( "It is not possible to read the file's content.", 'calculated-fields-form' );
            }
            fclose($handle);
        }
        @unlink($filename);
	}
	else
	{
		$message = __( "The file is inaccessible.", 'calculated-fields-form' );
	}
}

$orderby = get_option('CP_CALCULATEDFIELDSF_FORMS_LIST_ORDERBY', 'id'); // For sortin the forms list
if ($message) echo "<div id='setting-error-settings_updated' class='".( stripos($message, 'error') !== false ? "error" : "updated")." settings-error'><p><strong>".$message."</strong></p></div>";

?>
<div class="wrap">
<h1><?php _e( 'Calculated Fields Form', 'calculated-fields-form' ); ?></h1>
<form id="cff-register" name="registerplugin" action="admin.php?page=cp_calculated_fields_form" method="post">
    <div id="metabox_registering_area" class="postbox">
        <h3 class="hndle" style="padding:5px;margin-top:0;margin-bottom:0;"><span><?php _e( 'Registering of Plugin', 'calculated-fields-form' ); ?></span></h3>
        <div class="inside" style="margin-top:0;">
            <label><?php _e( 'Enter the buyer email address', 'calculated-fields-form' ); ?>:</label>
            <?php
                do_action( 'cpcff_register_user' );
            ?>
            <input type="submit" value="<?php esc_attr_e( 'Register', 'calculated-fields-form' ); ?>" class="button-primary" />
            <p><?php _e( 'Registering the plugin activates the commercial features and the auto-update to get the latest version.', 'calculated-fields-form' ); ?></p>
        </div>
    </div>
</form>
<script type="text/javascript">
 var cff_metabox_nonce = '<?php print esc_js( wp_create_nonce( 'cff-metabox-status' ) ); ?>';
 function cp_activateAddons()
 {
    <?php
    if(!CPCFF_AutoUpdateClss::valid())
    {
        print 'alert("'.esc_js(CPCFF_AutoUpdateClss::message()).'");
        document.location.href="#cff-register";
        return;';
    }
    ?>
    var cpcff_addons = document.getElementsByName("cpcff_addons"),
		cpcff_addons_active_list = [];
	for( var i = 0, h = cpcff_addons.length; i < h; i++ )
	{
		if( cpcff_addons[ i ].checked ) cpcff_addons_active_list.push( 'cpcff_addons_active_list[]='+encodeURIComponent( cpcff_addons[ i ].value ) );
	}
	document.location = 'admin.php?page=cp_calculated_fields_form&b=1&r='+Math.random()+( ( cpcff_addons_active_list.length ) ? '&'+cpcff_addons_active_list.join( '&' ) : '' )+'&_cpcff_nonce=<?php echo wp_create_nonce( 'cff-activate-deactivate-addons' ); ?>#addons-section';
 }

 function cp_addItem()
 {
    var e = jQuery("#cp_itemname"),
		form_tag = e.closest('form')[0],
		calname  = e.val().replace(/^\s*/, '').replace(/^\s*/, '').replace(/\s*$/, ''),
        category = document.getElementById("calculated-fields-form-category").value;

	e.val(calname);

	if('reportValidity' in form_tag && !form_tag.reportValidity()) return;

	document.location = 'admin.php?page=cp_calculated_fields_form&a=1&r='+Math.random()+'&name='+encodeURIComponent(calname)+'&category='+encodeURIComponent(category)+'&_cpcff_nonce=<?php echo wp_create_nonce( 'cff-add-form' ); ?>';
 }

 function cp_addItem_keyup( e )
 {
    e.which = e.which || e.keyCode;
    if(e.which == 13) cp_addItem();
 }

 function cp_updateItem(id)
 {
    var calname = document.getElementById("calname_"+id).value;
    document.location = 'admin.php?page=cp_calculated_fields_form&u='+id+'&r='+Math.random()+'&name='+encodeURIComponent(calname)+'&_cpcff_nonce=<?php echo wp_create_nonce( 'cff-update-form' ); ?>';
 }

 function cp_cloneItem(id)
 {
    document.location = 'admin.php?page=cp_calculated_fields_form&c='+id+'&r='+Math.random()+'&_cpcff_nonce=<?php echo wp_create_nonce( 'cff-clone-form' ); ?>';
 }

 function cp_manageSettings(id)
 {
    document.location = 'admin.php?page=cp_calculated_fields_form&cal='+id+'&r='+Math.random()+'&_cpcff_nonce=<?php echo wp_create_nonce( 'cff-form-settings' ); ?>';
 }

 function cp_viewMessages(id)
 {
    <?php
    if(!CPCFF_AutoUpdateClss::valid())
    {
        print 'alert("'.esc_js(CPCFF_AutoUpdateClss::message()).'");
        document.location.href="#cff-register";
        return;';
    }
    ?>
    document.location = 'admin.php?page=cp_calculated_fields_form&cal='+id+'&list=1&r='+Math.random()+'&_cpcff_nonce=<?php echo wp_create_nonce( 'cff-submissions-list' ); ?>';
 }

 function cp_BookingsList(id)
 {
    document.location = 'admin.php?page=cp_calculated_fields_form&cal='+id+'&list=1&r='+Math.random();
 }

 function cp_deleteItem(id)
 {
    if (confirm('<?php _e( 'Are you sure you want to delete this item?', 'calculated-fields-form' ); ?>'))
    {
        document.location = 'admin.php?page=cp_calculated_fields_form&d='+id+'&r='+Math.random()+'&_cpcff_nonce=<?php echo wp_create_nonce( 'cff-delete-form' ); ?>';
    }
 }

 function cp_updateConfig()
 {
    if (confirm('<?php _e( 'Are you sure you want to update these settings?', 'calculated-fields-form' ); ?>'))
    {
        var scr = document.getElementById("ccscriptload").value,
			chs = document.getElementById("cccharsets").value,
			dr  = (document.getElementById("ccdisablerevisions").checked) ? 1 : 0,
			jsc = (document.getElementById("ccjscache").checked) ? 1 : 0,
			optm = (document.getElementById("ccoptimizationplugin").checked) ? 1 : 0,
			cdm = (document.getElementById("cccaptchadirectmode").checked) ? 1 : 0,
			fmc = (document.getElementById("ccformcache").checked) ? 1 : 0,
			ecr = (document.getElementById("ccexcludecrawler").checked) ? 1 : 0,
			ehr = (document.getElementById("ccemailheader").checked) ? 1 : 0,
			em  = (document.getElementById("ccencodingemail").checked) ? 1 : 0,
			es  = (document.getElementById("ccearlysession").checked) ? 1 : 0,
			df  = (document.getElementById("ccdirectform").checked) ? 1 : 0,
			amp = (document.getElementById("ccampform").checked) ? 1 : 0,
			typc = (document.getElementById("cctypcache").checked) ? 1 : 0,
			nc  = (document.getElementById("ccusenonce").checked) ? 1 : 0,
			hp  =  document.getElementById("cchoneypot").value.replace( /^\s+/, '' ).replace( /\s+$/, '' );
		document.location = 'admin.php?page=cp_calculated_fields_form&ecr='+ecr+'&ac=st&scr='+scr+'&chs='+chs+'&dr='+dr+'&jsc='+jsc+'&optm='+optm+'&cdm='+cdm+'&fmc='+fmc+'&ehr='+ehr+'&em='+em+'&es='+es+'&df='+df+'&amp='+amp+'&typc='+typc+'&nc='+nc+'&hp='+encodeURIComponent( hp )+'&r='+Math.random()+'&_cpcff_nonce=<?php echo wp_create_nonce( 'cff-update-general-settings' ); ?>';
    }
 }

 function cp_exportItem()
 {
    <?php
    if(!CPCFF_AutoUpdateClss::valid())
    {
        print 'alert("'.esc_js(CPCFF_AutoUpdateClss::message()).'");
        document.location.href="#cff-register";
        return;';
    }
    ?>
    var calname = document.getElementById("exportid").options[document.getElementById("exportid").options.selectedIndex].value;
    document.location = 'admin.php?page=cp_calculated_fields_form&cp_calculatedfieldsf_export=1&r='+Math.random()+'&name='+encodeURIComponent(calname)+'&_cpcff_nonce=<?php echo wp_create_nonce( 'cff-export-form' ); ?>';
 }

 function cp_importItem()
 {
    <?php
    if(!CPCFF_AutoUpdateClss::valid())
    {
        print 'alert("'.esc_js(CPCFF_AutoUpdateClss::message()).'");
        document.location.href="#cff-register";
        return false;';
    }
    ?>
    return true;
 }

 function cp_select_template()
 {
    jQuery('.cp_template_info').hide();
    jQuery('.cp_template_'+jQuery('#cp_default_template').val()).show();
 }

 function cp_update_default_settings(e)
 {
    if(jQuery('[name="cp_default_existing_forms"]').prop('checked'))
    {
        if (confirm('<?php _e( 'Are you sure you want to modify existing forms?\\nWe recommend modifying the forms one by one.', 'calculated-fields-form' ); ?>'))
        {
            e.form.submit();
        }
    }
    else e.form.submit();
 }
</script>
<h2 class="nav-tab-wrapper">
    <a href="admin.php?page=cp_calculated_fields_form&cff-tab=forms" class="nav-tab <?php if(empty($_GET['cff-tab']) || $_GET['cff-tab'] == 'forms' ) print 'nav-tab-active'; ?>"><?php _e('Forms and Settings', 'calculated-fields-form'); ?></a>
    <a href="admin.php?page=cp_calculated_fields_form&cff-tab=marketplace" class="nav-tab <?php if(!empty($_GET['cff-tab']) && $_GET['cff-tab'] == 'marketplace' ) print 'nav-tab-active'; ?>"><?php _e('Marketplace', 'calculated-fields-form'); ?></a>
</h2>
<div style="margin-top:20px;display:<?php print (empty($_GET['cff-tab']) || $_GET['cff-tab'] == 'forms' ) ? 'block' : 'none'; ?>;"><!-- Forms & Settings Section -->
    <div id="normal-sortables" class="meta-box-sortables">

        <!-- Form Categories -->
        <div id="metabox_categories_list" class="postbox" >
            <div class="inside" style="overflow-x:auto;">
                <form action="admin.php?page=cp_calculated_fields_form" method="post">
                    <?php
                        if(isset($_POST['calculated-fields-form-category']))
                        {
                            check_admin_referer( 'cff-change-category', '_cpcff_nonce' );
                            update_option('calculated-fields-form-category', stripcslashes(sanitize_text_field($_POST['calculated-fields-form-category'])));
                        }
                        $cff_current_form_category = get_option('calculated-fields-form-category', '');
                    ?>
                    <input type="hidden" name="_cpcff_nonce" value="<?php echo wp_create_nonce( 'cff-change-category' ); ?>" />
                    <b><?php _e('Form Categories', 'calculated-fields-form'); ?></b>
                    <select name="calculated-fields-form-category" class="width50" onchange="this.form.submit();">
                        <option value=""><?php print esc_html(__('All forms', 'calculated-fields-form')); ?></option>
                        <?php
                            print $cpcff_main->get_categories('SELECT', $cff_current_form_category);
                        ?>
                    </select>
                </form>
            </div>
        </div>

        <!-- Forms List -->
        <div id="metabox_form_list" class="postbox" >
            <h3 class='hndle' style="padding:5px;"><span><?php
                _e( 'Form List / Items List', 'calculated-fields-form' );

                if($cff_current_form_category != '')
                {
                    print '&nbsp;'.__('in', 'calculated-fields-form').'&nbsp;<u>'.$cff_current_form_category.'</u>&nbsp;'.__('category', 'calculated-fields-form');
                }
            ?></span></h3>
            <div class="inside" style="overflow-x:auto;">
                <table cellspacing="10" class="cff-custom-table cff-forms-list">
                    <thead>
                        <tr>
                            <th align="left"><a href="?page=cp_calculated_fields_form&orderby=id" <?php if($orderby == 'id') print 'class="cff-active-column"'; ?>><?php _e( 'ID', 'calculated-fields-form' ); ?></a></th>
                            <th align="left"><a href="?page=cp_calculated_fields_form&orderby=form_name" <?php if($orderby == 'form_name') print 'class="cff-active-column"'; ?>><?php _e( 'Form Name', 'calculated-fields-form' ); ?></a></th>
                            <th align="center"><?php _e( 'Options', 'calculated-fields-form' ); ?></th>
                            <th align="left"><?php _e( 'Category/Shortcode', 'calculated-fields-form' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $myrows = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix.CP_CALCULATEDFIELDSF_FORMS_TABLE.($cff_current_form_category != '' ? $wpdb->prepare(' WHERE category=%s ', $cff_current_form_category) : '')." ORDER BY ".$orderby." ASC" );
                        foreach ($myrows as $item)
                        {
                    ?>
                        <tr>
                            <td nowrap><?php echo $item->id; ?></td>
                            <td nowrap><input type="text" name="calname_<?php echo $item->id; ?>" id="calname_<?php echo $item->id; ?>" value="<?php echo esc_attr($item->form_name); ?>" /></td>
                            <td nowrap>
                                <input type="button" name="calupdate_<?php echo $item->id; ?>" value="<?php esc_attr_e( 'Update', 'calculated-fields-form' ); ?>" onclick="cp_updateItem(<?php echo $item->id; ?>);" class="button-secondary" />
                                <input type="button" name="calmanage_<?php echo $item->id; ?>" value="<?php esc_attr_e( 'Settings', 'calculated-fields-form' ); ?>" onclick="cp_manageSettings(<?php echo $item->id; ?>);" class="button-primary" />
                                <input type="button" name="calmanage_<?php echo $item->id; ?>" value="<?php esc_attr_e( 'Messages', 'calculated-fields-form' ); ?>" onclick="cp_viewMessages(<?php echo $item->id; ?>);" class="button-secondary" />
                                <input type="button" name="calclone_<?php echo $item->id; ?>" value="<?php esc_attr_e( 'Clone', 'calculated-fields-form' ); ?>" onclick="cp_cloneItem(<?php echo $item->id; ?>);" class="button-secondary" />
                                <input type="button" name="caldelete_<?php echo $item->id; ?>" value="<?php esc_attr_e( 'Delete', 'calculated-fields-form' ); ?>" onclick="cp_deleteItem(<?php echo $item->id; ?>);" class="button-secondary" />
                            </td>
                            <td><?php if(!empty($item->category)) print __('Category: ', 'calculated-fields-form').'<b>'.esc_html($item->category).'</b><br>'; ?><div style="white-space:nowrap;">[CP_CALCULATED_FIELDS id="<?php echo $item->id; ?>"]</div></td>
                        </tr>
                    <?php
                        }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="metabox_new_form_area" class="postbox" >
            <h3 class='hndle' style="padding:5px;"><span><?php _e( 'New Form', 'calculated-fields-form' ); ?></span></h3>
            <div class="inside">
                <form name="additem">
                    <?php _e( 'Item Name', 'calculated-fields-form' ); ?>(*):<br />
					<div>
						<input type="text" name="cp_itemname" id="cp_itemname"  value="" onkeyup="cp_addItem_keyup( event );"  style="margin-top:5px;" required />
						<input type="text" name="calculated-fields-form-category" id="calculated-fields-form-category"  value="<?php print esc_attr($cff_current_form_category); ?>" style="margin-top:5px;" placeholder="<?php esc_attr_e('Category', 'calculated-fields-form'); ?>" list="calculated-fields-form-categories" />
						<datalist id="calculated-fields-form-categories">
							<?php
								print $cpcff_main->get_categories('DATALIST');
							?>
						</datalist>
						<input type="button" onclick="cp_addItem();" name="gobtn" value="<?php esc_attr_e( 'Create Form', 'calculated-fields-form' ); ?>" class="button-secondary" style="margin-top:5px;" />
						<input type="button" onclick="cff_openLibraryDialog();" name="gobtn" value="<?php esc_attr_e( 'From Template', 'calculated-fields-form' ); ?>" class="button-secondary" style="margin-top:5px;" />
                    </div>
                </form>
            </div>
        </div>
        <i id="default-settings-section"></i>
        <div id="metabox_default_settings" class="postbox cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_default_settings' ) ); ?>" >
            <h3 class='hndle' style="padding:5px;"><span><?php _e( 'Default Settings', 'calculated-fields-form' ); ?></span></h3>
            <div class="inside">
                <p><?php _e('Applies the default settings to new forms.', 'calculated-fields-form'); ?></p>
                <form name="defaultsettings" action="admin.php?page=cp_calculated_fields_form" method="post">
                    <?php _e( 'Default Template', 'calculated-fields-form' ); ?>:<br />
                    <?php
                        require_once CP_CALCULATEDFIELDSF_BASE_PATH.'/inc/cpcff_templates.inc.php';
                        $templates_list = CPCFF_TEMPLATES::load_templates();
                        $template_options = '<option value="">Use default template</option>';
                        $template_information =  '';
                        foreach($templates_list as $template_item)
                        {
                            $template_options .= '<option value="'.esc_attr($template_item['prefix']).'" '.($template_item['prefix'] == $cp_default_template ? 'SELECTED' : '').'>'.esc_html($template_item['title']).'</option>';
                            $template_information .= '<div class="width50 cp_template_info cp_template_'.esc_attr($template_item['prefix']).'" style="text-align:center;padding:10px 0; display:'.($template_item['prefix'] == $cp_default_template ? 'block' : 'none').'; margin:10px 0; border: 1px dashed #CCC;">'.(!empty($template_item['thumbnail']) ? '<img src="'.esc_attr($template_item['thumbnail']).'"><br>' : '').(!empty($template_item['description']) ? esc_html($template_item['description']) : '').'</div>';
                        }
                    ?>
                    <select name="cp_default_template" id="cp_default_template"class="width50" onchange="cp_select_template();"><?php print $template_options; ?></select><br />
                    <?php print $template_information; ?>
                    <br />
                    <input type="checkbox" aria-label="<?php esc_attr_e('Activate Captcha by Default', 'calculated-fields-form'); ?>" name="cp_default_captcha" <?php print($cp_default_captcha == 'true' ? 'CHECKED' : ''); ?> /> <?php _e( 'Activate Captcha by Default', 'calculated-fields-form' ); ?><br /><br />
                    <input type="checkbox" aria-label="<?php esc_attr_e('Display Submit Button by Default', 'calculated-fields-form'); ?>" name="cp_default_submit" <?php print($cp_default_submit == '' ? 'CHECKED' : ''); ?> /> <?php _e( 'Display Submit Button by Default', 'calculated-fields-form' ); ?><br /><br />
                    <div style="border:1px solid #DADADA; padding:10px;" class="width50">
                        <input type="checkbox" aria-label="<?php esc_attr_e('Apply To Existing Forms', 'calculated-fields-form'); ?>" name="cp_default_existing_forms" /> <?php _e( 'Apply To Existing Forms', 'calculated-fields-form' ); ?> (<i><?php _e('It will modify the settings of existing forms', 'calculated-fields-form'); ?></i>)
                    </div>
                    <br />
                    <input type="button" name="cp_save_default_settings" value="<?php esc_attr_e( 'Update', 'calculated-fields-form' ); ?>" class="button-secondary" onclick="cp_update_default_settings(this);" />
                    <input type="hidden" name="_cpcff_nonce" value="<?php echo wp_create_nonce( 'cff-default-settings' ); ?>" />
                </form>
            </div>
        </div>
        <a name="addons-section"></a>
        <h2><?php _e( 'Add-Ons Settings', 'calculated-fields-form' ); ?>:</h2><hr />
        <div style="border:1px solid #F0AD4E;background:#FBE6CA;padding:10px;margin:10px 0;font-size:1.3em;">
            <div><?php _e('For additional resources visit the plugin\'s', 'calculated-fields-form')?> <a href="https://cff-bundles.dwbooster.com" target="_blank" style="font-weight:bold;"><?php _e('Marketplace', 'calculated-fields-form'); ?></a></div>
            <div class="cff-bundles-plugin"></div>
        </div>
        <div id="metabox_addons_area" class="postbox cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_addons_area' ) ); ?>" >
            <h3 class='hndle' style="padding:5px;"><span><?php _e( 'Add-ons Area', 'calculated-fields-form' ); ?></span></h3>
            <div class="inside">
                <?php
                // Prints the add-ons list
                CPCFF_ADDONS::print_list();
                ?>
                <div style="margin-top:20px;"><input type="button" onclick="cp_activateAddons();" name="activateAddon" value="<?php esc_attr_e( 'Activate/Deactivate Addons', 'calculated-fields-form' ); ?>" class="button-secondary" /></div>
            </div>
        </div>
        <?php
            // Prints the add-ons settings
            CPCFF_ADDONS::print_settings();
        ?>
        <div id="metabox_troubleshoot_area" class="postbox cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_troubleshoot_area' ) ); ?>" >
            <h3 class='hndle' style="padding:5px;"><span><?php _e( 'Troubleshoot Area & General Settings', 'calculated-fields-form' ); ?></span></h3>
            <div class="inside">
                <form name="updatesettings">
                    <div style="border:1px solid #DADADA; padding:10px;">
                        <p><?php _e( '<strong>Important!</strong>: Use this area <strong>only</strong> if you are experiencing conflicts with third party plugins, with the theme scripts or with the character encoding.', 'calculated-fields-form' ); ?></p>
                        <?php _e( 'Script load method', 'calculated-fields-form' ); ?>:<br />
                        <select id="ccscriptload" name="ccscriptload"  class="width50">
                            <option value="0" <?php if (get_option('CP_CFF_LOAD_SCRIPTS',"1") == "1") echo 'selected'; ?>><?php _e( 'Classic (Recommended)', 'calculated-fields-form' ); ?></option>
                            <option value="1" <?php if (get_option('CP_CFF_LOAD_SCRIPTS',"1") != "1") echo 'selected'; ?>><?php _e( 'Direct', 'calculated-fields-form' ); ?></option>
                        </select><br />
                        <em><?php _e( '* Change the script load method if the form doesn\'t appear in the public website.', 'calculated-fields-form' ); ?></em>
                        <br /><br />
                        <?php _e( 'Character encoding', 'calculated-fields-form' ); ?>:<br />
                        <select id="cccharsets" name="cccharsets" class="width50">
                            <option value=""><?php _e( 'Keep current charset (Recommended)', 'calculated-fields-form' ); ?></option>
                            <option value="utf8_general_ci">UTF-8 (<?php _e( 'try this first', 'calculated-fields-form' ); ?>)</option>
                            <option value="utf8mb4_general_ci">UTF-8mb4 (<?php _e( 'Only from MySQL 5.5', 'calculated-fields-form' ); ?>)</option>
                            <option value="latin1_swedish_ci">latin1_swedish_ci</option>
                        </select><br />
                        <em><?php _e( '* Update the charset if you are getting problems displaying special/non-latin characters. After updated you need to edit the special characters again.', 'calculated-fields-form' ); ?></em>
                        <br /><br />
                        <?php
                            $compatibility_warnings = $cpcff_main->compatibility_warnings();
                            if(!empty($compatibility_warnings))
                            {
                                print '<div style="margin:10px 0; border:1px dashed #FF0000; padding:10px; color:red;">'.$compatibility_warnings;
                            }
                            _e( "There is active an optimization plugin in WordPress", 'calculated-fields-form' ); ?>:<br />
                        <input type="checkbox" id="ccoptimizationplugin" name="ccoptimizationplugin" value="1" <?php echo ( get_option( 'CP_CALCULATEDFIELDSF_OPTIMIZATION_PLUGIN', CP_CALCULATEDFIELDSF_OPTIMIZATION_PLUGIN ) ) ? 'CHECKED' : ''; ?> /><em><?php _e('* Tick the checkbox if there is an optimization plugin active on the website, and the forms are not visible.', 'calculated-fields-form'); ?></em>
                        <?php
                            if(!empty($compatibility_warnings))
                            {
                                print '</div>';
                            }
                        ?>
                        <br /><br />
                        <?php _e( "Captcha image doesn't load", 'calculated-fields-form' ); ?>:<br />
                        <input type="checkbox" id="cccaptchadirectmode" name="cccaptchadirectmode" value="1" <?php echo ( get_option( 'CP_CALCULATEDFIELDSF_CAPTCHA_DIRECT_MODE', false ) ) ? 'CHECKED' : ''; ?> /><em><?php _e('* Tick the checkbox if the captcha code is not generated to call its script directly.', 'calculated-fields-form'); ?></em>
                        <br /><br />
                        <?php _e( 'The emails contain invalid characters', 'calculated-fields-form' ); ?>:<br />
                        <input type="checkbox" name="ccencodingemail" id="ccencodingemail" <?php echo ( get_option( 'CP_CALCULATEDFIELDSF_ENCODING_EMAIL', false ) ) ? 'CHECKED' : ''; ?> /><em><?php _e('* Encodes the notification emails as ISO-8859-2 and base64.', 'calculated-fields-form' ); ?></em><br /><br />
						<input type="checkbox" name="ccemailheader" id="ccemailheader" <?php echo ( get_option( 'CP_CALCULATEDFIELDSF_EMAIL_HEADERS', false ) ) ? 'CHECKED' : ''; ?> /> <?php _e( 'Modify the eMails Headers', 'calculated-fields-form' ); ?>
						<br /><br />
                    </div>
                    <br />
                    <input type="checkbox" name="ccdisablerevisions" id="ccdisablerevisions" <?php echo ( get_option( 'CP_CALCULATEDFIELDSF_DISABLE_REVISIONS', CP_CALCULATEDFIELDSF_DISABLE_REVISIONS ) ) ? 'CHECKED' : ''; ?> /> <?php _e( 'Disable Form Revisions', 'calculated-fields-form' ); ?>
                    <br /><br />
                    <input type="checkbox" name="ccjscache" id="ccjscache" <?php echo ( get_option( 'CP_CALCULATEDFIELDSF_USE_CACHE', CP_CALCULATEDFIELDSF_USE_CACHE ) ) ? 'CHECKED' : ''; ?> /> <?php _e( 'Activate Javascript Cache', 'calculated-fields-form' ); ?>
                    <br /><br />
                    <input type="checkbox" name="ccformcache" id="ccformcache" <?php echo ( get_option( 'CP_CALCULATEDFIELDSF_FORM_CACHE', false ) ) ? 'CHECKED' : ''; ?> /> <?php _e( 'Activate Forms Cache', 'calculated-fields-form' ); ?>
                    <br /><br />
                    <input type="checkbox" name="ccearlysession" id="ccearlysession" <?php echo ( get_option( 'CP_CALCULATEDFIELDSF_EARLY_SESSION', false ) ) ? 'CHECKED' : ''; ?> /> <?php _e( 'Start Session as Soon as Possible', 'calculated-fields-form' ); ?>
                    <br /><br />
                    <input type="checkbox" name="ccdirectform" id="ccdirectform" <?php echo ( get_option( 'CP_CALCULATEDFIELDSF_DIRECT_FORM_ACCESS', CP_CALCULATEDFIELDSF_DIRECT_FORM_ACCESS ) ) ? 'CHECKED' : ''; ?> /> <?php _e( 'Allows to access the forms directly', 'calculated-fields-form' ); ?>
                    <br /><br />
                    <input type="checkbox" name="ccampform" id="ccampform" <?php echo ( get_option( 'CP_CALCULATEDFIELDSF_AMP', CP_CALCULATEDFIELDSF_AMP ) ) ? 'CHECKED' : ''; ?> /> <?php _e( 'Allows to access the forms from amp pages', 'calculated-fields-form' ); ?>
                    <br /><br />
                    <input type="checkbox" name="cctypcache" id="cctypcache" <?php echo ( get_option( 'CP_CALCULATEDFIELDSF_TYPC', CP_CALCULATEDFIELDSF_TYPC ) ) ? 'CHECKED' : ''; ?> /> <?php _e( 'Prevents the Thank you page be cached', 'calculated-fields-form' ); ?>
                    <br /><br />
                    <input type="checkbox" name="ccexcludecrawler" id="ccexcludecrawler" <?php echo ( get_option( 'CP_CALCULATEDFIELDSF_EXCLUDE_CRAWLERS', false ) ) ? 'CHECKED' : ''; ?> /> <?php _e( 'Do not load the forms with crawlers', 'calculated-fields-form' ); ?>
                    <br /><i><?php _e( '* The forms are not loaded when website is being indexed by searchers.', 'calculated-fields-form' ); ?></i>
                    <br /><br />
                    <strong><?php _e( 'Protect the forms against the spam bots', 'calculated-fields-form' ); ?></strong><br /><br />
                    <?php _e( 'Enter an unique field name', 'calculated-fields-form' ); ?>:<br><input type="text" name="cchoneypot" id="cchoneypot" value="<?php echo get_option( 'CP_CALCULATEDFIELDSF_HONEY_POT', '' ); ?>" class="width50" /><br />
                    <i><?php _e( '* Adds a hidden text field to the forms to trap the spam bots.', 'calculated-fields-form' ); ?></i>
                    <br /><br />
                    <input type="checkbox" name="ccusenonce" id="ccusenonce" <?php echo ( @intval(get_option( 'CP_CALCULATEDFIELDSF_NONCE', false )) ) ? 'CHECKED' : ''; ?> /> <?php _e( 'Protect the forms with nonce', 'calculated-fields-form' ); ?>
                    <br /><br />
                    <input type="button" onclick="cp_updateConfig();" name="gobtn" value="<?php esc_attr_e( 'UPDATE', 'calculated-fields-form' ); ?>" class="button-secondary" />
                </form>
            </div>
        </div>

        <div id="metabox_import_export_area" class="postbox cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_import_export_area' ) ); ?>" >
            <h3 class='hndle' style="padding:5px;"><span><?php _e( 'Import / Export Area', 'calculated-fields-form' ); ?></span></h3>
            <div class="inside">
                <p><?php _e( 'Use this area <strong>only</strong> to <strong>import/export the form\'s structure to the plugin in other (external) websites</strong>. If what you want is to duplicate a form into this website then use the "Clone" button. If what you want is to export the submissions then go to the messages list for the selected form.', 'calculated-fields-form' ); ?></p>
                <hr />
                <form name="exportitem">
                    <?php _e( 'Export this form structure and settings', 'calculated-fields-form' ); ?>:<br />
                    <select id="exportid" name="exportid" class="width50">
                        <?php
                        foreach ($myrows as $item)
                            echo '<option value="'.$item->id.'">'.$item->id.' - '.$item->form_name.'</option>';
                        ?>
                    </select>
                    <input type="button" onclick="cp_exportItem();" name="gobtn" value="<?php esc_attr_e( 'Export', 'calculated-fields-form' ); ?>" class="button-secondary" />
                    <br /><br />
                </form>
                <hr />
                <form name="importitem" action="admin.php?page=cp_calculated_fields_form" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="cp_fileimport" id="cp_fileimport"  value="1" />
                    <?php _e( 'Import a form structure and settings (only <em>.cpfm</em> files )', 'calculated-fields-form' ); ?>:<br />
                    <input type="file" name="cp_filename" id="cp_filename"  value="" class="width50" /> <input type="submit" name="gobtn" value="<?php esc_attr_e( 'Import', 'calculated-fields-form' ); ?>" class="button-secondary" onclick="return cp_importItem();" />
                    <input type="hidden" name="_cpcff_nonce" value="<?php echo wp_create_nonce( 'cff-import-form' ); ?>" />
                    <br /><br />
                </form>
            </div>
        </div>
    </div>
</div><!-- End Forms & Settings Section -->
<div style="margin-top:20px;display:<?php print (!empty($_GET['cff-tab']) && $_GET['cff-tab'] == 'marketplace' ) ? 'block' : 'none'; ?>;"><!-- Marketplace Section -->
    <div id="metabox_basic_settings" class="postbox" >
        <h3 class='hndle' style="padding:5px;"><span><?php _e( 'Calculated Fields Form Marketplace', 'calculated-fields-form' ); ?></span></h3>
        <div class="inside">
            <div class="cff-marketplace"></div>
        </div>
    </div>
</div><!-- End Marketplace Section -->
[<a href="https://cff.dwbooster.com/customization" target="_blank"><?php _e( 'Request Custom Modifications', 'calculated-fields-form' ); ?></a>] | [<a href="https://cff.dwbooster.com/documentation" target="_blank"><?php _e( 'Help', 'calculated-fields-form' ); ?></a>]
</div>
<script>cff_current_version='platinum';</script>
<script src="https://cff-bundles.dwbooster.com/plugins/plugins.js?v=<?php print CP_CALCULATEDFIELDSF_VERSION.'_'.date('Y-m-d'); ?>"></script>
<script src="https://cff.dwbooster.com/forms/forms.js?v=<?php print CP_CALCULATEDFIELDSF_VERSION.'_'.date('Y-m-d'); ?>"></script>