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
		<div class="frm-payments-tab-filler"></div>	
	<?php endforeach; ?>
</div>
