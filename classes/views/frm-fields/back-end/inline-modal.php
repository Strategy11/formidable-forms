<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-inline-modal postbox <?php echo esc_attr( $args['class'] . ( $args['show'] ? '' : ' frm_hidden' ) ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>">
	<a href="#" class="dismiss alignright" title="<?php esc_attr_e( 'Close', 'formidable' ); ?>">
		<?php FrmAppHelper::icon_by_class( 'frmfont frm_close_icon', array( 'aria-label' => __( 'Close', 'formidable' ) ) ); ?>
	</a>
	<ul class="frm-nav-tabs">
		<li class="frm-tabs">
			<a href="#">
				<?php echo esc_html( $args['title'] ); ?>
			</a>
		</li>
	</ul>
	<div class="inside">
		<?php call_user_func( $args['callback'], $args['args'] ); ?>
	</div>
</div>
