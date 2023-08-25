<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="frm_grid_container">
	<label for="frm_currency" class="frm4 frm_form_field frm_help" title="<?php esc_attr_e( 'Select the currency to be used by Formidable globally.', 'formidable' ); ?>">
		<?php esc_html_e( 'Currency', 'formidable' ); ?>
	</label>
	<select id="frm_currency" name="frm_currency" class="frm8 frm_form_field">
		<?php
		$selected_currency = ! empty( $frm_settings->currency ) ? strtoupper( $frm_settings->currency ) : 'USD';
		foreach ( $currencies as $code => $currency ) {
			?>
			<option value="<?php echo esc_attr( $code ); ?>"<?php selected( $selected_currency, strtoupper( $code ) ); ?>>
				<?php echo esc_html( $currency['name'] . ' (' . $code . ')' ); ?>
			</option>
			<?php
		}
		?>
	</select>
</p>
