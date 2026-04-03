<?php

// Do not allow direct access!
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Forbidden' );
}

/**
 * Class Strato_Assistant_Handler_Login
 *
 * Configure the alternative login page
 */
class Strato_Assistant_Handler_Login {

	/**
	 * Initialize the redirections/hooks in the front-end
	 * N.B. new design & behaviour is an extra feature that needs to be activated
	 *
	 * @param boolean $design_enabled
	 */
	public static function init( $design_enabled = true ) {

		// Redirect to assistant after first login
		add_filter( 'login_redirect', array( 'Strato_Assistant_Handler_Login', 'automatic_redirect_after_login' ), 90, 3 );
		add_action( 'one_time_login_after_auth_cookie_set', array( 'Strato_Assistant_Handler_Login', 'redirect_after_otl' ), 10, 1 );

		// Add the cookie script to control feature switches through JS
		add_action( 'login_enqueue_scripts', array( 'Strato_Assistant_Handler_Login', 'enqueue_cookie_script' ) );

		if ( $design_enabled ) {

			// Add the tweaks to adjust visual details
			add_filter( 'login_body_class', array( 'Strato_Assistant_Handler_Login', 'add_body_class' ) );
			add_filter( 'login_link_separator', array( 'Strato_Assistant_Handler_Login', 'remove_login_link_separator' ) );

			// Add the alternative login scripts
			add_action( 'login_enqueue_scripts', array( 'Strato_Assistant_Handler_Login', 'enqueue_scripts' ) );

			// Modify / Add some HTML components for the styles and animations to work
			add_action( 'login_header', function () { self::load_template_part( 'header' ); } );
			add_action( 'login_footer', function () { self::load_template_part( 'footer' ); } );
		}
	}

	/**
	 * Handle redirection to the Assistant after specific login with the "one-time-login" plugin
	 * https://wordpress.org/plugins/one-time-login/
	 *
	 * @param WP_User $user
	 */
	public static function redirect_after_otl( $user ) {
		wp_safe_redirect( self::redirect_after_login( $user, admin_url() ) );
		exit;
	}

	/**
	 * Handle redirection to the Assistant after first login
	 * (Administrators only: other roles don't have the permissions to use the Assistant)
	 *
	 * @param  WP_User $logged_user
	 * @param  string  $redirect_to
	 * @return string
	 */
	public static function redirect_after_login( $logged_user, $redirect_to ) {
		$current_user_authorized = ( $logged_user instanceof WP_User && $logged_user->has_cap( 'manage_options' ) );

		if ( $current_user_authorized ) {
			if ( isset( $_GET['setup_action'] )
			     && $_GET['setup_action'] == 'partner'
			     && ! empty( $usecase = $_GET['usecase'] ) ) {

				return admin_url( 'admin.php?page=' . Strato_Assistant_Handler_Dispatch::ASSISTANT_PAGE_ID . '&setup_action=partner&usecase=' . $usecase );
			}
			else if( get_option( 'strato_assistant_completed' ) == false &&
			    ( ! Strato\Assistant\Config::get( 'features.php8_warning' ) ||
			      PHP_MAJOR_VERSION < 8 ) ) {

				return admin_url( 'admin.php?page=' . Strato_Assistant_Handler_Dispatch::ASSISTANT_PAGE_ID . '&setup_action=greeting' );
			}
		}

		return $redirect_to;
	}

	/**
	 * Handle redirection to the Assistant after first login,
	 * in the WP Standard login (when the "redesign" feature is not activated)
	 *
	 * @param  string $redirect_to
	 * @return string
	 */
	public static function automatic_redirect_after_login( $redirect_to ) {
		global $user;
		return self::redirect_after_login( $user, $redirect_to );
	}

	/**
	 * Add the cookie script to control feature switches through JS
	 */
	public static function enqueue_cookie_script() {
		wp_enqueue_script(
			'strato-assistant-wp-cookies',
			Strato_Assistant::get_js_url( 'cookies.js' ),
			array(),
			filemtime( Strato_Assistant::get_js_path( 'cookies.js' ) )
		);
	}

	/**
	 * Add the alternative login scripts
	 */
	public static function enqueue_scripts() {
		if ( ! self::is_interim_login_screen() ) {

			// Add Assistant CSS and fonts
			Strato_Assistant_Handler_Dispatch::enqueue_assistant_styles();

			// Add Login specific CSS styles
			wp_enqueue_style(
				'strato-assistant-wp-login',
				Strato_Assistant::get_css_url( 'login.css' ),
				array( 'buttons' ),
				filemtime( Strato_Assistant::get_css_path( 'login.css' ) )
			);
		}
	}

	/**
	 * Add the special CSS classes to the login page
	 *
	 * @param  array $classes
	 * @return array
	 */
	public static function add_body_class( $classes ) {
		$classes[] = 'assistant-page';
		return $classes;
	}

	/**
	 * Remove the " | " login link separator
	 * (not needed in our design and breaking it anyway)
	 *
	 * @param  string $separator
	 * @return string
	 */
	public static function remove_login_link_separator( $separator ) {
		return '';
	}

	/**
	 * Extends the login form HTML code with template parts
	 *
	 * @param string $template
	 */
	public static function load_template_part( $template ) {
		if ( ! self::is_interim_login_screen() ) {
			Strato_Assistant_View::load_template( 'parts/login-' . $template );
		}
	}

	/**
	 * Check if we aren't in the lightbox-login (interim login)
	 * that appears when you're in the back-end and your session times out
	 *
	 * @return boolean
	 */
	public static function is_interim_login_screen() {
		global $interim_login;
		return $interim_login;
	}
}
