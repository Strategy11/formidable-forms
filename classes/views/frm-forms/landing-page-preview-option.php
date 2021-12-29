<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
$link   = FrmAddonsController::install_link( 'landing' );
$params = array(
	'class'       => 'frm_show_upgrade frm_noallow',
	'data-medium' => 'landing-preview',
);

if ( $link ) {
	$params['href']         = '#';
	$params['data-upgrade'] = __( 'Form Landing Pages', 'formidable' );
	if ( ! empty( $link['url'] ) ) {
		$params['data-oneclick'] = '{"url":"' . $link['url'] . '","class":"frm-activate-addon","status":"installed"}';
	}
} else {
	$params['href']         = FrmAppHelper::admin_upgrade_link( 'landing-preview' );
	$params['target']       = '_blank';
	$params['data-message'] = __( 'Easily manage a landing page for your form. Upgrade to get form landing pages.', 'formidable' );
}
?>
<a <?php FrmAppHelper::array_to_html_params( $params, true ); ?>>
	<?php esc_html_e( 'Generate Form Page', 'formidable' ); ?><span class="frm-new-pill"><?php esc_html_e( 'NEW', 'formidable' ); ?></span>
</a>
