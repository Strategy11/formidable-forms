<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<?php
$multiple_styles_title   = __( 'You are currently limited to 1 style template', 'formidable' );
$multiple_styles_message = __( 'Upgrade to create and manage as many form styles as you need.', 'formidable' );
?>
<a href="#" class="button button-secondary frm-button-secondary frm-with-plus frm_noallow" data-upgrade="<?php echo esc_attr( $multiple_styles_title ); ?>" data-message="<?php echo esc_attr( $multiple_styles_message ); ?>" data-image="styles-upsell.svg">
	<?php FrmAppHelper::icon_by_class( 'frmfont frm_plus_icon frm_svg15' ); ?>
	<?php esc_html_e( 'New Style', 'formidable' ); ?>
</a>
<a href="#" class="button button-secondary frm-button-secondary alignright frm_noallow" data-upgrade="<?php echo esc_attr( $multiple_styles_title ); ?>" data-message="<?php echo esc_attr( $multiple_styles_message ); ?>" data-image="styles-upsell.svg">
	<?php esc_html_e( 'Duplicate', 'formidable' ); ?>
</a>
