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
$div_class     = $args['div_class'] ?? false;
$show_labels   = $args['show_labels'] ?? false;
$off_label     = $args['off_label'] ?? '';
$on_label      = $args['on_label'] ?? 1;
$value         = $args['value'] ?? $on_label;
$checked       = isset( $args['checked'] ) && ( true === $args['checked'] || str_contains( $args['checked'], 'checked="checked"' ) );
$disabled      = ! empty( $args['disabled'] );
$aria_checked  = $checked ? 'true' : 'false';
$input_html    = $args['input_html'] ?? array();
$use_container = false;

$off_label_shown = $show_labels && $off_label;
// phpcs:ignore Universal.Operators.StrictComparisons
$on_label_shown = $show_labels && $on_label != 1;

$aria_attrs = array();

if ( isset( $args['aria-label-attr'] ) && '' !== $args['aria-label-attr'] ) {
	$aria_attrs['aria-label'] = $args['aria-label-attr'];
} elseif ( $off_label_shown || $on_label_shown ) {
	// When this view renders its own visible label span(s), point at those
	// instead of the `{$id}_label` fallback below, so the accessible name
	// resolves to the visible text. The fallback only resolves for callers that
	// render their own external element with that id -- most callers of this
	// view don't, so it stays a dangling reference for them (tracked separately,
	// not fixed by this change -- see the PR description).
	$labelledby = array();
	if ( $off_label_shown ) {
		$labelledby[] = $id . '_off_label';
	}
	if ( $on_label_shown ) {
		$labelledby[] = $id . '_on_label';
	}
	$aria_attrs['aria-labelledby'] = implode( ' ', $labelledby );
} else {
	$aria_attrs['aria-labelledby'] = $id . '_label';
}
$aria_attrs['aria-checked'] = $aria_checked;

$div_params = array(
	// This is important when the default style is !important as Pro styling may cause conflicts.
	// It uses --toggle-on-color so just set the variable.
	'style' => '--toggle-on-color:var(--primary-color);',
);

if ( $div_class ) {
	$use_container       = true;
	$div_params['class'] = $div_class;
}

if ( ! str_contains( $name, '[' ) ) {
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
		<?php if ( $off_label_shown ) { ?>
			<span id="<?php echo esc_attr( $id ); ?>_off_label" class="frm_off_label frm_toggle_opt frm-leading-none"><?php echo esc_html( $off_label ); ?></span>
		<?php } ?>

		<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $value ); ?>"
			<?php checked( $checked, true ); ?>
			<?php if ( $off_label_shown ) { ?>
				data-off="<?php echo esc_attr( $off_label ); ?>"
			<?php } ?>
			<?php if ( $disabled ) { ?>
				disabled
			<?php } ?>
			<?php
			if ( is_array( $input_html ) ) {
				FrmAppHelper::array_to_html_params( $input_html, true );
			} else {
				_doing_it_wrong( '$args[input_html]', 'An array is required', '6.0' );
			}
			?>
		/>

		<span class="frm_toggle" tabindex="0" role="switch"
			<?php FrmAppHelper::array_to_html_params( $aria_attrs, true ); ?>
		>
			<span class="frm_toggle_slider"></span>
		</span>

		<?php if ( $on_label_shown ) { ?>
			<span id="<?php echo esc_attr( $id ); ?>_on_label" class="frm_on_label frm_toggle_opt frm-leading-none"><?php FrmAppHelper::kses_echo( $on_label, 'all' ); ?></span>
		<?php } ?>
	</label>
<?php if ( $use_container ) { ?>
</div>
<?php } ?>
