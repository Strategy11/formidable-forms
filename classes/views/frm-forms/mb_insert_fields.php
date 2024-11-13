<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
$data_attrs = array( 'data-contextual-shortcodes' => FrmAppHelper::maybe_json_encode( FrmShortcodeHelper::get_contextual_codes() ) );
?>
<div id="frm_adv_info" class="postbox frm-dropdown-menu" <?php FrmAppHelper::array_to_html_params( $data_attrs, true ); ?>>
	<div class="inside">
		<?php FrmFormsController::mb_tags_box( $id ); ?>
	</div>
</div>
