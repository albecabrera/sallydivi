<?php
// Do not allow direct access!
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Forbidden' );
}

class Strato_Assistant_Modify_Settings_Page {

	public function __construct() {
		if ( Strato\Assistant\Config::get( 'features.domain_settings' ) ) {
			add_action( 'admin_head', array( $this, 'change_home_description' ) );
		}
	}

	public function change_home_description() {
		global $pagenow;

		$cp_application_link = Strato\Assistant\Config::get( 'links.control_panel_applications_' . Strato\Assistant\Options::get_market() );

		if ( is_admin() && $pagenow == 'options-general.php' && $cp_application_link ) {

			$websiteUrlDescription = sprintf(
				__( 'Website-Url-Description', 'strato-assistant' ),
				$cp_application_link,
				Strato_Assistant_Branding::get_brand_name()
			);

			?>
			<style type="text/css">
				#home-description {
					display: none;
				}
			</style>
			<script type="text/javascript">
				(function ($) {
					$(document).ready(function () {
						$('#siteurl').parent().append('<p class="description"><?php echo addslashes( $websiteUrlDescription ); ?></p>');
						$('#home').parent().append('<p class="description"><?php echo addslashes( $websiteUrlDescription ); ?></p>');
						$('.update-nag').hide();
					});
				})(jQuery);
			</script>
			<?php
		}
	}
}

new Strato_Assistant_Modify_Settings_Page();
