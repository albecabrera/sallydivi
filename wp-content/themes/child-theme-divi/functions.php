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

// ── WordPress Cleanup ────────────────────────────────────────────────────────

// Remove unused wp_head noise
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
remove_action( 'wp_head', 'wp_oembed_add_host_js' );
remove_action( 'wp_head', 'rest_output_link_wp_head' );
remove_action( 'wp_head', 'feed_links_extra', 3 );

// Disable XML-RPC completely
add_filter( 'xmlrpc_enabled', '__return_false' );

// Remove X-Pingback header
add_filter( 'wp_headers', function( $headers ) {
    unset( $headers['X-Pingback'] );
    return $headers;
} );

// Disable trackbacks on new posts
add_filter( 'default_ping_status', '__return_false' );

// Remove ?ver= query strings from CSS/JS (improves proxy/CDN caching)
function bh_remove_ver_strings( $src ) {
    if ( strpos( $src, '?ver=' ) !== false ) {
        $src = remove_query_arg( 'ver', $src );
    }
    return $src;
}
add_filter( 'style_loader_src',  'bh_remove_ver_strings', 15 );
add_filter( 'script_loader_src', 'bh_remove_ver_strings', 15 );

// ── Security ─────────────────────────────────────────────────────────────────

// Block user enumeration via ?author=N
add_action( 'template_redirect', function() {
    if ( ! is_admin() && ! is_user_logged_in() && isset( $_GET['author'] ) ) {
        wp_safe_redirect( home_url( '/' ), 301 );
        exit;
    }
} );

// Obscure login error messages
add_filter( 'login_errors', function() {
    return 'Anmeldedaten falsch.';
} );

// Security response headers
add_action( 'send_headers', function() {
    header( 'X-Content-Type-Options: nosniff' );
    header( 'X-Frame-Options: SAMEORIGIN' );
    header( 'X-XSS-Protection: 1; mode=block' );
    header( 'Referrer-Policy: strict-origin-when-cross-origin' );
    header( 'Permissions-Policy: geolocation=(), microphone=(), camera=()' );
} );

// ── SEO: Resource hints ───────────────────────────────────────────────────────

// dns-prefetch as fallback for browsers that don't support preconnect
add_action( 'wp_head', function() {
    echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">' . "\n";
    echo '<link rel="dns-prefetch" href="//fonts.gstatic.com">' . "\n";
}, 0 );

// ── SEO: Open Graph + Twitter Card ───────────────────────────────────────────
// Note: disable Divi's built-in OG output (Divi > Theme Options > SEO) if
// you see duplicate meta tags in the page source.

add_action( 'wp_head', 'bh_social_meta', 4 );
function bh_social_meta() {
    global $post;

    $site_name = get_bloginfo( 'name' );
    $site_url  = home_url( '/' );

    if ( is_singular() && $post ) {
        $title = get_the_title( $post );
        $raw   = has_excerpt( $post )
            ? get_the_excerpt( $post )
            : wp_trim_words( wp_strip_all_tags( get_the_content( null, false, $post ) ), 30, '...' );
        $url   = get_permalink( $post );
        $type  = 'article';
        $img   = get_the_post_thumbnail_url( $post, 'large' );
    } else {
        $title = $site_name;
        $raw   = get_bloginfo( 'description' );
        $url   = $site_url;
        $type  = 'website';
        $img   = '';
    }

    if ( ! $img ) {
        $logo_id = get_theme_mod( 'custom_logo' );
        if ( $logo_id ) {
            $img = wp_get_attachment_image_url( $logo_id, 'large' );
        }
    }

    $desc = esc_attr( wp_strip_all_tags( $raw ) );
    $t    = esc_attr( $title );

    $tags = [
        [ 'property' => 'og:type',        'content' => $type ],
        [ 'property' => 'og:title',       'content' => $t ],
        [ 'property' => 'og:description', 'content' => $desc ],
        [ 'property' => 'og:url',         'content' => esc_url( $url ) ],
        [ 'property' => 'og:site_name',   'content' => esc_attr( $site_name ) ],
        [ 'property' => 'og:locale',      'content' => 'de_DE' ],
        [ 'name'     => 'twitter:card',        'content' => 'summary_large_image' ],
        [ 'name'     => 'twitter:title',       'content' => $t ],
        [ 'name'     => 'twitter:description', 'content' => $desc ],
    ];

    if ( $img ) {
        $tags[] = [ 'property' => 'og:image',      'content' => esc_url( $img ) ];
        $tags[] = [ 'name'     => 'twitter:image', 'content' => esc_url( $img ) ];
    }

    foreach ( $tags as $tag ) {
        if ( isset( $tag['property'] ) ) {
            echo '<meta property="' . $tag['property'] . '" content="' . $tag['content'] . '">' . "\n";
        } else {
            echo '<meta name="' . $tag['name'] . '" content="' . $tag['content'] . '">' . "\n";
        }
    }
}

// ── SEO: Schema.org JSON-LD ───────────────────────────────────────────────────

add_action( 'wp_head', 'bh_schema_json_ld', 5 );
function bh_schema_json_ld() {
    $url  = home_url( '/' );
    $name = get_bloginfo( 'name' );
    $desc = get_bloginfo( 'description' );

    $schema = [
        '@context' => 'https://schema.org',
        '@graph'   => [
            [
                '@type'       => 'WebSite',
                '@id'         => $url . '#website',
                'url'         => $url,
                'name'        => $name,
                'description' => $desc,
                'inLanguage'  => 'de-DE',
                'potentialAction' => [
                    '@type'       => 'SearchAction',
                    'target'      => [
                        '@type'       => 'EntryPoint',
                        'urlTemplate' => $url . '?s={search_term_string}',
                    ],
                    'query-input' => 'required name=search_term_string',
                ],
            ],
            [
                '@type'    => 'Person',
                '@id'      => $url . '#person',
                'name'     => $name,
                'url'      => $url,
                'jobTitle' => 'Menopause Coach',
                'sameAs'   => [],
            ],
        ],
    ];

    echo '<script type="application/ld+json">'
        . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
        . '</script>' . "\n";
}

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
