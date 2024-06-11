<?php
/**
 * On Submit form action settings
 *
 * @package Formidable
 * @since 6.0
 *
 * @var object        $instance Form action post object.
 * @var array         $args     Contains `form`, `action_key`, `values`.
 * @var FrmFormAction $this     FrmFormAction instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$success_action = $instance->post_content['success_action'];

$types = array(
	'message'  => array(
		'label'        => __( 'Show Message', 'formidable' ),
		'icon'         => 'frm_icon_font frm_chat_forms_icon frm_svg20',
		'sub_settings' => array( 'FrmOnSubmitHelper', 'show_message_settings' ),
	),
	'redirect' => array(
		'label'        => __( 'Redirect to URL', 'formidable' ),
		'icon'         => 'frm_icon_font frm_globe_icon frm_svg20',
		'sub_settings' => array( 'FrmOnSubmitHelper', 'show_redirect_settings' ),
	),
	'page'     => array(
		'label'        => __( 'Show Page Content', 'formidable' ),
		'icon'         => 'frm_icon_font frm_file_text_icon frm_svg20',
		'sub_settings' => array( 'FrmOnSubmitHelper', 'show_page_settings' ),
	),
);

$col_count = count( $types );
if ( $col_count <= 4 ) {
	$col_class = 'frm' . ( 12 / $col_count );
} else {
	$col_count = 'frm2';
}
?>
<div class="frm_on_submit_type_setting">
	<div class="frm_grid_container">
		<?php
		foreach ( $types as $type => $type_data ) :
			$input_id = $this->get_field_id( 'success_action_' . $type );
			?>
			<div class="frm_on_submit_type frm_form_field <?php echo esc_attr( $col_class ); ?>">
				<input
					type="radio"
					id="<?php echo esc_attr( $input_id ); ?>"
					name="<?php echo esc_attr( $this->get_field_name( 'success_action' ) ); ?>"
					value="<?php echo esc_attr( $type ); ?>"
					<?php checked( $type, $success_action ); ?>
				/>
				<label for="<?php echo esc_attr( $input_id ); ?>">
					<?php FrmAppHelper::icon_by_class( $type_data['icon'], array( 'echo' => true ) ); ?>
					<?php echo esc_html( $type_data['label'] ); ?>
				</label>
			</div>
		<?php endforeach; ?>
	</div>
</div><!-- End .frm_on_submit_type_setting -->

<?php
$type_args                   = $args;
$type_args['form_action']    = $instance;
$type_args['action_control'] = $this;
foreach ( $types as $type_name => $type ) {
	$css_class = 'frm_on_submit_' . esc_attr( $type_name ) . '_settings frm_on_submit_dependent_setting';
	if ( $success_action !== $type_name ) {
		$css_class .= ' frm_hidden';
	}
	?>
	<div class="<?php echo esc_attr( $css_class ); ?>" data-show-if-<?php echo esc_attr( $type_name ); ?>>
		<?php
		if ( is_callable( $type['sub_settings'] ) ) {
			call_user_func( $type['sub_settings'], $type_args );
		}
		?>
	</div><!-- End .frm_on_submit_<?php echo esc_attr( $type_name ); ?>_settings -->
	<?php
}

unset( $css_class, $type_args, $type_name, $type, $success_action );
