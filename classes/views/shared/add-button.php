<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<a href="<?php echo esc_attr( $href ); ?>" class="<?php echo esc_attr( $class ); ?>">
	<?php self::icon_by_class( 'frmfont frm_plus_icon frm_svg15' ); ?>
	<?php echo esc_html( $button_text ); ?>
</a>
