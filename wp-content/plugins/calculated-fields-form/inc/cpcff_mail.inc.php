<?php
/**
 * CPCFF_MAIL process and sends the notification and confirmation emails
 *
 * @package CFF.
 * @since 5.0.216 (PRO), 5.0.257 (DEV), 10.0.288 (PLA)
 */

if(!class_exists('CPCFF_MAIL'))
{
	/**
	 * Class that sends the notification emails processing first the emails and subjects
	 *
	 * @since 5.0.215 (PRO), 5.0.256 (DEV), 10.0.287 (PLA)
	 */
	class CPCFF_MAIL
	{
		/**
		 * Submission ID
		 *
		 * @var int $_id
		 */
		private $_submission_obj;
		private $_form_obj;
		private $_from;
		private $_phpmailer_from;

		public function __construct()
		{
			add_action('wp_mail_failed', array($this, 'debug_email'));
		} // End __construct

		private function _default_if_empty($message, $default)
		{
			$processed = preg_replace('/[\t\s\n\r]/', '', $message);
			return (!empty($processed)) ?  $message : $default;
		} // End _default_if_empty

		private function _fix_encoding($str, $base64 = true)
		{
			$_fix = get_option( 'CP_CALCULATEDFIELDSF_ENCODING_EMAIL', false );
			if($_fix)
			{
				$str = mb_convert_encoding($str, "ISO-8859-2");
				if($base64) $str = chunk_split(base64_encode($str));
			}
			return $str;
		} // End _fix_encoding

		private function _modify_encoding_header($headers)
		{
			$_encoding = get_option( 'CP_CALCULATEDFIELDSF_ENCODING_EMAIL', false );
			if($_encoding) $headers[] = "Content-Transfer-Encoding: base64";
			return $headers;
		} // End _modify_encoding_header

		private function _attachment_url_to_path( $url )
		{
			$upload_directory = wp_get_upload_dir();
			if ( false == $upload_directory['error'] ) {
				$path = str_ireplace( $upload_directory['baseurl'], $upload_directory['basedir'], $url );
				if ( file_exists( $path ) ) return $path;
			}
			return false;
		} // End _attachment_url_to_path

		/**
		 * Creates an entry in the error logs if there is an error sending emails
		 */
		public function debug_email($error)
		{
			error_log($error->get_error_message());
		} // End debug_email

		/**
		 * Send the notification emails to the email address entered through the form's settings
		 *
		 *
		 * @return boolean.
		 */
		public function send_notification_email($submission_id)
		{
			$this->_init($submission_id);
			$submission_obj = $this->_submission_obj;

            $send_notification_email = apply_filters(
                'cpcff_send_notification_email',
                true,
                $this->_submission_obj,
                $this->_form_obj
            );

			if($submission_obj && $send_notification_email)
			{
				$form_obj = $this->_form_obj;

				$fields = $form_obj->get_fields();
				$fields[ 'ipaddr' ] = $submission_obj->ipaddr;
				$fields[ 'submission_datetime' ] = $submission_obj->time;
				$fields[ 'paid' ] = $submission_obj->paid;

				add_filter('phpmailer_init', array($this, 'phpmailer_init'));

				// Checks if the notification email includes a content
				$email_message = $this->_default_if_empty($form_obj->get_option('fp_message', CP_CALCULATEDFIELDSF_DEFAULT_fp_message), CP_CALCULATEDFIELDSF_DEFAULT_fp_message);

				$email_data = CPCFF_AUXILIARY::parsing_fields_on_text(
					$fields,
					$submission_obj->paypal_post,
					$email_message,
					$submission_obj->data,
					$form_obj->get_option('fp_emailformat', CP_CALCULATEDFIELDSF_DEFAULT_email_format),
					$submission_id
				);


				if ('true' == $form_obj->get_option('fp_inc_additional_info', CP_CALCULATEDFIELDSF_DEFAULT_fp_inc_additional_info))
				{
					$chln = "\n";
					if($form_obj->get_option('fp_emailformat', CP_CALCULATEDFIELDSF_DEFAULT_email_format) == 'html') $chln = "<br>";

					$basic_data = "IP: ".$submission_obj->ipaddr."{$chln}Server Time:  ".date("Y-m-d H:i:s").$chln;
					/**
					 *	Includes additional information to the email's message,
					 *  are passed two parameters: the basic information, and the IP address
					 */
					$basic_data = apply_filters( 'cpcff_additional_information',  $basic_data, $submission_obj->ipaddr );
					$email_data[ 'text' ] .= "{$chln}ADDITIONAL INFORMATION{$chln}*********************************{$chln}".$basic_data;
				}

				$subject = $form_obj->get_option('fp_subject', CP_CALCULATEDFIELDSF_DEFAULT_fp_subject);
				$subject = CPCFF_AUXILIARY::parsing_fields_on_text(
					$fields,
					$submission_obj->paypal_post,
					$subject,
					'',
					'plain text',
					$submission_id
				);

				$to = CPCFF_AUXILIARY::parsing_fields_on_text(
					$fields,
					$submission_obj->paypal_post,
					preg_replace("/(fieldname\d+)\s*%>/i", "$1_value%>", $form_obj->get_option('fp_destination_emails', CP_CALCULATEDFIELDSF_DEFAULT_fp_destination_emails)),
					'',
					'plain text',
					$submission_id
				);

				$to = explode(
					",",
					$to['text']
				);

				if ('html' == $form_obj->get_option('fp_emailformat', CP_CALCULATEDFIELDSF_DEFAULT_email_format))
				{
					$content_type = "Content-Type: text/html; charset=utf-8";
				}
				else $content_type = "Content-Type: text/plain; charset=utf-8";

				$replyto = explode( ',', $submission_obj->notifyto );

				if ($form_obj->get_option('fp_emailfrommethod', "fixed") == "customer" && !empty( $replyto ) )
					$from = trim($replyto[0]);

				if(empty($from)) $from = $this->_get_from();
				$this->_phpmailer_from = $from;

				if ( !empty($from) && strpos($from,">") === false ) $from = '"'.$from.'" <'.$from.'>';
				if ( !$form_obj->get_option('fp_inc_attachments', 0) ) $email_data[ 'files' ] = array();

				/**
				 * Attach or modify attached files,
				 * Example for adding ical or PDF attachments
				*/
				$email_data[ 'files' ] = apply_filters( 'cpcff_notification_email_attachments',  $email_data[ 'files' ], $submission_obj->paypal_post, $form_obj->get_id(), $submission_id);

				$replyto = str_replace(' ', '', implode( ',', $replyto ));

				$subject['text'] = self::_fix_encoding($subject['text'], false);
				$email_data['text'] = self::_fix_encoding($email_data['text']);

                $headers = array(
                    $content_type,
                    "X-Mailer: PHP/" . phpversion()
                );
                if(!empty($from)) $headers[] = "From: {$from}";

                if(!empty($replyto)) $headers[] = "Reply-To: ".$replyto;
				$headers = self::_modify_encoding_header($headers);

				// Static file attachment
				$fp_attach_static = $form_obj->get_option( 'fp_attach_static', '' );
				if ( ! empty( $fp_attach_static ) && false != ( $static_file = $this->_attachment_url_to_path( $fp_attach_static ) ) ) {
					$email_data['files'][] = $static_file;
				}

				foreach ($to as $item)
				{
					if (trim($item) != '')
					{
						try
						{
							wp_mail(
								trim($item),
								apply_filters('cpcff_notification_email_subject', $subject[ 'text' ], $submission_id),
								apply_filters('cpcff_notification_email_message', $email_data[ 'text' ], $submission_id),
								$headers,
                                $email_data[ 'files' ]
							);
						}
						catch( Exception $mail_err ){}
					}
				}
				do_action('cpcff_notification_email_sent', $submission_id);
				remove_filter('phpmailer_init', array($this,'phpmailer_init'));
			}
		} // End send_notification_email

		/**
		 * Sends the copy email to the users (using the email address submitted through the form),
		 * or to the email address passed as parameter
		 *
		 * @param string $email email address, default an empty string.
		 * @return boolean.
		 */
		public function send_confirmation_email( $submission_id, $email = '' )
		{
			$this->_init($submission_id);
			$submission_obj = $this->_submission_obj;

            $send_confirmation_email = apply_filters(
                'cpcff_send_confirmation_email',
                true,
                $this->_submission_obj,
                $this->_form_obj
            );

			if($submission_obj && $send_confirmation_email)
			{
				$form_obj = $this->_form_obj;
				$notifyto = explode( ',', $submission_obj->notifyto ); // Allows send multiple notification emails.

				if(
					(!empty($notifyto) || $email != '') &&
					'true' == $form_obj->get_option('cu_enable_copy_to_user', CP_CALCULATEDFIELDSF_DEFAULT_cu_enable_copy_to_user)
				)
				{
					$fields = $form_obj->get_fields();
					$fields[ 'ipaddr' ] = $submission_obj->ipaddr;
					$fields[ 'submission_datetime' ] = $submission_obj->time;
					$fields[ 'paid' ] = $submission_obj->paid;

					add_filter('phpmailer_init', array($this, 'phpmailer_init'));

                    // Checks if the notification email includes a content
                    $email_message = $this->_default_if_empty($form_obj->get_option('cu_message', CP_CALCULATEDFIELDSF_DEFAULT_cu_message), CP_CALCULATEDFIELDSF_DEFAULT_cu_message);

					$email_data = CPCFF_AUXILIARY::parsing_fields_on_text(
                        $fields,
                        $submission_obj->paypal_post,
                        $email_message,
                        $submission_obj->data,
                        $form_obj->get_option('cu_emailformat', CP_CALCULATEDFIELDSF_DEFAULT_email_format),
                        $submission_id
                    );

					$subject = $form_obj->get_option('cu_subject', CP_CALCULATEDFIELDSF_DEFAULT_cu_subject);
					$subject = CPCFF_AUXILIARY::parsing_fields_on_text(
						$fields,
						$submission_obj->paypal_post,
						$subject,
						'',
						'plain text',
						$submission_id
					);

					if ('html' == $form_obj->get_option('cu_emailformat', CP_CALCULATEDFIELDSF_DEFAULT_email_format))
					{
						$content_type = "Content-Type: text/html; charset=utf-8";
					}
					else $content_type = "Content-Type: text/plain; charset=utf-8";

					$from = $this->_get_from();
					$this->_phpmailer_from = $from;

					if ( !empty($from) && strpos($from,">") === false ) $from = '"'.$from.'" <'.$from.'>';
					if ( !in_array( $email, $notifyto ) && $email != '') $notifyto[] = $email;
					if ( !empty( $notifyto ) )
					{
						/**
						 * Attach or modify attached files,
						 * Example for adding ical or PDF attachments
						*/
						$email_data[ 'files' ] = array();
						$email_data[ 'files' ] = apply_filters( 'cpcff_confirmation_email_attachments',  $email_data[ 'files' ], $submission_obj->paypal_post, $form_obj->get_id(), $submission_id);

						$subject['text'] = self::_fix_encoding($subject['text'], false);
						$email_data['text'] = self::_fix_encoding($email_data['text']);

                        $headers = array(
                            $content_type,
                            "X-Mailer: PHP/" . phpversion()
                        );
                        if(!empty($from)) $headers[] = "From: {$from}";

                        $headers = self::_modify_encoding_header($headers);

						// Static file attachment
						$cu_attach_static = $form_obj->get_option( 'cu_attach_static', '' );
						if ( ! empty( $cu_attach_static ) && false != ( $static_file = $this->_attachment_url_to_path( $cu_attach_static ) ) ) {
							$email_data['files'][] = $static_file;
						}

						foreach( $notifyto as $email_address )
						{
							try
							{
								$email_address = trim($email_address);
								if(!empty($email_address))
								{
									wp_mail(
										trim($email_address),
										apply_filters('cpcff_confirmation_email_subject', $subject[ 'text' ], $submission_id),
										apply_filters('cpcff_confirmation_email_message', $email_data[ 'text' ], $submission_id),
										$headers,
                                        $email_data[ 'files' ]
									);
								}
							}
							catch( Exception $mail_err ){}
						}
						do_action('cpcff_user_email_sent', $submission_id);
					}
				}
				remove_filter('phpmailer_init',array($this, 'phpmailer_init'));
			}
		} // End send_confirmation_email

		public function phpmailer_init( $phpmailer )
		{
			// Checks if the email's headers should be corrected or not
			if( !get_option( 'CP_CALCULATEDFIELDSF_EMAIL_HEADERS', false ) ) return $phpmailer;

			$from = trim((!empty($this->_phpmailer_from)) ? $this->_phpmailer_from : $this->_get_from());
            if(!empty($from))
            {
                $from = strtolower($from);

                $parts 		= explode('@', $from);
                $home_url 	= CPCFF_AUXILIARY::site_url();

                if(
                    strtolower( $phpmailer->Mailer ) == 'smtp' ||
                    count( $parts ) != 2 ||
                    strpos( $home_url, $parts[ 1 ] ) === false
                ) return $phpmailer;

                $phpmailer->Sender 	= $from;
                $phpmailer->From 	= $from;
            }

			return $phpmailer;
		} // End phpmailer_init

		private function _init( $submission_id )
		{
			$this->_submission_obj = CPCFF_SUBMISSIONS::get($submission_id);
			if($this->_submission_obj) $this->_form_obj = CPCFF_SUBMISSIONS::get_form($submission_id);
		} // End _init

		private function _get_from()
		{
			if(empty($this->_from))
			{
				$from = CPCFF_AUXILIARY::parsing_fields_on_text(
					$this->_form_obj->get_fields(),
					$this->_submission_obj->paypal_post,
					preg_replace(
						"/(fieldname\d+)\s*%>/i",
						"$1_value%>",
						$this->_form_obj->get_option('fp_from_email', CP_CALCULATEDFIELDSF_DEFAULT_fp_from_email)
					),
					'',
					'plain text',
					$this->_submission_obj->id
				);
				$this->_from = trim($from['text']);
			}

			return $this->_from;
		} // End _get_from

	} // End CPCFF_MAIL
}