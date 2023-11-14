<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frmcenter">
	<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/upgrade-rocket.svg' ); ?>" />
	<h3><?php esc_html_e( 'Get access to all powerful features', 'formidable' ); ?></h3>
</div>
<div id="frm-upgrade-body-list-wrapper">
	<div>
		<ul>
			<li><?php esc_html_e( 'Conditional logic', 'formidable' ); ?></li>
			<li><?php esc_html_e( 'Calculations', 'formidable' ); ?></li>
			<li><?php esc_html_e( 'User registration', 'formidable' ); ?></li>
			<li><?php esc_html_e( 'Advanced templates', 'formidable' ); ?></li>
		</ul>
	</div><div>
		<ul>
			<li><?php esc_html_e( 'Formidable Views', 'formidable' ); ?></li>
			<li><?php esc_html_e( 'File uploads', 'formidable' ); ?></li>
			<li><?php esc_html_e( 'Multi-page forms', 'formidable' ); ?></li>
			<li><?php esc_html_e( 'Review before submit', 'formidable' ); ?></li>
		</ul>
	</div><div>
		<ul>
			<li><?php esc_html_e( 'Mailchimp integration', 'formidable' ); ?></li>
			<li><?php esc_html_e( 'Repeater fields', 'formidable' ); ?></li>
			<li><?php esc_html_e( 'Post submission', 'formidable' ); ?></li>
			<li><?php esc_html_e( 'Front-end editing', 'formidable' ); ?></li>
		</ul>
	</div>
</div>
