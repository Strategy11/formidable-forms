<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
// This partial view is used in the visual styler sidebar in "list" view.
// It lists all styles and allows the user to select and assign a style to a target form.
// It is accessed from /wp-admin/admin.php?page=formidable-styles&form=782

$frm_settings      = FrmAppHelper::get_settings();
$globally_disabled = 'none' === $frm_settings->load_style;
$enabled           = ( ! is_array( $form->options ) || 0 !== (int) $form->options['custom_style'] ) && ! $globally_disabled;
$card_helper       = new FrmStylesCardHelper( $active_style, $default_style, $form->id, $enabled );
$styles            = $card_helper->get_styles();
$custom_styles     = $card_helper->filter_custom_styles( $styles );
$sidebar_params    = array(
	'id'    => 'frm_style_sidebar',
	// Make sure not to put .frm_wrap on the whole container because it will cause admin styles to apply to style cards.
	'class' => 'frm-right-panel frm-p-6 frm_wrap',
);
$toggle_input_html = array();
if ( $globally_disabled ) {
	$sidebar_params['class']      .= ' frm-styles-globally-disabled';
	$toggle_input_html['disabled'] = 'disabled';
}
?>
<div <?php FrmAppHelper::array_to_html_params( $sidebar_params, true ); ?>>
	<?php
	$can_create_styles = class_exists( 'FrmProStylesPreviewHelper' );
	$trigger_params    = array(
		'id'   => 'frm_new_style_trigger',
		'href' => '#',
	);
	if ( $can_create_styles ) {
		$trigger_params['data-new-style-url'] = esc_url( admin_url( 'admin.php?page=formidable-styles&frm_action=new_style' ) );
	} else {
		$trigger_params['class']        = 'frm_noallow';
		$trigger_params['data-upgrade'] = __( 'You are currently limited to 1 style template', 'formidable' );
		$trigger_params['data-message'] = __( 'Upgrade to create and manage as many form styles as you need.', 'formidable' );
		$trigger_params['data-image']   = 'styles-upsell.svg';
	}
	?>
	<?php
	// This form isn't visible. It's just used for assigning the selected style id to the target form.
	?>
	<form id="frm_style_list_form" method="post" action="<?php echo esc_url( FrmStylesHelper::get_list_url( $form->id ) ); ?>">
		<input type="hidden" name="style_id" value="<?php echo absint( $enabled ? $active_style->ID : 0 ); ?>" />
		<input type="hidden" name="form_id" value="<?php echo absint( $form->id ); ?>" />
		<input type="hidden" name="frm_action" value="assign_style" />
		<?php wp_nonce_field( 'frm_save_form_style_nonce', 'frm_save_form_style' ); ?>
	</form>
	<div class="frm-mb-sm frm-flex-justify">
		<?php
		FrmHtmlHelper::toggle(
			'frm_enable_styling',
			'frm_enable_styling',
			array(
				'checked'     => $enabled,
				'on_label'    => __( 'Enable Formidable styling', 'formidable' ),
				'show_labels' => true,
				'echo'        => true,
				'input_html'  => $toggle_input_html,
			)
		);
		?>
		<div id="frm_new_style_trigger_wrapper">
			<a <?php FrmAppHelper::array_to_html_params( $trigger_params, true ); ?>>
				<?php
				FrmAppHelper::icon_by_class( 'frmfont frm_plus_icon' );
				esc_html_e( 'New Style', 'formidable' );
				?>
			</a>
		</div>
	</div>

	<div class="frm_form_settings">
		<h2><?php esc_html_e( 'Default Style', 'formidable' ); ?></h2>
	</div>
	<?php $card_helper->echo_card_wrapper( 'frm_default_style_cards_wrapper', array( $default_style ) ); ?>

	<?php if ( $custom_styles ) { ?>
		<div class="frm_form_settings">
			<h2><?php esc_html_e( 'Custom Styles', 'formidable' ); ?></h2>
		</div>
		<?php $card_helper->echo_card_wrapper( 'frm_custom_style_cards_wrapper', $custom_styles ); ?>
	<?php } ?>

	<?php $style_templates = array_filter( $card_helper->get_template_info(), 'is_array' ); ?>
	<?php if ( $style_templates ) { ?>
		<div class="frm_form_settings">
			<h2><?php esc_html_e( 'Formidable Styles', 'formidable' ); ?></h2>
		</div>
		<?php $card_helper->echo_card_wrapper( 'frm_template_style_cards_wrapper', $style_templates ); ?>
	<?php } ?>
</div>
