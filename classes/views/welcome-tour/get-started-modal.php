<?php
/**
 * Welcome Tour - Get Started modal.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm-get-started-modal" class="frm_wrap aligncenter frm_hidden">
	<div class="frm_modal_top frm-mt-xs">
		<div class="frm-modal-title">
			<h2><?php esc_html_e( 'Get Started with Formidable Forms', 'formidable' ); ?></h2>
		</div>
	</div>

	<div class="inside frm-px-md frm-mb-0 frm-py-0">
		<p><?php esc_html_e( 'Here\'s a quick checklist to help you set up and explore the key features of the plugin, so you can start building powerful forms in no time.', 'formidable' ); ?></p>
	</div>

	<div class="frm_modal_footer frm-flex-center frm-pt-sm frm-pb-md">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-form-templates&welcome-tour=1' ) ); ?>" class="button button-primary frm-button-primary">
			<?php esc_html_e( 'Begin Tour', 'formidable' ); ?>
		</a>
	</div>
</div>
