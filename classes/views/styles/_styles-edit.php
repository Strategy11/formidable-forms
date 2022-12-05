<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
// This is the view for the "style page" where you can assign a style to a form and view the list of style templates.
// It is accessed from /wp-admin/admin.php?page=formidable-styles&frm_action=edit&form_id=782
?>
<div id="frm_style_sidebar" class="frm-right-panel styling_settings">
	<form id="frm_styling_form" method="post" action="<?php echo esc_url( FrmStylesHelper::get_edit_url( $style, $form->id ) ); ?>">
		<input type="hidden" name="ID" value="<?php echo esc_attr( $style->ID ); ?>" />
		<input type="hidden" name="frm_action" value="save" />
		<?php wp_nonce_field( 'frm_style_nonce', 'frm_style' ); ?>

		<?php include $style_views_path . '_style-options.php'; ?>

		<div id="this_css"></div><?php // This holds the custom CSS for live updates to the preview. ?>
	</form>
</div>
