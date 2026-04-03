<?php if ( ! empty( $site_type ) && ! empty( $plugins ) && ! empty( $theme ) && is_array( $theme ) && ! empty( $redirect_url ) ): ?>

	<div class="theme-preview">
		<div class="theme-screenshot">
			<img src="<?php echo esc_url( $theme['screenshot_url'] ); ?>" alt="<?php esc_html_e( $theme['name'] ); ?>">
		</div>

		<div class="theme-info">
			<div class="card-content">
				<form action="" method="post" class="assistant-install-form-preview">
					<?php wp_nonce_field( 'activate' ) ?>

					<input type="hidden" id="install-site-type" name="site_type" value="<?php echo $site_type; ?>" />
					<input type="hidden" id="install-theme" name="theme" value="<?php echo $theme['id']; ?>" />
					<input type="hidden" id="redirect_url" name="redirect_url" value="<?php echo $redirect_url; ?>" />

					<?php if (is_array( $plugins )): ?>
						<?php foreach ( $plugins as $key => $plugin_slug ): ?>
							<input type="hidden" id="install-plugin-<?php echo $key ?>" name="plugins[]" value="<?php echo $plugin_slug; ?>" />
						<?php endforeach; ?>
					<?php endif; ?>

					<h2><?php echo esc_html( $theme['name'] ); ?></h2>

					<?php if ( ! empty( $theme['active'] ) ): ?>
						<p class="theme-active"><?php echo __( 'Active theme' ); ?></p>
					<?php endif; ?>

					<p><strong>
						<?php echo sprintf(
							__( 'From <a href="%s" target="_blank" rel="external nofollow">WordPress.org</a>:', 'strato-assistant' ),
							'https://wordpress.org/themes/' . $theme['slug'] . '/'
						) ?>
					</strong></p>

					<p>
						<?php echo esc_html(
							array_key_exists( 'short_description', $theme ) ? $theme['short_description'] : $theme['description']
						) ?>
					</p>
				</form>
			</div>

			<?php
				Strato_Assistant_View::load_template( 'card/footer', array(
					'card_actions' => array(
						'left'  => array(),
						'right' => array(
							'install' => array(
								'label' => esc_html__( 'Choose this theme', 'strato-assistant' ),
								'class' => 'button button-primary theme-btn'
							),
							'goto-design' => array(
								'label' => esc_html__( 'Back' ),
								'class' => 'button',
								'data'  => array(
									'site-type' => $site_type
								)
							)
						)
					)
				) );
			?>
		</div>
	</div>
<?php endif; ?>

