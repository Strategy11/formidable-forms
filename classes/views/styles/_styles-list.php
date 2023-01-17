<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
// This partial view is used in the visual styler sidebar in "list" view.
// It lists all styles and allows the user to select and assign a style to a target form.
// It is accessed from /wp-admin/themes.php?page=formidable-styles&form=782

$enabled = '0' !== $form->options['custom_style'];
?>
<div id="frm_style_sidebar" class="frm-right-panel frm_p_4"><?php // Make sure not to put .frm_wrap on the whole container because it will cause admin styles to apply to style cards. ?>
	<?php
	/**
	 * Pro needs to hook in here to add the "New Style" trigger.
	 *
	 * @since x.x
	 * @todo I think a more descriptive action name like frm_style_list_sidebar_top may be better.
	 *
	 * @param array $args {
	 *     @type stdClass $form
	 * }
	 */
	do_action( 'frm_style_list_sidebar_top', compact( 'form' ) );
	?>
	<?php // This form isn't visible. It's just used for assigning the selected style id to the target form. ?>
	<form id="frm_style_list_form" method="post" action="<?php echo esc_url( admin_url( 'themes.php?page=formidable-styles&form=' . $form->id . '&t=advanced_settings' ) ); ?>">
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
		<h2><?php esc_html_e( 'My styles', 'formidable' ); ?></h2>
	</div>
	<?php
	// Begin card wrapper
	$card_wrapper_params = array(
		'id'    => 'frm_custom_style_cards_wrapper',
		'class' => 'frm-style-card-wrapper with_frm_style',
		'style' => FrmStylesCardHelper::get_style_attribute_value_for_wrapper()
	);
	if ( $enabled ) {
		$card_wrapper_params['class'] .= ' frm-styles-enabled';
	}
	?>
	<div <?php FrmAppHelper::array_to_html_params( $card_wrapper_params, true ); ?>>
		<?php
		$card_helper = new FrmStylesCardHelper( $active_style, $default_style, $form->id );
		array_walk(
			$styles,
			/**
			 * Echo a style card for a single style in the $styles array.
			 *
			 * @param WP_Post             $style
			 * @param FrmStylesCardHelper $card_helper
			 * @return void
			 */
			function( $style ) use ( $card_helper ) {
				$card_helper->echo_style_card( $style );
			}
		);
		?>
	</div>
	<?php // End card wrapper ?>
	<div class="frm_form_settings">
		<h2><?php esc_html_e( 'Formidable styles', 'formidable' ); ?></h2>
	</div>
	<?php
	// Begin card wrapper
	$card_wrapper_params = array(
		'id'    => 'frm_template_style_cards_wrapper',
		'class' => 'frm-style-card-wrapper with_frm_style',
	);
	if ( $enabled ) {
		$card_wrapper_params['class'] .= ' frm-styles-enabled';
	}
	?>
	<div <?php FrmAppHelper::array_to_html_params( $card_wrapper_params, true ); ?>>
		<?php
		$style_api = new FrmStyleApi();
		$info      = $style_api->get_api_info();
		foreach ( $info as $key => $style ) {
			if ( ! is_numeric( $key ) ) {
				// Skip active_sub/expires keys.
				continue;
			}

			$card_helper->echo_card_template( $style );
			unset( $style );
		}
		?>
	</div>

	<?php FrmTipsHelper::pro_tip( 'get_styling_tip', 'p' ); // If Pro is not active, this will show an upsell. ?>
</div>
