<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
// This view is used on the style page to render a single style card.
// It is used for both custom style cards and card templates.
// This includes a basic preview (text field and submit button only).
// It also includes the title of the style and possibly some basic tags if "selected" or "default".

$is_template  = 0 === $style->ID;
$include_info = $is_active_style;
?>
<div <?php FrmAppHelper::array_to_html_params( $params, true ); ?>>
	<div>
		<div class="frm-style-card-title-wrapper">
			<?php
			if ( $is_active_style ) {
				FrmAppHelper::icon_by_class( 'frmfont frm_checkmark_circle_icon' );
			}
			if ( ! empty( $is_locked ) ) {
				FrmAppHelper::icon_by_class( 'frmfont frm_lock_icon' );
			}
			?>
			<?php // The rename option uses the text content of .frm-style-card-title so don't leave any additional whitespace here. ?>
			<span class="frm-style-card-title"><?php echo esc_html( $style->post_title ); ?></span>
			<?php if ( $include_info ) { ?>
				<span class="frm-style-card-info">
					<?php
					$info_text = __( 'Applied', 'formidable' );
					echo '(' . esc_html( $info_text ) . ')';
					?>
				</span>
			<?php } ?>
			<?php if ( ! empty( $is_new_template ) ) { ?>
				<span class="frm-new-pill"><?php esc_html_e( 'NEW', 'formidable' ); ?></span>
			<?php } ?>
		</div>
		<div class="frm-style-card-preview">
			<div class="frm-color-blocks">
			<?php
			$colors = array(
				'label-color'     => $style->post_content['label_color'],
				'text-color'      => $style->post_content['text_color'],
				'submit-bg-color' => $style->post_content['submit_bg_color'],
			);
			$index  = 0;
			foreach ( $colors as $css_var_name => $color ) {
				if ( 0 !== strpos( $color, 'rgb' ) ) {
					$color = '#' . $color;
				}

				$circle_params = array(
					'class' => 'frm-style-circle' . absint( $index + 1 ),
					'style' => 'background-color: var(--' . $css_var_name . ')',
				);

				++$index;
				?>
				<div <?php FrmAppHelper::array_to_html_params( $circle_params, true ); ?>></div>
				<?php
			}
			?>
			</div>
			<div class="frm-style-card-separator"></div>
			<div class="frm-mini-form-style">
				<div class="frm-button-style-example">
					<div></div>
				</div>
				<div class="frm-input-style-example">
					<div></div>
				</div>
			</div>
		</div>
	</div>
</div>
