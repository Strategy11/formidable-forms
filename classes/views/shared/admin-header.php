<div id="frm_top_bar">
	<?php

	// Add form nav
	if ( $has_nav ) {
		FrmAppController::get_form_nav( $atts['form'], true, 'hide' );
	} else {

	?>

	<div class="frm_top_left">
	<h1><?php echo esc_html( $atts['label'] ); ?>
		<?php FrmAppHelper::add_new_item_link( $atts ); ?>
	</h1>
	</div>
	<?php
	}
	?>

	<div class="clear"></div>
</div>

<?php if ( isset( $atts['form'] ) && ! empty( $atts['form'] ) && ! isset( $atts['hide_title'] ) ) { ?>
    <h<?php echo $has_nav ? 1 : 2 ?> id="frm_form_heading">
		<?php
		echo esc_html( strip_tags( '' === $atts['form']->name ? __( '(no title)' ) : $atts['form']->name ) );
		if ( $has_nav ) {
			FrmAppHelper::add_new_item_link( $atts );
		}
		?>
	</h<?php echo $has_nav ? 1 : 2 ?>>
<?php } ?>
