<?php
/**
 * Form Templates - Leave email modal.
 *
 * @package   Strategy11/FormidableForms
 * @copyright 2010 Formidable Forms
 * @license   GNU General Public License, version 2
 * @link      https://formidableforms.com/
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

// Initialize $args if not set.
if ( ! isset( $args ) ) {
	$args = array();
}

$defaults = array(
	'api_url'     => 'https://sandbox.formidableforms.com/api/wp-json/frm/v2/forms/freetemplates?return=html&exclude_script=jquery&exclude_style=formidable-css',
	'title'       => esc_html__( 'Get 10+ Free Form Templates', 'formidable' ),
	'description' => esc_html__( 'Just add your email address and you\'ll get a code for 10+ free form templates.', 'formidable' ),
);

$args = wp_parse_args( $args, $defaults );
?>
<div id="frm-leave-email-modal" class="frm-form-templates-modal-item frm_hidden">
	<div class="frm_modal_top">
		<div class="frm-modal-title">
			<span class="frm-modal-title-text"><?php esc_html_e( 'Leave your email address', 'formidable' ); ?></span>
		</div>
	</div>

	<div class="inside">
		<div class="frmcenter">
			<div id="frmapi-email-form" class="frmapi-form frm_hidden" data-url="<?php echo esc_attr( $args['api_url'] ); ?>">
				<span class="frm-wait"></span>
			</div>

			<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/form-templates/leave-email.svg' ); ?>" />
			<h3><?php echo esc_html( $args['title'] ); ?></h3>
			<p>
				<?php
				echo wp_kses(
					wpautop( esc_html( $args['description'] ) ),
					array(
						'p' => true,
						'br' => true,
					)
				);
				?>
			</p>

			<div id="frm_leave_email_wrapper" class="frm-form-templates-modal-fieldset">
				<span class="frm-with-left-icon">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm_email_icon' ); ?>
					<input id="frm_leave_email" type="email" placeholder="<?php esc_html_e( 'Enter your email', 'formidable' ); ?>" value="<?php echo esc_attr( $user->user_email ); ?>" />
				</span>

				<span id="frm_leave_email_error" class="frm-form-templates-modal-error frm_hidden">
					<span frm-error="invalid"><?php esc_html_e( 'Email is invalid', 'formidable' ); ?></span>
					<span frm-error="empty"><?php esc_html_e( 'Email is empty', 'formidable' ); ?></span>
				</span>
			</div>
		</div>
	</div>

	<div class="frm_modal_footer">
		<a href="#" class="button button-secondary frm-button-secondary frm-modal-close dismiss" role="button">
			<?php esc_html_e( 'Close', 'formidable' ); ?>
		</a>
		<a href="#" id="frm-get-code-button" class="button button-primary frm-button-primary" role="button">
			<?php esc_html_e( 'Get Code', 'formidable' ); ?>
		</a>
	</div>
</div>
