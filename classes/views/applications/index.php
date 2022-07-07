<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
FrmAppHelper::include_svg();
FrmAppHelper::print_admin_banner( true );
?>
<div class="frm_page_container frm_wrap">
	<?php self::render_applications_header( __( 'Applications', 'formidable' ), 'index' ); ?>
	<div id="frm_applications_container"></div>
</div>
