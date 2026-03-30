<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$single_action_attrs = array_merge(
	$data,
	array(
		'href'            => 'javascript:void(0)',
		'class'           => $classes . ' button frm-button-secondary frm-button-sm frm-with-icon frm-ml-auto-force frm-fadein-down-short',
		'data-limit'      => $action_control->action_options['limit'],
		'data-actiontype' => $action_control->id_base,
	)
);
?>
<li class="frm-card-item frm-card-item--outlined frm-action<?php echo esc_attr( $group_class . ( isset( $data['data-upgrade'] ) ? ' frm-not-installed' : '' ) ); ?>">
	<div class="frm-h-stack-xs frm-w-full">
		<span class="frm-border-icon">
			<?php FrmAppHelper::icon_by_class( $action_control->action_options['classes'], FrmFormActionsController::get_action_icon_atts( $action_control ) ); ?>
		</span>

		<div class="frm-flex-col">
			<h3 class="frm-h-stack-xs frm-text-md frm-capitalize">
				<?php
				if ( isset( $data['data-upgrade'] ) && ! isset( $data['data-oneclick'] ) ) {
					FrmAppHelper::icon_by_class( 'frmfont frm_lock_icon frm_svg15', array( 'aria-label' => __( 'Lock icon', 'formidable' ) ) );
				}
				?>
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
			<?php FrmAppHelper::icon_by_class( 'frmfont frm_plus_icon' ); ?>
			<span><?php echo esc_html_x( 'Add', 'form action', 'formidable' ); ?></span>
		</a>

		<?php if ( ! empty( $action_control->action_options['keywords'] ) ) { ?>
			<span class="frm_hidden"><?php echo esc_html( $action_control->action_options['keywords'] ); ?></span>
		<?php } ?>
	</div>
</li>
