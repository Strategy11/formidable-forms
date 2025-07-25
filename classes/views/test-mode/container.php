<?php
/**
 * Test Mode Container.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm_testing_mode">
	<span>Test Mode Controls</span>
	<div>
		<label>
			Disable Required Fields
			<input type="checkbox" id="frm_testmode_disable_required_fields" name="frm_testmode[disable_required_fields]" value="1"/>
		</label>
	</div>
</div>
