<?php

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

?>
/*
Theme Name: <?php echo esc_html($theme_name), "\n"; ?>
Template: <?php echo esc_html($parent_theme_template), "\n"; ?>

This is the child theme for <?php echo esc_html($parent_theme_name); ?> theme, generated with Generate Child Theme plugin by catchthemes.

Right to Left text support.
*/
@import url("../<?php echo esc_html($rtl_theme); ?>/rtl.css");