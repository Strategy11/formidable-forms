<div class="frm-embed-field-placeholder">
	<div class="frm-embed-message">
		<?php esc_html_e( 'Custom HTML:', 'formidable' ); ?>
		<?php echo esc_html( FrmAppHelper::truncate( htmlentities( $field['description'] ), 60 ) ); ?>
	</div>
</div>
