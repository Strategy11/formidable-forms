<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm_new_form_modal" class="frm_hidden settings-lite-cta">
	<div class="metabox-holder">
		<div class="postbox">
			<div>
				<div>
					<?php esc_html_e( 'Create new form', 'formidable' ); ?>
				</div>
				<div>
					<a href="#" class="dismiss">
						<?php esc_html_e( 'Cancel', 'formidable' ); ?>
					</a>
				</div>
			</div>
			<div class="inside">
				<div class="cta-inside frmcenter">
					<?php FrmFormsController::list_templates_new(); ?>
				</div>
			</div>
		</div>
	</div>
</div>
