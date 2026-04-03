<?php
/**
 * Plugin Name:  Strato Assistant
 * Plugin URI:   https://www.strato.de
 * Description:  Strato Assistant will help you complete the first setup of your WordPress in quick and easy steps. It will help you find a theme to start with and add some plugins that will help you with the purpose of your WordPress installation. You can restart it at any time from the admin menu.
 * Version:      7.3.0
 * License:      GPLv2 or later
 * Author:       Strato
 * Author URI:   https://www.strato.de
 * Text Domain:  strato-assistant
 * Domain Path:  /languages
 */

/*
Copyright 2020 IONOS by 1&1
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Online: http://www.gnu.org/licenses/gpl.txt
*/

// Do not allow direct access!
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Forbidden' );
}

// Define plugin basic constants
if ( ! defined( 'STRATO_ASSISTANT_VERSION' ) ) {
	define( 'STRATO_ASSISTANT_VERSION', '7.3.0' );
}
if ( ! defined( 'STRATO_ASSISTANT_BASENAME' ) ) {
	define( 'STRATO_ASSISTANT_BASENAME', plugin_basename( __FILE__ ) );
}
if ( ! defined( 'STRATO_ASSISTANT_SLUG' ) ) {
	define( 'STRATO_ASSISTANT_SLUG', dirname( STRATO_ASSISTANT_BASENAME ) );
}

class Strato_Assistant {

	public function __construct() {
		$this->load_global_files();

		Strato_Assistant_Handler_Login::init(
			Strato\Assistant\Config::get( 'features.login_redesign' )
		);

		/** admin actions */
		if ( is_admin() ) {
			$this->load_admin_files();

			add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			// Start and configure the Assistant in the admin area
			Strato_Assistant_Handler_Dispatch::admin_init();

			// add checks on plugin activation
			register_activation_hook( __FILE__, array( $this, 'activate_plugin' ) );

			// register deactivation hook
			register_deactivation_hook( __FILE__, array( $this, 'deactivation_hook' ) );

		/** front-end actions */
		} else {
			$this->load_frontend_files();

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}
	}

	public function load_global_files() {
		include_once 'inc/lib/options.php';
		Strato\Assistant\Options::set_tenant_and_plugin_name( 'strato', 'assistant' );

		include_once 'inc/lib/config.php';
		include_once 'inc/lib/data-providers/cloud.php';
		include_once 'inc/lib/updater.php';
		include_once 'inc/branding.php';
		include_once 'inc/view.php';
		include_once 'inc/handlers/login.php';
		include_once 'inc/handlers/dispatch.php';
		include_once 'inc/auto-updater.php';

		require_once 'inc/lib/features/disable-plugins/class-manager.php';
		new \Strato\Assistant\Warning( 'ionos-assistant' );
	}

	public function load_admin_files() {
		include_once 'inc/modify-settings-page.php';
		include_once 'inc/modify-plugins-page.php';
		include_once 'inc/sitetype-filter.php';
		include_once 'inc/cache-manager.php';
		include_once 'inc/dashboard.php';
		include_once 'inc/create-settings-page.php';
	}

	public function load_frontend_files() {
		include_once 'inc/cron-manager.php';
	}

	public function deactivation_hook() {
		wp_clear_scheduled_hook( 'strato_assistant_cron_update_deactivated_plugins' );
		wp_clear_scheduled_hook( 'strato_assistant_cron_update_plugin_meta' );
		delete_option( 'strato_assistant_completed' );
		delete_option( 'strato_assistant_sitetype' );
	}

	public function load_textdomain() {
		if ( self::is_must_use_plugin_folder() ) {
			$language_loaded = load_muplugin_textdomain( 'strato-assistant', basename( dirname( __FILE__ ) ) . '/languages' );
		} else {
			$language_loaded = load_plugin_textdomain( 'strato-assistant', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		// Check whether language could be loaded properly. If not, use en_US as a fallback.
		if ( ! empty( $language_loaded ) || false === $language_loaded ) {
			if ( self::is_must_use_plugin_folder() ) {
				$plugin_dir = WPMU_PLUGIN_DIR;
			} else {
				$plugin_dir = WP_PLUGIN_DIR;
			}

			$domain = 'strato-assistant';
			$path = trailingslashit( $plugin_dir . '/' . ltrim( dirname( plugin_basename( __FILE__ ) ) . '/languages/', '/' ) );
			$mofile = $domain . '-en_US.mo';

			load_textdomain( $domain, $path . $mofile );
		}
	}

	public function enqueue_scripts() {
		// Add the cookie script to control feature switches through JS
		wp_enqueue_script(
			'strato-assistant-wp-cookies',
			Strato_Assistant::get_js_url( 'cookies.js' ),
			array(),
			filemtime( self::get_js_path( 'cookies.js' ) )
		);
	}

	public static function get_site_type_label( $site_type ) {
		switch ( $site_type ) {
			case 'gallery':
				$site_type = _x( 'Gallery', 'website-types', 'strato-assistant' );
				break;
			case 'blog':
				$site_type = _x( 'Blog', 'website-types', 'strato-assistant' );
				break;
			case 'personal':
				$site_type = _x( 'Personal Website', 'website-types', 'strato-assistant' );
				break;
			case 'business':
				$site_type = _x( 'Business Website', 'website-types', 'strato-assistant' );
				break;
		}

		return $site_type;
	}

	public function activate_plugin() {
		// Check WordPress version
		if ( version_compare( get_bloginfo( 'version' ), '3.5', '<' ) ) {
			die( __( 'The Assistant could not be activated. To activate the plugin, you need WordPress 3.5 or higher.', 'strato-assistant' ) );
		}
	}

	public static function get_css_url( $file = '' ) {
		return plugins_url( 'css/' . $file, __FILE__ );
	}

	public static function get_css_path( $file = '' ) {
		return self::get_plugin_dir_path() . 'css/' . $file;
	}

	public static function get_js_url( $file = '' ) {
		return plugins_url( 'js/' . $file, __FILE__ );
	}

	public static function get_js_path( $file = '' ) {
		return self::get_plugin_dir_path() . 'js/' . $file;
	}

	public static function get_images_url( $image = '' ) {
		return plugins_url( 'images/' . $image, __FILE__ );
	}

	public static function get_plugin_file_path() {
		return __FILE__;
	}

	public static function get_plugin_dir_path() {
		return apply_filters( 'strato-assistant-plugin-dir-path', plugin_dir_path( __FILE__ ) );
	}

	public static function get_inc_dir_path() {
		return plugin_dir_path( __FILE__ ) . 'inc/';
	}

	public static function get_views_dir_path() {
		return Strato_Assistant_View::get_default_views_path();
	}

	public static function get_abspath() {
		return apply_filters( 'strato-assistant-abspath', ABSPATH );
	}

	/**
	 * Checks if the fragment exists
	 *
	 * @param null $string
	 * @param null $fragment
	 *
	 * @return bool
	 */
	public static function is_url_query_fragment_in_url_string( $string = null, $fragment = null ) {
		$return_value = false;
		if ( ! is_null( $string ) && ! is_null( $fragment ) ) {
			parse_str( parse_url( $string, PHP_URL_QUERY ), $query_array );
			$return_value = array_key_exists( $fragment, $query_array );
		}

		return $return_value;
	}

	/**
	 * Check if the plugin is a "must used" use case or not, based on its location:
	 *
	 * * wp-content/plugins -> optional
	 * * wp-content/mu-plugins -> required
	 *
	 * @return boolean
	 */
	public static function is_must_use_plugin_folder() {
		$plugin_path = Strato_Assistant::get_plugin_dir_path();

		if ( strpos( $plugin_path, 'mu-plugins' ) === false ) {
			return false;
		} else {
			return true;
		}
	}
}

new Strato_Assistant();
