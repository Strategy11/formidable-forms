<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm_top_bar">
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable' ) ); ?>" class="frm-header-logo">
		<?php FrmAppHelper::show_header_logo(); ?>
	</a>
	<div id="frm_bs_dropdown">
		<h1>
			<span>
				<?php echo esc_html( $title ); ?>
			</span>
		</h1>
	</div>
	<div id="frm-publishing">
		<?php do_action( 'frm_applications_header_inside_title_after_span', $context ); ?>
		<?php do_action( 'frm_applications_header_after_title', $context ); ?>
		<?php if ( 'index' === $context && ! FrmAppHelper::pro_is_installed() ) { ?>
			<a href="<?php echo esc_url( FrmAppHelper::admin_upgrade_link( 'header' ) ); ?>" class="button button-secondary frm-button-secondary">
				<?php esc_html_e( 'Upgrade', 'formidable' ); ?>
			</a>
		<?php } ?>
	</div>
</div>
