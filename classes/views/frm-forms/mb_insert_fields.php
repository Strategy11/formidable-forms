<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
if ( __CLASS__ === 'FrmFieldHTML' ) {
	if ( self::$included_adv_box ) {
		return;
	}
	
	self::$included_adv_box = true;
}

?>
<div id="frm_adv_info" class="postbox frm-dropdown-menu">
	<div class="inside">
		<?php FrmFormsController::mb_tags_box( $id ); ?>
	</div>
</div>

<style>
	/* .frm-type-html .frm-with-right-icon{
		position: absolute;
		z-index: 2;
		right: 0;
		width: 20px;
		top: 10px;
	} */
</style>
