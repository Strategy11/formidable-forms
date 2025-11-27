<?php
/**
 * Welcome Tour's main view file.
 *
 * @since 6.25.1
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
