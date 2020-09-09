<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
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
			'publish'     => array( 'FrmFormsController::form_publish_button', compact( 'values' ) ),
		)
	);
	?>

	<div class="columns-2">
		<div class="frm-right-panel">
			<?php include( FrmAppHelper::plugin_path() . '/classes/views/frm-settings/tabs.php' ); ?>
		</div>

		<div id="post-body-content" class="frm-fields">

			<input type="hidden" name="id" id="form_id" value="<?php echo (int) $id; ?>" />
			<input type="hidden" name="frm_action" value="update_settings" />
			<?php wp_nonce_field( 'process_form_nonce', 'process_form' ); ?>

			<div id="frm-categorydiv">
				<div class="inside frm-inner-content">
					<?php
					// Add form messages.
					require( FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php' );
					?>

					<?php foreach ( $sections as $section ) { ?>
						<div id="<?php echo esc_attr( $section['id'] ); ?>" class="tabs-panel <?php echo ( $current === $section['anchor'] ) ? ' frm_block' : ' frm_hidden'; ?> <?php echo esc_attr( $section['anchor'] . ' frm_' . $section['anchor'] ); ?>">
							<h2>
								<?php echo esc_html( $section['title'] ); ?>
							</h2>

							<?php
							if ( isset( $section['class'] ) ) {
								call_user_func( array( $section['class'], $section['function'] ), $values );
							} elseif ( ! isset( $section['data'] ) ) {
								call_user_func( ( isset( $section['function'] ) ? $section['function'] : $section ), $values );
							}
							?>
						</div>
					<?php } ?>

					<?php do_action( 'frm_add_form_option_section', $values ); ?>
					<div class="clear"></div>
					<?php include( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/mb_insert_fields.php' ); ?>
				</div>
			</div>
		</div>
	</div>
	</div>
	</form>
</div>

<?php
FrmAppHelper::multiselect_accessibility();
