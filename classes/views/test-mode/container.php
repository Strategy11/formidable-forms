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
		FrmHtmlHelper::toggle(
			'frm_testmode_disable_required_fields',
			'frm_testmode[disable_required_fields]',
			array(
				'echo'        => true,
				'off_label'   => __( 'Disable Required Fields', 'formidable' ),
				'show_labels' => true,
				'disabled'    => ! $enabled,
			)
		);
		?>

		<?php
		$roles = get_editable_roles();
		if ( $roles ) :
			?>
			<label><?php esc_html_e( 'Preview as:', 'formidable' ); ?></label>
			<select id="frm_testmode_preview_role" <?php disabled( ! $enabled ); ?>>
				<?php
				foreach ( $roles as $role => $details ) :
					$role_name = $details['name'];
					?>
					<option value="<?php echo esc_attr( $role ); ?>">
						<?php echo esc_html( $role_name ); ?>
					</option>
				<?php
				endforeach;
				?>
			</select>
		<?php endif; ?>
	</div>
	<hr>
	<div>
		<label><?php esc_html_e( 'Quick jump to page:', 'formidable' ); ?></label>

		<?php
		$pagination = apply_filters( 'frm_test_mode_pagination_buttons', false );
		if ( false === $pagination ) {
			include FrmAppHelper::plugin_path() . '/classes/views/test-mode/pagination-buttons.php';
		}
		?>

		<a class="frm_button <?php echo $enabled ? '' : 'frm_noallow'; ?>" href="#">
			<?php esc_html_e( 'Fill in empty form fields', 'formidable' ); ?>
		</a>
	</div>
</div>
