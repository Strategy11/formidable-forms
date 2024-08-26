<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-counter-card-wrapper frm-payments-placeholder">
	<div class="frm-dashboard-widget frm-card-item frm-counter-card frm-animate">
		<div class="frm-flex-box frm-justify-between frm-items-center">
			<div>
				<h3><?php echo esc_html( $template['counters'][0]['heading'] ); ?></h3>
				<p><?php echo esc_html( $template['placeholder']['copy'] ); ?></p>
			</div>
			<a href="<?php echo esc_url( $template['placeholder']['cta']['link'] ); ?>" class=" <?php echo isset( $template['placeholder']['cta']['classname'] ) ? esc_attr( $template['placeholder']['cta']['classname'] ) : ''; ?>"><?php echo esc_html( $template['placeholder']['cta']['label'] ); ?></a>
		</div>
	</div>
</div>

