<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm_new_form_modal" class="frm_hidden <?php echo esc_attr( $modal_class ); ?>" frm-page="create">
	<div class="metabox-holder">
		<div class="postbox">
			<div>
				<div>
					<span role="button" class="frm-modal-back" title="<?php esc_html_e( 'Back', 'formidable' ); ?>">
						<svg class="frmsvg">
							<use xlink:href="#frm_back"></use>
						</svg>
					</span>
					<span id="frm-create-title">
						<span frm-type="form"><?php esc_html_e( 'Create new form', 'formidable' ); ?></span>
						<span frm-type="template"><?php esc_html_e( 'Create new template', 'formidable' ); ?></span>
					</span>
					<span id="frm-upgrade-title">
						<?php esc_html_e( 'Upgrade your account', 'formidable' ); ?>
					</span>
					<span id="frm-preview-title"></span>
					<span id="frm-email-title">
						<?php esc_html_e( 'Leave your email address', 'formidable' ); ?>
					</span>
					<span id="frm-renew-title">
						<?php esc_html_e( 'Renew your account', 'formidable' ); ?>
					</span>
				</div>
				<div>
					<a href="#" class="dismiss">
						<?php esc_html_e( 'Cancel', 'formidable' ); ?>
					</a>
				</div>
			</div>
			<div class="inside" id="frm-create-block">
				<div class="cta-inside frmcenter">
					<?php FrmFormsController::list_templates(); ?>
				</div>
			</div>
			<div class="inside" id="frm-preview-block"></div>
			<div class="inside" id="frm-details-block">
				<form name="frm-new-template" id="frm-new-template" method="post" class="field-group">
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
					<input type="hidden" name="type" id="frm_action_type" value="frm_install_template" />

					<button type="submit" class="button-primary frm-button-primary">
						<?php esc_html_e( 'Create', 'formidable' ); ?>
					</button>
				</form>
			</div>
			<?php if ( in_array( 'upgrade', $blocks_to_render, true ) ) { ?>
				<div class="inside" id="frm-upgrade-block">
					<?php require $view_path . 'upgrade-body.php'; ?>
				</div>
			<?php } ?>
			<?php if ( in_array( 'email', $blocks_to_render, true ) ) { ?>
				<div class="inside" id="frm-email-block">
					<?php require $view_path . 'leave-email.php'; ?>
				</div>
			<?php } ?>
			<?php if ( in_array( 'code', $blocks_to_render, true ) ) { ?>
				<div class="inside" id="frm-code-block">
					<?php require $view_path . 'code-from-email.php'; ?>
				</div>
			<?php } ?>
			<?php if ( in_array( 'renew', $blocks_to_render, true ) ) { ?>
				<div class="inside" id="frm-renew-block">
					<?php require $view_path . 'renew-account.php'; ?>
				</div>
			<?php } ?>
			<?php do_action( 'frm_overlay_footer', array( 'type' => 'form' ) ); ?>

			<div id="frm-preview-footer" class="frm_modal_footer">
				<a href="#" class="button button-secondary frm-button-secondary frm-back-to-all-templates">
					<?php esc_html_e( 'Back to all templates', 'formidable' ); ?>
				</a>
				<a href="#" class="button button-primary frm-button-primary frm-use-this-template">
					<?php esc_html_e( 'Use this template', 'formidable' ); ?>
				</a>
			</div>
			<div id="frm-details-footer" class="frm_modal_footer">
				<a href="#" class="button button-secondary frm-modal-cancel frm-back-to-all-templates align-left">
					<?php esc_html_e( 'Cancel', 'formidable' ); ?>
				</a>
				<a href="#" class="button button-primary frm-button-primary frm-submit-new-template">
					<?php esc_html_e( 'Create', 'formidable' ); ?>
				</a>
			</div>
			<?php if ( in_array( 'upgrade', $blocks_to_render, true ) ) { ?>
				<div id="frm-upgrade-footer" class="frm_modal_footer">
					<a href="#" class="button button-secondary frm-modal-cancel frm-back-to-all-templates align-left">
						<?php esc_html_e( 'Cancel', 'formidable' ); ?>
					</a>
					<a href="<?php echo esc_url( $upgrade_link ); ?>" class="button button-primary frm-button-primary" target="_blank" rel="noopener">
						<?php esc_html_e( 'Upgrade Now', 'formidable' ); ?>
					</a>
				</div>
			<?php } ?>
			<?php if ( in_array( 'email', $blocks_to_render, true ) ) { ?>
				<div id="frm-email-footer" class="frm_modal_footer">
					<a href="#" class="button button-secondary frm-button-secondary frm-modal-cancel align-left">
						<?php esc_html_e( 'Cancel', 'formidable' ); ?>
					</a>
					<a id="frm-add-my-email-address" href="#" class="button button-primary frm-button-primary">
						<?php esc_html_e( 'Get Code', 'formidable' ); ?>
					</a>
				</div>
			<?php } ?>
			<?php if ( in_array( 'code', $blocks_to_render, true ) ) { ?>
				<div id="frm-code-footer" class="frm_modal_footer">
					<a href="#" class="button button-secondary frm-button-secondary frm-modal-cancel align-left">
						<?php esc_html_e( 'Cancel', 'formidable' ); ?>
					</a>
					<a href="#" class="button button-primary frm-button-primary frm-confirm-email-address">
						<?php esc_html_e( 'Save Code', 'formidable' ); ?>
					</a>
				</div>
			<?php } ?>
			<?php if ( in_array( 'renew', $blocks_to_render, true ) ) { ?>
				<div id="frm-renew-footer" class="frm_modal_footer">
					<a href="#" class="button button-secondary frm-button-secondary frm-modal-cancel align-left">
						<?php esc_html_e( 'Cancel', 'formidable' ); ?>
					</a>
					<a href="<?php echo esc_url( $renew_link ); ?>" class="button button-primary frm-button-primary" target="_blank" rel="noopener">
						<?php esc_html_e( 'Renew my account', 'formidable' ); ?>
					</a>
				</div>
			<?php } ?>
		</div>
	</div>
</div>
<div class="frm_hidden">
	<?php
	FrmAppHelper::icon_by_class( 'frmfont frm_eye_simple' );
	FrmAppHelper::icon_by_class( 'frmfont frm_plus_icon' );
	FrmAppHelper::icon_by_class( 'frmfont frm_back' );
	FrmAppHelper::icon_by_class( 'frmfont frm_lock_simple' );
	FrmAppHelper::icon_by_class( 'frmfont frm_unlock_simple' );
	?>
	<div id="frm-hover-icons-template" class="frm-hover-icons">
		<a role="button" href="#" class="frm-delete-form" aria-label="<?php esc_attr_e( 'Delete form', 'formidable' ); ?>" title="<?php esc_attr_e( 'Delete form', 'formidable' ); ?>">
			<svg class="frmsvg">
				<use xlink:href="#frm_delete_solid_icon"></use>
			</svg>
		</a><a role="button" href="#" class="frm-preview-form" aria-label="<?php esc_attr_e( 'Preview form', 'formidable' ); ?>" title="<?php esc_attr_e( 'Preview form', 'formidable' ); ?>">
			<svg class="frmsvg">
				<use xlink:href="#frm_eye_simple"></use>
			</svg>
		</a><a role="button" href="#" class="frm-create-form" aria-label="<?php esc_attr_e( 'Create form', 'formidable' ); ?>" title="<?php esc_attr_e( 'Create form', 'formidable' ); ?>">
			<svg class="frmsvg">
				<use xlink:href="#frm_plus_icon"></use>
			</svg>
		</a><a role="button" href="#" class="frm-unlock-form" aria-label="<?php esc_html_e( 'Unlock form', 'formidable' ); ?>" title="<?php esc_html_e( 'Unlock form', 'formidable' ); ?>">
			<svg class="frmsvg">
				<use xlink:href="#frm_unlock_simple"></use>
			</svg>
		</a>
	</div>
</div>
