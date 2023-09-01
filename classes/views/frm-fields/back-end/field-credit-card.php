<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! FrmStrpLiteConnectHelper::at_least_one_mode_is_setup() || ! FrmTransLiteActionsController::get_actions_for_form( $field['form_id'] ) ) {
	?>
	<span class="frm-with-icon frm-not-set frm_note_style">
		<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_report_problem_solid_icon' ); ?>
		<?php esc_attr_e( 'This field is not set up yet.', 'formidable' ); ?>
	</span>
	<?php
	return;
}
?>
<div class="frm-lite-credit-card-element">
	<input type="text" placeholder="1234 1234 1234 1234" disabled />
	<div>
		<?php FrmAppHelper::icon_by_class( 'frmfont frm_credit_card_icon' ); ?>
	</div>
</div>
