<?php
/**
 * Display format option
 *
 * @since 4.12.0
 * @package Formidable
 *
 * @var array $field   Field data.
 * @var array $options Options array.
 * @var array $args    The arguments.
 */

?>
<p id="frm_display_format_<?php echo intval( $field['id'] ); ?>_container" class="frm_form_field">
	<label for="frm_image_options_<?php echo intval( $field['id'] ); ?>"><?php esc_html_e( 'Display format', 'formidable' ); ?></label>
	<?php FrmAppHelper::images_dropdown( $args ); ?>
</p>
