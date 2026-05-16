<?php
/*
 * This is the child theme for Divi theme, generated with Generate Child Theme plugin by catchthemes.
 *
 * (Please see https://developer.wordpress.org/themes/advanced-topics/child-themes/#how-to-create-a-child-theme)
 */

require_once get_stylesheet_directory() . '/inc/instagram-api.php';
add_action( 'wp_enqueue_scripts', 'child_theme_divi_enqueue_styles' );
function child_theme_divi_enqueue_styles() {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
	wp_enqueue_style( 'child-style',
		get_stylesheet_directory_uri() . '/style.css',
		array('parent-style')
	);
}

add_action( 'wp_enqueue_scripts', 'bh_enqueue_main_script' );
function bh_enqueue_main_script() {
	wp_enqueue_script(
		'bh-main',
		get_stylesheet_directory_uri() . '/bh-main.js',
		array(),
		'1.0.0',
		true
	);

	$impressum_page   = get_page_by_path( 'impressum',   OBJECT, 'page' );
	$datenschutz_page = get_page_by_path( 'datenschutz', OBJECT, 'page' );

	wp_localize_script( 'bh-main', 'bhData', array(
		'impressumUrl'     => $impressum_page   ? get_permalink( $impressum_page )   : home_url( '/impressum/' ),
		'datenschutzUrl'   => $datenschutz_page ? get_permalink( $datenschutz_page ) : home_url( '/datenschutz/' ),
		'carouselEndpoint' => rest_url( 'bh/v1/ig-sally-latest' ),
		'isWechseljahre'   => (int) is_page( 'wechseljahrecoaching' ),
		'googleFontsUrl'   => 'https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap',
		'navItems'         => bh_get_primary_nav_items(),
	) );
}

function bh_get_primary_nav_items() {
	$locations = get_nav_menu_locations();
	$menu_id   = ! empty( $locations['primary-menu'] ) ? $locations['primary-menu'] : 0;

	if ( ! $menu_id ) {
		$menus   = wp_get_nav_menus();
		$menu_id = ! empty( $menus ) ? $menus[0]->term_id : 0;
	}
	if ( ! $menu_id ) return array();

	$items = wp_get_nav_menu_items( $menu_id );
	if ( ! $items ) return array();

	$result = array();
	foreach ( $items as $item ) {
		if ( $item->menu_item_parent != 0 ) continue;
		$result[] = array(
			'label'  => $item->title,
			'url'    => $item->url,
			'pageId' => (int) $item->object_id,
		);
	}
	return $result;
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
    // Google Fonts se cargan condicionalmente desde JS tras el cookie consent (DSGVO).
    // Solo dns-prefetch aquí — preconnect y preload omitidos (harían TCP/TLS con Google sin consentimiento).
    echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">' . "\n";
    echo '<link rel="dns-prefetch" href="//fonts.gstatic.com">' . "\n";
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
                'sameAs'   => [
                    'https://www.instagram.com/sally_bolinger/',
                    'https://www.facebook.com/sally.bolinger',
                ],
            ],
        ],
    ];

    echo '<script type="application/ld+json">'
        . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
        . '</script>' . "\n";
}

// ── Skip link (accesibilidad) ────────────────────────────────────────────────
add_action( 'wp_body_open', function() {
    echo '<a class="bh-skip-link" href="#main-content">Zum Inhalt springen</a>' . "\n";
} );

// ── 404: título en alemán ────────────────────────────────────────────────────
add_filter( 'document_title_parts', function( $parts ) {
	if ( is_404() ) {
		$parts['title'] = 'Seite nicht gefunden';
	}
	return $parts;
} );

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

// ── Über mich: Instagram carousel (latest 4 posts via Graph API) ─────────────
add_action( 'rest_api_init', function () {
	register_rest_route( 'bh/v1', '/ig-sally-latest', array(
		'methods'             => 'GET',
		'callback'            => 'bh_ig_sally_latest_endpoint',
		'permission_callback' => '__return_true',
	) );
} );

function bh_ig_sally_latest_endpoint() {
	// 1. Graph API (si hay token guardado)
	delete_transient( 'bh_ig_latest_posts_v1' );
	$posts = bh_ig_get_latest_posts();
	if ( ! empty( $posts ) ) return rest_ensure_response( array( 'posts' => $posts ) );

	// 2. Scraping de Instagram (funciona en producción, falla en localhost)
	$username = 'sally_bolinger';
	$api_res  = wp_remote_get(
		"https://i.instagram.com/api/v1/users/web_profile_info/?username={$username}",
		array( 'timeout' => 10, 'headers' => array( 'User-Agent' => 'Mozilla/5.0', 'x-ig-app-id' => '936619743392459' ) )
	);
	if ( ! is_wp_error( $api_res ) && wp_remote_retrieve_response_code( $api_res ) === 200 ) {
		$data  = json_decode( wp_remote_retrieve_body( $api_res ), true );
		$edges = $data['data']['user']['edge_owner_to_timeline_media']['edges'] ?? array();
		foreach ( $edges as $edge ) {
			$sc = $edge['node']['shortcode'] ?? '';
			if ( $sc ) $posts[] = "https://www.instagram.com/p/{$sc}/";
			if ( count( $posts ) >= 4 ) break;
		}
	}
	if ( ! empty( $posts ) ) {
		set_transient( 'bh_ig_latest_posts_v1', $posts, 600 );
		return rest_ensure_response( array( 'posts' => $posts ) );
	}

	// 3. URLs manuales (Settings → Instagram Feed)
	$manual = get_option( 'bh_ig_manual_urls', array() );
	$posts  = array_slice( array_filter( array_values( $manual ) ), 0, 4 );
	return rest_ensure_response( array( 'posts' => $posts ) );
}

// ── (Legacy: custom shortcode feed — desactivado) ─────────────────────────────
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
