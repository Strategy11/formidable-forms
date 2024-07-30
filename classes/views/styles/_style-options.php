<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<h2>
	<a href="<?php echo esc_url( FrmStylesHelper::get_style_options_back_button_args( $style, $form->id )['url'] ); ?>" tabindex="0" role="button" title="<?php esc_attr_e( 'Back', 'formidable' ); ?>">
		<svg class="frmsvg">
			<use xlink:href="#frm_back"></use>
		</svg>
	</a>
	<span id="frm_style_name"><?php echo esc_html( FrmStylesHelper::get_style_options_back_button_args( $style, $form->id )['title'] ); ?></span>
</h2>


<div class="styling_settings <?php echo FrmStylesHelper::is_quick_settings() ? 'frm_hidden' : ''; ?>">
	<?php FrmStylesController::do_accordion_sections( FrmStylesController::$screen, 'side', compact( 'style', 'frm_style' ) ); ?>
</div>

<?php if ( FrmStylesHelper::is_quick_settings() ) : ?>
	<div class="frm-quick-settings frm_grid_container">
		<?php FrmStylesController::get_quick_settings_template( $frm_style, $style, $form->id ); ?>
	</div>
<?php endif; ?>