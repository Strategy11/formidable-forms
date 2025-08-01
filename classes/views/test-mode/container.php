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
		FrmHtmlHelper::toggle( 'frm_testmode_show_all_hidden_fields', 'frm_testmode[show_all_hidden_fields]', $show_all_hidden_fields_toggle_args );

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

		<label><?php esc_html_e( 'Enabled form actions:', 'formidable' ); ?></label>
		<div id="frm_testmode_enabled_form_actions_container" class="frm-fields">
			<select id="frm_testmode_enabled_form_actions" multiple class="frm_multiselect" name="frm_testmode[enabled_form_actions][]" <?php disabled( ! $enabled ); ?>>
			<?php
			$form_actions         = FrmFormAction::get_action_for_form( $form_id );
			$enabled_form_actions = wp_list_pluck( $form_actions, 'ID' );
			if ( $enabled && ! empty( $_POST ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				if ( ! empty( $_POST['frm_testmode']['enabled_form_actions'] ) && is_array( $_POST['frm_testmode']['enabled_form_actions'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$enabled_form_actions = array_map( 'absint', $_POST['frm_testmode']['enabled_form_actions'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
				} else {
					$enabled_form_actions = array();
				}
			}
			foreach ( $form_actions as $form_action ) {
				?>
				<option value="<?php echo esc_attr( $form_action->ID ); ?>" <?php selected( in_array( $form_action->ID, $enabled_form_actions, true ), true ); ?>>
					<?php echo esc_html( $form_action->post_title ) . ' (' . absint( $form_action->ID ) . ')'; ?>
				</option>
				<?php
			}
			?>
			</select>
		</div>
		<script>
			( function() {
				jQuery( document ).ready(function() {
					frmDom.bootstrap.setupBootstrapDropdowns( function( frmDropdownMenu ) {
						const toggle = document.querySelector( '.dropdown-toggle' );
						if ( toggle ) {
							toggle.classList.add( 'frm-dropdown-toggle' );
							if ( ! toggle.hasAttribute( 'role' ) ) {
								toggle.setAttribute( 'role', 'button' );
							}
							if ( ! toggle.hasAttribute( 'tabindex' ) ) {
								toggle.setAttribute( 'tabindex', 0 );
							}
						}
					} );
					jQuery( '#frm_testmode_enabled_form_actions' ).hide().each( frmDom.bootstrap.multiselect.init );
				});
			}() );
		</script>
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

		<a id="frm_testmode_fill_in_empty_form_fields" class="frm_button <?php echo $enabled && $ai_enabled ? '' : 'frm_noallow'; ?>" href="#">
			<?php esc_html_e( 'Fill empty fields with AI', 'formidable' ); ?>
		</a>
	</div>
	<?php
	$start_over_button_attrs = array(
		'id'    => 'frm_testmode_start_over',
		'class' => 'frm_button frm_noallow',
		'href'  => '#',
	);
	$start_over_button_attrs = apply_filters( 'frm_testmode_start_over_button_attrs', $start_over_button_attrs );
	?>
	<a <?php FrmAppHelper::array_to_html_params( $start_over_button_attrs, true ); ?>>
		<img src="<?php echo esc_url( FrmAppHelper::plugin_url() ); ?>/images/reset.svg" alt="<?php esc_attr_e( 'Reset', 'formidable' ); ?>" />
		<?php esc_html_e( 'Reset', 'formidable' ); ?>
	</a>
</div>
