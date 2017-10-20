<div id="frm_top_bar">
	<?php

	// Add form nav
	if ( isset( $atts['form'] ) && ( ! isset( $atts['is_template'] ) || ! $atts['is_template'] ) ) {
		FrmAppController::get_form_nav( $atts['form'], true, 'hide' );
	}

	?>

	<div class="frm_top_left">
	<h1><?php echo esc_html( $atts['label'] ); ?>
		<?php if ( isset( $atts['new_link'] ) && ! empty( $atts['new_link'] ) ) { ?>
		<a href="<?php echo esc_url( $atts['new_link'] ) ?>" class="add-new-h2 frm_animate_bg"><?php _e( 'Add New', 'formidable' ); ?></a>
		<?php } elseif ( isset( $atts['link_hook'] ) ) {
			do_action( $atts['link_hook']['hook'], $atts['link_hook']['param'] );
		} ?>
	</h1>
	<?php
	if ( isset( $atts['form'] ) ) {
		FrmFormsHelper::form_switcher();
	}
	?>
	</div>
	<div class="clear"></div>
</div>

<?php if ( isset( $atts['form'] ) && ! empty( $atts['form'] ) && ! isset( $atts['hide_title'] ) ) { ?>
    <h2 id="frm_form_heading">
		<?php echo esc_html( strip_tags( $atts['form']->name == '' ? __( '(no title)' ) : $atts['form']->name ) ) ?>
	</h2>
<?php } ?>
