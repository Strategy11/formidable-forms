<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm_<?php echo esc_attr( $type ); ?>_modal" class="frm_hidden settings-lite-cta">
	<div class="metabox-holder">
		<div class="postbox">
			<div class="inside">
				<div class="cta-inside">

					<form name="frm-new-<?php echo esc_attr( $type ); ?>" id="frm-new-<?php echo esc_attr( $type ); ?>" method="post" class="field-group">
						<p>
							<label for="frm_template_name" id="frm_new_name" data-template="<?php esc_attr_e( 'Template Name', 'formidable' ); ?>" data-form="<?php esc_html_e( 'Form Name', 'formidable' ); ?>">
								<?php esc_html_e( 'Form Name', 'formidable' ); ?>
							</label><br/>
							<input type="text" name="template_name" id="frm_template_name" class="frm_long_input" />
						</p>
						
						<p>
							<label for="frm_template_desc" id="frm_new_desc" data-template="<?php esc_attr_e( 'Template Description', 'formidable' ); ?>" data-form="<?php esc_html_e( 'Form Description', 'formidable' ); ?>">
								<?php esc_html_e( 'Form Description', 'formidable' ); ?>
							</label>
							<span class="frm-sub-label"><?php esc_html_e( '(optional)', 'formidable' ); ?></span>
							<br/>
							<textarea name="template_desc" id="frm_template_desc" class="frm_long_input"></textarea>
						</p>
						<input type="hidden" name="link" id="frm_link" value="" />
						<input type="hidden" name="type" id="frm_action_type" value="frm_install_<?php echo esc_attr( $type ); ?>" />

						<button type="submit" class="button-primary frm-button-primary">
							<?php esc_html_e( 'Create', 'formidable' ); ?>
						</button>

						<a href="#" class="dismiss">
							<?php esc_attr_e( 'Cancel', 'formidable' ); ?>
						</a>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
