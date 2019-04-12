<div class="frm-inline-modal postbox frm_hidden" id="<?php echo esc_attr( $args['id'] ); ?>">
	<a href="#" class="dismiss alignright" title="<?php esc_attr_e( 'Close', 'formidable' ); ?>">
		<i class="fas fa-times" aria-label="<?php esc_attr_e( 'Close', 'formidable' ); ?>" aria-hidden="true"></i>
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
