<?php
/**
 * Images dropdown option view
 *
 * @since 5.0.04
 * @package Formidable
 *
 * @var array  $args         The arguments of images_dropdown() method.
 * @var array  $option       The option data.
 * @var string $image        The image HTML.
 * @var string $classes      The HTML classes.
 * @var string $custom_attrs The custom HTML attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm_images_dropdown frm_grid_container <?php echo esc_attr( $args['classes'] ); ?>">
	<?php
	foreach ( $args['options'] as $key => $option ) {
		$option['key'] = $key;

		$image        = self::get_images_dropdown_option_image( $option, $args );
		$classes      = self::get_images_dropdown_option_classes( $option, $args );
		$custom_attrs = self::get_images_dropdown_option_html_attrs( $option, $args );
		?>
		<div class="frm_radio frm_image_option frm<?php echo esc_attr( $args['col_class'] ); ?>">
			<label class="<?php echo esc_attr( $classes ); ?>" data-value="<?php echo esc_attr( $option['key'] ); ?>"<?php echo $custom_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<input value="<?php echo esc_attr( $option['key'] ); ?>" <?php checked( $option['key'], $args['selected'] ); ?> <?php echo $input_attrs_str; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> />
				<div class="frm_image_option_container frm_grid_container">
					<span class="frm_images_dropdown__image frm4">
						<?php echo FrmAppHelper::kses( $image, 'all' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</span>
					<span class="frm_text_label_for_image frm8">
						<?php echo esc_html( $option['text'] ); ?>
					</span>
				</div>
			</label>
		</div>
		<?php
	}
	?>
</div>
