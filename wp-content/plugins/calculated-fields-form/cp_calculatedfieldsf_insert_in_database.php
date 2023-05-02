<?php

/************************************************************************************************
*
*     PAY ATTENTION: As this file is edited manually, it is recommended create a backup
*     of it, to prevent lose your code in future updates. The plugin includes its own backup
*     mechanisms, but it can fail depending on your web sever settings.
*
*	  An alternative to keeping the file safe is to create a copy in
*     wp-content/uploads/calculated-field-form/cp_calculatedfieldsf_insert_in_database.php
*     and the plugin would use this copy instead.
*
*
*     Note, to run the queries with the submissions of a specific form, please, uncomment the lines of code
*     if($params["formid"] == 1):  and   endif;
*     and replace the number one with the corresponding form id.
*
*************************************************************************************************/

// if($params["formid"] == 1):

define( 'DATABASE_HOST',  '' );
define( 'DATABASE_USER',  '' );
define( 'DATABASE_PASS',  '' );
define( 'DATABASE_NAME', '' );
define( 'DATABASE_TABLE', '' );

if( DATABASE_HOST !== '' && DATABASE_USER !== '' && DATABASE_NAME !== '' && DATABASE_TABLE !== '' )
{
	try
	{
		$db_link = mysqli_connect( DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME );
		if( $db_link !== false )
		{
			$field1 = mysqli_escape_string( $db_link, $params[ 'fieldname%' ] );
			$field2 = mysqli_escape_string( $db_link, $params[ 'fieldname%' ] );
			$field3 = mysqli_escape_string( $db_link, $params[ 'fieldname%' ] );

			$cff_insert_result = mysqli_query($db_link, "INSERT INTO `".DATABASE_TABLE."` (field1, field2, field3) VALUES ('$field1', '$field2', '$field3');");

			// If there is an error in the insertion query, register it in the errors logs
			if($cff_insert_result === false)
			{
				error_log(mysqli_error($db_link));
			}
			mysqli_close($db_link);
		}
	}
	catch( Exception $e )
	{
		error_log($e->getMessage());
	}
}

// endif;