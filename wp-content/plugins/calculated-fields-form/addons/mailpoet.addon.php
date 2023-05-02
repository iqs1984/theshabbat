<?php
/*
....
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPCFF_MailPoet' ) )
{
    class CPCFF_MailPoet extends CPCFF_BaseAddon
    {
		static public $category = 'Third Party Plugins';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-mailpoet-20170213";
		protected $name = "CFF - MailPoet";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#mailpoet-addon';

		public function get_addon_form_settings( $form_id )
		{
			global $wpdb;

			// Insertion in database
			if( isset( $_REQUEST[ 'cpcff_mailpoet' ] ) )
			{
				$wpdb->delete( $wpdb->prefix.$this->form_mailpoet_table, array( 'formid' => $form_id ), array( '%d' ) );

				$mailpoet_emails = array_map( array($this, '_trim_stripcslashes'), (isset($_REQUEST['cpcff_mailpoet_emails'])) ? $_REQUEST['cpcff_mailpoet_emails'] : array() );
				$mailpoet_emails = array_filter( $mailpoet_emails, array($this, '_remove_empty'));
				$mailing_lists = array_map( intval, (!empty($_REQUEST['cpcff_mailpoet_mailing_lists'])) ? $_REQUEST['cpcff_mailpoet_mailing_lists'] : array() );

				$data = array(
					'emails' 					=> $mailpoet_emails,
					'firstname' 				=> $this->_trim_stripcslashes((isset($_REQUEST['cpcff_mailpoet_firstname'])) ? $_REQUEST['cpcff_mailpoet_firstname'] : ''),
					'lastname' 					=> $this->_trim_stripcslashes((isset($_REQUEST['cpcff_mailpoet_lastname'])) ? $_REQUEST['cpcff_mailpoet_lastname'] : ''),
					'mailing_lists' 			=> $mailing_lists,
					'mailing_lists_selectable' 	=> (!empty($_REQUEST['cpcff_mailpoet_mailing_lists_selectable'])) ? 1 : 0,
					'mailing_lists_container' 	=> $this->_trim_stripcslashes( (isset($_REQUEST['cpcff_mailpoet_mailing_lists_container'])) ? $_REQUEST['cpcff_mailpoet_mailing_lists_container'] : '' ),
					'mailing_lists_required' 	=> (!empty($_REQUEST['cpcff_mailpoet_mailing_lists_required'])) ? 1 : 0
				);

				$wpdb->insert(
					$wpdb->prefix.$this->form_mailpoet_table,
					array(
						'formid' => $form_id,
						'data'	 => serialize($data)
					),
					array( '%d', '%s' )
				);
			}

			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_mailpoet_table." WHERE formid=%d", $form_id ));

			$data = !empty($row) && isset($row->data) ? unserialize($row->data) : false;

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<div id="metabox_mailpoet_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_mailpoet_addon_form_settings' ) ); ?>" >
				<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
				<div class="inside">
			<?php
				if($this->_mailpoet_version() === false)
				{
					_e('MailPoet plugin is required to use the add-on, please install and activate the MailPoet plugin', 'calculated-fields-form' );
				}
				else
				{
			?>
					<table cellspacing="0" style="width:100%;">
						<tr>
							<td style="white-space:nowrap;width:200px;">Firstname field:</td>
							<td><input type="text" name="cpcff_mailpoet_firstname" value="<?php print esc_attr(($data && isset($data['firstname'])) ? $data['firstname'] : ''); ?>" placeholder="fieldname#" /></td>
						</tr>
						<tr>
							<td style="white-space:nowrap;width:200px;">Lastname field:</td>
							<td><input type="text" name="cpcff_mailpoet_lastname" value="<?php echo esc_attr(($data && isset($data['lastname'])) ? $data['lastname'] : ''); ?>" placeholder="fieldname#" /></td>
						</tr>
						<?php
							if( $data && $data['emails'] && is_array($data['emails']) )
							{
								foreach( $data['emails'] as $email )
								{
									if( ($email = trim($email)) !== '' )
										print '
											<tr>
												<td style="white-space:nowrap;width:200px;">'.__('Email field', 'calculated-fields-form').':</td>
												<td><input type="text" name="cpcff_mailpoet_emails[]" value="'.esc_attr( $email ).'" placeholder="fieldname#" /> <input type="button" value="[ X ]" onclick="cpcff_mailpoet_removeEmail( this );" class="button-secondary" /></td>
											</tr>
										';
								}
							}
						?>
						<tr>
							<td style="white-space:nowrap;width:200px;"><?php _e('Email field', 'calculated-fields-form');?>:</td>
							<td>
								<input type="text" name="cpcff_mailpoet_emails[]" value="" placeholder="fieldname#" />
								<input type="button" value="[ X ]" onclick="cpcff_mailpoet_removeEmail( this );" class="button-secondary" />
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<input type="button" value="<?php esc_attr_e('Add new Email', 'calculated-fields-form');?>" onclick="cpcff_mailpoet_addEmail( this );" class="button-secondary" />
							</td>
						</tr>
						<?php
							$mailpoet_lists = $this->_get_mailpoet_lists();
							if(empty($mailpoet_lists))
							{
								print '<td colspan="2" style="border:1px solid #FF0000;padding:10px;color:#FF0000;">'.__( 'Must be created at least a mailing list in MailPoet', 'calculated-fields-form' ).'</td>';
							}
							else
							{
								print '<td style="white-space:nowrap;width:200px;" valign="top">'.__('Mailing lists', 'calculated-fields-form').'</td><td>';

								foreach($mailpoet_lists as $list)
								{
									$checked = '';
									if(
										$data &&
										!empty($data[ 'mailing_lists' ]) &&
										in_array($list[ 'list_id' ], $data[ 'mailing_lists' ]) &&
										empty($data[ 'mailing_lists_selectable' ] )
									)
									{
										$checked = ' CHECKED ';
									}
									print '<input type="checkbox" name="cpcff_mailpoet_mailing_lists[]" value="'.$list[ 'list_id' ].'" '.$checked.' />'.$list[ 'name' ].'<br />';
								}

								$checked = '';
								if($data && !empty($data['mailing_lists_selectable'])) $checked = ' CHECKED ';
								print '<hr></hr><input type="checkbox" name="cpcff_mailpoet_mailing_lists_selectable" '.$checked.'  onchange="cff_mailpoet_deactivateLists(this);" />'.__('Allow the users select the mailing lists at runtime').'<br />';

								$value = (!empty($data['mailing_lists_selectable']) && !empty($data['mailing_lists_container'])) ? $data['mailing_lists_container'] : '';
								print 'Mailing lists container: <input type="text" name="cpcff_mailpoet_mailing_lists_container" placeholder="Container Id" value="'.esc_attr($value).'" /><br /><span style="color:red;font-style:italic;">'.__('Insert a "HTML Content" field in the form with a &lt;DIV&gt; tag as its content, for example: &lt;div id="<b>mailpoet_container</b>"&gt;&lt;/div&gt;, and enter the DIV id: <b>mailpoet_container</b> as the text in the box','calculated-fields-form').'</span><br />';

								$checked = '';
								if(!empty($data['mailing_lists_required']) && !empty($data['mailing_lists_selectable'])) $checked = ' CHECKED ';
								print '<input type="checkbox" name="cpcff_mailpoet_mailing_lists_required" '.$checked.' />'.__('Set the Mailing Lists required').'<br />';

								print '</td>';
							}
						?>
					</table>
					<input type="hidden" name="cpcff_mailpoet" value="1" />
			<?php
				}
			?>
                <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
				</div>
				<script>
					function cff_mailpoet_deactivateLists( e )
					{
						try
						{
							var $ = jQuery;
							if(e.checked)
							{
								$('[name*="cpcff_mailpoet_mailing_lists[]"]').prop('disabled', true).prop('checked', false);
								$('[name="cpcff_mailpoet_mailing_lists_required"],[name="cpcff_mailpoet_mailing_lists_container"]').prop('disabled', false);
							}
							else
							{
								$('[name*="cpcff_mailpoet_mailing_lists[]"]').prop('disabled', false);
								$('[name="cpcff_mailpoet_mailing_lists_required"],[name="cpcff_mailpoet_mailing_lists_container"]').prop('disabled', true);
								$('[name="cpcff_mailpoet_mailing_lists_required"]').prop('checked', false);
								$('[name="cpcff_mailpoet_mailing_lists_container"]').val('');
							}
						}
						catch( err ){}
					}
					cff_mailpoet_deactivateLists( jQuery('[name="cpcff_mailpoet_mailing_lists_selectable"]')[0] );
					function cpcff_mailpoet_addEmail( e )
					{
						try
						{
							var $ = jQuery;
							e = $( e );
							e.closest( 'tr' )
							 .before(
								'<tr><td style="white-space:nowrap;width:200px;"><?php print esc_js(_e('Email field', 'calculated-fields-form')); ?>:</td><td><input name="cpcff_mailpoet_emails[]" value="" placeholder="fieldname#" /> <input type="button" value="[ X ]" onclick="cpcff_mailpoet_removeEmail( this );" class="button-secondary" /></td></tr>'
							 );
						}
						catch( err ){}
					}

					function cpcff_mailpoet_removeEmail( e )
					{
						try
						{
							var $ = jQuery;
							$( e ).closest( 'tr' ).remove();
						}
						catch( err ){}
					}
				</script>
			</div>
			<?php
		}

		/************************ ADDON CODE *****************************/
        /************************ ATTRIBUTES *****************************/

		private $form_mailpoet_table = 'cp_calculated_fields_form_mailpoet';
		private $javascript_code = '';

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = __("The add-on adds new subscribers to MailPoet newsletters", 'calculated-fields-form');

            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;

			// Export the lead
			add_action( 'cpcff_process_data', array( &$this, 'put_data' ) );

			// Checks the form's settings and generate the javascript code
			add_filter( 'cpcff_pre_form', array( &$this, 'generate_javascript' ), 1, 2 );

			// Inserts the javascript code in the page's footer
			add_action( 'wp_footer', array( &$this, 'insert_javascript' ), 99 );
			add_action( 'cpcff_footer', array( &$this, 'insert_javascript' ), 99 );

			if( is_admin() )
			{
				// Delete forms
				add_action( 'cpcff_delete_form', array(&$this, 'delete_form') );

				// Clone forms
				add_action( 'cpcff_clone_form', array(&$this, 'clone_form'), 10, 2 );

				// Export addon data
				add_action( 'cpcff_export_addons', array(&$this, 'export_form'), 10, 2 );

				// Import addon data
				add_action( 'cpcff_import_addons', array(&$this, 'import_form'), 10, 2 );
			}

        } // End __construct

        /************************ PROTECTED METHODS *****************************/

		/**
         * Creates the database tables
         */
        protected function update_database()
		{
			global $wpdb;
			$charset_collate = $wpdb->get_charset_collate();
			$db_queries = array( "CREATE TABLE ".$wpdb->prefix.$this->form_mailpoet_table." (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					formid INT NOT NULL,
					data TEXT DEFAULT '' NOT NULL,
					UNIQUE KEY id (id)
				) $charset_collate;"
			);
			$this->_run_update_database($db_queries);
		} // End update_database

        /************************ PRIVATE METHODS *****************************/

		/**
		 * MailPoet 2 uses list_id as the lists ids, and MailPoet 3 simply id
		 */
		private function _list_id_mp3_to_mp2(&$e, $k)
		{
			if(isset($e['id'])) $e['list_id'] = $e['id'];
		} // End _list_id_mp3_to_mp2

		/**
		 * Checks if MailPoet is installed or not, and determine its version: MailPoet 2 or 3
		 */
		private function _mailpoet_version()
		{
			if(class_exists( 'WYSIJA' )) return 2;
			if(defined('MAILPOET_VERSION')) return 3;
			return false;
		} // End _mailpoet_version

		/**
		 * Get the MailPoet available lists
		 */
		private function _get_mailpoet_lists()
		{
			$mailpoet_lists = array();

			$version = $this->_mailpoet_version();
			if($version == 2)
			{
				$model_list = WYSIJA::get('list','model');
				$mailpoet_lists = $model_list->get(array('list_id','name'),array('is_enabled'=>1));
			}
			else if($version == 3)
			{
				if (class_exists(\MailPoet\API\API::class))
				{
					$mailpoet_api = \MailPoet\API\API::MP('v1');
					$mailpoet_lists = $mailpoet_api->getLists();
					array_walk($mailpoet_lists, array($this, '_list_id_mp3_to_mp2'));
				}
			}
			return $mailpoet_lists;
		} // End _get_mailpoet_lists

		/**
		 * Add a new suscriber to the MailPoet list
		 */
		private function _add_mailpoet_suscriber($user_data, $mailing_lists)
		{
			$version = $this->_mailpoet_version();
			if($version == 2)
			{
				$helper_user = WYSIJA::get('user','helper');
				$helper_user->addSubscriber(
					array(
						'user' => $user_data,
						'user_list' => array( 'list_ids' => $mailing_lists )
					)
				);
			}
			elseif($version == 3)
			{
				if (class_exists(\MailPoet\API\API::class))
				{
					$mailpoet_api = \MailPoet\API\API::MP('v1');

					try
					{
						$subscriber = $mailpoet_api->getSubscriber($user_data['email']);
					} catch (\Exception $e) {}

					try
					{
						if (!$subscriber)
						{
							$mailpoet_api->addSubscriber($user_data, $mailing_lists);
						}
						else
						{
							$mailpoet_api->subscribeToLists($user_data['email'], $mailing_lists);
						}
					}
					catch (\Exception $e)
					{
						error_log('CFF MailPoet add-on: '.$e->getMessage());
					}
				}
			}
		} // End _add_mailpoet_suscriber

		/**
		 * Checks if the MailPoet list id is associated to the form
		 */
		private function _in_mailpoet_lists($id, $mailpoet_lists)
		{
			$id = trim($id);
			foreach($mailpoet_lists as $list )
			{
				if( $list['list_id'] == $id)
				{
					return $id;
				}
			}
			return false;
		} // End _in_mailpoet_lists

		private function _trim_stripcslashes( $v )
		{
			return stripcslashes(trim($v));
		} // End _trim_stripcslashes

		private function _remove_empty( $v )
		{
			return !empty($v);
		} // End _trim_stripcslashes


        /************************ PUBLIC METHODS  *****************************/

		/**
         * Create MailPoet subscriber
         */
        public function	put_data( $params )
		{
			if($this->_mailpoet_version() === false) return;

			global $wpdb;
			$form_id = @intval( $params[ 'formid' ] );
			if( $form_id )
			{
				$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_mailpoet_table." WHERE formid=%d", $form_id ) );
				if( $row )
				{
					if( ($data = @unserialize($row->data)) !== false )
					{
						$user_data = array();

						// Firstname
						if(isset($data['firstname']) && ($firstname_param = trim($data['firstname'])) !== '')
						{
							if(isset($params[$firstname_param]) && ($firstname = trim($params[$firstname_param])) !== '')
								$user_data['firstname'] = $firstname;
						}

						// Lastname
						if(isset($data['lastname']) && ($lastname_param = trim($data['lastname'])) !== '')
						{
							if(isset($params[$lastname_param]) && ($lastname = trim($params[$lastname_param])) !== '')
								$user_data['lastname'] = $lastname;
						}

						$mailpoet_lists = $this->_get_mailpoet_lists();

						// Mailing lists
						$mailing_lists = array();
						if(!empty($data['mailing_lists']))
						{
							foreach($data['mailing_lists'] as $list_id)
								if(($list_id = $this->_in_mailpoet_lists($list_id, $mailpoet_lists)) !== false)
									$mailing_lists[] = $list_id;
						}
						elseif( !empty($_REQUEST['mailpoet_mailing_lists']) && is_array($_REQUEST['mailpoet_mailing_lists']))
						{
							foreach($_REQUEST['mailpoet_mailing_lists'] as $list_id)
								if(($list_id = $this->_in_mailpoet_lists($list_id, $mailpoet_lists)) !== false)
									$mailing_lists[] = $list_id;
						}

						if(
							!empty($mailing_lists) &&
							!empty($data['emails']) &&
							is_array($data['emails'])
						)
						{
							foreach($data['emails'] as $email_param)
							{
								$email_param = trim($email_param);
								if(
									!empty($email_param) &&
									!empty($params[$email_param]) &&
									($email = is_email($params[$email_param])) !== false
								)
								{
									$user_data[ 'email' ] = $email;
									$this->_add_mailpoet_suscriber($user_data, $mailing_lists);
								}
							}
						}
					} // if( $data !== false )
				}
			}
		} // End put_data

		/**
		 * Checks the form's settings and generates the javascript code
		 */
		public function generate_javascript( $atts )
		{
			if(
				!empty( $atts ) &&
				is_array( $atts ) &&
				!empty( $atts[ 'id' ] )
			)
			{
				global $wpdb;
				$instance = '_'.CPCFF_MAIN::$form_counter;
				$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_mailpoet_table." WHERE formid=%d", $atts[ 'id' ] ) );
				if(
					!empty( $row ) &&
					!empty( $row->data ) &&
					( $data = @unserialize( $row->data ) ) !== false &&
					!empty( $data[ 'mailing_lists_selectable' ] )
				)
				{
					$mailing_lists 	= '';
					$required  		= (!empty($data[ 'mailing_lists_required' ])) ? 'required': '';
					$container 		= (!empty($data[ 'mailing_lists_container'])) ? '#fieldlist'.$instance.' #'.$data[ 'mailing_lists_container'] : '#fieldlist'.$instance;

					$model_list = WYSIJA::get('list','model');
					$mailpoet_lists = $model_list->get(array('list_id','name'),array('is_enabled'=>1));
					foreach($mailpoet_lists as $list)
					{
						$mailing_lists .= '<label><input type=\"checkbox\" name=\"mailpoet_mailing_lists[]\" class=\"'.$required.'\" value=\"'.$list['list_id'].'\" />'.esc_js($list['name']).'</label>';
					}

					if( !empty($mailing_lists) )
					$this->javascript_code  .= 'jQuery("'.$container.'").append("'.$mailing_lists.'");';
				}
			}
			return $atts;
		} // End generate_javascript

		/**
		 * Inserts the javascript code in the footer section of page
		 */
		public function insert_javascript()
		{
			if( !empty( $this->javascript_code ) )
			{
			?>
				<script>if(typeof jQuery == 'undefined' && typeof fbuilderjQuery != 'undefined' ) jQuery = fbuilderjQuery;</script>
				<script>
					jQuery(window).on(
						'load',
						function()
						{
							<?php
							print $this->javascript_code;
							?>
						}
					);
				</script>
			<?php
			}
		} // End insert_javascript

		/**
		 *	Delete the form from the addon's table
		 */
        public function delete_form( $formid)
		{
			global $wpdb;
			$wpdb->delete( $wpdb->prefix.$this->form_mailpoet_table, array('formid' => $formid), '%d' );
		} // delete_form

		/**
		 *	Clone the form's row
		 */
		public function clone_form( $original_form_id, $new_form_id )
		{
			global $wpdb;

			$form_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_mailpoet_table." WHERE formid=%d", $original_form_id ), ARRAY_A);

			if(!empty($form_row))
			{
				unset($form_row["id"]);
				$form_row["formid"] = $new_form_id;
				$wpdb->insert( $wpdb->prefix.$this->form_mailpoet_table, $form_row);
			}
		} // End clone_form

		/**
		 *	It is called when the form is exported to export the addons data too.
		 *  Receive an array with the other addons data, and the form's id for filtering.
		 */
		public function export_form($addons_array, $formid)
		{
			global $wpdb;
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_mailpoet_table." WHERE formid=%d", $formid ), ARRAY_A );
			if(!empty( $row ))
			{
				unset($row['id']);
				unset($row['formid']);
				$addons_array[ $this->addonID ] = $row;
			}
			return $addons_array;
		} // End export_form

		/**
		 *	It is called when the form is imported to import the addons data too.
		 *  Receive an array with all the addons data, and the new form's id.
		 */
		public function import_form($addons_array, $formid)
		{
			global $wpdb;
			if(isset($addons_array[$this->addonID]))
			{
				$addons_array[$this->addonID]['formid'] = $formid;
				$wpdb->insert(
					$wpdb->prefix.$this->form_mailpoet_table,
					$addons_array[$this->addonID]
				);
			}
		} // End import_form

    } // End Class

    // Main add-on code
    $cpcff_mailpoet_obj = new CPCFF_MailPoet();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cpcff_mailpoet_obj);
}