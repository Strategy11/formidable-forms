<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$tabs = array(
	'payments' => array(
		'url'   => admin_url( 'admin.php?page=formidable-payments' ),
		'label' => __( 'Payments', 'formidable' ),
	),
	'coupons'  => array(
		'url'   => admin_url( 'admin.php?page=formidable-payments&action=coupons' ),
		'label' => __( 'Coupons', 'formidable' ),
	),
);
?>

<div class="frm-payments-tabs">
	<div class="frm-payments-tab-filler"></div>
	<?php foreach ( $tabs as $tab_key => $details ) : ?>
		<?php
		$is_active = $tab_key === $active_tab;
		$classes   = 'frm-payments-tab';

		if ( $is_active ) {
			$classes .= ' frm-active';
		}
		?>
		<div class="<?php echo esc_attr( $classes ); ?>">
			<?php if ( $is_active ) : ?>
				<?php echo esc_html( $details['label'] ); ?>
			<?php else : ?>
				<a href="<?php echo esc_url( $details['url'] ); ?>">
					<?php echo esc_html( $details['label'] ); ?>
				</a>
			<?php endif; ?>
		</div>
		<?php
		$filler_params = array(
			'class' => 'frm-payments-tab-filler',
		);

		if ( 'coupons' === $tab_key ) {
			$filler_params['style'] = 'flex: 1;';
		}
		?>
		<div <?php FrmAppHelper::array_to_html_params( $filler_params, true ); ?>></div>
	<?php endforeach; ?>
	<div class="frm-payments-settings-button">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-settings&t=stripe_settings' ) ); ?>" class="button button-secondary frm-button">
			<?php FrmAppHelper::icon_by_class( 'frmfont frm_small_settings_icon' ); ?>
			<?php esc_html_e( 'Payment settings', 'formidable' ); ?>
		</a>
	</div>
</div>
