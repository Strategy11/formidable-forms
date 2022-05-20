<?php
/**
 * View file for image options of Radio or Checkbox field.
 *
 * @package Formidable
 *
 * @var array $args Arguments. Contains `field`, `display` and `values`.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

FrmFieldsHelper::show_radio_display_format( $args['field'] );
?>
<p class="frm6 frm_form_field frm_noallow frm_show_upgrade" data-upgrade="<?php esc_attr_e( 'Separate Values', 'formidable' ); ?>" data-message="<?php esc_attr_e( 'Add a separate value to use for calculations, email routing, saving to the database, and many other uses. The option values are saved while the option labels are shown in the form.', 'formidable' ); ?>" data-medium="builder" data-content="separate-values">
	<label>
		<input type="checkbox" value="1" disabled="disabled" />
		<?php esc_html_e( 'Use separate values', 'formidable' ); ?>
	</label>
</p>
