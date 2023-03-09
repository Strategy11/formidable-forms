<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
// TODO Only include the toggle in the Stripe add on. We want to only support Link in Lite.
// TODO Use a hidden input that sets stripe_link to 1 for all Lite Stripe actions.
?>
<input type="hidden" name="<?php echo esc_attr( $action_control->get_field_name( 'plan_id' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['plan_id'] ); ?>" />
