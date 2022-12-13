<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
// This partial view is used in the visual styler sidebar in "edit" view.
// It renders the sidebar when editing a single style.
// It is accessed from /wp-admin/themes.php?page=formidable-styles&frm_action=edit&form=782
?>
<div id="frm_style_sidebar" class="frm-right-panel frm-fields">
	<form id="frm_styling_form" method="post" action="<?php echo esc_url( FrmStylesHelper::get_edit_url( $style, $form->id ) ); ?>">
		<input type="hidden" name="ID" value="<?php echo esc_attr( $style->ID ); ?>" />
		<input type="hidden" name="frm_action" value="save" />

		<?php
		wp_nonce_field( 'frm_style_nonce', 'frm_style' );

		$frm_style = new FrmStyle( $style->ID );
		include $style_views_path . '_style-options.php';
		?>

		<div id="this_css"></div><?php // This holds the custom CSS for live updates to the preview. ?>
	</form>
</div>
