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
    // Preconnect: eliminates DNS + TLS handshake latency for critical origins
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
    // DNS-prefetch: fallback for browsers that don't support preconnect
    echo '<link rel="dns-prefetch" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="dns-prefetch" href="https://fonts.gstatic.com">' . "\n";
    // Preload Roboto 400 + 700 (the two weights used site-wide) so they're
    // fetched before the render-blocking Google Fonts stylesheet resolves.
    echo '<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap">' . "\n";
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

// Lazy + async decode for all WordPress attachment images.
add_filter( 'wp_get_attachment_image_attributes', function( $attr ) {
    if ( empty( $attr['loading'] ) ) {
        $attr['loading'] = 'lazy';
    }
    if ( empty( $attr['decoding'] ) ) {
        $attr['decoding'] = 'async';
    }
    return $attr;
}, 20 );

// Native lazy loading for iframes (Instagram embeds, maps, etc.)
add_filter( 'the_content', function( $content ) {
    return str_replace( '<iframe ', '<iframe loading="lazy" ', $content );
}, 20 );

// Defer non-critical scripts that don't need the parser.
// Skips scripts already marked defer/async and the jQuery dependency chain.
add_filter( 'script_loader_tag', function( $tag, $handle, $src ) {
    $skip = [ 'jquery', 'jquery-core', 'jquery-migrate', 'et-builder-modules-script',
               'et_builder_5-root', 'et_builder_5-app-ui', 'et-core-common' ];
    if ( in_array( $handle, $skip, true ) ) {
        return $tag;
    }
    if ( strpos( $tag, ' defer' ) !== false || strpos( $tag, ' async' ) !== false ) {
        return $tag;
    }
    return str_replace( ' src=', ' defer src=', $tag );
}, 10, 3 );

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

// Header sidenav – panel deslizante desde la izquierda para mobile/tablet.
// Intercepta el click de la hamburguesa de Divi (capture:true) antes de que
// Divi abra su dropdown nativo, y abre el sidenav personalizado en su lugar.
add_action( 'wp_footer', 'bh_header_sidenav' );
function bh_header_sidenav() {
	?>
	<script>
	(function () {
		if ( window.innerWidth >= 980 ) return;

		// Crear overlay
		var overlay = document.createElement('div');
		overlay.className = 'bh-sidenav-overlay';
		document.body.appendChild(overlay);

		// Crear panel sidenav
		var sidenav = document.createElement('nav');
		sidenav.className = 'bh-sidenav';
		sidenav.setAttribute('aria-label', 'Navegación principal');

		// Clonar los enlaces del menú de escritorio del header
		var headerMenu = document.querySelector('.social_header_sec1 .et_pb_menu__menu ul.et-menu');
		if ( headerMenu ) {
			var clone = headerMenu.cloneNode(true);
			clone.removeAttribute('id');
			sidenav.appendChild(clone);
		}

		document.body.appendChild(sidenav);

		var isOpen = false;
		var mobileNav = document.querySelector('.social_header_sec1 .mobile_nav');

		function openNav() {
			sidenav.classList.add('open');
			overlay.classList.add('open');
			document.body.classList.add('bh-sidenav-open');
			if ( mobileNav ) { mobileNav.classList.remove('closed'); mobileNav.classList.add('opened'); }
			isOpen = true;
		}

		function closeNav() {
			sidenav.classList.remove('open');
			overlay.classList.remove('open');
			document.body.classList.remove('bh-sidenav-open');
			if ( mobileNav ) { mobileNav.classList.remove('opened'); mobileNav.classList.add('closed'); }
			isOpen = false;
		}

		// Interceptar click de la hamburguesa ANTES que Divi (capture: true)
		var bar = document.querySelector('.social_header_sec1 .mobile_menu_bar');
		if ( bar ) {
			bar.addEventListener('click', function (e) {
				e.stopImmediatePropagation();
				e.preventDefault();
				isOpen ? closeNav() : openNav();
			}, true);
		}

		// Cerrar al tocar el overlay
		overlay.addEventListener('click', closeNav);

		// Cerrar con ESC
		document.addEventListener('keydown', function (e) {
			if ( e.key === 'Escape' && isOpen ) closeNav();
		});

		// Cerrar al navegar a un enlace dentro del sidenav
		sidenav.addEventListener('click', function (e) {
			if ( e.target.tagName === 'A' && e.target.getAttribute('href') ) closeNav();
		});

		// Reset si el viewport crece a desktop
		window.addEventListener('resize', function () {
			if ( window.innerWidth >= 980 && isOpen ) closeNav();
		});
	})();
	</script>
	<?php
}

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

// Newsletter popup guard for Wechseljahrecoaching.
//
// WHY THREE LAYERS:
// Divi's interaction system generates a <style> that hides the popup on load,
// then REMOVES that <style> tag during JS init — causing a visible flash.
// After that Divi fires its own "removeVisibility" effect, but timing is
// unreliable and on some devices it never arrives.
//
// LAYER 1 — wp_head (priority 9999): a <style> block injected AFTER all Divi
//   inline CSS, targeting both the dynamic interaction class and the stable
//   section class. This blocks the flash at first paint.
//
// LAYER 2 — wp_footer JS: monkey-patches setProperty / removeProperty on the
//   popup element so Divi cannot override our display:none via inline style
//   until the user intentionally clicks the newsletter button.
//
// LAYER 3 — Two MutationObservers: one watches the popup attributes (class /
//   style), the other watches <head> childList so we catch the exact moment
//   Divi removes its own <style> tag and can immediately re-hide.
//
// When the user clicks the trigger button, release() restores everything and
// removes the <head> style so Divi can show the popup normally.

add_action( 'wp_head', 'bh_popup_head_style', 9999 );
function bh_popup_head_style() {
	if ( ! is_page( 'wechseljahrecoaching' ) ) return;
	?>
	<style id="bh-nl-popup-hide">
	.et-interaction-target-nds7mk13ev,
	.et_pb_section_3_tb_footer {
		display:        none    !important;
		visibility:     hidden  !important;
		opacity:        0       !important;
		pointer-events: none    !important;
	}
	</style>
	<?php
}

add_action( 'wp_footer', 'bh_hide_popup_on_wechseljahre' );
function bh_hide_popup_on_wechseljahre() {
	if ( ! is_page( 'wechseljahrecoaching' ) ) return;
	?>
	<script>
	(function () {
		var popup = document.querySelector('.et-interaction-target-nds7mk13ev')
		         || document.querySelector('.et_pb_section_3_tb_footer');
		var trigger = document.querySelector('[data-interaction-trigger="p40hyahirx"]');

		if (!popup) return;

		var intentional = false;

		function hide() {
			popup.style.setProperty('display',        'none',   'important');
			popup.style.setProperty('visibility',     'hidden', 'important');
			popup.style.setProperty('opacity',        '0',      'important');
			popup.style.setProperty('pointer-events', 'none',   'important');
		}

		function release() {
			intentional = true;
			// Remove the <head> style so Divi can show the popup via its own CSS
			var s = document.getElementById('bh-nl-popup-hide');
			if (s) s.parentNode.removeChild(s);
			// Remove all inline overrides so Divi's open animation works
			popup.style.removeProperty('display');
			popup.style.removeProperty('visibility');
			popup.style.removeProperty('opacity');
			popup.style.removeProperty('pointer-events');
			// Restore native style methods
			try { delete popup.style.setProperty;    } catch(e) {}
			try { delete popup.style.removeProperty; } catch(e) {}
			popupObs.disconnect();
			headObs.disconnect();
		}

		// LAYER 2a – intercept setProperty: block any Divi "show" calls
		try {
			var nativeSP = CSSStyleDeclaration.prototype.setProperty;
			popup.style.setProperty = function (prop, val, priority) {
				if (!intentional && prop === 'display' && val !== 'none') {
					return nativeSP.call(this, 'display', 'none', 'important');
				}
				return nativeSP.call(this, prop, val, priority);
			};
		} catch(e) {}

		// LAYER 2b – intercept removeProperty: keep display:none from being removed
		try {
			var nativeRP = CSSStyleDeclaration.prototype.removeProperty;
			popup.style.removeProperty = function (prop) {
				if (!intentional && prop === 'display') return '';
				return nativeRP.call(this, prop);
			};
		} catch(e) {}

		// Release all guards when user explicitly triggers the popup
		if (trigger) {
			trigger.addEventListener('click', release, true);
		}

		// LAYER 3a – watch popup attributes (inline style / class changes)
		var popupObs = new MutationObserver(function () {
			if (intentional) { popupObs.disconnect(); return; }
			if (window.getComputedStyle(popup).display !== 'none') hide();
		});
		popupObs.observe(popup, { attributes: true, attributeFilter: ['style', 'class'] });

		// LAYER 3b – watch <head> childList to catch Divi removing its own <style>
		var headObs = new MutationObserver(function () {
			if (intentional) { headObs.disconnect(); return; }
			if (window.getComputedStyle(popup).display !== 'none') hide();
		});
		headObs.observe(document.head, { childList: true });

		// Immediate hide + re-check on load
		hide();
		window.addEventListener('load', function () {
			if (!intentional) hide();
		});
	})();
	</script>
	<?php
}

// Über mich photo overlay disabled:
// it was duplicating/overlaying the portrait and could make the hero image look split.

// ── Footer: force "Impressum" link to WP editable page ─────────────────────
add_action( 'init', 'bh_ensure_impressum_page' );
function bh_ensure_impressum_page() {
	$page = get_page_by_path( 'impressum', OBJECT, 'page' );
	if ( $page ) return;

	$content = '<h2>Impressum</h2>
<p>Frau Sally Bolinger<br>Im Hasengarten 37<br>50996 Köln<br>E-Mail: <a href="mailto:bolinger.sally@gmail.com">bolinger.sally@gmail.com</a><br>Nutzen Sie gern auch die Nachrichtenfunktion von Instagram oder Facebook zur Kontaktaufnahme.</p>
<h3>Verantwortlich für redaktionelle Inhalte</h3>
<p>Frau Sally Bolinger<br>Als Kleinunternehmer ist Frau Sally Bolinger von der Umsatzsteuer gemäss Paragraph 19 Umsatzsteuergesetz befreit.</p>
<h3>Verbraucher - Streitschlichtung</h3>
<p>Die EU-Kommission hat eine Internetplatform zur Online-Beilegung von Streitigkeiten betreffend vertraglicher Verpflichtungen aus Online Verträgen geschaffen (OS-Platform). Sie können die OS-Platform unter dem folgenden Link erreichen: <a href="http://ec.europa.eu/consumers/odr/">http://ec.europa.eu/consumers/odr/</a> Wir sind nicht bereit und nicht verpflichtet an einem Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle teilzunehmen.</p>
<h3>Haftung für Links</h3>
<p>Externe Links in unserem Account führen ggf. Zu Inhalten fremder Anbieter. Für diese Inhalte ist alleine der jeweilige Anbieter verantwortlich. Bei bekannt werden von Rechtsverletzungen werden diese Links umgehend entfernt.</p>
<h3>Urheberrechtshinweis</h3>
<p>Die durch uns erstellten Inhalte und Werke unterliegen dem deutschen Urheberrecht. Die Vervielfältigung, Bearbeitung, Verbreitung und jede Art der Verwertung ausserhalb der Grenzen des Urheberrechts bedürfen der schriftlichen Zustimmung des jeweiligen Autors bzw. Erstellers. Soweit die Inhalte auf unserem Account nicht von uns selbst erstellt wurden, werden Urheberrechte Dritter beachtet. Insbesondere werden Inhalte Dritter als solche gekennzeichnet. Sollten Sie trotzdem auf eine Urheberrechtsverletzung aufmerksam werden, wird um einen entsprechenden Hinweis gebeten. Bei Bekanntwerden von Rechtsverletzungen werden wir derartige Inhalte umgehend entfernen.</p>';

	wp_insert_post( array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => 'Impressum',
		'post_name'    => 'impressum',
		'post_content' => $content,
	) );
}

add_action( 'wp_footer', 'bh_footer_legal_bar', 100 );
function bh_footer_legal_bar() {
	$impressum_page = get_page_by_path( 'impressum', OBJECT, 'page' );
	$impressum_url  = $impressum_page ? get_permalink( $impressum_page ) : home_url( '/impressum/' );
	$datenschutz_page = get_page_by_path( 'datenschutz', OBJECT, 'page' );
	$datenschutz_url  = $datenschutz_page ? get_permalink( $datenschutz_page ) : home_url( '/datenschutz/' );
	?>
	<style id="bh-impressum-below-footer-style">
		.bh-impressum-below-footer {
			text-align: center;
			padding: 12px 16px 20px;
			font-size: 14px;
			line-height: 1.4;
		}
		.bh-impressum-below-footer a { text-decoration: none; }
		.bh-impressum-below-footer a:hover,
		.bh-impressum-below-footer a:focus { text-decoration: underline; }
	</style>
	<script>
	(function () {
		var impressumUrl = <?php echo wp_json_encode( esc_url( $impressum_url ) ); ?>;
		var datenschutzUrl = <?php echo wp_json_encode( esc_url( $datenschutz_url ) ); ?>;

		function hideHeaderImpressum() {
			document.querySelectorAll('header a, #main-header a, .et-l--header a').forEach(function (a) {
				var text = (a.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();
				if (text === 'impressum') {
					a.style.display = 'none';
				}
			});
		}

		function hideFooterImpressumLinks() {
			document.querySelectorAll('footer a, .et-l--footer a, #footer-bottom a').forEach(function (a) {
				if (a.closest('.bh-impressum-below-footer')) return; // keep the one below footer
				var text = (a.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();
				if (text === 'impressum') {
					a.style.display = 'none';
				}
			});
		}

		function ensureImpressumBelowFooter() {
			var footerBottom = document.querySelector('#footer-bottom') || document.querySelector('.et-l--footer') || document.querySelector('footer');
			if (!footerBottom) return;

			var existing = document.querySelector('.bh-impressum-below-footer');
			if (!existing) {
				var wrap = document.createElement('div');
				wrap.className = 'bh-impressum-below-footer';
				wrap.innerHTML = '<a href="' + impressumUrl + '">Impressum</a> | <a href="' + datenschutzUrl + '">Datenschutz</a>';
				footerBottom.insertAdjacentElement('afterend', wrap);
			}
		}

		function replaceLegalTextInFooter() {
			// Remove custom injected bar from previous iteration
			document.querySelectorAll('.bh-legal-bar').forEach(function (el) { el.remove(); });

			var roots = document.querySelectorAll('#footer-bottom, .et-l--footer, footer');
			roots.forEach(function (root) {
				root.querySelectorAll('*').forEach(function (node) {
					var text = (node.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();
					if (!text) return;
					if (text === 'impressum | datenschutz' || text === 'impressum|datenschutz') {
						node.innerHTML = '<p>Design &amp; Entwicklung: <a href="https://deinewebseite.de">Alberto Cabrera</a></p>';
					}
				});
			});

			hideHeaderImpressum();
			hideFooterImpressumLinks();
			ensureImpressumBelowFooter();
		}

		replaceLegalTextInFooter();
		var mo = new MutationObserver(replaceLegalTextInFooter);
		mo.observe(document.body, { childList: true, subtree: true });
	})();
	</script>
	<?php
}

// ── Über mich: remove leftover hidden feed blocks behind "Meine letztes Feed" ─
add_action( 'wp_footer', 'bh_remove_hidden_feed_leftovers', 130 );
function bh_remove_hidden_feed_leftovers() {
	?>
	<script>
	(function () {
		function norm(s){ return (s||'').replace(/\s+/g,' ').trim().toLowerCase(); }
		function cleanup() {
			var hasMarker = Array.from(document.querySelectorAll('h1,h2,h3,h4,p,strong,.et_pb_text_inner'))
				.some(function(el){
					var t = norm(el.textContent);
					return t.indexOf('meine letztes feed') !== -1 || t.indexOf('mein letztes feed') !== -1;
				});
			if (!hasMarker) return;

			// Remove known feed containers/artifacts that may remain hidden.
			document.querySelectorAll(
				'#bh-ig-feeds-wrap, #sb_instagram, .sbi, .sbi_item, .instagram-media, iframe[src*=\"instagram.com\"]'
			).forEach(function(el){
				if (el.id === 'bh-ig-carousel-wrap' || el.closest('#bh-ig-carousel-wrap')) return;
				if (el.id === 'sif-wrap' || el.closest('.sif-wrap')) return;
				if (el && el.closest('.et-l--footer, footer, header, .et-l--header')) return;
				if (el && el.parentNode) el.parentNode.removeChild(el);
			});
		}
		cleanup();
		var mo = new MutationObserver(cleanup);
		mo.observe(document.body, { childList:true, subtree:true });
	})();
	</script>
	<?php
}

// ── Über mich: Instagram carousel (latest 4 posts) ──────────────────────────
add_action( 'rest_api_init', function () {
	register_rest_route( 'bh/v1', '/ig-sally-latest', array(
		'methods'             => 'GET',
		'callback'            => 'bh_ig_sally_latest_endpoint',
		'permission_callback' => '__return_true',
	) );
} );

function bh_ig_sally_latest_endpoint() {
	$username = 'sally_bolinger';
	$urls     = array();

	$api_res = wp_remote_get(
		"https://i.instagram.com/api/v1/users/web_profile_info/?username={$username}",
		array(
			'timeout' => 12,
			'headers' => array(
				'User-Agent'      => 'Mozilla/5.0',
				'x-ig-app-id'     => '936619743392459',
				'Accept'          => 'application/json',
			),
		)
	);
	if ( ! is_wp_error( $api_res ) && wp_remote_retrieve_response_code( $api_res ) === 200 ) {
		$data = json_decode( wp_remote_retrieve_body( $api_res ), true );
		$edges = $data['data']['user']['edge_owner_to_timeline_media']['edges'] ?? array();
		foreach ( $edges as $edge ) {
			$shortcode = $edge['node']['shortcode'] ?? '';
			if ( $shortcode ) $urls[] = "https://www.instagram.com/p/{$shortcode}/";
			if ( count( $urls ) >= 4 ) break;
		}
	}

	if ( count( $urls ) < 4 ) {
		$html_res = wp_remote_get( "https://www.instagram.com/{$username}/", array( 'timeout' => 12 ) );
		if ( ! is_wp_error( $html_res ) && wp_remote_retrieve_response_code( $html_res ) === 200 ) {
			$html = wp_remote_retrieve_body( $html_res );
			if ( preg_match_all( '#href="/p/([A-Za-z0-9_-]+)/"#', $html, $m ) ) {
				foreach ( array_values( array_unique( $m[1] ) ) as $sc ) {
					$urls[] = "https://www.instagram.com/p/{$sc}/";
					if ( count( $urls ) >= 4 ) break;
				}
			}
		}
	}

	return rest_ensure_response( array(
		'account' => $username,
		'posts'   => array_slice( array_values( array_unique( $urls ) ), 0, 4 ),
	) );
}

add_action( 'wp_footer', 'bh_ueber_mich_ig_carousel', 150 );
function bh_ueber_mich_ig_carousel() {
	?>
	<style id="bh-ig-carousel-style">
		#bh-ig-carousel-wrap { margin: 26px auto 0; width: 90%; max-width: 1200px; }
		.bh-feed-block-center { width: 90% !important; max-width: 1200px !important; margin-left: auto !important; margin-right: auto !important; }
		#bh-ig-carousel-wrap .bh-ig-row { display:flex; align-items:center; gap:10px; }
		#bh-ig-carousel-wrap .bh-ig-track {
			display:grid;
			grid-auto-flow:column;
			grid-auto-columns:minmax(260px,1fr);
			gap:12px;
			overflow-x:auto;
			width:100%;
			padding:2px;
			scroll-snap-type:x mandatory;
		}
		#bh-ig-carousel-wrap .bh-ig-item { scroll-snap-align:start; background:#fff; border:1px solid #eee; border-radius:8px; overflow:hidden; }
		#bh-ig-carousel-wrap .instagram-media { min-width:0!important; width:100%!important; max-width:100%!important; margin:0!important; }
		#bh-ig-carousel-wrap .bh-ig-nav { width:34px; height:34px; border:0; border-radius:50%; background:#ddd; cursor:pointer; }
		@media (max-width: 1200px) { #bh-ig-carousel-wrap, .bh-feed-block-center { width: 90% !important; max-width: 90% !important; } }
	</style>
	<script>
	(function () {
		function norm(s){ return (s||'').replace(/\s+/g,' ').trim().toLowerCase(); }
		function mountCarousel(){
			var existingWrap = document.getElementById('bh-ig-carousel-wrap');
			if (existingWrap) {
				existingWrap.style.display = 'block';
				if (existingWrap.querySelector('.instagram-media, iframe, .sbi_item')) return;
				existingWrap.remove();
			}
			var readMoreBtn = Array.from(document.querySelectorAll('a,button,.et_pb_button'))
				.find(function(el){ return norm(el.textContent) === 'read more'; });
			var marker = readMoreBtn || Array.from(document.querySelectorAll('h1,h2,h3,h4,p,strong,.et_pb_text_inner'))
				.find(function(el){ return norm(el.textContent).indexOf('meine letztes feeds') !== -1; });
			if (!marker) return;

			fetch('<?php echo esc_url( rest_url( 'bh/v1/ig-sally-latest' ) ); ?>')
				.then(function(r){ return r.json(); })
				.then(function(data){
					if (!data || !Array.isArray(data.posts) || data.posts.length === 0) return;

					var wrap = document.createElement('div');
					wrap.id = 'bh-ig-carousel-wrap';
					wrap.innerHTML = '<div class="bh-ig-row"><button type="button" class="bh-ig-nav bh-ig-prev" aria-label="Zurück">‹</button><div class="bh-ig-track"></div><button type="button" class="bh-ig-nav bh-ig-next" aria-label="Weiter">›</button></div>';
					var track = wrap.querySelector('.bh-ig-track');

					data.posts.slice(0,4).forEach(function(url){
						var item = document.createElement('div');
						item.className = 'bh-ig-item';
						item.innerHTML = '<blockquote class="instagram-media" data-instgrm-permalink="'+url+'" data-instgrm-version="14"></blockquote>';
						track.appendChild(item);
					});

					var row = marker.closest('.et_pb_row');
					if (row) {
						row.classList.add('bh-feed-block-center');
						row.insertAdjacentElement('afterend', wrap);
					} else {
						var anchor =
							(marker.closest('.et_pb_button_module_wrapper') ||
							 marker.closest('.et_pb_module') ||
							 marker);
						anchor.insertAdjacentElement('afterend', wrap);
					}
					wrap.style.display = 'block';

					wrap.querySelector('.bh-ig-prev').addEventListener('click', function(){ track.scrollBy({left:-320, behavior:'smooth'}); });
					wrap.querySelector('.bh-ig-next').addEventListener('click', function(){ track.scrollBy({left: 320, behavior:'smooth'}); });

					if (!document.getElementById('instagram-embed-js')) {
						var s = document.createElement('script');
						s.id = 'instagram-embed-js';
						s.async = true;
						s.src = 'https://www.instagram.com/embed.js';
						s.onload = function(){ if(window.instgrm && window.instgrm.Embeds) window.instgrm.Embeds.process(); };
						document.body.appendChild(s);
					} else if (window.instgrm && window.instgrm.Embeds) {
						window.instgrm.Embeds.process();
					}
				})
				.catch(function(){});
		}

		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', mountCarousel);
		} else {
			mountCarousel();
		}
	})();
	</script>
	<?php
}

// ── Clean hidden object replacement chars shown as [OBJ] in text ────────────
add_action( 'wp_footer', 'bh_remove_obj_marker_from_text', 140 );
function bh_remove_obj_marker_from_text() {
	?>
	<script>
	(function () {
		function cleanObjChars(root) {
			if (!root) return;
			var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, null);
			var nodes = [];
			while (walker.nextNode()) nodes.push(walker.currentNode);
			nodes.forEach(function (n) {
				var t = n.nodeValue || '';
				// U+FFFC object replacement char + visible fallback token [OBJ]
				var cleaned = t.replace(/\uFFFC/g, '').replace(/\[OBJ\]/gi, '');
				if (cleaned !== t) n.nodeValue = cleaned;
			});
		}

		function run() {
			var content = document.querySelector('#main-content, #page-container, body');
			cleanObjChars(content);
		}

		run();
		var mo = new MutationObserver(run);
		mo.observe(document.body, { childList: true, subtree: true, characterData: true });
	})();
	</script>
	<?php
}

// ── Force render of shortcode feed on Über mich (Divi builder-safe) ─────────
// add_action( 'wp_footer', 'bh_force_sally_instagram_shortcode_render', 170 );
function bh_force_sally_instagram_shortcode_render() {
	if ( ! is_page( 'ueber-uns' ) && ! is_page( 987550606 ) ) return;
	if ( ! shortcode_exists( 'sally_instagram_feed' ) ) return;
	$feed_html = do_shortcode( '[sally_instagram_feed]' );
	if ( ! $feed_html ) return;
	?>
	<script>
	(function () {
		function norm(s){ return (s||'').replace(/\s+/g,' ').trim().toLowerCase(); }
		function mount() {
			if (document.querySelector('.sif-wrap')) return;
			var feedHeading = Array.from(document.querySelectorAll('h1,h2,h3,h4,.et_pb_text_inner,p,strong'))
				.find(function(el){
					var t = norm(el.textContent);
					return t.indexOf('meine letztes feeds') !== -1 || t.indexOf('meine letzte feeds') !== -1;
				});
			if (!feedHeading) return;

			// Place carousel below the whole "Meine letzte Feeds" content block.
			var host = feedHeading.closest('.et_pb_row') || feedHeading.closest('.et_pb_module') || feedHeading;
			if (!host) return;

			var wrap = document.createElement('div');
			wrap.innerHTML = <?php echo wp_json_encode( $feed_html ); ?>;
			var inserted = wrap.firstElementChild;
			host.insertAdjacentElement('afterend', inserted);

			// Re-bind slider controls because scripts inside injected HTML won't auto-run.
			try {
				var track = inserted.querySelector('.sif-track');
				var prev  = inserted.querySelector('.sif-prev');
				var next  = inserted.querySelector('.sif-next');
				if (track && prev && next) {
					prev.addEventListener('click', function(){ track.scrollBy({left:-320, behavior:'smooth'}); });
					next.addEventListener('click', function(){ track.scrollBy({left: 320, behavior:'smooth'}); });
				}
			} catch(e) {}

			// Ensure Instagram embed script processes blockquotes.
			if (!document.getElementById('instagram-embed-js')) {
				var s = document.createElement('script');
				s.id = 'instagram-embed-js';
				s.async = true;
				s.src = 'https://www.instagram.com/embed.js';
				s.onload = function(){ if(window.instgrm && window.instgrm.Embeds) window.instgrm.Embeds.process(); };
				document.body.appendChild(s);
			} else if (window.instgrm && window.instgrm.Embeds) {
				window.instgrm.Embeds.process();
			}
		}
		mount();
		var mo = new MutationObserver(mount);
		mo.observe(document.body, { childList:true, subtree:true });
	})();
	</script>
	<?php
}
