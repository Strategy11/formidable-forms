<div id="frm_form_editor_container">

	<div class="frm_no_fields <?php echo ( isset( $values['fields'] ) && ! empty( $values['fields'] ) ) ? 'frm_hidden' : ''; ?>">

		<div class="frm_drag_inst">
			<?php esc_html_e( 'Add Fields Here', 'formidable' ); ?>
		</div>
		<p>
			<?php esc_html_e( 'Click or drag a field from the sidebar to add it to your form', 'formidable' ); ?>
		</p>
		<div class="clear"></div>
	</div>
	<ul id="new_fields" class="frm_sorting inside frm_grid_container">
		<?php
		if ( isset( $values['fields'] ) && ! empty( $values['fields'] ) ) {
			$values['count'] = 0;
			foreach ( $values['fields'] as $field ) {
				$values['count']++;
				FrmFieldsController::load_single_field( $field, $values );
				unset( $field );
			}
		}
		?>
	</ul>

	<input type="hidden" name="frm_end" value="1" />

</div>
