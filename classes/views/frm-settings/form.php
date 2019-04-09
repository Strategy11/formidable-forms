<div id="form_global_settings" class="frm_wrap">
	<div class="frm_page_container frm-simple-header">

	<?php
	FrmAppHelper::get_admin_header(
		array(
			'label' => __( 'Settings', 'formidable' ),
		)
	);
	?>

	<div class="columns-2">
		<div class="frm-right-panel">
			<ul class="frm-category-tabs frm-form-setting-tabs">
				<?php $a = FrmAppHelper::simple_get( 't', 'sanitize_title', 'general_settings' ); ?>
				<?php foreach ( $sections as $sec_name => $section ) { ?>
					<li <?php echo ( $a == $sec_name . '_settings' ) ? 'class="tabs active starttab"' : ''; ?>>
						<a href="#<?php echo esc_attr( $sec_name ); ?>_settings"
								data-frmajax="<?php echo esc_attr( isset( $section['ajax'] ) ? $section['ajax'] : '' ); ?>">
							<span class="<?php echo esc_attr( $section['icon'] ); ?>" aria-hidden="true"></span>
							<?php echo esc_html( isset( $section['name'] ) ? $section['name'] : ucfirst( $sec_name ) ); ?>
						</a>
					</li>
				<?php } ?>
			</ul>
		</div>

		<div id="post-body-content" class="frm-fields">

			<?php require( FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php' ); ?>

							<form name="frm_settings_form" method="post" class="frm_settings_form"
									action="?page=formidable-settings<?php echo esc_html( $a ? '&amp;t=' . $a : '' ); ?>">
								<input type="hidden" name="frm_action" value="process-form"/>
								<input type="hidden" name="action" value="process-form"/>
								<?php wp_nonce_field( 'process_form_nonce', 'process_form' ); ?>

								<?php
								foreach ( $sections as $sec_name => $section ) {
									if ( $a === $sec_name . '_settings' ) {
										?>
										<style type="text/css">.<?php echo esc_attr( $sec_name ); ?>_settings {
											display: block;
										}</style>
									<?php } ?>
									<div id="<?php echo esc_attr( $sec_name ); ?>_settings"
											class="<?php echo esc_attr( $sec_name ); ?>_settings tabs-panel <?php echo esc_attr( $a === $sec_name . '_settings' ? 'frm_block' : 'frm_hidden' ); ?>">
										<?php if ( isset( $section['ajax'] ) ) { ?>
											<div class="frm_ajax_settings_tab frm_<?php echo esc_attr( $sec_name ); ?>_settings_ajax">
												<span class="spinner"></span>
											</div>
										<?php } else { ?>
											<h2 class="frm-h2">
												<?php echo esc_html( isset( $section['name'] ) ? $section['name'] : ucfirst( $sec_name ) ); ?>
											</h2>
											<?php
											if ( isset( $section['class'] ) ) {
												call_user_func( array( $section['class'], $section['function'] ) );
											} else {
												call_user_func( ( isset( $section['function'] ) ? $section['function'] : $section ) );
											}
										}
										do_action( 'frm_' .  $sec_name . '_settings_form', $frm_settings );
										?>
									</div>
								<?php } ?>

								<p class="alignright frm_uninstall">
									<a href="javascript:void(0)"
											id="frm_uninstall_now"><?php esc_html_e( 'Uninstall Formidable', 'formidable' ); ?></a>
									<span class="spinner frm_spinner"></span>
								</p>
								<p class="submit">
									<input class="button-primary frm-button-primary" type="submit"
											value="<?php esc_attr_e( 'Update Options', 'formidable' ); ?>"/>
								</p>

							</form>
			</div>
		</div>

	</div>

	<?php do_action( 'frm_after_settings' ); ?>
</div>
