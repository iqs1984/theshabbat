<?php
/**
 * Plugin Name: Integrately for Wordpress
 * Description: Connect your wordpress with Integrately.
 * Author: Integrately.com
 * Author URI: https://integrately.com
 * Version: 0.1
 */

function integrately_basic_auth_handler( $user ) {
	global $wp_integrately_basic_auth_error;

	$wp_integrately_basic_auth_error = null;

	// Don't authenticate twice
	if ( ! empty( $user ) ) {
		return $user;
	}

	// Check that we're trying to authenticate
	if ( !isset( $_SERVER['PHP_AUTH_USER'] ) ) {
		return $user;
	}

	$username = $_SERVER['PHP_AUTH_USER'];
	$password = $_SERVER['PHP_AUTH_PW'];

	/**
	 * In multi-site, wp_authenticate_spam_check filter is run on authentication. This filter calls
	 * get_currentuserinfo which in turn calls the determine_current_user filter. This leads to infinite
	 * recursion and a stack overflow unless the current function is removed from the determine_current_user
	 * filter during authentication.
	 */
	remove_filter( 'determine_current_user', 'integrately_basic_auth_handler', 20 );

	$user = wp_authenticate( $username, $password );

	add_filter( 'determine_current_user', 'integrately_basic_auth_handler', 20 );

	if ( is_wp_error( $user ) ) {
		$wp_integrately_basic_auth_error = $user;
		return null;
	}

	$wp_integrately_basic_auth_error = true;

	return $user->ID;
}
add_filter( 'determine_current_user', 'integrately_basic_auth_handler', 20 );

function integrately_basic_auth_error( $error ) {
	// Passthrough other errors
	if ( ! empty( $error ) ) {
		return $error;
	}

	global $wp_integrately_basic_auth_error;

	return $wp_integrately_basic_auth_error;
}
add_filter( 'rest_authentication_errors', 'integrately_basic_auth_error' );
