<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
_deprecated_file( esc_html( basename( __FILE__ ) ), 'x.x' );
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
					<?php include FrmAppHelper::plugin_path() . '/classes/views/styles/_style-options.php'; ?>
				</div>
				<div id="post-body-content">
					<?php do_action( 'frm_style_switcher', $style, $styles ); ?>

					<div class="frm-inner-content">
						<?php include FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php'; ?>

						<input type="hidden" name="ID" value="<?php echo esc_attr( $style->ID ); ?>" />
						<input type="hidden" name="frm_action" value="save" />
						<textarea name="<?php echo esc_attr( $frm_style->get_field_name( 'custom_css' ) ); ?>" class="frm_hidden"><?php echo FrmAppHelper::esc_textarea( $style->post_content['custom_css'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></textarea>

						<?php
						wp_nonce_field( 'frm_style_nonce', 'frm_style' );
						FrmTipsHelper::pro_tip( 'get_styling_tip', 'p' );
						include dirname( __FILE__ ) . '/_sample_form.php';
						?>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>

<div id="this_css"></div>
