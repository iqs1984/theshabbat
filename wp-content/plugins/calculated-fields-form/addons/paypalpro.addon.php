<?php
/*
....
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPCFF_PayPalPro' ) )
{
    class CPCFF_PayPalPro extends CPCFF_BaseAddon
    {
		static public $category = 'Payment Gateways';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-paypalpro-20151212";
		protected $name = "CFF - PayPal Pro";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#paypal-pro-addon';
        protected $default_pay_label = "Pay with Credit Cards";

		public function get_addon_form_settings( $form_id )
		{
			global $wpdb;
			$table = $wpdb->prefix.$this->form_table;

			// Insertion in database
			if(
				isset( $_REQUEST[ 'cpcff_paypalpro_id' ] )
			)
			{
                $this->add_field_verify($table, 'paypalpro_default_country');
			    $wpdb->delete( $table, array( 'formid' => $form_id ), array( '%d' ) );
				$wpdb->insert(
								$table,
								array(
									'formid' => $form_id,
									'paypalpro_api_username'	 => $_REQUEST["paypalpro_api_username"],
									'paypalpro_api_password'	 => $_REQUEST["paypalpro_api_password"],
									'paypalpro_api_signature'	 => $_REQUEST["paypalpro_api_signature"],
                                    'paypalpro_api_bperiod'	     => $_REQUEST["paypalpro_api_bperiod"],
                                    'paypalpro_default_country'	 => $_REQUEST["paypalpro_default_country"],
									'currency'	 => $_REQUEST["pprocurrency"],
									'enabled'	 => $_REQUEST["enabled"],
                                    'enable_option_yes'	 => $_REQUEST["paypalpro_enable_option_yes"],
									'paypal_mode'	 => $_REQUEST["paypal_modepp"]
								),
								array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
							);
			}


			$rows = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM ".$table." WHERE formid=%d", $form_id )
					);
			if (!count($rows))
			{
			    $row["paypalpro_api_username"] = "";
			    $row["paypalpro_api_password"] = "";
			    $row["paypalpro_api_signature"] = "";
                $row["paypalpro_api_bperiod"] = "";
                $row["paypalpro_default_country"] = "GB";
			    $row["currency"] = "USD";
			    $row["enabled"] = "0";
			    $row["paypal_mode"] = "production";
                $row["enable_option_yes"] = $this->default_pay_label;
			} else {
			    $row["paypalpro_api_username"] = $rows[0]->paypalpro_api_username;
			    $row["paypalpro_api_password"] = $rows[0]->paypalpro_api_password;
			    $row["paypalpro_api_signature"] = $rows[0]->paypalpro_api_signature;
                $row["paypalpro_api_bperiod"] = $rows[0]->paypalpro_api_bperiod;
                $row["paypalpro_default_country"] = $rows[0]->paypalpro_default_country;
                if (empty($row["paypalpro_default_country"]))
                    $row["paypalpro_default_country"] = 'GB';
			    $row["currency"] = $rows[0]->currency;
			    $row["enabled"] = $rows[0]->enabled;
			    $row["paypal_mode"] = $rows[0]->paypal_mode;
                $row["enable_option_yes"] = $rows[0]->enable_option_yes;
			}

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<div id="metabox_paypalpro_addon_form_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_paypalpro_addon_form_settings' ) ); ?>" >
				<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
				<div class="inside">
				   <input type="hidden" name="cpcff_paypalpro_id" value="1" />
                   <table class="form-table">
                    <tr valign="top">
                    <th scope="row"><?php _e('Enable PayPal Pro? (if enabled PayPal Standard is disabled)', 'calculated-fields-form'); ?></th>
                    <td><select name="enabled">
                         <option value="0" <?php if (!$row["enabled"]) echo 'selected'; ?>><?php _e('No', 'calculated-fields-form'); ?></option>
                         <option value="1" <?php if ($row["enabled"] == '1') echo 'selected'; ?>><?php _e('Yes', 'calculated-fields-form'); ?></option>
                         <option value="2" <?php if ($row["enabled"] == '2') echo 'selected'; ?>><?php _e('Optional: This payment method + Pay Later (submit without payment)', 'calculated-fields-form'); ?></option>
                         <option value="3" <?php if ($row["enabled"] == '3') echo 'selected'; ?>><?php _e('Optional: This payment method + Other payment methods (enabled)', 'calculated-fields-form'); ?></option>
                         <option value="4" <?php if ($row["enabled"] == '4') echo 'selected'; ?>><?php _e('Optional: This payment method + Other payment methods  + Pay Later ', 'calculated-fields-form'); ?></option>
                         </select>
                         <div style="margin-top:10px;background:#EEF5FB;border: 1px dotted #888888;padding:10px;width:260px;">
                           <?php _e( 'Label for this payment option', 'calculated-fields-form' ); ?>:<br />
                           <input type="text" name="paypalpro_enable_option_yes" size="40" style="width:250px;" value="<?php echo esc_attr($row['enable_option_yes']); ?>" />
                        </div>
                    </td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('PayPal Pro - API UserName', 'calculated-fields-form'); ?></th>
                    <td><input type="text" name="paypalpro_api_username" size="20" value="<?php echo esc_attr($row["paypalpro_api_username"]); ?>" /></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('PayPal Pro - API Password', 'calculated-fields-form');?></th>
                    <td><input type="text" name="paypalpro_api_password" size="40" value="<?php echo esc_attr($row["paypalpro_api_password"]); ?>" /></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('PayPal Pro - API Signature', 'calculated-fields-form'); ?></th>
                    <td><input type="text" name="paypalpro_api_signature" size="20" value="<?php echo esc_attr($row["paypalpro_api_signature"]); ?>" /></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('PayPal Pro - Currency', 'calculated-fields-form'); ?></th>
                    <td><input type="text" name="pprocurrency" size="20" value="<?php echo esc_attr($row["currency"]); ?>" /></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Billing Period', 'calculated-fields-form'); ?></th>
                    <td><select name="paypalpro_api_bperiod">
                         <option value="" <?php if ($row["paypalpro_api_bperiod"] == '') echo 'selected'; ?>><?php _e('One-time payment', 'calculated-fields-form'); ?></option>
                         <option value="Day" <?php if ($row["paypalpro_api_bperiod"] == 'Day') echo 'selected'; ?>><?php _e('Daily', 'calculated-fields-form'); ?></option>
                         <option value="Week" <?php if ($row["paypalpro_api_bperiod"] == 'Week') echo 'selected'; ?>><?php _e('Weekly', 'calculated-fields-form'); ?></option>
                         <option value="SemiMonth" <?php if ($row["paypalpro_api_bperiod"] == 'SemiMonth') echo 'selected'; ?>><?php _e('SemiMonth', 'calculated-fields-form'); ?></option>
                         <option value="Month" <?php if ($row["paypalpro_api_bperiod"] == 'Month') echo 'selected'; ?>><?php _e('Monthly', 'calculated-fields-form'); ?></option>
                         <option value="Year" <?php if ($row["paypalpro_api_bperiod"] == 'Year') echo 'selected'; ?>><?php _e('Yearly', 'calculated-fields-form'); ?></option>
                        </select>
                    </td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Paypal Mode', 'calculated-fields-form'); ?></th>
                    <td><select name="paypal_modepp">
                         <option value="production" <?php if ($row["paypal_mode"] != 'sandbox') echo 'selected'; ?>><?php _e('Production - real payments processed', 'calculated-fields-form'); ?></option>
                         <option value="sandbox" <?php if ($row["paypal_mode"] == 'sandbox') echo 'selected'; ?>><?php _e('SandBox - PayPal testing sandbox area', 'calculated-fields-form'); ?></option>
                        </select>
                    </td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Default Country', 'calculated-fields-form'); ?></th>
                    <td><select name="paypalpro_default_country">
	<option value="AF"<?php if ($row["paypalpro_default_country"] == 'AF') echo ' selected'; ?>>Afghanistan</option>
	<option value="AX"<?php if ($row["paypalpro_default_country"] == 'AX') echo ' selected'; ?>>Åland Islands</option>
	<option value="AL"<?php if ($row["paypalpro_default_country"] == 'AL') echo ' selected'; ?>>Albania</option>
	<option value="DZ"<?php if ($row["paypalpro_default_country"] == 'DZ') echo ' selected'; ?>>Algeria</option>
	<option value="AS"<?php if ($row["paypalpro_default_country"] == 'AS') echo ' selected'; ?>>American Samoa</option>
	<option value="AD"<?php if ($row["paypalpro_default_country"] == 'AD') echo ' selected'; ?>>Andorra</option>
	<option value="AO"<?php if ($row["paypalpro_default_country"] == 'AO') echo ' selected'; ?>>Angola</option>
	<option value="AI"<?php if ($row["paypalpro_default_country"] == 'AI') echo ' selected'; ?>>Anguilla</option>
	<option value="AQ"<?php if ($row["paypalpro_default_country"] == 'AQ') echo ' selected'; ?>>Antarctica</option>
	<option value="AG"<?php if ($row["paypalpro_default_country"] == 'AG') echo ' selected'; ?>>Antigua and Barbuda</option>
	<option value="AR"<?php if ($row["paypalpro_default_country"] == 'AR') echo ' selected'; ?>>Argentina</option>
	<option value="AM"<?php if ($row["paypalpro_default_country"] == 'AM') echo ' selected'; ?>>Armenia</option>
	<option value="AW"<?php if ($row["paypalpro_default_country"] == 'AW') echo ' selected'; ?>>Aruba</option>
	<option value="AU"<?php if ($row["paypalpro_default_country"] == 'AU') echo ' selected'; ?>>Australia</option>
	<option value="AT"<?php if ($row["paypalpro_default_country"] == 'AT') echo ' selected'; ?>>Austria</option>
	<option value="AZ"<?php if ($row["paypalpro_default_country"] == 'AZ') echo ' selected'; ?>>Azerbaijan</option>
	<option value="BS"<?php if ($row["paypalpro_default_country"] == 'BS') echo ' selected'; ?>>Bahamas</option>
	<option value="BH"<?php if ($row["paypalpro_default_country"] == 'BH') echo ' selected'; ?>>Bahrain</option>
	<option value="BD"<?php if ($row["paypalpro_default_country"] == 'BD') echo ' selected'; ?>>Bangladesh</option>
	<option value="BB"<?php if ($row["paypalpro_default_country"] == 'BB') echo ' selected'; ?>>Barbados</option>
	<option value="BY"<?php if ($row["paypalpro_default_country"] == 'BY') echo ' selected'; ?>>Belarus</option>
	<option value="BE"<?php if ($row["paypalpro_default_country"] == 'BE') echo ' selected'; ?>>Belgium</option>
	<option value="BZ"<?php if ($row["paypalpro_default_country"] == 'BZ') echo ' selected'; ?>>Belize</option>
	<option value="BJ"<?php if ($row["paypalpro_default_country"] == 'BJ') echo ' selected'; ?>>Benin</option>
	<option value="BM"<?php if ($row["paypalpro_default_country"] == 'BM') echo ' selected'; ?>>Bermuda</option>
	<option value="BT"<?php if ($row["paypalpro_default_country"] == 'BT') echo ' selected'; ?>>Bhutan</option>
	<option value="BO"<?php if ($row["paypalpro_default_country"] == 'BO') echo ' selected'; ?>>Bolivia, Plurinational State of</option>
	<option value="BQ"<?php if ($row["paypalpro_default_country"] == 'BQ') echo ' selected'; ?>>Bonaire, Sint Eustatius and Saba</option>
	<option value="BA"<?php if ($row["paypalpro_default_country"] == 'BA') echo ' selected'; ?>>Bosnia and Herzegovina</option>
	<option value="BW"<?php if ($row["paypalpro_default_country"] == 'BW') echo ' selected'; ?>>Botswana</option>
	<option value="BV"<?php if ($row["paypalpro_default_country"] == 'BV') echo ' selected'; ?>>Bouvet Island</option>
	<option value="BR"<?php if ($row["paypalpro_default_country"] == 'BR') echo ' selected'; ?>>Brazil</option>
	<option value="IO"<?php if ($row["paypalpro_default_country"] == 'IO') echo ' selected'; ?>>British Indian Ocean Territory</option>
	<option value="BN"<?php if ($row["paypalpro_default_country"] == 'BN') echo ' selected'; ?>>Brunei Darussalam</option>
	<option value="BG"<?php if ($row["paypalpro_default_country"] == 'BG') echo ' selected'; ?>>Bulgaria</option>
	<option value="BF"<?php if ($row["paypalpro_default_country"] == 'BF') echo ' selected'; ?>>Burkina Faso</option>
	<option value="BI"<?php if ($row["paypalpro_default_country"] == 'BI') echo ' selected'; ?>>Burundi</option>
	<option value="KH"<?php if ($row["paypalpro_default_country"] == 'KH') echo ' selected'; ?>>Cambodia</option>
	<option value="CM"<?php if ($row["paypalpro_default_country"] == 'CM') echo ' selected'; ?>>Cameroon</option>
	<option value="CA"<?php if ($row["paypalpro_default_country"] == 'CA') echo ' selected'; ?>>Canada</option>
	<option value="CV"<?php if ($row["paypalpro_default_country"] == 'CV') echo ' selected'; ?>>Cape Verde</option>
	<option value="KY"<?php if ($row["paypalpro_default_country"] == 'KY') echo ' selected'; ?>>Cayman Islands</option>
	<option value="CF"<?php if ($row["paypalpro_default_country"] == 'CF') echo ' selected'; ?>>Central African Republic</option>
	<option value="TD"<?php if ($row["paypalpro_default_country"] == 'TD') echo ' selected'; ?>>Chad</option>
	<option value="CL"<?php if ($row["paypalpro_default_country"] == 'CL') echo ' selected'; ?>>Chile</option>
	<option value="CN"<?php if ($row["paypalpro_default_country"] == 'CN') echo ' selected'; ?>>China</option>
	<option value="CX"<?php if ($row["paypalpro_default_country"] == 'CX') echo ' selected'; ?>>Christmas Island</option>
	<option value="CC"<?php if ($row["paypalpro_default_country"] == 'CC') echo ' selected'; ?>>Cocos (Keeling) Islands</option>
	<option value="CO"<?php if ($row["paypalpro_default_country"] == 'CO') echo ' selected'; ?>>Colombia</option>
	<option value="KM"<?php if ($row["paypalpro_default_country"] == 'KM') echo ' selected'; ?>>Comoros</option>
	<option value="CG"<?php if ($row["paypalpro_default_country"] == 'CG') echo ' selected'; ?>>Congo</option>
	<option value="CD"<?php if ($row["paypalpro_default_country"] == 'CD') echo ' selected'; ?>>Congo, the Democratic Republic of the</option>
	<option value="CK"<?php if ($row["paypalpro_default_country"] == 'CK') echo ' selected'; ?>>Cook Islands</option>
	<option value="CR"<?php if ($row["paypalpro_default_country"] == 'CR') echo ' selected'; ?>>Costa Rica</option>
	<option value="CI"<?php if ($row["paypalpro_default_country"] == 'CI') echo ' selected'; ?>>Côte d'Ivoire</option>
	<option value="HR"<?php if ($row["paypalpro_default_country"] == 'HR') echo ' selected'; ?>>Croatia</option>
	<option value="CU"<?php if ($row["paypalpro_default_country"] == 'CU') echo ' selected'; ?>>Cuba</option>
	<option value="CW"<?php if ($row["paypalpro_default_country"] == 'CW') echo ' selected'; ?>>Curaçao</option>
	<option value="CY"<?php if ($row["paypalpro_default_country"] == 'CY') echo ' selected'; ?>>Cyprus</option>
	<option value="CZ"<?php if ($row["paypalpro_default_country"] == 'CZ') echo ' selected'; ?>>Czech Republic</option>
	<option value="DK"<?php if ($row["paypalpro_default_country"] == 'DK') echo ' selected'; ?>>Denmark</option>
	<option value="DJ"<?php if ($row["paypalpro_default_country"] == 'DJ') echo ' selected'; ?>>Djibouti</option>
	<option value="DM"<?php if ($row["paypalpro_default_country"] == 'DM') echo ' selected'; ?>>Dominica</option>
	<option value="DO"<?php if ($row["paypalpro_default_country"] == 'DO') echo ' selected'; ?>>Dominican Republic</option>
	<option value="EC"<?php if ($row["paypalpro_default_country"] == 'EC') echo ' selected'; ?>>Ecuador</option>
	<option value="EG"<?php if ($row["paypalpro_default_country"] == 'EG') echo ' selected'; ?>>Egypt</option>
	<option value="SV"<?php if ($row["paypalpro_default_country"] == 'SV') echo ' selected'; ?>>El Salvador</option>
	<option value="GQ"<?php if ($row["paypalpro_default_country"] == 'GQ') echo ' selected'; ?>>Equatorial Guinea</option>
	<option value="ER"<?php if ($row["paypalpro_default_country"] == 'ER') echo ' selected'; ?>>Eritrea</option>
	<option value="EE"<?php if ($row["paypalpro_default_country"] == 'EE') echo ' selected'; ?>>Estonia</option>
	<option value="ET"<?php if ($row["paypalpro_default_country"] == 'ET') echo ' selected'; ?>>Ethiopia</option>
	<option value="FK"<?php if ($row["paypalpro_default_country"] == 'FK') echo ' selected'; ?>>Falkland Islands (Malvinas)</option>
	<option value="FO"<?php if ($row["paypalpro_default_country"] == 'FO') echo ' selected'; ?>>Faroe Islands</option>
	<option value="FJ"<?php if ($row["paypalpro_default_country"] == 'FJ') echo ' selected'; ?>>Fiji</option>
	<option value="FI"<?php if ($row["paypalpro_default_country"] == 'FI') echo ' selected'; ?>>Finland</option>
	<option value="FR"<?php if ($row["paypalpro_default_country"] == 'FR') echo ' selected'; ?>>France</option>
	<option value="GF"<?php if ($row["paypalpro_default_country"] == 'GF') echo ' selected'; ?>>French Guiana</option>
	<option value="PF"<?php if ($row["paypalpro_default_country"] == 'PF') echo ' selected'; ?>>French Polynesia</option>
	<option value="TF"<?php if ($row["paypalpro_default_country"] == 'TF') echo ' selected'; ?>>French Southern Territories</option>
	<option value="GA"<?php if ($row["paypalpro_default_country"] == 'GA') echo ' selected'; ?>>Gabon</option>
	<option value="GM"<?php if ($row["paypalpro_default_country"] == 'GM') echo ' selected'; ?>>Gambia</option>
	<option value="GE"<?php if ($row["paypalpro_default_country"] == 'GE') echo ' selected'; ?>>Georgia</option>
	<option value="DE"<?php if ($row["paypalpro_default_country"] == 'DE') echo ' selected'; ?>>Germany</option>
	<option value="GH"<?php if ($row["paypalpro_default_country"] == 'GH') echo ' selected'; ?>>Ghana</option>
	<option value="GI"<?php if ($row["paypalpro_default_country"] == 'GI') echo ' selected'; ?>>Gibraltar</option>
	<option value="GR"<?php if ($row["paypalpro_default_country"] == 'GR') echo ' selected'; ?>>Greece</option>
	<option value="GL"<?php if ($row["paypalpro_default_country"] == 'GL') echo ' selected'; ?>>Greenland</option>
	<option value="GD"<?php if ($row["paypalpro_default_country"] == 'GD') echo ' selected'; ?>>Grenada</option>
	<option value="GP"<?php if ($row["paypalpro_default_country"] == 'GP') echo ' selected'; ?>>Guadeloupe</option>
	<option value="GU"<?php if ($row["paypalpro_default_country"] == 'GU') echo ' selected'; ?>>Guam</option>
	<option value="GT"<?php if ($row["paypalpro_default_country"] == 'GT') echo ' selected'; ?>>Guatemala</option>
	<option value="GG"<?php if ($row["paypalpro_default_country"] == 'GG') echo ' selected'; ?>>Guernsey</option>
	<option value="GN"<?php if ($row["paypalpro_default_country"] == 'GN') echo ' selected'; ?>>Guinea</option>
	<option value="GW"<?php if ($row["paypalpro_default_country"] == 'GW') echo ' selected'; ?>>Guinea-Bissau</option>
	<option value="GY"<?php if ($row["paypalpro_default_country"] == 'GY') echo ' selected'; ?>>Guyana</option>
	<option value="HT"<?php if ($row["paypalpro_default_country"] == 'HT') echo ' selected'; ?>>Haiti</option>
	<option value="HM"<?php if ($row["paypalpro_default_country"] == 'HM') echo ' selected'; ?>>Heard Island and McDonald Islands</option>
	<option value="VA"<?php if ($row["paypalpro_default_country"] == 'VA') echo ' selected'; ?>>Holy See (Vatican City State)</option>
	<option value="HN"<?php if ($row["paypalpro_default_country"] == 'HN') echo ' selected'; ?>>Honduras</option>
	<option value="HK"<?php if ($row["paypalpro_default_country"] == 'HK') echo ' selected'; ?>>Hong Kong</option>
	<option value="HU"<?php if ($row["paypalpro_default_country"] == 'HU') echo ' selected'; ?>>Hungary</option>
	<option value="IS"<?php if ($row["paypalpro_default_country"] == 'IS') echo ' selected'; ?>>Iceland</option>
	<option value="IN"<?php if ($row["paypalpro_default_country"] == 'IN') echo ' selected'; ?>>India</option>
	<option value="ID"<?php if ($row["paypalpro_default_country"] == 'ID') echo ' selected'; ?>>Indonesia</option>
	<option value="IR"<?php if ($row["paypalpro_default_country"] == 'IR') echo ' selected'; ?>>Iran, Islamic Republic of</option>
	<option value="IQ"<?php if ($row["paypalpro_default_country"] == 'IQ') echo ' selected'; ?>>Iraq</option>
	<option value="IE"<?php if ($row["paypalpro_default_country"] == 'IE') echo ' selected'; ?>>Ireland</option>
	<option value="IM"<?php if ($row["paypalpro_default_country"] == 'IM') echo ' selected'; ?>>Isle of Man</option>
	<option value="IL"<?php if ($row["paypalpro_default_country"] == 'IL') echo ' selected'; ?>>Israel</option>
	<option value="IT"<?php if ($row["paypalpro_default_country"] == 'IT') echo ' selected'; ?>>Italy</option>
	<option value="JM"<?php if ($row["paypalpro_default_country"] == 'JM') echo ' selected'; ?>>Jamaica</option>
	<option value="JP"<?php if ($row["paypalpro_default_country"] == 'JP') echo ' selected'; ?>>Japan</option>
	<option value="JE"<?php if ($row["paypalpro_default_country"] == 'JE') echo ' selected'; ?>>Jersey</option>
	<option value="JO"<?php if ($row["paypalpro_default_country"] == 'JO') echo ' selected'; ?>>Jordan</option>
	<option value="KZ"<?php if ($row["paypalpro_default_country"] == 'KZ') echo ' selected'; ?>>Kazakhstan</option>
	<option value="KE"<?php if ($row["paypalpro_default_country"] == 'KE') echo ' selected'; ?>>Kenya</option>
	<option value="KI"<?php if ($row["paypalpro_default_country"] == 'KI') echo ' selected'; ?>>Kiribati</option>
	<option value="KP"<?php if ($row["paypalpro_default_country"] == 'KP') echo ' selected'; ?>>Korea, Democratic People's Republic of</option>
	<option value="KR"<?php if ($row["paypalpro_default_country"] == 'KR') echo ' selected'; ?>>Korea, Republic of</option>
	<option value="KW"<?php if ($row["paypalpro_default_country"] == 'KW') echo ' selected'; ?>>Kuwait</option>
	<option value="KG"<?php if ($row["paypalpro_default_country"] == 'KG') echo ' selected'; ?>>Kyrgyzstan</option>
	<option value="LA"<?php if ($row["paypalpro_default_country"] == 'LA') echo ' selected'; ?>>Lao People's Democratic Republic</option>
	<option value="LV"<?php if ($row["paypalpro_default_country"] == 'LV') echo ' selected'; ?>>Latvia</option>
	<option value="LB"<?php if ($row["paypalpro_default_country"] == 'LB') echo ' selected'; ?>>Lebanon</option>
	<option value="LS"<?php if ($row["paypalpro_default_country"] == 'LS') echo ' selected'; ?>>Lesotho</option>
	<option value="LR"<?php if ($row["paypalpro_default_country"] == 'LR') echo ' selected'; ?>>Liberia</option>
	<option value="LY"<?php if ($row["paypalpro_default_country"] == 'LY') echo ' selected'; ?>>Libya</option>
	<option value="LI"<?php if ($row["paypalpro_default_country"] == 'LI') echo ' selected'; ?>>Liechtenstein</option>
	<option value="LT"<?php if ($row["paypalpro_default_country"] == 'LT') echo ' selected'; ?>>Lithuania</option>
	<option value="LU"<?php if ($row["paypalpro_default_country"] == 'LU') echo ' selected'; ?>>Luxembourg</option>
	<option value="MO"<?php if ($row["paypalpro_default_country"] == 'MO') echo ' selected'; ?>>Macao</option>
	<option value="MK"<?php if ($row["paypalpro_default_country"] == 'MK') echo ' selected'; ?>>Macedonia, the former Yugoslav Republic of</option>
	<option value="MG"<?php if ($row["paypalpro_default_country"] == 'MG') echo ' selected'; ?>>Madagascar</option>
	<option value="MW"<?php if ($row["paypalpro_default_country"] == 'MW') echo ' selected'; ?>>Malawi</option>
	<option value="MY"<?php if ($row["paypalpro_default_country"] == 'MY') echo ' selected'; ?>>Malaysia</option>
	<option value="MV"<?php if ($row["paypalpro_default_country"] == 'MV') echo ' selected'; ?>>Maldives</option>
	<option value="ML"<?php if ($row["paypalpro_default_country"] == 'ML') echo ' selected'; ?>>Mali</option>
	<option value="MT"<?php if ($row["paypalpro_default_country"] == 'MT') echo ' selected'; ?>>Malta</option>
	<option value="MH"<?php if ($row["paypalpro_default_country"] == 'MH') echo ' selected'; ?>>Marshall Islands</option>
	<option value="MQ"<?php if ($row["paypalpro_default_country"] == 'MQ') echo ' selected'; ?>>Martinique</option>
	<option value="MR"<?php if ($row["paypalpro_default_country"] == 'MR') echo ' selected'; ?>>Mauritania</option>
	<option value="MU"<?php if ($row["paypalpro_default_country"] == 'MU') echo ' selected'; ?>>Mauritius</option>
	<option value="YT"<?php if ($row["paypalpro_default_country"] == 'YT') echo ' selected'; ?>>Mayotte</option>
	<option value="MX"<?php if ($row["paypalpro_default_country"] == 'MX') echo ' selected'; ?>>Mexico</option>
	<option value="FM"<?php if ($row["paypalpro_default_country"] == 'FM') echo ' selected'; ?>>Micronesia, Federated States of</option>
	<option value="MD"<?php if ($row["paypalpro_default_country"] == 'MD') echo ' selected'; ?>>Moldova, Republic of</option>
	<option value="MC"<?php if ($row["paypalpro_default_country"] == 'MC') echo ' selected'; ?>>Monaco</option>
	<option value="MN"<?php if ($row["paypalpro_default_country"] == 'MN') echo ' selected'; ?>>Mongolia</option>
	<option value="ME"<?php if ($row["paypalpro_default_country"] == 'ME') echo ' selected'; ?>>Montenegro</option>
	<option value="MS"<?php if ($row["paypalpro_default_country"] == 'MS') echo ' selected'; ?>>Montserrat</option>
	<option value="MA"<?php if ($row["paypalpro_default_country"] == 'MA') echo ' selected'; ?>>Morocco</option>
	<option value="MZ"<?php if ($row["paypalpro_default_country"] == 'MZ') echo ' selected'; ?>>Mozambique</option>
	<option value="MM"<?php if ($row["paypalpro_default_country"] == 'MM') echo ' selected'; ?>>Myanmar</option>
	<option value="NA"<?php if ($row["paypalpro_default_country"] == 'NA') echo ' selected'; ?>>Namibia</option>
	<option value="NR"<?php if ($row["paypalpro_default_country"] == 'NR') echo ' selected'; ?>>Nauru</option>
	<option value="NP"<?php if ($row["paypalpro_default_country"] == 'NP') echo ' selected'; ?>>Nepal</option>
	<option value="NL"<?php if ($row["paypalpro_default_country"] == 'NL') echo ' selected'; ?>>Netherlands</option>
	<option value="NC"<?php if ($row["paypalpro_default_country"] == 'NC') echo ' selected'; ?>>New Caledonia</option>
	<option value="NZ"<?php if ($row["paypalpro_default_country"] == 'NZ') echo ' selected'; ?>>New Zealand</option>
	<option value="NI"<?php if ($row["paypalpro_default_country"] == 'NI') echo ' selected'; ?>>Nicaragua</option>
	<option value="NE"<?php if ($row["paypalpro_default_country"] == 'NE') echo ' selected'; ?>>Niger</option>
	<option value="NG"<?php if ($row["paypalpro_default_country"] == 'NG') echo ' selected'; ?>>Nigeria</option>
	<option value="NU"<?php if ($row["paypalpro_default_country"] == 'NU') echo ' selected'; ?>>Niue</option>
	<option value="NF"<?php if ($row["paypalpro_default_country"] == 'NF') echo ' selected'; ?>>Norfolk Island</option>
	<option value="MP"<?php if ($row["paypalpro_default_country"] == 'MP') echo ' selected'; ?>>Northern Mariana Islands</option>
	<option value="NO"<?php if ($row["paypalpro_default_country"] == 'NO') echo ' selected'; ?>>Norway</option>
	<option value="OM"<?php if ($row["paypalpro_default_country"] == 'OM') echo ' selected'; ?>>Oman</option>
	<option value="PK"<?php if ($row["paypalpro_default_country"] == 'PK') echo ' selected'; ?>>Pakistan</option>
	<option value="PW"<?php if ($row["paypalpro_default_country"] == 'PW') echo ' selected'; ?>>Palau</option>
	<option value="PS"<?php if ($row["paypalpro_default_country"] == 'PS') echo ' selected'; ?>>Palestinian Territory, Occupied</option>
	<option value="PA"<?php if ($row["paypalpro_default_country"] == 'PA') echo ' selected'; ?>>Panama</option>
	<option value="PG"<?php if ($row["paypalpro_default_country"] == 'PG') echo ' selected'; ?>>Papua New Guinea</option>
	<option value="PY"<?php if ($row["paypalpro_default_country"] == 'PY') echo ' selected'; ?>>Paraguay</option>
	<option value="PE"<?php if ($row["paypalpro_default_country"] == 'PE') echo ' selected'; ?>>Peru</option>
	<option value="PH"<?php if ($row["paypalpro_default_country"] == 'PH') echo ' selected'; ?>>Philippines</option>
	<option value="PN"<?php if ($row["paypalpro_default_country"] == 'PN') echo ' selected'; ?>>Pitcairn</option>
	<option value="PL"<?php if ($row["paypalpro_default_country"] == 'PL') echo ' selected'; ?>>Poland</option>
	<option value="PT"<?php if ($row["paypalpro_default_country"] == 'PT') echo ' selected'; ?>>Portugal</option>
	<option value="PR"<?php if ($row["paypalpro_default_country"] == 'PR') echo ' selected'; ?>>Puerto Rico</option>
	<option value="QA"<?php if ($row["paypalpro_default_country"] == 'QA') echo ' selected'; ?>>Qatar</option>
	<option value="RE"<?php if ($row["paypalpro_default_country"] == 'RE') echo ' selected'; ?>>Réunion</option>
	<option value="RO"<?php if ($row["paypalpro_default_country"] == 'RO') echo ' selected'; ?>>Romania</option>
	<option value="RU"<?php if ($row["paypalpro_default_country"] == 'RU') echo ' selected'; ?>>Russian Federation</option>
	<option value="RW"<?php if ($row["paypalpro_default_country"] == 'RW') echo ' selected'; ?>>Rwanda</option>
	<option value="BL"<?php if ($row["paypalpro_default_country"] == 'BL') echo ' selected'; ?>>Saint Barthélemy</option>
	<option value="SH"<?php if ($row["paypalpro_default_country"] == 'SH') echo ' selected'; ?>>Saint Helena, Ascension and Tristan da Cunha</option>
	<option value="KN"<?php if ($row["paypalpro_default_country"] == 'KN') echo ' selected'; ?>>Saint Kitts and Nevis</option>
	<option value="LC"<?php if ($row["paypalpro_default_country"] == 'LC') echo ' selected'; ?>>Saint Lucia</option>
	<option value="MF"<?php if ($row["paypalpro_default_country"] == 'MF') echo ' selected'; ?>>Saint Martin (French part)</option>
	<option value="PM"<?php if ($row["paypalpro_default_country"] == 'PM') echo ' selected'; ?>>Saint Pierre and Miquelon</option>
	<option value="VC"<?php if ($row["paypalpro_default_country"] == 'VC') echo ' selected'; ?>>Saint Vincent and the Grenadines</option>
	<option value="WS"<?php if ($row["paypalpro_default_country"] == 'WS') echo ' selected'; ?>>Samoa</option>
	<option value="SM"<?php if ($row["paypalpro_default_country"] == 'SM') echo ' selected'; ?>>San Marino</option>
	<option value="ST"<?php if ($row["paypalpro_default_country"] == 'ST') echo ' selected'; ?>>Sao Tome and Principe</option>
	<option value="SA"<?php if ($row["paypalpro_default_country"] == 'SA') echo ' selected'; ?>>Saudi Arabia</option>
	<option value="SN"<?php if ($row["paypalpro_default_country"] == 'SN') echo ' selected'; ?>>Senegal</option>
	<option value="RS"<?php if ($row["paypalpro_default_country"] == 'RS') echo ' selected'; ?>>Serbia</option>
	<option value="SC"<?php if ($row["paypalpro_default_country"] == 'SC') echo ' selected'; ?>>Seychelles</option>
	<option value="SL"<?php if ($row["paypalpro_default_country"] == 'SL') echo ' selected'; ?>>Sierra Leone</option>
	<option value="SG"<?php if ($row["paypalpro_default_country"] == 'SG') echo ' selected'; ?>>Singapore</option>
	<option value="SX"<?php if ($row["paypalpro_default_country"] == 'SX') echo ' selected'; ?>>Sint Maarten (Dutch part)</option>
	<option value="SK"<?php if ($row["paypalpro_default_country"] == 'SK') echo ' selected'; ?>>Slovakia</option>
	<option value="SI"<?php if ($row["paypalpro_default_country"] == 'SI') echo ' selected'; ?>>Slovenia</option>
	<option value="SB"<?php if ($row["paypalpro_default_country"] == 'SB') echo ' selected'; ?>>Solomon Islands</option>
	<option value="SO"<?php if ($row["paypalpro_default_country"] == 'SO') echo ' selected'; ?>>Somalia</option>
	<option value="ZA"<?php if ($row["paypalpro_default_country"] == 'ZA') echo ' selected'; ?>>South Africa</option>
	<option value="GS"<?php if ($row["paypalpro_default_country"] == 'GS') echo ' selected'; ?>>South Georgia and the South Sandwich Islands</option>
	<option value="SS"<?php if ($row["paypalpro_default_country"] == 'SS') echo ' selected'; ?>>South Sudan</option>
	<option value="ES"<?php if ($row["paypalpro_default_country"] == 'ES') echo ' selected'; ?>>Spain</option>
	<option value="LK"<?php if ($row["paypalpro_default_country"] == 'LK') echo ' selected'; ?>>Sri Lanka</option>
	<option value="SD"<?php if ($row["paypalpro_default_country"] == 'SD') echo ' selected'; ?>>Sudan</option>
	<option value="SR"<?php if ($row["paypalpro_default_country"] == 'SR') echo ' selected'; ?>>Suriname</option>
	<option value="SJ"<?php if ($row["paypalpro_default_country"] == 'SJ') echo ' selected'; ?>>Svalbard and Jan Mayen</option>
	<option value="SZ"<?php if ($row["paypalpro_default_country"] == 'SZ') echo ' selected'; ?>>Swaziland</option>
	<option value="SE"<?php if ($row["paypalpro_default_country"] == 'SE') echo ' selected'; ?>>Sweden</option>
	<option value="CH"<?php if ($row["paypalpro_default_country"] == 'CH') echo ' selected'; ?>>Switzerland</option>
	<option value="SY"<?php if ($row["paypalpro_default_country"] == 'SY') echo ' selected'; ?>>Syrian Arab Republic</option>
	<option value="TW"<?php if ($row["paypalpro_default_country"] == 'TW') echo ' selected'; ?>>Taiwan, Province of China</option>
	<option value="TJ"<?php if ($row["paypalpro_default_country"] == 'TJ') echo ' selected'; ?>>Tajikistan</option>
	<option value="TZ"<?php if ($row["paypalpro_default_country"] == 'TZ') echo ' selected'; ?>>Tanzania, United Republic of</option>
	<option value="TH"<?php if ($row["paypalpro_default_country"] == 'TH') echo ' selected'; ?>>Thailand</option>
	<option value="TL"<?php if ($row["paypalpro_default_country"] == 'TL') echo ' selected'; ?>>Timor-Leste</option>
	<option value="TG"<?php if ($row["paypalpro_default_country"] == 'TG') echo ' selected'; ?>>Togo</option>
	<option value="TK"<?php if ($row["paypalpro_default_country"] == 'TK') echo ' selected'; ?>>Tokelau</option>
	<option value="TO"<?php if ($row["paypalpro_default_country"] == 'TO') echo ' selected'; ?>>Tonga</option>
	<option value="TT"<?php if ($row["paypalpro_default_country"] == 'TT') echo ' selected'; ?>>Trinidad and Tobago</option>
	<option value="TN"<?php if ($row["paypalpro_default_country"] == 'TN') echo ' selected'; ?>>Tunisia</option>
	<option value="TR"<?php if ($row["paypalpro_default_country"] == 'TR') echo ' selected'; ?>>Turkey</option>
	<option value="TM"<?php if ($row["paypalpro_default_country"] == 'TM') echo ' selected'; ?>>Turkmenistan</option>
	<option value="TC"<?php if ($row["paypalpro_default_country"] == 'TC') echo ' selected'; ?>>Turks and Caicos Islands</option>
	<option value="TV"<?php if ($row["paypalpro_default_country"] == 'TV') echo ' selected'; ?>>Tuvalu</option>
	<option value="UG"<?php if ($row["paypalpro_default_country"] == 'UG') echo ' selected'; ?>>Uganda</option>
	<option value="UA"<?php if ($row["paypalpro_default_country"] == 'UA') echo ' selected'; ?>>Ukraine</option>
	<option value="AE"<?php if ($row["paypalpro_default_country"] == 'AE') echo ' selected'; ?>>United Arab Emirates</option>
	<option value="GB"<?php if ($row["paypalpro_default_country"] == 'GB') echo ' selected'; ?>>United Kingdom</option>
	<option value="US"<?php if ($row["paypalpro_default_country"] == 'US') echo ' selected'; ?>>United States</option>
	<option value="UM"<?php if ($row["paypalpro_default_country"] == 'UM') echo ' selected'; ?>>United States Minor Outlying Islands</option>
	<option value="UY"<?php if ($row["paypalpro_default_country"] == 'UY') echo ' selected'; ?>>Uruguay</option>
	<option value="UZ"<?php if ($row["paypalpro_default_country"] == 'UZ') echo ' selected'; ?>>Uzbekistan</option>
	<option value="VU"<?php if ($row["paypalpro_default_country"] == 'VU') echo ' selected'; ?>>Vanuatu</option>
	<option value="VE"<?php if ($row["paypalpro_default_country"] == 'VE') echo ' selected'; ?>>Venezuela, Bolivarian Republic of</option>
	<option value="VN"<?php if ($row["paypalpro_default_country"] == 'VN') echo ' selected'; ?>>Viet Nam</option>
	<option value="VG"<?php if ($row["paypalpro_default_country"] == 'VG') echo ' selected'; ?>>Virgin Islands, British</option>
	<option value="VI"<?php if ($row["paypalpro_default_country"] == 'VI') echo ' selected'; ?>>Virgin Islands, U.S.</option>
	<option value="WF"<?php if ($row["paypalpro_default_country"] == 'WF') echo ' selected'; ?>>Wallis and Futuna</option>
	<option value="EH"<?php if ($row["paypalpro_default_country"] == 'EH') echo ' selected'; ?>>Western Sahara</option>
	<option value="YE"<?php if ($row["paypalpro_default_country"] == 'YE') echo ' selected'; ?>>Yemen</option>
	<option value="ZM"<?php if ($row["paypalpro_default_country"] == 'ZM') echo ' selected'; ?>>Zambia</option>
	<option value="ZW"<?php if ($row["paypalpro_default_country"] == 'ZW') echo ' selected'; ?>>Zimbabwe</option>
                        </select>
                    </td>
                    </tr>
                   </table>
                   <div class="cff-goto-top"><a href="#cpformconf"><?php _e('Up to form structure', 'calculated-fields-form'); ?></a></div>
				</div>
			</div>
			<?php
		} // end get_addon_form_settings



		/************************ ADDON CODE *****************************/

        /************************ ATTRIBUTES *****************************/

        private $form_table = 'cp_calculated_fields_form_paypalpro';
        private $_inserted = false;
		private $_cpcff_main;

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->_cpcff_main = CPCFF_MAIN::instance();
			$this->description = __("The add-on adds support for PayPal Payment Pro payments to accept credit cars directly into the website", 'calculated-fields-form' );
            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;

			add_action( 'cpcff_process_data_before_insert', array( &$this, 'pp_payments_pro' ), 10, 3 );

			add_action( 'cpcff_process_data', array( &$this, 'pp_payments_pro_update_status' ), 10, 1 );

			add_action( 'cpcff_script_after_validation', array( &$this, 'pp_payments_script' ), 10, 2 );

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
					paypalpro_api_username varchar(255) DEFAULT '' NOT NULL ,
					paypalpro_api_password varchar(255) DEFAULT '' NOT NULL ,
					paypalpro_api_signature varchar(255) DEFAULT '' NOT NULL ,
                    paypalpro_api_bperiod varchar(255) DEFAULT '' NOT NULL ,
                    paypalpro_default_country varchar(255) DEFAULT '' NOT NULL ,
					paypal_mode varchar(255) DEFAULT '' NOT NULL ,
					currency varchar(255) DEFAULT '' NOT NULL ,
                    enable_option_yes varchar(255) DEFAULT '' NOT NULL ,
					UNIQUE KEY id (id)
				) $charset_collate;"
			);
			$this->_run_update_database($db_queries);
		} // end update_database


		/**
         * connection to process payment
         */
		private function pp_payments_pro_POST($methodName_, $nvpStr_, $params)
		{

	        // Set up your API credentials, PayPal end point, and API version.
	        $API_UserName = urlencode($params->paypalpro_api_username);
	        $API_Password = urlencode($params->paypalpro_api_password);
	        $API_Signature = urlencode($params->paypalpro_api_signature);

            if ($params->paypal_mode == "sandbox")
                $API_Endpoint = "https://api-3t.sandbox.paypal.com/nvp";
            else
                $API_Endpoint = "https://api-3t.paypal.com/nvp";
	        $version = urlencode('51.0');

	        // Set the curl parameters.
	        $ch = curl_init();
	        curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
	        curl_setopt($ch, CURLOPT_VERBOSE, 1);

	        // Turn off the server and peer verification (TrustManager Concept).
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($ch, CURLOPT_POST, 1);

	        // Set the API operation, version, and API signature in the request.
	        $nvpreq = "METHOD=$methodName_&VERSION=$version&PWD=$API_Password&USER=$API_UserName&SIGNATURE=$API_Signature$nvpStr_";

	        // Set the request as a POST FIELD for curl.
	        curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);

	        // Get response from the server.
	        $httpResponse = curl_exec($ch);

	        if(!$httpResponse) {
	        	exit("$methodName_ failed: ".curl_error($ch).'('.curl_errno($ch).')');
	        }

	        // Extract the response details.
	        $httpResponseAr = explode("&", $httpResponse);

	        $httpParsedResponseAr = array();
	        foreach ($httpResponseAr as $i => $value) {
	        	$tmpAr = explode("=", $value);
	        	if(sizeof($tmpAr) > 1) {
	        		$httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
	        	}
	        }

	        if((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr)) {
                exit("Invalid HTTP Response for POST request(-available on debug mode-) to $API_Endpoint.");
	        	// uncomment this for debug purposes only: exit("Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.");
	        }

	        return $httpParsedResponseAr;
        } // end pp_payments_pro_POST


		/**
         * public payment fields
         */
        private function get_payment_fields ($id, $row)
        {
            wp_enqueue_style('pppro-style', plugins_url('/paypalpro.addon/paypalpro.css', __FILE__) );
            ob_start();
            $checkscript = '<script type="text/javascript">function cffaddonpppro'.$id.'(){ try { if(document.getElementById("cffaddonidpaypro'.$id.'").checked) document.getElementById("opfield'.$this->addonID.$id.'").style.display=""; else document.getElementById("opfield'.$this->addonID.$id.'").style.display="none"; } catch (e) {} }setInterval("cffaddonpppro'.$id.'()",200);</script>';
            echo $checkscript;
            if (empty($row->paypalpro_default_country))
                $paypalpro_default_country = 'GB';
            else
                $paypalpro_default_country = $row->paypalpro_default_country;
?>
<div id="opfield<?php echo $this->addonID.$id; ?>">
<input type="hidden" id="cp_contact_form_paypal_paymentspro<?php echo $id; ?>" name="cp_contact_form_paypal_paymentspro<?php echo $id; ?>" value="1" />
<div id="pprol" style="width:100%;">

  <div class="fields column2">
    <label><?php echo __('First Name:','calculated-fields-form'); ?></label>
    <div class="dfield" id="field-c0-ppp">
      <input type="text" size="15" name="cfpp_customer_first_name" id="cfpp_customer_first_name" value="" />
    </div>
	<div class="clearer"></div>
  </div>
  <div class="fields column2">
    <label><?php echo __('Last Name:','calculated-fields-form'); ?></label>
    <div class="dfield" id="field-c1-ppp">
      <input type="text" size="15" name="cfpp_customer_last_name" id="cfpp_customer_last_name" value="" />
    </div>
	<div class="clearer"></div>
  </div>
  <div class="fields">
    <label><?php echo __('Address:','calculated-fields-form'); ?></label>
    <div class="dfield" id="field-c2-ppp">
      <input type="text" size="30" name="cfpp_customer_address1" id="cfpp_customer_address1" value="" />
      <input type="text" size="30" name="cfpp_customer_address2" id="cfpp_customer_address2" value="" />
    </div>
	<div class="clearer"></div>
  </div>
  <div class="fields column3">
    <label><?php echo __('City:','calculated-fields-form'); ?></label>
    <div class="dfield" id="field-c3-ppp">
      <input type="text" size="15" name="cfpp_customer_city" id="cfpp_customer_city" value="" />
    </div>
	<div class="clearer"></div>
  </div>
  <div class="fields column3">
    <label><?php echo __('State:','calculated-fields-form'); ?></label>
    <div class="dfield" id="field-c4-ppp">
      <input type="text" size="15" name="cfpp_customer_state" id="cfpp_customer_state" value="" />
    </div>
	<div class="clearer"></div>
  </div>
  <div class="fields column3">
    <label><?php echo __('Zip Code:','calculated-fields-form'); ?></label>
    <div class="dfield" id="field-c5-ppp">
      <input type="text" size="5" name="cfpp_customer_zip" id="cfpp_customer_zip" value="" />
    </div>
	<div class="clearer"></div>
  </div>
  <div class="fields">
    <label><?php echo __('Country:','calculated-fields-form'); ?></label>
    <div class="dfield" id="field-c6-ppp">
<select name="cfpp_customer_country" id="cfpp_customer_country">
	<option value="AF"<?php if ($paypalpro_default_country == 'AF') echo ' selected'; ?>><?php _e('Afghanistan', 'calculated-fields-form'); ?></option>
	<option value="AX"<?php if ($paypalpro_default_country == 'AX') echo ' selected'; ?>><?php _e('Åland Islands', 'calculated-fields-form'); ?></option>
	<option value="AL"<?php if ($paypalpro_default_country == 'AL') echo ' selected'; ?>><?php _e('Albania', 'calculated-fields-form'); ?></option>
	<option value="DZ"<?php if ($paypalpro_default_country == 'DZ') echo ' selected'; ?>><?php _e('Algeria', 'calculated-fields-form'); ?></option>
	<option value="AS"<?php if ($paypalpro_default_country == 'AS') echo ' selected'; ?>><?php _e('American Samoa', 'calculated-fields-form'); ?></option>
	<option value="AD"<?php if ($paypalpro_default_country == 'AD') echo ' selected'; ?>><?php _e('Andorra', 'calculated-fields-form'); ?></option>
	<option value="AO"<?php if ($paypalpro_default_country == 'AO') echo ' selected'; ?>><?php _e('Angola', 'calculated-fields-form'); ?></option>
	<option value="AI"<?php if ($paypalpro_default_country == 'AI') echo ' selected'; ?>><?php _e('Anguilla', 'calculated-fields-form'); ?></option>
	<option value="AQ"<?php if ($paypalpro_default_country == 'AQ') echo ' selected'; ?>><?php _e('Antarctica', 'calculated-fields-form'); ?></option>
	<option value="AG"<?php if ($paypalpro_default_country == 'AG') echo ' selected'; ?>><?php _e('Antigua and Barbuda', 'calculated-fields-form'); ?></option>
	<option value="AR"<?php if ($paypalpro_default_country == 'AR') echo ' selected'; ?>><?php _e('Argentina', 'calculated-fields-form'); ?></option>
	<option value="AM"<?php if ($paypalpro_default_country == 'AM') echo ' selected'; ?>><?php _e('Armenia', 'calculated-fields-form'); ?></option>
	<option value="AW"<?php if ($paypalpro_default_country == 'AW') echo ' selected'; ?>><?php _e('Aruba', 'calculated-fields-form'); ?></option>
	<option value="AU"<?php if ($paypalpro_default_country == 'AU') echo ' selected'; ?>><?php _e('Australia', 'calculated-fields-form'); ?></option>
	<option value="AT"<?php if ($paypalpro_default_country == 'AT') echo ' selected'; ?>><?php _e('Austria', 'calculated-fields-form'); ?></option>
	<option value="AZ"<?php if ($paypalpro_default_country == 'AZ') echo ' selected'; ?>><?php _e('Azerbaijan', 'calculated-fields-form'); ?></option>
	<option value="BS"<?php if ($paypalpro_default_country == 'BS') echo ' selected'; ?>><?php _e('Bahamas', 'calculated-fields-form'); ?></option>
	<option value="BH"<?php if ($paypalpro_default_country == 'BH') echo ' selected'; ?>><?php _e('Bahrain', 'calculated-fields-form'); ?></option>
	<option value="BD"<?php if ($paypalpro_default_country == 'BD') echo ' selected'; ?>><?php _e('Bangladesh', 'calculated-fields-form'); ?></option>
	<option value="BB"<?php if ($paypalpro_default_country == 'BB') echo ' selected'; ?>><?php _e('Barbados', 'calculated-fields-form'); ?></option>
	<option value="BY"<?php if ($paypalpro_default_country == 'BY') echo ' selected'; ?>><?php _e('Belarus', 'calculated-fields-form'); ?></option>
	<option value="BE"<?php if ($paypalpro_default_country == 'BE') echo ' selected'; ?>><?php _e('Belgium', 'calculated-fields-form'); ?></option>
	<option value="BZ"<?php if ($paypalpro_default_country == 'BZ') echo ' selected'; ?>><?php _e('Belize', 'calculated-fields-form'); ?></option>
	<option value="BJ"<?php if ($paypalpro_default_country == 'BJ') echo ' selected'; ?>><?php _e('Benin', 'calculated-fields-form'); ?></option>
	<option value="BM"<?php if ($paypalpro_default_country == 'BM') echo ' selected'; ?>><?php _e('Bermuda', 'calculated-fields-form'); ?></option>
	<option value="BT"<?php if ($paypalpro_default_country == 'BT') echo ' selected'; ?>><?php _e('Bhutan', 'calculated-fields-form'); ?></option>
	<option value="BO"<?php if ($paypalpro_default_country == 'BO') echo ' selected'; ?>><?php _e('Bolivia, Plurinational State of', 'calculated-fields-form'); ?></option>
	<option value="BQ"<?php if ($paypalpro_default_country == 'BQ') echo ' selected'; ?>><?php _e('Bonaire, Sint Eustatius and Saba', 'calculated-fields-form'); ?></option>
	<option value="BA"<?php if ($paypalpro_default_country == 'BA') echo ' selected'; ?>><?php _e('Bosnia and Herzegovina', 'calculated-fields-form'); ?></option>
	<option value="BW"<?php if ($paypalpro_default_country == 'BW') echo ' selected'; ?>><?php _e('Botswana', 'calculated-fields-form'); ?></option>
	<option value="BV"<?php if ($paypalpro_default_country == 'BV') echo ' selected'; ?>><?php _e('Bouvet Island', 'calculated-fields-form'); ?></option>
	<option value="BR"<?php if ($paypalpro_default_country == 'BR') echo ' selected'; ?>><?php _e('Brazil', 'calculated-fields-form'); ?></option>
	<option value="IO"<?php if ($paypalpro_default_country == 'IO') echo ' selected'; ?>><?php _e('British Indian Ocean Territory', 'calculated-fields-form'); ?></option>
	<option value="BN"<?php if ($paypalpro_default_country == 'BN') echo ' selected'; ?>><?php _e('Brunei Darussalam', 'calculated-fields-form'); ?></option>
	<option value="BG"<?php if ($paypalpro_default_country == 'BG') echo ' selected'; ?>><?php _e('Bulgaria', 'calculated-fields-form'); ?></option>
	<option value="BF"<?php if ($paypalpro_default_country == 'BF') echo ' selected'; ?>><?php _e('Burkina Faso', 'calculated-fields-form'); ?></option>
	<option value="BI"<?php if ($paypalpro_default_country == 'BI') echo ' selected'; ?>><?php _e('Burundi', 'calculated-fields-form'); ?></option>
	<option value="KH"<?php if ($paypalpro_default_country == 'KH') echo ' selected'; ?>><?php _e('Cambodia', 'calculated-fields-form'); ?></option>
	<option value="CM"<?php if ($paypalpro_default_country == 'CM') echo ' selected'; ?>><?php _e('Cameroon', 'calculated-fields-form'); ?></option>
	<option value="CA"<?php if ($paypalpro_default_country == 'CA') echo ' selected'; ?>><?php _e('Canada', 'calculated-fields-form'); ?></option>
	<option value="CV"<?php if ($paypalpro_default_country == 'CV') echo ' selected'; ?>><?php _e('Cape Verde', 'calculated-fields-form'); ?></option>
	<option value="KY"<?php if ($paypalpro_default_country == 'KY') echo ' selected'; ?>><?php _e('Cayman Islands', 'calculated-fields-form'); ?></option>
	<option value="CF"<?php if ($paypalpro_default_country == 'CF') echo ' selected'; ?>><?php _e('Central African Republic', 'calculated-fields-form'); ?></option>
	<option value="TD"<?php if ($paypalpro_default_country == 'TD') echo ' selected'; ?>><?php _e('Chad', 'calculated-fields-form'); ?></option>
	<option value="CL"<?php if ($paypalpro_default_country == 'CL') echo ' selected'; ?>><?php _e('Chile', 'calculated-fields-form'); ?></option>
	<option value="CN"<?php if ($paypalpro_default_country == 'CN') echo ' selected'; ?>><?php _e('China', 'calculated-fields-form'); ?></option>
	<option value="CX"<?php if ($paypalpro_default_country == 'CX') echo ' selected'; ?>><?php _e('Christmas Island', 'calculated-fields-form'); ?></option>
	<option value="CC"<?php if ($paypalpro_default_country == 'CC') echo ' selected'; ?>><?php _e('Cocos (Keeling) Islands', 'calculated-fields-form'); ?></option>
	<option value="CO"<?php if ($paypalpro_default_country == 'CO') echo ' selected'; ?>><?php _e('Colombia', 'calculated-fields-form'); ?></option>
	<option value="KM"<?php if ($paypalpro_default_country == 'KM') echo ' selected'; ?>><?php _e('Comoros', 'calculated-fields-form'); ?></option>
	<option value="CG"<?php if ($paypalpro_default_country == 'CG') echo ' selected'; ?>><?php _e('Congo', 'calculated-fields-form'); ?></option>
	<option value="CD"<?php if ($paypalpro_default_country == 'CD') echo ' selected'; ?>><?php _e('Congo, the Democratic Republic of the', 'calculated-fields-form'); ?></option>
	<option value="CK"<?php if ($paypalpro_default_country == 'CK') echo ' selected'; ?>><?php _e('Cook Islands', 'calculated-fields-form'); ?></option>
	<option value="CR"<?php if ($paypalpro_default_country == 'CR') echo ' selected'; ?>><?php _e('Costa Rica', 'calculated-fields-form'); ?></option>
	<option value="CI"<?php if ($paypalpro_default_country == 'CI') echo ' selected'; ?>><?php _e('Côte d\'Ivoire', 'calculated-fields-form'); ?></option>
	<option value="HR"<?php if ($paypalpro_default_country == 'HR') echo ' selected'; ?>><?php _e('Croatia', 'calculated-fields-form'); ?></option>
	<option value="CU"<?php if ($paypalpro_default_country == 'CU') echo ' selected'; ?>><?php _e('Cuba', 'calculated-fields-form'); ?></option>
	<option value="CW"<?php if ($paypalpro_default_country == 'CW') echo ' selected'; ?>><?php _e('Curaçao', 'calculated-fields-form'); ?></option>
	<option value="CY"<?php if ($paypalpro_default_country == 'CY') echo ' selected'; ?>><?php _e('Cyprus', 'calculated-fields-form'); ?></option>
	<option value="CZ"<?php if ($paypalpro_default_country == 'CZ') echo ' selected'; ?>><?php _e('Czech Republic', 'calculated-fields-form'); ?></option>
	<option value="DK"<?php if ($paypalpro_default_country == 'DK') echo ' selected'; ?>><?php _e('Denmark', 'calculated-fields-form'); ?></option>
	<option value="DJ"<?php if ($paypalpro_default_country == 'DJ') echo ' selected'; ?>><?php _e('Djibouti', 'calculated-fields-form'); ?></option>
	<option value="DM"<?php if ($paypalpro_default_country == 'DM') echo ' selected'; ?>><?php _e('Dominica', 'calculated-fields-form'); ?></option>
	<option value="DO"<?php if ($paypalpro_default_country == 'DO') echo ' selected'; ?>><?php _e('Dominican Republic', 'calculated-fields-form'); ?></option>
	<option value="EC"<?php if ($paypalpro_default_country == 'EC') echo ' selected'; ?>><?php _e('Ecuador', 'calculated-fields-form'); ?></option>
	<option value="EG"<?php if ($paypalpro_default_country == 'EG') echo ' selected'; ?>><?php _e('Egypt', 'calculated-fields-form'); ?></option>
	<option value="SV"<?php if ($paypalpro_default_country == 'SV') echo ' selected'; ?>><?php _e('El Salvador', 'calculated-fields-form'); ?></option>
	<option value="GQ"<?php if ($paypalpro_default_country == 'GQ') echo ' selected'; ?>><?php _e('Equatorial Guinea', 'calculated-fields-form'); ?></option>
	<option value="ER"<?php if ($paypalpro_default_country == 'ER') echo ' selected'; ?>><?php _e('Eritrea', 'calculated-fields-form'); ?></option>
	<option value="EE"<?php if ($paypalpro_default_country == 'EE') echo ' selected'; ?>><?php _e('Estonia', 'calculated-fields-form'); ?></option>
	<option value="ET"<?php if ($paypalpro_default_country == 'ET') echo ' selected'; ?>><?php _e('Ethiopia', 'calculated-fields-form'); ?></option>
	<option value="FK"<?php if ($paypalpro_default_country == 'FK') echo ' selected'; ?>><?php _e('Falkland Islands (Malvinas)', 'calculated-fields-form'); ?></option>
	<option value="FO"<?php if ($paypalpro_default_country == 'FO') echo ' selected'; ?>><?php _e('Faroe Islands', 'calculated-fields-form'); ?></option>
	<option value="FJ"<?php if ($paypalpro_default_country == 'FJ') echo ' selected'; ?>><?php _e('Fiji', 'calculated-fields-form'); ?></option>
	<option value="FI"<?php if ($paypalpro_default_country == 'FI') echo ' selected'; ?>><?php _e('Finland', 'calculated-fields-form'); ?></option>
	<option value="FR"<?php if ($paypalpro_default_country == 'FR') echo ' selected'; ?>><?php _e('France', 'calculated-fields-form'); ?></option>
	<option value="GF"<?php if ($paypalpro_default_country == 'GF') echo ' selected'; ?>><?php _e('French Guiana', 'calculated-fields-form'); ?></option>
	<option value="PF"<?php if ($paypalpro_default_country == 'PF') echo ' selected'; ?>><?php _e('French Polynesia', 'calculated-fields-form'); ?></option>
	<option value="TF"<?php if ($paypalpro_default_country == 'TF') echo ' selected'; ?>><?php _e('French Southern Territories', 'calculated-fields-form'); ?></option>
	<option value="GA"<?php if ($paypalpro_default_country == 'GA') echo ' selected'; ?>><?php _e('Gabon', 'calculated-fields-form'); ?></option>
	<option value="GM"<?php if ($paypalpro_default_country == 'GM') echo ' selected'; ?>><?php _e('Gambia', 'calculated-fields-form'); ?></option>
	<option value="GE"<?php if ($paypalpro_default_country == 'GE') echo ' selected'; ?>><?php _e('Georgia', 'calculated-fields-form'); ?></option>
	<option value="DE"<?php if ($paypalpro_default_country == 'DE') echo ' selected'; ?>><?php _e('Germany', 'calculated-fields-form'); ?></option>
	<option value="GH"<?php if ($paypalpro_default_country == 'GH') echo ' selected'; ?>><?php _e('Ghana', 'calculated-fields-form'); ?></option>
	<option value="GI"<?php if ($paypalpro_default_country == 'GI') echo ' selected'; ?>><?php _e('Gibraltar', 'calculated-fields-form'); ?></option>
	<option value="GR"<?php if ($paypalpro_default_country == 'GR') echo ' selected'; ?>><?php _e('Greece', 'calculated-fields-form'); ?></option>
	<option value="GL"<?php if ($paypalpro_default_country == 'GL') echo ' selected'; ?>><?php _e('Greenland', 'calculated-fields-form'); ?></option>
	<option value="GD"<?php if ($paypalpro_default_country == 'GD') echo ' selected'; ?>><?php _e('Grenada', 'calculated-fields-form'); ?></option>
	<option value="GP"<?php if ($paypalpro_default_country == 'GP') echo ' selected'; ?>><?php _e('Guadeloupe', 'calculated-fields-form'); ?></option>
	<option value="GU"<?php if ($paypalpro_default_country == 'GU') echo ' selected'; ?>><?php _e('Guam', 'calculated-fields-form'); ?></option>
	<option value="GT"<?php if ($paypalpro_default_country == 'GT') echo ' selected'; ?>><?php _e('Guatemala', 'calculated-fields-form'); ?></option>
	<option value="GG"<?php if ($paypalpro_default_country == 'GG') echo ' selected'; ?>><?php _e('Guernsey', 'calculated-fields-form'); ?></option>
	<option value="GN"<?php if ($paypalpro_default_country == 'GN') echo ' selected'; ?>><?php _e('Guinea', 'calculated-fields-form'); ?></option>
	<option value="GW"<?php if ($paypalpro_default_country == 'GW') echo ' selected'; ?>><?php _e('Guinea-Bissau', 'calculated-fields-form'); ?></option>
	<option value="GY"<?php if ($paypalpro_default_country == 'GY') echo ' selected'; ?>><?php _e('Guyana', 'calculated-fields-form'); ?></option>
	<option value="HT"<?php if ($paypalpro_default_country == 'HT') echo ' selected'; ?>><?php _e('Haiti', 'calculated-fields-form'); ?></option>
	<option value="HM"<?php if ($paypalpro_default_country == 'HM') echo ' selected'; ?>><?php _e('Heard Island and McDonald Islands', 'calculated-fields-form'); ?></option>
	<option value="VA"<?php if ($paypalpro_default_country == 'VA') echo ' selected'; ?>><?php _e('Holy See (Vatican City State)', 'calculated-fields-form'); ?></option>
	<option value="HN"<?php if ($paypalpro_default_country == 'HN') echo ' selected'; ?>><?php _e('Honduras', 'calculated-fields-form'); ?></option>
	<option value="HK"<?php if ($paypalpro_default_country == 'HK') echo ' selected'; ?>><?php _e('Hong Kong', 'calculated-fields-form'); ?></option>
	<option value="HU"<?php if ($paypalpro_default_country == 'HU') echo ' selected'; ?>><?php _e('Hungary', 'calculated-fields-form'); ?></option>
	<option value="IS"<?php if ($paypalpro_default_country == 'IS') echo ' selected'; ?>><?php _e('Iceland', 'calculated-fields-form'); ?></option>
	<option value="IN"<?php if ($paypalpro_default_country == 'IN') echo ' selected'; ?>><?php _e('India', 'calculated-fields-form'); ?></option>
	<option value="ID"<?php if ($paypalpro_default_country == 'ID') echo ' selected'; ?>><?php _e('Indonesia', 'calculated-fields-form'); ?></option>
	<option value="IR"<?php if ($paypalpro_default_country == 'IR') echo ' selected'; ?>><?php _e('Iran, Islamic Republic of', 'calculated-fields-form'); ?></option>
	<option value="IQ"<?php if ($paypalpro_default_country == 'IQ') echo ' selected'; ?>><?php _e('Iraq', 'calculated-fields-form'); ?></option>
	<option value="IE"<?php if ($paypalpro_default_country == 'IE') echo ' selected'; ?>><?php _e('Ireland', 'calculated-fields-form'); ?></option>
	<option value="IM"<?php if ($paypalpro_default_country == 'IM') echo ' selected'; ?>><?php _e('Isle of Man', 'calculated-fields-form'); ?></option>
	<option value="IL"<?php if ($paypalpro_default_country == 'IL') echo ' selected'; ?>><?php _e('Israel', 'calculated-fields-form'); ?></option>
	<option value="IT"<?php if ($paypalpro_default_country == 'IT') echo ' selected'; ?>><?php _e('Italy', 'calculated-fields-form'); ?></option>
	<option value="JM"<?php if ($paypalpro_default_country == 'JM') echo ' selected'; ?>><?php _e('Jamaica', 'calculated-fields-form'); ?></option>
	<option value="JP"<?php if ($paypalpro_default_country == 'JP') echo ' selected'; ?>><?php _e('Japan', 'calculated-fields-form'); ?></option>
	<option value="JE"<?php if ($paypalpro_default_country == 'JE') echo ' selected'; ?>><?php _e('Jersey', 'calculated-fields-form'); ?></option>
	<option value="JO"<?php if ($paypalpro_default_country == 'JO') echo ' selected'; ?>><?php _e('Jordan', 'calculated-fields-form'); ?></option>
	<option value="KZ"<?php if ($paypalpro_default_country == 'KZ') echo ' selected'; ?>><?php _e('Kazakhstan', 'calculated-fields-form'); ?></option>
	<option value="KE"<?php if ($paypalpro_default_country == 'KE') echo ' selected'; ?>><?php _e('Kenya', 'calculated-fields-form'); ?></option>
	<option value="KI"<?php if ($paypalpro_default_country == 'KI') echo ' selected'; ?>><?php _e('Kiribati', 'calculated-fields-form'); ?></option>
	<option value="KP"<?php if ($paypalpro_default_country == 'KP') echo ' selected'; ?>><?php _e('Korea, Democratic People\'s Republic of', 'calculated-fields-form'); ?></option>
	<option value="KR"<?php if ($paypalpro_default_country == 'KR') echo ' selected'; ?>><?php _e('Korea, Republic of', 'calculated-fields-form'); ?></option>
	<option value="KW"<?php if ($paypalpro_default_country == 'KW') echo ' selected'; ?>><?php _e('Kuwait', 'calculated-fields-form'); ?></option>
	<option value="KG"<?php if ($paypalpro_default_country == 'KG') echo ' selected'; ?>><?php _e('Kyrgyzstan', 'calculated-fields-form'); ?></option>
	<option value="LA"<?php if ($paypalpro_default_country == 'LA') echo ' selected'; ?>><?php _e('Lao People\'s Democratic Republic', 'calculated-fields-form'); ?></option>
	<option value="LV"<?php if ($paypalpro_default_country == 'LV') echo ' selected'; ?>><?php _e('Latvia', 'calculated-fields-form'); ?></option>
	<option value="LB"<?php if ($paypalpro_default_country == 'LB') echo ' selected'; ?>><?php _e('Lebanon', 'calculated-fields-form'); ?></option>
	<option value="LS"<?php if ($paypalpro_default_country == 'LS') echo ' selected'; ?>><?php _e('Lesotho', 'calculated-fields-form'); ?></option>
	<option value="LR"<?php if ($paypalpro_default_country == 'LR') echo ' selected'; ?>><?php _e('Liberia', 'calculated-fields-form'); ?></option>
	<option value="LY"<?php if ($paypalpro_default_country == 'LY') echo ' selected'; ?>><?php _e('Libya', 'calculated-fields-form'); ?></option>
	<option value="LI"<?php if ($paypalpro_default_country == 'LI') echo ' selected'; ?>><?php _e('Liechtenstein', 'calculated-fields-form'); ?></option>
	<option value="LT"<?php if ($paypalpro_default_country == 'LT') echo ' selected'; ?>><?php _e('Lithuania', 'calculated-fields-form'); ?></option>
	<option value="LU"<?php if ($paypalpro_default_country == 'LU') echo ' selected'; ?>><?php _e('Luxembourg', 'calculated-fields-form'); ?></option>
	<option value="MO"<?php if ($paypalpro_default_country == 'MO') echo ' selected'; ?>><?php _e('Macao', 'calculated-fields-form'); ?></option>
	<option value="MK"<?php if ($paypalpro_default_country == 'MK') echo ' selected'; ?>><?php _e('Macedonia, the former Yugoslav Republic of', 'calculated-fields-form'); ?></option>
	<option value="MG"<?php if ($paypalpro_default_country == 'MG') echo ' selected'; ?>><?php _e('Madagascar', 'calculated-fields-form'); ?></option>
	<option value="MW"<?php if ($paypalpro_default_country == 'MW') echo ' selected'; ?>><?php _e('Malawi', 'calculated-fields-form'); ?></option>
	<option value="MY"<?php if ($paypalpro_default_country == 'MY') echo ' selected'; ?>><?php _e('Malaysia', 'calculated-fields-form'); ?></option>
	<option value="MV"<?php if ($paypalpro_default_country == 'MV') echo ' selected'; ?>><?php _e('Maldives', 'calculated-fields-form'); ?></option>
	<option value="ML"<?php if ($paypalpro_default_country == 'ML') echo ' selected'; ?>><?php _e('Mali', 'calculated-fields-form'); ?></option>
	<option value="MT"<?php if ($paypalpro_default_country == 'MT') echo ' selected'; ?>><?php _e('Malta', 'calculated-fields-form'); ?></option>
	<option value="MH"<?php if ($paypalpro_default_country == 'MH') echo ' selected'; ?>><?php _e('Marshall Islands', 'calculated-fields-form'); ?></option>
	<option value="MQ"<?php if ($paypalpro_default_country == 'MQ') echo ' selected'; ?>><?php _e('Martinique', 'calculated-fields-form'); ?></option>
	<option value="MR"<?php if ($paypalpro_default_country == 'MR') echo ' selected'; ?>><?php _e('Mauritania', 'calculated-fields-form'); ?></option>
	<option value="MU"<?php if ($paypalpro_default_country == 'MU') echo ' selected'; ?>><?php _e('Mauritius', 'calculated-fields-form'); ?></option>
	<option value="YT"<?php if ($paypalpro_default_country == 'YT') echo ' selected'; ?>><?php _e('Mayotte', 'calculated-fields-form'); ?></option>
	<option value="MX"<?php if ($paypalpro_default_country == 'MX') echo ' selected'; ?>><?php _e('Mexico', 'calculated-fields-form'); ?></option>
	<option value="FM"<?php if ($paypalpro_default_country == 'FM') echo ' selected'; ?>><?php _e('Micronesia, Federated States of', 'calculated-fields-form'); ?></option>
	<option value="MD"<?php if ($paypalpro_default_country == 'MD') echo ' selected'; ?>><?php _e('Moldova, Republic of', 'calculated-fields-form'); ?></option>
	<option value="MC"<?php if ($paypalpro_default_country == 'MC') echo ' selected'; ?>><?php _e('Monaco', 'calculated-fields-form'); ?></option>
	<option value="MN"<?php if ($paypalpro_default_country == 'MN') echo ' selected'; ?>><?php _e('Mongolia', 'calculated-fields-form'); ?></option>
	<option value="ME"<?php if ($paypalpro_default_country == 'ME') echo ' selected'; ?>><?php _e('Montenegro', 'calculated-fields-form'); ?></option>
	<option value="MS"<?php if ($paypalpro_default_country == 'MS') echo ' selected'; ?>><?php _e('Montserrat', 'calculated-fields-form'); ?></option>
	<option value="MA"<?php if ($paypalpro_default_country == 'MA') echo ' selected'; ?>><?php _e('Morocco', 'calculated-fields-form'); ?></option>
	<option value="MZ"<?php if ($paypalpro_default_country == 'MZ') echo ' selected'; ?>><?php _e('Mozambique', 'calculated-fields-form'); ?></option>
	<option value="MM"<?php if ($paypalpro_default_country == 'MM') echo ' selected'; ?>><?php _e('Myanmar', 'calculated-fields-form'); ?></option>
	<option value="NA"<?php if ($paypalpro_default_country == 'NA') echo ' selected'; ?>><?php _e('Namibia', 'calculated-fields-form'); ?></option>
	<option value="NR"<?php if ($paypalpro_default_country == 'NR') echo ' selected'; ?>><?php _e('Nauru', 'calculated-fields-form'); ?></option>
	<option value="NP"<?php if ($paypalpro_default_country == 'NP') echo ' selected'; ?>><?php _e('Nepal', 'calculated-fields-form'); ?></option>
	<option value="NL"<?php if ($paypalpro_default_country == 'NL') echo ' selected'; ?>><?php _e('Netherlands', 'calculated-fields-form'); ?></option>
	<option value="NC"<?php if ($paypalpro_default_country == 'NC') echo ' selected'; ?>><?php _e('New Caledonia', 'calculated-fields-form'); ?></option>
	<option value="NZ"<?php if ($paypalpro_default_country == 'NZ') echo ' selected'; ?>><?php _e('New Zealand', 'calculated-fields-form'); ?></option>
	<option value="NI"<?php if ($paypalpro_default_country == 'NI') echo ' selected'; ?>><?php _e('Nicaragua', 'calculated-fields-form'); ?></option>
	<option value="NE"<?php if ($paypalpro_default_country == 'NE') echo ' selected'; ?>><?php _e('Niger', 'calculated-fields-form'); ?></option>
	<option value="NG"<?php if ($paypalpro_default_country == 'NG') echo ' selected'; ?>><?php _e('Nigeria', 'calculated-fields-form'); ?></option>
	<option value="NU"<?php if ($paypalpro_default_country == 'NU') echo ' selected'; ?>><?php _e('Niue', 'calculated-fields-form'); ?></option>
	<option value="NF"<?php if ($paypalpro_default_country == 'NF') echo ' selected'; ?>><?php _e('Norfolk Island', 'calculated-fields-form'); ?></option>
	<option value="MP"<?php if ($paypalpro_default_country == 'MP') echo ' selected'; ?>><?php _e('Northern Mariana Islands', 'calculated-fields-form'); ?></option>
	<option value="NO"<?php if ($paypalpro_default_country == 'NO') echo ' selected'; ?>><?php _e('Norway', 'calculated-fields-form'); ?></option>
	<option value="OM"<?php if ($paypalpro_default_country == 'OM') echo ' selected'; ?>><?php _e('Oman', 'calculated-fields-form'); ?></option>
	<option value="PK"<?php if ($paypalpro_default_country == 'PK') echo ' selected'; ?>><?php _e('Pakistan', 'calculated-fields-form'); ?></option>
	<option value="PW"<?php if ($paypalpro_default_country == 'PW') echo ' selected'; ?>><?php _e('Palau', 'calculated-fields-form'); ?></option>
	<option value="PS"<?php if ($paypalpro_default_country == 'PS') echo ' selected'; ?>><?php _e('Palestinian Territory, Occupied', 'calculated-fields-form'); ?></option>
	<option value="PA"<?php if ($paypalpro_default_country == 'PA') echo ' selected'; ?>><?php _e('Panama', 'calculated-fields-form'); ?></option>
	<option value="PG"<?php if ($paypalpro_default_country == 'PG') echo ' selected'; ?>><?php _e('Papua New Guinea', 'calculated-fields-form'); ?></option>
	<option value="PY"<?php if ($paypalpro_default_country == 'PY') echo ' selected'; ?>><?php _e('Paraguay', 'calculated-fields-form'); ?></option>
	<option value="PE"<?php if ($paypalpro_default_country == 'PE') echo ' selected'; ?>><?php _e('Peru', 'calculated-fields-form'); ?></option>
	<option value="PH"<?php if ($paypalpro_default_country == 'PH') echo ' selected'; ?>><?php _e('Philippines', 'calculated-fields-form'); ?></option>
	<option value="PN"<?php if ($paypalpro_default_country == 'PN') echo ' selected'; ?>><?php _e('Pitcairn', 'calculated-fields-form'); ?></option>
	<option value="PL"<?php if ($paypalpro_default_country == 'PL') echo ' selected'; ?>><?php _e('Poland', 'calculated-fields-form'); ?></option>
	<option value="PT"<?php if ($paypalpro_default_country == 'PT') echo ' selected'; ?>><?php _e('Portugal', 'calculated-fields-form'); ?></option>
	<option value="PR"<?php if ($paypalpro_default_country == 'PR') echo ' selected'; ?>><?php _e('Puerto Rico', 'calculated-fields-form'); ?></option>
	<option value="QA"<?php if ($paypalpro_default_country == 'QA') echo ' selected'; ?>><?php _e('Qatar', 'calculated-fields-form'); ?></option>
	<option value="RE"<?php if ($paypalpro_default_country == 'RE') echo ' selected'; ?>><?php _e('Réunion', 'calculated-fields-form'); ?></option>
	<option value="RO"<?php if ($paypalpro_default_country == 'RO') echo ' selected'; ?>><?php _e('Romania', 'calculated-fields-form'); ?></option>
	<option value="RU"<?php if ($paypalpro_default_country == 'RU') echo ' selected'; ?>><?php _e('Russian Federation', 'calculated-fields-form'); ?></option>
	<option value="RW"<?php if ($paypalpro_default_country == 'RW') echo ' selected'; ?>><?php _e('Rwanda', 'calculated-fields-form'); ?></option>
	<option value="BL"<?php if ($paypalpro_default_country == 'BL') echo ' selected'; ?>><?php _e('Saint Barthélemy', 'calculated-fields-form'); ?></option>
	<option value="SH"<?php if ($paypalpro_default_country == 'SH') echo ' selected'; ?>><?php _e('Saint Helena, Ascension and Tristan da Cunha', 'calculated-fields-form'); ?></option>
	<option value="KN"<?php if ($paypalpro_default_country == 'KN') echo ' selected'; ?>><?php _e('Saint Kitts and Nevis', 'calculated-fields-form'); ?></option>
	<option value="LC"<?php if ($paypalpro_default_country == 'LC') echo ' selected'; ?>><?php _e('Saint Lucia', 'calculated-fields-form'); ?></option>
	<option value="MF"<?php if ($paypalpro_default_country == 'MF') echo ' selected'; ?>><?php _e('Saint Martin (French part)', 'calculated-fields-form'); ?></option>
	<option value="PM"<?php if ($paypalpro_default_country == 'PM') echo ' selected'; ?>><?php _e('Saint Pierre and Miquelon', 'calculated-fields-form'); ?></option>
	<option value="VC"<?php if ($paypalpro_default_country == 'VC') echo ' selected'; ?>><?php _e('Saint Vincent and the Grenadines', 'calculated-fields-form'); ?></option>
	<option value="WS"<?php if ($paypalpro_default_country == 'WS') echo ' selected'; ?>><?php _e('Samoa', 'calculated-fields-form'); ?></option>
	<option value="SM"<?php if ($paypalpro_default_country == 'SM') echo ' selected'; ?>><?php _e('San Marino', 'calculated-fields-form'); ?></option>
	<option value="ST"<?php if ($paypalpro_default_country == 'ST') echo ' selected'; ?>><?php _e('Sao Tome and Principe', 'calculated-fields-form'); ?></option>
	<option value="SA"<?php if ($paypalpro_default_country == 'SA') echo ' selected'; ?>><?php _e('Saudi Arabia', 'calculated-fields-form'); ?></option>
	<option value="SN"<?php if ($paypalpro_default_country == 'SN') echo ' selected'; ?>><?php _e('Senegal', 'calculated-fields-form'); ?></option>
	<option value="RS"<?php if ($paypalpro_default_country == 'RS') echo ' selected'; ?>><?php _e('Serbia', 'calculated-fields-form'); ?></option>
	<option value="SC"<?php if ($paypalpro_default_country == 'SC') echo ' selected'; ?>><?php _e('Seychelles', 'calculated-fields-form'); ?></option>
	<option value="SL"<?php if ($paypalpro_default_country == 'SL') echo ' selected'; ?>><?php _e('Sierra Leone', 'calculated-fields-form'); ?></option>
	<option value="SG"<?php if ($paypalpro_default_country == 'SG') echo ' selected'; ?>><?php _e('Singapore', 'calculated-fields-form'); ?></option>
	<option value="SX"<?php if ($paypalpro_default_country == 'SX') echo ' selected'; ?>><?php _e('Sint Maarten (Dutch part)', 'calculated-fields-form'); ?></option>
	<option value="SK"<?php if ($paypalpro_default_country == 'SK') echo ' selected'; ?>><?php _e('Slovakia', 'calculated-fields-form'); ?></option>
	<option value="SI"<?php if ($paypalpro_default_country == 'SI') echo ' selected'; ?>><?php _e('Slovenia', 'calculated-fields-form'); ?></option>
	<option value="SB"<?php if ($paypalpro_default_country == 'SB') echo ' selected'; ?>><?php _e('Solomon Islands', 'calculated-fields-form'); ?></option>
	<option value="SO"<?php if ($paypalpro_default_country == 'SO') echo ' selected'; ?>><?php _e('Somalia', 'calculated-fields-form'); ?></option>
	<option value="ZA"<?php if ($paypalpro_default_country == 'ZA') echo ' selected'; ?>><?php _e('South Africa', 'calculated-fields-form'); ?></option>
	<option value="GS"<?php if ($paypalpro_default_country == 'GS') echo ' selected'; ?>><?php _e('South Georgia and the South Sandwich Islands', 'calculated-fields-form'); ?></option>
	<option value="SS"<?php if ($paypalpro_default_country == 'SS') echo ' selected'; ?>><?php _e('South Sudan', 'calculated-fields-form'); ?></option>
	<option value="ES"<?php if ($paypalpro_default_country == 'ES') echo ' selected'; ?>><?php _e('Spain', 'calculated-fields-form'); ?></option>
	<option value="LK"<?php if ($paypalpro_default_country == 'LK') echo ' selected'; ?>><?php _e('Sri Lanka', 'calculated-fields-form'); ?></option>
	<option value="SD"<?php if ($paypalpro_default_country == 'SD') echo ' selected'; ?>><?php _e('Sudan', 'calculated-fields-form'); ?></option>
	<option value="SR"<?php if ($paypalpro_default_country == 'SR') echo ' selected'; ?>><?php _e('Suriname', 'calculated-fields-form'); ?></option>
	<option value="SJ"<?php if ($paypalpro_default_country == 'SJ') echo ' selected'; ?>><?php _e('Svalbard and Jan Mayen', 'calculated-fields-form'); ?></option>
	<option value="SZ"<?php if ($paypalpro_default_country == 'SZ') echo ' selected'; ?>><?php _e('Swaziland', 'calculated-fields-form'); ?></option>
	<option value="SE"<?php if ($paypalpro_default_country == 'SE') echo ' selected'; ?>><?php _e('Sweden', 'calculated-fields-form'); ?></option>
	<option value="CH"<?php if ($paypalpro_default_country == 'CH') echo ' selected'; ?>><?php _e('Switzerland', 'calculated-fields-form'); ?></option>
	<option value="SY"<?php if ($paypalpro_default_country == 'SY') echo ' selected'; ?>><?php _e('Syrian Arab Republic', 'calculated-fields-form'); ?></option>
	<option value="TW"<?php if ($paypalpro_default_country == 'TW') echo ' selected'; ?>><?php _e('Taiwan, Province of China', 'calculated-fields-form'); ?></option>
	<option value="TJ"<?php if ($paypalpro_default_country == 'TJ') echo ' selected'; ?>><?php _e('Tajikistan', 'calculated-fields-form'); ?></option>
	<option value="TZ"<?php if ($paypalpro_default_country == 'TZ') echo ' selected'; ?>><?php _e('Tanzania, United Republic of', 'calculated-fields-form'); ?></option>
	<option value="TH"<?php if ($paypalpro_default_country == 'TH') echo ' selected'; ?>><?php _e('Thailand', 'calculated-fields-form'); ?></option>
	<option value="TL"<?php if ($paypalpro_default_country == 'TL') echo ' selected'; ?>><?php _e('Timor-Leste', 'calculated-fields-form'); ?></option>
	<option value="TG"<?php if ($paypalpro_default_country == 'TG') echo ' selected'; ?>><?php _e('Togo', 'calculated-fields-form'); ?></option>
	<option value="TK"<?php if ($paypalpro_default_country == 'TK') echo ' selected'; ?>><?php _e('Tokelau', 'calculated-fields-form'); ?></option>
	<option value="TO"<?php if ($paypalpro_default_country == 'TO') echo ' selected'; ?>><?php _e('Tonga', 'calculated-fields-form'); ?></option>
	<option value="TT"<?php if ($paypalpro_default_country == 'TT') echo ' selected'; ?>><?php _e('Trinidad and Tobago', 'calculated-fields-form'); ?></option>
	<option value="TN"<?php if ($paypalpro_default_country == 'TN') echo ' selected'; ?>><?php _e('Tunisia', 'calculated-fields-form'); ?></option>
	<option value="TR"<?php if ($paypalpro_default_country == 'TR') echo ' selected'; ?>><?php _e('Turkey', 'calculated-fields-form'); ?></option>
	<option value="TM"<?php if ($paypalpro_default_country == 'TM') echo ' selected'; ?>><?php _e('Turkmenistan', 'calculated-fields-form'); ?></option>
	<option value="TC"<?php if ($paypalpro_default_country == 'TC') echo ' selected'; ?>><?php _e('Turks and Caicos Islands', 'calculated-fields-form'); ?></option>
	<option value="TV"<?php if ($paypalpro_default_country == 'TV') echo ' selected'; ?>><?php _e('Tuvalu', 'calculated-fields-form'); ?></option>
	<option value="UG"<?php if ($paypalpro_default_country == 'UG') echo ' selected'; ?>><?php _e('Uganda', 'calculated-fields-form'); ?></option>
	<option value="UA"<?php if ($paypalpro_default_country == 'UA') echo ' selected'; ?>><?php _e('Ukraine', 'calculated-fields-form'); ?></option>
	<option value="AE"<?php if ($paypalpro_default_country == 'AE') echo ' selected'; ?>><?php _e('United Arab Emirates', 'calculated-fields-form'); ?></option>
	<option value="GB"<?php if ($paypalpro_default_country == 'GB') echo ' selected'; ?>><?php _e('United Kingdom', 'calculated-fields-form'); ?></option>
	<option value="US"<?php if ($paypalpro_default_country == 'US') echo ' selected'; ?>><?php _e('United States', 'calculated-fields-form'); ?></option>
	<option value="UM"<?php if ($paypalpro_default_country == 'UM') echo ' selected'; ?>><?php _e('United States Minor Outlying Islands', 'calculated-fields-form'); ?></option>
	<option value="UY"<?php if ($paypalpro_default_country == 'UY') echo ' selected'; ?>><?php _e('Uruguay', 'calculated-fields-form'); ?></option>
	<option value="UZ"<?php if ($paypalpro_default_country == 'UZ') echo ' selected'; ?>><?php _e('Uzbekistan', 'calculated-fields-form'); ?></option>
	<option value="VU"<?php if ($paypalpro_default_country == 'VU') echo ' selected'; ?>><?php _e('Vanuatu', 'calculated-fields-form'); ?></option>
	<option value="VE"<?php if ($paypalpro_default_country == 'VE') echo ' selected'; ?>><?php _e('Venezuela, Bolivarian Republic of', 'calculated-fields-form'); ?></option>
	<option value="VN"<?php if ($paypalpro_default_country == 'VN') echo ' selected'; ?>><?php _e('Viet Nam', 'calculated-fields-form'); ?></option>
	<option value="VG"<?php if ($paypalpro_default_country == 'VG') echo ' selected'; ?>><?php _e('Virgin Islands, British', 'calculated-fields-form'); ?></option>
	<option value="VI"<?php if ($paypalpro_default_country == 'VI') echo ' selected'; ?>><?php _e('Virgin Islands, U.S.', 'calculated-fields-form'); ?></option>
	<option value="WF"<?php if ($paypalpro_default_country == 'WF') echo ' selected'; ?>><?php _e('Wallis and Futuna', 'calculated-fields-form'); ?></option>
	<option value="EH"<?php if ($paypalpro_default_country == 'EH') echo ' selected'; ?>><?php _e('Western Sahara', 'calculated-fields-form'); ?></option>
	<option value="YE"<?php if ($paypalpro_default_country == 'YE') echo ' selected'; ?>><?php _e('Yemen', 'calculated-fields-form'); ?></option>
	<option value="ZM"<?php if ($paypalpro_default_country == 'ZM') echo ' selected'; ?>><?php _e('Zambia', 'calculated-fields-form'); ?></option>
	<option value="ZW"<?php if ($paypalpro_default_country == 'ZW') echo ' selected'; ?>><?php _e('Zimbabwe', 'calculated-fields-form'); ?></option>
</select>
    </div>
	<div class="clearer"></div>
  </div>
  <div class="fields column2 redw70">
    <label><?php echo __('Credit Card Number:','calculated-fields-form'); ?></label>
    <div class="dfield" id="field-c7-ppp">
      <input type="text" size="18" name="cfpp_customer_credit_card_number" id="cfpp_customer_credit_card_number" value="" />
    </div>
	<div class="clearer"></div>
  </div>
  <div class="fields column2 redw30">
    <label><?php echo __('CVV Number:','calculated-fields-form'); ?></label>
    <div class="dfield" id="field-c8-ppp">
      <input type="text" size="5" name="cfpp_cc_cvv2_number" id="cfpp_cc_cvv2_number" value="" />
    </div>
	<div class="clearer"></div>
  </div>
  <div class="fields column2">
    <label><?php echo __('Card Type:','calculated-fields-form'); ?></label>
    <div class="dfield" id="field-c9-ppp">
      <select name="cfpp_customer_credit_card_type" id="cfpp_customer_credit_card_type">
        <option value="Visa">Visa</option>
        <option value="MasterCard">MasterCard</option>
        <option value="Discover">Discover</option>
        <option value="Amex">Amex</option>
      </select>
    </div>
	<div class="clearer"></div>
  </div>
  <div class="fields column2">
    <label><?php echo __('Expiration:','calculated-fields-form'); ?></label>
    <div class="dfield" id="field-c10-ppp">
      <select name="cfpp_cc_expiration_month">
        <option value="01"><?php echo __('January','calculated-fields-form'); ?></option>
        <option value="02"><?php echo __('February','calculated-fields-form'); ?></option>
        <option value="03"><?php echo __('March','calculated-fields-form'); ?></option>
        <option value="04"><?php echo __('April','calculated-fields-form'); ?></option>
        <option value="05"><?php echo __('May','calculated-fields-form'); ?></option>
        <option value="06"><?php echo __('June','calculated-fields-form'); ?></option>
        <option value="07"><?php echo __('July','calculated-fields-form'); ?></option>
        <option value="08"><?php echo __('August','calculated-fields-form'); ?></option>
        <option value="09"><?php echo __('September','calculated-fields-form'); ?></option>
        <option value="10"><?php echo __('October','calculated-fields-form'); ?></option>
        <option value="11"><?php echo __('November','calculated-fields-form'); ?></option>
        <option value="12"><?php echo __('December','calculated-fields-form'); ?></option>
      </select>
      /
      <select name="cfpp_cc_expiration_year">
        <?php $d= intval(date("Y")); for($i=$d;$i<$d+10;$i++) echo '<option value="'.$i.'" vt="'.$i.'">'.$i.'</option>'; ?>
      </select>
    </div>
	<div class="clearer"></div>
  </div>

</div>
<div class="clearer"></div>
</div>
<?php
            $buffered_contents = ob_get_contents();
            ob_end_clean();
            return $buffered_contents;
        }


		/************************ PUBLIC METHODS  *****************************/


	    /**
         * Check if the payments fields is used in the form, and inserts them
         */
        public function	insert_payment_fields( $form_code, $id)
		{
			global $wpdb;
			$rows = $wpdb->get_results(
					$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $id )
				);

			if ( !empty( $rows ) && $rows[0]->enabled && strpos($form_code, 'vt="'.$this->addonID.'"') === false)
			{
			    $this->_inserted = true;
			    $form_code = preg_replace( '/<!--addons-payment-fields-->/i', '<!--addons-payment-fields-->'.$this->get_payment_fields($id, $rows[0]), $form_code );

			    // output radio-buttons here
    			$form_code = preg_replace( '/<!--addons-payment-options-->/i', '<div><input type="radio" id="cffaddonidpaypro'.$id.'" name="bccf_payment_option_paypal" vt="'.$this->addonID.'" value="'.$this->addonID.'" checked> '.__( ($rows[0]->enable_option_yes!=''?$rows[0]->enable_option_yes:$this->default_pay_label) , 'calculated-fields-form').'</div><!--addons-payment-options-->', $form_code );

                if (($rows[0]->enabled == '2' || $rows[0]->enabled == '4') && !strpos($form_code,'bccf_payment_option_paypal" vt="0') )
    			    $form_code = preg_replace( '/<!--addons-payment-options-->/i', '<!--addons-payment-options--><div><input type="radio" name="bccf_payment_option_paypal" vt="0" value="0"> '.__( $this->_cpcff_main->get_form($id)->get_option('enable_paypal_option_no',CP_CALCULATEDFIELDSF_PAYPAL_OPTION_NO), 'calculated-fields-form').'</div>', $form_code );

    			if (substr_count ($form_code, 'name="bccf_payment_option_paypal"') > 1)
    			    $form_code = str_replace( 'id="field-c0" style="display:none">', 'id="field-c0">', $form_code);
			}

            return $form_code;
		} // End insert_script


		/**
         * script process payment
         */
		public function pp_payments_script( $form_sequence_id, $form_id )
		{
            global $wpdb;

            $rows = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $form_id )
					);

			if (empty( $rows ) || !$rows[0]->enabled)
			    return;
?>
            if (!document.getElementById("cffaddonidpaypro<?php echo $form_id; ?>").checked)
                delete validation_rules['<?php print esc_js( $this->addonID); ?>'];
			if(
				document.getElementById("cffaddonidpaypro<?php echo $form_id; ?>").checked &&
                document.getElementById("cp_contact_form_paypal_paymentspro<?php echo $form_id; ?>").value != '' &&
				(
					typeof validation_rules['<?php print esc_js( $this->addonID); ?>'] == 'undefined'||
					validation_rules['<?php print esc_js( $this->addonID); ?>'] == false
				)
			)
			{
				validation_rules['<?php print esc_js( $this->addonID); ?>'] = false;
                /**
				<?php // This code was commented because cloning the form breaks the captcha ?>
				var cp_calculatedfieldsf_pform_copy = $dexQuery("#cp_calculatedfieldsf_pform<?php echo $form_sequence_id; ?>").clone(true);
				cp_calculatedfieldsf_pform_copy.find( '.ignore' ).closest( '.fields' ).remove();
				cp_calculatedfieldsf_pform_copy.find('[name="cfpp_customer_country"]').val( $dexQuery("#cp_calculatedfieldsf_pform<?php echo $form_sequence_id; ?>").find('[name="cfpp_customer_country"]').val());
				cp_calculatedfieldsf_pform_copy.find('[name="cfpp_cc_expiration_month"]').val( $dexQuery("#cp_calculatedfieldsf_pform<?php echo $form_sequence_id; ?>").find('[name="cfpp_cc_expiration_month"]').val());
				cp_calculatedfieldsf_pform_copy.find('[name="cfpp_cc_expiration_year"]').val( $dexQuery("#cp_calculatedfieldsf_pform<?php echo $form_sequence_id; ?>").find('[name="cfpp_cc_expiration_year"]').val());
				cp_calculatedfieldsf_pform_copy.find('[name="cfpp_customer_credit_card_type"]').val( $dexQuery("#cp_calculatedfieldsf_pform<?php echo $form_sequence_id; ?>").find('[name="cfpp_customer_credit_card_type"]').val());
                */
                var ppdata = $dexQuery("#cp_calculatedfieldsf_pform<?php echo $form_sequence_id; ?> :input:not(.ignore)").serialize() +'&'+$dexQuery.param({ 'cffpproprocess': '1' });
                $dexQuery.ajax({
                    type: "POST",
                    async: true,
                    url: '<?php echo CPCFF_AUXILIARY::site_url(true); ?>/',
                    data: ppdata,
                    success: function(data)
                    {
                       if (data.trim() != 'OK')
                           alert(data);
                       else
                       {
                           document.getElementById("cp_contact_form_paypal_paymentspro<?php echo $form_id; ?>").value = "";
						   validation_rules['<?php print esc_js( $this->addonID); ?>'] = true;
                           processing_form();
                       }
                    }
                });
			}
<?php
        }


		/**
         * process payment
         */
		public function pp_payments_pro(&$params, &$str, $fields)
		{
            global $wpdb;

            $payment_option = (isset($_POST["bccf_payment_option_paypal"])?$_POST["bccf_payment_option_paypal"]:$this->addonID);
            if ($payment_option != $this->addonID)
                return;

            if (@$_POST['cp_contact_form_paypal_paymentspro'.$params["formid"]] != "1")
            {
                $params["payment_option"] = $this->name;
                return;
            }

            if (@$_POST["cffpproprocess"] != '1')
                return;

            $rows = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $params["formid"] )
					);
			if (empty( $rows ) || !$rows[0]->enabled)
			    return;

            $form_obj = $this->_cpcff_main->get_form($params['formid']);

            // Set request-specific fields.
            $paymentType = urlencode('Sale');				// or 'Authorization'

            $firstName = urlencode($_POST['cfpp_customer_first_name']);
            $lastName = urlencode($_POST['cfpp_customer_last_name']);
            $creditCardType = urlencode($_POST['cfpp_customer_credit_card_type']);
            $creditCardNumber = urlencode($_POST['cfpp_customer_credit_card_number']);
            $expDateMonth = $_POST['cfpp_cc_expiration_month'];
            // Month must be padded with leading zero
            $padDateMonth = urlencode(str_pad($expDateMonth, 2, '0', STR_PAD_LEFT));

            $expDateYear = urlencode($_POST['cfpp_cc_expiration_year']);
            $cvv2Number = urlencode($_POST['cfpp_cc_cvv2_number']);
            $address1 = urlencode($_POST['cfpp_customer_address1']);
            $address2 = urlencode($_POST['cfpp_customer_address2']);
            $city = urlencode($_POST['cfpp_customer_city']);
            $state = urlencode($_POST['cfpp_customer_state']);
            $zip = urlencode($_POST['cfpp_customer_zip']);
            $country = urlencode($_POST['cfpp_customer_country']);				// US or other valid country code

            $amount = urlencode($params["final_price"]);
            $currencyID = urlencode(strtoupper($rows[0]->currency));

            if ($rows[0]->paypalpro_api_bperiod == '')
            {
                // Add request-specific fields to the request string.
                $nvpStr =	"&PAYMENTACTION=$paymentType&AMT=$amount&CREDITCARDTYPE=$creditCardType&ACCT=$creditCardNumber".
                			"&EXPDATE=$padDateMonth$expDateYear&CVV2=$cvv2Number&FIRSTNAME=$firstName&LASTNAME=$lastName".
                			"&STREET=$address1&CITY=$city&STATE=$state&ZIP=$zip&COUNTRYCODE=$country&CURRENCYCODE=$currencyID&BUTTONSOURCE=NetFactorSL_SI_Custom";

                // Execute the API operation; see the PPHttpPost function above.
                $httpParsedResponseAr = $this->pp_payments_pro_POST('DoDirectPayment', $nvpStr, $rows[0]);
            }
            else
            {

                if ($period == 'Week')
                    $frequency = 52;
                else if ($period == 'SemiMonth' || $period == 'Year')
                    $frequency = 1;
                else $frequency = 12;
                // Add request-specific fields to the request string.
                $nvpStr =	"&MAXFAILEDPAYMENTS=3&DESC=".urlencode($form_obj->get_option('paypal_product_name', CP_CALCULATEDFIELDSF_DEFAULT_PRODUCT_NAME))."&PROFILESTARTDATE=".date("Y-m-d")."T00:00:00Z&BILLINGPERIOD=".$rows[0]->paypalpro_api_bperiod."&BILLINGFREQUENCY=".$frequency."&AMT=$amount&CREDITCARDTYPE=$creditCardType&ACCT=$creditCardNumber".
                			"&EXPDATE=$padDateMonth$expDateYear&CVV2=$cvv2Number&FIRSTNAME=$firstName&LASTNAME=$lastName".
                			"&STREET=$address1&CITY=$city&STATE=$state&ZIP=$zip&COUNTRYCODE=$country&CURRENCYCODE=$currencyID&BUTTONSOURCE=NetFactorSL_SI_Custom";

                // Execute the API operation; see the PPHttpPost function above.
                $httpParsedResponseAr = $this->pp_payments_pro_POST('CreateRecurringPaymentsProfile', $nvpStr, $rows[0]);
            }
            foreach ($httpParsedResponseAr as $item => $value)
                $httpParsedResponseAr[$item] = urldecode($value);
            if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) {
            	exit('OK');
            } else  {
            	exit("Payment failed\n\nErrorCode: " . $httpParsedResponseAr["L_ERRORCODE0"]."\nError: ". $httpParsedResponseAr["L_SHORTMESSAGE0"]."\nMessage: ". $httpParsedResponseAr["L_LONGMESSAGE0"]);
            }
		} // end pp_payments_pro


		/**
		 * mark the item as paid
		 */
		public function pp_payments_pro_update_status( $params )
		{
            global $wpdb;

            $payment_option = (isset($_POST["bccf_payment_option_paypal"])?$_POST["bccf_payment_option_paypal"]:$this->addonID);
            if($payment_option != $this->addonID)
                return;

            $row = $wpdb->get_row(
				$wpdb->prepare( "SELECT * FROM ".$wpdb->prefix.$this->form_table." WHERE formid=%d", $params["formid"] )
			);
			if(empty($row) || !$row->enabled) return;
            
			// mark item as paid
            $form_obj = $this->_cpcff_main->get_form($params['formid']);
			CPCFF_SUBMISSIONS::update($params["itemnumber"], array('paid'=>1));

            do_action( 'cpcff_payment_processed', $params );

			$form_obj = CPCFF_SUBMISSIONS::get_form($params["itemnumber"]);
			if($form_obj->get_option('paypal_notiemails', '0') != '1')
			    $this->_cpcff_main->send_mails($params['itemnumber']);


			/**
			 * Action called after process the data received by PayPal.
			 * To the function is passed an array with the data collected by the form.
			 */
			do_action( 'cpcff_payment_processed', $params );

            $redirect = true;

		    /**
		     * Filters applied to decide if the website should be redirected to the thank you page after submit the form,
		     * pass a boolean as parameter and returns a boolean
		     */
            $redirect = apply_filters( 'cpcff_redirect', $redirect );

            if( $redirect )
            {
                $location = CPCFF_AUXILIARY::replace_params_into_url($form_obj->get_option('fp_return_page', CP_CALCULATEDFIELDSF_DEFAULT_fp_return_page), $params);
                header("Location: ".$location);
                exit;
            }            

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
    $cpcff_paypalpro_obj = new CPCFF_PayPalPro();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cpcff_paypalpro_obj);
}
?>