<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-counter-card-wrapper frm-payments-placeholder frm-full-width">
	<div class="frm-dashboard-widget frm-counter-card frm-animate">
		<h4><?php echo esc_html( $template['counters'][0]['heading'] ); ?></h4>
		<div class="frm-flex-box frm-justify-between">
			<p><?php echo esc_html( $template['placeholder']['copy'] ); ?></p>
			<a href="<?php esc_url( $template['placeholder']['cta']['link'] ); ?>" class=" <?php echo isset( $template['placeholder']['cta']['classname'] ) ? esc_attr( $template['placeholder']['cta']['classname'] ) : ''; ?>"><?php echo esc_html( $template['placeholder']['cta']['label'] ); ?></a>
		</div>
	</div>
</div>

