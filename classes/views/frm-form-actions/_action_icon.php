<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<li class="frm-action <?php echo esc_attr( $group_class . ( isset( $data['data-upgrade'] ) ? ' frm-not-installed' : '' ) ); ?>">
	<a href="javascript:void(0)" class="<?php echo esc_attr( $classes ); ?>"
		data-limit="<?php echo esc_attr( $action_control->action_options['limit'] ); ?>"
		data-actiontype="<?php echo esc_attr( $action_control->id_base ); ?>"
		<?php FrmAppHelper::array_to_html_params( $data, true ); ?>
		>
		<span class="frm-outer-circle">
			<span class="frm-inner-circle<?php echo esc_attr( strpos( $action_control->action_options['classes'], 'frm-inverse' ) === false ? '' : ' frm-inverse' ); ?>" <?php
				FrmAppHelper::array_to_html_params( $icon_atts, true );
			?>>
				<?php FrmAppHelper::icon_by_class( $action_control->action_options['classes'], $icon_atts ); ?>
			</span>
		</span>
		<?php echo esc_html( str_replace( 'Add to ', '', $action_control->name ) ); ?>
	</a>
</li>
