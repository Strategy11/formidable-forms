<div class="frm_wrap">
	<?php
	FrmAppHelper::get_admin_header(
		array(
			'label'       => __( 'Add new form', 'formidable' ),
			'cancel_link' => '?page=formidable',
		)
	);
	?>
	<div class="wrap frm-add-new-form">
		<div class="frm-addons">
			<div class="frm-card">
				<div class="plugin-card-top">
					<div class="frm-round-image">
						<img src="<?php echo esc_attr( FrmAppHelper::plugin_url() ); ?>/images/new-blank.svg" alt="<?php esc_attr_e( 'Create Blank Form', 'formidable' ); ?>" class="frm-blank-form-icon" />
					</div>
					<h2><?php esc_html_e( 'Start with a blank form', 'formidable' ); ?></h2>
					<p><?php esc_html_e( 'Build anything you can imagine.', 'formidable' ); ?></p>
				</div>
				<div class="plugin-card-bottom">
					<a class="button button-primary frm-button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=formidable&frm_action=new' ) ); ?>">
						<?php esc_html_e( 'Create Blank Form', 'formidable' ); ?>
					</a>
				</div>
			</div>

			<div class="frm-card">
				<div class="plugin-card-top">
					<div class="frm-round-image">
						<img src="<?php echo esc_attr( FrmAppHelper::plugin_url() ); ?>/images/new-form.svg" alt="<?php esc_attr_e( 'Choose Template', 'formidable' ); ?>" class="frm-new-template-icon" />
					</div>
					<h2><?php esc_html_e( 'Start with a template', 'formidable' ); ?></h2>
					<p><?php esc_html_e( 'We\'ve done the heavy lifting.', 'formidable' ); ?></p>
				</div>
				<div class="plugin-card-bottom">
					<a class="button button-primary frm-button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=formidable&frm_action=list_templates' ) ); ?>">
						<?php esc_html_e( 'Choose Template', 'formidable' ); ?>
					</a>
				</div>
			</div>

			<div class="frm-card">
				<div class="plugin-card-top">
					<div class="frm-round-image">
						<img src="<?php echo esc_attr( FrmAppHelper::plugin_url() ); ?>/images/new-import.svg" alt="<?php esc_attr_e( 'Import Form', 'formidable' ); ?>" class="frm-import-form-icon" />
					</div>
					<h2><?php esc_html_e( 'Import your form', 'formidable' ); ?></h2>
					<p><?php esc_html_e( 'Import a form from an XML file.', 'formidable' ); ?></p>
				</div>
				<div class="plugin-card-bottom">
					<a class="button button-primary frm-button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-import' ) ); ?>">
						<?php esc_html_e( 'Import Form', 'formidable' ); ?>
					</a>
				</div>
			</div>
		</div>
		<div class="clear"></div>
	</div>
</div>
