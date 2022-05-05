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
			<?php do_action( 'frm_applications_header_inside_title_after_span', $context ); ?>
		</h1>
		<?php if ( 'index' === $context && ! FrmAppHelper::pro_is_installed() ) { ?>
			<?php
			FrmAddonsController::show_conditional_action_button(
				array(
					'addon'        => false,
					'upgrade_link' => FrmAppHelper::admin_upgrade_link(
						array(
							'medium'  => 'applications-header',
							'content' => 'applications',
						)
					),
				)
			);
			?>
		<?php } ?>
		<?php do_action( 'frm_applications_header_after_title', $context ); ?>
	</div>
	<div style="clear: both;"></div>
</div>
