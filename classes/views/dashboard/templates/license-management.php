<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-dashboard-license-management">
	<h3><?php echo esc_attr( $template['heading'] ); ?></h3>
	<span><?php echo esc_html( $template['copy'] ); ?></span>
	<?php if ( ! empty( $template['buttons'] ) ) : ?>
		<div class="frm-flex-box">
			<?php foreach ( $template['buttons'] as $button ) : ?>
				<?php
					$extra_classname       = 'open-license-modal' === $button['action'] ? 'frm-dashboard-open-license-modal' : '';
					$button_type_classname = 'primary' === $button['type'] ? 'frm-button-primary' : 'frm-button-secondary';
				?>
				<a href="<?php echo esc_url( $button['link'] ); ?>" class="<?php echo esc_attr( $extra_classname ) . ' ' . esc_attr( $button_type_classname ); ?>">
					<?php echo esc_attr( $button['label'] ); ?>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
