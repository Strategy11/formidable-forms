<?php
/**
 * Primary options for gdpr field
 *
 * @package Formidable
 * @since 6.19
 *
 * @var array        $field Field array.
 * @var array        $args  Includes 'field', 'display', and 'values'.
 * @var FrmFieldGdpr $this  Field type object.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

// Options here need to be declared in FrmFieldGdpr::extra_field_opts().
$field_id = $field['id'];
?>
<p>
	<label for="gdpr_agreement_text_<?php echo esc_attr( $field_id ); ?>">
		<?php esc_html_e( 'Agreement text', 'formidable' ); ?>
	</label>
	<input type="text" name="field_options[gdpr_agreement_text_<?php echo esc_attr( $field_id ); ?>]" id="gdpr_agreement_text_<?php echo esc_attr( $field_id ); ?>" value="<?php echo esc_attr( FrmField::get_option( $field, 'gdpr_agreement_text' ) ); ?>">
</p>
