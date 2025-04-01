<?php
/**
 * Show the GDPR Field on the front-end.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
$agreement_text = $field['gdpr_agreement_text'];
$field_id       = $field['id'];
$checked        = FrmAppHelper::check_selected( $field['value'], 1 ) ? ' checked="checked"' : '';
$label_id       = 'frm-gdpr-accept-' . $field_id;
?>

<?php if ( ! FrmFieldGdprHelper::hide_gdpr_field() ) : ?>
<div class="frm_checkbox" role="group" aria-labelledby="<?php echo esc_attr( $label_id ); ?>">
	<label for="<?php echo esc_attr( $label_id ); ?>">
		<input type="checkbox" aria-required="true" name="item_meta[<?php echo esc_attr( $field_id ); ?>]" id="<?php echo esc_attr( $label_id ); ?>" value="1" 
		<?php echo $checked . ' '; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php do_action( 'frm_field_input_html', $field ); ?>
		/>
		<?php FrmAppHelper::kses_echo( $agreement_text, array( 'a' ) ); ?>
	</label>
</div>
<?php elseif ( current_user_can( 'frm_edit_forms' ) ) : ?>
	<div class="frm_checkbox" role="group" aria-labelledby="<?php echo esc_attr( $label_id ); ?>">
		<label for="<?php echo esc_attr( $label_id ); ?>">
			<?php
			/* translators: %1$s: Link HTML, %2$s: End link */
			printf( esc_html__( 'GDPR field is disabled. Please enable it in the Formidable %1$sSettings%2$s.', 'formidable' ), '<a href="?page=formidable-settings" target="_blank">', '</a>' );
			?>
		</label>
	</div>
<?php endif; ?>
