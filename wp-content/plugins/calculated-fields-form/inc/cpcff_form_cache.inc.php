<?php
if(!defined('WP_DEBUG') || true != WP_DEBUG)
{
	error_reporting(E_ERROR|E_PARSE);
}
add_action( 'init', 'cp_calculatedfieldsf_form_cache', 1 );

function cp_calculatedfieldsf_form_cache()
	{
		if(
			get_option( 'CP_CALCULATEDFIELDSF_FORM_CACHE', false ) &&
			isset( $_REQUEST[ 'cffaction' ] ) &&
			$_REQUEST[ 'cffaction' ] == 'cff_cache' &&
			!empty( $_REQUEST[ 'cache' ] ) &&
			!empty( $_REQUEST[ 'form' ] ) &&
			current_user_can( 'manage_options' )
		)
		{
			global $wpdb;
			$table_name = $wpdb->prefix.CP_CALCULATEDFIELDSF_FORMS_TABLE;

			if( $result  = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM `'.$table_name.'` WHERE id=%d', $_REQUEST[ 'form' ] ), ARRAY_A ) )
			{
				if( empty( $result[ 'cache' ] ) )
				{
					if( !isset( $result[ 'cache' ] ) )
					{
						$wpdb->query( "ALTER TABLE  `".$table_name."` ADD `cache` longtext NOT NULL default ''" );
					}

					$wpdb->update(
						$wpdb->prefix.CP_CALCULATEDFIELDSF_FORMS_TABLE,
						array(
							'cache' => ( function_exists( 'get_magic_quotes_gpc' ) && get_magic_quotes_gpc() ) ? wp_unslash( $_REQUEST[ 'cache' ] ) : $_REQUEST[ 'cache' ]
						),
						array(
							'id' => $_REQUEST[ 'form' ]
						),
						array( '%s' ),
						array( '%d' )
					);
				}
			}
			print 'ok';
			exit;
		}
	} // End cp_calculatedfieldsf_form_cache
?>