<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
// This partial view is used in the visual styler sidebar in "list" view.
// It lists all styles and allows the user to select and assign a style to a target form.
// It is accessed from /wp-admin/themes.php?page=formidable-styles&form=782

$enabled = '0' !== $form->options['custom_style'];
?>
<div id="frm_style_sidebar">
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
	do_action( 'frm_style_sidebar_top', compact( 'form' ) );
	?>
	<?php // This form isn't visible. It's just used for assigning the selected style id to the target form. ?>
	<form id="frm_style_list_form" method="post" action="<?php echo esc_url( admin_url( 'themes.php?page=formidable-styles&form=' . $form->id . '&t=advanced_settings' ) ); ?>">
		<input type="hidden" name="style_id" value="<?php echo absint( $active_style->ID ); ?>" />
		<input type="hidden" name="form_id" value="<?php echo absint( $form->id ); ?>" />
		<input type="hidden" name="frm_action" value="assign_style" />
		<?php wp_nonce_field( 'frm_save_form_style_nonce', 'frm_save_form_style' ); ?>
	</form>
	<div id="frm_enable_styling_wrapper">
		<?php
		FrmHtmlHelper::toggle(
			'frm_enable_styling',
			'frm_enable_styling',
			array(
				'div_class'   => 'with_frm_style',
				'checked'     => $enabled,
				'on_label'    => __( 'Enable Formidable styling', 'formidable' ),
				'show_labels' => true,
				'echo'        => true,
			)
		);
		?>
	</div>
	<?php
	$card_wrapper_params = array(
		'id' => 'frm_style_cards_wrapper',
	);
	if ( ! $enabled ) {
		$card_wrapper_params['style'] = 'opacity: 0.5; pointer-events: none;';
	}
	?>
	<div <?php FrmAppHelper::array_to_html_params( $card_wrapper_params, true ); ?>>
		<?php
		array_walk(
			$styles,
			/**
			 * @param string   $style_views_path
			 * @param WP_Post  $active_style
			 * @param WP_Post  $default_style
			 * @param stdClass $form
			 * @return void
			 */
			function( $style ) use ( $style_views_path, $active_style, $default_style, $form ) {
				FrmStylesHelper::echo_style_card( $style, $style_views_path, $active_style, $default_style, $form->id );
			}
		);
		?>
	</div>
</div>
