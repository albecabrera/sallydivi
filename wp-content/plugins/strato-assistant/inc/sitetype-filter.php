<?php

/**
 * Class Strato_Assistant_Sitetype_Filter
 * Retrieves Use Cases data from the sitetype-config.json
 */
class Strato_Assistant_Sitetype_Filter {

	const PLUGINS_DATA_URL = 'https://s3-de-central.profitbricks.com/web-hosting/plugins.json';

	/**
	 * @var array
	 */
	private $sitetypes;

	/**
	 * @var array
	 */
	private $plugins;

	/**
	 * @var string
	 */
	private $market;

	/**
	 * Strato_Assistant_Sitetype_Filter constructor.
	 *
	 * @param array  $sitetypes
	 * @param array  $plugins
	 * @param string $market
	 */
	function __construct( $sitetypes, $plugins, $market ) {
		$this->sitetypes = is_array( $sitetypes ) ? $sitetypes : array();
		$this->plugins = is_array( $plugins ) ? $plugins : array();
		$this->market = $market;
	}

	/**
	 * Get the list of Use Cases,
	 * each one with an array of associated data if $with_data is set to true.
	 * Data includes Use Case's:
	 * - title,
	 * - description,
	 * - image path.
	 *
	 * @param  bool $with_data
	 * @return array | bool
	 */
	public function get_sitetypes( $with_data = true ) {
		$sitetypes = array();

		$data_format = array(
			"headline"    => "",
			"description" => "",
			"image"       => ""
		);

		foreach ( $this->sitetypes as $key => $data ) {
			if ( $key !== 'any' ) {

				if ( $with_data ) {
					$sitetypes[ $key ] =  array_intersect_key(
						$data,
						$data_format
					);

				} else {
					$sitetypes[] = $key;
				}
			}
		}

		return $sitetypes;
	}

	/**
	 * Get all the data of a particular use case
	 *
	 * @param string $sitetype
	 *
	 * @return array | bool
	 */
	public function get_sitetype( $sitetype ) {
		if ( array_key_exists( $sitetype, $this->sitetypes )
		     && is_array( $this->sitetypes[ $sitetype ] ) ) {
			return $this->sitetypes[ $sitetype ];
		}

		return false;
	}

	/**
	 * Get themes (as slugs) for a Use Case, among the list of selected themes
	 *
	 * @param  string $sitetype
	 *
	 * @return array
	 */
	public function get_theme_slugs( $sitetype ) {
		return $this->sitetypes[ $sitetype ]['themes'] ?? array();
	}

	/**
	 * Get plugins' config data for a given Use Case
	 *
	 * @param  string $sitetype
	 *
	 * @return array
	 */
	public function get_plugins( $sitetype ) {
		$sitetype_plugins = $this->sitetypes[ $sitetype ]['plugins'] ?? array();
		$plugins_configs = $this->plugins;

		$available_plugins = array();
		foreach ( $sitetype_plugins as $key => $plugin_slug ) {
			if ( ! array_key_exists( $plugin_slug, $plugins_configs ) ) {
				continue;
			}

			$plugin_config = $plugins_configs[ $plugin_slug ];
			if ( $this->market === 'any' || is_null( $plugin_config['market'] ) || $this->market === $plugin_config['market'] ) {
				$available_plugins[ $plugin_slug ] = $plugin_config;
			}
		}

		return $available_plugins;
	}

	/**
	 * Get a plugin's config data
	 *
	 * @param  string $plugin_slug
	 * @return array
	 */
	public function get_plugin_config( $plugin_slug ) {
		return $this->plugins[ $plugin_slug ] ?? array();
	}
}
