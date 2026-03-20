<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$single_action_attrs = array_merge(
	$data,
	array(
		'href'            => 'javascript:void(0)',
		'class'           => $classes . ' button frm-button-secondary frm-button-sm frm-with-icon frm-ml-auto-force',
		'data-limit'      => $action_control->action_options['limit'],
		'data-actiontype' => $action_control->id_base,
	)
);
?>
<li class="frm-card-item frm-card-item--outlined frm-action<?php echo esc_attr( $group_class . ( isset( $data['data-upgrade'] ) ? ' frm-not-installed' : '' ) ); ?>">
	<div class="frm-h-stack-xs frm-w-full frm-mb-2xs">
		<span class="frm-border-icon">
			<?php FrmAppHelper::icon_by_class( $action_control->action_options['classes'], $icon_atts ); ?>
		</span>

		<div class="frm-flex-col">
			<h3>
				<span class="frm-font-medium frm-truncate"><?php echo esc_html( str_replace( 'Add to ', '', $action_control->name ) ); ?></span>
				<?php if ( ! empty( $action_control->action_options['is_new'] ) ) { ?>
					<?php FrmAppHelper::show_pill_text(); ?>
				<?php } ?>
			</h3>
			<?php if ( ! empty( $action_control->action_options['description'] ) ) { ?>
				<p class="frm-line-clamp-2"><?php echo esc_html( $action_control->action_options['description'] ); ?></p>
			<?php } ?>
		</div>

		<a <?php FrmAppHelper::array_to_html_params( $single_action_attrs, true ); ?>>
			<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_plus_icon' ); ?>
			<span><?php echo esc_html_x( 'Add', 'form action', 'formidable' ); ?></span>
		</a>

		<?php if ( ! empty( $action_control->action_options['keywords'] ) ) { ?>
			<span class="frm_hidden"><?php echo esc_html( $action_control->action_options['keywords'] ); ?></span>
		<?php } ?>
	</div>
</li>
