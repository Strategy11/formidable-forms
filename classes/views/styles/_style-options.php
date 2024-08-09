<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
$back_button_args = FrmStylesHelper::get_style_options_back_button_args( $style, $form->id );
?>
<h2>
	<a	<?php echo ! empty( $back_button_args['id'] ) ? esc_attr( 'id=' . $back_button_args['id'] ) : ''; ?>
		<?php echo ! empty( $back_button_args['url'] ) ? esc_attr( 'href=' . $back_button_args['url'] ) : ''; ?>
		tabindex="0" role="button"
		title="<?php esc_attr_e( 'Back', 'formidable' ); ?>">
			<svg class="frmsvg">
				<use xlink:href="#frm_back"></use>
			</svg>
	</a>
	<span id="frm_style_name"><?php echo esc_html( $back_button_args['title'] ); ?></span>
</h2>


<div class="styling_settings <?php echo FrmStylesHelper::is_quick_settings() ? 'frm_hidden' : ''; ?>">
	<?php FrmStylesController::do_accordion_sections( FrmStylesController::$screen, 'side', compact( 'style', 'frm_style' ) ); ?>
</div>


<div class="frm-quick-settings frm_grid_container <?php echo ! FrmStylesHelper::is_quick_settings() ? 'frm_hidden' : ''; ?>">
	<?php FrmStylesController::get_quick_settings_template( $frm_style, $style, $form->id ); ?>
</div>