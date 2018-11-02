<div id="postbox-container-1" class="postbox-container frm-right-panel">
<div id="frm_position_ele"></div>
<div id="frm-fixed" class="frm-mobile-fixed">
<?php
$action = isset( $_REQUEST['frm_action'] ) ? 'frm_action' : 'action';
$action = FrmAppHelper::get_param( $action, '', 'get', 'sanitize_title' );
$button = ( 'new' === $action || 'duplicate' === $action ) ? __( 'Create', 'formidable' ) : __( 'Update', 'formidable' );

include( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/_publish_box.php' );
?>

	<div id="frm_set_height_ele"></div>
	<div id="frm-fixed-panel">
	<div class="frm-ltr frm_field_list">
	<div id="taxonomy-linkcategory" class="categorydiv">
		<ul id="category-tabs" class="category-tabs frm-category-tabs">
			<li class="tabs">
				<a href="#frm-insert-fields" id="frm_insert_fields_tab"><?php esc_html_e( 'Fields', 'formidable' ); ?></a>
			</li>
			<li class="hide-if-no-js">
				<a href="#frm-layout-classes" id="frm_layout_classes_tab" class="frm_help" title="<?php esc_attr_e( 'Open the Field Settings and click on the CSS Layout Classes option to enable this tab', 'formidable' ) ?>">
					<?php esc_html_e( 'Layout', 'formidable' ); ?>
				</a>
			</li>
			<?php do_action( 'frm_extra_form_instruction_tabs' ); ?>
		</ul>

		<div id="frm-insert-fields" class="tabs-panel">
			<ul class="field_type_list">
<?php
foreach ( $frm_field_selection as $field_key => $field_type ) {
	$field_label = FrmFormsHelper::get_field_link_name( $field_type );
	?>
				<li class="frmbutton button <?php echo esc_attr( ' frm_t' . $field_key ) ?>" id="<?php echo esc_attr( $field_key ) ?>">
					<a href="#" class="frm_add_field frm_animate_bg" title="<?php echo esc_html( $field_label ) ?>">
						<i class="<?php echo esc_attr( FrmFormsHelper::get_field_link_icon( $field_type ) ) ?> frm_animate_bg"></i>
						<span><?php echo esc_html( $field_label ) ?></span>
					</a>
				</li>
<?php
	unset( $field_key, $field_type );
}
?>
			</ul>
			<div class="clear"></div>
			<?php FrmTipsHelper::pro_tip( 'get_builder_tip' ); ?>
			<ul class="field_type_list">
<?php

$no_allow_class = apply_filters( 'frm_noallow_class', 'frm_noallow' );
if ( $no_allow_class === 'frm_noallow' ) {
	$no_allow_class .= ' frm_show_upgrade';
	FrmAppController::include_upgrade_overlay();
}
foreach ( FrmField::pro_field_selection() as $field_key => $field_type ) {

	if ( is_array( $field_type ) && isset( $field_type['switch_from'] ) ) {
		continue;
	}

	if ( is_array( $field_type ) && isset( $field_type['types'] ) ) {
		$field_label = $field_type['name'];

?>
				<li class="frmbutton button <?php echo esc_attr( $no_allow_class . ' frm_t' . $field_key ) ?> dropdown" id="<?php echo esc_attr( $field_key ) ?>">
	                <a href="#" id="frm-<?php echo esc_attr( $field_key ) ?>Drop" class="frm-dropdown-toggle" data-toggle="dropdown" title="<?php echo esc_html( $field_label ) ?>">
						<i class="<?php echo esc_attr( FrmFormsHelper::get_field_link_icon( $field_type ) ) ?> frm_animate_bg"></i>
						<span><?php echo esc_html( $field_label ) ?> <b class="caret"></b></span>
					</a>

					<ul class="frm-dropdown-menu" role="menu" aria-labelledby="frm-<?php echo esc_attr( $field_key ) ?>Drop">
					<?php foreach ( $field_type['types'] as $k => $type ) { ?>
						<li class="frm_t<?php echo esc_attr( $field_key ) ?>" id="<?php echo esc_attr( $field_key ) ?>|<?php echo esc_attr( $k ) ?>">
							<?php echo FrmAppHelper::kses( apply_filters( 'frmpro_field_links', $type, $id, $field_key . '|' . $k ), array( 'a', 'i', 'span' ) ); // WPCS: XSS ok. ?>
						</li>
					<?php
						unset( $k, $type );
					}
					?>
					</ul>
				</li>
<?php
				} else {
					$field_label = '<i class="' . esc_attr( FrmFormsHelper::get_field_link_icon( $field_type ) ) . ' frm_animate_bg"></i>';
					$field_name  = FrmFormsHelper::get_field_link_name( $field_type );
					$field_label .= ' <span>' . $field_name . '</span>';
					$upgrade_label = sprintf( esc_html__( '%s fields', 'formidable' ), $field_name );
                    ?>
					<li class="frmbutton button <?php echo esc_attr( $no_allow_class . ' frm_t' . str_replace( '|', '-', $field_key ) ) ?>" id="<?php echo esc_attr( $field_key ) ?>" data-upgrade="<?php echo esc_attr( $upgrade_label ); ?>" data-medium="builder-<?php echo esc_attr( sanitize_title( $upgrade_label ) ); ?>">
						<?php echo FrmAppHelper::kses( apply_filters( 'frmpro_field_links', $field_label, $id, $field_key ), array( 'a', 'i', 'span' ) ); // WPCS: XSS ok. ?>
					</li>
				<?php
				}

				unset( $field_key, $field_type, $field_label );
			}
			?>
			</ul>
			<div class="clear"></div>
		</div>
		<?php do_action( 'frm_extra_form_instructions' ); ?>

		<div id="frm-layout-classes" class="tabs-panel">
			<ul>
				<li class="frm_show_inactive">
					<?php esc_html_e( 'Click inside the "CSS layout classes" field option in any field to enable the options below.', 'formidable' ); ?>
				</li>
				<li class="frm_show_active frm_hidden">
					<?php esc_html_e( 'Click on any box below to set the width for your selected field.', 'formidable' ); ?>
				</li>
			</ul>
			<ul class="frm_code_list frm_grid_container">
				<li class="frm_half frm_form_field">
					<a href="javascript:void(0);" data-code="frm_half" class="frm_insert_code show_frm_classes">
						1/2
					</a>
				</li>
				<?php
				foreach ( FrmFormsHelper::grid_classes() as $c => $d ) {
					?>
					<li class="<?php echo esc_attr( $c ) ?> frm_form_field">
						<a href="javascript:void(0);" data-code="<?php echo esc_attr( $c ) ?>" class="frm_insert_code show_frm_classes">
							<?php echo esc_html( FrmFormsHelper::style_class_label( $d, $c ) ); ?>
						</a>
					</li>
					<?php
					unset( $c, $d );
				}
				?>
			</ul>
			<ul class="frm_code_list">
			<?php
			$col = 'one';
			foreach ( FrmFormsHelper::css_classes() as $c => $d ) {
				$title = ( ! empty( $d ) && is_array( $d ) && isset( $d['title'] ) ) ? $d['title'] : '';
				?>
				<li class="frm_col_<?php echo esc_attr( $col ) ?>">
					<a href="javascript:void(0);" data-code="<?php echo esc_attr( $c ) ?>" class="frmbutton button frm_insert_code show_frm_classes<?php echo esc_attr( ! empty( $title ) ? ' frm_help' : '' ); ?>" <?php echo ( ! empty( $title ) ? ' title="' . esc_attr( $title ) . '"' : '' ); ?>>
						<?php echo esc_html( FrmFormsHelper::style_class_label( $d, $c ) ); ?>
					</a>
				</li>
				<?php
				$col = ( 'one' === $col ) ? 'two' : 'one';
				unset( $c, $d );
			}
?>
			</ul>
		</div>
	</div>
	</div>

	<form method="post" id="frm_js_build_form">
		<input type="hidden" id="frm_compact_fields" name="frm_compact_fields" value="" />
		<button class="frm_submit_form frm_submit_<?php echo esc_attr( ( isset( $values['ajax_load'] ) && $values['ajax_load'] ) ? '' : 'no_' ); ?>ajax frm_hidden frm_button_submit" type="button" id="frm_submit_side"><?php echo esc_html( $button ); ?></button>
	</form>

	</div>
	</div>
</div>
