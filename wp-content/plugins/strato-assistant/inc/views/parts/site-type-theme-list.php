<?php if ( ! empty( $site_type ) && ! empty( $themes ) && is_array( $themes ) ): ?>

	<?php foreach ( $themes as $theme ): ?>
		<?php
		if ( empty( $theme['id'] ) ) {
			continue;
		}
		?>

		<a class="theme" href="#" id="goto-preview" data-site-type="<?php echo $site_type; ?>" data-theme="<?php echo $theme[ 'id' ]; ?>">

            <span class="theme-thumbnail">
                <?php if ( ! empty( $theme['active'] ) ): ?>
	                <span class="theme-active"><?php echo __( 'Active theme' ); ?></span>
                <?php endif; ?>

                <img src="<?php echo esc_url( $theme['screenshot_url'] ); ?>" alt="<?php esc_html_e( $theme['name'] ); ?>">

	            <div class="theme-caption">
	                <span class="theme-name"><?php esc_html_e( $theme['name'] ); ?></span>
	            </div>
            </span>
		</a>

	<?php endforeach; ?>

<?php endif; ?>