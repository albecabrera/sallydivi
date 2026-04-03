<?php
// Do not allow direct access!
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Forbidden' );
}

include_once( ABSPATH . '/wp-admin/includes/plugin.php' );
include_once( ABSPATH . '/wp-admin/includes/class-wp-upgrader.php' );
include_once 'automatic-installer-skin.php';

class Strato_Assistant_Installer {

	/**
	 * Install a plugin package
	 *
	 * @param  array $plugin
	 * @return boolean
	 */
	static public function install_plugin( $plugin ) {
		$upgrader = new Plugin_Upgrader( new Strato_Automatic_Installer_Skin() );
		$download_link = $plugin['custom_link'] ?? 'https://downloads.wordpress.org/plugin/' . $plugin['slug'] . '.latest-stable.zip';

		$result = $upgrader->install( $download_link );
		return is_wp_error( $result ) ? false : $result;
	}

	/**
	 * Update an existing (= installed) plugin
	 *
	 * @param  string $plugin_path
	 * @return boolean
	 */
	static public function update_plugin( $plugin_path ) {
		$upgrader = new Plugin_Upgrader( new Strato_Automatic_Installer_Skin() );

		$result = $upgrader->upgrade( $plugin_path );
		return is_wp_error( $result ) ? false : $result;
	}

	/**
	 * Get the list of installation paths from given plugins (in the plugins directory)
	 *
	 * @param  array $plugin_slugs
	 * @return array
	 */
	static public function get_plugin_installation_paths( $plugin_slugs = array() ) {

		/** @todo check if this is really needed to get the last state of the plugins? */
		wp_clean_plugins_cache( true );

		$plugins = get_plugins();
		$plugins_installed = array_flip( $plugin_slugs );

		foreach ( $plugins as $plugin_path => $plugin ) {
			$parts = explode( '/', $plugin_path );

			if ( empty( $plugin_slugs ) || array_key_exists( $parts[0], $plugins_installed ) ) {
				$plugins_installed[ $parts[0] ] = $plugin_path;
			}
		}
		return $plugins_installed;
	}

	/**
	 * Install a theme package
	 *
	 * @param  array $theme_meta
	 * @return boolean
	 */
	static public function install_theme( $theme_meta ) {
		$upgrader = new Theme_Upgrader( new Strato_Automatic_Installer_Skin() );

		$result = $upgrader->install(
			'https://downloads.wordpress.org/theme/' . $theme_meta['slug'] . '.latest-stable.zip'
		);
		return is_wp_error( $result ) ? false : $result;
	}
}