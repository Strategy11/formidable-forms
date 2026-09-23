<?php
/**
 * HTML content editor for HTML fields.
 *
 * @package Formidable
 *
 * @var array $field Field data including 'id' and 'description'.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p>
	<label>
		<?php esc_html_e( 'Content', 'formidable' ); ?>
	</label>
	<?php
	$e_args  = array(
		'textarea_name' => 'field_options[description_' . absint( $field['id'] ) . ']',
		'textarea_rows' => 7,
		// wp_skip_init keeps the markup/quicktags scaffolding but stops core's own page-load init loop from
		// booting TinyMCE here; js/admin/dom.js lazily boots it the first time the field's panel is opened.
		'tinymce'       => array(
			'wp_skip_init' => true,
		),
	);
	$html_id = 'frm_description_' . absint( $field['id'] );
	wp_editor( $field['description'], $html_id, $e_args );
	?>
</p>
