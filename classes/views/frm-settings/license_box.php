<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$button_upgrade_text = FrmSalesApi::get_best_sale_value( 'global_settings_license_cta_text' );
if ( ! $button_upgrade_text ) {
	$button_upgrade_text = __( 'Get Formidable Now', 'formidable' );
}

$button_upgrade_link = FrmSalesApi::get_best_sale_value( 'global_settings_license_cta_link' );
$utm                 = array(
	'medium'  => 'settings-license',
	'content' => 'global-settings-license-box-get-formidable-button',
);
if ( $button_upgrade_link ) {
	$button_upgrade_link = FrmAppHelper::maybe_add_missing_utm( $button_upgrade_link, $utm );
} else {
	$button_upgrade_link = FrmAppHelper::admin_upgrade_link( $utm );
}

$unlock_more_upgrade_text = FrmSalesApi::get_best_sale_value( 'global_settings_unlock_more_cta_text' );
if ( ! $unlock_more_upgrade_text ) {
	$unlock_more_upgrade_text = __( 'upgrading to PRO', 'formidable' );
}

$unlock_more_upgrade_link = FrmSalesApi::get_best_sale_value( 'global_settings_unlock_more_cta_link' );
$utm                      = array(
	'medium'  => 'settings-license',
	'content' => 'global-settings-license-box-unlock-more',
);
if ( $unlock_more_upgrade_link ) {
	$unlock_more_upgrade_link = FrmAppHelper::maybe_add_missing_utm( $unlock_more_upgrade_link, $utm );
} else {
	$unlock_more_upgrade_link = FrmAppHelper::admin_upgrade_link( $utm );
}
?>
<div id="frm_license_top" class="frm_unauthorized_box">
	<p id="frm-connect-btns" class="frm-show-unauthorized">
		<a href="<?php echo esc_url( FrmAddonsController::connect_link() ); ?>" class="button-primary frm-button-primary frm-button-sm">
			<?php esc_html_e( 'Connect an Account', 'formidable' ); ?>
		</a>
		<?php esc_html_e( 'or', 'formidable' ); ?>
		<a href="<?php echo esc_url( $button_upgrade_link ); ?>" target="_blank" class="button-secondary frm-button-secondary frm-button-sm">
			<?php echo esc_html( $button_upgrade_text ); ?>
		</a>
	</p>

	<div id="frm-using-lite" class="frm-show-unauthorized">
		<p>
			<?php echo esc_html( FrmAppHelper::copy_for_lite_license() ); ?>
		</p>
		<p>
			<?php
			printf(
				/* translators: %1$s: Start link HTML, %2$s: CTA Text (Default is "upgrading to PRO"), %3$s: End link HTML */
				esc_html__( 'To unlock more features consider %1$s%2$s%3$s.', 'formidable' ),
				'<a href="' . esc_url( $unlock_more_upgrade_link ) . '">',
				esc_html( $unlock_more_upgrade_text ),
				'</a>'
			);
			?>
		</p>
	</div>
</div>

<div class="frm_pro_license_msg frm_hidden"></div>
