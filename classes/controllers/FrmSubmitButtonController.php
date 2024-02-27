<?php
/**
 * Submit button controller
 *
 * @since x.x
 * @package Formidable
 */

class FrmSubmitButtonController {

	/**
	 * Shows submit button in form builder.
	 *
	 * @param object $form Form object.
	 */
	public static function show_submit_in_form_builder( $form ) {
		?>
		<div id="frm-form-button" class="frm-show-field-settings" data-fid="submit">
			<button class="frm_button_submit" disabled="disabled">
				<?php echo esc_attr( isset( $form->options['submit_value'] ) ? $form->options['submit_value'] : __( 'Submit', 'formidable' ) ); ?>
			</button>

			<div class="frm-single-settings frm_hidden frm-fields frm-type-text" id="frm-single-settings-submit" data-fid="submit">
				<div>
					<a href="javascript:void(0)" id="logic_<?php echo absint( $field['id'] ); ?>" class="frm_add_logic_row frm_add_logic_link frm-collapsed frm-flex-justify <?php
					echo ( ! empty( $field['hide_field'] ) && ( count( $field['hide_field'] ) > 1 || reset( $field['hide_field'] ) != '' ) ) ? ' frm_hidden' : '';
					?>" aria-expanded="false" tabindex="0" role="button" aria-label="<?php esc_html_e( 'Collapsible Conditional Logic Settings', 'formidable-pro' ); ?>" aria-controls="collapsible-section">
						<?php esc_html_e( 'Conditional Logic', 'formidable-pro' ); ?>
						<?php FrmAppHelper::icon_by_class( 'frmfont frm_arrowdown6_icon', array( 'aria-hidden' => 'true' ) ); ?>
					</a>
					<div class="frm_logic_rows frm_add_remove<?php echo ( ! empty( $field['hide_field'] ) && ( count( $field['hide_field'] ) > 1 || reset( $field['hide_field'] ) != '' ) ) ? '' : ' frm_hidden'; ?>" id="frm_logic_rows_<?php echo absint( $field['id'] ); ?>">
						<h3 aria-expanded="true" tabindex="0" role="button" aria-label="<?php esc_html_e( 'Collapsible Conditional Logic Settings', 'formidable-pro' ); ?>" aria-controls="collapsible-section">
							<?php esc_html_e( 'Conditional Logic', 'formidable-pro' ); ?>
							<?php FrmAppHelper::icon_by_class( 'frmfont frm_arrowdown6_icon', array( 'aria-hidden' => 'true' ) ); ?>
						</h3>
						<div class="frm-collapse-me" role="group">
							<div id="frm_logic_row_<?php echo absint( $field['id'] ); ?>" class="frm-mb-sm">
								<select name="field_options[show_hide_<?php echo absint( $field['id'] ); ?>]" class="auto_width">
									<option value="show" <?php selected( $field['show_hide'], 'show' ); ?>><?php echo ( $field['type'] == 'break' ) ? __( 'Do not skip next page', 'formidable-pro' ) : __( 'Show this field', 'formidable-pro' ); ?></option>
									<option value="hide" <?php selected( $field['show_hide'], 'hide' ); ?>><?php echo ( $field['type'] == 'break' ) ? __( 'Skip next page', 'formidable-pro' ) : __( 'Hide this field', 'formidable-pro' ); ?></option>
								</select>

								<?php
								$all_select =
									'<select name="field_options[any_all_' . absint( $field['id'] ) . ']" class="auto_width">' .
									'<option value="any" ' . selected( $field['any_all'], 'any', false ) . '>' . __( 'any', 'formidable-pro' ) . '</option>' .
									'<option value="all" ' . selected( $field['any_all'], 'all', false ) . '>' . __( 'all', 'formidable-pro' ) . '</option>' .
									'</select>';

								printf( __( 'if %s of the following match:', 'formidable-pro' ), $all_select );
								unset($all_select);

								if ( ! empty( $field['hide_field'] ) ) {
									foreach ( (array) $field['hide_field'] as $meta_name => $hide_field ) {
										include(FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/_logic_row.php');
									}
								}
								?>
							</div>
							<a href="javascript:void(0)" class="frm_add_logic_row button frm-button-secondary">
								<?php FrmProAppHelper::icon_by_class( 'frmfont frm_plus_icon' ); ?>
								<?php esc_html_e( 'Add', 'formidable-pro' ); ?>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	// TODO: remove.
	const FIELD_TYPE = 'submit';

	// TODO: remove.
	public static function get_submit_field( $form_id ) {
		$fields = FrmField::get_all_types_in_form( $form_id, self::FIELD_TYPE, 1 );
		if ( ! $fields ) {
			return false;
		}

		return reset( $fields );
	}
}
