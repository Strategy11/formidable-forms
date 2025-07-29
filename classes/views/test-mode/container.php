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
		<?php
		FrmHtmlHelper::toggle( 'frm_testmode_disable_required_fields', 'frm_testmode[disable_required_fields]', $disabled_required_fields_toggle_args );

		if ( $roles ) :
			$selected_role = $enabled ? FrmAppHelper::simple_get( 'frm_testmode_role' ) : '';
			?>
			<label><?php esc_html_e( 'Preview as:', 'formidable' ); ?></label>
			<select id="frm_testmode_preview_role" <?php disabled( ! $enabled ); ?>>
				<?php
				foreach ( $roles as $role => $details ) {
					FrmHtmlHelper::echo_dropdown_option(
						$details['name'],
						$selected_role === $role,
						array(
							'value' => $role,
						)
					);
				}
				?>
			</select>
		<?php endif; ?>
	</div>
	<hr>
	<div>
		<label><?php esc_html_e( 'Quick jump to page:', 'formidable' ); ?></label>

		<?php
		if ( false !== $pagination && is_callable( $pagination ) ) {
			$pagination();
		} elseif ( false === $pagination ) {
			include FrmAppHelper::plugin_path() . '/classes/views/test-mode/pagination-buttons.php';
		}
		?>

		<a class="frm_button <?php echo $enabled ? '' : 'frm_noallow'; ?>" href="#">
			<?php esc_html_e( 'Fill in empty form fields', 'formidable' ); ?>
		</a>
	</div>
</div>
