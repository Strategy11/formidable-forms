<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm_error_modal" class="frm-dialog frm-modal frm_common_modal frm_hidden">
	<div class="metabox-holder">
		<div class="inside">
			<div>
				<div class="frm_modal_top">
					<a href="<?php echo esc_attr( $error_args['cancel_url'] ); ?>" class="alignright" title="<?php esc_attr_e( 'Dismiss this message', 'formidable' ); ?>">
						<?php FrmAppHelper::icon_by_class( 'frmfont frm_close_icon', array( 'aria-label' => 'Dismiss' ) ); ?>
					</a>
				</div>
				<div class="frm_modal_content">
					<div class="inside">
						<?php if ( ! empty( $error_args['icon'] ) ) : ?>
							<span class="frm_lock_simple"><?php FrmAppHelper::icon_by_class( 'frmfont ' . $error_args['icon'] ); ?></span><br><br>
						<?php endif; ?>
						<div class="frm-modal-title"><h2><?php echo esc_html( $error_args['title'] ); ?></h2></div>
						<p><?php echo esc_html( $error_args['body'] ); ?></p>
					</div>
				</div>
				<div class="frm_modal_footer">
					<?php if ( ! empty( $error_args['cancel_text'] ) ) : ?>
						<a href="<?php echo esc_attr( $error_args['cancel_url'] ); ?>" class="button button-secondary frm-button-secondary dismiss <?php echo esc_attr( $error_args['cancel_classes'] ); ?>"><?php echo esc_html( $error_args['cancel_text'] ); ?></a>
						<?php
						endif;
					if ( ! empty( $error_args['continue_text'] ) ) :
						?>
						<a href="<?php echo esc_attr( $error_args['continue_url'] ); ?>" class="button button-primary dismiss frm-button-primary <?php echo esc_attr( $error_args['continue_classes'] ); ?>"><?php echo esc_html( $error_args['continue_text'] ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="<?php echo esc_attr( includes_url() ); ?>js/jquery/ui/core.js?ver=1.13.2" id="jquery-ui-core-js"></script> <?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>
<script src="<?php echo esc_attr( includes_url() ); ?>js/jquery/ui/button.js?ver=1.13.2" id="jquery-ui-core-js"></script> <?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>
<script src="<?php echo esc_attr( includes_url() ); ?>js/jquery/ui/dialog.js?ver=1.13.2" id="jquery-ui-dialog-js"></script> <?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>
<script>
	jQuery( document ).ready(
		function() {
			document.querySelector( 'body' ).classList.add( 'frm-error-modal' );
			const modal = document.querySelector( '#frm_error_modal' );
			jQuery( modal ).dialog(
				{
					dialogClass: 'frm-dialog',
					modal: true,
					autoOpen: true,
					closeOnEscape: false,
					width: '550px',
					resizable: false,
					draggable: false,
					open: function() {
						jQuery( '.ui-dialog-titlebar' ).addClass( 'frm_hidden' ).removeClass( 'ui-helper-clearfix' );
						document.querySelector( '.frm_modal_top a' )?.blur();
					}
				}
			);
		}
	);
</script>
