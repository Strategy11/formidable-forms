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
					<?php esc_html_e( 'Style Name', 'formidable' ); ?>
					<?php if ( $style->ID ) { ?>
						<span class="howto alignright">
							<span>.frm_style_<?php echo esc_attr( $style->post_name ); ?></span>
						</span>
					<?php } ?>
				</label>
				<input id="menu-name" name="<?php echo esc_attr( $frm_style->get_field_name( 'post_title', '' ) ); ?>" type="text" class="frm_full" title="<?php esc_attr_e( 'Enter style name here', 'formidable' ); ?>" value="<?php echo esc_attr( $style->post_title ); ?>" />
			</p>

			<p>
				<label class="default-style-box" for="menu_order">
					<?php if ( $style->menu_order ) { ?>
						<input name="<?php echo esc_attr( $frm_style->get_field_name( 'menu_order', '' ) ); ?>" type="hidden" value="1" />
						<input id="menu_order" disabled="disabled" type="checkbox" value="1" <?php checked( $style->menu_order, 1 ); ?> />
					<?php } else { ?>
						<input id="menu_order" name="<?php echo esc_attr( $frm_style->get_field_name( 'menu_order', '' ) ); ?>" type="checkbox" value="1" <?php checked( $style->menu_order, 1 ); ?> />
					<?php } ?>
					<?php esc_html_e( 'Make this the default style', 'formidable' ); ?></span>
				</label>
			</p>

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
