<div id="assistant-welcome-panel" class="dashboard-column dashboard-column4 assistant-dashboard-panel">
	<div class="dashboard-row">
		<?php Strato_Assistant_View::load_template( 'dashboard/branded-wp-column' ); ?>
		<div class="dashboard-column dashboard-column1 assistant-preview-text">
			<div class="inside">
				<h2>
					<?php _e( 'Welcome to WordPress!', 'strato-assistant' ); ?>
				</h2>
				<p class="about-description">
					<?php _e( 'setup_assistant_type_title', 'strato-assistant' ); ?>
				</p>
				<p class="introduction">
					<?php _e( 'setup_assistant_type_description', 'strato-assistant' ); ?>
				</p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . Strato_Assistant_Handler_Dispatch::ASSISTANT_PAGE_ID ) ); ?>"
				   class="button button-primary button-hero assistant-get-started-website">

					<?php esc_html_e( 'dashboard_widget_start', 'strato-assistant' ); ?>
				</a>
				<br>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . Strato_Assistant_Handler_Dispatch::ASSISTANT_PAGE_ID . '&setup_type=eshop' ) ); ?>"
				   class="button button-primary button-hero assistant-get-started-eshop">

					<?php esc_html_e( 'dashboard_widget_shop_start', 'strato-assistant' ); ?>
				</a>
			</div>
		</div>
	</div>
</div>