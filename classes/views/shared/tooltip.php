<?php
/**
 * Tooltip view
 *
 * @since x.x
 *
 * @var array $atts Tooltip HTML attributes including title and class
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<span <?php FrmAppHelper::array_to_html_params( $atts, true ); ?>>
	<?php FrmAppHelper::icon_by_class( 'frmfont frm_tooltip_icon' ); ?>
</span>
