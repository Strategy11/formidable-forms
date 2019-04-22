<li class="frm-action <?php echo esc_attr( $group_class . ( isset( $data['data-upgrade'] ) ? ' frm-not-installed' : '' ) ); ?>">
	<a href="javascript:void(0)" class="<?php echo esc_attr( $classes ); ?>"
		title="<?php echo esc_attr( $action_control->action_options['tooltip'] ); ?>"
		data-limit="<?php echo esc_attr( $action_control->action_options['limit'] ); ?>"
		data-actiontype="<?php echo esc_attr( $action_control->id_base ); ?>"
		<?php
		foreach ( $data as $name => $value ) {
			echo esc_attr( $name ) . '="' . esc_attr( $value ) . '" ';
		}
		?>
		>
		<span><i class="<?php echo esc_attr( $action_control->action_options['classes'] ); ?>" style="--primary-hover:<?php echo esc_attr( $action_control->action_options['color'] ); ?>"></i></span>
		<?php echo esc_html( str_replace( 'Add to ', '', $action_control->name ) ); ?>
	</a>
</li>
