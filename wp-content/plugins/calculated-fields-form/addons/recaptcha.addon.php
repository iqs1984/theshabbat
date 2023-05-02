<?php
/*
....
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPCFF_reCAPTCHA' ) )
{
    class CPCFF_reCAPTCHA extends CPCFF_BaseAddon
    {
		static public $category = 'External Services';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-recaptcha-20151106";
		protected $name = "CFF - reCAPTCHA";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#recaptcha-addon';

		public function get_addon_settings()
		{
			if( isset( $_REQUEST[ 'cpcff_recaptcha' ] ) )
			{
				check_admin_referer( $this->addonID, '_cpcff_nonce' );
				update_option( 'cpcff_recaptcha_sitekey', sanitize_text_field( $_REQUEST[ 'cpcff_recaptcha_sitekey' ] ) );
				update_option( 'cpcff_recaptcha_secretkey', sanitize_text_field( $_REQUEST[ 'cpcff_recaptcha_secretkey' ] ) );
				update_option( 'cpcff_recaptcha_version', $_REQUEST[ 'cpcff_recaptcha_version' ] == 'v2' ? 'v2' : 'v3' );
				update_option( 'cpcff_recaptcha_invisible', isset($_REQUEST[ 'cpcff_recaptcha_invisible' ] ) ? 1 : 0 );
				update_option( 'cpcff_recaptcha_check_twice', isset($_REQUEST[ 'cpcff_recaptcha_check_twice' ] ) ? 1 : 0 );
			}

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<form method="post" action="<?php print esc_url(admin_url('admin.php?page=cp_calculated_fields_form')); ?>">
				<div id="metabox_recaptcha_addon_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_recaptcha_addon_settings' ) ); ?>" >
					<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
					<div class="inside">
						<table cellspacing="0" style="width:100%;">
							<tr>
								<td style="white-space:nowrap;width:200px;"><?php _e('Site Key', 'calculated-fields-form');?>:</td>
								<td>
									<input type="text" name="cpcff_recaptcha_sitekey" value="<?php echo ( ( $key = get_option( 'cpcff_recaptcha_sitekey' ) ) !== false ) ? $key : ''; ?>"  style="width:80%;" />
								</td>
							</tr>
							<tr>
								<td style="white-space:nowrap;width:200px;"><?php _e('Secret Key', 'calculated-fields-form');?>:</td>
								<td>
									<input type="text" name="cpcff_recaptcha_secretkey" value="<?php echo ( ( $key = get_option( 'cpcff_recaptcha_secretkey' ) ) !== false ) ? $key : ''; ?>" style="width:80%;" />
								</td>
							</tr>
							<tr>
								<td></td>
								<td>
									<br /><br />
									<label><input type="radio" name="cpcff_recaptcha_version" value="v2" <?php print get_option('cpcff_recaptcha_version', 'v2') == 'v2' ? 'CHECKED' : ''; ?> /> reCaptcha V2</label> -
									<label><input type="radio" name="cpcff_recaptcha_version" value="v3" <?php print get_option('cpcff_recaptcha_version', 'v2') == 'v3' ? 'CHECKED' : ''; ?> /> reCaptcha V3</label>
									<br /><br />
								</td>
							</tr>
							<tr class="cpcff-recaptcha-v2" style="display:none;">
								<td>
								</td>
								<td>
									<input type="checkbox" name="cpcff_recaptcha_invisible" <?php echo ( get_option( 'cpcff_recaptcha_invisible' ) ) ? 'CHECKED' : ''; ?> />
									<?php _e('Is it a key for invisible reCAPTCHA?','calculated-fields-form');?>
								</td>
							</tr>
							<tr class="cpcff-recaptcha-v2" style="display:none;">
								<td>
								</td>
								<td>
									<input type="checkbox" name="cpcff_recaptcha_check_twice" <?php echo ( get_option( 'cpcff_recaptcha_check_twice' ) ) ? 'CHECKED' : ''; ?> />
									<?php _e('Check reCAPTCHA in both sides, client and server','calculated-fields-form');?><br>
									<em><?php _e('If there is any issue with the sessions in your server, please, untick the checkbox','calculated-fields-form');?></em>
								</td>
							</tr>
						</table>
						<script>
							jQuery(document).on('change', '[name="cpcff_recaptcha_version"]', function(){
								jQuery('.cpcff-recaptcha-v2')[(jQuery('[name="cpcff_recaptcha_version"]:checked').val() == 'v2') ? 'show' : 'hide']();
							});
							jQuery('[name="cpcff_recaptcha_version"]').change();
						</script>
						<input type="submit" value="Save settings" class="button-secondary" />
					</div>
					<input type="hidden" name="cpcff_recaptcha" value="1" />
					<input type="hidden" name="_cpcff_nonce" value="<?php echo wp_create_nonce( $this->addonID ); ?>" />
				</div>
			</form>
			<?php
		}

		public function get_addon_form_settings( $form_id )
		{
			$lang_list = array(
				'ar'=>'Arabic',
				'af'=>'Afrikaans',
				'am'=>'Amharic',
				'hy'=>'Armenian',
				'az'=>'Azerbaijani',
				'eu'=>'Basque',
				'bn'=>'Bengali',
				'bg'=>'Bulgarian',
				'ca'=>'Catalan',
				'zh-HK'=>'Chinese (Hong Kong)',
				'zh-CN'=>'Chinese (Simplified)',
				'zh-TW'=>'Chinese (Traditional)',
				'hr'=>'Croatian',
				'cs'=>'Czech',
				'da'=>'Danish',
				'nl'=>'Dutch',
				'en-GB'=>'English (UK)',
				'en'=>'English (US)',
				'et'=>'Estonian',
				'fil'=>'Filipino',
				'fi'=>'Finnish',
				'fr'=>'French',
				'fr-CA'=>'French (Canadian)',
				'gl'=>'Galician',
				'ka'=>'Georgian',
				'de'=>'German',
				'de-AT'=>'German (Austria)',
				'de-CH'=>'German (Switzerland)',
				'el'=>'Greek',
				'gu'=>'Gujarati',
				'iw'=>'Hebrew',
				'hi'=>'Hindi',
				'hu'=>'Hungarain',
				'is'=>'Icelandic',
				'id'=>'Indonesian',
				'it'=>'Italian',
				'ja'=>'Japanese',
				'kn'=>'Kannada',
				'ko'=>'Korean',
				'lo'=>'Laothian',
				'lv'=>'Latvian',
				'lt'=>'Lithuanian',
				'ms'=>'Malay',
				'ml'=>'Malayalam',
				'mr'=>'Marathi',
				'mn'=>'Mongolian',
				'no'=>'Norwegian',
				'fa'=>'Persian',
				'pl'=>'Polish',
				'pt'=>'Portuguese',
				'pt-BR'=>'Portuguese (Brazil)',
				'pt-PT'=>'Portuguese (Portugal)',
				'ro'=>'Romanian',
				'ru'=>'Russian',
				'sr'=>'Serbian',
				'si'=>'Sinhalese',
				'sk'=>'Slovak',
				'sl'=>'Slovenian',
				'es'=>'Spanish',
				'es-419'=>'Spanish (Latin America)',
				'sw'=>'Swahili',
				'sv'=>'Swedish',
				'ta'=>'Tamil',
				'te'=>'Telugu',
				'th'=>'Thai',
				'tr'=>'Turkish',
				'uk'=>'Ukrainian',
				'ur'=>'Urdu',
				'vi'=>'Vietnamese',
				'zu'=>'Zulu'
			);

			if(isset($_POST['cpcff_recaptcha_language']) && isset($lang_list[$_POST['cpcff_recaptcha_language']])) update_option('cpcff_recaptcha_language_'.$form_id, $_POST['cpcff_recaptcha_language']);

			$recaptcha_language = $this->_get_lang($form_id);

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<div id="metabox_recaptcha_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_recaptcha_addon_form_settings' ) ); ?>" >
				<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
				<div class="inside">
					<table cellspacing="0">
						<tr>
							<td style="white-space:nowrap;width:200px; vertical-align:top;font-weight:bold;"><?php _e('reCAPTCHA language', 'calculated-fields-form');?>:</td>
							<td>
								<select name="cpcff_recaptcha_language">
									<?php
										foreach($lang_list as $code => $lang)
										{
											print '<option value="'.esc_attr($code).'" '.( $code == $recaptcha_language ? 'SELECTED' : '').'>'.esc_html($lang).'</option>';
										}
									?>
								</select>
							</td>
						</tr>
					</table>
                    <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
				</div>
			</div>
			<?php
		}

		/************************ ADDON CODE *****************************/
        /************************ ATTRIBUTES *****************************/

		private $_recaptcha_inserted = false;
		private $_recaptcha_callback = false;
		private $_im_flag    = false; // I'm
		private $_sitekey 	= '';
		private $_secretkey = '';
		private $_version = 'v2';
		private $_invisible = false;
		private	$_check_twice = false;
		private $_cpcff_main;
		private $_current_form; // To know the form that is being loaded to select the language in the footer

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->_cpcff_main = CPCFF_MAIN::instance();
			$this->description = __("The add-on allows to protect the forms with reCAPTCHA service of Google", 'calculated-fields-form');

            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;
			if( !is_admin() )
			{
				if( $this->apply_addon() !== false )
				{
					// If reCAPTCHA is enabled do not include the common captcha in the form
					add_filter( 'cpcff_get_option', array( &$this, 'get_form_options' ), 10, 3 );

					// If the reCAPTCHA is being validated with AJAX in the doValidate routine
					if( isset( $_REQUEST[ 'cpcff_recaptcha_response' ] ) )
					{
						if(
							!empty($_REQUEST[ 'cpcff_id' ]) &&
							$this->validate_form( trim( $_REQUEST[ 'cpcff_recaptcha_response' ] ), intval(@$_REQUEST[ 'cpcff_id' ] ) )
						)
						{
							print 'ok';
						}
						else
						{
							print 'captchafailed';
						}
						exit;
					}

					// Inserts the SCRIPT tag to import the reCAPTCHA on webpage
					add_action( 'wp_footer', array( &$this, 'insert_script' ), 99 );
					add_action( 'cpcff_footer', array( &$this, 'insert_script' ), 99 );

					// Inserts the reCAPTCHA field in the form
					add_filter( 'cpcff_the_form', array( &$this, 'insert_recaptcha'), 99, 2 );

					// Validate the form's submission
					add_filter( 'cpcff_valid_submission', array( &$this, 'validate_form' ) );

					// Insert the JS code to validate the recaptcha code through AJAX
					add_action( 'cpcff_script_after_validation', array( &$this, 'validate_form_script'), 1, 2 );
				}
			}
        } // End __construct

        /************************ PRIVATE METHODS *****************************/

		/**
		 * Check if the API keys have been defined and return the pair of keys or false
		 */
        private function apply_addon()
		{
			if(
				( $sitekey   = get_option( 'cpcff_recaptcha_sitekey' ) ) !== false && !empty( $sitekey ) &&
				( $secretkey = get_option( 'cpcff_recaptcha_secretkey' ) ) !== false && !empty( $secretkey )
			)
			{
				$this->_sitekey   = $sitekey;
				$this->_secretkey = $secretkey;
				$this->_version = get_option('cpcff_recaptcha_version', 'v2');
				$this->_invisible = get_option( 'cpcff_recaptcha_invisible' );
				$this->_check_twice = get_option( 'cpcff_recaptcha_check_twice' );
				return true;
			}
			return false;

		} // End apply_addon

		private function _get_lang($form_id)
		{
			return get_option('cpcff_recaptcha_language_'.$form_id, 'en');
		} // End _get_lang

		/************************ PUBLIC METHODS  *****************************/

		/**
         * Check if the reCAPTCHA is used in the form, and inserts the SCRIPT tag that includes its code
         */
        public function	insert_script( $params )
		{
			if( $this->_recaptcha_inserted )
			{
				if( !$this->_recaptcha_callback )
				{
                    $this->_recaptcha_callback = true;
					print '
					<script type="text/javascript">
                        (function(){
                            var fbuilderjQuery = fbuilderjQuery || jQuery,
                                cff_recaptcha_script,
                                processed_forms = {},
                                pending_forms = {};
                            function process_form(formid)
                            {
                                processed_forms[formid] = 1;
                                if(formid in pending_forms) delete pending_forms[formid];
                                var e = fbuilderjQuery(".cff-recaptcha","#"+formid);
                                ';
                                if($this->_version == 'v2')
                                {
                                    print 'grecaptcha.render( e[0], {"sitekey" : "'.esc_js($this->_sitekey).'"'.(($this->_invisible*1) ? ', "size":"invisible"':'').' });';
                                }
                                else
                                {
                                    print 'grecaptcha.execute("'.esc_js($this->_sitekey).'", { action: "CFF" }).then(function (token) { fbuilderjQuery(\'.cff-recaptcha\').html(\'<input type="hidden" name="g-recaptcha-response" value="\'+token+\'">\'); });';
                                }

                                print 'fbuilderjQuery(this).closest("form").find(\'[id*="captchaimg"],[id*="hdcaptcha_cp_calculated_fields_form_post"]\').closest(".fields").remove();
                            }
                            window["cff_reCAPTCHA_callback"] = function(){
                                for(var i in pending_forms)
                                {
                                    process_form(i);
                                    delete pending_forms[i];
                                }
                            };
                            function load_script()
                            {';

                                $url = ($this->_version == 'v2') ?
                                '//www.google.com/recaptcha/api.js?onload=cff_reCAPTCHA_callback&render=explicit&hl='.urlencode($this->_get_lang(!empty($this->_current_form) ? $this->_current_form : 0)) :
                                '//www.google.com/recaptcha/api.js?onload=cff_reCAPTCHA_callback&render='.urlencode($this->_sitekey).'&hl='.urlencode($this->_get_lang(!empty($this->_current_form) ? $this->_current_form : 0));

                            print '
                                if(typeof grecaptcha == "undefined")
                                {
                                    if(typeof cff_recaptcha_script == "undefined")
                                    {
                                        var cff_recaptcha_script = document.createElement("script");
                                        cff_recaptcha_script.type  = "text/javascript";
                                        cff_recaptcha_script.src= "'.$url.'";
                                        document.body.appendChild(cff_recaptcha_script);
                                    }
                                }
                                else
                                {
                                    cff_reCAPTCHA_callback();
                                }
                            }
                            function check_form(formid)
                            {
                                if(typeof formid != "undefined" && formid != "")
                                {
                                    if(
                                        !(formid in processed_forms) &&
                                        fbuilderjQuery(".cff-recaptcha","#"+formid).length
                                    )
                                    {
                                        if(typeof cff_recaptcha_script != "undefined") process_form(formid);
                                        else
                                        {
                                            pending_forms[formid] = 1;
                                            load_script();
                                        }
                                    }
                                }
                            }
                            fbuilderjQuery(document).on("showHideDepEvent", function(evt, formid){
                                check_form(formid);
                            });
                            fbuilderjQuery(window).on("load", function(){
                                fbuilderjQuery("form").each(function(){
                                    var id = fbuilderjQuery(this).attr("id");
                                    check_form(id);
                                });
                            });
                        })();
					</script>';
				}
			}
		} // End insert_script

		/**
         * Check if the reCAPTCHA is used in the form, and inserts the reCAPTCHA tag
         */
        public function	insert_recaptcha( $form_code, $id )
		{
			$this->_im_flag = true;
			$is_captcha_enabled = $this->_cpcff_main->get_form($id)->get_option('cv_enable_captcha', true);
			$this->_im_flag = false;

			if( is_admin() || $is_captcha_enabled == false || $is_captcha_enabled == 'false' )
			{
				return $form_code;
			}
			$this->_current_form = $id;
			$this->_recaptcha_inserted = true;

            // Applied only to the forms preview
            add_filter('cpcff_form_preview_resources', array($this, 'form_preview_resources'));

			return str_replace(
				'<!--add-ons-->',
				'<div style="margin-top:20px;" class="cff-recaptcha" data-sitekey="'.$this->_sitekey.'" '.(($this->_invisible*1) ? 'data-size="invisible"':'').'></div>',
				$form_code
			);
		} // End insert_recaptcha

        public function form_preview_resources($v)
        {
            return '<script>fbuilderjQuery(window).on("load", function(){jQuery(".grecaptcha-badge").parents().css("visibility", "visible");});</script>';
        }
		/**
         * Insert the JS code into the doValidate function for checking the reCAPTCHA code with AJAX
         */
        public function validate_form_script( $sequence, $formid )
		{
			$this->_im_flag = true;
			$form_obj = $this->_cpcff_main->get_form($formid);
			$is_captcha_enabled = $form_obj->get_option('cv_enable_captcha', true);
			$this->_im_flag = false;

			if( $is_captcha_enabled == false || $is_captcha_enabled == 'false' )
			{
				return;
			}

			global $cpcff_default_texts_array;
			$cpcff_texts_array = $form_obj->get_option('vs_all_texts', $cpcff_default_texts_array);
			$cpcff_texts_array = CPCFF_AUXILIARY::array_replace_recursive(
				$cpcff_default_texts_array,
				is_string( $cpcff_texts_array ) ? unserialize( $cpcff_texts_array ) : $cpcff_texts_array
			);

		?>
			var recaptcha = $dexQuery( '[name="cp_calculatedfieldsf_pform<?php print $sequence; ?>"] [name="g-recaptcha-response"]' );
			if(
				recaptcha.length == 0 ||
				/^\s*$/.test( recaptcha.val() )
			)
			{
				disabling_form();
				var grecaptcha_e = $dexQuery( '[name="cp_calculatedfieldsf_pform<?php print $sequence; ?>"] .cff-recaptcha' );
				if(grecaptcha_e.length && grecaptcha_e.attr( 'data-size' ) == 'invisible')
				{
                    grecaptcha.execute();
					if(grecaptcha.getResponse() == '')
                    {
                        enabling_form();
                        return false;
                    }
				}
				else
				{
					alert('<?php echo( $cpcff_texts_array[ 'captcha_required_text' ][ 'text' ] ); ?>');
					enabling_form();
					return false;
				}
			}
		<?php
			if($this->_check_twice)
			{
		?>
			else
			{
				if(
					typeof validation_rules['<?php print esc_js( $this->addonID); ?>'] == 'undefined'||
					validation_rules['<?php print esc_js( $this->addonID); ?>'] == false
				)
				{
					disabling_form();
					validation_rules['<?php print esc_js( $this->addonID); ?>'] = false;
					$dexQuery.ajax({
						type: "GET",
						url:  "<?php echo CPCFF_AUXILIARY::site_url(true); ?>",
						data: {
							ps: "<?php echo $sequence; ?>",
							cpcff_recaptcha_response: recaptcha.val(),
							cpcff_id:<?php print $formid; ?>
						},
						success:function(result){
							enabling_form();
							if (result.indexOf("captchafailed") != -1)
							{
								alert('<?php echo( $cpcff_texts_array[ 'incorrect_captcha_text' ][ 'text' ] ); ?>');
							}
							else
							{
								validation_rules['<?php print esc_js( $this->addonID); ?>'] = true;
								processing_form();
							}
						}
					});
				}
			}
		<?php
			} // End if _check_twice
		} // End validate_form_script

		/**
         * Check if the reCAPTCHA is valid and return a boolean
         */
        public function	validate_form( $recaptcha_response = '', $id='' )
		{
			$this->_im_flag = true;
			$is_captcha_enabled = $this->_cpcff_main->get_form($id)->get_option('cv_enable_captcha', true);
			$this->_im_flag = false;

			if( $is_captcha_enabled == false || $is_captcha_enabled == 'false' )
			{
				return true;
			}

			// If was enabled the twice validation and the reCAPTCHA was validated with AJAX
			if(
				CP_SESSION::get_var('cpcff_recaptcha_i_am_human') !== false
			)
			{
				CP_SESSION::unset_var('cpcff_recaptcha_i_am_human');
				return true;
			}

			// The reCAPTCHA value is received in the form's submission
			if( isset( $_POST[ 'g-recaptcha-response' ] ) )
			{
				$recaptcha_response = $_POST[ 'g-recaptcha-response' ];
			}

			if( !empty( $recaptcha_response ) )
			{
				$response = wp_remote_post(
					'https://www.google.com/recaptcha/api/siteverify',
					array(
						'body' => array(
							'secret' 	=> $this->_secretkey,
							'response' 	=> $recaptcha_response
						)
					)
				);

				if( !is_wp_error( $response ) )
				{
					$response = json_decode( $response[ 'body' ] );
					if( !is_null( $response ) )
					{
						if(
							($this->_version == 'v2' && isset( $response->success ) && $response->success) ||
							($this->_version == 'v3' && isset( $response->score ) && $response->score >= 0.5)
						)
						{
							CP_SESSION::set_var('cpcff_recaptcha_i_am_human', 1);
							return true;
						}
					}

				}

			}
			return false;

		} // End cpcff_valid_submission

		/**
         * Corrects the form options
         */
        public function get_form_options( $value, $field, $formid )
        {

			if( !$this->_im_flag && $field == 'cv_enable_captcha' && $this->apply_addon() !== false ){
				return 'false';
			}
            return $value;
		} // End get_form_options

    } // End Class

    // Main add-on code
    $cpcff_recaptcha_obj = new CPCFF_reCAPTCHA();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cpcff_recaptcha_obj);
}