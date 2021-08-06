<?php
/**
 * Images dropdown option view
 *
 * @since 4.12.0
 * @package Formidable
 *
 * @var array  $args         The arguments of images_dropdown() method.
 * @var array  $option       The option data.
 * @var string $image        The image HTML.
 * @var string $classes      The HTML classes.
 * @var string $custom_attrs The custom HTML attributes.
 */

?>
<button type="button" class="<?php echo esc_attr( $classes ); ?>" data-value="<?php echo esc_attr( $option['key'] ); ?>"<?php echo $custom_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $image ) : ?>
		<span class="frm_images_dropdown__image"><?php echo FrmAppHelper::kses( $image, 'all' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	<?php endif; ?>

	<?php if ( ! empty( $option['text'] ) ) : ?>
		<span class="frm_images_dropdown__text"><?php echo esc_html( $option['text'] ); ?></span>
	<?php endif; ?>
</button>
