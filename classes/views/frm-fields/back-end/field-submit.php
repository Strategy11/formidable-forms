<?php
/**
 * View for submit button field in form builder
 *
 * @since x.x
 * @package Formidable
 *
 * @var array $field Field array.
 */

?>
<p class="frm-form-button">
	<button id="field_label_<?php echo intval( $field['id'] ); ?>" class="frm_button_submit" disabled>
		<?php echo esc_html( $field['name'] ); ?>
	</button>
</p>
