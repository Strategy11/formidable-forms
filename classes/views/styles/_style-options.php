<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-inner-content">
	<p>
		<?php if ( FrmAppHelper::simple_get( 'form', 'absint', 0 ) ) { ?>
			<a href="<?php echo esc_url( FrmStylesHelper::get_style_page_url( $form->id ) ); ?>" tabindex="0" role="button" title="<?php esc_attr_e( 'Back', 'formidable' ); ?>">
				<svg class="frmsvg">
					<use xlink:href="#frm_back"></use>
				</svg>
			</a>
		<?php } ?>
		<span id="frm_style_name"><?php echo esc_html( $style->post_title ); ?></span>
		<input name="<?php echo esc_attr( $frm_style->get_field_name( 'post_title', '' ) ); ?>" type="hidden" value="<?php echo esc_attr( $style->post_title ); ?>" /><?php // TODO: Remove this. Make sure removing it doesn't clear style titles though. ?>
	</p>

	<?php
	if ( ! class_exists( 'FrmProStylesController' ) ) {
		require dirname( __FILE__ ) . '/_upsell-multiple-styles.php';
	}
	?>
</div>
<div class="styling_settings">
	<?php FrmStylesController::do_accordion_sections( FrmStylesController::$screen, 'side', compact( 'style', 'frm_style' ) ); ?>
</div>
