<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm_wrap">
	<form id="frm_styling_form" action="" name="frm_styling_form" method="post">
	<div class="frm_page_container frm-fields">
	<?php
	FrmAppHelper::get_admin_header(
		array(
			'publish'     => array( 'FrmStylesHelper::styler_save_button', compact( 'style' ) ),
			'nav'         => FrmStylesHelper::get_style_menu(),
			'switcher'    => array( 'FrmStylesHelper::styler_switcher', compact( 'style', 'styles' ) ),
		)
	);
	?>

	<div class="columns-2">
	<div class="frm-right-panel styling_settings">
		<input name="prev_menu_order" type="hidden" value="<?php echo esc_attr( $style->menu_order ); ?>" />
		<input type="hidden" name="style_name" value="frm_style_<?php echo esc_attr( $style->post_name ); ?>" />
		<div class="frm-inner-content">
			<p>
				<label for="menu-name">
					<?php if ( FrmAppHelper::simple_get( 'form_id', 'absint', 0 ) ) { ?>
						<a href="<?php echo esc_url( FrmStylesHelper::get_style_page_url( FrmAppHelper::simple_get( 'form_id', 'absint', 0 ) ) ); ?>" tabindex="0" role="button" title="<?php esc_attr_e( 'Back', 'formidable' ); ?>">
							<svg class="frmsvg">
								<use xlink:href="#frm_back"></use>
							</svg>
						</a>
					<?php } ?>
					<?php esc_html_e( 'Style Name', 'formidable' ); ?>
					<?php if ( $style->ID ) { ?>
						<span class="howto alignright">
							<span>.frm_style_<?php echo esc_attr( $style->post_name ); ?></span>
						</span>
					<?php } ?>
				</label>
				<input id="menu-name" name="<?php echo esc_attr( $frm_style->get_field_name( 'post_title', '' ) ); ?>" type="text" class="frm_full" title="<?php esc_attr_e( 'Enter style name here', 'formidable' ); ?>" value="<?php echo esc_attr( $style->post_title ); ?>" />
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
	</div>
	<div id="post-body-content">
		<?php do_action( 'frm_style_switcher', $style, $styles ); ?>

		<div class="frm-inner-content">

			<?php include( FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php' ); ?>

			<input type="hidden" name="ID" value="<?php echo esc_attr( $style->ID ); ?>" />
			<input type="hidden" name="frm_action" value="save" />
			<textarea name="<?php echo esc_attr( $frm_style->get_field_name( 'custom_css' ) ); ?>" class="frm_hidden"><?php echo FrmAppHelper::esc_textarea( $style->post_content['custom_css'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></textarea>
			<?php wp_nonce_field( 'frm_style_nonce', 'frm_style' ); ?>
			<?php FrmTipsHelper::pro_tip( 'get_styling_tip', 'p' ); ?>

			<?php include( dirname( __FILE__ ) . '/_sample_form.php' ); ?>

		</div>
	</div>
</div>
</div>
</form>
</div>

<div id="this_css"></div>
