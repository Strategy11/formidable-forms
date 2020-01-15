<div id="frm_top_bar" <?php if ( $has_nav ) {
		?> class="frm-has-nav"
	<?php } ?> >
	<div id="frm-publishing">
	<?php
	if ( isset( $atts['publish'] ) ) {
		call_user_func( $atts['publish'][0], $atts['publish'][1] );
	}
	?>
	</div>

	<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable' ) ); ?>" class="frm-header-logo">
		<?php FrmAppHelper::show_header_logo(); ?>
	</a>

	<?php
	// Add form nav
	if ( $has_nav ) {
		FrmAppController::get_form_nav( $atts['form'], true, 'hide' );
	} elseif ( isset( $atts['switcher'] ) ) {
		call_user_func( $atts['switcher'][0], $atts['switcher'][1] );
	} else {
		// Used when no form is currently selected.
		?>

	<div class="frm_top_left">
		<h1>
			<?php echo esc_html( $atts['label'] ); ?>
			<?php FrmAppHelper::add_new_item_link( $atts ); ?>
			<?php if ( isset( $atts['cancel_link'] ) ) { ?>
				<a href="<?php echo esc_url( $atts['cancel_link'] ); ?>" class="button button-secondary frm-button-secondary frm_animate_bg">
					<?php esc_html_e( 'Cancel', 'formidable' ); ?>
				</a>
			<?php } ?>
		</h1>
	</div>
		<?php
	}

	if ( isset( $atts['nav'] ) ) {
		echo FrmAppHelper::kses( $atts['nav'], 'all' ); // WPCS: XSS ok.
	}
	?>
	<div style="clear:right;"></div>
</div>
