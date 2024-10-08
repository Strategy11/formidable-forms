<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$button_upgrade_link      = FrmAppHelper::admin_upgrade_link(
	array(
		'medium'  => 'settings-license',
		'content' => 'global-settings-license-box-get-formidable-button',
	)
);
$unlock_more_upgrade_link = FrmAppHelper::admin_upgrade_link(
	array(
		'medium'  => 'settings-license',
		'content' => 'global-settings-license-box-unlock-more',
	)
);
?>
<div id="frm_license_top" class="frm_unauthorized_box">
	<p id="frm-connect-btns" class="frm-show-unauthorized">
		<a href="<?php echo esc_url( FrmAddonsController::connect_link() ); ?>" class="button-primary frm-button-primary frm-button-sm">
			<?php esc_html_e( 'Connect an Account', 'formidable' ); ?>
		</a>
		<?php esc_html_e( 'or', 'formidable' ); ?>
		<a href="<?php echo esc_url( $button_upgrade_link ); ?>" target="_blank" class="button-secondary frm-button-secondary frm-button-sm">
			<?php esc_html_e( 'Get Formidable Now', 'formidable' ); ?>
		</a>
	</p>

	<div id="frm-using-lite" class="frm-show-unauthorized">
		<p>
			<?php echo esc_html( FrmAppHelper::copy_for_lite_license() ); ?>
		</p>
		<p>
			<?php
			printf(
				/* translators: %1$s: Start link HTML, %2$s: End link HTML */
				esc_html__( 'To unlock more features consider %1$supgrading to PRO%2$s.', 'formidable' ),
				'<a href="' . esc_url( $unlock_more_upgrade_link ) . '">',
				'</a>'
			);
			?>
		</p>
	</div>
</div>

<div class="frm_pro_license_msg frm_hidden"></div>
