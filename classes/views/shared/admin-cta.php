<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>

<div class="frm-cta">
	<div class="frm-cta-content">
		<h4 class="frm-cta-title">
			<?php echo self::kses( $title, array( 'a', 'br', 'span', 'p', 'svg', 'use' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</h4><!-- .frm-cta-title -->

		<span class="frm-cta-text">
			<?php echo self::kses( $description, array( 'a', 'br', 'span', 'p', 'svg', 'use' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</span><!-- .frm-cta-text -->
	</div><!-- .frm-cta-content -->

	<a class="frm-cta-link button button-primary frm-button-primary" href="<?php echo esc_url( $link_url ); ?>">
		<?php echo esc_html( $link_text ); ?>
	</a><!-- .frm-cta-link -->
</div><!-- .frm-cta -->
