<div id="form_global_settings" class="frm_wrap">
	<form name="frm_settings_form" method="post" class="frm_settings_form"
		action="?page=formidable-settings<?php echo esc_html( $current ? '&amp;t=' . $current : '' ); ?>">
		<div class="frm_page_container">

			<?php
			FrmAppHelper::get_admin_header(
				array(
					'label'   => __( 'Settings', 'formidable' ),
					'publish' => array( 'FrmSettingsController::save_button', array() ),
				)
			);
			?>

			<div class="columns-2">
				<div class="frm-right-panel">
					<?php include( FrmAppHelper::plugin_path() . '/classes/views/frm-settings/tabs.php' ); ?>
				</div>

				<div id="post-body-content" class="frm-fields">

								<?php require( FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php' ); ?>
								<input type="hidden" name="frm_action" value="process-form"/>
								<input type="hidden" name="action" value="process-form"/>
								<?php wp_nonce_field( 'process_form_nonce', 'process_form' ); ?>

								<?php
								foreach ( $sections as $section ) {
									if ( $current === $section['anchor'] ) {
										?>
										<style type="text/css">.<?php echo esc_attr( $section['anchor'] ); ?> {
											display: block;
										}</style>
									<?php } ?>
									<div id="<?php echo esc_attr( $section['anchor'] ); ?>"
											class="<?php echo esc_attr( $section['anchor'] ); ?> tabs-panel <?php echo esc_attr( $current === $section['anchor'] ? 'frm_block' : 'frm_hidden' ); ?>">
										<?php if ( isset( $section['ajax'] ) ) { ?>
											<div class="frm_ajax_settings_tab frm_<?php echo esc_attr( $section['anchor'] ); ?>_ajax">
												<span class="frm-wait"></span>
											</div>
										<?php } else { ?>
											<h2 class="frm-h2">
												<?php echo esc_html( $section['name'] ); ?>
											</h2>
											<?php
											if ( isset( $section['class'] ) ) {
												call_user_func( array( $section['class'], $section['function'] ) );
											} elseif ( isset( $section['function'] ) ) {
												call_user_func( $section['function'] );
											}
										}
										do_action( 'frm_' . $section['anchor'] . '_form', $frm_settings );
										?>
									</div>
								<?php } ?>
				</div>
			</div>
		</div>
	</form>
	<?php do_action( 'frm_after_settings' ); ?>
</div>
