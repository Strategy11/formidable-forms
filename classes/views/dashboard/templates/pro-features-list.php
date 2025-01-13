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

$discount_link = FrmAppHelper::admin_upgrade_link(
	array(
		'medium'  => 'dashboard-discount',
		'content' => 'dashboard-defy-limits-cta',
	)
);
?>
<div class="frm-dashboard-widget frm-card-item frm-px-0 frm-p-0">
	<div class="frm-pro-features-list">
		<div class="frm-pro-features-list-left">
			<h2><?php esc_html_e( 'Unlock all the Powerful Features to Defy the Limits', 'formidable' ); ?></h2>
			<ul class="frm_two_col frm-green-icons">
				<?php foreach ( $features as $feature ) : ?>
					<li>
						<?php FrmAppHelper::icon_by_class( 'frmfont frm_checkmark_icon' ); ?>
						<?php echo esc_html( $feature ); ?>
					</li>
				<?php endforeach; ?>
			</ul>
			<a target="_blank" href="<?php echo esc_url( $discount_link ); ?>" title="Upgrade" class="frm-button-primary frm-gradient">
				<?php esc_html_e( 'Upgrade to Pro & Get 50% Off', 'formidable' ); ?>
			</a>
		</div>

		<div class="frm-pro-features-list-right">
			<div class="frm-testimonial-wrapper">
				<div class="frm-testimonial">
					<div class="frm-testimonial__content">Amazing plugin, amazing support.  We've been using FF since 2016. The best form plugin on WP. Its powerful and versatile with an amazing support!</div>
					<div class="frm-testimonial__author">Emmanuel Khoury</div>
					<div class="frm-testimonial__rating">
						<?php FrmAddonsHelper::show_five_star_rating( '#FFD966' ); ?>
					</div>
					<div class="frm-testimonial__guarantee-icon">
						<?php FrmAddonsHelper::guarantee_icon(); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
