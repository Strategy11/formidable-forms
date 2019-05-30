<p class="frm-has-modal">
	<label>
		<?php esc_html_e( 'Content', 'formidable' ); ?>
	</label>
	<span class="frm-with-right-icon">
		<?php
		FrmAppHelper::icon_by_class(
			'frm_icon_font frm_more_horiz_solid_icon frm-show-inline-modal',
			array(
				'data-open' => 'frm-smart-values-box',
				'title'     => esc_attr__( 'Toggle Options', 'formidable' ),
			)
		);
		?>
		<textarea name="field_options[description_<?php echo absint( $field['id'] ); ?>]" id="frm_description_<?php echo esc_attr( $field['id'] ); ?>" rows="8"><?php
		echo FrmAppHelper::esc_textarea( $field['description'] ); // WPCS: XSS ok.
		?></textarea>
	</span>
</p>
