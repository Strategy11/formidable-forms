<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-pro-features-list">
	<h2><?php esc_html_e( 'Unlock all the Powerful Features to Defy the Limits', 'formidable' ); ?></h2>
	<ul class="frm-flex-box">
		<li>
			<ul>
				<li><?php esc_html_e( 'Form Templates', 'formidable' ); ?></li>
				<li><?php esc_html_e( 'Calculated Fields and Math', 'formidable' ); ?></li>
				<li><?php esc_html_e( 'Quizzes', 'formidable' ); ?></li>
				<li><?php esc_html_e( 'Save and Continue', 'formidable' ); ?></li>
				<li><?php esc_html_e( 'Ecommerce pricing fields', 'formidable' ); ?></li>
			</ul>
		</li>
		<li>
			<ul>
				<li><?php esc_html_e( 'Customize Form HTML', 'formidable' ); ?></li>
				<li><?php esc_html_e( 'Smart Forms with Conditional Logic', 'formidable' ); ?></li>
				<li><?php esc_html_e( 'Schedule Forms & Limit Responses', 'formidable' ); ?></li>
				<li><?php esc_html_e( 'Display Form Data With Views', 'formidable' ); ?></li>
				<li><?php esc_html_e( 'And much more...', 'formidable' ); ?></li>
			</ul>
		</li>
	</ul>
	<a target="_blank" href="<?php echo esc_url( FrmAppHelper::admin_upgrade_link( 'dashboard-discount', 'lite-upgrade' ) ); ?>" title="Upgrade" class="frm-button-primary"><?php esc_html_e( 'Upgrade', 'formidable' ); ?></a>
</div>
