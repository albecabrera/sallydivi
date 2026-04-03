<?php

use Strato\Assistant\Config;
use Strato\Assistant\Options;

/**
 * Class Strato_Assistant_Handler_Dispatch
 * Computes and shows to the corresponding view of the Assistant in the WP Admin
 */
class Strato_Assistant_Handler_Dispatch {

	/** WP Admin page ID for the Assistant */
	const ASSISTANT_PAGE_ID = 'strato-assistant';

	/**
	 * Start and configure the Assistant in the WP admin area
	 */
	public static function admin_init() {

		add_action( 'admin_init', array( 'Strato_Assistant_Handler_Dispatch', 'init' ) );

		// Load Assistant single page
		add_action( 'admin_head', array( 'Strato_Assistant_Handler_Dispatch', 'load_assistant_page' ) );

		// Configure Customizer as last step
		add_action( 'init', array( 'Strato_Assistant_Handler_Dispatch', 'configure_customizer' ) );
		add_action( 'customize_controls_print_footer_scripts', array(
			'Strato_Assistant_Handler_Dispatch',
			'add_customizer_thickbox'
		) );

		// Configure AJAX hook for the themes loading
		add_action( 'wp_ajax_ajaxload', array( 'Strato_Assistant_Handler_Dispatch', 'load_recommended_themes' ) );

		// Configure AJAX hook for the theme preview
		add_action( 'wp_ajax_ajaxpreview', array( 'Strato_Assistant_Handler_Dispatch', 'load_theme_preview' ) );

		// Configure AJAX hook for the site type setup
		add_action( 'wp_ajax_ajaxsetup', array( 'Strato_Assistant_Handler_Dispatch', 'configure_site_type' ) );

		// Configure AJAX hook for the plugins & themes installation
		add_action( 'wp_ajax_ajaxinstall', array( 'Strato_Assistant_Handler_Dispatch', 'install_asset' ) );

		// Configure AJAX hook for hidden usecase installation
		add_action( 'wp_ajax_ajaxinstall_hiddenusecase', array( 'Strato_Assistant_Handler_Dispatch', 'install_hidden_usecase' ) );

		// Add Assistant scripts
		add_action( 'admin_enqueue_scripts', array( 'Strato_Assistant_Handler_Dispatch', 'enqueue_assistant_scripts' ) );

		// Add styles and fonts for the new Assistant design
		add_action( 'admin_enqueue_scripts', array( 'Strato_Assistant_Handler_Dispatch', 'enqueue_assistant_styles' ) );

		// Create and configure the wizard page in the admin area
		add_action( 'admin_menu', array( 'Strato_Assistant_Handler_Dispatch', 'add_admin_menu_wizard_page' ), 5 );
	}

	public static function init() {
		if ( ! self::is_assistant_admin_page() ) {
			return;
		}

		if ( ! empty( $_GET['setup_action'] ) && ! empty( $_GET['usecase'] ) && $_GET['setup_action'] === 'partner'  ) {
			include_once( Strato_Assistant::get_inc_dir_path() . 'assets-manager.php' );

			$assets_manager = new \Strato_Assistant_Assets_Manager();
			if ( ! array_key_exists( $_GET['usecase'], $assets_manager->hidden_usecases ) || ! isset( $_GET['coupon'] ) ) {
				wp_redirect( admin_url() );
			}
		}
	}

	/**
	 * Check if we are in the Assistant context
	 *
	 * @return boolean
	 */
	public static function is_assistant_admin_page() {
		return ( isset( $_GET['page'] ) && ( $_GET['page'] === self::ASSISTANT_PAGE_ID ) );
	}

	/**
	 * Check if we are on a wp-login page
	 *
	 * @return boolean
	 */
	public static function is_login_page() {
		return false !== stripos( wp_login_url(), $_SERVER['SCRIPT_NAME'] );
	}

	/**
	 * Check if we are in the Customizer Step after the Assistant
	 * (in the Assistant context, identified by the "message" URL param)
	 *
	 * @param string $with_msg
	 *
	 * @return boolean
	 */
	public static function is_customizer_page( $with_msg = null ) {
		global $wp_customize;

		$is_customizer_page = $wp_customize instanceof WP_Customize_Manager
		                      && $wp_customize->is_preview();

		if ( $with_msg ) {
			return $is_customizer_page
			       && isset( $_GET['message'] )
			       && $_GET['message'] == esc_attr( $with_msg );
		} else {
			return $is_customizer_page;
		}
	}

	/**
	 * Create and configure the Assistant page in the admin area
	 * WP Hook https://codex.wordpress.org/Plugin_API/Action_Reference/admin_menu
	 */
	public static function add_admin_menu_wizard_page() {
		global $menu;

		$pos = 50;
		$posp1 = $pos + 1;

		while ( isset( $menu[ $pos ] ) || isset( $menu[ $posp1 ] ) ) {
			$pos ++;
			$posp1 = $pos + 1;

			/** check that there is no menu at our level neither at ourlevel+1 because that will make us disappear in some case */
			if ( ! isset( $menu[ $pos ] ) && isset( $menu[ $posp1 ] ) ) {
				$pos = $pos + 2;
			}
		}

		add_menu_page(
			__( 'Assistant', 'strato-assistant' ),
			__( 'Assistant', 'strato-assistant' ),
			'manage_options',
			self::ASSISTANT_PAGE_ID,
			function () {
			},
			'dashicons-universal-access-alt',
			$pos
		);

	}

	/**
	 * Handle status change of the wizard anywhere in the admin area (via GET parameters)
	 * WP Hook https://codex.wordpress.org/Plugin_API/Action_Reference/admin_init
	 */
	public static function handle_assistant_params() {

		/** reset the wizard (restart from the beginning) */
		if ( isset( $_GET['strato-assistant-reset'] ) ) {
			delete_option( 'strato_assistant_completed' );
			delete_option( 'strato_assistant_sitetype' );
		}

		/** skip the wizard completely (the user won't be bother by it anymore) */
		if ( isset( $_GET['strato-assistant-cancel'] ) ) {
			update_option( 'strato_assistant_completed', true );
		}
	}

	/**
	 * Load the themes list for a given site type (AJAX)
	 */
	public static function load_recommended_themes() {

		if ( isset( $_POST['site_type'] ) ) {
			$cache_manager = new Strato_Assistant_Cache_Manager();
			$site_type_filter = new Strato_Assistant_Sitetype_Filter(
				Config::get( 'sitetypes' ),
				Config::get( 'plugins' ),
				Options::get_market()
			);

			$site_type = sanitize_text_field( $_POST['site_type'] );

			// Create cache file if not created yet
			if ( ! $cache_manager->has_cache( 'theme', $site_type ) ) {
				$theme_slugs = $site_type_filter->get_theme_slugs( $site_type );
				$cache_manager->fill_theme_cache( $site_type, $theme_slugs );
			}

			// Load theme data from cache
			$themes = $cache_manager->load_cache( 'theme', $site_type );

			// Flag the current active theme in the list for information
			$active_theme_slug = wp_get_theme()->get_stylesheet();

			if ( array_key_exists( $active_theme_slug, $themes ) ) {
				$themes[ $active_theme_slug ]['active'] = true;
			}

			Strato_Assistant_View::load_template( 'parts/site-type-theme-list', array(
				'site_type' => $site_type,
				'themes'    => $themes
			) );
		}
		die;
	}

	/**
	 * Load the preview of a selected theme (AJAX)
	 */
	public static function load_theme_preview() {
		$max_word_count = 80;

		if ( isset( $_POST['theme'] ) && isset( $_POST['site_type'] ) ) {
			$cache_manager = new Strato_Assistant_Cache_Manager();
			$site_type_filter = new Strato_Assistant_Sitetype_Filter(
				Config::get( 'sitetypes' ),
				Config::get( 'plugins' ),
				Options::get_market()
			);

			$theme_slug = sanitize_text_field( $_POST['theme'] );
			$site_type = sanitize_text_field( $_POST['site_type'] );

			if ( $cache_manager->has_cache( 'theme', $site_type ) ) {
				$themes = $cache_manager->load_cache( 'theme', $site_type );

				// Deliver a short/truncated version of the description if it's too long
				if ( str_word_count( $themes[ $theme_slug ]['description'], 0 ) > $max_word_count ) {
					$word_count = array_keys(
						str_word_count( $themes[ $theme_slug ]['description'], 2 )
					);
					$themes[ $theme_slug ]['short_description'] = trim( substr(
						$themes[ $theme_slug ]['description'],
						0,
						$word_count[ $max_word_count ]
					) ) . ' [...]';
				}

				// Flag the current active theme in the list for information
				$active_theme_slug = wp_get_theme()->get_stylesheet();

				if ( array_key_exists( $active_theme_slug, $themes ) ) {
					$themes[ $active_theme_slug ]['active'] = true;
				}

				// Get plugins to install for this site type
				$plugins = $site_type_filter->get_plugins( $site_type );

				// Create cache file if not created yet
				if ( ! $cache_manager->has_cache( 'plugin', $site_type ) ) {
					$cache_manager->fill_plugin_cache( $site_type, $plugins );
				}

				Strato_Assistant_View::load_template( 'parts/theme-preview', array(
					'theme'     => $themes[ $theme_slug ],
					'plugins'   => array_keys( $plugins ),
					'site_type' => $site_type,
					'redirect_url' => add_query_arg(
						array(
							'return'  => urlencode(home_url()),
							'message' => 'congrats'
						),
						admin_url( 'customize.php' )
					)
				) );
			}
		}
		die;
	}

	/**
	 * Configure the site type before installing assets (AJAX)
	 */
	public static function configure_site_type() {

		try {
			if ( isset( $_POST['site_type'] ) ) {
				include_once( Strato_Assistant::get_inc_dir_path() . 'assets-manager.php' );

				$site_type = sanitize_text_field( $_POST['site_type'] );
				$assets_manager = new Strato_Assistant_Assets_Manager( $site_type );

				// Check nonce
				check_admin_referer( 'activate' );

				// Set up WordPress to the use case
				$assets_manager->setup_options();

				wp_send_json_success();

			} else {
				wp_send_json_error();
			}
		} catch ( Exception $e ) {
			wp_send_json_error();
		}
	}

	/**
	 * Install selected plugin and/or theme (AJAX)
	 */
	public static function install_asset() {

		try {
			if ( isset( $_POST['site_type'] ) && isset( $_POST['asset'] ) && isset( $_POST['asset_type'] ) ) {
				include_once( Strato_Assistant::get_inc_dir_path() . 'assets-manager.php' );

				$site_type = sanitize_text_field( $_POST['site_type'] );
				$asset = sanitize_text_field( $_POST['asset'] );
				$asset_type = sanitize_text_field( $_POST['asset_type'] );

				$assets_manager = new Strato_Assistant_Assets_Manager( $site_type );

				// Check nonce
				check_admin_referer( 'activate' );

				// Activate / install chosen asset
				switch ( $asset_type ) {
					case 'theme':
						$assets_manager->setup_theme( $asset );
						break;
					case 'plugin':
						$assets_manager->setup_plugin( $asset );
						break;
				}

				// Store website type in DB
				update_option( 'strato_assistant_sitetype', $site_type );

				wp_send_json_success();

			} else {
				wp_send_json_error();
			}
		} catch ( Exception $e ) {
			wp_send_json_error();
		}
	}

	public static function install_hidden_usecase() {
		include_once( Strato_Assistant::get_inc_dir_path() . 'assets-manager.php' );

		try {
			if ( isset( $_POST['usecase'] ) ) {
				check_admin_referer( 'activate' );
				$usecase = sanitize_text_field( $_POST['usecase'] );
				$assets_manager = new \Strato_Assistant_Assets_Manager;
				if ( $assets_manager->setup_hidden_usecase( $usecase ) ) {
					wp_send_json_success();
				}
			}
		} catch ( Exception $e ) {
			wp_send_json_error();
		}
	}

	/**
	 * Register the CSS and fonts for the new Assistant design
	 * (used in the Assistant & Login)
	 */
	public static function enqueue_assistant_styles() {

		// Remove WP standard CSS in the Assistant pages
		if ( self::is_assistant_admin_page() ) {
			wp_deregister_style( 'wp-admin' );
		}

		// Add the Assistant CSS in the Assistant pages & where the Assistant adds features
		if ( self::is_login_page() || self::is_assistant_admin_page() || self::is_customizer_page( 'congrats' ) ) {
			wp_enqueue_style(
				'strato-assistant',
				Strato_Assistant::get_css_url( 'assistant.css' ),
				array( 'buttons' ),
				filemtime( Strato_Assistant::get_css_path( 'assistant.css' ) )
			);
			wp_add_inline_style( 'strato-assistant', Strato_Assistant_Branding::get_color_styles() );

			if ( Config::get('custom-css.css' ) !== false ) {
				wp_add_inline_style( 'strato-assistant', Config::get('custom-css.css') );
			}
		}
	}

	/**
	 * Register JS scripts for the Assistant
	 */
	public static function enqueue_assistant_scripts() {

		if ( self::is_assistant_admin_page() || self::is_customizer_page( 'congrats' ) ) {

			// Add the assistant JS scripts for use case filter + installation
			wp_enqueue_script(
				'strato-assistant',
				Strato_Assistant::get_js_url( 'assistant.js' ),
				array(
					'jquery',
					'wp-util'
				),
				filemtime( Strato_Assistant::get_js_path( 'assistant.js' ) ),
				true
			);

			// Configure the AJAX object for the assistant scripts
			wp_localize_script( 'strato-assistant', 'ajax_assistant_object', array(
				'ajaxurl' => admin_url( 'admin-ajax.php' )
			) );
		}
	}

	/**
	 * Add WP lightbox library (thickbox) in the Customizer page
	 * when we come from the Assistant context
	 */
	public static function configure_customizer() {
		if ( self::is_customizer_page( 'congrats' ) ) {
			add_thickbox();
		}
	}

	/**
	 * Add lightbox content in the Customizer
	 * when the Assistant is completed for the first time
	 */
	public static function add_customizer_thickbox() {
		if ( get_option( 'strato_assistant_completed', false ) == false ) {

			/// Sets flag for the assistant being completed
			update_option( 'strato_assistant_completed', true );

			// Render lightbox HTML (the lightbox won't open if this content isn't there)
			Strato_Assistant_View::load_template( 'customizer-congrats-step' );
		}
	}

	/**
	 * Show the single-page Assistant
	 * (Load specific view if a current action is given)
	 */
	public static function load_assistant_page() {

		// Handle status change of the wizard
		self::handle_assistant_params();

		// Only call our process in the Assistant Admin page!
		if ( self::is_assistant_admin_page() ) {
			$usecase_name = null;
			$usecase_data = null;
			if ( ! empty( $_GET['setup_action'] ) && ! empty( $_GET['usecase'] ) && $_GET['setup_action'] === 'partner'  ) {
				include_once( Strato_Assistant::get_inc_dir_path() . 'assets-manager.php' );

				$assets_manager = new \Strato_Assistant_Assets_Manager();
				$usecase_name = sanitize_text_field( $_GET['usecase'] );
				$usecase_data = $assets_manager->hidden_usecases[ $_GET['usecase'] ];
			}

			$site_type_filter = new Strato_Assistant_Sitetype_Filter(
				Config::get( 'sitetypes' ),
				Config::get( 'plugins' ),
				Options::get_market()
			);
			$site_types = $site_type_filter->get_sitetypes();

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die(
					sprintf( __( 'Sorry, you do not have permission to access the %s.', 'strato-assistant' ) ),
					__( 'Assistant', 'strato-assistant' )
				);
			}

			Strato_Assistant_View::load_template( 'assistant', array(
				'site_types' => $site_types,
				'usecase_name' => $usecase_name,
				'usecase_data' => $usecase_data
			) );
			exit;
		}
	}
}
