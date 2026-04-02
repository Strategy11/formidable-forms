<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
/**
 * @since x.x
 *
 * @var array $field
 */
?>
<p class="frm6 frm_form_field frm_show_upgrade">
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
	<select <?php FrmAppHelper::array_to_html_params( $autocomplete_upsell_atts, true ); ?>>
		<option value=""><?php esc_html_e( '&mdash; Select &mdash;' ); ?></option>
	</select>
</p>
