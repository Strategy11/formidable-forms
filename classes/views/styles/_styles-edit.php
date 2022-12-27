<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
// This partial view is used in the visual styler sidebar in "edit" view.
// It renders the sidebar when editing a single style.
// It is accessed from /wp-admin/themes.php?page=formidable-styles&frm_action=edit&form=782
?>
<div id="frm_style_sidebar" class="frm-right-panel frm-fields frm_wrap">
	<form id="frm_styling_form" method="post" action="<?php echo esc_url( FrmStylesHelper::get_edit_url( $style, $form->id ) ); ?>">
		<input type="hidden" name="ID" value="<?php echo esc_attr( $style->ID ); ?>" />
		<input type="hidden" name="frm_action" value="save" />

		<?php
		wp_nonce_field( 'frm_style_nonce', 'frm_style' );

		$frm_style = new FrmStyle( $style->ID );
		include $style_views_path . '_style-options.php';
		?>

		<?php
		// Custom CSS is no longer used from the default style, but it is still checked if the Glboal Setting is missing.
		// Include the field so we do not load the old value in case Custom CSS has not been saved as a Global Setting yet.
		?>
		<textarea name="<?php echo esc_attr( $frm_style->get_field_name( 'custom_css' ) ); ?>" class="frm_hidden"><?php echo FrmAppHelper::esc_textarea( $style->post_content['custom_css'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></textarea>
	</form>
</div>
<?php
// This holds the custom CSS for a single theme that is being worked on on the edit page.
// It gets populated with the frm_change_styling action.
?>
<div id="this_css"></div>
