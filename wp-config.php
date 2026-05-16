<?php




/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('WP_CACHE', true);
define( 'WPCACHEHOME', '/var/www/html/sallydivi/wp-content/plugins/wp-super-cache/' );
define( 'DB_NAME', 'sallydivi' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'mariadb' );

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
define( 'AUTH_KEY',         ';icy+eRfU:^E90d?*^Bpr!wt8?23O+JJ<q{&#`f-R3sH1Pc?Mz5#c{ko|8z:y>D;' );
define( 'SECURE_AUTH_KEY',  'v/s:ComvdMly$96Vc~7MQbFm#)Y$5Cu-4Oou@.#d+9t`KT7sKk]%J:~kVqcOr!mI' );
define( 'LOGGED_IN_KEY',    'UN<x[V)5r <^uUoD~=3p9nTB)}W>ZpKv:!>nnj;,m`R*cnd,F@*5>S2hya.:iZz4' );
define( 'NONCE_KEY',        '/,?(SLkP!*+0}j_SR4-7F_>,zy/zaO9rPiE>]9)(;:5P%nZQ0Ng=L.iv-M-g-HU~' );
define( 'AUTH_SALT',        'opiWU*Ki6wH#Bm!TcDz8D4Yw]+[:FY9xUbuSmu: E7cU~Kw!^fNmN[x4s<zxzx2n' );
define( 'SECURE_AUTH_SALT', 'xRJ.j,S}q7t@cw<OALKRg-!<21:uN>i8o->b lI9T@yk)-DD:k/qT#1g#[2W{A.!' );
define( 'LOGGED_IN_SALT',   '3_1&/m@I}G`hGef}~k-([72$BU<:TrLfgH0+GZH=`nTgI4+<Ht47|Z]<::lDCA+i' );
define( 'NONCE_SALT',       ';`$7^Q-$__F.ivAoz&#_(Z_!dDBL{RId+%3}<&7wUY!ee!f/lG(W!^5bSO]W1~=t' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'ydp0_';

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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_DISPLAY', false );
define( 'WP_DEBUG_LOG', false );

/* Add any custom values between this line and the "stop editing" line. */

define( 'FS_METHOD', 'direct' );



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
