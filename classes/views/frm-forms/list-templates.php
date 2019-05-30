<div class="frm_wrap" id="frm-templates-page">
	<?php
	FrmAppHelper::get_admin_header(
		array(
			'label'       => __( 'Add New Form', 'formidable' ),
			'cancel_link' => '?page=formidable',
		)
	);
	?>
	<div class="wrap">
		<p class="howto">
			<?php
			printf(
				/* translators: %1$s: Start link HTML, %2$s: End link HTML */
				esc_html__( 'Save time by starting from one of our pre-made templates. They are expertly designed and configured to work right out of the box. If you don\'t find a template you like, you can always start with a %1$sblank form%2$s.', 'formidable' ),
				'<a href="' . esc_url( admin_url( 'admin.php?page=formidable&frm_action=add_new' ) ) . '">',
				'</a>'
			);
			?>
		</p>

		<?php
		FrmAppHelper::show_search_box(
			array(
				'input_id'    => 'template',
				'placeholder' => __( 'Search Templates', 'formidable' ),
			)
		);
		?>
		<div class="clear"></div>

		<div class="frm-addons">
			<div class="frm-card frm-no-thumb">
				<div class="plugin-card-top">
					<h3><?php esc_html_e( 'Blank Form', 'formidable' ); ?></h3>
					<p><?php esc_html_e( 'Start from scratch and build exactly what you want. This option will not pre-load any fields.', 'formidable' ); ?></p>
				</div>
				<div class="plugin-card-bottom">
					<a class="button button-primary frm-button-primary frm-new-form-button" href="#">
						<?php esc_html_e( 'Create Form', 'formidable' ); ?>
					</a>
				</div>
			</div>

			<div class="frm-card frm-no-thumb">
				<div class="plugin-card-top">
					<h3><?php esc_html_e( 'Create a Custom Template', 'formidable' ); ?></h3>
					<ol>
						<li><?php esc_html_e( 'Build a form and setup form actions.', 'formidable' ); ?></li>
						<li><?php esc_html_e( 'Select your form in the dropdown below.', 'formidable' ); ?></li>
						<li><?php esc_html_e( 'Give your template a name and description.', 'formidable' ); ?></li>
						<li><?php esc_html_e( 'Delete the original form. (optional)', 'formidable' ); ?></li>
					</ol>
				</div>
				<div class="plugin-card-bottom frm-new-template">
					<div class="dropdown">
						<a href="#" id="frm-template-drop" class="frm-dropdown-toggle" data-toggle="dropdown">
							<?php esc_html_e( 'Select form for new template', 'formidable' ); ?>
							<b class="caret"></b>
						</a>
						<ul class="frm-dropdown-menu" role="menu" aria-labelledby="frm-template-drop">
							<?php
							if ( empty( $forms ) ) {
								?>
								<li class="frm_dropdown_li">
									<?php esc_html_e( 'You have not created any forms yet.', 'formidable' ); ?>
								</li>
								<?php
							} else {
								foreach ( $forms as $form ) {
									?>
									<li>
										<a href="#" data-formid="<?php echo esc_attr( $form->id ); ?>" class="frm-build-template" data-fullname="<?php echo esc_attr( $form->name ); ?>" tabindex="-1">
											<?php echo esc_html( empty( $form->name ) ? __( '(no title)', 'formidable' ) : FrmAppHelper::truncate( $form->name, 33 ) ); ?>
										</a>
									</li>
									<?php
									unset( $form );
								}
							}
							?>
						</ul>
					</div>
				</div>
			</div>

			<?php
			foreach ( $templates as $k => $template ) {
				if ( ! is_numeric( $k ) ) {
					continue;
				}
				?>
				<div class="frm-card frm-no-thumb" id="frm-template-<?php echo esc_attr( ( isset( $template['installed'] ) && $template['installed'] ? 'custom-' : '' ) . $template['id'] ); ?>">
					<div class="plugin-card-top">
						<?php if ( strtotime( $template['released'] ) > strtotime( '-10 days' ) ) { ?>
							<div class="frm_ribbon">
								<span>New</span>
							</div>
						<?php } ?>
						<h3><?php echo esc_html( preg_replace( '/(\sForm)?(\sTemplate)?$/', '', $template['name'] ) ); ?></h3>
						<p><?php echo esc_html( $template['description'] ); ?></p>
						<?php
						if ( isset( $template['installed'] ) && $template['installed'] ) {
							$preview_link = admin_url( 'admin-ajax.php?action=frm_forms_preview&form=' . $template['key'] );
						} else {
							$preview_link = 'https://sandbox.formidableforms.com/demos/wp-json/frm/v2/forms/' . $template['key'] . '?return=html';
						}

						$plan_required = FrmFormsHelper::get_plan_required( $template );
						FrmFormsHelper::show_plan_required( $plan_required, $pricing . '&utm_content=' . $template['key'] );
						?>
						<?php if ( ! empty( $template['categories'] ) ) { ?>
							<div class="frm_hidden">
								<?php
								esc_html_e( 'Category:', 'formidable' );
								echo esc_html( implode( $template['categories'], ', ' . __( 'Category:', 'formidable' ) ) );
								?>
							</div>
						<?php } ?>
					</div>
					<div class="plugin-card-bottom">
						<a href="#" class="frm-preview-template" rel="<?php echo esc_url( $preview_link ); ?>">
							<?php esc_html_e( 'Preview', 'formidable' ); ?>
						</a>
						<?php if ( isset( $template['installed'] ) && $template['installed'] ) { ?>
							|
							<a href="#" class="frm-trash-template frm-trash" data-frmdelete="trash-template" data-id="<?php echo esc_attr( $template['id'] ); ?>" data-trashtemplate="1" data-frmverify="<?php esc_attr_e( 'Delete this form template?', 'formidable' ); ?>">
								<?php esc_html_e( 'Delete', 'formidable' ); ?>
							</a>
						<?php } ?>

						<?php if ( isset( $template['url'] ) && ! empty( $template['url'] ) ) { ?>
							<?php if ( isset( $template['installed'] ) && $template['installed'] ) { ?>
								<a class="button button-primary frm-button-primary" href="<?php echo esc_attr( $template['url'] ); ?>" aria-label="<?php esc_attr_e( 'Create Form', 'formidable' ); ?>">
							<?php } else { ?>
								<a class="frm-install-template button button-primary frm-button-primary" rel="<?php echo esc_attr( $template['url'] ); ?>" aria-label="<?php esc_attr_e( 'Create Form', 'formidable' ); ?>">
							<?php } ?>
								<?php esc_html_e( 'Create Form', 'formidable' ); ?>
							</a>
						<?php } elseif ( ! empty( $license_type ) && $license_type === strtolower( $plan_required ) ) { ?>
								<a class="install-now button button-secondary frm-button-secondary" href="<?php echo esc_url( FrmAppHelper::admin_upgrade_link( 'addons', 'account/licenses/' ) . '&utm_content=' . $template['slug'] ); ?>" target="_blank" aria-label="<?php esc_attr_e( 'Renew Now', 'formidable' ); ?>">
									<?php esc_html_e( 'Renew Now', 'formidable' ); ?>
								</a>
						<?php } else { ?>
							<a class="install-now button button-secondary frm-button-secondary" href="<?php echo esc_url( $pricing ); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Upgrade Now', 'formidable' ); ?>">
								<?php esc_html_e( 'Upgrade Now', 'formidable' ); ?>
							</a>
						<?php } ?>
					</div>
				</div>
				<?php unset( $template, $templates[ $k ] ); ?>
			<?php } ?>
		</div>
		<?php if ( $expired ) { ?>
			<br/>
			<p class="frm_error_style">
				<?php echo FrmAppHelper::kses( $error, 'a' ); // WPCS: XSS ok. ?>
			</p>
		<?php } ?>
		<?php include( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/template-name-overlay.php' ); ?>
		<div class="clear"></div>
	</div>
</div>
