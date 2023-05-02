<?php
/*
....
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPCFF_AffiliateWP' ) )
{
    class CPCFF_AffiliateWP extends CPCFF_BaseAddon
    {
		static public $category = 'Third Party Plugins';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-affiliatewp-20171226";
		protected $name = "CFF - Affiliate WP";
        protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#affiliatewp-addon';

		public function get_addon_form_settings( $form_id )
		{
			global $wpdb;

			// Insertion in database
			if( isset( $_REQUEST[ 'cpcff_affiliate_wp_form' ] ) )
			{
				$wpdb->delete( $wpdb->prefix.$this->form_affiliate_table, array( 'formid' => $form_id ), array( '%d' ) );
				$wpdb->insert(
						$wpdb->prefix.$this->form_affiliate_table,
						array(
							'formid' => $form_id,
							'active' => (isset($_REQUEST['cpcff_affiliate_wp_active'])) ? 1 : 0,
							'description' => (isset($_REQUEST['cpcff_affiliate_wp_description'])) ? $_REQUEST['cpcff_affiliate_wp_description'] : '',
							'context' => (isset($_REQUEST['cpcff_affiliate_wp_context'])) ? $_REQUEST['cpcff_affiliate_wp_context'] : ''
						),
						array( '%d', '%d', '%s', '%s' )
					);
			}

			$row = $wpdb->get_row(
						$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_affiliate_table." WHERE formid=%d", $form_id )
					);

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<div id="metabox_affiliatewp_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_affiliatewp_addon_form_settings' ) ); ?>" >
				<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
				<div class="inside">
					<input type="hidden" name="cpcff_affiliate_wp_form" value="<?php print esc_attr($form_id); ?>" />
					<p><input type="checkbox" name="cpcff_affiliate_wp_active" <?php if(!is_null($row) && $row->active) print 'CHECKED'; ?> /><?php _e('Integrate the Affiliate WP with this form', 'calculated-fields-form'); ?></p>
					<p><?php _e('Description', 'calculated-fields-form'); ?></p>
					<textarea name="cpcff_affiliate_wp_description" rows="5" style="width:100%;resize:vertical;"><?php if(!is_null($row) && !empty($row->description)) print esc_textarea($row->description); ?></textarea>
					<p><?php _e('Context', 'calculated-fields-form'); ?></p>
					<input type="text" name="cpcff_affiliate_wp_context" style="width:100%;" value="<?php if(!is_null($row) && !empty($row->context)) print esc_attr($row->context); ?>" />
                    <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
				</div>
			</div>
			<?php
		}

		/************************ ADDON CODE *****************************/
        /************************ ATTRIBUTES *****************************/

		private $form_affiliate_table = 'cp_calculated_fields_form_affiliatewp';
		private $context = 'calculated-fields-form';
		private $base_obj;

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = __("The add-on allows to integrate the forms with the Affiliate WP plugin", 'calculated-fields-form');

            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;

			require_once( dirname(__FILE__).'/affiliatewp.addon/base.class.php');

			if( is_admin() )
			{
				if(class_exists('Affiliate_WP_CFF'))
				{
					$this->base_obj = new Affiliate_WP_CFF;
					add_action( 'cpcff_change_payment_status', array( &$this, 'cpcff_change_payment_status' ), 10, 2 );
				}

				// Delete forms
				add_action( 'cpcff_delete_form', array(&$this, 'delete_form') );

				// Clone forms
				add_action( 'cpcff_clone_form', array(&$this, 'clone_form'), 10, 2 );

				// Export addon data
				add_action( 'cpcff_export_addons', array(&$this, 'export_form'), 10, 2 );

				// Import addon data
				add_action( 'cpcff_import_addons', array(&$this, 'import_form'), 10, 2 );
			}
			else
			{
				if(class_exists('Affiliate_WP_CFF'))
				{
					$this->base_obj = new Affiliate_WP_CFF;
					add_action( 'cpcff_process_data', array( &$this, 'cpcff_process_data_action' ) );
					add_action( 'cpcff_payment_processed', array( &$this, 'cpcff_payment_processed_action' ), 99 );
				}
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
			$db_queries = array( "CREATE TABLE ".$wpdb->prefix.$this->form_affiliate_table." (
					formid INT NOT NULL,
					active INT NOT NULL DEFAULT 0,
					description MEDIUMTEXT NOT NULL DEFAULT '',
					context VARCHAR(255) NOT NULL DEFAULT '',
					UNIQUE KEY formid (formid)
				) $charset_collate;"
			);
			$this->_run_update_database($db_queries);
		} // End update_database

        /************************ PRIVATE METHODS *****************************/

		private function insert_pending_referral($params)
		{
			global $wpdb;
			if(
				$params['affiliate_id']
			)
			{
				// Populate description
				$description = '';
				$row = $wpdb->get_row(
					$wpdb->prepare("SELECT * FROM ".$wpdb->prefix.$this->form_affiliate_table." WHERE formid=%d", $params['formid'])
				);

				if(!is_null($row))
				{
					if(!empty($row->description))
					{
						$description = $row->description;
						$submission_obj = CPCFF_SUBMISSIONS::get($params['itemnumber']);
						if($submission_obj)
						{
							$form_obj = CPCFF_SUBMISSIONS::get_form($params['itemnumber']);
							$fields = $form_obj->get_fields();
							$fields[ 'ipaddr' ] = $submission_obj->ipaddr;
							$description_data = CPCFF_AUXILIARY::parsing_fields_on_text(
								$fields,
								$params,
								$description,
								$submission_obj->data,
								'html',
								$params['itemnumber']
							);
							$description = $description_data['text'];
						}
					}

					if(!empty($row->context))
					{
						$this->base_obj->context = trim($row->context);
					}
				}

				$product_id		 = $params['formid'];
				$reference       = 'cff-'.$params['formid'] . '-' . $params['itemnumber'];
				$base_amount	 = $params['final_price'];
				$referral_total  = $this->base_obj->calculate_referral_amount($base_amount, $reference, $product_id, $params['affiliate_id']);
				if ( !empty( $referral_total ) )
				{
					$data = array('affiliate_id' => $params['affiliate_id']);
					$referral_id = $this->base_obj->insert_pending_referral($referral_total, $reference, $description, $product_id, $data);

					// Update the paypal_post column with referral_id
					$params['referral_id'] = $referral_id;
					CPCFF_SUBMISSIONS::update(
						$params['itemnumber'],
						array(
							'paypal_post' => $params
						)
					);
				}
			}
			return $params;
		} // End insert_pending_referral

		/************************ PUBLIC METHODS  *****************************/

		/**
         * Process the cpcff_process_data action
         */
		public function cpcff_process_data_action( &$params )
		{
			global $wpdb;
			if($this->base_obj->was_referred())
			{
				$row = $wpdb->get_row( $wpdb->prepare("SELECT * FROM ".$wpdb->prefix.$this->form_affiliate_table." WHERE formid=%d", $params['formid']));
				if(!is_null($row) && $row->active)
				{
					$params['affiliate_id'] = $this->base_obj->get_affiliate_id();
					if(
						$params['affiliate_id'] &&
						$params['final_price']
					)
					{
						CPCFF_SUBMISSIONS::update(
							$params['itemnumber'],
							array(
								'paypal_post' => $params
							)
						);
					}
				}
			}
		} // End cpcff_process_data_action

		/**
		 * Process the cpcff_change_payment_status action
		 */
		public function cpcff_change_payment_status($itemnumber, $new_status)
		{
			$submission_obj = CPCFF_SUBMISSIONS::get($itemnumber);
			if($submission_obj)
			{
				if($new_status == 1) $this->cpcff_payment_processed_action($submission_obj->paypal_post);
				else $this->revoke($submission_obj->paypal_post);
			}
		} // End cpcff_change_payment_status

		/**
         * Process the cpcff_payment_processed action
         */
		public function cpcff_payment_processed_action( $params )
		{
			if(empty($params['referral_id']) && !empty($params['affiliate_id']))
				$params = $this->insert_pending_referral($params);

			if(!empty($params['referral_id']))
			{
				$referral = affwp_get_referral( $params['referral_id'] );
				if( $referral )
				{
					$this->base_obj->complete_referral( $referral );
				}
				else
				{
					$this->base_obj->log( sprintf( 'CFF integration: Referral could not be retrieved during mark_referral_complete(). ID given: %d.' ), $params['referral_id'] );
				}
			}
		} // End cpcff_payment_processed_action

		/**
		 * Revokes all pending payments after the time interval defined in the property revoke_interval
		 */
		public function revoke( $params )
		{
			if(!empty($params['referral_id']))
			{
				$referral = affwp_get_referral( $params['referral_id'] );
				if( $referral )
				{
					$this->base_obj->reject_referral( $referral );
				}
				else
				{
					$this->base_obj->log( sprintf( 'CFF integration: Referral could not be retrieved during revoke(). ID given: %d.' ), $params['referral_id'] );
				}
			}
		} // End revoke

		/**
		 *	Delete the form from the addon's table
		 */
        public function delete_form( $formid)
		{
			global $wpdb;
			$wpdb->delete( $wpdb->prefix.$this->form_affiliate_table, array('formid' => $formid), '%d' );
		} // delete_form

		/**
		 *	Clone the form's row
		 */
		public function clone_form( $original_form_id, $new_form_id )
		{
			global $wpdb;

			$form_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_affiliate_table." WHERE formid=%d", $original_form_id ), ARRAY_A);

			if(!empty($form_row))
			{
				unset($form_row["id"]);
				$form_row["formid"] = $new_form_id;
				$wpdb->insert( $wpdb->prefix.$this->form_affiliate_table, $form_row);
			}
		} // End clone_form

		/**
		 *	It is called when the form is exported to export the addons data too.
		 *  Receive an array with the other addons data, and the form's id for filtering.
		 */
		public function export_form($addons_array, $formid)
		{
			global $wpdb;
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_affiliate_table." WHERE formid=%d", $formid ), ARRAY_A );
			if(!empty($row))
			{
				$addons_array[ $this->addonID ] = array();
				$addons_array[ $this->addonID ][] = $row;
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
				foreach($addons_array[$this->addonID] as $row)
				{
					if(!empty($row))
					{
						$row['formid'] = $formid;
						$wpdb->insert(
							$wpdb->prefix.$this->form_affiliate_table,
							$row
						);
					}
				}
			}
		} // End import_form
    } // End Class

    // Main add-on code
    $cpcff_affiliate_wp_obj = new CPCFF_AffiliateWP();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cpcff_affiliate_wp_obj);
}