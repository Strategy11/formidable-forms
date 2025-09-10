<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<button <?php FrmAppHelper::array_to_html_params( $attributes, true ); ?>>
	<?php FrmAppHelper::icon_by_class( 'frmfont frm-ai-form-icon frm_svg15', array( 'aria-label' => __( 'Generate options with AI', 'formidable' ) ) ); ?>
	<span><?php esc_html_e( 'Generate with AI', 'formidable' ); ?></span>
</button>
