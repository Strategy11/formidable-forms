<?php
/**
 * Solutions app install form view
 *
 * @since 4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
/**
 * @var array $step Step information including button_class, button_label, and nested flag
 * @var string $xml  XML package URL for the template
 */
?>
				<input type="hidden" name="link" id="frm_link" value="<?php echo esc_attr( $xml ); ?>" />
				<input type="hidden" name="type" id="frm_action_type" value="frm_install_template" />
				<input type="hidden" name="template_name" id="frm_template_name" value="" />
				<input type="hidden" name="template_desc" id="frm_template_desc" value="" />
				<input type="hidden" name="redirect" value="0" />
				<input type="hidden" name="show_response" value="frm_install_error" />
				<?php
				$this->show_form_options( $xml );
				$this->show_view_options();

				if ( ! $this->is_complete( 'all' ) ) {
					// Don't show on the settings page when complete.
					$this->show_page_options();
				}
				?>
				<p>
					<button <?php echo esc_html( isset( $step['nested'] ) ? '' : 'type="submit" ' ); ?>class="<?php echo esc_attr( $step['button_class'] ); ?>">
						<?php echo esc_html( $step['button_label'] ); ?>
					</button>
				</p>
				<p id="frm_install_error" class="frm_error_style frm_hidden"></p>
