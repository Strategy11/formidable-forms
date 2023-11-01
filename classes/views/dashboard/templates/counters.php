<?php
$classname = 'frm-counter-card-wrapper frm-flex-box frm-justify-between';
if ( isset( $template['template-type'] ) && 'full-width' === $template['template-type'] ) {
	$classname .= ' frm-full-width';
}
?>

<?php if ( ! empty( $template ) && isset( $template['counters'] ) && is_array( $template['counters'] ) ) : ?>
	<div class="<?php echo esc_attr( $classname ); ?>">

		<?php foreach ( $template['counters'] as $counter ) : ?>
			<?php $counter_status = isset( $counter['disabled'] ) && true === $counter['disabled'] ? 'frm-disabled frm-has-tooltip' : ''; ?>
			<?php $tooltip = isset( $counter['tooltip'] ) ? $counter['tooltip'] : ''; ?>
			<div title="<?php echo esc_attr( $tooltip ); ?>" class="frm-counter-card frm-dashboard-widget frm-animate <?php echo esc_attr( $counter_status ); ?>">
				<h4><?php echo esc_html( $counter['heading'] ); ?></h4>
				<b>
					<?php echo isset( $counter['counter_label'] ) ? esc_html( $counter['counter_label'] ) : ''; ?><span class="frm-counter" data-type="<?php echo esc_attr( $counter['type'] ); ?>" data-counter="<?php echo (int) $counter['counter']; ?>"><?php echo (int) $counter['counter']; ?></span>
				</b>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
