<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<input type="hidden" name="<?php echo esc_attr( $action_control->get_field_name( 'plan_id' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['plan_id'] ); ?>" />
