<?php
/**
 * Welcome Tour - Individual step template (equivalent to form-templates/template.php).
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! is_array( $template ) ) {
	return;
}

$status       = $template['complete'] ? 'complete' : 'incomplete';
$is_active    = ( $active_step === $step );
$step_classes = 'frm-checklist__step';
if ( $is_active ) {
	$step_classes .= ' frm-checklist__step--active';
}
?>
<div id="frm-checklist__step-<?php echo esc_attr( $step ); ?>" class="<?php echo esc_attr( $step_classes ); ?>">
	<div class="frm-checklist__step-main-content">
		<svg><use href="#frm_<?php echo esc_attr( $status ); ?>_status_icon"></use></svg>
		<?php echo esc_html( $template['title'] ); ?>
	</div>
	<div class="frm-checklist__step-description">
		<?php echo esc_html( $template['description'] ); ?>
	</div>
</div>
