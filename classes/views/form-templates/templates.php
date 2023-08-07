<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>

<ul id="frm-form-templates-featured-list" class="frm-form-templates-list frm_grid_container">
	<?php
	// Featured templates key includes:
	// "Contact Us", "User Registration", "Create WordPress Post", "Credit Card Payment", "Survey", and "Quiz".
	$featured_templates = array( 20872734, 20874748, 20882522, 20874739, 20908981, 28109851 );

	// Define additional variables.
	$render_icon = true;

	foreach ( $featured_templates as $template ) {
		if ( isset( $all_templates[ $template ] ) ) {
			$template = $all_templates[ $template ];
			require $view_path . 'template.php';
		}
	}
	?>
</ul><!-- #frm-form-templates-featured-list -->
