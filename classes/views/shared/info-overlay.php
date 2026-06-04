<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm_info_modal" class="frm_hidden frm-modal frm-info-modal">
	<div class="metabox-holder">
		<div class="postbox">
			<a href="#" class="dismiss" title="<?php esc_attr_e( 'Dismiss this message', 'formidable' ); ?>">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_close_icon', array( 'aria-label' => 'Dismiss' ) ); ?>
			</a>
			<div class="inside">
				<h3 class="info-modal-title frm_hidden"></h3>
				<div class="info-modal-inside frmcenter">
					<p class="frm-info-msg frm-mb-md">
						<?php esc_html_e( 'Are you sure?', 'formidable' ); ?>
					</p>

					<div class="info-modal-img-wrapper frmcenter frm_hidden frm-mb-md">
						<img src="" alt="" class="info-modal-img" />
					</div>

					<div class="info-modal-buttons">
						<a href="#" id="frm-info-click" class="button button-primary frm-button-primary dismiss">
							<?php esc_html_e( 'Got it!', 'formidable' ); ?>
						</a>
						<a href="#" class="info-modal-action-link button button-primary frm-button-primary frm_hidden"></a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
