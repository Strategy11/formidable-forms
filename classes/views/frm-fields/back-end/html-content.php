<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
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
		$e_args  = array(
			'textarea_name' => 'field_options[description_' . absint( $field['id'] ) . ']',
			'textarea_rows' => 8,
		);
		$html_id = 'frm_description_' . absint( $field['id'] );
		wp_editor( $field['description'], $html_id, $e_args );
		?>
	</span>
</p>
