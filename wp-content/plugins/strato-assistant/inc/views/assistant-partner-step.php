<?php

Strato_Assistant_View::load_template( 'card/header-default' );
foreach ( $usecase_data['plugins'] as $plugin ) {
	echo sprintf( '<input name="plugins[]" value="%s" hidden />', $plugin );
}

wp_nonce_field( 'activate' );
echo sprintf( '<input name="redirect_to" value="%s" hidden />', $usecase_data['redirect_to'] );
echo sprintf( '<input name="usecase" value="%s" hidden />', $usecase_name );

?>

<div class="card-content">
	<div class="card-content-inner">
		<h2><?php echo sprintf(esc_html__( '%s Installation', 'strato-assistant' ), $usecase_data['title']); ?></h2>
		<p><?php echo sprintf(esc_html__( 'We are going to install %s plugin now.', 'strato-assistant' ), $usecase_data['title']); ?></p>
	</div>
</div>

<?php
Strato_Assistant_View::load_template( 'card/footer', array(
	'card_actions' => array(
		'left'  => array(),
		'right' => array(
			'install-hidden-usecase' => array(
				'label' => esc_html__( 'Proceed', 'strato-assistant' ),
				'class' => 'button button-primary'
			),
			'cancel' => array(
				'label' => esc_html__( 'Cancel', 'strato-assistant' ),
				'class' => 'button',
				'href'  => esc_url( ! Strato_Assistant::is_url_query_fragment_in_url_string( wp_get_referer(), 'reauth' ) ? wp_get_referer() ?: admin_url() : admin_url( 'index.php?strato-assistant-cancel=1' ) )
			)
		)
	)
) );
?>
