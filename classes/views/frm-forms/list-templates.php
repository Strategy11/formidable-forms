<div class="frm_wrap" id="frm-templates-page">
	<?php
	FrmAppHelper::get_admin_header(
		array(
			'label'       => __( 'Add new form', 'formidable' ),
			'cancel_link' => '?page=formidable&frm_action=add_new',
		)
	);
	?>
	<div class="wrap">
		<h2 class="frm-h2"><?php esc_html_e( 'Form templates', 'formidable' ); ?></h2>
		<p class="howto">
			<?php
			/* translators: %1$s: Start link HTML, %2$s: End link HTML */
			printf(
				esc_html__( 'Save time by starting from one of our pre-made templates. They are expertly designed and configured to work right out of the box. If you don\'t find a template you like, you can always start with a %1$sblank form%2$s.', 'formidable' ),
				'<a href="' . esc_url( admin_url( 'admin.php?page=formidable&frm_action=new' ) ) . '">',
				'</a>'
			);
			?>
		</p>

		<?php FrmAppHelper::show_search_box( '', 'template', __( 'Search Templates', 'formidable' ) ); ?>
		<div class="clear"></div>

		<div class="frm-addons">
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
						<a href="#" id="frm-templateDrop" class="frm-dropdown-toggle" data-toggle="dropdown">
							<?php esc_html_e( 'Select form for new template', 'formidable' ); ?>
							<b class="caret"></b>
						</a>
						<ul class="frm-dropdown-menu" role="menu" aria-labelledby="frm-templateDrop">
							<?php
							if ( empty( $forms ) ) {
								?>
								<li class="frm_dropdown_li">
									<?php esc_html_e( 'You have not created any forms yet.', 'formidable' ); ?>
								</li>
								<?php
							} else {
								foreach ( $forms as $form ) {
									$args['id'] = $form->id;
									?>
									<li>
										<a href="<?php echo esc_url( add_query_arg( $args, $base ) ); ?>" tabindex="-1">
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

			<div class="frm-card frm-no-thumb">
				<div class="plugin-card-top">
					<h3><?php esc_html_e( 'Blank Form', 'formidable' ); ?></h3>
					<p><?php esc_html_e( 'Start from scratch and build exactly what you want. This option will not pre-load any fields.', 'formidable' ); ?></p>
				</div>
				<div class="plugin-card-bottom">
					<a class="button button-primary frm-button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=formidable&frm_action=new' ) ); ?>">
						<?php esc_html_e( 'Create Form', 'formidable' ); ?>
					</a>
				</div>
			</div>

			<?php foreach ( $templates as $template ) { ?>
				<div class="frm-card frm-no-thumb">
					<div class="plugin-card-top">
						<?php if ( strtotime( $template['released'] ) > strtotime( '-10 days' ) ) { ?>
							<div class="frm_ribbon">
								<span>New</span>
							</div>
						<?php } ?>
						<h3><?php echo esc_html( $template['name'] ); ?></h3>
						<p><?php echo esc_html( $template['description'] ); ?></p>
					</div>
					<div class="plugin-card-bottom">
						<?php if ( isset( $template['url'] ) && ! empty( $template['url'] ) ) { ?>
							<?php if ( isset( $template['installed'] ) && $template['installed'] ) { ?>
								<a class="button button-primary frm-button-primary" href="<?php echo esc_attr( $template['url'] ); ?>" aria-label="<?php esc_attr_e( 'Create Form', 'formidable' ); ?>">
							<?php } else { ?>
								<a class="frm-install-template button button-primary frm-button-primary" rel="<?php echo esc_attr( $template['url'] ); ?>" aria-label="<?php esc_attr_e( 'Create Form', 'formidable' ); ?>">
							<?php } ?>
								<?php esc_html_e( 'Create Form', 'formidable' ); ?>
							</a>
							<span class="spinner"></span>
						<?php } else { ?>
							<a class="install-now button button-primary frm-button-primary" href="<?php echo esc_url( $pricing ); ?>" target="_blank" aria-label="<?php esc_attr_e( 'Upgrade Now', 'formidable' ); ?>">
								<?php esc_html_e( 'Upgrade Now', 'formidable' ); ?>
							</a>
						<?php } ?>
					</div>
				</div>
			<?php } ?>
		</div>
		<?php include( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/template-name-overlay.php' ); ?>
		<div class="clear"></div>
	</div>
</div>
