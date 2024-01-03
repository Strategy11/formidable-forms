<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
$id = $field['form_id'];
?>
<?php //include( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/mb_insert_fields.php' ); ?>

<p class="">
	<label>
		<?php esc_html_e( 'Content', 'formidable' ); ?>
	</label>
	<?php
	$e_args  = array(
		'textarea_name' => 'field_options[description_' . absint( $field['id'] ) . ']',
		'textarea_rows' => 8,
	);
	$html_id = 'frm_description_' . absint( $field['id'] );
	wp_editor( $field['description'], $html_id, $e_args );
	?>
</p>
