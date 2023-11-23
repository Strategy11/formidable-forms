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
			<div class="inside frm-pt-xl frm-pb-md">
				<div class="cta-inside frm-flex-col frm-items-center frm-gap-xl frmcenter">
					<p class="frm-confirm-msg frm-m-0">
						<?php esc_html_e( 'Are you sure?', 'formidable' ); ?>
					</p>

					<div class="frm-flex-box frm-gap-xs-2xs frm-justify-center">
						<a href="#" class="button button-secondary frm-button-secondary dismiss">
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
</div>
