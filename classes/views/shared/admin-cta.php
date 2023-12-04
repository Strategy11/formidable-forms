<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$attributes          = array();
$attributes['class'] = trim( 'frm-cta frm-flex frm-p-md ' . $class );

if ( $id ) {
	$attributes['id'] = $id;
}
?>

<div <?php FrmAppHelper::array_to_html_params( $attributes, true ); ?>>
	<div>
		<h4>
			<?php echo FrmAppHelper::kses( $title, array( 'a', 'br', 'span', 'p', 'svg', 'use' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</h4>

		<p class="frm-mb-0">
			<?php echo FrmAppHelper::kses( $description, array( 'a', 'br', 'span', 'p', 'svg', 'use' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</p>
	</div>

	<a href="<?php echo esc_url( $link_url ); ?>" target="_blank" class="frm-cta-link button button-primary frm-button-primary">
		<?php echo esc_html( $link_text ); ?>
	</a>
</div>
