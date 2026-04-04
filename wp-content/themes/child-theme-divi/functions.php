<?php
/*
 * This is the child theme for Divi theme, generated with Generate Child Theme plugin by catchthemes.
 *
 * (Please see https://developer.wordpress.org/themes/advanced-topics/child-themes/#how-to-create-a-child-theme)
 */
add_action( 'wp_enqueue_scripts', 'child_theme_divi_enqueue_styles' );
function child_theme_divi_enqueue_styles() {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
	wp_enqueue_style( 'child-style',
		get_stylesheet_directory_uri() . '/style.css',
		array('parent-style')
	);
}
/*
 * Your code goes below
 */
 // COPYRIGHT JAHR ALS SHORTCODE ///////////////////////////////////////////////
function bh_year_shortcode( $atts ) {
    $atts = shortcode_atts(
        array(
            'format' => 'Y',
        ),
        $atts,
        'year'
    );

    return esc_html( date( sanitize_text_field( $atts['format'] ) ) );
}
add_shortcode( 'year', 'bh_year_shortcode' );

// Force-hide newsletter popups globally.
// Divi's et_animated class triggers the animation system after window.load,
// which can override display:none. A MutationObserver watches for any style
// change that would make a popup visible, and immediately re-hides it —
// unless the newsletter button was explicitly clicked by the user.
add_action( 'wp_footer', 'bh_hide_newsletter_popups' );
function bh_hide_newsletter_popups() {
	?>
	<script>
	(function () {
		// Guard each popup independently.
		// popup1 = canvas popup (nds7mk13ev), popup2 = footer popup (jkr8gfytlw)
		var configs = [
			{
				popupSel:   '[data-interaction-target="nds7mk13ev"]',
				triggerSel: '[data-interaction-trigger="p40hyahirx"]'
			},
			{
				popupSel:   '[data-interaction-target="jkr8gfytlw"]',
				triggerSel: '[data-interaction-trigger="p40hyahirx"]'
			}
		];

		configs.forEach( function ( cfg ) {
			var popup   = document.querySelector( cfg.popupSel );
			var trigger = document.querySelector( cfg.triggerSel );
			if ( ! popup ) return;

			var intentional = false;

			function forceHide() {
				popup.style.setProperty( 'display', 'none', 'important' );
			}

			if ( trigger ) {
				trigger.addEventListener( 'click', function () {
					intentional = true;
				});
			}

			var observer = new MutationObserver( function () {
				if ( intentional ) return;
				var display = popup.style.display;
				if ( display && display !== 'none' ) {
					forceHide();
				}
			});
			observer.observe( popup, { attributes: true, attributeFilter: ['style', 'class'] } );

			forceHide();
			window.addEventListener( 'load', forceHide );
			setTimeout( forceHide, 500 );
		});
	})();
	</script>
	<?php
}
