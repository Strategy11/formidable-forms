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
?>
<div <?php FrmAppHelper::array_to_html_params( $attributes, true ); ?>>
	<div>
		<h4>
			<?php echo FrmAppHelper::kses( $args['title'], array( 'a', 'br', 'span', 'p', 'svg', 'use' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</h4>

		<p class="frm-m-0">
			<?php echo FrmAppHelper::kses( $args['description'], array( 'a', 'br', 'span', 'p', 'svg', 'use' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</p>
	</div>

	<a href="<?php echo esc_url( $args['link_url'] ); ?>" target="_blank" class="frm-cta-link button button-primary frm-button-primary">
		<?php echo esc_html( $args['link_text'] ); ?>
	</a>
</div>
