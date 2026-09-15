<?php
/**
 * Total field form builder view.
 *
 * @package Formidable
 *
 * @var FrmFieldTotal $this       Field type handler that included this template.
 * @var string       $html_id    HTML id attribute for the hidden input.
 * @var string       $field_name HTML name attribute for the hidden input.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<input type="hidden" id="<?php echo esc_attr( $html_id ); ?>" name="<?php echo esc_attr( $field_name ); ?>" value="0" />
<p><?php echo esc_html( $this->get_builder_display_value() ); ?></p>
