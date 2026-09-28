<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
/**
 * @since x.x This was moved from Pro.
 *
 * @var array $field
 */

$field_obj            = FrmFieldFactory::get_field_type( $field['type'] );
$selected_value       = ! empty( $field['autocomplete'] ) ? (string) $field['autocomplete'] : '';
$autocomplete_options = $field_obj->autocomplete_options();

/**
 * Allows modifying the list of autocomplete attribute options.
 *
 * @since 5.4.1 This was added in Pro.
 * @since x.x   This was moved to Lite.
 *
 * @param array $autocomplete_options The list of autocomplete attribute options.
 * @param array $field                The form field.
 */
$autocomplete_options = apply_filters( 'frm_autocomplete_options', $autocomplete_options, $field );
?>
<p class="frm6 frm_form_field">
	<label class="frm-h-stack-xs" id="for_field_options_autocomplete_<?php echo absint( $field['id'] ); ?>" for="field_options_autocomplete_<?php echo absint( $field['id'] ); ?>">
		<span><?php esc_html_e( 'Autocomplete', 'formidable' ); ?></span>
		<?php
		FrmAppHelper::tooltip_icon(
			__( 'The autocomplete attribute asks the browser to attempt autocompletion, based on user history.', 'formidable' ),
			array(
				'data-placement' => 'right',
				'class'          => 'frm-flex',
			)
		);
		?>
	</label>
	<select name="field_options[autocomplete_<?php echo absint( $field['id'] ); ?>]" id="field_options_autocomplete_<?php echo absint( $field['id'] ); ?>">
		<?php
		FrmHtmlHelper::echo_dropdown_option( __( '&mdash; Select &mdash;', 'formidable' ), '' === $selected_value, array( 'value' => '' ) );

		foreach ( $autocomplete_options as $value => $label ) {
			FrmHtmlHelper::echo_dropdown_option( $label, $selected_value === (string) $value, array( 'value' => $value ) );
		}
		?>
	</select>
</p>
