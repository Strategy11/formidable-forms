<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<button <?php FrmAppHelper::array_to_html_params( $attributes, true ); ?>>
	<?php FrmAppHelper::icon_by_class( 'frmfont frm-ai-form-icon frm_svg15', array( 'aria-label' => __( 'Generate options with AI', 'formidable' ) ) ); ?>
	<span><?php echo isset( $args['button_text'] ) ? esc_html( $args['button_text'] ) : esc_html__( 'Generate with AI', 'formidable' ); ?></span>
	<?php if ( isset( $args['show_pill'] ) ) : ?>
		<?php FrmAppHelper::show_pill_text( __( 'BETA', 'formidable' ) ); ?>
	<?php endif; ?>
</button>
