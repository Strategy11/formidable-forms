<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
// This is the view for the "style page" where you can assign a style to a form and view the list of style templates.
// It is accessed from /wp-admin/admin.php?page=formidable&frm_action=style&id=782

/*
TODO:
- Left sidebar with list of styles.
  - My Styles includes custom styles.
      - With a "New style" option.
  - Formidable Styles includes templates from an API.
      - TODO write unit tests for the API.
- The right side body shows a preview (of the target form) so you can see the form you're actually styling.
  - There is a floating button here that links to the Style editor page.
*/

$style_api = new FrmStyleApi();
$info      = $style_api->get_api_info();

foreach ( $info as $key => $style ) {
	if ( ! is_numeric( $key ) ) {
		// Skip active_sub/expires keys.
		continue;
	}
	?>
	<div>
		<?php echo $style['name']; ?>
	</div>
	<?php
}
