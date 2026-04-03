<?php Strato_Assistant_View::load_template( 'card/header-default' ); ?>

<div class="card-content warning">
	<div class="card-content-inner">
		<h2><?php _e( 'We are sorry, but you cannot go further.', 'strato-assistant' ); ?></h2>
		<p>
			<?php
				echo sprintf(
					__(
						'It seems you are PHP 8. Unfortunately, the Assistant does not yet support PHP 8. ' .
						'To use it we recommend you to <a href="%s">switch to a lower version</a> (PHP 7.4) an wait for the update that we will provide soon. ' .
						'Thank you for your understanding!',
						'strato-assistant'
					),
					Strato\Assistant\Config::get( 'links.help_php_' . Strato\Assistant\Options::get_market() ) ?? Strato\Assistant\Config::get( 'links.help_php_US' )
				);
			?>
		</p>
	</div>
</div>

<?php
	Strato_Assistant_View::load_template( 'card/footer', array(
		'card_actions' => array(
			'left'  => array(),
			'right' => array(
				'back-to-wp' => array(
					'label' => __( 'Take me back', 'strato-assistant' ),
					'class' => 'button button-primary',
					'href'  => admin_url()
				)
			)
		)
	) );
?>