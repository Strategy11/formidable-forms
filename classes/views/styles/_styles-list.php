<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
// This partial view is used in the visual styler sidebar in "list" view.
// It lists all styles and allows the user to select and assign a style to a target form.
// It is accessed from /wp-admin/admin.php?page=formidable-styles&form=782

$enabled     = '0' !== $form->options['custom_style'];
$card_helper = new FrmStylesCardHelper( $active_style, $default_style, $form->id, $enabled );
$style_api   = new FrmStyleApi();
$sidebar_params = array(
	'id'    => 'frm_style_sidebar',
	'class' => 'frm-right-panel frm_p_4', // Make sure not to put .frm_wrap on the whole container because it will cause admin styles to apply to style cards.
);
if ( ! FrmAppHelper::pro_is_installed() ) {
	// Use flex on the sidebar so that the style card matches the size of the upsell card.
	$sidebar_params['class'] .= ' frm-flex-custom-card-wrapper';
}

// TODO move this out of the view.
$custom_styles = array_filter(
	$styles,
	/**
	 * @param WP_Post $style
	 * @param WP_Post $default_style
	 * @return array<WP_Post>
	 */
	function( $style ) use ( $default_style ) {
		return $default_style->ID !== $style->ID;
	}
);
?>
<div <?php FrmAppHelper::array_to_html_params( $sidebar_params, true ); ?>>
	<?php
	/**
	 * Pro needs to hook in here to add the "New Style" trigger.
	 *
	 * @since x.x
	 *
	 * @param array $args {
	 *     @type stdClass $form
	 * }
	 */
	do_action( 'frm_style_list_sidebar_top', compact( 'form' ) );
	?>
	<?php // This form isn't visible. It's just used for assigning the selected style id to the target form. ?>
	<form id="frm_style_list_form" method="post" action="<?php echo esc_url( FrmStylesHelper::get_list_url( $form->id ) ); ?>">
		<input type="hidden" name="style_id" value="<?php echo absint( $active_style->ID ); ?>" />
		<input type="hidden" name="form_id" value="<?php echo absint( $form->id ); ?>" />
		<input type="hidden" name="frm_action" value="assign_style" />
		<?php wp_nonce_field( 'frm_save_form_style_nonce', 'frm_save_form_style' ); ?>
	</form>
	<div>
		<?php
		FrmHtmlHelper::toggle(
			'frm_enable_styling',
			'frm_enable_styling',
			array(
				'checked'     => $enabled,
				'on_label'    => __( 'Enable Formidable styling', 'formidable' ),
				'show_labels' => true,
				'echo'        => true,
			)
		);
		?>
	</div>

	<div class="frm_form_settings">
		<h2><?php esc_html_e( 'Default style', 'formidable' ); ?></h2>
	</div>
	<?php $card_helper->echo_card_wrapper( 'frm_default_style_cards_wrapper', array( $default_style ) ); ?>

	<div class="frm_form_settings">
		<h2><?php esc_html_e( 'Custom styles', 'formidable' ); ?></h2>
	</div>
	<?php // TODO the new button gets moved into the custom styles wrapper. ?>
	<?php $card_helper->echo_card_wrapper( 'frm_custom_style_cards_wrapper', $custom_styles ); ?>

	<div class="frm_form_settings">
		<h2><?php esc_html_e( 'Formidable styles', 'formidable' ); ?></h2>
	</div>
	<?php $card_helper->echo_card_wrapper( 'frm_template_style_cards_wrapper', $style_api->get_api_info() ); ?>

	<?php FrmTipsHelper::pro_tip( 'get_styling_tip', 'p' ); // If Pro is not active, this will show an upsell. ?>
</div>
