<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
// This view is used on the style page to render a single style card.
// It is used for both custom style cards and card templates.
// This includes a basic preview (text field and submit button only).
// It also includes the title of the style and possibly some basic tags if "selected" or "default".

$is_template  = 0 === $style->ID;
$include_info = $is_active_style || $is_template;
?>
<div <?php FrmAppHelper::array_to_html_params( $params, true ); ?>>
	<div class="frm-style-card-title-wrapper">
		<?php
		if ( $is_active_style ) {
			FrmAppHelper::icon_by_class( 'frmfont frm_checkmark_circle_icon' );
		}
		if ( ! empty( $is_locked ) ) {
			FrmAppHelper::icon_by_class( 'frmfont frm_lock_simple' );
		}
		?>
		<?php // The rename option uses the text content of .frm-style-card-title so don't leave any additional whitespace here. ?>
		<span class="frm-style-card-title"><?php echo esc_html( $style->post_title ); ?></span>
		<?php if ( $include_info ) { ?>
			<span class="frm-style-card-info">
				<?php
				$info_text = $is_active_style ? __( 'Applied', 'formidable' ) : __( 'Preview', 'formidable' );
				echo '(' . esc_html( $info_text ) . ')';
				?>
			</span>
		<?php } ?>
	</div>
	<div class="frm-style-card-preview">
		<?php
		/**
		 * This is used in Pro to include the default/selected tags.
		 *
		 * @since x.x
		 *
		 * @param array $args {
		 *     @type WP_Post $style
		 *     @type bool    $is_default_style
		 *     @type bool    $is_active_style
		 * }
		 */
		do_action( 'frm_style_card_after_submit', compact( 'style', 'is_default_style', 'is_active_style' ) );

		$color1 = $style->post_content['label_color'];
		$color2 = $style->post_content['text_color'];
		$color3 = $style->post_content['submit_bg_color'];

		if ( 0 !== strpos( $color1, 'rgb' ) ) {
			$color1 = '#' . $color1;
		}
		if ( 0 !== strpos( $color2, 'rgb' ) ) {
			$color2 = '#' . $color2;
		}
		if ( 0 !== strpos( $color3, 'rgb' ) ) {
			$color3 = '#' . $color3;
		}
		?>
		<div class="circle1" style="background-color: <?php echo esc_attr( $color1 ); ?>"></div>
		<div class="circle2" style="background-color: <?php echo esc_attr( $color2 ); ?>"></div>
		<div class="circle3" style="background-color: <?php echo esc_attr( $color3 ); ?>"></div>
	</div>
</div>
