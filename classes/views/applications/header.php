<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm_top_bar">
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable' ) ); ?>" class="frm-header-logo">
		<?php FrmAppHelper::show_header_logo(); ?> 
	</a>
	<div id="frm_bs_dropdown">
		<h1 style="width:auto;"><span><?php echo esc_html( $title ); ?></span></h1>
		<?php do_action( 'frm_applications_header_after_title', $context ); ?>
	</div>
	<div style="clear: both;"></div>
</div>
