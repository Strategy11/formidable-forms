<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$single_action_attrs = array_merge(
	$data,
	array(
		'href'            => 'javascript:void(0)',
		'class'           => $classes,
		'data-limit'      => $action_control->action_options['limit'],
		'data-actiontype' => $action_control->id_base,
	)
);
?>
<li class="frm-card-item frm-action<?php echo esc_attr( $group_class . ( isset( $data['data-upgrade'] ) ? ' frm-not-installed' : '' ) ); ?>">
	<a <?php FrmAppHelper::array_to_html_params( $single_action_attrs, true ); ?>>
		<span class="frm-form-templates-item-icon">
			<span class="frm-category-icon frm-icon-wrapper">
				<?php FrmAppHelper::icon_by_class( $action_control->action_options['classes'] ); ?>
			</span>
		</span>

		<span class="frm-form-templates-item-body">
			<span class="frm-form-templates-item-title frm-font-medium">
				<span class="frm-form-templates-item-title-text">
					<span class="frm-form-template-name frm-truncate">
						<?php echo esc_html( str_replace( 'Add to ', '', $action_control->name ) ); ?>
					</span>
				</span>
				<span class="frm-flex-box frm-gap-xs frm-items-center frm-ml-auto">
					<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_plus_icon' ); ?>
					<span><?php echo esc_html_x( 'Add', 'form action', 'formidable' ); ?></span>
				</span>
			</span>

			<span class="frm-form-templates-item-content">
				<?php echo esc_html( $action_control->action_options['description'] ?? '' ); ?>
			</span>
		</span>

		<?php if ( ! empty( $action_control->action_options['keywords'] ) ) { ?>
			<span class="frm_hidden">
				<?php
				// Include keywords for the action search.
				echo esc_html( $action_control->action_options['keywords'] );
				?>
			</span>
		<?php } ?>
	</a>
</li>
