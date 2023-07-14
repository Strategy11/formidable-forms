<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
// This partial view is used in the visual styler sidebar in "edit" view.
// It renders the sidebar when editing a single style.
// It is accessed from /wp-admin/admin.php?page=formidable-styles&frm_action=edit&form=782
?>
<div id="frm_style_sidebar" class="frm-right-panel frm-fields frm_wrap frm-p-6">
	<form id="frm_styling_form" method="post" action="<?php echo esc_url( FrmStylesHelper::get_edit_url( $style, $form->id ) ); ?>">
		<input type="hidden" name="ID" value="<?php echo esc_attr( $style->ID ); ?>" />
		<input type="hidden" name="frm_action" value="save" />

		<?php if ( ! $style->ID ) { ?>
			<?php // Include a hidden input for new styles so the new style name updates. ?>
			<input name="<?php echo esc_attr( $frm_style->get_field_name( 'post_title', '' ) ); ?>" type="hidden" value="<?php echo esc_attr( $style->post_title ); ?>" />
		<?php } ?>

		<?php
		wp_nonce_field( 'frm_style_nonce', 'frm_style' );

		$frm_style = new FrmStyle( $style->ID );
		include $style_views_path . '_style-options.php';
		?>
	</form>

	<?php
	/**
	 * We need this style_name field for calls made from the changeStyling function.
	 * Without it, some styles (including background image opacity and repeater button icons) don't properly sync when updated.
	 * It isn't required for frm_styling_form so it's left out of the form.
	 * It is targetted in JavaScript by its #frm_style_sidebar parent though, so it's important that it's inside the sidebar.
	 */
	?>
	<input type="hidden" name="style_name" value="frm_style_<?php echo esc_attr( $style->post_name ); ?>" />

	<?php
	// This holds the custom CSS for a single theme that is being worked on on the edit page.
	// It gets populated with the frm_change_styling action.
	?>
	<div id="this_css"></div>
</div>
