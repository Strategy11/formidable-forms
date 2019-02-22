<div id="form_settings_page" class="frm_wrap">
	<form method="post" class="frm_form_settings">
	<div class="frm_page_container">

	<?php
	FrmAppHelper::get_admin_header(
		array(
			'label'       => __( 'Settings', 'formidable' ),
			'form'        => $form,
			'hide_title'  => true,
			'close'       => '?page=formidable',
		)
	);
	?>

	<div id="frm-bar-two">
		<?php
		FrmFormsHelper::form_switcher( $form->name );

		// Add form messages.
		require( FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php' );

		include( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/_publish_box.php' );
		?>
	</div>

	<div class="columns-2">
		<div class="postbox-container frm-right-panel">
			<ul class="frm-category-tabs frm-form-setting-tabs">
				<?php foreach ( $sections as $section ) { ?>
					<li class="<?php echo esc_attr( $current === $section['anchor'] ? 'tabs active' : '' ); ?>">
						<a href="#<?php echo esc_attr( $section['anchor'] ); ?>">
							<?php echo esc_html( $section['name'] ); ?>
						</a>
					</li>
				<?php } ?>
			</ul>
		</div>

		<div id="post-body-content" class="frm-fields">

				<input type="hidden" name="id" id="form_id" value="<?php echo (int) $id; ?>" />
				<input type="hidden" name="frm_action" value="update_settings" />
				<?php wp_nonce_field( 'process_form_nonce', 'process_form' ); ?>

				<div id="frm-categorydiv">
					<div class="inside frm-inner-content">>
						<?php foreach ( $sections as $section ) { ?>
							<div id="<?php echo esc_attr( $section['anchor'] ); ?>" class="tabs-panel <?php echo ( $current === $section['anchor'] ) ? ' frm_block' : ' frm_hidden'; ?>">
								<h2>
									<?php echo esc_html( $section['title'] ); ?>
								</h2>

								<?php
								if ( isset( $section['class'] ) ) {
									call_user_func( array( $section['class'], $section['function'] ), $values );
								} else {
									call_user_func( ( isset( $section['function'] ) ? $section['function'] : $section ), $values );
								}
								?>
							</div>
						<?php } ?>

						<?php do_action( 'frm_add_form_option_section', $values ); ?>
						<div class="clear"></div>

					</div>

				</div>
		</div>
	<?php require( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/sidebar-settings.php' ); ?>
	</div>
	</div>
	</form>
</div>
