<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-banner-alert frm_error_style frm_previous_install">
	<?php esc_html_e( 'You are running a version of Formidable Forms that may not be compatible with your version of Formidable Forms Pro.', 'formidable' ); ?>

	<?php if ( FrmAddonsController::is_license_expired() ) : ?>
		<?php
		$utm = array(
			'campaign' => 'outdated',
			'content'  => 'min-pro-version-notice',
		);
		?>
		Please <a href="<?php echo esc_url( FrmAppHelper::admin_upgrade_link( $utm, 'account/downloads/' ) ); ?>">renew now</a> to get the latest version.
	<?php else : ?>
		Please <a href="<?php echo esc_url( admin_url( 'plugins.php?s=formidable%20forms%20pro' ) ); ?>">update now</a>.
	<?php endif; ?>
</div>
