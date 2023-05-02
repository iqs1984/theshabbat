<?php
/*
Documentation: https://goo.gl/w3kKoH
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPCFF_SabTPVBizum' ) )
{
    class CPCFF_SabTPVBizum extends CPCFF_BaseAddon
    {
		static public $category = 'Payment Gateways';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-sabtpvbizum-20151212";
		protected $name = "CFF - RedSys Bizum";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#bizum-addon';

		public function get_addon_form_settings( $form_id )
		{
			global $wpdb;
			// Insertion in database
			if(
				isset( $_REQUEST[ 'cpcff_sabtpvbizum_id' ] )
			)
			{
                $this->add_field_verify($wpdb->prefix.$this->form_table, "sabtpvbizum_returnfail");
			    $wpdb->delete( $wpdb->prefix.$this->form_table, array( 'formid' => $form_id ), array( '%d' ) );
				$wpdb->insert(
								$wpdb->prefix.$this->form_table,
								array(
									'formid' => $form_id,
									'sabtpvbizum_api_username'	 => $_REQUEST["sabtpvbizum_api_username"],
									'sabtpvbizum_api_password'	 => $_REQUEST["sabtpvbizum_api_password"],
									'sabtpvbizum_enable_option_yes'	 => $_REQUEST["sabtpvbizum_enable_option_yes"],
                                    'sabtpvbizum_returnfail'	 => $_REQUEST["sabtpvbizum_returnfail"],
									'enabled'	 => $_REQUEST["sabtpvbizum_enabled"],
									'paypal_mode'	 => $_REQUEST["redsysbizum_paypal_mode"]
								),
								array( '%d', '%s', '%s','%s', '%s', '%s', '%s' )
							);
			}


			$rows = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $form_id )
					);
			if (!count($rows))
			{
			    $row["sabtpvbizum_api_username"] = "";
			    $row["sabtpvbizum_api_password"] = "";
                $row["sabtpvbizum_returnfail"] = "";
			    $row["enabled"] = "0";
			    $row["sabtpvbizum_enable_option_yes"] = "Pay now with Bizum";
                $row["paypal_mode"] = '';
			} else {
			    $row["sabtpvbizum_api_username"] = $rows[0]->sabtpvbizum_api_username;
			    $row["sabtpvbizum_api_password"] = $rows[0]->sabtpvbizum_api_password;
                $row["sabtpvbizum_returnfail"] = $rows[0]->sabtpvbizum_returnfail;
			    $row["enabled"] = $rows[0]->enabled;
			    $row["sabtpvbizum_enable_option_yes"] = $rows[0]->sabtpvbizum_enable_option_yes;
			    $row["paypal_mode"] = $rows[0]->paypal_mode;
			}

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<div id="metabox_bizum_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_bizum_addon_form_settings' ) ); ?>" >
				<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
				<div class="inside">
				   <input type="hidden" name="cpcff_sabtpvbizum_id" value="1" />
                   <table class="form-table">
                    <tr valign="top">
                    <th scope="row"><?php _e('Enable TPV?', 'calculated-fields-form'); ?></th>
                    <td><select name="sabtpvbizum_enabled">
                         <option value="0" <?php if (!$row["enabled"]) echo 'selected'; ?>><?php _e('No', 'calculated-fields-form'); ?></option>
                         <option value="1" <?php if ($row["enabled"] == '1') echo 'selected'; ?>><?php _e('Yes', 'calculated-fields-form'); ?></option>
                         <option value="2" <?php if ($row["enabled"] == '2') echo 'selected'; ?>><?php _e('Optional: This payment method + Pay Later (submit without payment)', 'calculated-fields-form'); ?></option>
                         <option value="3" <?php if ($row["enabled"] == '3') echo 'selected'; ?>><?php _e('Optional: This payment method + Other payment methods (enabled)', 'calculated-fields-form'); ?></option>
                         <option value="4" <?php if ($row["enabled"] == '4') echo 'selected'; ?>><?php _e('Optional: This payment method + Other payment methods  + Pay Later ', 'calculated-fields-form'); ?></option>
                         </select>
                         <br /><em style="font-size:11px;"><?php _e( 'Note: If "Pay Later" or "PayPal" are selected, a radiobutton will appear in the form to select if the payment will be made with Bizum or not.', 'calculated-fields-form' ); ?></em>
                         <div id="sabtpvbizum_options_label" style="margin-top:10px;background:#EEF5FB;border: 1px dotted #888888;padding:10px;width:260px;">
                           <?php _e( 'Label for the "<strong>Pay with Bizum</strong>" option', 'calculated-fields-form' ); ?>:<br />
                           <input type="text" name="sabtpvbizum_enable_option_yes" size="40" style="width:250px;" value="<?php echo esc_attr($row['sabtpvbizum_enable_option_yes']); ?>" />
                         </div>
                    </td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('C&Oacute;DIGO COMERCIO', 'calculated-fields-form'); ?></th>
                    <td><input type="text" name="sabtpvbizum_api_username" size="20" value="<?php echo esc_attr($row["sabtpvbizum_api_username"]); ?>" /></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('CLAVE SECRETA', 'calculated-fields-form');?></th>
                    <td><input type="text" name="sabtpvbizum_api_password" size="40" value="<?php echo esc_attr($row["sabtpvbizum_api_password"]); ?>" /></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Mode', 'calculated-fields-form'); ?></th>
                    <td><select name="redsysbizum_paypal_mode">
                         <option value="production" <?php if ($row["paypal_mode"] != 'sandbox') echo 'selected'; ?>><?php _e('Production - real payments processed', 'calculated-fields-form'); ?></option>
                         <option value="sandbox" <?php if ($row["paypal_mode"] == 'sandbox') echo 'selected'; ?>><?php _e('SandBox - Testing sandbox area', 'calculated-fields-form'); ?></option>
                        </select>
                    </td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('P&aacute;gina (URL) a mostrar en caso de pago fallado', 'calculated-fields-form');?></th>
                    <td><input type="text" name="sabtpvbizum_returnfail" size="40" value="<?php echo esc_attr($row["sabtpvbizum_returnfail"]); ?>" /></td>
                    </tr>
                   </table>
                   <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
				</div>
			</div>

			<?php
		} // end get_addon_form_settings



		/************************ ADDON CODE *****************************/

        /************************ ATTRIBUTES *****************************/

        private $form_table = 'cp_calculated_fields_form_sabtpvbizum';
        private $_inserted = false;
        private $paypal_enabled = -1;
		private $_cpcff_main;

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->_cpcff_main = CPCFF_MAIN::instance();
			$this->description = __("The add-on adds support for Bizum payments", 'calculated-fields-form' );
            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;

			add_action( 'cpcff_process_data_before_insert', array( &$this, 'pp_before_insert' ), 10, 3 );

			add_action( 'cpcff_process_data', array( &$this, 'pp_sabtpvbizum' ), 11, 1 );

			add_action( 'init', array( &$this, 'pp_sabtpvbizum_update_status' ), 10, 0 );

			add_filter( 'cpcff_the_form', array( &$this, 'insert_payment_fields'), 99, 2 );

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

            $this->update_database();

        } // End __construct



        /************************ PRIVATE METHODS *****************************/

		/**
         * Create the database tables
         */
        protected function update_database()
		{
			global $wpdb;
			$charset_collate = $wpdb->get_charset_collate();
			$db_queries = array( "CREATE TABLE ".$wpdb->prefix.$this->form_table." (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					formid INT NOT NULL,
					enabled varchar(10) DEFAULT '0' NOT NULL ,
					sabtpvbizum_api_username varchar(255) DEFAULT '' NOT NULL ,
					sabtpvbizum_api_password varchar(255) DEFAULT '' NOT NULL ,
					sabtpvbizum_enable_option_yes varchar(255) DEFAULT '' NOT NULL ,
                    sabtpvbizum_returnfail varchar(255) DEFAULT '' NOT NULL ,
					paypal_mode varchar(255) DEFAULT '' NOT NULL ,
					UNIQUE KEY id (id)
				) $charset_collate;"
			);
			$this->_run_update_database($db_queries);
		} // end update_database


		/************************ PUBLIC METHODS  *****************************/


		/**
         * process before insert
         */
		public function pp_before_insert(&$params, &$str, $fields )
		{
            global $wpdb;

            $rows = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $params["formid"] )
					);

			$payment_option = (isset($_POST["bccf_payment_option_paypal"])?$_POST["bccf_payment_option_paypal"]:$this->addonID);
			if (empty( $rows ) || !$rows[0]->enabled || $payment_option != $this->addonID)
			    return;

			$params["payment_option"] = $this->name;

	    }


		/**
         * Check if the Optional is enabled in the form, and inserts radiobutton
         */
        public function	insert_payment_fields( $form_code, $id )
		{
            global $cpcff_texts_array, $wpdb;
            $rows = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $id )
					);

			if (empty( $rows ) || $rows[0]->enabled == '0' || strpos($form_code, 'vt="'.$this->addonID.'"') !== false)
			    return $form_code;

			// output radio-buttons here
			$form_code = preg_replace( '/<!--addons-payment-options-->/i', '<div><input type="radio" name="bccf_payment_option_paypal" vt="'.$this->addonID.'" value="'.$this->addonID.'" checked> '.__($rows[0]->sabtpvbizum_enable_option_yes, 'calculated-fields-form').'</div><!--addons-payment-options-->', $form_code );

            if (($rows[0]->enabled == '2' || $rows[0]->enabled == '4') && !strpos($form_code,'bccf_payment_option_paypal" vt="0') )
			    $form_code = preg_replace( '/<!--addons-payment-options-->/i', '<!--addons-payment-options--><div><input type="radio" name="bccf_payment_option_paypal" vt="0" value="0"> '.__($this->_cpcff_main->get_form($id)->get_option('enable_paypal_option_no',CP_CALCULATEDFIELDSF_PAYPAL_OPTION_NO), 'calculated-fields-form').'</div>', $form_code );

			if (substr_count ($form_code, 'name="bccf_payment_option_paypal"') > 1)
			    $form_code = str_replace( 'id="field-c0" style="display:none">', 'id="field-c0">', $form_code);

            return $form_code;
		} // End insert_recaptcha


		/**
         * process payment
         */
		public function pp_sabtpvbizum($params)
		{
            global $wpdb;

			CP_SESSION::register_event($params[ 'itemnumber' ], $params[ 'formid' ]);

			// documentation: https://goo.gl/w3kKoH

            $rows = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $params["formid"] )
					);

			$payment_option = (isset($_POST["bccf_payment_option_paypal"])?$_POST["bccf_payment_option_paypal"]:$this->addonID);
			if (empty( $rows ) || !$rows[0]->enabled  || $payment_option != $this->addonID || floatval($params["final_price"]) == 0)
			      return;
			$form_obj = $this->_cpcff_main->get_form($params['formid']);

			if($form_obj->get_option('paypal_notiemails', '0') == '1')
			    $this->_cpcff_main->send_mails($params['itemnumber']);

            $pro_item_name = $form_obj->get_option('paypal_product_name', CP_CALCULATEDFIELDSF_DEFAULT_PRODUCT_NAME);
            foreach ($params as $item => $value)
                $pro_item_name = str_replace('<%'.$item.'%>',(is_array($value)?(implode(", ",$value)):($value)),$pro_item_name);

			if ($pro_item_name == '') $pro_item_name = CP_CALCULATEDFIELDSF_DEFAULT_PRODUCT_NAME;
            $key = $rows[0]->sabtpvbizum_api_password;
            $redsys = new CPCFF_SermepaTPV();
            $redsys->setAmount($params["final_price"]);
            $redsys->setOrder('9000'.$params["itemnumber"]);
            $redsys->setMerchantcode($rows[0]->sabtpvbizum_api_username);
            $redsys->setCurrency('978');
            $redsys->setTransactiontype('0');
            $redsys->setTerminal('1');
            $redsys->setMethod('z'); //Solo pago con bizum
            $redsys->setNotification( (CPCFF_AUXILIARY::site_url().'/?cp_sabtpvbizum_ipncheck=1&itemnumber='.$params["itemnumber"]) ); //Url de notificacion
			$url_ok = CPCFF_AUXILIARY::replace_params_into_url($form_obj->get_option('fp_return_page', CP_CALCULATEDFIELDSF_DEFAULT_fp_return_page), $params);
            $redsys->setUrlOk( $url_ok ); //Url OK
			$url_ok .= (( strpos( '?', $url_ok ) === false ) ? '?' : '&' ).'payment_canceled=1';
            if ($rows[0]->sabtpvbizum_returnfail != '')
                $redsys->setUrlKo( $rows[0]->sabtpvbizum_returnfail ); //Url KO
            else
                $redsys->setUrlKo( $url_ok ); //Url KO
            //$redsys->setUrlKo( $_POST["cp_ref_page"] ); //Url KO
            $redsys->setVersion('HMAC_SHA256_V1');
            //$redsys->setTradeName('Tienda S.L');
            //$redsys->setTitular('Pedro Risco');

            $redsys->setProductDescription($pro_item_name);
            if ($rows[0]->paypal_mode == 'sandbox')
                $redsys->setEnviroment('test'); //Entorno test
            else
                $redsys->setEnviroment('live'); //Entorno production

            $signature = $redsys->generateMerchantSignature($key);
            $redsys->setMerchantSignature($signature);

            $form = $redsys->executeRedirection();

            exit;
		} // end pp_sabtpvbizum


		/**
		 * mark the item as paid
		 */
		private function _log($adarray = array())
		{
			$h = fopen( dirname(__FILE__).'/logs.txt', 'a' );
			$log = "";
			foreach( $_REQUEST as $KEY => $VAL )
			{
				$log .= $KEY.": ".$VAL."\n";
			}
			foreach( $adarray as $KEY => $VAL )
			{
				$log .= $KEY.": ".$VAL."\n";
			}
			$log .= "================================================\n";
			fwrite( $h, $log );
			fclose( $h );
		}

		public function pp_sabtpvbizum_update_status( )
		{
            if (
				!isset( $_GET['cp_sabtpvbizum_ipncheck'] ) ||
				$_GET['cp_sabtpvbizum_ipncheck'] != '1' ||
				!isset( $_GET["itemnumber"] )
			) return;

            $redsys = new CPCFF_SermepaTPV();
            $redsys_params = $redsys->getMerchantParameters($_REQUEST["Ds_MerchantParameters"]);
			//$this->_log($redsys_params);

			if (!isset($redsys_params["Ds_Response"]))
			    return;

            $itemnumber = intval(@$_GET['itemnumber'] );
			$submission = CPCFF_SUBMISSIONS::get($itemnumber);
			if(empty($submission)) return;

            $params = $submission->paypal_post;

			$paymentok = (intval($redsys_params["Ds_Response"]) < 100);
			if (!$paymentok)
			{
				echo 'Payment failed';
				exit;
			}

			if ($submission->paid == 0)
			{
				$params[ 'tpv_response_code' ] = $redsys_params["Ds_Response"];
				CPCFF_SUBMISSIONS::update($itemnumber, array('paid'=>1, 'paypal_post'=>$params));

			    /**
			     * Action called after process the data received by PayPal.
			     * To the function is passed an array with the data collected by the form.
			     */
				$params['itemnumber'] =  $itemnumber;
			    do_action( 'cpcff_payment_processed', $params );

				$form_obj = CPCFF_SUBMISSIONS::get_form($itemnumber);
				if ($form_obj->get_option('paypal_notiemails', '0') != '1')
					$this->_cpcff_main->send_mails($itemnumber);
				echo 'OK - processed';
			}
			else
				echo 'OK - already processed';

            exit;
		}

        /**
         * Translate response codes
         */
		public function getResponseText($responseCode)
		{
            switch($responseCode)
            {
            	case '101':
            		$reason =  'Tarjeta caducada';
            	break;
            	case '102':
            		$reason =  'Tarjeta en excepcion transitoria o bajo sospecha de fraude';
            	break;
            	case '104':
            		$reason =  'Operacion no permitida para esa tarjeta o terminal';
            	break;
            	case '106':
            		$reason =  'Intentos de PIN excedidos';
            	break;
            	case '116':
            		$reason =  'Disponible insuficiente';
            	break;
            	case '118':
            		$reason =  'Tarjeta no registrada';
            	break;
            	case '125':
            		$reason =  'Tarjeta no efectiva.';
            	break;
            	case '129':
            		$reason =  'Codigo de seguridad (CVV2/CVC2) incorrecto';
            	break;
            	case '180':
            		$reason =  'Tarjeta ajena al servicio';
            	break;
            	case '184':
            		$reason =  'Error en la autenticacion del titular';
            	break;
            	case '190':
            		$reason =  'Denegacion sin especificar Motivo';
            	break;
            	case '191':
            		$reason =  'Fecha de caducidad erronea';
            	break;
            	case '201':
            		$reason =  'Transacción denegada porque la fecha de caducidad de la tarjeta que se ha informado en el pago, es anterior a la actualmente vigente';
            	break;
            	case '202':
            		$reason =  'Tarjeta en excepcion transitoria o bajo sospecha de fraude con retirada de tarjeta';
            	break;
            	case '204':
            		$reason =  'Operación no permitida para ese tipo de tarjeta';
            	break;
            	case '207':
            		$reason =  'El banco emisor no permite una autorización automática. Es necesario contactar telefónicamente con su centro autorizador para obtener una aprobación manual';
            	break;
            	case '208':
            	case '209':
            		$reason =  'Tarjeta bloqueada por el banco emisor debido a que el titular le ha manifestado que le ha sido robada o perdida';
            	break;
            	case '280':
            		$reason =  'Es erróneo el código CVV2/CVC2 informado por el comprador';
            	break;
            	case '290':
            		$reason =  'Transacción denegada por el banco emisor pero sin que este dé detalles acerca del motivo';
            	break;
            	case '904':
            		$reason =  'Comercio no registrado en FUC.';
            	break;
            	case '909':
            		$reason =  'Error de sistema.';
            	break;
            	case '913':
            		$reason =  'Pedido repetido.';
            	break;
            	case '930':
            		if( !empty( $_REQUEST["Ds_pay_method"] ) && $_REQUEST["Ds_pay_method"] == 'R')
            		{
            			$reason =  'Realizado por Transferencia bancaria';
            		} else
            		{
            			$reason =  'Realizado por Domiciliacion bancaria';
            		}
            	break;
            	case '944':
            		$reason =  'Sesión Incorrecta.';
            	break;
            	case '950':
            		$reason =  'Operación de devolución no permitida.';
            	break;
            	case '9064':
            		$reason =  'Número de posiciones de la tarjeta incorrecto.';
            	break;
            	case '9078':
            		$reason =  'No existe método de pago válido para esa tarjeta.';
            	break;
            	case '9093':
            		$reason =  'Tarjeta no existente.';
            	break;
            	case '9094':
            		$reason =  'Rechazo servidores internacionales.';
            	break;
            	case '9104':
            		$reason =  'Comercio con "titular seguro" y titular sin clave de compra segura.';
            	break;
            	case '9218':
            		$reason =  'El comercio no permite op. seguras por entrada /operaciones.';
            	break;
            	case '9253':
            		$reason =  'Tarjeta no cumple el check-digit.';
            	break;
            	case '9256':
            		$reason =  'El comercio no puede realizar preautorizaciones.';
            	break;
            	case '9257':
            		$reason =  'Esta tarjeta no permite operativa de preautorizaciones.';
            	break;
            	case '9261':
            	case '912':
            	case '9912':
            		$reason =  'Emisor no disponible';
            	break;
            	case '9913':
            		$reason =  'Error en la confirmación que el comercio envía al TPV Virtual (solo aplicable en la opción de sincronización SOAP).';
            	break;
            	case '9914':
            		$reason =  'Confirmación "KO" del comercio (solo aplicable en la opción de sincronización SOAP).';
            	break;
            	case '9915':
            		$reason =  'A petición del usuario se ha cancelado el pago.';
            	break;
            	case '9928':
            		$reason =  'Anulación de autorización en diferido realizada por el SIS (proceso batch).';
            	break;
            	case '9929':
            		$reason =  'Anulación de autorización en diferido realizada por el comercio.';
            	break;
            	case '9997':
            		$reason =  'Se está procesando otra transacción en SIS con la misma tarjeta.';
            	break;
            	case '9998':
            		$reason =  'Operación en proceso de solicitud de datos de tarjeta.';
            	break;
            	case '9999':
            		$reason =  'Operación que ha sido redirigida al emisor a autenticar.';
            	default:
            		$reason =  'Transaccion denegada codigo:'.$_REQUEST["Ds_Response"];
            	break;
            }
            return $reason;
       }


		/**
		 *	Delete the form from the addon's table
		 */
        public function delete_form( $formid)
		{
			global $wpdb;
			$wpdb->delete( $wpdb->prefix.$this->form_table, array('formid' => $formid), '%d' );
		} // delete_form

		/**
		 *	Clone the form's row
		 */
		public function clone_form( $original_form_id, $new_form_id )
		{
			global $wpdb;

			$form_rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $original_form_id ), ARRAY_A);

			if(!empty($form_rows))
			{
				foreach($form_rows as $form_row)
				{
					unset($form_row["id"]);
					$form_row["formid"] = $new_form_id;
					$wpdb->insert( $wpdb->prefix.$this->form_table, $form_row);
				}
			}
		} // End clone_form

		/**
		 *	It is called when the form is exported to export the addons data too.
		 *  Receive an array with the other addons data, and the form's id for filtering.
		 */
		public function export_form($addons_array, $formid)
		{
			global $wpdb;
			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $formid ), ARRAY_A );
			if(!empty($rows))
			{
				$addons_array[ $this->addonID ] = array();
				foreach($rows as $row)
				{
					unset($row['id']);
					unset($row['formid']);
					$addons_array[ $this->addonID ][] = $row;
				}
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
							$wpdb->prefix.$this->form_table,
							$row
						);
					}
				}
			}
		} // End import_form

    } // End Class

    // Main add-on code
    $cpcff_sabtpvbizum_obj = new CPCFF_SabTPVBizum();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cpcff_sabtpvbizum_obj);
}

if( !class_exists( 'CPCFF_SermepaTPV' ) )
{
class CPCFF_SermepaTPV{
    protected $_setEnvironment;
    protected $_setNameForm;
    protected $_setIdForm;
    protected $_setParameters;
    protected $_setVersion;
    protected $_setNameSubmit;
    protected $_setIdSubmit;
    protected $_setValueSubmit;
    protected $_setStyleSubmit;
    protected $_setClassSubmit;
    protected $_setSignature;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setEnvironment();

        $this->_setParameters = array();
        $this->_setVersion = 'HMAC_SHA256_V1';
        $this->_setNameForm = 'redsys_form';
        $this->_setIdForm = 'redsys_form';
        $this->_setNameSubmit = 'btn_submit';
        $this->_setIdSubmit = 'btn_submit';
        $this->_setValueSubmit = 'Send';
        $this->_setStyleSubmit = '';
        $this->_setClassSubmit = '';

    }

    /************* NEW METHODS ************* */

    /**
     * Set identifier required
     *
     * @param string $value Este parámetro se utilizará para manejar la referencia asociada a los datos de tarjeta. Es
     *                      un campo alfanumérico de un máximo de 40 posiciones cuyo valor es generado por el TPV
     *                      Virtual.
     *
     * @return $this
     * @throws TpvException
     */
    public function setIdentifier($value = 'REQUIRED')
    {
        if ($this->isEmpty($value)) {
            throw new TpvException('Please add value');
        }

        $this->_setParameters['DS_MERCHANT_IDENTIFIER'] = $value;

        return $this;
    }

    /**
     * @param bool $flat
     *
     * @return $this
     * @throws TpvException
     */
    public function setMerchantDirectPayment($flat = false)
    {
        if (!is_bool($flat)) {
            throw new TpvException('Please set true or false');
        }

        $this->_setParameters['DS_MERCHANT_DIRECTPAYMENT'] = $flat;

        return $this;
    }

    /**
     * Set amount (required)
     *
     * @param $amount
     *
     * @return $this
     * @throws TpvException
     */
    public function setAmount($amount)
    {
        if ($amount < 0) {
            throw new TpvException('Amount must be greater than or equal to 0.');
        }

        $amount = $this->convertNumber($amount);
        $amount = intval(strval($amount * 100));

        $this->_setParameters['DS_MERCHANT_AMOUNT'] = $amount;

        return $this;
    }

    /**
     * Set Order number - [The first 4 digits must be numeric.] (required)
     *
     * @param $order
     *
     * @return $this
     * @throws TpvException
     */
    public function setOrder($order='')
    {
        $order = trim($order);
        if (strlen($order) <= 3 || strlen($order) > 12 || !is_numeric(substr($order, 0, 4))) {
            throw new TpvException('Order id must be a 4 digit string at least, maximum 12 characters.');
        }

        $this->_setParameters['DS_MERCHANT_ORDER'] = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return mixed
     */
    public function getOrder()
    {
        return $this->_setParameters['DS_MERCHANT_ORDER'];
    }

    /**
     * Get Ds_Order of Notification
     *
     * @param array $parameters Array with parameters
     *
     * @return string
     */
    public function getOrderNotification($parameters)
    {
        $order = '';
        foreach ($parameters as $key => $value) {
            if (strtolower($key) === 'ds_order') {
                $order = $value;
            }
        }

        return $order;
    }

    /**
     * Set code Fuc of trade (required)
     *
     * @param string $fuc Fuc
     *
     * @return $this
     * @throws TpvException
     */
    public function setMerchantcode($fuc='')
    {
        if ($this->isEmpty($fuc)) {
            throw new TpvException('Please add Fuc');
        }

        $this->_setParameters['DS_MERCHANT_MERCHANTCODE'] = $fuc;

        return $this;
    }

    /**
     * Set currency
     *
     * @param int $currency Algunos ejemplos: 978 para Euros, 840 para Dólares, 826 para libras esterlinas y 392 para Yenes.
     *
     * @return $this
     * @throws TpvException
     */
    public function setCurrency($currency = 978)
    {
        if (!preg_match('/^[0-9]{3}$/', $currency)) {
            throw new TpvException('Currency is not valid');
        }

        $this->_setParameters['DS_MERCHANT_CURRENCY'] = $currency;

        return $this;
    }

    /**
     * Set Transaction type
     *
     * @param int $transaction
     *
     * @return $this
     * @throws TpvException
     */
    public function setTransactiontype($transaction = 0)
    {
        if ($this->isEmpty($transaction)) {
            throw new TpvException('Please add transaction type');
        }

        $this->_setParameters['DS_MERCHANT_TRANSACTIONTYPE'] = $transaction;

        return $this;
    }

    /**
     * Set terminal by default is 1 to  Sadabell(required)
     *
     * @param int $terminal
     *
     * @return $this
     * @throws TpvException
     */
    public function setTerminal($terminal = 1)
    {
        if (intval($terminal) === 0) {
            throw new TpvException('Terminal is not valid.');
        }

        $this->_setParameters['DS_MERCHANT_TERMINAL'] = $terminal;

        return $this;
    }

    /**
     * Set url notification
     *
     * @param string $url
     * @return $this
     */
    public function setNotification($url = '')
    {
        $this->_setParameters['DS_MERCHANT_MERCHANTURL'] = $url;

        return $this;
    }

    /**
     * Set url Ok
     *
     * @param string $url
     * @return $this
     */
    public function setUrlOk($url = '')
    {
        $this->_setParameters['DS_MERCHANT_URLOK'] = $url;

        return $this;
    }

    /**
     * Set url Ko
     *
     * @param string $url
     * @return $this
     */
    public function setUrlKo($url = '')
    {
        $this->_setParameters['DS_MERCHANT_URLKO'] = $url;

        return $this;
    }

    /**
     * @param string $version
     * @return $this
     */
    public function setVersion($version = '')
    {
        if ($this->isEmpty($version)) {
            throw new TpvException('Please add version.');
        }
        $this->_setVersion = $version;

        return $this;
    }

    /**
     * Generate Merchant Parameters
     *
     * @return string
     */
    public function generateMerchantParameters()
    {
        //Convert Array to Json
        $json = $this->arrayToJson($this->_setParameters);

        //Return Json to Base64
        return $this->encodeBase64($json);
    }

    /**
     * Generate Merchant Signature
     *
     * @param string $key
     *
     * @return string
     */
    public function generateMerchantSignature($key)
    {
        $key = $this->decodeBase64($key);
        //Generate Merchant Parameters
        $merchant_parameter = $this->generateMerchantParameters();
        // Get key with Order and key
        $key = $this->encrypt_3DES($this->getOrder(), $key);
        // Generated Hmac256 of Merchant Parameter
        $result = $this->hmac256($merchant_parameter, $key);

        // Base64 encoding
        return $this->encodeBase64($result);
    }

    /**
     * Generate Merchant Signature Notification
     *
     * @param string $key
     * @param string $data
     *
     * @return string
     */
    public function generateMerchantSignatureNotification($key, $data)
    {
        $key = $this->decodeBase64($key);
        // Decode data base64
        $decode = $this->base64_url_decode($data);
        // Los datos decodificados se pasan al array de datos
        $parameters = $this->JsonToArray($decode);
        $order = $this->getOrderNotification($parameters);

        $key = $this->encrypt_3DES($order, $key);
        // Generated Hmac256 of Merchant Parameter
        $result = $this->hmac256($data, $key);

        return $this->base64_url_encode($result);
    }

    /**
     * Set Merchant Signature
     *
     * @param string $signature
     * @return $this
     */
    public function setMerchantSignature($signature)
    {
        $this->_setSignature = $signature;

        return $this;
    }

    /**
     * Set enviroment
     *
     * @param string $environment test or live
     *
     * @return $this
     * @throws Exception
     */
    public function setEnvironment($environment = 'test')
    {
        $environment = trim($environment);
        if ($environment === 'live') {
            //Live
            $this->_setEnvironment = 'https://sis.redsys.es/sis/realizarPago';
        } elseif ($environment === 'test') {
            //Test
            $this->_setEnvironment = 'https://sis-t.redsys.es:25443/sis/realizarPago';
        } else {
            throw new TpvException('Add test or live');
        }

        return $this;
    }

    /**
     * @param string $environment
     * @deprecated Use `setEnvironment`
     * @return $this
     */
    public function setEnviroment($environment = 'test')
    {
        $this->setEnvironment($environment);

        return $this;
    }

    /**
     * Set language code by default 001 = Spanish
     *
     * @param string $languageCode Language code [Castellano-001, Inglés-002, Catalán-003, Francés-004, Alemán-005,
     *                             Holandés-006, Italiano-007, Sueco-008, Portugués-009, Valenciano-010, Polaco-011,
     *                             Gallego-012 y Euskera-013.]
     *
     * @return $this
     * @throws Exception
     */
    public function setLanguage($languageCode = '001')
    {
        if ($this->isEmpty($languageCode)) {
            throw new TpvException('Add language code');
        }

        $this->_setParameters['DS_MERCHANT_CONSUMERLANGUAGE'] = trim($languageCode);

        return $this;
    }

    /**
     * Return enviroment
     *
     * @return string Url of enviroment
     */
    public function getEnviroment()
    {
        return $this->_setEnvironment;
    }

    /**
     * Optional field for the trade to be included in the data sent by the "on-line" response to trade if this option
     * has been chosen.
     *
     * @param string $merchantdata
     *
     * @return $this
     * @throws Exception
     */
    public function setMerchantData($merchantdata='')
    {
        if ($this->isEmpty($merchantdata)) {
            throw new TpvException('Add merchant data');
        }

        $this->_setParameters['DS_MERCHANT_MERCHANTDATA'] = trim($merchantdata);

        return $this;
    }

    /**
     * Set product description (optional)
     *
     * @param string $description
     *
     * @return $this
     * @throws Exception
     */
    public function setProductDescription($description = '')
    {
        if ($this->isEmpty($description)) {
            throw new TpvException('Add product description');
        }

        $this->_setParameters['DS_MERCHANT_PRODUCTDESCRIPTION'] = trim($description);

        return $this;
    }

    /**
     * Set name of the user making the purchase (required)
     *
     * @param string $titular name of the user (for example Alonso Cotos)
     *
     * @return $this
     * @throws Exception
     */
    public function setTitular($titular = '')
    {
        if ($this->isEmpty($titular)) {
            throw new TpvException('Add name for the user');
        }

        $this->_setParameters['DS_MERCHANT_TITULAR'] = trim($titular);

        return $this;
    }

    /**
     * Set Trade name Trade name will be reflected in the ticket trade (Optional)
     *
     * @param string $tradename trade name
     *
     * @return $this
     * @throws Exception
     */
    public function setTradeName($tradename = '')
    {
        if ($this->isEmpty($tradename)) {
            throw new TpvException('Add name for Trade name');
        }

        $this->_setParameters['DS_MERCHANT_MERCHANTNAME'] = trim($tradename);

        return $this;
    }

    /**
     * Payment type
     *
     * @param string $method [T o C = Sólo Tarjeta (mostrará sólo el formulario para datos de tarjeta)
     *                       R = Pago por Transferencia, D = Domiciliacion]
     *
     * @return $this
     * @throws Exception
     */
    public function setMethod($method = 'T')
    {
        if ($this->isEmpty($method)) {
            throw new TpvException('Add pay method');
        }

        $this->_setParameters['DS_MERCHANT_PAYMETHODS'] = trim($method);

        return $this;
    }

    /**
     * Card number
     *
     * @param string $pan Tarjeta. Su longitud depende del tipo de tarjeta.
     *
     * @return $this
     * @throws TpvException
     */
    public function setPan($pan=0)
    {
        if (intval($pan) == 0) {
            throw new TpvException('Pan not valid');
        }

        $this->_setParameters['DS_MERCHANT_PAN'] = $pan;

        return $this;
    }

    /**
     * Expire date
     *
     * @param $expirydate . Caducidad de la tarjeta. Su formato es AAMM, siendo AA los dos últimos dígitos del año y MM
     *                    los dos dígitos del mes.
     *
     * @return $this
     * @throws TpvException
     */
    public function setExpiryDate($expirydate='')
    {
        if ( !$this->isExpiryDate($expirydate) ) {
            throw new TpvException('Expire date is not valid');
        }
        $this->_setParameters['DS_MERCHANT_EXPIRYDATE'] = $expirydate;
        return $this;

    }

    /**
     * CVV2 card
     *
     * @param string $cvv2 Código CVV2 de la tarjeta
     *
     * @return $this
     * @throws TpvException
     */
    public function setCVV2($cvv2=0)
    {
        if (intval($cvv2) == 0) {
            throw new TpvException('CVV2 is not valid');
        }

        $this->_setParameters['DS_MERCHANT_CVV2'] = $cvv2;

        return $this;
    }

    /**
     * Set name to form
     *
     * @param string $name Name for form.
     * @return $this
     */
    public function setNameForm($name = 'servired_form')
    {
        $this->_setNameForm = $name;

        return $this;
    }

    /**
     * Get name form
     *
     * @return string
     */
    public function getNameForm()
    {
        return $this->_setNameForm;
    }

    /**
     * Set Id to form
     *
     * @param string $id Name for Id
     * @return $this
     */
    public function setIdForm($id = 'servired_form')
    {
        $this->_setIdForm = $id;

        return $this;
    }

    /**
     * Set Attributes to submit
     *
     * @param string $name Name submit
     * @param string $id Id submit
     * @param string $value Value submit
     * @param string $style Set Style
     * @param string $cssClass CSS class
     * @return $this
     */
    public function setAttributesSubmit(
        $name = 'btn_submit',
        $id = 'btn_submit',
        $value = 'Send',
        $style = '',
        $cssClass = ''
    ) {
        $this->_setNameSubmit = $name;
        $this->_setIdSubmit = $id;
        $this->_setValueSubmit = $value;
        $this->_setStyleSubmit = $style;
        $this->_setClassSubmit = $cssClass;

        return $this;
    }

    /**
     * Execute redirection to TPV
     *
     * @return string|null
     */
    public function executeRedirection($return = false)
    {
        $html = $this->createForm();
        $html .= '<script>document.forms["'.$this->_setNameForm.'"].submit();</script>';

        if (!$return) {
            echo $html;

            return null;
        }

        return $html;
    }

    /**
     * Generate form html
     *
     * @return string
     */
    public function createForm()
    {
        $form = '
            <form action="'.$this->_setEnvironment.'" method="post" id="'.$this->_setIdForm.'" name="'.$this->_setNameForm.'" >
                <input type="hidden" name="Ds_MerchantParameters" value="'.$this->generateMerchantParameters().'"/>
                <input type="hidden" name="Ds_Signature" value="'.$this->_setSignature.'"/>
                <input type="hidden" name="Ds_SignatureVersion" value="'.$this->_setVersion.'"/>
                <input type="hidden" name="'.$this->_setNameSubmit.'" id="'.$this->_setIdSubmit.'" value="'.$this->_setValueSubmit.'" '.($this->_setStyleSubmit != '' ? ' style="'.$this->_setStyleSubmit.'"' : '').' '.($this->_setClassSubmit != '' ? ' class="'.$this->_setClassSubmit.'"' : '').'>
            </form>
        ';

        return $form;
    }

    /**
     * Check if properly made ​​the purchase.
     *
     * @param string $key      Key
     * @param array  $postData Data received by the bank
     *
     * @return bool
     * @throws TpvException
     */
    public function check($key = '', $postData = '')
    {
        if (!isset($postData)) {
            throw new TpvException("Add data return of bank");
        }

        $parameters = $postData["Ds_MerchantParameters"];
        $signatureReceived = $postData["Ds_Signature"];
        $signature = $this->generateMerchantSignatureNotification($key, $parameters);

        return ($signature === $signatureReceived);
    }

    /**
     *  Decode Ds_MerchantParameters, return array with the parameters
     *
     * @param $parameters
     *
     * @return array with parameters of bank
     */
    public function getMerchantParameters($parameters)
    {
        $decoded = $this->decodeParameters($parameters);

        return $this->JsonToArray($decoded);
    }

    /**
     * Return array with all parameters assigned.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->_setParameters;
    }

    /**
     * Return version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->_setVersion;
    }

    /**
     * Return MerchantSignature
     *
     * @return string
     */
    public function getMerchantSignature()
    {
        return $this->_setSignature;
    }

    // ******** UTILS ********

    /**
     * Convert Array to json
     *
     * @param array $data Array
     *
     * @return string Json
     */
    protected function arrayToJson($data)
    {
        return json_encode($data);
    }

    /**
     * Convert Json to array
     *
     * @param string $data
     *
     * @return mixed
     */
    protected function JsonToArray($data)
    {
        return json_decode($data, true);
    }

    /**
     * Generate sha256
     *
     * @param string $data
     * @param string $key
     *
     * @return string
     */
    protected function hmac256($data, $key)
    {
        return hash_hmac('sha256', $data, $key, true);
    }

    /**
     * Encrypt to 3DES
     *
     * @param string $data Data for encrypt
     * @param string $key  Key
     *
     * @return string
     */
    protected function encrypt_3DES($data, $key)
    {
        $iv = "\0\0\0\0\0\0\0\0";
        $data_padded = $data;

        if (strlen($data_padded) % 8) {
            $data_padded = str_pad($data_padded, strlen($data_padded) + 8 - strlen($data_padded) % 8, "\0");
        }

        return openssl_encrypt($data_padded, "DES-EDE3-CBC", $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, $iv);
    }

    /**
     * @param string $data
     *
     * @return bool|string
     */
    protected function decodeParameters($data)
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * @param string $value
     *
     * @return int
     */
    protected function isEmpty($value)
    {
        return '' === trim($value);
    }

    /**
     * Check if expiry date is valid
     *
     * @param string $expirydate
     * @return boolean
     */
    protected function isExpiryDate($expirydate='')
    {
        return (strlen(trim($expirydate)) == 4 && is_numeric($expirydate));
    }

    /**
     * Check is order is valid
     *
     * @param string $order
     * @return boolean
     */
    protected function isValidOrder($order='')
    {
        return ( strlen($order) >= 4 && strlen($order) <= 12 && is_numeric(substr($order, 0, 4)) )?true:false;

    }

    /**
     * @param mixed $price
     *
     * @return string
     */
    protected function convertNumber($price)
    {
        return number_format(str_replace(',', '.', $price), 2, '.', '');
    }

    /******  Base64 Functions  *****
     *
     * @param string $input
     *
     * @return string
     */
    protected function base64_url_encode($input)
    {
        return strtr(base64_encode($input), '+/', '-_');
    }

    /**
     * @param string $data
     *
     * @return string
     */
    protected function encodeBase64($data)
    {
        return base64_encode($data);
    }

    /**
     * @param string $input
     *
     * @return string
     */
    protected function base64_url_decode($input)
    {
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * @param string $data
     *
     * @return string
     */
    protected function decodeBase64($data)
    {
        return base64_decode($data);
    }

    // ******** END UTILS ********
}
}

?>