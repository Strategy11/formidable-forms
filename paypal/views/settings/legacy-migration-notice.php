<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

// Notice shown inside a legacy PayPal action, offering to convert it to PayPal Commerce.
// $form_action is the legacy action. $blocker explains why it cannot be converted yet, and is empty when it can.

$button_attrs = array(
	'type'           => 'button',
	'class'          => 'button frm-button-primary frm-paypal-migrate-legacy',
	'data-action-id' => (int) $form_action->ID,
);

if ( $blocker ) {
	$button_attrs['disabled'] = 'disabled';
}
?>
<div class="frm_note_style frm-paypal-legacy-migration">
	<div class="frm-paypal-migration-intro">
		<p class="frm-mt-0 frm-mb-xs">
			<strong class="frm-text-grey-900"><?php esc_html_e( 'Upgrade to PayPal Commerce', 'formidable' ); ?></strong>
		</p>
		<p class="frm-mt-0 frm-mb-xs">
			<?php esc_html_e( 'PayPal Standard is a legacy integration. Convert this action to a PayPal Commerce payment action to take payments on your form page, including cards, Pay Later and wallets. Your settings are copied to the new action and this legacy action is turned off.', 'formidable' ); ?>
		</p>
		<p class="frm-mt-0 frm-mb-sm">
			<?php esc_html_e( 'Save any unsaved changes to this form before converting. The page reloads afterward.', 'formidable' ); ?>
		</p>
		<?php if ( $blocker ) : ?>
			<div class="frm_warning_style frm-with-icon frm-mt-0 frm-mb-sm">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_alert_icon', array( 'style' => 'width:24px' ) ); ?>
				<span><?php echo esc_html( $blocker ); ?></span>
			</div>
		<?php endif; ?>
		<button <?php FrmAppHelper::array_to_html_params( $button_attrs, true ); ?>>
			<?php esc_html_e( 'Convert to PayPal Commerce', 'formidable' ); ?>
		</button>
	</div>
	<div class="frm-paypal-migration-result" role="status" aria-live="polite"></div>
</div>
