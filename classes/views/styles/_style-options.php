<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<input name="prev_menu_order" type="hidden" value="<?php echo esc_attr( $style->menu_order ); ?>" />
<input type="hidden" name="style_name" value="frm_style_<?php echo esc_attr( $style->post_name ); ?>" />
<div class="frm-inner-content">
	<p>
		<?php if ( FrmAppHelper::simple_get( 'form_id', 'absint', 0 ) ) { ?>
			<a href="<?php echo esc_url( FrmStylesHelper::get_style_page_url( FrmAppHelper::simple_get( 'form_id', 'absint', 0 ) ) ); ?>" tabindex="0" role="button" title="<?php esc_attr_e( 'Back', 'formidable' ); ?>">
				<svg class="frmsvg">
					<use xlink:href="#frm_back"></use>
				</svg>
			</a>
		<?php } ?>
		<span style="font-size: 16px;"><?php echo esc_html( $style->post_title ); ?></span>
		<?php if ( $style->ID ) { ?>
			<span class="howto alignright">
				<span>.frm_style_<?php echo esc_attr( $style->post_name ); ?></span>
			</span>
		<?php } ?>
		<input name="<?php echo esc_attr( $frm_style->get_field_name( 'post_title', '' ) ); ?>" type="hidden" value="<?php echo esc_attr( $style->post_title ); ?>" /><?php // TODO: Remove this. Make sure removing it doesn't clear style titles though. ?>
	</p>

	<?php // menu_order is 1 for the default style, 0 for other styles. This can probably get removed but for now the menu order value is a hidden field. ?>
	<input name="<?php echo esc_attr( $frm_style->get_field_name( 'menu_order', '' ) ); ?>" type="hidden" value="<?php echo esc_attr( $style->menu_order ); ?>" />

	<?php
	if ( ! class_exists( 'FrmProStylesController' ) ) {
		require dirname( __FILE__ ) . '/_upsell-multiple-styles.php';
	}

	/**
	 * @param WP_Post $style
	 */
	do_action( 'frm_style_settings_top', $style );
	?>
</div>
<?php FrmStylesController::do_accordion_sections( FrmStylesController::$screen, 'side', compact( 'style', 'frm_style' ) ); ?>
