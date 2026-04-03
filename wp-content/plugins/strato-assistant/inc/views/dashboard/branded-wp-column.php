<?php
	$logo_src = Strato_Assistant_Branding::get_logo();
	$logo_alt = sprintf( __( 'by %s' ), Strato_Assistant_Branding::get_brand_name() );
	$visual = Strato_Assistant_Branding::get_visual( 1 );
?>
<div class="dashboard-column dashboard-column1 branded-wordpress-column">
    <div class="inside">
        <div class="branded-wordpress-img">
            <img src="<?php echo $visual; ?>" alt="WordPress" />
        </div>
        <?php if ( $logo_src ): ?>
            <img src="<?php echo $logo_src; ?>" alt="<?php echo $logo_alt; ?>" class="logo" />
		<?php endif; ?>
    </div>
</div>