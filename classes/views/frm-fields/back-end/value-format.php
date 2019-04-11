<p class="frm-has-modal">
	<label for="frm_format_<?php echo esc_attr( $field['field_key'] ); ?>" class="frm_help" title="<?php esc_attr_e( 'Insert the format you would like to accept. Use a regular expression starting with ^ or an exact format like (999)999-9999.', 'formidable' ); ?>">
		<?php esc_html_e( 'Format', 'formidable' ); ?>
	</label>
	<span class="frm-with-right-icon">
		<i class="frm-show-inline-modal fas fa-ellipsis-h"></i>
		<input type="text" class="frm_long_input frm_format_opt" value="<?php echo esc_attr( $field['format'] ); ?>" name="field_options[format_<?php echo absint( $field['id'] ); ?>]" id="frm_format_<?php echo absint( $field['id'] ); ?>" />
	</span>
</p>
<div class="frm-inline-modal postbox">
	<a href="#" class="dismiss alignright" title="<?php esc_attr_e( 'Close', 'formidable' ); ?>">
		<i class="fas fa-times" aria-label="<?php esc_attr_e( 'Close', 'formidable' ); ?>" aria-hidden="true"></i>
	</a>
		<ul class="frm-nav-tabs">
			<li class="frm-tabs">
				<a href="#">
					<?php esc_html_e( 'Input Mask Format', 'formidable' ); ?>
				</a>
			</li>
		</ul>
		<div class="inside">
			<p class="howto">
				<?php esc_html_e( 'To create a custom input mask, you’ll need to use this specific set of symbols:', 'formidable' ); ?>
			</p>
			<p>
				9 - <?php esc_html_e( 'Numeric', 'formidable' ); ?> (0-9)<br/>
				a - <?php esc_html_e( 'Alphabetical', 'formidable' ); ?> (a-z, A-Z)<br/>
				A - <?php esc_html_e( 'Uppercase alphabetical', 'formidable' ); ?> (A-Z)<br/>
				* - <?php esc_html_e( 'Alphanumeric', 'formidable' ); ?> (0-9, a-z, A-Z)<br/>
				& - <?php esc_html_e( 'Uppercase alphanumeric', 'formidable' ); ?> (0-9, A-Z)
			</p>
			<p>
				<?php esc_html_e( 'Example:', 'formidable' ); ?> 1 (999)-999-9999
			</p>
			<p>
				<a href="<?php echo esc_url( FrmAppHelper::admin_upgrade_link(
					array(
						'medium' => 'builder',
						'content' => 'inputmask',
					),
					'knowledgebase/format/'
				) ); ?>" target="_blank" rel="noopener">
					<?php esc_html_e( 'See more examples and docs', 'formidable' ); ?>
				</a>
			</p>
	</div>
</div>
