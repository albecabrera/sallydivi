<?php
/**
 * Plugin Name: Sally Instagram Latest Feed
 * Description: Muestra siempre los últimos feeds de @sally_bolinger con shortcode [sally_instagram_feed].
 * Version: 1.0.0
 * Author: Alberto Cabrera
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Sally_Instagram_Latest_Feed {
	const USERNAME = 'sally_bolinger';
	const CACHE_KEY = 'sally_ig_latest_feed_v1';
	const CACHE_TTL = 1800; // 30 min

	public static function init() {
		add_shortcode( 'sally_instagram_feed', array( __CLASS__, 'shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function register_routes() {
		register_rest_route( 'sif/v1', '/latest', array(
			'methods'             => 'GET',
			'callback'            => array( __CLASS__, 'latest_endpoint' ),
			'permission_callback' => '__return_true',
		) );
	}

	public static function assets() {
		$css = '
		:root{--sif-primary:#b79ea5}
		.sif-wrap{width:90%;max-width:1200px;margin:24px auto}
		.sif-row{display:flex;align-items:center;gap:10px}
		.sif-track{display:grid;grid-auto-flow:column;grid-auto-columns:minmax(260px,1fr);gap:12px;overflow-x:auto;width:100%;scroll-snap-type:x mandatory}
		.sif-item{position:relative;scroll-snap-align:start;border:1px solid #eee;border-radius:8px;overflow:hidden;background:#fff;padding:8px;transition:transform .25s ease, box-shadow .25s ease;animation:sifFadeUp .55s cubic-bezier(.22,.61,.36,1) both}
		.sif-item:before{content:\"\";position:absolute;inset:0;background:var(--sif-primary);opacity:0;transition:opacity .25s ease;pointer-events:none;z-index:2;mix-blend-mode:multiply}
		.sif-item:hover:before,.sif-item:focus-within:before{opacity:.22}
		.sif-item:hover,.sif-item:focus-within{transform:translateY(-3px);box-shadow:0 10px 24px rgba(0,0,0,.14)}
		.sif-item .instagram-media{min-width:0!important;width:100%!important;max-width:100%!important;margin:0!important}
		.sif-nav{width:34px;height:34px;border:0;border-radius:50%;background:#ddd;cursor:pointer}
		@keyframes sifFadeUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
		';
		wp_register_style( 'sif-style', false );
		wp_enqueue_style( 'sif-style' );
		wp_add_inline_style( 'sif-style', $css );
	}

	public static function fetch_latest_posts() {
		$urls = array();
		$username = self::USERNAME;

		$api = wp_remote_get(
			"https://i.instagram.com/api/v1/users/web_profile_info/?username={$username}",
			array(
				'timeout' => 6,
				'headers' => array(
					'User-Agent' => 'Mozilla/5.0',
					'x-ig-app-id' => '936619743392459',
					'Accept' => 'application/json',
				),
			)
		);
		if ( ! is_wp_error( $api ) && wp_remote_retrieve_response_code( $api ) === 200 ) {
			$data = json_decode( wp_remote_retrieve_body( $api ), true );
			$edges = $data['data']['user']['edge_owner_to_timeline_media']['edges'] ?? array();
			foreach ( $edges as $edge ) {
				$sc = $edge['node']['shortcode'] ?? '';
				if ( $sc ) $urls[] = "https://www.instagram.com/p/{$sc}/";
				if ( count( $urls ) >= 4 ) break;
			}
		}

		if ( count( $urls ) < 4 ) {
			$html_res = wp_remote_get( "https://www.instagram.com/{$username}/", array( 'timeout' => 6 ) );
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

		return array_slice( array_values( array_unique( $urls ) ), 0, 4 );
	}

	public static function get_posts() {
		$cached = get_transient( self::CACHE_KEY );
		if ( is_array( $cached ) && ! empty( $cached ) ) return $cached;

		$urls = self::fetch_latest_posts();
		if ( ! empty( $urls ) ) set_transient( self::CACHE_KEY, $urls, self::CACHE_TTL );
		return $urls;
	}

	public static function latest_endpoint() {
		$fresh = self::fetch_latest_posts(); // refresh on every page load request
		if ( ! empty( $fresh ) ) {
			set_transient( self::CACHE_KEY, $fresh, self::CACHE_TTL );
			return rest_ensure_response( array( 'posts' => $fresh ) );
		}
		$fallback = get_transient( self::CACHE_KEY );
		return rest_ensure_response( array( 'posts' => is_array( $fallback ) ? $fallback : array() ) );
	}

	public static function shortcode( $atts ) {
		$posts = self::get_posts();
		if ( empty( $posts ) ) {
			return '<div class="sif-wrap"><p>No se pudieron cargar los feeds por ahora.</p></div>';
		}

		$id = 'sif-' . wp_generate_uuid4();
		ob_start();
		?>
		<div class="sif-wrap" id="<?php echo esc_attr( $id ); ?>">
			<div class="sif-row">
				<button class="sif-nav sif-prev" type="button" aria-label="Anterior">‹</button>
				<div class="sif-track">
					<?php foreach ( $posts as $i => $url ) : ?>
						<div class="sif-item" style="animation-delay:<?php echo esc_attr( number_format( ( $i + 1 ) * 0.06, 2 ) ); ?>s">
							<blockquote class="instagram-media" data-instgrm-permalink="<?php echo esc_url( $url ); ?>" data-instgrm-version="14"></blockquote>
						</div>
					<?php endforeach; ?>
				</div>
				<button class="sif-nav sif-next" type="button" aria-label="Siguiente">›</button>
			</div>
		</div>
		<script>
		(function(){
			var wrap=document.getElementById('<?php echo esc_js( $id ); ?>'); if(!wrap) return;
			var track=wrap.querySelector('.sif-track');
			function processEmbeds(){
				if(!document.getElementById('instagram-embed-js')){
					var s=document.createElement('script');
					s.id='instagram-embed-js'; s.async=true; s.src='https://www.instagram.com/embed.js';
					s.onload=function(){ if(window.instgrm&&window.instgrm.Embeds) window.instgrm.Embeds.process(); };
					document.body.appendChild(s);
				}else if(window.instgrm&&window.instgrm.Embeds){ window.instgrm.Embeds.process(); }
			}
			wrap.querySelector('.sif-prev').addEventListener('click',function(){track.scrollBy({left:-320,behavior:'smooth'});});
			wrap.querySelector('.sif-next').addEventListener('click',function(){track.scrollBy({left:320,behavior:'smooth'});});
			processEmbeds();

			// Refresh latest feeds on each page load (fast fallback: keep current if fails).
			fetch('<?php echo esc_url( rest_url( 'sif/v1/latest' ) ); ?>?t=' + Date.now())
				.then(function(r){ return r.json(); })
				.then(function(data){
					if(!data || !Array.isArray(data.posts) || !data.posts.length) return;
					track.innerHTML = '';
					data.posts.slice(0,4).forEach(function(url,idx){
						var item=document.createElement('div');
						item.className='sif-item';
						item.style.animationDelay=((idx+1)*0.06).toFixed(2)+'s';
						item.innerHTML='<blockquote class="instagram-media" data-instgrm-permalink=\"'+url+'\" data-instgrm-version=\"14\"></blockquote>';
						track.appendChild(item);
					});
					processEmbeds();
				})
				.catch(function(){});
		})();
		</script>
		<?php
		return ob_get_clean();
	}
}

Sally_Instagram_Latest_Feed::init();
