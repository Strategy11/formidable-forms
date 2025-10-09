<?php
/**
 * Welcome Tour's main view file.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-welcome-tour">
	<?php
	if ( ! empty( $spotlight ) ) {
		include $view_path . 'spotlight.php';
	}

	if ( $show_checklist ) {
		include $view_path . 'checklist.php';
	}
	?>
</div>
