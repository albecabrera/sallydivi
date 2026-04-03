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
define( 'DB_NAME', 'dbs15346734' );

/** Database username */
define( 'DB_USER', 'dbu4981056' );

/** Database password */
define( 'DB_PASSWORD', 'J4Sga8wazR92jcgZmoJuoTh82yGgdqV96uV' );

/** Database hostname */
define( 'DB_HOST', 'database-5019837176.webspace-host.com' );

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
define('AUTH_KEY',         'xcpO5YCvQG2R5BmkCRkDhXvnuc1EdTVnY0QV7Frld6H1MJYN2EKpGYDlWxXffReP');
define('SECURE_AUTH_KEY',  'TSwJUvPjgsEfX8OuEcZch0JthyBAqKrPCUJy0L1gzVLxzTi7CWjZVSPK60pB3yaO');
define('LOGGED_IN_KEY',    'UMLhGbEw8rNXuSc4ow5ZQpW9NTbx3QawgkhMIgF4Fk9UCLRhY677ocLNqm3aWuFy');
define('NONCE_KEY',        'fHF1xwX1wNtD8YejGW8UMORHGhvI4cnkdzY2QbVvIh3GVGH3LqkpQ5wnEK8FHF3p');
define('AUTH_SALT',        '0UOUTfvkrefNFPKaq8tPqbQ9c8yJ8U4hJ1fP5FcqZ3byDzw2MyMfukAYZq0Ntzkh');
define('SECURE_AUTH_SALT', 'F6iq4l8poBDzhVDPHsduin6lnbZiofnnz5ciV20ZyMMPCPsewY8lXX8PSXIJg93n');
define('LOGGED_IN_SALT',   '28mn7N6eUZcokCp6rGfflUoHuki169CddAaZzpElVQlX71NFKhn7VX69EqPXLR1a');
define('NONCE_SALT',       'kmuNmgKwUXC4tRlDax7t8C61nmkuDLcKt8fX7coLqfF1sHqkR05YJeQbvTryMlHy');

/**
 * Other customizations.
 */
define('WP_TEMP_DIR',dirname(__FILE__).'/wp-content/uploads');


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

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

define( "WP_AUTO_UPDATE_CORE", true );