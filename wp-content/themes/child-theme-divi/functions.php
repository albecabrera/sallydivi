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

// ── Performance & SEO ────────────────────────────────────────────────────────

// 1. Fix Divi's hardcoded viewport meta (removes user-scalable=0 / maximum-scale=1.0
//    which blocks pinch-zoom, violates WCAG and hurts Google mobile ranking).
add_action( 'template_redirect', 'bh_start_viewport_fix' );
function bh_start_viewport_fix() {
	ob_start( 'bh_replace_viewport' );
}
function bh_replace_viewport( $html ) {
	return str_replace(
		'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0',
		'width=device-width, initial-scale=1.0',
		$html
	);
}

// 2. Preconnect to Google Fonts origins so the browser opens the TCP/TLS
//    connection while the HTML is still being parsed (saves ~150-300 ms).
add_action( 'wp_head', 'bh_preconnect_fonts', 1 );
function bh_preconnect_fonts() {
	echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}

// 3. Remove WordPress emoji scripts/styles – they add an extra HTTP request
//    and inline JS on every page load without any benefit for this site.
remove_action( 'wp_head',             'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles',     'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles',  'print_emoji_styles' );
remove_filter( 'the_content_feed',    'wp_staticize_emoji' );
remove_filter( 'comment_text_rss',    'wp_staticize_emoji' );
remove_filter( 'wp_mail',             'wp_staticize_emoji_for_email' );
add_filter( 'tiny_mce_plugins', function( $plugins ) {
	return array_diff( $plugins, array( 'wpemoji' ) );
});

// 4. Remove the Windows-Tiles generator tag (unnecessary HTTP overhead).
remove_action( 'wp_head', 'msapplication_config', 10 );

// 5. Remove the WordPress version meta generator tag (minor security + cleanliness).
remove_action( 'wp_head', 'wp_generator' );

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

// Footer hamburger menu – Divi never populates et_mobile_menu inside footer
// menus, so the toggle does nothing out of the box. This script clones the
// desktop nav into a new et_mobile_menu list and wires the open/close toggle.
add_action( 'wp_footer', 'bh_footer_mobile_menu_toggle' );
function bh_footer_mobile_menu_toggle() {
	?>
	<script>
	(function () {
		// Single panel appended to body, rebuilt fresh on each open
		var mobileMenu = null;

		function buildMenu() {
			var wrap    = document.querySelector('.et_pb_menu_0_tb_footer');
			if ( ! wrap ) return null;
			var desktop = wrap.querySelector('.et_pb_menu__menu ul.et-menu');
			if ( ! desktop ) return null;

			var ul = document.createElement('ul');
			ul.className = 'bh-footer-mobile-menu';
			Array.prototype.forEach.call( desktop.children, function (li) {
				ul.appendChild( li.cloneNode(true) );
			});
			document.body.appendChild(ul);
			return ul;
		}

		function closeMenu() {
			if ( ! mobileMenu ) return;
			mobileMenu.classList.remove('open');
			var nav = document.querySelector('.et_pb_menu_0_tb_footer .mobile_nav');
			if ( nav ) { nav.classList.remove('opened'); nav.classList.add('closed'); }
		}

		var lastToggleTime = 0;

		function handleBarInteraction(e) {
			var bar = e.target.closest
				? e.target.closest('.et_pb_menu_0_tb_footer .mobile_menu_bar')
				: null;

			if ( ! bar ) {
				// tap/click outside → close
				if ( mobileMenu && ! mobileMenu.contains(e.target) ) {
					closeMenu();
				}
				return;
			}

			e.preventDefault();
			e.stopPropagation();

			// Deduplicate: touchend + click fire within ms of each other
			var now = Date.now();
			if ( now - lastToggleTime < 400 ) { return; }
			lastToggleTime = now;

			var isOpen = mobileMenu && mobileMenu.classList.contains('open');
			if ( isOpen ) {
				closeMenu();
			} else {
				if ( mobileMenu && mobileMenu.parentNode ) {
					mobileMenu.parentNode.removeChild( mobileMenu );
				}
				mobileMenu = buildMenu();
				if ( ! mobileMenu ) return;

				var rect   = bar.getBoundingClientRect();
				mobileMenu.style.setProperty('bottom', (window.innerHeight - rect.top) + 'px', 'important');
				mobileMenu.classList.add('open');

				var nav = document.querySelector('.et_pb_menu_0_tb_footer .mobile_nav');
				if ( nav ) { nav.classList.add('opened'); nav.classList.remove('closed'); }
			}
		}

		// capture:true fires BEFORE jQuery Mobile or any other handler
		document.addEventListener('touchend', handleBarInteraction, true);
		document.addEventListener('click',    handleBarInteraction, true);

		window.addEventListener('resize', function () {
			if ( mobileMenu && mobileMenu.classList.contains('open') ) {
				var bar = document.querySelector('.et_pb_menu_0_tb_footer .mobile_menu_bar');
				if ( bar ) {
					var rect = bar.getBoundingClientRect();
					mobileMenu.style.setProperty('bottom', (window.innerHeight - rect.top) + 'px', 'important');
				}
			}
		});

	})();
	</script>
	<?php
}

// Force-hide the newsletter popup on Wechseljahrecoaching.
// Divi's et_animated class triggers the animation system after window.load,
// which can override display:none. A MutationObserver watches for any style
// change that would make the popup visible, and immediately re-hides it —
// unless the newsletter button was explicitly clicked by the user.
add_action( 'wp_footer', 'bh_hide_popup_on_wechseljahre' );
function bh_hide_popup_on_wechseljahre() {
	if ( ! is_page( 'wechseljahrecoaching' ) ) {
		return;
	}
	?>
	<script>
	(function () {
		var popup     = document.querySelector('[data-interaction-target="nds7mk13ev"]');
		var trigger   = document.querySelector('[data-interaction-trigger="p40hyahirx"]');
		var intentional = false;

		if ( ! popup ) return;

		function forceHide() {
			popup.style.setProperty( 'display', 'none', 'important' );
		}

		// Mark popup as intentionally opened only when the newsletter button is clicked
		if ( trigger ) {
			trigger.addEventListener( 'click', function () {
				intentional = true;
			});
		}

		// Watch for any style/attribute change that makes the popup visible
		var observer = new MutationObserver( function () {
			if ( intentional ) return;
			var display = popup.style.display;
			if ( display && display !== 'none' ) {
				forceHide();
			}
		});
		observer.observe( popup, { attributes: true, attributeFilter: ['style', 'class'] } );

		// Also hide on load and after load to catch late Divi scripts
		forceHide();
		window.addEventListener( 'load', forceHide );
		setTimeout( forceHide, 500 );
	})();
	</script>
	<?php
}
