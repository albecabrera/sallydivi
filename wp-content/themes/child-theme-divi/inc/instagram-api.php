<?php
/**
 * Instagram Basic Display API integration.
 * Admin: Settings → Instagram Feed
 * Stores long-lived token (60 days) and auto-refreshes via WP Cron.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'BH_IG_API',  'https://graph.instagram.com' );
define( 'BH_IG_AUTH', 'https://api.instagram.com' );

// ── Admin menu ────────────────────────────────────────────────────────────────
add_action( 'admin_menu', function () {
	add_options_page(
		'Instagram Feed',
		'Instagram Feed',
		'manage_options',
		'bh-instagram',
		'bh_ig_admin_page'
	);
} );

// ── OAuth callback + settings save ───────────────────────────────────────────
add_action( 'admin_init', 'bh_ig_handle_admin_actions' );
function bh_ig_handle_admin_actions() {
	if ( ! current_user_can( 'manage_options' ) ) return;
	if ( ( $_GET['page'] ?? '' ) !== 'bh-instagram' ) return;

	// Save settings
	if ( isset( $_POST['bh_ig_save'] ) && check_admin_referer( 'bh_ig_settings' ) ) {
		update_option( 'bh_ig_app_id',     sanitize_text_field( $_POST['bh_ig_app_id']     ?? '' ) );
		update_option( 'bh_ig_app_secret', sanitize_text_field( $_POST['bh_ig_app_secret'] ?? '' ) );

		if ( ! empty( $_POST['bh_ig_manual_token'] ) ) {
			bh_ig_store_long_lived_token( sanitize_text_field( $_POST['bh_ig_manual_token'] ) );
		}

		// Manual post URLs (fallback when API not available)
		$raw_urls = $_POST['bh_ig_manual_urls'] ?? array();
		$clean    = array_slice( array_map( 'esc_url_raw', array_filter( array_map( 'trim', (array) $raw_urls ) ) ), 0, 4 );
		update_option( 'bh_ig_manual_urls', $clean );
		delete_transient( 'bh_ig_latest_posts_v1' );

		wp_redirect( admin_url( 'options-general.php?page=bh-instagram&saved=1' ) );
		exit;
	}

	// Disconnect
	if ( isset( $_GET['disconnect'] ) && check_admin_referer( 'bh_ig_disconnect' ) ) {
		delete_option( 'bh_ig_access_token' );
		delete_option( 'bh_ig_token_expires' );
		delete_option( 'bh_ig_user_id' );
		delete_transient( 'bh_ig_latest_posts_v1' );
		wp_redirect( admin_url( 'options-general.php?page=bh-instagram&disconnected=1' ) );
		exit;
	}

	// OAuth code callback
	if ( isset( $_GET['code'] ) ) {
		$code         = sanitize_text_field( $_GET['code'] );
		$app_id       = get_option( 'bh_ig_app_id', '' );
		$app_secret   = get_option( 'bh_ig_app_secret', '' );
		$redirect_uri = admin_url( 'options-general.php?page=bh-instagram' );

		$res = wp_remote_post( BH_IG_AUTH . '/oauth/access_token', [
			'body' => [
				'client_id'     => $app_id,
				'client_secret' => $app_secret,
				'grant_type'    => 'authorization_code',
				'redirect_uri'  => $redirect_uri,
				'code'          => $code,
			],
			'timeout' => 15,
		] );

		if ( ! is_wp_error( $res ) && wp_remote_retrieve_response_code( $res ) === 200 ) {
			$data = json_decode( wp_remote_retrieve_body( $res ), true );
			if ( ! empty( $data['access_token'] ) ) {
				bh_ig_exchange_for_long_lived( $data['access_token'], (string) ( $data['user_id'] ?? '' ) );
				wp_redirect( admin_url( 'options-general.php?page=bh-instagram&connected=1' ) );
				exit;
			}
		}
		wp_redirect( admin_url( 'options-general.php?page=bh-instagram&error=oauth' ) );
		exit;
	}
}

// ── Token exchange helpers ────────────────────────────────────────────────────
function bh_ig_exchange_for_long_lived( string $short_token, string $user_id = '' ) {
	$res = wp_remote_get( BH_IG_API . '/access_token?' . http_build_query( [
		'grant_type'    => 'ig_exchange_token',
		'client_id'     => get_option( 'bh_ig_app_id', '' ),
		'client_secret' => get_option( 'bh_ig_app_secret', '' ),
		'access_token'  => $short_token,
	] ), [ 'timeout' => 15 ] );

	if ( ! is_wp_error( $res ) && wp_remote_retrieve_response_code( $res ) === 200 ) {
		$data = json_decode( wp_remote_retrieve_body( $res ), true );
		if ( ! empty( $data['access_token'] ) ) {
			update_option( 'bh_ig_access_token', $data['access_token'] );
			update_option( 'bh_ig_token_expires', time() + ( (int) ( $data['expires_in'] ?? 5183944 ) ) );
			if ( $user_id ) update_option( 'bh_ig_user_id', $user_id );
		}
	}
}

function bh_ig_store_long_lived_token( string $token ) {
	update_option( 'bh_ig_access_token', $token );
	update_option( 'bh_ig_token_expires', time() + 5183944 ); // ~60 days

	$res = wp_remote_get( BH_IG_API . '/me?fields=id&access_token=' . $token, [ 'timeout' => 10 ] );
	if ( ! is_wp_error( $res ) && wp_remote_retrieve_response_code( $res ) === 200 ) {
		$data = json_decode( wp_remote_retrieve_body( $res ), true );
		if ( ! empty( $data['id'] ) ) update_option( 'bh_ig_user_id', $data['id'] );
	}
}

// ── WP Cron: auto-refresh token before expiry ─────────────────────────────────
add_action( 'wp', function () {
	if ( ! wp_next_scheduled( 'bh_ig_refresh_token_cron' ) ) {
		wp_schedule_event( time(), 'daily', 'bh_ig_refresh_token_cron' );
	}
} );

add_action( 'bh_ig_refresh_token_cron', 'bh_ig_do_refresh_token' );
function bh_ig_do_refresh_token() {
	$token   = get_option( 'bh_ig_access_token', '' );
	$expires = (int) get_option( 'bh_ig_token_expires', 0 );
	if ( ! $token ) return;
	if ( $expires - time() > 15 * DAY_IN_SECONDS ) return; // Refresh only in last 15 days

	$res = wp_remote_get( BH_IG_API . '/refresh_access_token?' . http_build_query( [
		'grant_type'   => 'ig_refresh_token',
		'access_token' => $token,
	] ), [ 'timeout' => 15 ] );

	if ( ! is_wp_error( $res ) && wp_remote_retrieve_response_code( $res ) === 200 ) {
		$data = json_decode( wp_remote_retrieve_body( $res ), true );
		if ( ! empty( $data['access_token'] ) ) {
			update_option( 'bh_ig_access_token', $data['access_token'] );
			update_option( 'bh_ig_token_expires', time() + ( (int) ( $data['expires_in'] ?? 5183944 ) ) );
		}
	}
}

// ── Fetch latest 4 posts ──────────────────────────────────────────────────────
function bh_ig_get_latest_posts(): array {
	$token = get_option( 'bh_ig_access_token', '' );
	if ( ! $token ) return [];

	$cached = get_transient( 'bh_ig_latest_posts_v1' );
	if ( is_array( $cached ) && ! empty( $cached ) ) return $cached;

	$res = wp_remote_get( BH_IG_API . '/me/media?' . http_build_query( [
		'fields'       => 'id,permalink,timestamp,media_type',
		'limit'        => 8,
		'access_token' => $token,
	] ), [ 'timeout' => 10 ] );

	if ( is_wp_error( $res ) || wp_remote_retrieve_response_code( $res ) !== 200 ) {
		return [];
	}

	$data  = json_decode( wp_remote_retrieve_body( $res ), true );
	$posts = [];
	foreach ( $data['data'] ?? [] as $item ) {
		if ( ! empty( $item['permalink'] ) ) {
			$posts[] = $item['permalink'];
		}
		if ( count( $posts ) >= 4 ) break;
	}

	if ( ! empty( $posts ) ) {
		set_transient( 'bh_ig_latest_posts_v1', $posts, 600 ); // 10 min cache
	}

	return $posts;
}

// ── Admin page UI ─────────────────────────────────────────────────────────────
function bh_ig_admin_page() {
	$app_id       = get_option( 'bh_ig_app_id', '' );
	$app_secret   = get_option( 'bh_ig_app_secret', '' );
	$token        = get_option( 'bh_ig_access_token', '' );
	$expires      = (int) get_option( 'bh_ig_token_expires', 0 );
	$user_id      = get_option( 'bh_ig_user_id', '' );
	$redirect_uri = admin_url( 'options-general.php?page=bh-instagram' );
	$days_left    = $expires ? round( ( $expires - time() ) / DAY_IN_SECONDS ) : 0;

	$auth_url = '';
	if ( $app_id ) {
		$auth_url = BH_IG_AUTH . '/oauth/authorize?' . http_build_query( [
			'client_id'     => $app_id,
			'redirect_uri'  => $redirect_uri,
			'scope'         => 'user_media,user_profile',
			'response_type' => 'code',
		] );
	}
	?>
	<div class="wrap">
		<h1>Instagram Feed — API-Konfiguration</h1>

		<?php if ( isset( $_GET['connected'] ) ) : ?>
			<div class="notice notice-success is-dismissible"><p>✓ Instagram verbunden! Token wurde gespeichert.</p></div>
		<?php elseif ( isset( $_GET['saved'] ) ) : ?>
			<div class="notice notice-success is-dismissible"><p>✓ Einstellungen gespeichert.</p></div>
		<?php elseif ( isset( $_GET['disconnected'] ) ) : ?>
			<div class="notice notice-info is-dismissible"><p>Account getrennt.</p></div>
		<?php elseif ( isset( $_GET['error'] ) ) : ?>
			<div class="notice notice-error"><p>✗ OAuth-Fehler. Prüfe App ID / Secret und Redirect URI.</p></div>
		<?php endif; ?>

		<?php if ( $token ) : ?>
			<div class="notice notice-info">
				<p>
					<strong>Status:</strong> Verbunden
					<?php if ( $user_id ) : ?>(User ID: <code><?php echo esc_html( $user_id ); ?></code>)<?php endif; ?><br>
					Token läuft ab in: <strong><?php echo esc_html( $days_left ); ?> Tage</strong>
					<?php if ( $days_left < 15 ) : ?> — <span style="color:red;font-weight:bold">Bald abgelaufen! Neu verbinden.</span><?php endif; ?>
				</p>
				<p>
					<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'options-general.php?page=bh-instagram&disconnect=1' ), 'bh_ig_disconnect' ) ); ?>"
					   class="button button-secondary" onclick="return confirm('Account wirklich trennen?')">Account trennen</a>
				</p>
			</div>
		<?php else : ?>
			<div class="notice notice-warning"><p>Noch kein Token. Verbinde deinen Instagram-Account unten.</p></div>
		<?php endif; ?>

		<h2>Schritt 1 — Meta App einrichten</h2>
		<ol style="max-width:700px;line-height:2">
			<li>Öffne <a href="https://developers.facebook.com/apps/" target="_blank">developers.facebook.com/apps</a> → <strong>App erstellen</strong></li>
			<li>Typ: <strong>Consumer</strong> (oder "Sonstige → Consumer")</li>
			<li>Füge das Produkt <strong>Instagram Basic Display</strong> hinzu</li>
			<li>Unter <em>Instagram Basic Display → Grundeinstellungen</em> → diese Redirect URI eintragen:<br>
				<code style="background:#f0f0f0;padding:4px 8px;display:inline-block;margin:4px 0"><?php echo esc_html( $redirect_uri ); ?></code>
			</li>
			<li>Füge deinen Instagram-Account unter <strong>Rollen → Instagram-Tester</strong> hinzu und akzeptiere die Einladung in Instagram</li>
			<li>Kopiere <strong>App ID</strong> und <strong>App Secret</strong> hierher und klicke <em>Speichern</em></li>
		</ol>

		<form method="post" action="">
			<?php wp_nonce_field( 'bh_ig_settings' ); ?>
			<table class="form-table" style="max-width:600px">
				<tr>
					<th scope="row"><label for="bh_ig_app_id">App ID</label></th>
					<td><input type="text" id="bh_ig_app_id" name="bh_ig_app_id"
					           value="<?php echo esc_attr( $app_id ); ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th scope="row"><label for="bh_ig_app_secret">App Secret</label></th>
					<td><input type="password" id="bh_ig_app_secret" name="bh_ig_app_secret"
					           value="<?php echo esc_attr( $app_secret ); ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th scope="row"><label for="bh_ig_manual_token">Long-Lived Token<br><small>(optional, manuell)</small></label></th>
					<td>
						<input type="text" id="bh_ig_manual_token" name="bh_ig_manual_token"
						       value="" class="regular-text" placeholder="IGQVJx...">
						<p class="description">Nur wenn du den Token direkt von der Facebook Graph API Explorer kopierst.</p>
					</td>
				</tr>
			</table>

			<h2 style="margin-top:30px">Feed-URLs manuell eintragen <small style="font-weight:normal;font-size:14px">(Sofort-Lösung — kein API nötig)</small></h2>
			<p>Füge hier die URLs deiner 4 letzten Instagram-Posts ein. Das Carousel zeigt diese immer an, solange kein API-Token vorhanden ist.</p>
			<?php $manual_urls = (array) get_option( 'bh_ig_manual_urls', array() ); ?>
			<table class="form-table" style="max-width:600px">
				<?php for ( $i = 0; $i < 4; $i++ ) : $val = $manual_urls[ $i ] ?? ''; ?>
				<tr>
					<th scope="row"><label>Post <?php echo $i + 1; ?></label></th>
					<td><input type="url" name="bh_ig_manual_urls[]"
					           value="<?php echo esc_attr( $val ); ?>"
					           class="regular-text"
					           placeholder="https://www.instagram.com/p/..."></td>
				</tr>
				<?php endfor; ?>
			</table>

			<p><input type="submit" name="bh_ig_save" class="button button-primary" value="Speichern"></p>
		</form>

		<?php if ( $app_id ) : ?>
			<h2>Schritt 2 — Instagram verbinden</h2>
			<a href="<?php echo esc_url( $auth_url ); ?>" class="button button-primary button-hero">
				Instagram-Account verbinden
			</a>
			<p class="description" style="margin-top:8px">
				Du wirst zu Instagram weitergeleitet. Nach der Authorisierung wirst du automatisch zurückgeleitet
				und der Token (60 Tage gültig) wird automatisch gespeichert. WP Cron verlängert ihn vor Ablauf.
			</p>
		<?php endif; ?>

		<?php if ( $token ) : ?>
			<h2>Test</h2>
			<?php
			$posts = bh_ig_get_latest_posts();
			if ( $posts ) :
				echo '<ul>';
				foreach ( $posts as $url ) {
					echo '<li><a href="' . esc_url( $url ) . '" target="_blank">' . esc_html( $url ) . '</a></li>';
				}
				echo '</ul>';
			else :
				echo '<p style="color:red">Keine Posts geladen — prüfe den Token.</p>';
			endif;
			?>
		<?php endif; ?>
	</div>
	<?php
}
