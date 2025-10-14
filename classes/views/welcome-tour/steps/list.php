<?php
/**
 * Welcome Tour's Individual step view.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! is_array( $steps ) ) {
	return;
}

foreach ( $steps as $key => $step ) {
	$is_active  = $active_step === $key;
	$step_attrs = array(
		'id'    => 'frm-checklist__step-' . $key,
		'class' => 'frm-checklist__step',
	);

	if ( $is_active ) {
		$step_attrs['class'] .= ' frm-checklist__step--active';
	}

	if ( $step['completed'] ) {
		$step_attrs['class'] .= ' frm-checklist__step--completed';
	}
	?>
	<div <?php FrmAppHelper::array_to_html_params( $step_attrs, true ); ?>>
		<div class="frm-checklist__step-title frm-h-stack-xs frm-p-sm">
			<span class="frm-checklist__step-status frm-flex-center">
				<?php FrmAppHelper::icon_by_class( 'frmfont frm_checkmark_icon frm_svg7 frm-text-white', array( 'aria-hidden' => 'true' ) ); ?>
			</span>
			<span class=" frm-leading-none"><?php echo esc_html( $step['title'] ); ?></span>
		</div>

		<div class="frm-checklist__step-description frm-flex frm-p-xs frm-mx-xs -frm-mt-2xs">
			<span><?php echo esc_html( $step['description'] ); ?></span>
		</div>
	</div>
	<?php
}//end foreach
