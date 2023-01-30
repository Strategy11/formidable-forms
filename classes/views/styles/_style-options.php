<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-inner-content">
	<p>
		<?php if ( FrmAppHelper::simple_get( 'form', 'absint', 0 ) ) { ?>
			<a href="<?php echo esc_url( FrmStylesHelper::get_list_url( $form->id ) ); ?>" tabindex="0" role="button" title="<?php esc_attr_e( 'Back', 'formidable' ); ?>">
				<svg class="frmsvg">
					<use xlink:href="#frm_back"></use>
				</svg>
			</a>
		<?php } ?>
		<span id="frm_style_name" class="frm-text-lg"><?php echo esc_html( $style->post_title ); ?></span>
	</p>
</div>
<div class="styling_settings">
	<?php FrmStylesController::do_accordion_sections( FrmStylesController::$screen, 'side', compact( 'style', 'frm_style' ) ); ?>
</div>
