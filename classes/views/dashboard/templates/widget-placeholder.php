<h2 class="frm-widget-heading"><?php echo esc_html( $template['placeholder']['widget-heading'] ); ?></h2>
<div class="frm-widget-placeholder" data-background="<?php echo esc_attr( $template['placeholder']['background'] ); ?>">
	<div>
		<h4><?php echo esc_html( $template['placeholder']['heading'] ); ?></h4>
		<p><?php echo wp_kses_post( $template['placeholder']['copy'] ); ?></p>
		<?php if ( null !== $template['placeholder']['button'] ) : ?>
			<a href="<?php echo esc_url( $template['placeholder']['button']['link'] ); ?>" class="frm-button-secondary"><?php echo esc_html( $template['placeholder']['button']['label'] ); ?></a>
		<?php endif; ?>
	</div>
</div>
