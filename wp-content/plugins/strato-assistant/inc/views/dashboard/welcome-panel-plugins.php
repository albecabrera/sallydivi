<div id="assistant-welcome-panel" class="dashboard-column dashboard-column4 assistant-dashboard-panel">
	<div class="dashboard-row">
		<?php Strato_Assistant_View::load_template( 'dashboard/branded-wp-column' ); ?>
		<div class="dashboard-column dashboard-column1 assistant-preview-text">
			<div class="inside">
				<h2>
					<?php _ex( 'Configure your WordPress in just a few steps.', 'plugins-panel-second-run', 'strato-assistant' ); ?>
				</h2>
				<p class="about-description">
					<?php _ex( 'Get started and optimize your website with selected themes and plugins.', 'plugins-panel-second-run', 'strato-assistant' ); ?>
				</p>
				<?php if ( empty( $is_assistant_completed ) ): ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . Strato_Assistant_Handler_Dispatch::ASSISTANT_PAGE_ID ) ); ?>"
					   class="button button-primary button-hero assistant-get-started-website">

						<?php esc_html_e( 'dashboard_widget_start', 'strato-assistant' ); ?>
					</a>
					<br>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . Strato_Assistant_Handler_Dispatch::ASSISTANT_PAGE_ID . '&sitetype=eshop' ) ); ?>"
					   class="button button-primary button-hero assistant-get-started-eshop">

						<?php esc_html_e( 'dashboard_widget_shop_start', 'strato-assistant' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>