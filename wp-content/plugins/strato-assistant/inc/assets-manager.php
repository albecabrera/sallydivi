<?php

use Strato\Assistant\Config;
use Strato\Assistant\Options;

class Strato_Assistant_Assets_Manager {

	/**
	 * @var string
	 */
	protected $site_type = '';

	/**
	 * @var Strato_Assistant_Cache_Manager
	 */
	protected $cache_manager;

	/**
	 * @var Strato_Assistant_Assets_Adapter
	 */
	protected $assets_adapter;

	public $hidden_usecases;

	/**
	 * Strato_Assistant_Assets_Manager constructor.
	 *
	 * @param string $site_type
	 */
	public function __construct( $site_type = null ) {
		include_once( Strato_Assistant::get_inc_dir_path() . 'installer.php' );
		include_once( Strato_Assistant::get_inc_dir_path() . 'assets-adapter.php' );

		$this->site_type = $site_type;
		$this->cache_manager = new Strato_Assistant_Cache_Manager();
		$this->assets_adapter = new Strato_Assistant_Assets_Adapter();
		$coupon = $_GET['coupon'] ?? null;
		$this->hidden_usecases = array(
			'jetpack-backup' => array(
				'title' => 'Jetpack Backup',
				'plugins' => array(
					'jetpack'
				),
				'redirect_to' => add_query_arg( 'jetpack-partner-coupon', $coupon, admin_url() )
			)
		);
	}

	/**
	 * Activate some options in WordPress core depending on the use case
	 */
	public function setup_options() {

		$site_type_filter = new Strato_Assistant_Sitetype_Filter(
			Config::get( 'sitetypes' ),
			Config::get( 'plugins' ),
			Options::get_market()
		);
		$site_type_config = $site_type_filter->get_sitetype( $this->site_type );

		// Use case specifies if we have a static page as homepage or a list of the last posts
		if ( isset( $site_type_config['homepage'] ) && $site_type_config['homepage'] === 'static' ) {

			// Creates a page if no homepage has been set yet
			if ( get_option( 'show_on_front' ) !== 'page' ) {
				$home_page = $this->create_assistant_home_page();

				if ( $home_page ) {
					update_option( 'page_on_front', $home_page );
					update_option( 'show_on_front', 'page' );
				}
			}

		} else {
			update_option( 'show_on_front', 'posts' );
		}
	}

	public static function is_plugin_installed( $plugin_slug ) {
		$installed_plugins = get_plugins();

		foreach ( $installed_plugins as $plugin_path => $wp_plugin_data ) {
			if ( explode( '/', $plugin_path )[0] == $plugin_slug ) {
				return true;
			}
		}

		return false;
	}

	public function setup_hidden_usecase( $usecase ) {
		if ( empty( $usecase ) ) {
			return false;
		}

		$success = true;
		$usecase = $this->hidden_usecases[ $usecase ];
		foreach ( $usecase['plugins'] as $plugin ) {
			if ( self::is_plugin_installed( $plugin ) ) {
				do_action( 'strato_assistant_plugin_post_install_' . $plugin );
				$this->activate_plugins( array( $plugin ) );
				continue;
			}

			$plugin_data = array_merge(
				$this->cache_manager->get_data_from_api( 'plugin', $plugin ),
				array()
			);
			if ( Strato_Assistant_Installer::install_plugin( $plugin_data ) ) {
				do_action( 'strato_assistant_plugin_post_install_' . $plugin );
				$this->activate_plugins( array( $plugin ) );
			} else {
				$success = false;
			}
		}

		return $success;
	}

	/**
	 * Install and activate given plugin
	 *
	 * @param string $plugin_slug
	 *
	 * @return boolean
	 */
	public function setup_plugin( $plugin_slug ) {

		$site_type_filter = new Strato_Assistant_Sitetype_Filter(
			Config::get( 'sitetypes' ),
			Config::get( 'plugins' ),
			Options::get_market()
		);
		$installed = false;

		if ( ! empty( $plugin_slug ) ) {

			// Check if the plugin is already installed
			$installed = self::is_plugin_installed( $plugin_slug );

			// Install desired plugin
			if ( ! $installed ) {

				// Get metadata from the cache
				if ( $this->site_type ) {
					$plugins = $this->cache_manager->load_cache( 'plugin', $this->site_type );
				} else {
					$plugins = array();
				}

				// Load plugin data if it can't be found in the cache
				if ( ! is_array( $plugins ) || ! array_key_exists( $plugin_slug, $plugins ) ) {
					$plugin_data = array_merge(
						$this->cache_manager->get_data_from_api( 'plugin', $plugin_slug ),
						$site_type_filter->get_plugin_config( $plugin_slug )
					);
				} else {
					$plugin_data = $plugins[ $plugin_slug ];
				}

				$installed = Strato_Assistant_Installer::install_plugin( $plugin_data );
			}

			// Activate plugin once installed
			if ( $installed ) {

				// Post actions after installation
				do_action( 'strato_assistant_plugin_post_install_' . $plugin_slug );

				// Activation
				$this->activate_plugins( array( $plugin_slug ) );
				wp_redirect( admin_url( "plugins.php" ) );
			}
		}

		return $installed;
	}

	/**
	 * Install and activate a recommended theme for the current site type,
	 * chosen by the user
	 *
	 * @param string $theme_slug
	 *
	 * @return boolean
	 */
	public function setup_theme( $theme_slug ) {
		$installed = false;

		if ( ! empty( $theme_slug ) ) {

			// Check if the theme is already installed
			$installed_themes = wp_get_themes();

			if ( array_key_exists( $theme_slug, $installed_themes ) ) {
				$installed = true;
			}

			// Get theme download info and install it if not already installed
			if ( ! $installed ) {
				if ( $this->site_type ) {
					$themes = $this->cache_manager->load_cache( 'theme', $this->site_type );
				} else {
					$themes[ $theme_slug ] = $this->cache_manager->get_data_from_api( 'theme', $theme_slug );
				}
				$installed = Strato_Assistant_Installer::install_theme( $themes[ $theme_slug ] );
			}

			// Activate theme once installed
			if ( $installed ) {

				// Post actions after installation
				do_action( 'strato_assistant_theme_post_install_' . $theme_slug );

				// Activation
				switch_theme( $theme_slug );

				// Post actions after activation
				do_action( 'strato_assistant_theme_post_activate_' . $theme_slug );
			}
		}

		return $installed;
	}

	/**
	 * Activate a given set of plugins
	 *
	 * @param array $plugin_slugs
	 */
	public function activate_plugins( $plugin_slugs ) {

		// Get plugins installation paths
		$plugin_paths = Strato_Assistant_Installer::get_plugin_installation_paths( $plugin_slugs );

		// Activate the previously installed plugins
		foreach ( $plugin_paths as $plugin_slug => $plugin_path ) {
			$plugin_base_path = plugin_basename( $plugin_path );

			try {

				// Plugin activation (with activation hooks disabled)
				activate_plugin( $plugin_base_path );

				// Post actions after activation
				do_action( 'strato_assistant_plugin_post_activate_' . $plugin_slug );

			} catch ( Exception $e ) {
				error_log( $e->getMessage() );
			}
		}
	}

	/**
	 * Generate a home page if the page does not exists
	 * Return ID of the existing page if it has already been generated
	 *
	 * @return int | boolean
	 */
	function create_assistant_home_page() {

		$query = new WP_Query(
			array(
				'post_type'   => 'page',
				'post_status' => array( 'any', 'trash' ),
				'meta_query'  => array(
					array(
						'key'     => 'assistant_home_page',
						'value'   => 1,
						'compare' => '=',
					),
				),
			)
		);

		// Page already exists, regardless if it's been put in the trash or not
		if ( $query->have_posts() ) {
			$assistant_home_page = $query->posts[0];

			// Re-activate page if not published
			wp_update_post( array(
				'ID'          => $assistant_home_page->ID,
				'post_status' => 'publish'
			) );

			// Return ID
			return $assistant_home_page->ID;

		// Page doesn't exist at all and must be created
		} else {
			$assistant_home_page_id = wp_insert_post(
				array(
					'post_content'   => Strato_Assistant_View::get_template_content(
						'content/wp-default-homepage',
						array( 'url' => admin_url() )
					),
					'post_title'     => sprintf(
						__( "Welcome to %s", 'strato-assistant' ),
						home_url()
					),
					'post_status'    => 'publish',
					'post_type'      => 'page',
					'comment_status' => 'closed',
					'ping_status'    => 'open',
					'meta_input'     => array(
						'assistant_home_page' => 1
					)
				)
			);

			if ( $assistant_home_page_id instanceof WP_Error ) {
				return false;
			}

			return $assistant_home_page_id;
		}
	}
}
