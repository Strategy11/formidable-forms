<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

FrmAppHelper::print_admin_banner( ! $has_nav && empty( $atts['switcher'] ) );
?>
<div id="frm_top_bar" class="<?php echo esc_attr( 'frm_nav_bar' ); ?>">
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
			<?php
			echo esc_html( $atts['label'] );
			FrmAppHelper::add_new_item_link( $atts );

			if ( isset( $atts['cancel_link'] ) ) {
				?>
				<a href="<?php echo esc_url( $atts['cancel_link'] ); ?>" class="button frm-button-secondary frm_animate_bg">
					<?php esc_html_e( 'Cancel', 'formidable' ); ?>
				</a>
				<?php
			}
			?>
		</h1>
	</div>
	<ul class="frm_form_nav"></ul>
		<?php
	}

	if ( isset( $atts['nav'] ) ) {
		echo FrmAppHelper::kses( $atts['nav'], 'all' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	echo '<div id="frm-publishing">';
	if ( ! empty( $atts['publish'] ) || ! empty( $atts['import_link'] ) ) {
		$btn_type = 'primary';
		if ( isset( $atts['publish'] ) && is_array( $atts['publish'] ) ) {
			call_user_func( $atts['publish'][0], $atts['publish'][1] );
			$btn_type = 'secondary';
		}

		if ( $atts['import_link'] ) {
			// Use primary style if it's the only button.
			FrmAppHelper::import_link( $btn_type );
		}
	} elseif ( ! FrmAppHelper::pro_is_installed() ) {
		?>
			<a href="<?php echo esc_url( FrmAppHelper::admin_upgrade_link( 'header' ) ); ?>" class="button button-secondary frm-button-secondary">
				<?php esc_html_e( 'Upgrade', 'formidable' ); ?>
			</a>
		<?php
	}
	echo '</div>';

	if ( FrmAppHelper::is_full_screen() ) {
		?>
		<div class="frm-full-close">
			<a href="<?php echo esc_attr( $atts['close'] ); ?>" aria-label="<?php esc_attr_e( 'Close', 'formidable' ); ?>">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_close_icon', array( 'aria-label' => 'Dismiss' ) ); ?>
			</a>
		</div>
	<?php } ?>
</div>
