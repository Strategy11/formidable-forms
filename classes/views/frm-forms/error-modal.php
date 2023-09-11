<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$defaults = array(
	'cancel_text' => __( 'Cancel', 'formidable' ),
	'cancel_url'  => '',
	'cancel_classes' => '',
	'continue_text' => __( 'Continue', 'formidable' ),
	'continue_url' => '',
	'continue_classes' => '',
);

$error = wp_parse_args( $error, $defaults );
?>
<div id="frm_error_modal" class="frm-dialog frm-modal frm_common_modal">
	<div class="metabox-holder">
		<div class="inside">
			<div>
				<div class="frm_modal_top">
					<a href="<?php echo esc_attr( $error['cancel_url'] ); ?>" class="alignright" title="<?php esc_attr_e( 'Dismiss this message', 'formidable' ); ?>">
						<?php FrmAppHelper::icon_by_class( 'frmfont frm_close_icon', array( 'aria-label' => 'Dismiss' ) ); ?>
					</a>
					<p><?php FrmAppHelper::icon_by_class( 'frmfont frm_lock_simple' ); ?></p>
					<div class="frm-modal-title"><?php echo esc_html( $error['title'] ); ?></div>
				</div>
				<div class="frm_modal_content">
					<div class="inside">
						<?php echo esc_html( $error['body'] ); ?>
					</div>
				<div class="frm_modal_footer">
					<a href="<?php echo esc_attr( $error['cancel_url'] ); ?>" class="button button-secondary frm-button-secondary dismiss <?php echo esc_attr( $error['cancel_classes'] ); ?>"><?php esc_html_e( $error['cancel_text'], 'formidable' ); ?></a>
					<a href="<?php echo esc_attr( $error['continue_url'] ); ?>" class="button button-primary dismiss frm-button-primary <?php echo esc_attr( $error['continue_classes'] ); ?>"><?php esc_html_e( $error['continue_text'], 'formidable' ); ?></a>
				</div>
				<script src="<?php echo esc_attr( includes_url() ); ?>js/jquery/ui/core.js?ver=1.13.2" id="jquery-ui-core-js"></script>
				<script src="<?php echo esc_attr( includes_url() ); ?>js/jquery/ui/dialog.js?ver=1.13.2" id="jquery-ui-dialog-js"></script>
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
									}
								}
							);
						}
					);
				</script>
			</div>
		</div>
	</div>
</div>
