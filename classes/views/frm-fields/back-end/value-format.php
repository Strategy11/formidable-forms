<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="frm-has-modal">
	<label for="frm_format_<?php echo esc_attr( $field['field_key'] ); ?>" class="frm_help" title="<?php esc_attr_e( 'Insert the format you would like to accept. Use a regular expression starting with ^ or an exact format like (999)999-9999.', 'formidable' ); ?>">
		<?php esc_html_e( 'Format', 'formidable' ); ?>
	</label>
	<span class="frm-with-right-icon">
		<?php
		FrmAppHelper::icon_by_class(
			'frm_icon_font frm_more_horiz_solid_icon frm-show-inline-modal',
			array(
				'data-open' => 'frm-input-mask-box',
				'title'     => esc_attr__( 'Toggle Options', 'formidable' ),
			)
		);
		?>
		<input type="text" class="frm_long_input frm_format_opt" value="<?php echo esc_attr( $field['format'] ); ?>" name="field_options[format_<?php echo absint( $field['id'] ); ?>]" id="frm_format_<?php echo absint( $field['id'] ); ?>" data-fid="<?php echo intval( $field['id'] ); ?>" />
	</span>
</p>
