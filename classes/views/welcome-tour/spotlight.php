<?php
/**
 * Welcome Tour's Spotlight component.
 *
 * @since 6.25.1
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( empty( $spotlight['target'] ) ) {
	return;
}

$placement                      = $spotlight['placement'] ?? 'right';
$spotlight_attributes           = array(
	'class'              => 'frm-spotlight frm-welcome-tour-spotlight frm-gap-xs frm-items-center frm-force-hidden frm-fadein',
	'data-target'        => $spotlight['target'],
	'data-left-position' => $spotlight['left-position'] ?? 'end',
	'data-placement'     => $placement,
	'style'              => '',
);
$spotlight_attributes['class'] .= 'bottom' === $placement ? ' frm-flex-col' : ' frm-flex';

if ( ! empty( $spotlight['offset']['top'] ) ) {
	$spotlight_attributes['style'] .= 'margin-top: ' . $spotlight['offset']['top'] . 'px;';
}
if ( ! empty( $spotlight['offset']['left'] ) ) {
	$spotlight_attributes['style'] .= 'margin-left: ' . $spotlight['offset']['left'] . 'px;';
}
?>
<div <?php FrmAppHelper::array_to_html_params( $spotlight_attributes, true ); ?>>
	<div class="frm-spotlight__pointer frm-flex-center frm-fadein-up-back frm-pulse"></div>

	<div class="frm-spotlight__content frm-flex-col frm-justify-center frm-gap-xs frmcenter frm-bg-white frm-p-sm frm-rounded-md frm-box-shadow-xxl frm-fadein-up-back">
		<?php if ( ! empty( $spotlight['title'] ) ) { ?>
			<h3 class="frm-text-xs frm-font-bold frm-text-grey-800 frm-m-0">
				<?php echo esc_html( $spotlight['title'] ); ?>
			</h3>
		<?php } ?>

		<?php if ( ! empty( $spotlight['description'] ) ) { ?>
			<p class="frm-text-xs frm-text-grey-700 frm-m-0">
				<?php echo esc_html( $spotlight['description'] ); ?>
			</p>
		<?php } ?>
	</div>
</div>
