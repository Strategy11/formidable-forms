<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<dialog id="frm_error_modal" class="frm-modal frm_common_modal frm-dialog" aria-labelledby="frm_error_modal_title" aria-describedby="frm_error_modal_body">
	<div class="metabox-holder">
		<div class="postbox">
			<div class="frm_modal_top">
				<?php if ( ! empty( $error_args['icon'] ) ) : ?>
					<div class="frm_lock_simple"><?php FrmAppHelper::icon_by_class( 'frmfont ' . $error_args['icon'] ); ?></div>
				<?php endif; ?>
				<div class="frm-modal-title"><h2 id="frm_error_modal_title" tabindex="-1" autofocus><?php echo esc_html( $error_args['title'] ); ?></h2></div>
				<div>
					<a href="<?php echo esc_url( $error_args['cancel_url'] ); ?>" class="dismiss" title="<?php esc_attr_e( 'Dismiss this message', 'formidable' ); ?>">
						<?php FrmAppHelper::icon_by_class( 'frmfont frm_close_icon', array( 'aria-label' => 'Dismiss' ) ); ?>
					</a>
				</div>
			</div>
			<div id="frm_error_modal_body" class="frm_modal_content"><?php echo esc_html( $error_args['body'] ); ?></div>
			<div class="frm_modal_footer">
				<?php if ( ! empty( $error_args['cancel_text'] ) ) : ?>
					<a href="<?php echo esc_url( $error_args['cancel_url'] ); ?>" class="button button-secondary frm-button-secondary dismiss frm-modal-cancel <?php echo esc_attr( $error_args['cancel_classes'] ); ?>"><?php echo esc_html( $error_args['cancel_text'] ); ?></a>
					<?php
					endif;

				if ( ! empty( $error_args['continue_text'] ) ) :
					$continue_attrs = array(
						'class' => trim( 'button button-primary dismiss frm-button-primary ' . $error_args['continue_classes'] ),
					);

					if ( ! empty( $error_args['continue_target'] ) ) {
						$continue_attrs['target'] = $error_args['continue_target'];
						$continue_attrs['rel']    = 'noopener';
					}
					?>
					<a href="<?php echo esc_url( $error_args['continue_url'] ); ?>"<?php FrmAppHelper::array_to_html_params( $continue_attrs, true ); ?>><?php echo esc_html( $error_args['continue_text'] ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</dialog>
<script>
	{
		const modalElement = document.getElementById( 'frm_error_modal' );
		const scrollContainers = [ document.documentElement, document.body ];
		const previousOverflow = scrollContainers.map( element => element.style.overflow );

		scrollContainers.forEach( element => {
			element.style.overflow = 'hidden';
		} );
		modalElement.addEventListener( 'cancel', event => event.preventDefault() );
		modalElement.addEventListener( 'close', () => {
			scrollContainers.forEach( ( element, index ) => {
				element.style.overflow = previousOverflow[ index ];
			} );
		} );
		modalElement.showModal();
	}
</script>
