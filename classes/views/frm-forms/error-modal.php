<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm_error_modal" class="frm-modal frm_common_modal" style="display: block;">
	<div class="metabox-holder">
		<div class="inside">
			<div style="padding: 16px;">
				<div style="font-size: var(--text-xl); font-weight: 400; color: var(--grey-900);"><?php echo esc_html( $error['title'] ); ?></div>
				<?php echo esc_html( $error['body'] ); ?>
				<script src="<?php echo esc_attr( includes_url() ); ?>js/jquery/ui/core.js?ver=1.13.2" id="jquery-ui-core-js"></script>
				<script src="<?php echo esc_attr( includes_url() ); ?>js/jquery/ui/dialog.js?ver=1.13.2" id="jquery-ui-dialog-js"></script>
				<script>
					jQuery( document ).ready(
						function() {
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
