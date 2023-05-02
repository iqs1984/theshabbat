<?php
//Begin Really Simple SSL session cookie settings
@ini_set('session.cookie_httponly', true);
@ini_set('session.cookie_secure', true);
@ini_set('session.use_only_cookies', true);
//END Really Simple SSL cookie settings
define( 'WP_CACHE', true ); // Added by WP Rocket

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'theshab' );

/** Database username */
define( 'DB_USER', 'theshab' );

/** Database password */
define( 'DB_PASSWORD', 'f}6Vs(s$K5@~' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '6ll8ltypxgsz6l797q53ig1y5jv2stypn2vge9abiqzlspykkno2pdgvtein01bo' );
define( 'SECURE_AUTH_KEY',  '5rcoacgtys66zfxw5koulgwdeqovx3wphyhx9km9n2stxqdlz84voxupennhg9pz' );
define( 'LOGGED_IN_KEY',    'qzfoq72axkms8rtuyfm8pn7niy7jodcgqkpdfbrph8bs5qsdxowxecddhy5yhvzw' );
define( 'NONCE_KEY',        'a029d8d2srf82ptgkblzuwfafespjd5bar0dcylccihsqjthv2taj4zcdx0yy9he' );
define( 'AUTH_SALT',        'ld4tejgvg2zszvu2fskl1zcbugysq8657u11rexja1iau2mmamiqurgaam8wy6k6' );
define( 'SECURE_AUTH_SALT', 'bnbr1b6ejjufyllc92anadzingjj8gathbaj44u8rxydcjln5opf1dvswqynlpfm' );
define( 'LOGGED_IN_SALT',   '843zuu5kjufxxbh4qzstvn6ww2gnuiwjixibttqhbv5ewveiddpqcf3jgeflj87o' );
define( 'NONCE_SALT',       'mwjucgut1mlb9oz1zphjjszaxxqp8bz0euaddu0rgpihr4wvzjyw3mcttvcyrrqq' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wpip_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

define('DISALLOW_FILE_EDIT', true);

define('ALLOW_UNFILTERED_UPLOADS', true);

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
