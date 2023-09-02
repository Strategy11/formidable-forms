<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>

<div id="frm-form-templates-page" class="frm_wrap">
	<div class="frm_page_container">
		<!-- Page Header -->
		<?php
		FrmAppHelper::get_admin_header(
			array(
				'label' => __( 'Form Templates', 'formidable' ),
			)
		);
		?>

		<!-- Page Body -->
		<div class="columns-2">
			<!-- Page Sidebar -->
			<div class="frm-right-panel">
				<div class="frm-scrollbar-wrapper">
					<?php
					// Search box.
					FrmAppHelper::show_search_box(
						array(
							'input_id'    => 'template',
							'placeholder' => __( 'Search Templates', 'formidable' ),
							'tosearch'    => 'frm-searchable-template',
						)
					);

					// Categories list.
					require $view_path . 'categories.php';
					?>
				</div>
			</div><!-- .frm-right-panel -->

			<!-- Page Content -->
			<div id="post-body-content" class="frm_hidden">
				<!-- Create a blank form -->
				<span id="frm-form-templates-create-form">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm_plus_icon', array( 'aria-label' => _x( 'Create', 'form templates: create a blank form', 'formidable' ) ) ); ?>
					<span><?php esc_html_e( 'Create a blank form', 'formidable' ); ?></span>
				</span><!-- #frm-form-templates-new-form -->
				<span id="frm-form-templates-create-form-divider" class="frm-form-templates-divider"></span>

				<?php
				// Templates list.
				require $view_path . 'templates.php';
				?>
			</div><!-- #post-body-content -->
		</div><!-- .columns-2 -->

		<!--
		<div id="frm-details-block">
			<form name="frm-new-template" id="frm-new-template" method="post" class="field-group">
				<p>
					<label for="frm_template_name" id="frm_new_name" data-template="<?php esc_attr_e( 'Template Name', 'formidable' ); ?>" data-form="<?php esc_html_e( 'Form Name', 'formidable' ); ?>">
						<?php esc_html_e( 'Form Name', 'formidable' ); ?>
					</label>
					<input type="text" name="template_name" id="frm_template_name" class="frm_long_input" />
				</p>

				<input type="hidden" name="template_desc" id="frm_template_desc" />
				<input type="hidden" name="link" id="frm_link" value="" />
				<input type="hidden" name="type" id="frm_action_type" value="frm_install_template" />

				<button type="submit" class="button-primary frm-button-primary">
					<?php esc_html_e( 'Create', 'formidable' ); ?>
				</button>
			</form>
		</div>

		<?php if ( in_array( 'upgrade', $blocks_to_render, true ) ) { ?>
			<div id="frm-upgrade-block">
				<?php require $view_path . '_upgrade-body.php'; ?>
			</div>
		<?php } ?>

		<?php if ( in_array( 'email', $blocks_to_render, true ) ) { ?>
			<div id="frm-email-block">
				<?php require $view_path . '_leave-email.php'; ?>
			</div>
		<?php } ?>

		<?php if ( in_array( 'code', $blocks_to_render, true ) ) { ?>
			<div id="frm-code-block">
				<?php require $view_path . '_code-from-email.php'; ?>
			</div>
		<?php } ?>

		<?php if ( in_array( 'renew', $blocks_to_render, true ) ) { ?>
			<div id="frm-renew-block">
				<?php require $view_path . '_renew-account.php'; ?>
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
		<?php } ?> -->
	</div><!-- .frm_page_container -->
</div><!-- #frm-from-templates-page.frm_wrap -->

<div class="frm_hidden">
	<?php
	FrmAppHelper::icon_by_class( 'frmfont frm_eye_simple' );
	FrmAppHelper::icon_by_class( 'frmfont frm_plus_icon' );
	FrmAppHelper::icon_by_class( 'frmfont frm_back' );
	FrmAppHelper::icon_by_class( 'frmfont frm_lock_icon' );
	FrmAppHelper::icon_by_class( 'frmfont frm_unlock_simple' );
	?>
	<div id="frm-hover-icons-template" class="frm-hover-icons">
		<a role="button" href="#" class="frm-delete-form" aria-label="<?php esc_attr_e( 'Delete form', 'formidable' ); ?>" title="<?php esc_attr_e( 'Delete form', 'formidable' ); ?>">
			<svg class="frmsvg">
				<use xlink:href="#frm_delete_icon"></use>
			</svg>
		</a>
		<a role="button" href="#" class="frm-preview-form" aria-label="<?php esc_attr_e( 'Preview form', 'formidable' ); ?>" title="<?php esc_attr_e( 'Preview form', 'formidable' ); ?>">
			<svg class="frmsvg">
				<use xlink:href="#frm_eye_simple"></use>
			</svg>
		</a>
		<a role="button" href="#" class="frm-create-form" aria-label="<?php esc_attr_e( 'Create form', 'formidable' ); ?>" title="<?php esc_attr_e( 'Create form', 'formidable' ); ?>">
			<svg class="frmsvg">
				<use xlink:href="#frm_plus_icon"></use>
			</svg>
		</a>
		<a role="button" href="#" class="frm-unlock-form" aria-label="<?php esc_html_e( 'Unlock form', 'formidable' ); ?>" title="<?php esc_html_e( 'Unlock form', 'formidable' ); ?>">
			<svg class="frmsvg">
				<use xlink:href="#frm_unlock_simple"></use>
			</svg>
		</a>
	</div>
</div>
