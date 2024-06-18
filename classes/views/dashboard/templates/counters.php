<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$classname = 'frm-counter-card-wrapper frm-flex-box';
?>

<?php if ( ! empty( $template ) && isset( $template['counters'] ) && is_array( $template['counters'] ) ) : ?>
	<div class="<?php echo esc_attr( $classname ); ?>">

		<?php foreach ( $template['counters'] as $counter ) : ?>
			<div class="frm-counter-card frm-dashboard-widget frm-card-item">
				<h4><?php echo esc_html( $counter['heading'] ); ?></h4>
				<?php if ( isset( $counter['cta'] ) && isset( $counter['cta']['display'] ) && true === $counter['cta']['display'] ) : ?>
					<a href="<?php echo esc_url( $counter['cta']['link'] ); ?>"><?php echo esc_html( $counter['cta']['title'] ); ?></a>
				<?php else : ?>
					<?php if ( 'currency' === $counter['type'] ) : ?>
						<div class="frm-flex-box frm-gap-md">
							<?php foreach ( $counter['items'] as $item ) : ?>
								<b>
									<?php echo esc_attr( $item['counter_label']['symbol_left'] ); ?>
									<span class="frm-counter" data-type="<?php echo esc_attr( $counter['type'] ); ?>" data-locale="<?php echo esc_attr( get_locale() ); ?>" data-counter="<?php echo (int) $item['counter']; ?>"><?php echo (int) $item['counter']; ?></span>
									<?php echo esc_attr( $item['counter_label']['symbol_right'] ); ?>
								</b>
							<?php endforeach; ?>
						</div>
					<?php else : ?>
						<b>
							<span class="frm-counter" data-type="<?php echo esc_attr( $counter['type'] ); ?>" data-locale="<?php echo esc_attr( get_locale() ); ?>" data-counter="<?php echo (int) $counter['counter']; ?>"><?php echo (int) $counter['counter']; ?></span>
						</b>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
