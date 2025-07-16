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
<style>
	#frm_testing_mode {
		border-radius: 1rem;
		background-color: #fff;
		border-color: #0000001a;
		border-width: 1px;
		border-style: solid;
		padding: 16px 20px;
		margin-bottom: 40px;
	}

	#frm_testing_mode > span:first-child {
		background-color: rgb(250, 200, 182);
		border-radius: 200px;
		font-weight: 600;
		font-size: 12px;
		padding: 4px 8px;
	}

	#frm_testing_mode label {
		font-size: 12px;
		font-weight: 600;
	}

	#frm_testing_mode input[type="checkbox"] {
		margin-top: 3px;
		margin-left: 4px;
	}
</style>
<div id="frm_testing_mode">
	<span>Test Mode Controls</span>
	<div>
		<label>
			Disable Required Fields
			<input type="checkbox" id="frm_testmode_disable_required_fields" name="frm_testmode[disable_required_fields]" value="1"/>
		</label>
	</div>
</div>
