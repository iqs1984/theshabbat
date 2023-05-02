<?php
if(!defined('WP_DEBUG') || true != WP_DEBUG)
{
	error_reporting(E_ERROR|E_PARSE);
}

add_action( 'setup_theme', array('CPCFF_DATA_SOURCE', 'init_ds'), 11 );

if(!class_exists('CPCFF_DATA_SOURCE'))
{
	class CPCFF_DATA_SOURCE
	{
		private static $cpcff_db_connect;
		private static $cpcff_db_constants;

		private static function init()
		{
			global $wpdb;
			$current_user = wp_get_current_user();
			self::$cpcff_db_constants = array(

				// BLOG CONSTANTS
				'blog.id' => get_current_blog_id(), // current Blog ID

				// DB CONSTANTS
				'wpdb.prefix' 				=> $wpdb->prefix, // database prefix
				'wpdb.comments' 			=> $wpdb->comments, // name of Comments table
				'wpdb.commentmeta' 			=> $wpdb->commentmeta, // name of Comment Metadata table
				'wpdb.links' 				=> $wpdb->links, // name of Links table
				'wpdb.options' 				=> $wpdb->options, // name of Options table
				'wpdb.postmeta' 			=> $wpdb->postmeta, // name of Post Metadata table
				'wpdb.posts' 				=> $wpdb->posts, // name of Posts table
				'wpdb.terms' 				=> $wpdb->terms, // name of Terms table
				'wpdb.term_relationships' 	=> $wpdb->term_relationships, // name of Term Relationships table
				'wpdb.term_taxonomy' 		=> $wpdb->term_taxonomy, // name of Term Taxonomy table
				'wpdb.termmeta' 			=> $wpdb->termmeta, // name of Term Meta table
				'wpdb.usermeta' 			=> $wpdb->usermeta, // name of User Metadata table
				'wpdb.users' 				=> $wpdb->users, // name of Users table
				'wpdb.blogs' 				=> $wpdb->blogs, // name of Multisite Blogs table
				'wpdb.blog_versions' 		=> !empty($wpdb->blog_versions) ? $wpdb->blog_versions : '', // name of Multisite Blog Versions table
				'wpdb.site' 				=> $wpdb->site, // name of Multisite Sites table
				'wpdb.sitecategories' 		=> $wpdb->sitecategories, // name of Multisite Sitewide Terms table
				'wpdb.sitemeta' 			=> $wpdb->sitemeta, // name of Multisite Site Metadata table

				// CURRENT USER PROPERTIES
				'user.id' 			=> $current_user->ID,
				'user.login' 		=> ($current_user->has_prop('user_login')) ? $current_user->user_login: '',
				'user.nicename' 	=> ($current_user->has_prop('user_nicename')) ? $current_user->user_nicename: '',
				'user.email' 		=> ($current_user->has_prop('user_email')) ? $current_user->user_email: '',
				'user.url' 			=> ($current_user->has_prop('user_url')) ? $current_user->user_url: '',
				'user.display_name' => ($current_user->has_prop('display_name')) ? $current_user->display_name: '',
				'user.first_name' 	=> ($current_user->has_prop('first_name')) ? $current_user->first_name: '',
				'user.last_name' 	=> ($current_user->has_prop('last_name')) ? $current_user->last_name: ''
			);
		} // End init

		public static function init_ds()
		{
			if( isset( $_REQUEST[ 'cffaction' ] ) )
			{
				// Initialize class properties
				self::init();

				$_REQUEST = stripslashes_deep( $_REQUEST );
				switch( $_REQUEST[ 'cffaction' ] )
				{
					case 'test_db_connection':
						$_REQUEST[ 'data_source' ] = 'database';
						// $_REQUEST[ 'query' ] = 'SHOW tables';
						try
						{
							$result =  self::ds( $_REQUEST );
						}
						catch(Exception $error_obj)
						{
							$err = $error_obj->getMessage();
						}
						print( ( ( empty( $err ) ) ? 'Connection OK' : $err ) );
						exit;
					break;
					case 'test_db_query':
						if( isset($_REQUEST[ 'active' ]) && $_REQUEST[ 'active' ] == 'structure' )
						{
							if(isset($_REQUEST[ 'table' ])) self::check_for_variable( $_REQUEST[ 'table' ] );
							if(isset($_REQUEST[ 'where' ])) self::check_for_variable( $_REQUEST[ 'where' ] );
						}
						else
						{
							if(isset($_REQUEST[ 'query' ])) self::check_for_variable( $_REQUEST[ 'query' ] );
						}
					case 'get_data_from_database':
						$_REQUEST[ 'data_source' ] = 'database';
						if( isset($_REQUEST[ 'active' ]) && $_REQUEST[ 'active' ] == 'structure' )
						{
							$_REQUEST[ 'query' ] = '';
						}
						try
						{
							$query_result =  self::ds( $_REQUEST );
						}
						catch( Exception $error_obj)
						{
							$err = $error_obj->getMessage();
						}
						if( $_REQUEST[ 'cffaction' ] == 'test_db_query' )
						{
							print_r( ( ( empty( $err ) ) ? $query_result : $err ) );
						}
						else
						{
							$result_obj = new stdClass;
							if( !empty( $err ) )
							{
								$result_obj->error = $err;
							}
							else
							{
								$result_obj->data = $query_result;
							}
							print( json_encode( $result_obj ) );
						}
						exit;
					break;
					case 'get_post_types':
						print json_encode(  get_post_types( array( 'public' => true ) ) );
						exit;
					break;
					case 'get_posts':
						$_REQUEST[ 'data_source' ] = 'post_type';
						$result_obj = new stdClass;
						$result_obj->data = self::ds( $_REQUEST );
						print( json_encode( $result_obj ) );
						exit;
					break;
					case 'get_available_taxonomies':
						print json_encode( get_taxonomies( array('public' => true), 'objects' ) );
						exit;
					break;
					case 'get_taxonomies':
						$_REQUEST[ 'data_source' ] = 'taxonomy';
						$result_obj = new stdClass;
						$result_obj->data = self::ds( $_REQUEST );
						print( json_encode( $result_obj ) );
						exit;
					break;
					case 'get_users':
						$_REQUEST[ 'data_source' ] = 'user';
						$result_obj = new stdClass;
						$result_obj->data = self::ds( $_REQUEST );
						print( json_encode( $result_obj ) );
						exit;
					break;
					case 'get_csv_headers':
						if( is_admin() )
						{
							$_REQUEST[ 'data_source' ] = 'csv';
							$_REQUEST[ 'return' ] = 'headers';
							if(
								isset($_REQUEST['file']) &&
								base64_encode(base64_decode($_REQUEST['file'], true)) === $_REQUEST['file']
							) $_REQUEST['file'] = base64_decode($_REQUEST['file'], true);
							self::ds( $_REQUEST );
							exit;
						}
					break;
					case 'get_csv_rows':
						$_REQUEST[ 'data_source' ] = 'csv';
						$_REQUEST[ 'return' ] = 'rows';
						self::ds( $_REQUEST );
						exit;
					break;
                    case 'get_submissions':
                        $_REQUEST['data_source'] = 'submissions';
                        $result_obj = new stdClass;
						$result_obj->data = self::ds( $_REQUEST );
						print( json_encode( $result_obj ) );
						exit;
                    break;
					case 'get_acf':
						$_REQUEST[ 'data_source' ] = 'acf';
						$result_obj = new stdClass;
						$result_obj->data = self::ds( $_REQUEST );
						print( json_encode( $result_obj ) );
						exit;
					break;
				}
			}

		} // End init_ds

		private static function ds( $data )
		{
			switch( $data[ 'data_source' ] )
			{
				case 'acf':
                    return self::ds_acf( $data );
                break;
                case 'submissions':
                    return self::ds_submissions( $data );
                break;
                case 'database':
					return self::ds_db( $data );
				break;
				case 'csv':
					return self::ds_csv( $data );
				break;
				case 'post_type':
					return self::ds_post_type( $data );
				break;
				case 'taxonomy':
					return self::ds_taxonomy( $data );
				break;
				case 'user':
					return self::ds_user( $data );
				break;
			}
		} // End ds

		/**
			Displays a text about the existence of variables in the query, and stops the script execution.
		**/
		private static function check_for_variable( $str )
		{
			if( preg_match( '/<%[^%]+%>/', $str ) )
			{
				print 'Your query includes variables, so it cannot be tested from the form\'s edition';
				exit;
			}
		} // End check_for_variable

		/**
			Replaces the constants in the $str
		**/
		private static function replace_constants($str)
		{
			foreach(self::$cpcff_db_constants as $name => $value)
			{
				$name = preg_quote("{{$name}}");
				$str = preg_replace('/'.$name.'/i', $value, $str);
			}
			return $str;
		} // End replace_constants

		/**
			Replace variables from the string
		**/
		private static function replace_variables( $str, $vars, $is_query = false )
		{
			global $wpdb;
			if( $is_query )
			{
				$str = str_replace( array( '%', '<%', '%>' ), array( '%%', '<', '>' ), $str );
				$str = str_replace( '%%', '%', $str );
			}
			foreach( $vars as $var => $val )
			{

				$var = '<%'.urldecode( $var ).'%>';
				$val = stripslashes( $val );

				if( $is_query && !is_numeric( $val ) )
				{
					$preg_var = preg_quote($var);
					$val = esc_sql( $val );
					while(strpos($str, $var) !== false)
						$str = preg_replace( '/'.$preg_var.'/', $val, $str, 1 );
				}
				else
				{
					$str = str_replace( $var, $val, $str );
				}
			}
			return ( is_numeric( $str ) ) ? $str*1 : $str;
		} // End replace_variables

		private static function set_attr( &$obj, $attr, $arr, $elem )
		{
			$arr = (array)$arr;
			if( !empty( $elem ) && !empty( $arr[ $elem ] ) )
			{
				$tmp = (array)$obj;
				$tmp[ $attr ] = $arr[ $elem ];
				$obj = (object)$tmp;
			}
		} // End set_attr

        private static function ds_acf( $data )
        {
			if(
				empty( $data[ 'form' ] ) ||
				empty( $data[ 'field' ] ) ||
				! function_exists( 'get_field' ) ||
				! function_exists( 'get_fields' )
			) return false;

			$obj = get_transient(  'cpcff_db_'.$data[ 'form' ].'_'.$data[ 'field' ] );
			if( $obj === false ) return false;

			$field_name = ( property_exists( $obj, 'acfData' ) && property_exists( $obj->acfData, 'field_name' ) ) ? trim( $obj->acfData->field_name ) : '';
			$read_from  = ( property_exists( $obj, 'acfData' ) && property_exists( $obj->acfData, 'read_from' ) ) ? trim( $obj->acfData->read_from ) : '';
			$src_id     = ( property_exists( $obj, 'acfData' ) && property_exists( $obj->acfData, 'src_id' ) ) ? trim( $obj->acfData->src_id ) : '';

			$src_id = self::replace_constants( $src_id );
			$vars 	= ( !empty( $data[ 'vars' ] )  && is_array( $data[ 'vars' ] ) ) ? $data[ 'vars' ] : array();
			$src_id = self::replace_variables( $src_id , $vars );

			if($read_from == 'option') $src_id = 'option';

			$results = array();

			if ( ! empty( $src_id ) ) {
				if ( ! empty( $field_name ) ) {
					$results[] = array( $field_name => get_field( $field_name, $src_id ) );
				} else {
					$results[] = get_fields( $src_id );
				}
			}

			return $results;
		} // End ds_acf

        private static function ds_submissions( $data )
        {
            if( empty( $data[ 'form' ] ) && empty( $data[ 'field' ] ) ) return false;
            $obj = get_transient(  'cpcff_db_'.$data[ 'form' ].'_'.$data[ 'field' ] );
            if( $obj === false ) return false;

            global $wpdb;

            $forms = $obj->messagesData->forms;
            $forms = preg_replace('/[^\d\,]/', '', $forms);
            $forms = preg_replace('/\,+/', ',', $forms);
            $forms = preg_replace('/^\,/', '', $forms);
            $forms = preg_replace('/\,$/', '', $forms);

            $submissions = $obj->messagesData->submissions;
            $submissions = preg_replace('/[^\d\,]/', '', $submissions);
            $submissions = preg_replace('/\,+/', ',', $submissions);
            $submissions = preg_replace('/^\,/', '', $submissions);
            $submissions = preg_replace('/\,$/', '', $submissions);

            $filters = [];
            if(!empty($obj->messagesData->conditions))
            {
                $conditions = trim($obj->messagesData->conditions);
                $conditions_list = explode("\n", $conditions);
                foreach($conditions_list as $condition)
                {
                    $condition = trim($condition);
                    if(empty($condition)) continue;
                    $condition_components = explode('|', $condition);
                    if(count($condition_components)<2) continue;
                    $condition_left_component = trim(array_shift($condition_components));
                    $condition_right_component = trim(implode('|', $condition_components));
                    if(empty($condition_left_component)) continue;
                    if(!isset($filters[$condition_left_component])) $filters[$condition_left_component] = [];
                    if(
                        preg_match('/(fieldname\d+(\|[rv])?)/', $condition_right_component, $matches) &&
                        isset($data['vars'][$matches[1]])
                    ) $filters[$condition_left_component][] = $data['vars'][$matches[1]];
                    else $filters[$condition_left_component][] = $condition_right_component;
                }
            }

            $fields = [];
            if(preg_match_all('/fieldname\d+/i', strtolower($obj->messagesData->fields), $matches)) $fields = $matches[0];
            $logged = $obj->messagesData->logged;
            $paid = isset($obj->messagesData->paid) ? $obj->messagesData->paid : 0;
            $unpaid = isset($obj->messagesData->unpaid) ? $obj->messagesData->unpaid : 0;

            $table_exists = $wpdb->get_results('SHOW TABLES LIKE "'.$wpdb->prefix.'cp_calculated_fields_user_submission"');

            $where = ' WHERE 1=1';
            if(!empty($table_exists) || $logged)
            {
                $query = 'SELECT posts.* FROM '.CP_CALCULATEDFIELDSF_POSTS_TABLE_NAME.' posts LEFT JOIN '.$wpdb->prefix.'cp_calculated_fields_user_submission usersposts ON (posts.id=usersposts.submissionid)';
                $where .= ' AND (usersposts.active=1 OR usersposts.active is NULL)';
                if($logged) $where .= ' AND usersposts.userid = '.get_current_user_id();
            }
            else
            {
                $query = 'SELECT posts.* FROM '.CP_CALCULATEDFIELDSF_POSTS_TABLE_NAME.' posts';
            }

            if(!empty($forms)) $where .= ' AND posts.formid in ('.$forms.')';
            if(!empty($submissions)) $where .= ' AND posts.id in ('.$submissions.')';
            if(!empty($paid) || !empty($unpaid))
            {
                $separator = '';
                $where .= ' AND (';
                if(!empty($paid))
                {
                    $where .= 'posts.paid=1';
                    $separator = ' OR ';
                }
                if(!empty($unpaid)) $where .= $separator.'posts.paid=0';
                $where .= ')';
            }

            if(!empty($obj->messagesData->from))
            {
                if(
                    preg_match('/(fieldname\d+(\|[rv])?)/', $obj->messagesData->from, $matches) &&
                    isset($data['vars'][$matches[1]])
                )
                {
                    if(is_numeric($data['vars'][$matches[1]]))
                        $data['vars'][$matches[1]] = date('Y-m-d', $data['vars'][$matches[1]]*86400);

                    $obj->messagesData->from = '<%'.$matches[1].'%>';
                    $obj->messagesData->from = self::replace_variables($obj->messagesData->from, $data['vars'], true);
                }
                $where .= $wpdb->prepare(' AND %s <= DATE(posts.time)', $obj->messagesData->from);
            }

            if(!empty($obj->messagesData->to))
            {
                if(
                    preg_match('/(fieldname\d+(\|[rv])?)/', $obj->messagesData->to, $matches) &&
                    isset($data['vars'][$matches[1]])
                )
                {
                    if(is_numeric($data['vars'][$matches[1]]))
                        $data['vars'][$matches[1]] = date('Y-m-d', $data['vars'][$matches[1]]*86400);

                    $obj->messagesData->to = '<%'.$matches[1].'%>';
                    $obj->messagesData->to = self::replace_variables($obj->messagesData->to, $data['vars'], true);
                }
                $where .= $wpdb->prepare(' AND DATE(posts.time) <= %s', $obj->messagesData->to);
            }

            $query .= $where.' ORDER BY posts.id DESC';

            if(!empty($obj->messagesData->limit)) $query .= $wpdb->prepare(' LIMIT %d', $obj->messagesData->limit);

            $rows = $wpdb->get_results($query);
            $results = array();

            $filters_to_satisfy = count($filters);
            foreach($rows as $row)
            {
                $filters_satisfied = 0;
                $item = array();
                $item['id']     = $row->id;
                $item['form']   = $row->formid;
                $item['date']   = $row->time;
                $item['paid']   = @intval($row->paid);

                $data = unserialize($row->paypal_post);
                if($data)
                {
                    foreach($data as $i => $v)
                    {
                        if(isset($filters[$i]))
                        {
                            if(is_array($v))
                            {
                                if(count(array_intersect($v, $filters[$i])) == count($filters[$i])) $filters_satisfied++;
                                else continue;
                            }
                            elseif(in_array($v, $filters[$i])) $filters_satisfied++;
                            else continue;
                        }

                        if(empty($fields) || in_array($i, $fields))
                        {
                            if(preg_match('/fieldname\d+/', $i))
                                $item[substr($i, 9)] = $v;
                            $item[$i] = $v;
                        }
                    }
                }
                if($filters_satisfied == $filters_to_satisfy) $results[] = $item;
            }
            return $results;
        } // End ds_submissions

		private static function ds_db( $data )
		{
			global $wpdb;

			// Initialization
			$query = '';
			$results = array();

			if( !is_admin() || !empty( $data[ 'form' ] ) && !empty( $data[ 'field' ] ) )
			{
				if( empty( $data[ 'form' ] ) && empty( $data[ 'field' ] ) ) return false;
				$obj = get_transient(  'cpcff_db_'.$data[ 'form' ].'_'.$data[ 'field' ] );
				if( $obj === false ) return false;

				// Engine data
				$data[ 'engine' ] 		= trim((!empty($obj->databaseData->engine)) ? $obj->databaseData->engine : 'mysql');
				// Connection data
				$data[ 'connection' ] = (!empty($obj->databaseData->connection)) ? $obj->databaseData->connection : 'structure';
				$data[ 'dns' ] 			= trim((!empty($obj->databaseData->dns)) ? $obj->databaseData->dns : '');
				$data[ 'host' ] 		= trim($obj->databaseData->host);
				$data[ 'user' ] 		= trim($obj->databaseData->user);
				$data[ 'pass' ] 		= trim($obj->databaseData->pass);
				$data[ 'database' ] 	= trim($obj->databaseData->database);

				// Query data
				$data[ 'active' ]   	= (!empty($obj->queryData->active)) ? $obj->queryData->active : 'structure';
				$data[ 'query' ] 		= trim($obj->queryData->query);
				$data[ 'value' ] 		= trim($obj->queryData->value);
				$data[ 'text' ] 		= trim($obj->queryData->text);
				$data[ 'table' ] 	    = trim($obj->queryData->table);
				$data[ 'where' ] 	    = trim($obj->queryData->where);
				$data[ 'orderby' ] 	    = trim($obj->queryData->orderby);
				$data[ 'limit' ] 		= trim($obj->queryData->limit);
			}

			// Format query replacing variables and constants
			// were defined the query components
			if(empty($data['query']) || (isset($data['active']) && $data['active'] == 'structure'))
			{
				$separator = '';
				$select = '';
				if( !empty( $data[ 'value' ] ) )
				{
					$separator = ',';
					$select .= $data[ 'value' ] . ' AS value';
				}

				if( !empty( $data[ 'text' ] ) )
				{
					$select .= $separator . $data[ 'text' ] . ' AS text';
				}

				if(!empty($select)) // has not been defined text or values to return
					$query = 'SELECT DISTINCT ' . $select . ' FROM ' . $data[ 'table' ] . ( ( !empty( $data[ 'where' ] ) ) ? ' WHERE ' . $data[ 'where' ] : '' ) . ( ( !empty( $data[ 'orderby' ] ) ) ? ' ORDER BY ' . $data[ 'orderby' ] : '' ).( ( !empty( $data[ 'limit' ] ) ) ? ' LIMIT ' . $data[ 'limit' ] : '' );
			}

			// The query was entered manually
			if(empty($query) && !empty($data[ 'query' ]))
			{
				$query = trim($data[ 'query' ]);
			}

			// Replace constants on query
			$query = self::replace_constants( $query );

			// Replace variables on query
			if(isset($data[ 'vars' ])) $query = self::replace_variables( $query, $data[ 'vars' ], true );

			if(
				(!empty($data['connection']) && $data['connection'] == 'dns' && !empty($data['dns'])) ||
				((empty($data['connection']) || $data['connection'] == 'structure') &&  !empty( $data[ 'host' ]))
			) // External database
			{
				if(!empty($data['connection']) && $data['connection'] == 'dns')
				{
					$dns = $data['dns'];
				}
				else
				{
					// For compatibily with versions of the plugin previous to the use of PDO
					$data['engine'] = (empty($data['engine'])) ? 'mysql' : strtolower(trim($data['engine']));

					switch($data['engine'])
					{
						case 'sqlite' :
							$dns = $data['engine'].':'.$data[ 'host' ];
						break;
						case 'firebird':
							$dns = $data['engine'].':dbname='.$data[ 'database' ];
						break;
						case 'ibm':
							$dns = $data['engine'].':DRIVER={IBM DB2 ODBC DRIVER};HOSTNAME='.$data[ 'host' ].';PROTOCOL=TCPIP;DATABASE='.$data[ 'database' ];
						break;
						case 'informix':
							$dns = $data['engine'].':host='.$data[ 'host' ].';database='.$data[ 'database' ];
						break;
						case 'sqlsrv':
							$dns = $data['engine'].':Server='.$data[ 'host' ].';Database='.$data[ 'database' ];
						break;
						case 'oci':
							$dns = $data['engine'].':dbname='.$data[ 'database' ];
						break;
						default:
							$dns = $data['engine'].':host='.$data[ 'host' ].';dbname='.$data[ 'database' ];
						break;
					}
				}

				self::$cpcff_db_connect = new PDO( $dns, $data[ 'user' ], $data[ 'pass' ], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

				if(!empty($query))
				{
					$result = self::$cpcff_db_connect->query($query);
					while($row = $result->fetch(PDO::FETCH_ASSOC))
					{
						foreach( $row as $_key => $_val )
						{
							if(!function_exists('mb_check_encoding') || true !== mb_check_encoding( $_val, 'UTF-8' ))
								$row[ $_key ] = utf8_encode($_val);
						}
						$results[] = (object)$row;
					}
				}
			}
			else // Local database
			{
				if(!empty($query)) $results = $wpdb->get_results( $query, ARRAY_A );
			}
			return $results;
		} // End ds_db

		public static function ds_csv_correct_body( $response, $agrs, $url )
		{
			try
			{
				$response_object = $response[ 'http_response' ]->get_response_object();
				$raw = $response_object->raw;
				if (($pos = strpos($raw, "\r\n\r\n")) === false) return $response;
				$response['body'] = substr($raw, $pos + strlen("\n\r\n\r"));
			}
			catch( Exception $err )
			{
				return $response;
			}
			return $response;
		} // End ds_csv_correct_body

		private static function ds_csv( $data )
		{
			$return_obj = new stdClass;
			try
			{
				if( !is_admin() || !empty( $data[ 'form' ] ) && !empty( $data[ 'field' ] ) )
				{
					if( empty( $data[ 'form' ] ) && empty( $data[ 'field' ] ) ) return false;
					$obj = get_transient(  'cpcff_db_'.$data[ 'form' ].'_'.$data[ 'field' ] );
					if( $obj === false ) return false;
					$csvData = $obj->csvData;

					$data[ 'file' ] 		= (property_exists( $csvData, 'file' )) ? $csvData->file : '';
					$text_column  			= (property_exists( $csvData, 'text' )) ?
											  (
												(is_array($csvData->text)) ? $csvData->text : @intval($csvData->text)
											  ) : '';
					$value_column 			= (property_exists( $csvData, 'value' )) ? @intval($csvData->value) : '';
					$fields 				= (property_exists( $csvData, 'fields' ) && is_array($csvData->fields) ) ?
											  $csvData->fields : array();
					$data[ 'delimiter' ] 	= (property_exists( $csvData, 'delimiter' )) ? $csvData->delimiter : '';
					$data[ 'character' ] 	= (property_exists( $csvData, 'character' )) ? $csvData->character : ',';
					$data[ 'header' ] 		= (property_exists( $csvData, 'headline' )) ? $csvData->headline : '';
					$where 					= (property_exists( $csvData, 'where' )) ? $csvData->where : '';

					$vars = ( !empty( $data[ 'vars' ] )  && is_array( $data[ 'vars' ] ) ) ? $data[ 'vars' ] : array();
					$where = self::replace_constants( $where );
					$where = self::replace_variables( $where , $vars );
				}

				if( !empty( $data[ 'file' ] ))
				{
					$file = $data[ 'file' ];

					$delimiter = ',';
					if( !empty( $data[ 'delimiter' ] ) )
					{
						if( $data[ 'delimiter' ] == 'tabulator' ) $delimiter = "\t";
						elseif( !empty( $data[ 'character' ] ) ) $delimiter = $data[ 'character' ];
					}
					$includes_headers = ( !empty( $data[ 'header' ] ) && ( $data[ 'header' ] === 'true' || $data[ 'header' ] === 1 || $data[ 'header' ] === true)) ? true : false;

					add_filter('http_response', array('CPCFF_DATA_SOURCE','ds_csv_correct_body'), 10, 3 );

                    $timeout = MAX((ini_get('max_execution_time') && is_numeric(ini_get('max_execution_time'))) ? intval(ini_get('max_execution_time')) : 10, 10);

					$response = wp_remote_get( $file, array('sslverify' => false, 'timeout'=>$timeout) );

					remove_filter('http_response', array('CPCFF_DATA_SOURCE','ds_csv_correct_body'), 10, 3 );

					if( !is_wp_error( $response ) && $response['response']['code'] == 200 )
					{
						$body = wp_remote_retrieve_body($response);
						$body = preg_replace("/\r\n|\n\r|\n|\r/", "\n", $body);
						$rows = explode( "\n", $body ); //parse the rows
						if( is_array( $rows ) && count( $rows ) )
						{
							$csv_arr = array();
							foreach( $rows as &$row )
							{
								if(empty($row)) continue;
								$csv_arr[] = str_getcsv($row, $delimiter); //parse the items in rows
							}

							$headers = array();

							/*
								Get the headers row if exists, or a list of generic fields: Field_0, Field_1, ..., Field_#
							*/
							if( $includes_headers )
							{
								$headers = array_shift( $csv_arr );
							}
							else
							{
								$headers = array();
								for( $i = 0; $i < count( $csv_arr[0] ); $i++ )
								{
									$headers[] = 'Field_'.$i;
								}
							}

							if( $data[ 'return' ] == 'headers' )
							{
								// Return an array with the headers
								$return_obj->data = $headers;
							}
							else
							{
								if(
									isset( $text_column )  && $text_column  !== '' &&
									isset( $value_column ) && $value_column !== ''
								)
								{
									// Return an associative array
									// with all rows in the csv field that satisfy the "where" clause if correspond.
									$return_rows = array();
									if( is_array( $text_column ) )
									{
										foreach( $csv_arr as $index => $row )
										{
											$data = array();
											foreach( $text_column as $field )
											{
												$data[ $fields[ $field ] ] = ( isset( $row[ $field ] ) ) ? $row[ $field ]   : '';
											}

											// If was defined the "where" clause, check if the condition is satisfied
											if( isset( $where) && $where != '' )
											{
												if(
													isset( $data[ $fields[ $value_column ] ] ) &&
													$data[ $fields[ $value_column ] ] == $where
												) $return_rows[] = $data;
											}
											else
												$return_rows[] = $data;
										}
									}
									else
									{
										foreach( $csv_arr as $index => $row )
										{
											$data = array(
												'text' =>  ( isset( $row[ $text_column ] ) ) ? $row[ $text_column ]   : '',
												'value' => ( isset( $row[ $value_column ] ) ) ? $row[ $value_column ] : ''
											);

											// If was defined the "where" clause, check if the condition is satisfied
											if( isset( $where) && $where != '' )
											{
												if( $data[ 'value' ] == $where ) $return_rows[] = $data;
											}
											else
												$return_rows[] = $data;
										}
									}
									$return_obj->data = $return_rows;
								}
								else
								{
									$return_obj->error = __( 'Invalid columns' );
								}
							}
						}
						else
						{
							$return_obj->error = __( 'The file has not a CSV valid format' );
						}
					}
					else
					{
						$return_obj->error = __( 'The CSV file is not accessible' );
					}
				}
				else
				{
					$return_obj->error = __( 'The CSV file is not specified' );
				}
			}
			catch( Exception $err )
			{
				$return_obj->error = __( 'An error occurred processing the file' );
			}

			print ((version_compare(CP_CFF_PHPVERSION,"5.5.0")>=0)?json_encode( $return_obj, JSON_PARTIAL_OUTPUT_ON_ERROR ):json_encode( $return_obj ));
		} // End ds_csv

		private static function ds_post_type( $data )
		{
			try
			{
				if( empty( $data[ 'form' ] ) && empty( $data[ 'field' ] ) ) return false;
				$obj = get_transient(  'cpcff_db_'.$data[ 'form' ].'_'.$data[ 'field' ] );
				if( $obj === false ) return false;

				$vars = ( !empty( $data[ 'vars' ] )  && is_array( $data[ 'vars' ] ) ) ? $data[ 'vars' ] : array();

				$data[ 'posttype' ] 	= $obj->posttypeData->posttype;
				$data[ 'value' ] 		= $obj->posttypeData->value;
				$data[ 'text' ] 		= $obj->posttypeData->text;
				$data[ 'last' ] 		= self::replace_variables( $obj->posttypeData->last, $vars );
				$data[ 'id' ] 			= self::replace_variables( $obj->posttypeData->id, $vars );

				$posts = array();
				if( $data[ 'id' ] === 0 || !empty( $data[ 'id' ] ) )
				{
					$result = get_post( $data[ 'id' ], ARRAY_A );
					if( !is_null( $result ) )
					{
						$tmp = new stdClass;
						self::set_attr( $tmp, 'value', $result, $data[ 'value' ] );
						self::set_attr( $tmp, 'text',  $result, $data[ 'text' ] );
						array_push( $posts, $tmp );
					}
				}
				else
				{
					$args = array(
						'post_status'  => 'publish',
						'orderby'        => 'post_date',
						'order'           => 'DESC'
					);

					if( !empty( $data[ 'posttype' ] ) )
					{
						$args[ 'post_type' ] = $data[ 'posttype' ];
					}

					if( $data[ 'last' ] === 0 )
					{
						return array();
					}
					if( !empty( $data[ 'last' ] ) )
					{
						$args[ 'numberposts' ] = intval( @$data[ 'last' ] );
					}

					$results = get_posts( $args );

					foreach ( $results as $result )
					{
						$tmp = new stdClass;
						self::set_attr( $tmp, 'value', $result, $data[ 'value' ] );
						self::set_attr( $tmp, 'text',  $result, $data[ 'text' ] );
						array_push( $posts, $tmp );
					}
				}
				return $posts;
			}
			catch( Exception $err )
			{
				return false;
			}
		} // End ds_post_type

		private static function ds_taxonomy( $data )
		{
			try
			{
				if( empty( $data[ 'form' ] ) && empty( $data[ 'field' ] ) ) return false;
				$obj = get_transient(  'cpcff_db_'.$data[ 'form' ].'_'.$data[ 'field' ] );
				if( $obj === false ) return false;

				$vars = ( !empty( $data[ 'vars' ] )  && is_array( $data[ 'vars' ] ) ) ? $data[ 'vars' ] : array();

				$data[ 'taxonomy' ] 	= $obj->taxonomyData->taxonomy;
				$data[ 'value' ] 			= $obj->taxonomyData->value;
				$data[ 'text' ] 			= $obj->taxonomyData->text;
				$data[ 'id' ] 				= self::replace_variables( $obj->taxonomyData->id, $vars );
				$data[ 'slug' ] 			= self::replace_variables( $obj->taxonomyData->slug, $vars );

				$taxonomies = array();
				if( $data[ 'id' ] === 0 || !empty( $data[ 'id' ] ) || $data[ 'slug' ] === 0 || !empty( $data[ 'slug' ] ) )
				{
					if( !empty( $data[ 'taxonomy' ] ) )
					{
						if( !empty( $data[ 'id' ] ) )
						{
							$result = get_term( $data[ 'id' ], $data[ 'taxonomy' ], ARRAY_A );
						}
						else
						{
							$result = get_term_by( 'slug', $data[ 'slug' ], $data[ 'taxonomy' ], ARRAY_A );
						}

						$tmp = new stdClass;
						$tmp->value = '';
						$tmp->text = '';
						if( !is_null( $result ) )
						{
							self::set_attr( $tmp, 'value', $result, $data[ 'value' ] );
							self::set_attr( $tmp, 'text',  $result, $data[ 'text' ] );
						}
						array_push( $taxonomies, $tmp );
					}
				}
				else
				{
					if( !empty( $data[ 'taxonomy' ] ) )
					{
						$results = get_terms( $data[ 'taxonomy' ], array( 'hide_empty' => 0 ) );

						foreach ( $results as $result )
						{
							$tmp = new stdClass;
							self::set_attr( $tmp, 'value', $result, $data[ 'value' ] );
							self::set_attr( $tmp, 'text',  $result, $data[ 'text' ] );
							array_push( $taxonomies, $tmp );
						}
					}
				}
				return $taxonomies;
			}
			catch( Exception $err )
			{
				return false;
			}
		} // End ds_taxonomy


		private static function ds_user( $data )
		{
			try
			{
				if( empty( $data[ 'form' ] ) && empty( $data[ 'field' ] ) ) return false;
				$obj = get_transient(  'cpcff_db_'.$data[ 'form' ].'_'.$data[ 'field' ] );
				if( $obj === false ) return false;

				$vars = ( !empty( $data[ 'vars' ] )  && is_array( $data[ 'vars' ] ) ) ? $data[ 'vars' ] : array();

				$data[ 'logged' ] 	= $obj->userData->logged;
				$data[ 'text' ] 		= $obj->userData->text;
				$data[ 'value' ] 		= $obj->userData->value;
				$data[ 'id' ] 			= self::replace_variables( $obj->userData->id, $vars );
				$data[ 'login' ] 		= self::replace_variables( $obj->userData->login, $vars );

				$users = array();
				if( !empty( $data[ 'logged' ] ) && $data[ 'logged' ] !== 'false' )
				{
					$result = wp_get_current_user();
					if( !empty( $result ) )
					{
						$tmp = new stdClass;
						self::set_attr( $tmp, 'value', $result->data, $data[ 'value' ] );
						$users[] = $tmp;
					}
				}
				elseif( $data[ 'id' ] === 0 || !empty( $data[ 'id' ] ) || $data[ 'login' ] === 0 || !empty( $data[ 'login' ] ) )
				{
					if( !empty( $data[ 'id' ] ) )
					{
						$result = get_user_by( 'id', $data[ 'id' ] );
					}
					elseif( !empty( $data[ 'login' ] ) )
					{
						$result = get_user_by( 'login', $data[ 'login' ] );
					}

					$tmp = new stdClass;
					$tmp->value = '';
					if( !empty( $result ) )
					{
						self::set_attr( $tmp, 'value', $result->data, $data[ 'value' ] );
					}
					$users[] = $tmp;

				}
				else
				{

					$results = get_users();
					foreach( $results as $result )
					{
						$tmp = new stdClass;
						self::set_attr( $tmp, 'value', $result->data, $data[ 'value' ] );
						self::set_attr( $tmp, 'text', $result->data, $data[ 'text' ] );
						$users[] = $tmp;
					}
				}

				return $users;
			}
			catch( Exception $err )
			{
				return false;
			}
		} // End ds_user
	} // End CPCFF_DATA_SOURCE
}