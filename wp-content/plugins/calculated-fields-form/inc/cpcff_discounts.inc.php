<?php
if(
	!defined('CP_CALCULATEDFIELDSF_DISCOUNT_CODES_TABLE_NAME') ||
	!defined('CP_CALCULATEDFIELDSF_DEFAULT_CURRENCY')
)
{
	print 'Direct access not allowed.';
    exit;
}

if(!class_exists( 'CPCFF_COUPON' ))
{
	class CPCFF_COUPON
	{
		public static $coupon_applied = false;
		public static $discount_note  = '';

		/**
		 * Checks the coupon's code entered through the public form.
		 *
		 * This method checks the validity of the coupon's code and the nonce protection rules
		 * to returns a JSON object with the coupon's data
		 *
		 * @sinze 1.0.178
		 *
		 * @params array $params, array with the elements:
		 * 		action string with value checkcoupon.
		 * 		formid integer with the form's id that is being populated.
		 * 		formsequence integer that represent the form in the webpage.
		 * 		_cpcff_public_nonce string the nonce code that protects the form.
		 *
		 * @return string json object
		 */
		public static function check_from_web( $params )
		{
			$cpcff_main = CPCFF_MAIN::instance();
			if(
				isset($params['action']) &&
				$params['action'] == 'checkcoupon'
			)
			{
				$json = "{}";
				if(
					!empty($params['couponcode']) &&
					!empty($params['formid']) &&
					!empty($params['formsequence']) &&
					(
						!@intval(apply_filters('cpcff_check_nonce', get_option('CP_CALCULATEDFIELDSF_NONCE', false))) ||
						(
							!empty($params['_cpcff_public_nonce']) &&
							wp_verify_nonce($params['_cpcff_public_nonce'], 'cpcff_form_'.$params['formid'].$params['formsequence'])
						)
					)
				){
					if(($coupon = self::get_coupon($params['formid'],$params['couponcode'])) !== false)
					{
						$json = json_encode(
							array(
								"code" 			=> $coupon->code,
								"discount" 		=> $coupon->discount,
								"availability" 	=> $coupon->availability
							)
						);
					}
					else
					{
						global $cpcff_default_texts_array;
						$cpcff_texts_array = $cpcff_main->get_form($params['formid'])->get_option('vs_all_texts', $cpcff_default_texts_array);
						$cpcff_texts_array = (is_string($cpcff_texts_array)) ? unserialize($cpcff_texts_array) : $cpcff_texts_array;
						if(
							!empty($cpcff_texts_array) &&
							!empty($cpcff_texts_array['errors']) &&
							!empty($cpcff_texts_array['errors']['coupon'])
						)
						{
							$json = json_encode(
								array(
									"error" => $cpcff_texts_array['errors']['coupon']['text']
								)
							);
						}
					}
				}
				print $json;
				exit;
			}
		} // End check_from_web

		/**
		 * Prints the coupons table, and applies actions like delete or add a coupon
		 *
		 * Checks if the user is an administrator before displaying the table of coupons or apply the coupons actions.
		 *
		 * @sinze 1.0.178
		 *
		 * @params array $params, with the elements:
		 * 		cp_calculated_fields_form_post string with value loadcoupons.
		 * 		dex_item integer with the form's id.
		 *		................ FOR THE ADD COUPON ACTION .....................
		 *		cff_add_coupon integer number with value 1.
		 *		cff_coupon_code string.
		 *		cff_discount float.
		 *		cff_discounttype integer, 0 percentage, 1 fixed value.
		 *		cff_coupon_expires string, date format.
		 *		................ FOR THE DELETE COUPON ACTION .....................
		 *		cff_delete_coupon integer number with value 1.
		 *		cff_coupon_code string.
		 *
		 * @return void.
		 */
		public static function settings_actions( $params )
		{
			if(
				isset($params['cp_calculated_fields_form_post']) &&
				$params['cp_calculated_fields_form_post'] == 'loadcoupons'
			)
			{
				if ( !current_user_can('manage_options') ) // prevent loading coupons from outside admin area
				{
					_e( 'No enough privilegies to load this content.', 'calculated-fields-form' );
				}
				else
				{
					if(
						isset($params['dex_item']) &&
						($form_id = @intval($params['dex_item'])) != 0
					)
					{
						// Add coupon
						if(
							isset($params['cff_add_coupon']) &&
							$params['cff_add_coupon'] == 1 &&
							isset($params['cff_coupon_code']) &&
							isset($params['cff_discount']) &&
							isset($params['cff_discounttype']) &&
							isset($params['cff_coupon_expires']) &&
							isset($params['cff_discounttimes'])
						)
						{
							CPCFF_COUPON::add_coupon($form_id, $params['cff_coupon_code'], $params['cff_discount'], $params['cff_discounttype'], $params['cff_discounttimes'], $params['cff_coupon_expires']);
						}

						// Delete coupon
						if(
							isset($params['cff_delete_coupon']) &&
							$params['cff_delete_coupon'] == 1
						)
						{
							CPCFF_COUPON::delete_coupon($params['cff_coupon_code'] );
						}

						// Prints the discounts table
						print CPCFF_COUPON::display_codes($form_id);
					}
				}
				exit;
			}
		} // End settings_actions

		public static function apply_discount($formid, $couponcode, $price)
		{
			global $wpdb;
			$coupon = self::get_coupon($formid, $couponcode);

			if (!empty($coupon))
			{
				self::$coupon_applied = $coupon;
				$coupon->discount = preg_replace('/[^\.\d]/', '', $coupon->discount);

				if ($coupon->availability==1)
				{
					$price = number_format (floatval ($price) - $coupon->discount,2);
					$cpcff_main = CPCFF_MAIN::instance();
					self::$discount_note = " (".$cpcff_main->get_form($formid)->get_option('currency', CP_CALCULATEDFIELDSF_DEFAULT_CURRENCY)." ".$coupon->discount." discount applied)";
				}
				else
				{
					$price = number_format (floatval ($price) - $price*$coupon->discount/100,2);
					self::$discount_note = " (".$coupon->discount."% discount applied)";
				}

				$wpdb->query( $wpdb->prepare( 'UPDATE ' . CP_CALCULATEDFIELDSF_DISCOUNT_CODES_TABLE_NAME . ' SET used=used+1 WHERE form_id=%d AND code=%s', $coupon->form_id, $coupon->code ) );

			}

			return $price;
		} // End apply_discount

		public static function active_coupons( $formid )
		{
			global $wpdb;
			$coupons = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".CP_CALCULATEDFIELDSF_DISCOUNT_CODES_TABLE_NAME." WHERE expires>='".date("Y-m-d")." 00:00:00' AND `form_id`=%d", $formid ) );

			return (!empty($coupons)) ? $coupons : 0;
		} // End active_coupons

		public static function get_coupon($formid, $couponcode)
		{
			global $wpdb;
			$coupon = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".CP_CALCULATEDFIELDSF_DISCOUNT_CODES_TABLE_NAME." WHERE code=%s AND expires>='".date("Y-m-d")." 00:00:00' AND (times = 0 OR used < times) AND `form_id`=%d", $couponcode, $formid ) );

			if (!empty($coupon)) return $coupon;

			return false;
		} // End get_coupon

		public static function add_coupon($formid, $couponcode, $discount, $discounttype, $times, $expires)
		{
			global $wpdb;
			return $wpdb->insert( CP_CALCULATEDFIELDSF_DISCOUNT_CODES_TABLE_NAME,
							array('form_id' => $formid,
									'code' => $couponcode,
									'discount' => $discount,
									'availability' => $discounttype,
									'expires' => $expires,
									'times' => $times,
							),
							array( '%d', '%s', '%s', '%d', '%s', '%d' )
				);
		} // End add_coupon

		public static function delete_coupon($id)
		{
			global $wpdb;
			return $wpdb->query( $wpdb->prepare( "DELETE FROM ".CP_CALCULATEDFIELDSF_DISCOUNT_CODES_TABLE_NAME." WHERE id = %d", $id ));
		} // End delete_coupon

		public static function display_codes($formid)
		{
			global $wpdb;

			$codes = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM '.CP_CALCULATEDFIELDSF_DISCOUNT_CODES_TABLE_NAME.' WHERE `form_id`=%d', $formid ) );

			$result  = '';
			if (count ($codes) && ! is_null( $codes[0]->id ) )
			{
				$result .= '<table>
					<tr>
						<th style="padding:2px;background-color: #cccccc;font-weight:bold;">'.__('Cupon Code', 'calculated-fields-form' ).'</th>
						<th style="padding:2px;background-color: #cccccc;font-weight:bold;">'.__('Discount', 'calculated-fields-form' ).'</th>
						<th style="padding:2px;background-color: #cccccc;font-weight:bold;">'.__('Type', 'calculated-fields-form' ).'</th>
						<th style="padding:2px;background-color: #cccccc;font-weight:bold;">'.__('Can be used?', 'calculated-fields-form' ).'</th>
						<th style="padding:2px;background-color: #cccccc;font-weight:bold;">'.__('Used so far', 'calculated-fields-form' ).'</th>
						<th style="padding:2px;background-color: #cccccc;font-weight:bold;">'.__('Valid until', 'calculated-fields-form' ).'</th>
						<th style="padding:2px;background-color: #cccccc;font-weight:bold;">'.__('Options', 'calculated-fields-form' ).'</th>
					</tr>';

				foreach ($codes as $value)
				{
				   $result .= '<tr>';
				   $result .= '<td>'.$value->code.'</td>';
				   $result .= '<td>'.$value->discount.'</td>';
				   $result .= '<td>'.($value->availability==1? __("Fixed Value", 'calculated-fields-form' ):__("Percent", 'calculated-fields-form' )).'</td>';
				   $result .= '<td>'.( $value->times == 0 ? esc_html__( 'Unlimited', 'calculated-fields-form' ) : $value->times . ' ' . esc_html__( 'Times', 'calculated-fields-form' ) ).'</td>';
				   $result .= '<td>'.$value->used.'</td>';
				   $result .= '<td>'.substr($value->expires,0,10).'</td>';
				   $result .= '<td>[<a href="javascript:dex_delete_coupon('.$value->id.')">'.__('Delete', 'calculated-fields-form' ).'</a>]</td>';
				   $result .= '</tr>';
				}
				$result .= '</table>';
			}
			else
				$result .= __( 'No discount codes listed for this form yet.', 'calculated-fields-form' );

			return $result;
		} // End display_codes

	} // End Class
}
