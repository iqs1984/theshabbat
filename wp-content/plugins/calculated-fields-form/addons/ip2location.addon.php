<?php
/*
....
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'CPCFF_IP2LOCATION' ) )
{
    class CPCFF_IP2LOCATION extends CPCFF_BaseAddon
    {
		static public $category = 'External Services';

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-ip2location-20151221";
		protected $name = "CFF - IP2Location";
		protected $description;
        protected $help = 'https://cff.dwbooster.com/documentation#ip2location-addon';

		public function get_addon_settings()
		{
			if( isset( $_REQUEST[ 'cpcff_ip2location' ] ) )
			{
				check_admin_referer( $this->addonID, '_cpcff_nonce' );
				update_option( 'cpcff_ip2location_data', $_REQUEST[ 'cpcff_ip2location_data' ] );
			}
			$cpcff_ip2location_data = get_option( 'cpcff_ip2location_data', array() );

			$cpcff_main = CPCFF_MAIN::instance();
			?>
			<form method="post" enctype="multipart/form-data" action="<?php print esc_url(admin_url('admin.php?page=cp_calculated_fields_form')); ?>">
				<div id="metabox_ip2location_addon_settings" class="postbox cff-add-on-settings <?php print esc_attr(__CLASS__); ?> cff-metabox <?php print esc_attr($cpcff_main->metabox_status( 'metabox_ip2location_addon_settings' ) ); ?>" >
					<h3 class='hndle' style="padding:5px;"><span><?php print $this->name; ?></span></h3>
					<div class="inside">
						<table cellspacing="0" style="width:100%;">
							<tr>
								<td style="font-weight:bold;">
								<?php
								_e('Database file', 'calculated-fields-form');
								?>
								</td>
							</tr>
							<tr>
								<td>
									<?php _e('Upload the BIN file, with the database distributed by ip2location, to the WordPress directory: "/wp-content/uploads" through FTP, and enter the file name in the following input box', 'calculated-fields-form'); ?>:
								</td>
							</tr>
							<tr>
								<td>
									<b>For IPV4:</b> <input type="text" name="cpcff_ip2location_data[file_ipv4]" style="min-width:50%;" value="<?php
										echo esc_attr((!empty($cpcff_ip2location_data['file'])) ? $cpcff_ip2location_data['file'] : ((!empty($cpcff_ip2location_data['file_ipv4'])) ? $cpcff_ip2location_data['file_ipv4'] : ''));
									?>" />
								</td>
							</tr>
							<tr>
								<td>
									<b>For IPV6:</b> <input type="text" name="cpcff_ip2location_data[file_ipv6]" style="min-width:50%;" value="<?php
										echo esc_attr( !empty($cpcff_ip2location_data['file_ipv6']) ? $cpcff_ip2location_data['file_ipv6'] : '');
									?>" />
								</td>
							</tr>
							<tr>
								<td style="font-weight:bold;">
								<?php
								_e('Get from each submission', 'calculated-fields-form');
								?>
								</td>
							</tr>
							<tr>
								<td>
									<table cellspacing="0">
										<tr>
											<td>
												<table cellspacing="0">
													<tr>
														<td>
															<label>
															<input type="checkbox" name="cpcff_ip2location_data[ipNumber]" <?php echo ( !empty( $cpcff_ip2location_data[ 'ipNumber' ] ) ) ? 'CHECKED' : ''; ?> />
															<?php _e('IP Number', 'calculated-fields-form');?>
															</label>
														</td>
													</tr>
													<tr>
														<td>
															<label>
															<input type="checkbox" name="cpcff_ip2location_data[ipVersion]" <?php echo ( !empty( $cpcff_ip2location_data[ 'ipVersion' ] ) ) ? 'CHECKED' : ''; ?> />
															<?php _e('IP Version', 'calculated-fields-form');?>
															</label>
														</td>
													</tr>
													<tr>
														<td>
															<label>
															<input type="checkbox" name="cpcff_ip2location_data[ipAddress]" <?php echo ( !empty( $cpcff_ip2location_data[ 'ipAddress' ] ) ) ? 'CHECKED' : ''; ?> />
															<?php _e('IP Address', 'calculated-fields-form');?>
															</label>
														</td>
													</tr>
													<tr>
														<td>
															<label>
															<input type="checkbox" name="cpcff_ip2location_data[countryCode]" <?php echo ( !empty( $cpcff_ip2location_data[ 'countryCode' ] ) ) ? 'CHECKED' : ''; ?> />
															<?php _e('Country Code', 'calculated-fields-form');?>
															</label>
														</td>
													</tr>
													<tr>
														<td>
															<label>
															<input type="checkbox" name="cpcff_ip2location_data[countryName]" <?php echo ( !empty( $cpcff_ip2location_data[ 'countryName' ] ) ) ? 'CHECKED' : ''; ?> />
															<?php _e('Country Name', 'calculated-fields-form');?>
															</label>
														</td>
													</tr>
													<tr>
														<td>
															<label>
															<input type="checkbox" name="cpcff_ip2location_data[regionName]" <?php echo ( !empty( $cpcff_ip2location_data[ 'regionName' ] ) ) ? 'CHECKED' : ''; ?> />
															<?php _e('Region Name', 'calculated-fields-form');?>
															</label>
														</td>
													</tr>
												</table>
											</td>
											<td>
												<table cellspacing="0">
													<tr>
														<td>
															<label>
															<input type="checkbox" name="cpcff_ip2location_data[cityName]" <?php echo ( !empty( $cpcff_ip2location_data[ 'cityName' ] ) ) ? 'CHECKED' : ''; ?> />
															<?php _e('City Name', 'calculated-fields-form');?>
															</label>
														</td>
													</tr>
													<tr>
														<td>
															<label>
															<input type="checkbox" name="cpcff_ip2location_data[latitude]" <?php echo ( !empty( $cpcff_ip2location_data[ 'latitude' ] ) ) ? 'CHECKED' : ''; ?> />
															<?php _e('Latitude', 'calculated-fields-form');?>
															</label>
														</td>
													</tr>
													<tr>
														<td>
															<label>
															<input type="checkbox" name="cpcff_ip2location_data[longitude]" <?php echo ( !empty( $cpcff_ip2location_data[ 'longitude' ] ) ) ? 'CHECKED' : ''; ?> />
															<?php _e('Longitude', 'calculated-fields-form');?>
															</label>
														</td>
													</tr>
													<tr>
														<td>
															<label>
															<input type="checkbox" name="cpcff_ip2location_data[areaCode]" <?php echo ( !empty( $cpcff_ip2location_data[ 'areaCode' ] ) ) ? 'CHECKED' : ''; ?> />
															<?php _e('Area Code', 'calculated-fields-form');?>
															</label>
														</td>
													</tr>
													<tr>
														<td>
															<label>
															<input type="checkbox" name="cpcff_ip2location_data[iddCode]" <?php echo ( !empty( $cpcff_ip2location_data[ 'iddCode' ] ) ) ? 'CHECKED' : ''; ?> />
															<?php _e('IDD Code', 'calculated-fields-form');?>
															</label>
														</td>
													</tr>
													<tr>
														<td>
															<label>
															<input type="checkbox" name="cpcff_ip2location_data[weatherStationCode]" <?php echo ( !empty( $cpcff_ip2location_data[ 'weatherStationCode' ] ) ) ? 'CHECKED' : ''; ?> />
															<?php _e('Weather Station Code', 'calculated-fields-form');?>
															</label>
														</td>
													</tr>
												</table>
											</td>
											<td>
												<table cellspacing="0">
													<tr>
														<td>
															<label>
															<input type="checkbox" name="cpcff_ip2location_data[weatherStationName]" <?php echo ( !empty( $cpcff_ip2location_data[ 'weatherStationName' ] ) ) ? 'CHECKED' : ''; ?> />
															<?php _e('Weather Station Name', 'calculated-fields-form');?>
															</label>
														</td>
													</tr>
													<tr>
														<td>
															<label>
															<input type="checkbox" name="cpcff_ip2location_data[mcc]" <?php echo ( !empty( $cpcff_ip2location_data[ 'mcc' ] ) ) ? 'CHECKED' : ''; ?> />
															<?php _e('MCC', 'calculated-fields-form');?>
															</label>
														</td>
													</tr>
													<tr>
														<td>
															<label>
															<input type="checkbox" name="cpcff_ip2location_data[mnc]" <?php echo ( !empty( $cpcff_ip2location_data[ 'mnc' ] ) ) ? 'CHECKED' : ''; ?> />
															<?php _e('MNC', 'calculated-fields-form');?>
															</label>
														</td>
													</tr>
													<tr>
														<td>
															<label>
															<input type="checkbox" name="cpcff_ip2location_data[mobileCarrierName]" <?php echo ( !empty( $cpcff_ip2location_data[ 'mobileCarrierName' ] ) ) ? 'CHECKED' : ''; ?> />
															<?php _e('Mobile Carrier', 'calculated-fields-form');?>
															</label>
														</td>
													</tr>
													<tr>
														<td>
															<label>
															<input type="checkbox" name="cpcff_ip2location_data[usageType]" <?php echo ( !empty( $cpcff_ip2location_data[ 'usageType' ] ) ) ? 'CHECKED' : ''; ?> />
															<?php _e('Usage Type', 'calculated-fields-form');?>
															</label>
														</td>
													</tr>
													<tr>
														<td>
															<label>
															<input type="checkbox" name="cpcff_ip2location_data[elevation]" <?php echo ( !empty( $cpcff_ip2location_data[ 'elevation' ] ) ) ? 'CHECKED' : ''; ?> />
															<?php _e('Elevation', 'calculated-fields-form');?>
															</label>
														</td>
													</tr>
												</table>
											</td>
											<td>
												<table cellspacing="0">
													<tr>
														<td>
															<label>
															<input type="checkbox" name="cpcff_ip2location_data[netSpeed]" <?php echo ( !empty( $cpcff_ip2location_data[ 'netSpeed' ] ) ) ? 'CHECKED' : ''; ?> />
															<?php _e('Net Speed', 'calculated-fields-form');?>
															</label>
														</td>
													</tr>
													<tr>
														<td>
															<label>
															<input type="checkbox" name="cpcff_ip2location_data[timeZone]" <?php echo ( !empty( $cpcff_ip2location_data[ 'timeZone' ] ) ) ? 'CHECKED' : ''; ?> />
															<?php _e('Time Zone', 'calculated-fields-form');?>
															</label>
														</td>
													</tr>
													<tr>
														<td>
															<label>
															<input type="checkbox" name="cpcff_ip2location_data[zipCode]" <?php echo ( !empty( $cpcff_ip2location_data[ 'zipCode' ] ) ) ? 'CHECKED' : ''; ?> />
															<?php _e('ZIP Code', 'calculated-fields-form');?>
															</label>
														</td>
													</tr>
													<tr>
														<td>
															<label>
															<input type="checkbox" name="cpcff_ip2location_data[domainName]" <?php echo ( !empty( $cpcff_ip2location_data[ 'domainName' ] ) ) ? 'CHECKED' : ''; ?> />
															<?php _e('Domain Name', 'calculated-fields-form');?>
															</label>
														</td>
													</tr>
													<tr>
														<td>
															<label>
															<input type="checkbox" name="cpcff_ip2location_data[isp]" <?php echo ( !empty( $cpcff_ip2location_data[ 'isp' ] ) ) ? 'CHECKED' : ''; ?> />
															<?php _e('ISP Name', 'calculated-fields-form');?>
															</label>
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
						<input type="submit" name="Save settings" class="button-secondary" />
					</div>
					<input type="hidden" name="cpcff_ip2location" value="1" />
					<input type="hidden" name="_cpcff_nonce" value="<?php echo wp_create_nonce( $this->addonID ); ?>" />
				</div>
			</form>
			<?php
		}

		/************************ ADDON CODE *****************************/
		/************************ ATTRIBUTES *****************************/

		private $titles = array(
			'ipNumber' 				=> 'IP Number',
            'ipVersion' 			=> 'IP Version',
            'ipAddress' 			=> 'IP Address',
			'countryCode' 			=> 'Country Code',
			'countryName' 			=> 'Country Name',
			'regionName' 			=> 'Region Name',
            'cityName' 				=> 'City Name',
            'latitude' 				=> 'Latitude',
            'longitude' 			=> 'Longitude',
            'areaCode' 				=> 'Area Code',
            'iddCode' 				=> 'IDD Code',
			'weatherStationCode' 	=> 'Weather Station Code',
			'weatherStationName' 	=> 'Weather Station Name',
            'mcc' 					=> 'MCC',
            'mnc' 					=> 'MNC',
			'mobileCarrierName' 	=> 'Mobile Carrier',
            'usageType' 			=> 'Usage Type',
            'elevation' 			=> 'Elevation',
            'netSpeed' 				=> 'Net Speed',
            'timeZone' 				=> 'Time Zone',
            'zipCode' 				=> 'ZIP Code',
			'domainName' 			=> 'Domain Name',
            'isp' 					=> 'ISP Name'
		);

        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = __("The add-on allows identify additional data of users, and receive the data in the notification email", 'calculated-fields-form');

            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;

			// Determines the user information and returns all their data.
			add_action( 'cpcff_additional_information', array( &$this, 'get_information'), 1, 2 );
			add_action( 'cpcff_process_data_before_insert', array( &$this, 'before_insert'), 1, 3 );
			add_action( 'cpcff_message_additional_details', array( &$this, 'message_additional_details') );
			add_filter( 'cpcff_custom_tags', array( &$this, 'replace_tags' ), 1, 2 );
		} // End __construct

        /************************ PUBLIC METHODS  *****************************/

		/**
         * Instanciates the IP2Location API to get the user information, if there is not related information returns its * basic data, received as parameter. The user ip is passed as the second parameter.
         */
		public function get_location_data( $ip, $context = 'raw' )
		{
			error_reporting(E_ERROR|E_PARSE);
			$data = array();
			$cpcff_ip2location_data = get_option( 'cpcff_ip2location_data', array() );
			if(
				!empty($cpcff_ip2location_data) &&
				(
					!empty($cpcff_ip2location_data['file']) || // file is for compatibility
					!empty($cpcff_ip2location_data['file_ipv4']) ||
					!empty($cpcff_ip2location_data['file_ipv6'])
				)
			)
			{
				$upload_dir = wp_upload_dir();
				if(!$upload_dir['error'])
				{
					$ip_type = (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) ? 6 : 4;
					$file = '';

					if(
						!empty($cpcff_ip2location_data['file']) &&
						file_exists( $upload_dir[ 'basedir' ].'/'.$cpcff_ip2location_data[ 'file' ] )
					) $file = $cpcff_ip2location_data[ 'file' ];

					if(
						!empty($cpcff_ip2location_data['file_ipv4']) &&
						file_exists( $upload_dir[ 'basedir' ].'/'.$cpcff_ip2location_data[ 'file_ipv4' ] )
					) $file = $cpcff_ip2location_data[ 'file_ipv4' ];

					if(
						($ip_type == 6 || empty($file)) &&
						!empty($cpcff_ip2location_data['file_ipv6']) &&
						file_exists( $upload_dir[ 'basedir' ].'/'.$cpcff_ip2location_data[ 'file_ipv6' ] )
					) $file = $cpcff_ip2location_data[ 'file_ipv6' ];

					if(!empty($file))
					{
						$new_data = "";
						require_once dirname( __FILE__ ).'/ip2location.addon/IP2Location.php';

						$db = new \IP2Location\Database( $upload_dir[ 'basedir' ].'/'.$file, \IP2Location\Database::FILE_IO);

						$records = $db->lookup($ip, \IP2Location\Database::ALL);
						if( !empty( $records ) )
						{
							foreach( $cpcff_ip2location_data as $key => $value )
							{
								if( !empty( $records[ $key ] ) )
								{
									$attr_name = $context == 'view' && ! empty( $this->titles[ $key ] ) ? $this->titles[ $key ] : $key;

									$data[ $attr_name ] = $records[ $key ];
								}
							}
						}
					}
				}
			}
			return $data;

		} // End get_information

        public function get_information( $data, $ip )
		{
			$data_arr = $this->get_location_data( $ip, 'view' );
			$new_data = '';
			foreach ( $data_arr as $key => $value ) {
				$new_data .= $key . ": " . $value . "\n";
			}
			return empty( $new_data ) ? $data : $new_data;
		} // End get_information

		public function before_insert( &$params, &$str, $fields )
		{
			$ip_address = empty( $_SERVER['REMOTE_ADDR'] ) ? '' : $_SERVER['REMOTE_ADDR'];
			if ( ! empty( $ip_address ) ) {
				$data = $this->get_location_data( $ip_address );
				foreach ( $data as $key => $value ) {
					if ( ! isset( $params[ $key ] ) && ! empty( $value ) ) {
						$params[ $key ] = $value;
					}
				}
			}
		} // End before_insert

		public function message_additional_details( $event ) {
			$flag = false;
			$additional_tags = function( $titles, $data ) use ( &$flag) {
				foreach ( $titles as $key => $value ) {
					if ( isset( $data[ $key ] ) && 'Invalid IP address.' != $data[ $key ] ) {
						$flag = true;
						print "<br>" . esc_html( $value ) . ": " . esc_html( $data[ $key ] );
					}
				}
			};

			if( false != ( $data = unserialize( $event->paypal_post ) ) ) {
				$additional_tags( $this->titles, $data );
				if ( ! $flag && ! empty( $event->ipaddr ) && '::1' != $event->ipaddr ) {
					$data = $this->get_location_data( $event->ipaddr );
					$text = $additional_tags( $this->titles, $data );
				}
			}
		} // End message_additional_details

		public function replace_tags( $text, $submission_id )
		{
			$flag = false;
			$replace_tags = function( $text, $titles, $data ) use ( &$flag) {
				foreach ( $titles as $key => $value ) {
					if ( isset( $data[ $key ] ) ) { $flag = true; }
					$text = str_ireplace( "<%{$key}%>", ( isset( $data[ $key ] ) && 'Invalid IP address.' != $data[ $key ] ? $data[ $key ] : '' ), $text );
				}
				return $text;
			};

			$submission = CPCFF_SUBMISSIONS::get( $submission_id );
			$text_bk 	= $text;
			$text 		= $replace_tags( $text, $this->titles, $submission->paypal_post );

			if ( ! $flag && ! empty( $submission->ipaddr ) && '::1' != $submission->ipaddr ) {
				$text = $text_bk;
				$data = $this->get_location_data( $submission->ipaddr );
				$text = $replace_tags( $text, $this->titles, $data );
			}
			return $text;
		} // End replace_tags

	} // End Class

    // Main add-on code
    $cpcff_ip2location_obj = new CPCFF_IP2LOCATION();

	// Add addon object to the objects list
	CPCFF_ADDONS::add($cpcff_ip2location_obj);
}
?>