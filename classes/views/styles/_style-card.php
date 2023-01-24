<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
// This view is used on the style page to render a single style card.
// It is used for both custom style cards and card templates.
// This includes a basic preview (text field and submit button only).
// It also includes the title of the style and possibly some basic tags if "selected" or "default".
?>
<div <?php FrmAppHelper::array_to_html_params( $params, true ); ?>>
	<div class="frm-style-card-preview">
		<div class="frm_form_field form-field">
			<label class="frm_primary_label"><?php esc_html_e( 'Text field', 'formidable' ); ?></label>
			<input type="text" value="<?php esc_attr_e( 'This is sample text', 'formidable' ); ?>" />
		</div>
		<div class="frm_submit">
			<input <?php FrmAppHelper::array_to_html_params( $submit_button_params, true ); ?> />
		</div>
		<?php
		/**
		 * This is used in Pro to include the default/selected tags.
		 *
		 * @since x.x
		 *
		 * @param array $args {
		 *     @type WP_Post $style
		 *     @type bool    $is_default_style
		 *     @type bool    $is_active_style
		 * }
		 */
		do_action( 'frm_style_card_after_submit', compact( 'style', 'is_default_style', 'is_active_style' ) );
		?>
	</div>
	<div>
		<span class="frm-style-card-title">
			<?php
			if ( ! empty( $is_locked ) ) {
				FrmAppHelper::icon_by_class( 'frmfont frm_lock_solid_icon' );
			}
			echo esc_html( $style->post_title );
			?>
		</span>
	</div>
</div>
