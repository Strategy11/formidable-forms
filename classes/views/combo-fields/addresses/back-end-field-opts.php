<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p>
	<label for="address_type_<?php echo esc_attr( $field['id'] ); ?>">
		<?php esc_html_e( 'Address Type', 'formidable' ); ?>
	</label>

	<select name="field_options[address_type_<?php echo esc_attr( $field['id'] ); ?>]" id="address_type_<?php echo esc_attr( $field['id'] ); ?>">
		<option value="international" <?php selected( $field['address_type'], 'international' ); ?>>
			<?php esc_html_e( 'International', 'formidable' ); ?>
		</option>
		<option value="us" <?php selected( $field['address_type'], 'us' ); ?>>
			<?php esc_html_e( 'United States', 'formidable' ); ?>
		</option>
		<option value="europe" <?php selected( $field['address_type'], 'europe' ); ?>>
			<?php esc_html_e( 'Europe', 'formidable' ); ?>
		</option>
		<option value="generic" <?php selected( $field['address_type'], 'generic' ); ?>>
			<?php esc_html_e( 'Other - exclude country field', 'formidable' ); ?>
		</option>
	</select>
</p>
