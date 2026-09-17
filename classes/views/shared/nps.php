<?php
/**
 * NPS Score template.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$nps_attrs = array(
	'class' => 'frm-nps frm-flex-col frm-gap-xs',
);

if ( ! empty( $args['id'] ) ) {
	$nps_attrs['id'] = $args['id'];
}

if ( ! empty( $args['class'] ) ) {
	$nps_attrs['class'] .= ' ' . $args['class'];
}

$input_attrs = array(
	'type'  => 'radio',
	'name'  => $args['name'],
	'class' => 'frm_hidden',
);
?>
<div <?php FrmAppHelper::array_to_html_params( $nps_attrs, true ); ?>>
	<div class="frm-nps__buttons frm-flex frm-justify-center" role="radiogroup">
		<?php
		for ( $i = 0; $i <= 10; $i++ ) {
			$input_attrs['id']    = $args['id'] . '-' . $i;
			$input_attrs['value'] = $i;
			?>
			<input <?php FrmAppHelper::array_to_html_params( $input_attrs, true ); ?> <?php checked( $i, $args['value'] ); ?>/>
			<label for="<?php echo esc_attr( $input_attrs['id'] ); ?>" class="frm-nps__button frm-flex-center">
				<?php echo intval( $i ); ?>
			</label>
		<?php } ?>
	</div>

	<div class="frm-nps__statements frm-flex frm-justify-between frm-leading-none">
		<div class="frm-nps__negative">
			<?php
			printf(
				/* translators: %s is the negative statement. */
				esc_html__( '0 - %s', 'formidable' ),
				'<span>' . esc_html( $args['negative_statement'] ) . '</span>'
			);
			?>
		</div>

		<div class="frm-nps__positive frm-text-right">
			<?php
			printf(
				/* translators: %s is the positive statement. */
				esc_html__( '10 - %s', 'formidable' ),
				'<span>' . esc_html( $args['positive_statement'] ) . '</span>'
			);
			?>
		</div>
	</div>
</div>
