<?php
if ( !is_admin() )
{
	print 'Direct access not allowed.';
    exit;
}

if (!defined('CP_CALCULATEDFIELDSF_ID'))
		define ('CP_CALCULATEDFIELDSF_ID',intval($_GET["cal"]));

if( !empty( $_REQUEST['le'] ))
{
	// Check nonce
	check_admin_referer( 'cff-edit-submission', '_cpcff_nonce' );

	$_REQUEST = CPCFF_AUXILIARY::stripcslashes_recursive($_REQUEST);

	/**
	 * Loads the form's fields for edition
	 */
	$_le 	= intval(@$_REQUEST['le']);

	print '<h1>'.__('Editing entry', 'calculated-fields-form').': '.$_le.'</h1>';

	// Return to the submissions list button.
	$_rtn_btn = '<input type="button" name="backbtn" value="'.esc_attr__( 'Back to events list...', 'calculated-fields-form' ).'" onclick="document.location.href=\'admin.php?page=cp_calculated_fields_form&cal='.urlencode(CP_CALCULATEDFIELDSF_ID).'&list=1&r='.rand().'&_cpcff_nonce='.urlencode(wp_create_nonce( 'cff-submissions-list' )).'\';" class="button-secondary"  />';

	$_data 		= CPCFF_SUBMISSIONS::get($_le);

	if($_data)
	{
		if(isset($_data->paypal_post))
		{
			$_form_obj = CPCFF_SUBMISSIONS::get_form($_le);
			if(is_object($_form_obj)) // The form exists, get the fields names and labels
			{
				$_form_fields = $_form_obj->get_fields();
			}

			if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'update')
			{
				$_data->data  = "";
				$_files_links = ''; // To create the links to display at the end of summary
				foreach($_REQUEST as $_field_name => $_field_value)
				{
					if(
						preg_match('/fieldname\d+/', $_field_name) &&
						isset($_data->paypal_post[$_field_name])
					)
					{
						$_data->paypal_post[$_field_name] = $_field_value;

						// Complete the summary
						if(preg_match('/_((link(s)?)|(url))$/', $_field_name)) continue;
						elseif(preg_match('/_urls$/', $_field_name))
						{
							$_field_value = preg_replace(
								'/http(s)?:[^\r\n]+/i',
								'<a href="$0" target="_blank">$0</a>',
								$_field_value
							);
							$_files_links .= $_field_value;
							continue;
						}
						elseif(
							!empty($_form_fields) &&
							isset($_form_fields[$_field_name]) &&
							!empty($_form_fields[$_field_name]->title)
						)
						{
							$_data->data .= $_form_fields[$_field_name]->title;
						}
						else
						{
							$_data->data .= $_field_name;
						}
						$_data->data .= ": ".$_field_value."\n"; // FROM \n\n to \n
					}
				}

				// Include the coupon information if exists
				if(!empty($_data->paypal_post['coupon'])) $_data->data .= "Coupon code: ".$_data->paypal_post['coupon']."\n"; // FROM \n\n to \n

				// Include the links at the end of summary
				$_data->data .= $_files_links;

				// Update submission
				CPCFF_SUBMISSIONS::update($_le, $_data);
				print '<div class="notice notice-success" style="margin:20px 0;">'.__('Entry updated', 'calculated-fields-form').'</div>';
			}

			print '
			<div class="postbox">
				<div class="inside">
					<form method="post">
						<input type="hidden" name="action" value="update" />
						<input type="hidden" name="le" value="'.esc_attr($_le).'" />
						'.wp_nonce_field('cff-edit-submission');
			foreach($_data->paypal_post as $_field_name => $_field_value)
			{
				if(preg_match('/fieldname\d+/', $_field_name))
				{
					print '<label>';
					// Checks if the field exists in the form and has assigned a title
					if(
						!empty($_form_fields) &&
						isset($_form_fields[$_field_name]) &&
						!empty($_form_fields[$_field_name]->title)
					)
					{
						print $_form_fields[$_field_name]->title;
					}
					else
					{
						print $_field_name;

					}
					print '</label><div><textarea name="'.esc_attr($_field_name).'" style="width:100%;resize:vertical;">'.esc_textarea(is_array($_field_value) ? implode(',', $_field_value) : $_field_value).'</textarea></div>';
				}
			}
			print '
						</table>
						<input type="submit" value="'.__('Save Changes', 'calculated-fields-form').'" class="button-primary">
						'.$_rtn_btn.'
					</form>
				</div>
			</div>';
			$_rtn_btn = '';
		}
		else
		{
			// No data
			print '<div class="notice notice-error" style="margin:20px 0;">'.__('There are no data associated to this submission', 'calculated-fields-form').'</div>';
		}
	}
	else
	{
		// The submission is invalid or was delete
		print '<div class="notice notice-error" style="margin:20px 0;">'.__('The submission id is invalid or the submission does not exits', 'calculated-fields-form').'</div>';
	}
	print $_rtn_btn;
}
else
{
	if(isset($_GET['ld'])) check_admin_referer( 'cff-delete-submission', '_cpcff_nonce' );
	elseif(isset($_GET['lu'])) check_admin_referer( 'cff-paid-status', '_cpcff_nonce' );
	elseif(isset($_GET['da'])) check_admin_referer( 'cff-delete-all-submissions', '_cpcff_nonce' );
	else check_admin_referer( 'cff-submissions-list', '_cpcff_nonce' );

	/**
	 * Process the submissions list
	 */

	$_GET['lu'] = isset($_GET['lu']) ? @intval($_GET['lu']) : 0;

	if( isset( $_GET['ld'] ) && is_array( $_GET['ld'] ) )
	{
		foreach( $_GET['ld'] as $key => $ld ) $_GET['ld'][ $key ] = intval( $ld );
	}
	else
	{
		$_GET['ld'] = isset($_GET['ld']) ? @intval($_GET['ld']) : 0;
	}



	global $wpdb;

	$message = "";

	if ( !empty( $_GET['lu'] ) )
	{
		check_admin_referer( 'cff-paid-status', '_cpcff_nonce' );
		if(CPCFF_SUBMISSIONS::update($_GET['lu'], array('paid'=>$_GET["status"])))
		{
			/**
			 * Action called after change the payment status of event
			 * The event's id and new payment's status are passed as parameters
			 */
			do_action( 'cpcff_change_payment_status', $_GET['lu'], $_GET["status"]);
			$message = __("Item updated", 'calculated-fields-form' );
		}
	}
	else if ( !empty( $_GET['ld'] ) )
	{
		check_admin_referer( 'cff-delete-submission', '_cpcff_nonce' );
		if( is_array( $_GET['ld'] ) )
		{
			foreach( $_GET['ld'] as $ld ) CPCFF_SUBMISSIONS::delete($ld);
		}
		else
		{
			CPCFF_SUBMISSIONS::delete($_GET['ld']);
		}
		$message = __("Item(s) deleted", 'calculated-fields-form' );
	}
    else if ( !empty( $_GET['da'] ) )
    {
		check_admin_referer( 'cff-delete-all-submissions', '_cpcff_nonce' );
        $events = $wpdb->get_results('SELECT id FROM '.CP_CALCULATEDFIELDSF_POSTS_TABLE_NAME);
        foreach($events as $event) CPCFF_SUBMISSIONS::delete($event->id);
        $message = __("Item(s) deleted", 'calculated-fields-form' );
    }

	$form_list_opts = '';

    // $form_list = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix.CP_CALCULATEDFIELDSF_FORMS_TABLE." ORDER BY id" );
    $form_list = $wpdb->get_results(
        "(SELECT id, form_name FROM ".$wpdb->prefix.CP_CALCULATEDFIELDSF_FORMS_TABLE.")
        UNION
        (SELECT formid as id, '' as form_name FROM ".CP_CALCULATEDFIELDSF_POSTS_TABLE_NAME." WHERE formid NOT IN (SELECT id FROM ".$wpdb->prefix.CP_CALCULATEDFIELDSF_FORMS_TABLE."))
        ORDER BY id"
    );

	foreach( $form_list as $form )
	{
		$selected_opt = '';
		if( $form->id == CP_CALCULATEDFIELDSF_ID ){ $myform = $form; $selected_opt = 'SELECTED'; }
		$form_list_opts .= '<option value="'.esc_attr( $form->id ).'" '.$selected_opt.'>'.$form->id.' - '.$form->form_name.'</option>';
	}

	$current_page = isset($_GET["p"]) ? @intval($_GET["p"]) : 0;
	if (!$current_page) $current_page = 1;
	$records_per_page = 50;

	$cond = '';
	if(isset($_GET["search"]) && $_GET["search"] != '')
		$cond .= $wpdb->prepare(" AND (data like %s OR paypal_post LIKE %s)", '%'.$_GET["search"].'%', '%'.$_GET["search"].'%');
	if(isset($_GET["dfrom"]) && $_GET["dfrom"] != '')
		$cond .= $wpdb->prepare(" AND (`time` >= %s)", $_GET["dfrom"]);
	if(isset($_GET["dto"]) && $_GET["dto"] != '')
		$cond .= $wpdb->prepare(" AND (`time` <= %s)", $_GET["dto"].' 23:59:59');
    if(isset($_GET['paid']))
		$cond .= " AND `paid`=1";

	$events_query = "SELECT * FROM ".CP_CALCULATEDFIELDSF_POSTS_TABLE_NAME." WHERE formid".((CP_CALCULATEDFIELDSF_ID == 0)?"<>":"=").CP_CALCULATEDFIELDSF_ID.$cond." ORDER BY `time` DESC";
	/**
	 * Allows modify the query of messages, passing the query as parameter
	 * returns the new query
	 */
	$events_query = apply_filters( 'cpcff_messages_query', $events_query );
	$events = CPCFF_SUBMISSIONS::populate( $events_query );

	$total_pages = ceil(count($events) / $records_per_page);
	if ($message) echo "<div id='setting-error-settings_updated' class='updated settings-error'><p><strong>".$message."</strong></p></div>";
	?>
	<script type="text/javascript">
	 function cp_moreInfo( e )
	 {
		try{
			var $ = jQuery,
				e = $( e ),
				t = e.text();
			e.text( ( t.indexOf( '+' ) != -1 ) ? t.replace( '+', '-' ) : t.replace( '-', '+' ) )
			 .closest( 'td' )
			 .find( 'div.paypal_data' )
			 .toggle();

		}catch( err ){}
	 }
	 function cp_checkAllItems( e )
	 {
		try{
			var $  = jQuery;
			$( e ).closest( 'table' ).find( 'input[type="checkbox"]' ).prop( 'checked', $( e ).prop( 'checked' ) );
		}catch( err ){}
	 }
	 function cp_deleteAllTicked( )
	 {
		try{
			var $  = jQuery,
				ld = [];

			$( '.cp_item:checked' ).each( function(){ ld.push( 'ld[]='+this.value ); } );
			if( ld.length )
			{
				if (confirm('<?php _e( 'Are you sure that you want to delete these items?', 'calculated-fields-form' ); ?>'))
				{
					document.location = 'admin.php?page=cp_calculated_fields_form&cal=<?php echo CP_CALCULATEDFIELDSF_ID; ?>&list=1&'+ld.join( '&' )+'&r='+Math.random()+'&_cpcff_nonce=<?php echo wp_create_nonce( 'cff-delete-submission' ); ?>';
				}
			}
		}catch( err ){}
	 }
	 function cp_deleteAll( )
	 {
		try{
            if (confirm('<?php _e( 'Are you sure that you want to delete all items? Please note that you will not be able to recover them.', 'calculated-fields-form' ); ?>'))
            {
                document.location = 'admin.php?page=cp_calculated_fields_form&cal=<?php echo CP_CALCULATEDFIELDSF_ID; ?>&list=1&r='+Math.random()+'&da=1&_cpcff_nonce=<?php echo wp_create_nonce( 'cff-delete-all-submissions' ); ?>';
            }
		}catch( err ){}
	 }
	 function cp_updateMessageItem(id,status)
	 {
		document.location = 'admin.php?page=cp_calculated_fields_form&cal=<?php echo CP_CALCULATEDFIELDSF_ID; ?>&list=1&status='+status+'&lu='+id+'&r='+Math.random( )+'&_cpcff_nonce=<?php echo wp_create_nonce( 'cff-paid-status' ); ?>';
	 }
	 function cp_editMessageItem(id)
	 {
		document.location = 'admin.php?page=cp_calculated_fields_form&cal=<?php echo CP_CALCULATEDFIELDSF_ID; ?>&list=1&le='+id+'&r='+Math.random( )+'&_cpcff_nonce=<?php echo wp_create_nonce( 'cff-edit-submission' ); ?>';
	 }
	 function cp_deleteMessageItem(id)
	 {
		if (confirm('<?php _e( 'Are you sure that you want to delete this item?', 'calculated-fields-form' ); ?>'))
		{
			document.location = 'admin.php?page=cp_calculated_fields_form&cal=<?php echo CP_CALCULATEDFIELDSF_ID; ?>&list=1&ld='+id+'&r='+Math.random()+'&_cpcff_nonce=<?php echo wp_create_nonce( 'cff-delete-submission' ); ?>';
		}
	 }
	 function do_dexapp_print()
	 {
		  w=window.open();
		  w.document.write("<style>table{border:2px solid black;width:100%;}th{border-bottom:2px solid black;text-align:left}td{padding-left:10px;border-bottom:1px solid black;} img{max-width:100%;}</style>"+document.getElementById('dex_printable_contents').innerHTML);
		  w.document.close();
		  w.focus();
		  w.print();
		  w.close();
	 }
	</script>
	<div class="wrap">
	<h1 style="display:block;"><?php _e( 'Calculated Fields Form - Message List', 'calculated-fields-form' ); ?></h1>

	<input type="button" name="backbtn" value="<?php esc_attr_e( 'Back to items list...', 'calculated-fields-form' ); ?>" onclick="document.location='admin.php?page=cp_calculated_fields_form';" class="button-secondary" />


	<div id="normal-sortables" class="meta-box-sortables">
	 <hr />
	 <h3><?php _e( 'This message list is from', 'calculated-fields-form' ); ?>: <?php echo !empty($myform) ? $myform->form_name : __('every form', 'calculated-fields-form'); ?></h3>
	</div>


	<form action="admin.php" method="get">
	 <input type="hidden" name="page" value="cp_calculated_fields_form" />
	 <input type="hidden" name="list" value="1" />
	 <div style="display:inline-block; white-space:nowrap; margin-right:20px;">
		<?php _e( 'Search for', 'calculated-fields-form' ); ?>: <input type="text" name="search" value="<?php echo esc_attr(isset($_GET["search"]) ? $_GET["search"] : ''); ?>" />
	 </div>
	 <div style="display:inline-block; white-space:nowrap; margin-right:20px;">
		<?php _e( 'From', 'calculated-fields-form' ); ?>: <input type="text" id="dfrom" name="dfrom" value="<?php echo esc_attr(isset($_GET["dfrom"]) ? $_GET["dfrom"] : ''); ?>" />
	 </div>
	 <div style="display:inline-block; white-space:nowrap; margin-right:20px;">
		<?php _e( 'To', 'calculated-fields-form' ); ?>: <input type="text" id="dto" name="dto" value="<?php echo esc_attr(isset($_GET["dto"]) ? $_GET["dto"] : ''); ?>" />
	 </div>
	 <div style="display:inline-block; white-space:nowrap; margin-right:20px;">
		<?php _e( 'In', 'calculated-fields-form' ); ?>: <select id="cal" name="cal"><option value="0"><?php _e('All forms', 'calculated-fields-form'); ?></option><?php echo $form_list_opts; ?></select>
	 </div>
	 <div style="display:inline-block; white-space:nowrap; margin-right:20px;">
		<input type="checkbox" id="paid" name="paid" <?php if(isset($_GET['paid'])) print 'CHECKED'; ?> /><?php _e( 'Only Paid', 'calculated-fields-form' ); ?>
	 </div>
	 <?php
		/**
		 * Additional filtering options, allows to add new fields for filtering the results
		 */
		do_action( 'cpcff_messages_filters' );
	 ?>
	 <nobr><span class="submit"><input type="submit" name="ds" value="<?php esc_attr_e( 'Filter', 'calculated-fields-form' ); ?>" class="button-secondary" /></span> &nbsp; &nbsp; &nbsp;
	 <span class="submit"><input type="submit" name="cp_calculatedfieldsf_csv" value="<?php esc_attr_e( 'Export to CSV', 'calculated-fields-form' ); ?>" class="button-secondary" /></span></nobr>
	 <input type="hidden" name="_cpcff_nonce" value="<?php echo wp_create_nonce( 'cff-submissions-list' ); ?>" />
	</form>

	<br />

	<?php
    $page_links = paginate_links(  array(
		'base'         => 'admin.php?page=cp_calculated_fields_form&cal='.CP_CALCULATEDFIELDSF_ID.'&list=1%_%&dfrom='.urlencode(isset($_GET["dfrom"]) ? $_GET["dfrom"] : '').'&dto='.urlencode(isset($_GET["dto"]) ? $_GET["dto"] : '').'&search='.urlencode(isset($_GET["search"]) ? $_GET["search"] : ''),
		'format'       => '&p=%#%',
		'total'        => $total_pages,
		'current'      => $current_page,
		'show_all'     => False,
		'end_size'     => 1,
		'mid_size'     => 2,
		'prev_next'    => True,
		'prev_text'    => __('&laquo; Previous'),
		'next_text'    => __('Next &raquo;'),
		'type'         => 'plain',
		'add_args'     => False
		) );

    print $page_links;
	?>

	<div id="dex_printable_contents" style="padding:10px 0;">
	<table class="wp-list-table widefat pages cff-custom-table cff-events-list" cellspacing="0">
		<thead>
		<tr>
		  <th style="font-weight:bold;"><input type="checkbox" onclick="cp_checkAllItems( this )" style="margin-left:8px;"></th>
		  <th style="padding-left:7px;font-weight:bold;"><?php _e( 'ID', 'calculated-fields-form' ); ?></th>
		  <th style="padding-left:7px;font-weight:bold;"><?php _e( 'Form', 'calculated-fields-form' ); ?></th>
		  <th style="padding-left:7px;font-weight:bold;"><?php _e( 'Date', 'calculated-fields-form' ); ?></th>
		  <th style="padding-left:7px;font-weight:bold;"><?php _e( 'Email', 'calculated-fields-form' ); ?></th>
		  <th style="padding-left:7px;font-weight:bold;"><?php _e( 'Message', 'calculated-fields-form' ); ?></th>
		  <th style="padding-left:7px;font-weight:bold;"><?php _e( 'Payment Info', 'calculated-fields-form' ); ?></th>
		  <?php
			/**
			 * Action called to include new headers in the table of messages
			 */
			do_action( 'cpcff_messages_list_header' );
		  ?>
		  <th style="padding-left:7px;font-weight:bold;"><?php _e( 'Options', 'calculated-fields-form' ); ?></th>
		</tr>
		</thead>
		<tbody id="the-list">
		 <?php for ($i=($current_page-1)*$records_per_page; $i<$current_page*$records_per_page; $i++) if (isset($events[$i])) { ?>
		  <tr class='<?php if (!($i%2)) { ?>alternate <?php } ?>author-self status-draft format-default iedit' valign="top">
			<td><input type="checkbox" value="<?php echo $events[$i]->id; ?>" class="cp_item" style="margin-left:8px;"></td>
			<td><?php echo $events[$i]->id; ?></td>
			<td><?php echo $events[$i]->formid; ?></td>
			<td><?php echo substr($events[$i]->time,0,16); ?></td>
			<td><?php echo sanitize_email($events[$i]->notifyto); ?></td>
			<td>
				<div class="cff-event-data">
				<?php
					echo str_replace( array( '\"', "\'", "\n" ), array( '"', "'", "<br />" ), $events[$i]->data );

					// Add links
					$paypal_post = @unserialize( $events[ $i ]->paypal_post );
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
										print '<p><a href="'.esc_attr( $_url ).'" target="_blank">'.$_url.'</a></p>';
									}
								}
							}
						}

                        print '<hr />';
                        if(!empty($paypal_post['from_page']))
                        {
                            print '<p>'.__('Form Page', 'calculated-fields-form').': <a href="'.esc_attr($paypal_post['from_page']).'" target="_blank">'.$paypal_post['from_page'].'</a></p>';
                        }
					}
                    do_action( 'cpcff_message_additional_details', $events[ $i ] );
				?>
				</div>
			</td>
			<td>
				<?php
					if ($events[$i]->paid) {

						if( $paypal_post !== false && !empty( $paypal_post[ 'paypal_data' ] ) )
						{
							echo '<span style="color:#00aa00;font-weight:bold"><a href="javascript:void(0);" onclick="cp_moreInfo(this);">'.__("Paid", 'calculated-fields-form' ).'[+]</a></span><div class="paypal_data" style="display:none;">'.$paypal_post[ 'paypal_data' ].'</div>';
						}
						else
						{
							echo '<span style="color:#00aa00;font-weight:bold">'.__("Paid", 'calculated-fields-form' ).'</span>';
						}
					}
					else
						echo '<span style="color:#ff0000;font-weight:bold">'.__("Not Paid", 'calculated-fields-form' ).'</span>';
				?>

			</td>
			<?php
				/**
				 * Action called to add related data to the message
				 * The row is passed as parameter
				 */
				do_action( 'cpcff_message_row_data', $events[ $i ] );
			?>
			<td class="cff-events-actions">
				<?php
					$buttons = '';
					if ($events[$i]->paid) {
						$buttons .= '<input type="button" name="calmanage_'.$events[$i]->id.'" value="'.esc_attr__('Change to NOT PAID', 'calculated-fields-form').'" onclick="cp_updateMessageItem('.$events[$i]->id.',0);" class="button-secondary" />';
					} else {
						$buttons .= '<input type="button" name="calmanage_'.$events[$i]->id.'" value="'.esc_attr__('Change to PAID', 'calculated-fields-form').'" onclick="cp_updateMessageItem('.$events[$i]->id.',1);" class="button-secondary" />';
					}
					$buttons .= '<input type="button" name="caledit_'.$events[$i]->id.'" value="'.esc_attr__('Edit (Raw)', 'calculated-fields-form').'" onclick="cp_editMessageItem('.$events[$i]->id.');" class="button-secondary" />
					<input type="button" name="caldelete_'.$events[$i]->id.'" value="'.esc_attr__('Delete', 'calculated-fields-form').'" onclick="cp_deleteMessageItem('.$events[$i]->id.');" class="button-secondary" />';
					print apply_filters('cpcff_message_row_buttons', $buttons, $events[$i]);
				?>
			</td>
		  </tr>
		 <?php } ?>
		</tbody>
	</table>
	</div>
    <?php
    print $page_links;
    ?>
	<p class="submit"><input type="button" name="pbutton" value="<?php esc_attr_e( 'Delete all checked', 'calculated-fields-form' ); ?>" onclick="cp_deleteAllTicked();" class="button-secondary" /> <input type="button" name="pbutton" value="<?php esc_attr_e( 'Print', 'calculated-fields-form' ); ?>" onclick="do_dexapp_print();" class="button-secondary" /> <input type="button" name="pbutton" value="<?php esc_attr_e( 'Delete all', 'calculated-fields-form' ); ?>" onclick="cp_deleteAll();" class="button-secondary" style="background-color:#951717;border-color:#951717;color:white;" /></p>
	</div>

	<script type="text/javascript">
	 var $j = jQuery.noConflict();
	 $j(function() {
		$j("#dfrom").datepicker({
						dateFormat: 'yy-mm-dd'
					 });
		$j("#dto").datepicker({
						dateFormat: 'yy-mm-dd'
					 });
	 });

	</script>
<?php
 } // End else