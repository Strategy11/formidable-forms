<?php
/**
 * @since 6.8
 *
 * @var array $features The list of pro features.
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-dashboard-widget frm-card-item frm-px-0 frm-p-0">
	<div class="frm-pro-features-list">
		<h2><?php esc_html_e( 'Unlock all the Powerful Features to Defy the Limits', 'formidable' ); ?></h2>
		<ul class="frm_two_col frm-green-icons">
			<?php foreach ( $features as $feature ) : ?>
				<li>
					<?php FrmAppHelper::icon_by_class( 'frmfont frm_checkmark_icon' ); ?>
					<?php echo esc_html( $feature ); ?>
				</li>
			<?php endforeach; ?>
		</ul>
		<a target="_blank" href="<?php echo esc_url( FrmAppHelper::admin_upgrade_link( 'dashboard-discount' ) ); ?>" title="Upgrade" class="frm-button-primary">
			<?php esc_html_e( 'Upgrade to Pro & Get 50% Off', 'formidable' ); ?>
		</a>
	</div>
</div>
