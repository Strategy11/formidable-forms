<?php
/**
 * Solutions step top view
 *
 * @since x.x
 *
 * @var array  $step           Step information including current status, completion, label, description, and error
 * @var string $section_class CSS class for the step section
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<section class="step step-install <?php echo esc_attr( $section_class ); ?>">
	<aside class="num">
	<?php
	if ( ! empty( $step['complete'] ) ) {
		FrmAppHelper::icon_by_class(
			'frmfont frm_step_complete_icon',
			array(
				/* translators: %1$s: Step number */
				'aria-label' => sprintf( __( 'Step %1$d', 'formidable' ), $step['num'] ),
				'style'      => 'width:50px;height:50px;',
			)
		);
	} else {
		?>
		<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 100 100"><circle cx="50" cy="50" r="50" fill="#ccc"/><text x="50%" y="50%" text-anchor="middle" fill="#fff" stroke="#fff" stroke-width="2px" dy=".3em" font-size="3.7em"><?php echo esc_html( $step['num'] ); ?></text></svg><?php // phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong ?>
		<?php
	}
	?>
		<i class="loader hidden"></i>
	</aside>
	<div>
		<?php if ( $step['label'] ) { ?>
		<h3 class="frm-step-heading"><?php echo esc_html( $step['label'] ); ?></h3>
		<?php } ?>
		<p><?php echo esc_html( $step['description'] ); ?></p>
		<?php if ( isset( $step['error'] ) ) { ?>
			<p class="frm_error"><?php echo esc_html( $step['error'] ); ?></p>
		<?php } ?>
