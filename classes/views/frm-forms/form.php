<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm_form_editor_container" class="frm-m-12 <?php echo ( $has_fields ? 'frm-has-fields' : '' ); ?>">

	<?php
	if ( $has_fields ) {
		// Add form messages.
		require FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php';
	}
	?>

	<div id="frm-fake-page" class="frm_hidden">
		<?php
		/**
		 * Fires before the fake page in form builder.
		 *
		 * @since 6.9
		 *
		 * @param array $form_array Processed form array.
		 */
		do_action( 'frm_before_builder_fake_page', $values );
		?>

		<div class="frm-page-break">
			<div class="frm-collapse-page button frm-button-secondary frm-button-sm">
				<?php
				/* translators: %s: The page number */
				printf( esc_html__( 'Page %s', 'formidable' ), '<span class="frm-page-num">1</span>' );
				?>
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_arrowdown6_icon', array( 'aria-hidden' => 'true' ) ); ?>
			</div>
		</div>

		<?php
		/**
		 * Fires after the fake page in form builder.
		 *
		 * @since 6.9
		 *
		 * @param array $form_array Processed form array.
		 */
		do_action( 'frm_after_builder_fake_page', $values );
		?>
	</div>

	<ul id="frm-show-fields" class="frm_sorting inside">
		<?php
		if ( ! empty( $values['fields'] ) ) {
			$grid_helper     = new FrmFieldGridHelper();
			$values['count'] = 0;
			foreach ( $values['fields'] as $field ) {
				++$values['count'];
				$grid_helper->set_field( $field );
				$grid_helper->maybe_begin_field_wrapper();
				FrmFieldsController::load_single_field( $field, $values );
				$grid_helper->sync_list_size();
				unset( $field );
			}
			$grid_helper->force_close_field_wrapper();
			unset( $grid_helper );
		}
		?>
	</ul>

	<?php if ( ! FrmAppHelper::is_admin_page() ) : ?>
		<p id="frm-form-button">
			<button class="frm_button_submit" disabled="disabled">
				<?php echo esc_html( isset( $form->options['submit_value'] ) ? $form->options['submit_value'] : __( 'Submit', 'formidable' ) ); ?>
			</button>
		</p>
	<?php endif; ?>

	<div class="frm_no_fields">

		<div class="frm_drag_inst">
			<?php esc_html_e( 'Add Fields Here', 'formidable' ); ?>
		</div>
		<p>
			<?php esc_html_e( 'Click or drag a field from the sidebar to add it to your form', 'formidable' ); ?>
		</p>
		<div class="clear"></div>
	</div>

	<?php do_action( 'frm_page_footer', array( 'table' => 'form-builder' ) ); ?>
</div>
<?php
FrmFieldsHelper::bulk_options_overlay();
