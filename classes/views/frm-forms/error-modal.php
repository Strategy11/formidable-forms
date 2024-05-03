<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm_error_modal" class="frm-modal frm_common_modal">
	<div class="metabox-holder">
		<div class="postbox">
			<div class="frm_modal_top">
				<?php if ( ! empty( $error_args['icon'] ) ) : ?>
					<div class="frm_lock_simple"><?php FrmAppHelper::icon_by_class( 'frmfont ' . $error_args['icon'] ); ?></div>
				<?php endif; ?>
				<div class="frm-modal-title"><h2><?php echo esc_html( $error_args['title'] ); ?></h2></div>
				<div>
					<a href="<?php echo esc_url( $error_args['cancel_url'] ); ?>" class="dismiss" title="<?php esc_attr_e( 'Dismiss this message', 'formidable' ); ?>">
						<?php FrmAppHelper::icon_by_class( 'frmfont frm_close_icon', array( 'aria-label' => 'Dismiss' ) ); ?>
					</a>
				</div>
			</div>
			<div class="frm_modal_content"><?php echo esc_html( $error_args['body'] ); ?></div>
			<div class="frm_modal_footer">
				<?php if ( ! empty( $error_args['cancel_text'] ) ) : ?>
					<a href="<?php echo esc_url( $error_args['cancel_url'] ); ?>" class="button button-secondary frm-button-secondary dismiss frm-modal-cancel <?php echo esc_attr( $error_args['cancel_classes'] ); ?>"><?php echo esc_html( $error_args['cancel_text'] ); ?></a>
					<?php
					endif;
				if ( ! empty( $error_args['continue_text'] ) ) :
					?>
					<a href="<?php echo esc_url( $error_args['continue_url'] ); ?>" class="button button-primary dismiss frm-button-primary <?php echo esc_attr( $error_args['continue_classes'] ); ?>"><?php echo esc_html( $error_args['continue_text'] ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
<script src="<?php echo esc_url( includes_url() ); ?>js/jquery/ui/core.js?ver=1.13.2" id="jquery-ui-core-js"></script> <?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>
<script src="<?php echo esc_url( includes_url() ); ?>js/jquery/ui/button.js?ver=1.13.2" id="jquery-ui-core-js"></script> <?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>
<script src="<?php echo esc_url( includes_url() ); ?>js/jquery/ui/dialog.js?ver=1.13.2" id="jquery-ui-dialog-js"></script> <?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>
<script>
	jQuery( document ).ready(
		function() {
			const modalElement = document.getElementById( 'frm_error_modal' );
			jQuery( modalElement ).dialog({
				dialogClass: 'frm-dialog',
				modal: true,
				autoOpen: true,
				closeOnEscape: false,
				width: '550px',
				resizable: false,
				draggable: false,
				open: function() {
					jQuery( '.ui-dialog-titlebar' ).addClass( 'frm_hidden' ).removeClass( 'ui-helper-clearfix' );
					modalElement.parentElement.classList.remove( 'ui-widget', 'ui-corner-all', 'ui-widget-content' );
					modalElement.classList.remove( 'ui-widget-content', 'ui-dialog-content' );
					document.querySelector( '.frm_modal_top a' )?.blur();
				}
			});
		}
	);
</script>
