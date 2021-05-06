<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( current_user_can( 'administrator' ) && ! FrmAppHelper::pro_is_installed() && ! $has_nav && empty( $atts['switcher'] ) ) {
	?>
	<div class="frm-upgrade-bar">
		<span>You're using Formidable Forms Lite. To unlock more features consider</span>
		<a href="<?php echo esc_url( FrmAppHelper::admin_upgrade_link( 'top-bar' ) ); ?>" target="_blank" rel="noopener">upgrading to Pro</a>.
	</div>
	<?php
}
?>
<div id="frm_top_bar">
	<?php if ( FrmAppHelper::is_full_screen() ) { ?>
		<div class="frm-full-close">
			<a href="<?php echo esc_attr( $atts['close'] ); ?>" aria-label="<?php esc_attr_e( 'Close', 'formidable' ); ?>">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_close_icon', array( 'aria-label' => 'Dismiss' ) ); ?>
			</a>
		</div>
	<?php } ?>
	<?php
	if ( isset( $atts['publish'] ) ) {
		echo '<div id="frm-publishing">';
		call_user_func( $atts['publish'][0], $atts['publish'][1] );
		echo '</div>';
	} elseif ( ! FrmAppHelper::pro_is_installed() ) {
		?>
		<div id="frm-publishing">
			<a href="<?php echo esc_url( FrmAppHelper::admin_upgrade_link( 'header' ) ); ?>" class="button button-secondary frm-button-secondary">
				<?php esc_html_e( 'Upgrade', 'formidable' ); ?>
			</a>
		</div>
		<?php
	}
	?>

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
	<div class="frm_top_left <?php echo esc_attr( $atts['import_link'] ? 'frm_top_wide' : '' ); ?>">
		<h1>
			<?php echo esc_html( $atts['label'] ); ?>
			<?php FrmAppHelper::add_new_item_link( $atts ); ?>
			<?php if ( $atts['import_link'] ) { ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-import' ) ); ?>" class="button button-secondary frm-button-secondary frm_animate_bg">
					<?php esc_html_e( 'Import', 'formidable' ); ?>
				</a>
			<?php } ?>
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
