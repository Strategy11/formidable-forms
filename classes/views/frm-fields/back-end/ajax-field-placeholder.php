<?php
/**
 * Ajax field placeholder in the form builder.
 *
 * @package Formidable
 *
 * @var object $field_object Field object with 'id', 'type', 'form_id' properties.
 * @var string $li_classes   Classes for the list item wrapper.
 * @var array  $display      Display options; 'type' is used.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<li id="frm_field_id_<?php echo esc_attr( $field_object->id ); ?>" class="<?php echo esc_attr( $li_classes ); ?> frm_field_loading" data-fid="<?php echo esc_attr( $field_object->id ); ?>" data-formid="<?php echo esc_attr( 'divider' === $field_object->type ? FrmField::get_option( $field_object, 'form_select' ) : $field_object->form_id ); ?>" data-ftype="<?php echo esc_attr( $display['type'] ); ?>">
<span class="frm-wait frm_visible_spinner"></span>
<span class="frm_hidden_fdata frm_hidden"><?php echo htmlspecialchars( json_encode( $field_object ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
</li>
