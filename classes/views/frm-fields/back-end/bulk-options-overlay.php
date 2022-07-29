<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="frm-bulk-modal" class="frm_hidden frm-modal frm_common_modal">
	<div class="postbox">
	<div class="frm_modal_top">
		<div class="frm-modal-title">
			<?php esc_html_e( 'Bulk Edit Options', 'formidable' ); ?>
		</div>
		<div>
			<a class="dismiss" title="<?php esc_attr_e( 'Close', 'formidable' ); ?>">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_close_icon', array( 'aria-label' => __( 'Close', 'formidable' ) ) ); ?>
			</a>
		</div>
	</div>
	<div class="frm_grid_container">
		<div class="frm8">
			<p class="howto">
				<?php esc_html_e( 'Edit or add field options (one per line)', 'formidable' ); ?>
			</p>
			<textarea name="frm_bulk_options" id="frm_bulk_options"></textarea>
			<input type="hidden" value="" id="bulk-field-id" />
			<input type="hidden" value="" id="bulk-option-type" />

		</div>
		<div class="frm4">
			<h3>
				<?php esc_html_e( 'Insert Presets', 'formidable' ); ?>
			</h3>
			<ul class="frm_prepop">
				<?php
				foreach ( $prepop as $label => $pop ) {
					if ( isset( $pop['class'] ) ) {
						$class = $pop['class'];
						unset( $pop['class'] );
					} else {
						$class = '';
					}
					?>
					<li class="<?php echo esc_attr( $class ); ?>">
						<a href="#" class="frm-insert-preset" data-opts="<?php echo esc_attr( json_encode( $pop ) ); ?>">
							<?php echo esc_html( $label ); ?>
						</a>
					</li>
				<?php } ?>
			</ul>
		</div>
	</div>
	<div class="frm_modal_footer">
		<button class="button-primary frm-button-primary" id="frm-update-bulk-opts">
			<?php esc_attr_e( 'Update Options', 'formidable' ); ?>
		</button>
	</div>
	</div>
</div>
