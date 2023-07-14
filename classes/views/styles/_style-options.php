<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<h2>
	<a href="<?php echo esc_url( FrmStylesHelper::get_list_url( $form->id ) ); ?>" tabindex="0" role="button" title="<?php esc_attr_e( 'Back', 'formidable' ); ?>">
		<svg class="frmsvg">
			<use xlink:href="#frm_back"></use>
		</svg>
	</a>
	<span id="frm_style_name"><?php echo esc_html( $style->post_title ); ?></span>
</h2>
<div class="styling_settings">
	<?php FrmStylesController::do_accordion_sections( FrmStylesController::$screen, 'side', compact( 'style', 'frm_style' ) ); ?>
</div>
