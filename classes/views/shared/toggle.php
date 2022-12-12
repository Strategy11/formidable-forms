<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<?php
$div_class    = isset( $args['div_class'] ) ? $args['div_class'] : false;
$show_labels  = isset( $args['show_labels'] ) ? $args['show_labels'] : false;
$off_label    = isset( $args['off_label'] ) ? $args['off_label'] : '';
$on_label     = isset( $args['on_label'] ) ? $args['on_label'] : 1;
$checked      = isset( $args['checked'] ) && ( true === $args['checked'] || false !== strpos( $args['checked'], 'checked="checked"' ) );
$aria_checked = $checked ? 'true' : 'false';
?>

<div <?php echo $div_class ? 'class="' . esc_attr( $div_class ) . '"' : ''; ?>>
    <label class="frm_switch_block">
		<?php if ( $show_labels && $off_label ) { ?>
			<span class="frm_off_label frm_switch_opt"><?php echo esc_html( $off_label ); ?></span>
		<?php } ?>

		<input type="checkbox" name="<?php echo esc_attr( $name ); ?>[]" id="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $on_label ); ?>"
			<?php checked( $checked, true ); ?>
			<?php if ( $show_labels && $off_label ) { ?>
				data-off="<?php echo esc_attr( $off_label ); ?>"
			<?php } ?>
		/>

		<span class="frm_switch" tabindex="0" role="switch" aria-labelledby="<?php echo esc_attr( $id ); ?>_label" aria-checked="<?php echo esc_attr( $aria_checked ); ?>">
			<span class="frm_slider"></span>
		</span>

		<?php if ( $show_labels && $on_label != 1 ) { ?>
			<span class="frm_on_label frm_switch_opt"><?php echo FrmAppHelper::kses( $on_label, 'all' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<?php } ?>
	</label>
</div>
