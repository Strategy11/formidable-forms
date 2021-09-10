<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<a href="<?php echo esc_attr( FrmAppHelper::admin_upgrade_link( 'landing-page' ) ); ?>" target="_blank">
	<?php esc_html_e( 'Generate Form Page', 'formidable' ); ?><span class="frm-new-pill"><?php esc_html_e( 'NEW', 'formidable' ); ?></span>
</a>
