<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
// TODO Only include the toggle in the Stripe add on. We want to only support Link in Lite.
// TODO Use a hidden input that sets stripe_link to 1 for all Lite Stripe actions.
?>
<input type="hidden" name="<?php echo esc_attr( $action_control->get_field_name( 'plan_id' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['plan_id'] ); ?>" />

<?php
$toggle_id   = $action_control->get_field_id( 'stripe_link' );
$toggle_name = $action_control->get_field_name( 'stripe_link' );
?>
<div>
	<?php
	FrmProHtmlHelper::toggle(
		$toggle_id,
		$toggle_name,
		array(
			'div_class' => 'with_frm_style frm_toggle',
			'checked'   => ! empty( $form_action->post_content['stripe_link'] ),
			'echo'      => true,
		)
	);
	?>
	<label for="<?php echo esc_attr( $toggle_id ); ?>" id="<?php echo esc_attr( $toggle_id ); ?>_label">
		<?php
		$stripe_link_documentation_url = 'https://stripe.com/docs/payments/link/accept-a-payment';
		printf(
			// translators: %1$s: Anchor open tag, %2$s: Anchor close tag.
			esc_html__( 'Use previously saved card %1$swith Stripe link%2$s.', 'formidable' ),
			'<a href="' . esc_url( $stripe_link_documentation_url ) . '" target="_blank">',
			'</a>'
		);
		?>
	</label>
</div>
