<?php
/**
 * Onboarding Wizard - Footer.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-card-box-footer<?php echo $args['footer-class'] ? ' ' . esc_attr( $args['footer-class'] ) : ''; ?>">
	<?php if ( $args['display-back-button'] ) { ?>
		<a href="#" class="frm-onboarding-back-button frm-button-tertiary frm-mr-auto frm_large" role="button">
			<?php esc_html_e( 'Back', 'formidable' ); ?>
		</a>
	<?php } ?>

	<a <?php FrmAppHelper::array_to_html_params( $secondary_button_attributes, true ); ?>>
		<?php echo esc_html( $args['secondary-button-text'] ); ?>
	</a>

	<a <?php FrmAppHelper::array_to_html_params( $primary_button_attributes, true ); ?>>
		<?php
		echo esc_html( $args['primary-button-text'] );

		if ( $args['primary-button-with-icon'] ) {
			FrmAppHelper::icon_by_class( 'frmfont frm_arrowup6_icon frm_svg13 frm_inline_block frm-rotate-90 frm-ml-xs', array( 'aria-hidden' => 'true' ) );
		}
		?>
	</a>
</div>
