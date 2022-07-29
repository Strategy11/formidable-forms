<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm_confirm_modal" class="frm_hidden frm-modal frm-info-modal">
	<div class="metabox-holder">
		<div class="postbox">
			<a href="#" class="dismiss" title="<?php esc_attr_e( 'Dismiss this message', 'formidable' ); ?>">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_close_icon', array( 'aria-label' => 'Dismiss' ) ); ?>
			</a>
			<div class="inside">
				<div class="cta-inside frmcenter">
					<p class="frm-confirm-msg">
						<?php esc_html_e( 'Are you sure?', 'formidable' ); ?>
					</p>
					<br/>
					<a href="#" class="button button-secondary frm-button-secondary dismiss" style="margin:0 20px;">
						<?php esc_html_e( 'Cancel', 'formidable' ); ?>
					</a>
					<a href="#" id="frm-confirmed-click" class="button button-primary frm-button-primary dismiss">
						<?php esc_html_e( 'Confirm', 'formidable' ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>
