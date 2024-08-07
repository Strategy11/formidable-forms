<?php
/**
 * View for submit button field in form builder
 *
 * @since 6.9
 * @package Formidable
 *
 * @var array $field Field array.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="frm-form-button">
	<button id="field_label_<?php echo intval( $field['id'] ); ?>" class="frm_button_submit" disabled>
		<?php echo esc_html( $field['name'] ); ?>
	</button>
</p>
