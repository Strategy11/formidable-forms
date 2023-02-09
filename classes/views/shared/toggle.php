<?php
/**
 * Show a toggle with or without labels.
 *
 * @package Formidable
 *
 * @var string $id   The HTML id.
 * @var string $name The HTML name.
 * @var array  $args Pass args.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<?php
$div_class    = isset( $args['div_class'] ) ? $args['div_class'] : false;
$show_labels  = isset( $args['show_labels'] ) ? $args['show_labels'] : false;
$off_label    = isset( $args['off_label'] ) ? $args['off_label'] : '';
$on_label     = isset( $args['on_label'] ) ? $args['on_label'] : 1;
$value        = isset( $args['value'] ) ? $args['value'] : $on_label;
$checked      = isset( $args['checked'] ) && ( true === $args['checked'] || false !== strpos( $args['checked'], 'checked="checked"' ) );
$aria_checked = $checked ? 'true' : 'false';
$input_html   = isset( $args['input_html'] ) ? $args['input_html'] : array();

$use_container = false;

$div_params = array(
	// This is important when the default style is !important as Pro styling may cause conflicts.
	// It uses --toggle-on-color so just set the variable.
	'style' => '--toggle-on-color:var(--primary-color);',
);
if ( $div_class ) {
	$use_container       = true;
	$div_params['class'] = $div_class;
}

if ( strpos( $name, '[' ) === false ) {
	$name .= '[]';
}

if ( $use_container ) {
	?>
<div <?php FrmAppHelper::array_to_html_params( $div_params, true ); ?>>
	<?php
	$div_params = array();
}
?>
	<label class="frm_toggle_block" <?php FrmAppHelper::array_to_html_params( $div_params, true ); ?>>
		<?php if ( $show_labels && $off_label ) { ?>
			<span class="frm_off_label frm_toggle_opt"><?php echo esc_html( $off_label ); ?></span>
		<?php } ?>

		<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $value ); ?>"
			<?php checked( $checked, true ); ?>
			<?php if ( $show_labels && $off_label ) { ?>
				data-off="<?php echo esc_attr( $off_label ); ?>"
			<?php } ?>
			<?php
			if ( is_array( $input_html ) ) {
				FrmAppHelper::array_to_html_params( $input_html, true );
			} else {
				_doing_it_wrong( '$args[input_html]', 'An array is required', '6.0' );
			}
			?>
		/>

		<span class="frm_toggle" tabindex="0" role="switch" aria-labelledby="<?php echo esc_attr( $id ); ?>_label" aria-checked="<?php echo esc_attr( $aria_checked ); ?>">
			<span class="frm_toggle_slider"></span>
		</span>

		<?php if ( $show_labels && $on_label != 1 ) { ?>
			<span class="frm_on_label frm_toggle_opt"><?php echo FrmAppHelper::kses( $on_label, 'all' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<?php } ?>
	</label>
<?php if ( $use_container ) { ?>
</div>
<?php } ?>
