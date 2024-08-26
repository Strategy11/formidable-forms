<?php
/**
 * Form Templates - Leave email modal.
 *
 * @package Formidable
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
	'title'       => esc_html__( 'Get 20+ Free Form Templates', 'formidable' ),
	'description' => esc_html__( 'Just add your email address and we\'ll send you a code for free form templates!', 'formidable' ),
);

$args = wp_parse_args( $args, $defaults );
?>
<div id="frm-leave-email-modal" class="frm_wrap frm-form-templates-modal-item frm_hidden">
	<div class="frm_modal_top">
		<div class="frm-modal-title">
			<h2><?php echo esc_html( $args['title'] ); ?></h2>
		</div>
	</div>

	<div class="inside frm_grid_container frm-fields frm-px-md frm-py-0 frm-mt-xs frm-mb-0">
		<div id="frmapi-email-form" class="frmapi-form frm_hidden" data-url="<?php echo esc_url( $args['api_url'] ); ?>">
			<span class="frm-wait"></span>
		</div>

		<?php
		echo wp_kses(
			wpautop( esc_html( $args['description'] ) ),
			array(
				'p'  => true,
				'br' => true,
			)
		);
		?>

		<div id="frm_leave_email_wrapper" class="frm-form-templates-modal-fieldset frm_form_field">
			<span class="frm-with-left-icon">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_email_icon' ); ?>
				<input id="frm_leave_email" type="email" placeholder="<?php esc_attr_e( 'Enter your email', 'formidable' ); ?>" value="<?php echo esc_attr( $user->user_email ); ?>" />
			</span>

			<span id="frm_leave_email_error" class="frm-validation-error frm-justify-center frm-items-center frm-mt-xs frm_hidden">
				<span frm-error="invalid"><?php esc_html_e( 'Email is invalid', 'formidable' ); ?></span>
				<span frm-error="empty"><?php esc_html_e( 'Email is empty', 'formidable' ); ?></span>
			</span>
		</div>
	</div>

	<div class="frm_modal_footer frm-flex-box frm-justify-end frm-pt-sm frm-pb-md">
		<a href="#" class="button button-secondary frm-button-secondary frm-modal-close dismiss" role="button">
			<?php esc_html_e( 'Close', 'formidable' ); ?>
		</a>
		<a href="#" id="frm-get-code-button" class="button button-primary frm-button-primary" role="button">
			<?php esc_html_e( 'Get Code', 'formidable' ); ?>
		</a>
	</div>
</div>
