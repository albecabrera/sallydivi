<?php

// Do not allow direct access!
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

use Strato\Assistant\Config;

class Strato_Create_Settings_Page {


	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function add_settings_page() {
		$brand_name = Config::get('branding.name') ?? 'Assistant';

		add_options_page(
			$brand_name . ' Settings Page',
			$brand_name,
			'manage_options',
			'strato-settings-page',
			array( $this, 'page_content' )
		);
	}

	public function page_content() {
		Strato_Assistant_View::load_template(
			'settings-page'
		);
	}

	public function register_settings() {
		$option_group_id = 'strato_assistant_settings_plugin_options';

		register_setting(
			$option_group_id,
			'strato_assistant_features_login_redesign',
			array(
				'default' => Config::get( 'features.login_redesign' )
			)
		);
		add_settings_section(
			'strato_assistant_design_settings',
			'',
			'',
			'strato_assistant_settings_plugin'
		);
		add_settings_field(
			'strato_assistant_features_login_redesign', __( 'Login design', 'strato-assistant' ),
			array(
				$this,
				'login_redesign'
			),
			'strato_assistant_settings_plugin',
			'strato_assistant_design_settings'
		);
		// Allow other plugins to register some more options with the branding
		do_action(
			'strato_assistant_register_settings',
			$option_group_id,
			Config::get( 'branding' )
		);
	}

	public function login_redesign() {
		$option = Config::get( 'features.login_redesign' );
		echo "<label id='strato_assistant_features_login_redesign_option' for='strato_assistant_features_login_redesign'>";
		echo "<input id='strato_assistant_features_login_redesign' name='strato_assistant_features_login_redesign' type='checkbox' value='1' " . checked( '1', $option, false ) . " />";
		echo "<span>" . sprintf( __( 'Use %s design for login', 'strato-assistant' ), Config::get( 'branding.name') ?? 'Assistant' ) . "</span>";
		echo "<p class='description'>" . sprintf( __( 'When activated this setting will theme the login page at %s with %s design', 'strato-assistant' ), get_admin_url(), Config::get( 'branding.name' ) ?? 'Assistant' ) . "</p></label>";
	}
}

new Strato_Create_Settings_Page();