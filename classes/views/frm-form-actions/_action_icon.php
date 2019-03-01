<li class="<?php echo esc_attr( $group_class ); ?>">
	<a href="javascript:void(0)" class="<?php echo esc_attr( $classes ); ?>" title="<?php echo esc_attr( $action_control->action_options['tooltip'] ); ?>" data-limit="<?php echo esc_attr( isset( $action_control->action_options['limit'] ) ? $action_control->action_options['limit'] : '99' ); ?>" data-actiontype="<?php echo esc_attr( $action_control->id_base ); ?>" data-upgrade="<?php echo esc_attr( $upgrade_label ); ?>" data-medium="settings-<?php echo esc_attr( $action_control->id_base ); ?>">
		<span><i class="<?php echo esc_attr( $action_control->action_options['classes'] ); ?>" style="--primary-hover:<?php echo esc_attr( $action_control->action_options['color'] ); ?>"></i></span>
		<?php echo esc_html( $action_control->name ); ?>
	</a>
</li>