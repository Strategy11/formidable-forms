<li id="frm_delete_field_<?php echo esc_attr( $field['id'] . '-' . $opt_key ) ?>_container" class="frm_single_option">
	<a href="javascript:void(0)" class="frm_single_visible_hover frm_icon_font frm_delete_icon" data-fid="<?php echo esc_attr( $field['id'] ); ?>"> </a>
	<?php if ( 'select' !== $field['type'] ) { ?>
		<input type="<?php echo esc_attr( $field['type'] ); ?>" name="<?php echo esc_attr( $field_name . ( 'checkbox' === $field['type'] ? '[]' : '' ) ); ?>" value="<?php echo esc_attr( $field_val ) ?>"<?php echo esc_html( isset( $checked ) ? $checked : '' ); ?>/>
	<?php } ?>

	<label class="frm_ipe_field_option field_<?php echo esc_attr( $field['id'] ) ?>_option <?php echo esc_attr( $field['separate_value'] ? 'frm_with_key' : '' ); ?>" id="<?php echo esc_attr( $html_id . '-' . $opt_key ) ?>"><?php
		echo '' === $opt ? esc_html__( '(Blank)', 'formidable' ) : FrmAppHelper::kses( $opt, 'all' ); // WPCS: XSS ok.
	?></label>
	<input type="hidden" name="field_options[options_<?php echo esc_attr( $field['id'] ) ?>][<?php echo esc_attr( $opt_key ) ?>][label]" value="<?php echo esc_attr( $opt ) ?>" />

	<span class="frm_option_key field_<?php echo esc_attr( $field['id'] ) ?>_option_key<?php echo esc_attr( $field['separate_value'] ? '' : ' frm_hidden' ); ?>">
		<label class="frm-show-click frm_ipe_field_option_key" id="field_key_<?php echo esc_attr( $field['id'] . '-' . $opt_key ) ?>"><?php
			echo ( '' === $field_val ) ? esc_html__( '(Blank)', 'formidable' ) : FrmAppHelper::kses( $field_val, 'all' ); // WPCS: XSS ok.
		?></label>
		<input type="hidden" name="field_options[options_<?php echo esc_attr( $field['id'] ) ?>][<?php echo esc_attr( $opt_key ) ?>][value]" value="<?php echo esc_attr( $field_val ) ?>" />
	</span>
</li>
<?php
unset( $field_val, $opt, $opt_key );
