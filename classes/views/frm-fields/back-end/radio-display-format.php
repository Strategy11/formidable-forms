<?php
/**
 * Display format option
 *
 * @since 5.0.04
 * @package Formidable
 *
 * @var array $field   Field data.
 * @var array $options Options array.
 * @var array $args    The arguments.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm_display_format_<?php echo intval( $field['id'] ); ?>_container" class="frm_form_field">
	<label for="frm_image_options_<?php echo intval( $field['id'] ); ?>"><?php esc_html_e( 'Display format', 'formidable' ); ?></label>
	<?php FrmAppHelper::images_dropdown( $args ); ?>
</div>
