<div id="frm_top_bar">
	<?php
	if ( isset( $atts['close'] ) && ! empty( $atts['close'] ) ) {
		?>
		<div class="frm-full-close">
			<a href="<?php echo esc_attr( $atts['close'] ); ?>" aria-label="<?php esc_attr_e( 'Close', 'formidable' ); ?>">
				<span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
			</a>
		</div>
		<?php
	}

	// Add form nav
	if ( $has_nav ) {
		FrmAppController::get_form_nav( $atts['form'], true, 'hide' );
	} else {

		?>

	<div class="frm_top_left">
	<h1><?php echo esc_html( $atts['label'] ); ?>
		<?php FrmAppHelper::add_new_item_link( $atts ); ?>
		<?php if ( isset( $atts['cancel_link'] ) ) { ?>
			<a href="<?php echo esc_url( $atts['cancel_link'] ); ?>" class="button button-secondary frm-button-secondary frm_animate_bg"><?php esc_html_e( 'Cancel', 'formidable' ); ?></a>
		<?php } ?>
	</h1>
	</div>
		<?php
	}
	?>

	<div class="clear"></div>
</div>

<?php if ( isset( $atts['form'] ) && ! empty( $atts['form'] ) && ! isset( $atts['hide_title'] ) ) { ?>
    <h<?php echo $has_nav ? 1 : 2; ?> id="frm_form_heading">
		<?php
		echo esc_html( strip_tags( '' === $atts['form']->name ? __( '(no title)', 'formidable' ) : $atts['form']->name ) );
		if ( $has_nav ) {
			FrmAppHelper::add_new_item_link( $atts );
		}
		?>
	</h<?php echo $has_nav ? 1 : 2; ?>>
<?php } ?>
