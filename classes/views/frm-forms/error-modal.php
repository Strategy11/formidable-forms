<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$defaults = array(
	'title'            => '',
	'body'             => '',
	'cancel_url'       => '',
	'cancel_classes'   => '',
	'continue_url'     => '',
	'continue_classes' => '',
	'icon'             => 'frm_lock_simple'
);

$error_args = wp_parse_args( $error_args, $defaults );
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
						<?php
						if ( ! empty( $error_args['icon'] ) ) { ?>
							<span class="frm_lock_simple"><?php FrmAppHelper::icon_by_class( 'frmfont ' . $error_args['icon'] ); ?></span><br><br>
						<?php }
						?>
						<div class="frm-modal-title"><h2><?php echo esc_html( $error_args['title'] ); ?></h2></div>
						<p><?php echo esc_html( $error_args['body'] ); ?></p>
					</div>
				</div>
				<div class="frm_modal_footer">
					<?php
					if ( ! empty( $error_args['cancel_text'] ) ) { ?>
						<a href="<?php echo esc_attr( $error_args['cancel_url'] ); ?>" class="button button-secondary frm-button-secondary dismiss <?php echo esc_attr( $error_args['cancel_classes'] ); ?>"><?php esc_html_e( $error_args['cancel_text'], 'formidable' ); ?></a>
					<?php }
					if ( ! empty( $error_args['continue_text'] ) ) { ?>
						<a href="<?php echo esc_attr( $error_args['continue_url'] ); ?>" class="button button-primary dismiss frm-button-primary <?php echo esc_attr( $error_args['continue_classes'] ); ?>"><?php esc_html_e( $error_args['continue_text'], 'formidable' ); ?></a>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
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
			const dismiss = modal.querySelector( '.dismiss' );
		}
	);
</script>
