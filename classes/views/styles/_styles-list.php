<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
// This is the view for the "style page" where you can assign a style to a form and view the list of style templates.
// It is accessed from /wp-admin/themes.php?page=formidable-styles&form=782
?>
<div id="frm_style_sidebar">
	<?php
	/**
	 * Pro needs to hook in here to add the "New Style" trigger.
	 *
	 * @since x.x
	 *
	 * @param array $args {
	 *     @type stdClass $form
	 * }
	 */
	do_action( 'frm_style_sidebar_top', compact( 'form' ) );
	?>
	<form id="frm_style_form" method="post" action="<?php echo esc_url( admin_url( 'themes.php?page=formidable-styles&form=' . $form->id . '&t=advanced_settings' ) ); ?>">
		<input type="hidden" name="style_id" value="<?php echo absint( $active_style->ID ); ?>" />
		<input type="hidden" name="form_id" value="<?php echo absint( $form->id ); ?>" />
		<?php wp_nonce_field( 'frm_save_form_style_nonce', 'frm_save_form_style' ); ?>
	</form>
	<?php
	array_walk(
		$styles,
		function( $style ) use ( $style_views_path, $active_style, $default_style, $form ) {
			FrmStylesHelper::echo_style_card( $style, $style_views_path, $active_style, $default_style, $form->id );
		}
	);
	?>
</div>
