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
	<h2><?php esc_html_e( 'Testing Mode Controls', 'formidable' ); ?></h2>
	<div>
		<label>
			<?php
			FrmHtmlHelper::toggle(
				'frm_testmode_disable_required_fields',
				'frm_testmode[disable_required_fields]',
				array(
					'echo'        => true,
					'off_label'   => __( 'Disable Required Fields', 'formidable' ),
					'show_labels' => true,
				)
			);
			?>
		</label>
	</div>
</div>
