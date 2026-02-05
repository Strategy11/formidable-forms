<?php
/**
 * Search view
 *
 * @since x.x
 *
 * @var array  $atts       Search attributes including class, text, and tosearch
 * @var string $input_id   Input element ID
 * @var array  $input_atts Input HTML attributes
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="frm-search <?php echo esc_attr( $atts['class'] ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>">
		<?php echo esc_html( $atts['text'] ); ?>:
	</label>
	<?php FrmAppHelper::icon_by_class( 'frmfont frm_search_icon frm_svg20' ); ?>
	<input <?php FrmAppHelper::array_to_html_params( $input_atts, true ); ?> />
	<?php
	if ( empty( $atts['tosearch'] ) ) {
		submit_button( $atts['text'], 'button-secondary', '', false, array( 'id' => 'search-submit' ) );
	}
	?>
</p>
