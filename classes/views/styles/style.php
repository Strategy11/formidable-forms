<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
// This is the view for the "style page" where you can assign a style to a form and view the list of style templates.
// It is accessed from /wp-admin/admin.php?page=formidable&frm_action=style&id=782

?>


<div class="frm_form_fields frm_sample_form frm_forms frm_pro_form">
<fieldset>

<div class="frm_fields_container">


<div class="frm_grid_container">
	<?php // Sidebar. ?>
	<div class="frm_grid_container frm5" style="background-color: #F6F7FB; padding: 10px; grid-gap: 5%; grid-template-columns: repeat( 12, 3.725%);">
		<?php
		// TODO add a title for My Styles.
		// TODO the design has a "New style" option here.
		   // TODO this will trigger a new modal.

		array_walk(
			$styles,
			function( $style ) use ( $style_views_path ) {
				include $style_views_path . '_custom-style-card.php';
			}
		);
		?>
	</div>
	<?php // Preview area. ?>
	<div class="frm7">
		<?php
		/**
		 * The right side body shows a preview (of the target form) so you can see the form you're actually styling.
		 * TODO: There is a floating button here that links to the Style editor page.
		 */
		?>
		Preview Area
		<?php // TODO: What's the best way to do this? Can I use an iframe? ?>
	</div>
</div>

</div>

</fieldset>
</div>
