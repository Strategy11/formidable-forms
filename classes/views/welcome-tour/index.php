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
<div id="frm-welcome-tour" class="frm-welcome-tour">
	<?php
	if ( ! empty( $spotlight ) ) {
		include $view_path . 'spotlight.php';
	}

	include $view_path . 'checklist.php';
	?>
</div>
