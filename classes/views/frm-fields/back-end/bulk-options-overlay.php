<div id="frm-bulk-modal" class="frm_hidden settings-lite-cta">
	<a href="#" class="dismiss alignright" title="<?php esc_attr_e( 'Close', 'formidable' ); ?>">
		<?php FrmAppHelper::icon_by_class( 'frmfont frm_close_icon', array( 'aria-label' => __( 'Close', 'formidable' ) ) ); ?>
	</a>
	<h2>
		<?php esc_html_e( 'Bulk Edit Options', 'formidable' ); ?>
	</h2>
	<div class="frm_grid_container">
		<div class="frm8 frm_form_field">
			<p class="howto">
				<?php esc_html_e( 'Edit or add field options (one per line)', 'formidable' ); ?>
			</p>
			<textarea name="frm_bulk_options" id="frm_bulk_options"></textarea>
			<input type="hidden" value="" id="bulk-field-id" />
				
			<button class="button-primary frm-button-primary" id="frm-update-bulk-opts">
				<?php esc_attr_e( 'Update Options', 'formidable' ); ?>
			</button>
		</div>
		<div class="frm4 frm_form_field">
			<h3>
				<?php esc_html_e( 'Insert Presets', 'formidable' ); ?>
			</h3>
			<ul class="frm_prepop">
				<?php foreach ( $prepop as $label => $pop ) { ?>
					<li>
						<a href="#" class="frm-insert-preset" data-opts="<?php echo esc_attr( json_encode( $pop ) ); ?>">
							<?php echo esc_html( $label ); ?>
						</a>
					</li>
				<?php } ?>
			</ul>
		</div>
	</div>
</div>
