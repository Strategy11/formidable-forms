<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm_new_form_modal" class="frm_hidden" frm-active-modal-page="create">
	<div class="metabox-holder">
		<div class="postbox">
			<div>
				<div>
					<span class="frm-modal-back" title="<?php esc_html_e( 'Back', 'formidable' ); ?>">
						<svg class="frmsvg">
							<use xlink:href="#frm_back"></use>
						</svg>
					</span>
					<span id="frm-create-title">
						<?php esc_html_e( 'Create new form', 'formidable' ); ?>
					</span>
					<span id="frm-upgrade-title">
						<?php esc_html_e( 'Upgrade your account', 'formidable' ); ?>
					</span>
					<span id="frm-preview-title"></span>
					<span id="frm-details-title">
						<?php esc_html_e( 'Create new form', 'formidable' ); ?>
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
					<?php FrmFormsController::list_templates_new(); ?>
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

					<a href="#" class="dismiss">
						<?php esc_attr_e( 'Cancel', 'formidable' ); ?>
					</a>
				</form>
			</div>
			<div class="inside" id="frm-upgrade-block">
				<?php require dirname( __FILE__ ) . '/upgrade-body.php'; ?>
			</div>
			<div id="frm-preview-footer">
				<a href="#" class="button button-secondary frm-button-secondary frm-back-to-all-templates">
					<?php esc_html_e( 'Back to all templates', 'formidable' ); ?>
				</a>
				<a href="#" class="button button-primary frm-button-primary frm-use-this-template">
					<?php esc_html_e( 'Use this template', 'formidable' ); ?>
				</a>
			</div>
			<div id="frm-upgrade-footer">
				<a href="#" class="button button-secondary frm-button-secondary frm-back-to-all-templates align-left">
					<?php esc_html_e( 'Cancel', 'formidable' ); ?>
				</a>
				<a
					href="<?php
					echo esc_url(
						FrmAppHelper::admin_upgrade_link(
							array(
								'medium'  => 'upgrade',
								'content' => 'button',
							)
						)
					);
					?>"
					class="button button-primary frm-button-primary"
				>
					<?php esc_html_e( 'Continue to upgrade', 'formidable' ); ?>
				</a>
			</div>
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
		<a href="#" class="frm-preview-form" title="<?php esc_html_e( 'Preview form', 'formidable' ); ?>">
			<svg class="frmsvg">
				<use xlink:href="#frm_eye_simple"></use>
			</svg>
		</a><a href="#" class="frm-create-form" title="<?php esc_html_e( 'Create form', 'formidable' ); ?>">
			<svg class="frmsvg">
				<use xlink:href="#frm_plus_icon"></use>
			</svg>
		</a><a href="#" class="frm-unlock-form" title="<?php esc_html_e( 'Unlock form', 'formidable' ); ?>">
			<svg class="frmsvg">
				<use xlink:href="#frm_unlock_simple"></use>
			</svg>
		</a>
	</div>
</div>
