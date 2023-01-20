<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
$html_id = 'frm_default_value_' . absint( $field['id'] );
?>
<p
class="frm-has-modal frm-default-value-wrapper default-value-section-<?php echo esc_attr( $field['id'] . ( isset( $default_value_types['default_value']['current'] ) ? '' : ' frm_hidden' ) ); ?>"
<?php echo $field['type'] === 'rte' ? 'data-modal-trigger-title="' . esc_attr__( 'Toggle Options', 'formidable' ) . '"' : ''; ?>
<?php echo $field['type'] === 'rte' ? 'data-html-id="' . esc_attr( $html_id ) . '"' : ''; ?>
id="default-value-for-<?php echo esc_attr( $field['id'] ); ?>">
	<label for="frm_default_value_<?php echo esc_attr( $field['id'] ); ?>">
		<?php esc_html_e( 'Default Value', 'formidable' ); ?>
	</label>
	<span class="frm-with-right-icon">
		<?php
		$special_default = ( isset( $field['post_field'] ) && $field['post_field'] === 'post_category' ) || $field['type'] === 'data';
		if ( $field['type'] !== 'rte' ) {
			FrmAppHelper::icon_by_class(
				'frm_icon_font frm_more_horiz_solid_icon frm-show-inline-modal',
				array(
					'data-open' => $special_default ? 'frm-tax-box-' . $field['id'] : 'frm-smart-values-box',
					'title'     => esc_attr__( 'Toggle Options', 'formidable' ),
				)
			);
		}

		unset( $special_default );

		if ( isset( $display['default_value'] ) && $display['default_value'] ) {
			$default_name  = 'field_options[dyn_default_value_' . $field['id'] . ']';
			$default_value = isset( $field['dyn_default_value'] ) ? $field['dyn_default_value'] : '';
		} else {
			$default_name  = 'default_value_' . $field['id'];
			$default_value = $field['default_value'];
		}
		$field_obj->default_value_to_string( $default_value );

		$field_obj->show_default_value_field( $field, $default_name, $default_value );
		?>
	</span>
</p>