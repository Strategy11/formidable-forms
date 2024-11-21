<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
$back_button_args = FrmStylesHelper::get_style_options_back_button_args( $style, $form->id );
$back_button_attr = array_intersect_key( array_filter( $back_button_args ), array_flip( array( 'id', 'href' ) ) );
?>
<h2>
	<a <?php FrmAppHelper::array_to_html_params( $back_button_attr, true ); ?>
		tabindex="0" role="button"
		title="<?php esc_attr_e( 'Back', 'formidable' ); ?>">
			<svg class="frmsvg">
				<use xlink:href="#frm_back"></use>
			</svg>
	</a>
	<span id="frm_style_name"><?php echo esc_html( $back_button_args['title'] ); ?></span>
</h2>

<div class="styling_settings <?php echo esc_attr( FrmStylesHelper::style_editor_get_wrapper_classname( 'advanced-settings' ) ); ?>">
	<?php FrmStylesController::do_accordion_sections( FrmStylesController::$screen, 'side', compact( 'style', 'frm_style' ) ); ?>
</div>

<div class="frm-quick-settings frm_grid_container <?php echo esc_attr( FrmStylesHelper::style_editor_get_wrapper_classname( 'quick-settings' ) ); ?>">
	<?php require_once FrmAppHelper::plugin_path() . '/classes/views/styles/_quick-settings.php'; ?>
</div>