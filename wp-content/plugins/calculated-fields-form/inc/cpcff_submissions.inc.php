<?php
/**
 * Manage the forms' submissions with the database interaction, data, and methods.
 *
 * The class is static for sharing the submissions with multiple classes in the plugin and reduce the accesses to database.
 *
 * @package CFF.
 * @since 5.0.216 (PRO), 5.0.257 (DEV), 10.0.288 (PLA)
 */

if(!class_exists('CPCFF_SUBMISSIONS'))
{
	/**
	 * Class to insert, update, and read the submissions data.
	 *
	 * @since 5.0.216 (PRO), 5.0.257 (DEV), 10.0.288 (PLA)
	 */
	class CPCFF_SUBMISSIONS
	{
		/**
		 * Submissions list
		 * Class property.
		 *
		 * @var array $_list, array of objects
		 */
		static private $_list = array();

		static private $_structure;
		/*********************************** PUBLIC METHODS  ********************************************/

		/**
		 * Populates the $_list property with the submissions reading them from the database
		 *
		 * @param string $query, SQL query for reading the submissions.
		 * @return void.
		 */
		static public function populate($query)
		{
			global $wpdb;

			$rows = $wpdb->get_results($query);
			if(!empty($rows))
			{
				foreach($rows as $row)
				{
					if(isset($row->id))
					{
						self::$_list[$row->id] = $row;
					}
				}
			}
			return $rows;
		} // End populate

		/**
		 * Returns a submission object or false.
		 *
		 * @param integer, the submission's id.
		 * @return mixed, return the submission object or false.
		 */
		static public function get($submission_id)
		{
			global $wpdb;

			// if the submission object has not been read, or has been read partially, reads it again.
			if(
				empty(self::$_list[$submission_id]) ||
				empty(self::$_list[$submission_id]->paypal_post)
			)
			{
				$query = $wpdb->prepare(
							'SELECT * FROM `'.CP_CALCULATEDFIELDSF_POSTS_TABLE_NAME.'` WHERE id=%d',
							$submission_id
						);
				self::populate($query);
				if(empty(self::$_list[$submission_id])) return false;
			}

			// Unserialize the submitted fields if it is a text
			if(is_string(self::$_list[$submission_id]->paypal_post))
			{
				self::$_list[$submission_id]->paypal_post = (($tmp = unserialize(self::$_list[$submission_id]->paypal_post)) !== false) ? $tmp : array();
			}
			return self::$_list[$submission_id];
		} // end get

		/**
		 * Returns the form associated to a submission.
		 *
		 * If the submission exists it returns the corresponding form's object if there is the CPCFF_MAIN class,
		 * the form's id if the CPCFF_MAIN class does not exists, or false if the submisssion id is invalid.
		 *
		 * @param integer $submission_id, id of submission.
		 * @return mixed, the form's object, the form's id, or false.
		 */
		static public function get_form($submission_id)
		{
			$submission = self::get($submission_id);

			// The submission was read with a query that does not include the form's id.
			if(
				!empty($submission) &&
				empty($submission->formid)
			)
			{
				unset(self::$_list[$submission_id]);
				$submission = self::get($submission_id);
			}

			if(!empty($submission))
			{
				if(class_exists('CPCFF_MAIN'))
				{
					$cpcff_main = CPCFF_MAIN::instance();
					return $cpcff_main->get_form($submission->formid);
				}
				return $submission->formid;
			}
			return false;
		} // End get_form

		/**
		 * Inserts the submission in the database.
		 *
		 * @param array, associative array with the submission data.
		 * @return mixes, the submission' id or false.
		 */
		static public function insert($data)
		{
			global $wpdb;
			self::_init();

			$data = self::_clear($data);

			if(!empty($data))
			{
				if(empty($data['paypal_post'])) $data['paypal_post'] = array();
				if(!is_string($data['paypal_post'])) $data['paypal_post'] = serialize($data['paypal_post']);

				$format = self::_format($data);

				if(
					$wpdb->insert(
						CP_CALCULATEDFIELDSF_POSTS_TABLE_NAME,
						$data,
						$format
					)
				)
				{
					return $wpdb->insert_id;
				}
			}
			return false;
		} // End insert

		/**
		 * Updates the submission's data in the database.
		 *
		 * Calls the  cpcff_update_submission action.
		 *
		 * @param integer, the submission's id.
		 * @param mixed, associative array with the submission data or a stdClass.
		 * @return mixed, the submission' id or false.
		 */
		static public function update($submission_id, $data)
		{
			global $wpdb;
			self::_init();

			if(is_object($data)) $data = (array)$data;

			$data = self::_clear($data);

			if(!empty($data))
			{
				if(
					isset($data['paypal_post']) &&
					!is_string($data['paypal_post'])
				)
				$data['paypal_post'] = serialize($data['paypal_post']);

				$format = self::_format($data);

				unset(self::$_list[$submission_id]);

				if(
					$wpdb->update(
						CP_CALCULATEDFIELDSF_POSTS_TABLE_NAME,
						$data,
						array('id'=>$submission_id),
						$format,
						array('%d')
					)
				)
				{
					/**
					 * Action called when a submission is updated, the submission ID is passed as parameter
					 */
					do_action( 'cpcff_update_submission', $submission_id );

					return $submission_id;
				}
			}
			return false;
		} // End update

		/**
		 * Deletes the submission from the submissions database.
		 *
		 * Deletes the database entry and calls the  cpcff_delete_submission action to allow the add-ons can update their databases.
		 *
		 * @param integer $submission_id, id of submission
		 * @return integer, the number of deleted rows.
		 */
		static public function delete($submission_id)
		{
			global $wpdb;
            $obj = self::get($submission_id);

            if($obj != false)
            {
                // Delete the uploaded files if they are not associated with other submissions
                // or they are not indexed by the WordPress media library
                try
                {
                    foreach($obj->paypal_post as $field => $files)
                    {
                        if(preg_match('/(fieldname\d+)_link/', $field, $matches))
                        {
                            if(!empty($obj->paypal_post[$matches[1].'_url']))
                            {
                                $urls = $obj->paypal_post[$matches[1].'_url'];
                                foreach($urls as $index => $url)
                                {
                                    $c = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '.CP_CALCULATEDFIELDSF_POSTS_TABLE_NAME.' WHERE paypal_post LIKE %s', '%"'.esc_sql($url).'"%'));

                                    if(@intval($c) == 1 && file_exists($files[$index]) && ! is_dir($files[$index]))
                                    {
                                        @unlink($files[$index]);
                                    }
                                }
                            }
                        }
                    }
                }catch(Exception $err){}
            }

			$deleted_rows = $wpdb->delete(
				CP_CALCULATEDFIELDSF_POSTS_TABLE_NAME,
				array('id' => $submission_id),
				array('%d')
			);

			/**
			 * Action called when a submission is deleted, the submission ID is passed as parameter
			 */
			do_action( 'cpcff_delete_submission', $submission_id );

			// Removes the submission from the list
			unset(self::$_list[$submission_id]);

			return ($deleted_rows) ? $deleted_rows : 0;
		} // End delete

		/*********************************** PRIVATE METHODS  ********************************************/

		/**
		 * Initializes the $_structure property with the names of columns and datatype in the submissions database's table.
		 *
		 * @return void.
		 */
		static private function _init()
		{
			self::$_structure = array(
				'id' 		=> '%d',
				'formid' 	=> '%d',
				'time' 		=> '%s',
				'ipaddr' 	=> '%s',
				'notifyto' 	=> '%s',
				'data' 		=> '%s',
				'paypal_post' => '%s',
				'paid'  	=> '%d'
			);
		} // End _init

		/**
		 * Checks if the there are elements not corresponding to columns in the submissions database, and removes them.
		 *
		 * @param array $data, the submissions data.
		 * @return array.
		 */
		static private function _clear($data)
		{
			self::_init();
			foreach($data as $column => $value)
			{
				if(!isset(self::$_structure[$column])) unset($data[$column]);
			}

			return $data;
		} // End _clear

		/**
		 * Returns an arrays with the corresponding format (%d, %s, %f) for the items of the array received as parameter,
		 * and the database structure.
		 *
		 * @param array, Array with items to be inserted in the database.
		 * @return array, Array of formats.
		 */
		static private function _format($data)
		{
			$format = array();
			foreach($data as $column => $value)
			{
				if(isset(self::$_structure[$column])) $format[] = self::$_structure[$column];
			}

			return $format;
		} // End _format

	} // End CPCFF_SUBMISSIONS
}