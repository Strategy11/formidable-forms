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

		<ul class="frm-featured-forms" style="margin-top:30px">
			<li class="frm-add-blank-form">
				<a class="frm-new-form-button frm-featured-form" href="#">
					<span class="frm-inner-circle">
						<?php FrmAppHelper::icon_by_class( 'frmfont frm_plus_icon' ); ?>
					</span>
					<h3><?php esc_html_e( 'Blank Form', 'formidable' ); ?></h3>
				</a>
			</li>
			<?php
			foreach ( array( 20872734, 20874748, 20882522, 20874739 ) as $template ) {
				if ( ! isset( $templates[ $template ] ) ) {
					continue;
				}

				$template      = $templates[ $template ];
				$plan_required = FrmFormsHelper::get_plan_required( $template );
				$link          = FrmFormsHelper::get_template_install_link( $template, compact( 'pricing', 'license_type', 'plan_required' ) );
				?>
				<li>
					<?php FrmFormsHelper::template_install_html( $link, 'frm-featured-form' ); ?>
						<?php FrmFormsHelper::template_icon( isset( $template['categories'] ) ? $template['categories'] : array() ); ?>
						<h3><?php echo esc_html( preg_replace( '/(\sForm)?(\sTemplate)?$/', '', $template['name'] ) ); ?></h3>
					</a>
					<a href="#" class="frm-preview-template" rel="https://sandbox.formidableforms.com/demos/wp-json/frm/v2/forms/<?php echo esc_attr( $template['key'] ); ?>?return=html">
						<span class="frm-inner-circle">
							<?php FrmAppHelper::icon_by_class( 'frmfont frm_search_icon' ); ?>
						</span>
					</a>
				</li>
			<?php } ?>
		</ul>

		<?php
		FrmAppHelper::show_search_box(
			array(
				'input_id'    => 'template',
				'placeholder' => __( 'Search Templates', 'formidable' ),
				'tosearch'    => 'frm-template-row',
			)
		);
		?>

		<ul class="frm-nav-tabs">
			<li class="frm-tabs">
				<a href="#frm-premium-templates">
					<?php esc_html_e( 'Premium Templates', 'formidable' ); ?>
				</a>
			</li>
			<li class="hide-if-no-js">
				<a href="#frm-custom-templates">
					<?php esc_html_e( 'My Templates', 'formidable' ); ?>
				</a>
			</li>
		</ul>
		<div class="clear"></div>

		<div id="frm-premium-templates" class="hide_with_tabs">
		<table class="wp-list-table widefat fixed striped frm-list-templates">
			<tbody>
			<?php
			foreach ( $templates as $k => $template ) {
				if ( ! is_numeric( $k ) ) {
					continue;
				}

				$plan_required = FrmFormsHelper::get_plan_required( $template );
				$link = FrmFormsHelper::get_template_install_link( $template, compact( 'pricing', 'license_type', 'plan_required' ) );
				?>
				<tr class="frm-template-row <?php echo esc_attr( $link['class'] === 'install-now' ? $link['class'] : '' ); ?>" id="frm-template-<?php echo esc_attr( $template['id'] ); ?>">
					<td>
						<?php if ( strtotime( $template['released'] ) > strtotime( '-10 days' ) ) { ?>
							<div class="frm_ribbon">
								<span>New</span>
							</div>
						<?php } ?>
						<?php FrmFormsHelper::template_icon( isset( $template['categories'] ) ? $template['categories'] : array() ); ?>
						<h3><?php echo esc_html( preg_replace( '/(\sForm)?(\sTemplate)?$/', '', $template['name'] ) ); ?></h3>
						<p><?php echo esc_html( $template['description'] ); ?></p>
						<?php

						echo '<p class="frm_plan_required">';
						if ( ! empty( $plan_required ) ) {
							printf(
								/* translators: %s: Link with label */
								esc_html__( 'This template requires an active %s license or above.', 'formidable' ),
								'<a href="' . esc_url( $pricing . '&utm_content=' . $template['key'] ) . '" target="_blank" rel="noopener">' .
								esc_html( $plan_required ) .
								'</a>'
							);
						} else {
							// Show the description on hover too.
							echo esc_html( $template['description'] );
						}
						?>
						</p>

						<?php if ( ! empty( $template['categories'] ) ) { ?>
							<div class="frm_hidden">
								<?php
								esc_html_e( 'Category:', 'formidable' );
								echo esc_html( implode( $template['categories'], ', ' ) );
								?>
							</div>
						<?php } ?>

					<div class="frm-template-actions">
						<?php FrmFormsHelper::template_install_html( $link, 'button button-primary frm-button-primary' ); ?>
							<?php echo esc_html( $link['label'] ); ?>
						</a>
						&nbsp;
						<a href="#" class="frm-preview-template button frm-button-secondary" rel="<?php echo esc_url( 'https://sandbox.formidableforms.com/demos/wp-json/frm/v2/forms/' . $template['key'] . '?return=html' ); ?>">
							<?php esc_html_e( 'Preview', 'formidable' ); ?>
						</a>
					</div>
				</td>
				</tr>
				<?php unset( $template, $templates[ $k ] ); ?>
			<?php } ?>
		</tbody>
</table>
		<?php if ( $expired ) { ?>
			<br/>
			<p class="frm_error_style">
				<?php echo FrmAppHelper::kses( $error, 'a' ); // WPCS: XSS ok. ?>
			</p>
		<?php } ?>
		</div>

		<div id="frm-custom-templates" class="hide_with_tabs frm_hidden">

			<h3><?php esc_html_e( 'Create a template from an existing form', 'formidable' ); ?></h3>
			<div class="dropdown frm-fields">
				<button type="button" class="frm-dropdown-toggle dropdown-toggle btn btn-default" id="frm-template-drop" data-toggle="dropdown" style="width:auto">
					<?php esc_html_e( 'Select form for new template', 'formidable' ); ?>
					<b class="caret"></b>
				</button>
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

			<h3 class="frm-nav-tabs" style="padding-bottom:10px;margin-top:30px">
				<?php esc_html_e( 'My Templates', 'formidable' ); ?>
			</h3>
			<table class="wp-list-table widefat fixed striped frm-list-templates">
				<tbody>
				<?php
				if ( empty( $custom_templates ) ) {
					?>
					<tr class="frm-template-row">
						<td>
							<span class="frm-inner-circle">
								<?php FrmAppHelper::icon_by_class( 'frmfont frm_tooltip_icon' ); ?>
							</span>
							<h3><?php esc_html_e( 'You do not have any custom templates yet.', 'formidable' ); ?></h3>
							<p style="display:block">
								<a href="<?php
									echo esc_url(
										FrmAppHelper::admin_upgrade_link(
											array(
												'anchor'  => 'kb-how-to-create-a-template-from-a-form',
												'medium'  => 'form-templates',
												'content' => 'create-template',
											),
											'knowledgebase/create-a-form/'
										)
									); // phpcs:ignore Generic.WhiteSpace.ScopeIndent
											?>"
									target="_blank" rel="noopener">
									<?php esc_html_e( 'Learn how to create custom form templates.', 'formidable' ); ?>
								</a>
							</p>
						</td>
					</tr>
					<?php
				}

				foreach ( $custom_templates as $k => $template ) {
					$link = FrmFormsHelper::get_template_install_link( $template, compact( 'pricing', 'license_type', 'plan_required' ) );
					?>
					<tr class="frm-template-row" id="frm-template-custom-<?php echo esc_attr( $template['id'] ); ?>">
						<td>
							<?php FrmFormsHelper::template_icon( array() ); ?>
							<h3><?php echo esc_html( $template['name'] ); ?></h3>
							<p style="display:block"><?php echo esc_html( $template['description'] ); ?></p>

							<div class="frm-template-actions">
								<a href="#" class="frm-trash-template frm-trash" data-frmdelete="trash-template" data-id="<?php echo esc_attr( $template['id'] ); ?>" data-trashtemplate="1" data-frmverify="<?php esc_attr_e( 'Delete this form template?', 'formidable' ); ?>">
									<?php esc_html_e( 'Delete', 'formidable' ); ?>
								</a>
								&nbsp;
								<a class="button button-primary frm-button-primary" href="<?php echo esc_attr( $template['url'] ); ?>" aria-label="<?php esc_attr_e( 'Create Form', 'formidable' ); ?>">
									<?php esc_html_e( 'Create Form', 'formidable' ); ?>
								</a>
								&nbsp;
								<a href="#" class="frm-preview-template button frm-button-secondary" rel="<?php echo esc_url( admin_url( 'admin-ajax.php?action=frm_forms_preview&form=' . $template['key'] ) ); ?>">
									<?php esc_html_e( 'Preview', 'formidable' ); ?>
								</a>
							</div>
						</td>
					</tr>
					<?php unset( $template, $templates[ $k ] ); ?>
				<?php } ?>
				</tbody>
			</table>
		</div>

		<?php include( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/template-name-overlay.php' ); ?>
		<div class="clear"></div>
	</div>
</div>
