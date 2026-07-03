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
	// filemtime as version: page cache can serve old HTML briefly, but a CSS
	// edit always changes the URL, so browsers never keep a stale stylesheet.
	wp_enqueue_style( 'child-style',
		get_stylesheet_directory_uri() . '/style.css',
		array('parent-style'),
		filemtime( get_stylesheet_directory() . '/style.css' )
	);
}

// Inject overrides in footer — after ALL Divi CSS regardless of customizer order.
add_action( 'wp_footer', 'bh_button_overrides', 1 );
function bh_button_overrides() {
	?>
	<style id="bh-button-overrides">
	/* Kill Divi arrow icon on BOTH pseudo-elements (::before left, ::after right) */
	.et_pb_button::before,
	.et_pb_button::after,
	a.et_pb_button::before,
	a.et_pb_button::after,
	.et_pb_button:hover::before,
	.et_pb_button:hover::after,
	a.et_pb_button:hover::before,
	a.et_pb_button:hover::after,
	.et_pb_module .et_pb_button:hover::before,
	.et_pb_module .et_pb_button:hover::after {
		content: none !important;
		opacity: 0 !important;
		display: none !important;
		width: 0 !important;
	}
	.et_pb_button:hover,
	.et_pb_module .et_pb_button:hover {
		padding: .3em 1em !important;
	}

	/* Uniform vertical rhythm: drop stray 60px top margins so every content
	   section is separated only by its 80px padding (40px on mobile). */
	body.page-id-987550602 .et_pb_section_5,
	body.page-id-987550606 .et_pb_section_2,
	body.page-id-987550609 .et_pb_section_1 {
		margin-top: 0 !important;
	}

	/* Über mich: Divi builder injects margin-top:52px on first content section —
	   creates gap between fixed header and hero. Reset to 0 like other pages. */
	body.page-id-987550606 .et_pb_section_0.et_pb_section {
		margin-top: 0 !important;
	}

	/* Footer nav: Divi hides .et_pb_menu__menu on mobile via customizer CSS.
	   Force it visible in the footer so links are always accessible. */
	@media only screen and (max-width: 980px) {
		.et_pb_section_0_tb_footer .et_pb_menu__menu {
			display: block !important;
		}
		.et_pb_section_0_tb_footer .mobile_menu_bar,
		.et_pb_section_0_tb_footer .et_mobile_menu_icon {
			display: none !important;
		}
		.et_pb_section_0_tb_footer .et_pb_menu__menu ul.et-menu {
			display: flex !important;
			flex-direction: row !important;
			flex-wrap: wrap !important;
			justify-content: center !important;
			gap: 4px 16px !important;
		}
		.et_pb_section_0_tb_footer .et_pb_menu__menu ul.et-menu li a {
			display: block !important;
			padding: 4px 0 !important;
		}
	}

	/* Über mich mobile fixes */
	@media only screen and (max-width: 980px) {
		/* Hero fullwidth header: correct negative margins so it spans viewport
		   without overflowing (Divi generates wrong values for this layout). */
		body.page-id-987550606 .et_pb_fullwidth_header_0 {
			margin-left: calc(50% - 50vw) !important;
			margin-right: calc(50% - 50vw) !important;
			width: 100vw !important;
			max-width: 100vw !important;
		}
		/* Card columns 1/3 don't stack on mobile — row is flex nowrap so
		   width alone doesn't work; switch direction to column. */
		body.page-id-987550606 .et_pb_section_2 .et_pb_row_2 {
			flex-direction: column !important;
		}
		body.page-id-987550606 .et_pb_section_2 .et_pb_row_2 .et_pb_column_1_3 {
			flex: 0 0 100% !important;
			width: 100% !important;
			max-width: 100% !important;
			margin-bottom: 20px !important;
		}
		body.page-id-987550606 .et_pb_section_2 .et_pb_row_2 .et_pb_column_1_3:last-child {
			margin-bottom: 0 !important;
		}
	}
	</style>
	<?php
}

add_action( 'wp_enqueue_scripts', 'bh_enqueue_main_script' );
function bh_enqueue_main_script() {
	wp_enqueue_script(
		'bh-main',
		get_stylesheet_directory_uri() . '/bh-main.js',
		array(),
		filemtime( get_stylesheet_directory() . '/bh-main.js' ),
		true
	);

	$impressum_page   = get_page_by_path( 'impressum',   OBJECT, 'page' );
	$datenschutz_page = get_page_by_path( 'datenschutz', OBJECT, 'page' );
	$contact_page     = get_page_by_path( 'contact-us',  OBJECT, 'page' );

	wp_localize_script( 'bh-main', 'bhData', array(
		'homeUrl'          => home_url( '/' ),
		'kontaktUrl'       => $contact_page ? get_permalink( $contact_page ) : home_url( '/contact-us/' ),
		'impressumUrl'     => $impressum_page   ? get_permalink( $impressum_page )   : home_url( '/impressum/' ),
		'datenschutzUrl'   => $datenschutz_page ? get_permalink( $datenschutz_page ) : home_url( '/datenschutz/' ),
		'carouselEndpoint' => rest_url( 'bh/v1/ig-sally-latest' ),
		'igProfile'        => 'https://www.instagram.com/sally_bolinger/',
		'isWechseljahre'   => (int) is_page( 'wechseljahrecoaching' ),
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

// 2. Roboto is self-hosted (child theme /fonts, @font-face in style.css).
//    Divi would still enqueue Roboto/Open Sans from fonts.gstatic.com on its
//    own (inline @font-face styles) — remove those so no request ever leaves
//    for Google (GDPR + speed). Divi 5 ignores et_use_google_fonts, so the
//    handles are dequeued directly.
add_filter( 'et_use_google_fonts', '__return_false' );
add_action( 'wp_enqueue_scripts', function () {
    foreach ( [ 'et-builder-googlefonts-cached', 'et-builder-googlefonts', 'et-divi-open-sans', 'divi-fonts' ] as $handle ) {
        wp_dequeue_style( $handle );
        wp_deregister_style( $handle );
    }
}, 99 );
// Drop the now-pointless preconnect to fonts.gstatic.com.
add_filter( 'wp_resource_hints', function ( $urls, $relation_type ) {
    if ( 'preconnect' === $relation_type || 'dns-prefetch' === $relation_type ) {
        $urls = array_filter( $urls, function ( $url ) {
            $href = is_array( $url ) ? ( $url['href'] ?? '' ) : $url;
            return strpos( $href, 'fonts.gstatic.com' ) === false && strpos( $href, 'fonts.googleapis.com' ) === false;
        } );
    }
    return $urls;
}, 10, 2 );

//    Preload the latin file so text renders with the right font on first paint.
add_action( 'wp_head', 'bh_preload_fonts', 1 );
function bh_preload_fonts() {
    echo '<link rel="preload" href="' . esc_url( get_stylesheet_directory_uri() . '/fonts/roboto-latin.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
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

// Remove ?ver= query strings from CSS/JS (improves proxy/CDN caching).
// Exempt child theme assets — they use filemtime() for cache busting.
function bh_remove_ver_strings( $src ) {
    if ( strpos( $src, get_stylesheet_directory_uri() ) !== false ) {
        return $src;
    }
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

// Divi 5 renders module <img> tags without alt even when the attachment has one
// in the media library. Enrich the final HTML: alt from _wp_attachment_image_alt
// (a11y/SEO) and a core-standard sizes attribute when missing (better srcset pick).
add_filter( 'the_content', 'bh_enrich_content_images', 25 );
add_filter( 'et_builder_render_layout', 'bh_enrich_content_images', 25 );
function bh_enrich_content_images( $content ) {
    if ( is_admin() || isset( $_GET['et_fb'] ) || strpos( $content, '<img' ) === false ) {
        return $content;
    }
    return preg_replace_callback( '/<img\b[^>]*>/', function ( $m ) {
        $img = $m[0];
        if ( ! preg_match( '/\bwp-image-(\d+)/', $img, $idm ) ) {
            return $img;
        }
        $id = (int) $idm[1];
        if ( ! preg_match( '/\balt=/', $img ) ) {
            $alt = trim( (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) );
            if ( $alt !== '' ) {
                $img = str_replace( '<img ', '<img alt="' . esc_attr( $alt ) . '" ', $img );
            }
        }
        if ( strpos( $img, ' sizes=' ) === false
            && strpos( $img, ' srcset=' ) !== false
            && preg_match( '/\bwidth="(\d+)"/', $img, $wm ) ) {
            $w   = (int) $wm[1];
            $img = str_replace( ' srcset=', ' sizes="(max-width: ' . $w . 'px) 100vw, ' . $w . 'px" srcset=', $img );
        }
        return $img;
    }, $content );
}

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

// Block user enumeration via REST API
add_filter( 'rest_endpoints', function( $endpoints ) {
    if ( ! current_user_can( 'list_users' ) ) {
        unset( $endpoints['/wp/v2/users'] );
        unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
    }
    return $endpoints;
} );

// ── Gmail SMTP ───────────────────────────────────────────────────────────────
// Set BH_SMTP_PASSWORD in wp-config.php with a Gmail App Password.
// Generate one at: myaccount.google.com → Security → App passwords
// Priority 9999 overrides Post SMTP (priority 999).
add_action( 'phpmailer_init', function ( $mail ) {
    if ( ! defined( 'BH_SMTP_PASSWORD' ) || BH_SMTP_PASSWORD === '' ) return;
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Port       = 587;
    $mail->SMTPSecure = 'tls';
    $mail->Username   = 'albecabrera442@gmail.com';
    $mail->Password   = BH_SMTP_PASSWORD;
    $mail->From       = 'albecabrera442@gmail.com';
    $mail->FromName   = get_bloginfo( 'name' );
}, 9999 );

add_filter( 'wp_mail_from',      function() { return 'albecabrera442@gmail.com'; } );
add_filter( 'wp_mail_from_name', function() { return get_bloginfo( 'name' ); } );

// ── SEO: Resource hints ───────────────────────────────────────────────────────

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
	// 1. Graph API (si hay token guardado) — usa el transient interno de bh_ig_get_latest_posts()
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
