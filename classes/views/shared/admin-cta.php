<?php
/**
 * Admin CTA banner view.
 *
 * @package Formidable
 *
 * @var array $attributes HTML attributes for the wrapper element.
 * @var array $args       Arguments used in FrmTipsHelper::show_admin_cta.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
if ( false === strpos( $attributes['class'], 'frm-gradient' ) ) {
	$button_class = 'frm-button-primary';
} else {
	$button_class = 'frm-button-secondary';
}
?>
<div <?php FrmAppHelper::array_to_html_params( $attributes, true ); ?>>
	<?php
	if ( ! empty( $args['icon'] ) ) {
		?>
		<div class="frm-cta-icon">
			<?php FrmAppHelper::icon_by_class( $args['icon'] ); ?>
		</div>
		<?php
	}
	?>
	<div class="frm-cta-content">
		<h4>
			<?php echo FrmAppHelper::kses( $args['title'], array( 'a', 'br', 'span', 'p', 'svg', 'use' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</h4>

		<p class="frm-m-0">
			<?php echo FrmAppHelper::kses( $args['description'], array( 'a', 'br', 'span', 'p', 'svg', 'use' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</p>
	</div>

	<a href="<?php echo esc_url( $args['link_url'] ); ?>" target="<?php echo esc_attr( $args['target'] ); ?>" class="frm-cta-link button button-primary <?php echo esc_attr( $button_class ); ?>">
		<?php echo esc_html( $args['link_text'] ); ?>
	</a>
</div>
