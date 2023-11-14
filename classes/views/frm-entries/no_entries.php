<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frmcenter frm_no_entries_form frm_placeholder_block">
<?php if ( $form && isset( $form->options['no_save'] ) && $form->options['no_save'] ) { ?>
<h3><?php esc_html_e( 'This form is not set to save any entries.', 'formidable' ); ?></h3>
<p>
	<?php
	printf(
		/* translators: %1$s: Start link HTML, %2$s: End link HTML, %3$s: Line break HTML */
		esc_html__( 'If you would like to save entries in this form, go to the %1$sform Settings%2$s page %3$s and uncheck the "Do not store any entries submitted from this form" box.', 'formidable' ),
		'<a href="' . esc_url( admin_url( 'admin.php?page=formidable&frm_action=settings&id=' . absint( $form->id ) ) ) . '">',
		'</a>',
		'</br>'
	);
	?>
</p>
<?php } elseif ( $form || $has_form ) { ?>
<div class="frmcenter frm_placeholder_block">
<svg width="450" height="308" viewBox="0 0 450 308" fill="none" xmlns="http://www.w3.org/2000/svg"><g filter="url(#filter0_d)"><rect x="118" y="28" width="312" height="205" rx="10.1" fill="#fff"/></g><rect x="174" y="84" width="202" height="15" rx="2" fill="#8F99A6" fill-opacity=".2"/><rect x="174" y="69" width="179.1" height="9.4" rx="4.7" fill="#9EA9B8" fill-opacity=".7"/><rect x="174" y="132.2" width="202" height="15" rx="2" fill="#8F99A6" fill-opacity=".2"/><rect x="174" y="117" width="148" height="10" rx="5" fill="#9EA9B8" fill-opacity=".7"/><rect x="174" y="183.2" width="202" height="15" rx="2" fill="#8F99A6" fill-opacity=".2"/><rect x="174" y="168.2" width="179.1" height="9.4" rx="4.7" fill="#9EA9B8" fill-opacity=".7"/><ellipse cx="137" cy="42.2" rx="4" ry="3.8" fill="#F54242"/><ellipse cx="151" cy="42.2" rx="4" ry="3.8" fill="#F8E434"/><ellipse cx="165" cy="42.2" rx="4" ry="3.8" fill="#ADD779"/><g filter="url(#filter1_d)"><rect x="25" y="62" width="312" height="205" rx="10.1" fill="#fff"/></g><rect x="81" y="118" width="202" height="15" rx="2" fill="#8F99A6" fill-opacity=".2"/><rect x="81" y="103" width="179.1" height="9.4" rx="4.7" fill="#9EA9B8" fill-opacity=".7"/><rect x="81" y="166.2" width="202" height="15" rx="2" fill="#8F99A6" fill-opacity=".2"/><rect x="81" y="151" width="148" height="10" rx="5" fill="#9EA9B8" fill-opacity=".7"/><rect x="81" y="217.2" width="202" height="15" rx="2" fill="#8F99A6" fill-opacity=".2"/><rect x="81" y="202.2" width="179.1" height="9.4" rx="4.7" fill="#9EA9B8" fill-opacity=".7"/><ellipse cx="44" cy="76.2" rx="4" ry="3.8" fill="#F54242"/><ellipse cx="58" cy="76.2" rx="4" ry="3.8" fill="#F8E434"/><ellipse cx="72" cy="76.2" rx="4" ry="3.8" fill="#ADD779"/><defs><filter id="filter0_d" x="93.6" y=".5" width="360.9" height="253.9" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/><feOffset dy="-3.1"/><feGaussianBlur stdDeviation="12.2"/><feColorMatrix values="0 0 0 0 0.164706 0 0 0 0 0.223529 0 0 0 0 0.294118 0 0 0 0.21 0"/><feBlend in2="BackgroundImageFix" result="effect1_dropShadow"/><feBlend in="SourceGraphic" in2="effect1_dropShadow" result="shape"/></filter><filter id="filter1_d" x=".6" y="34.5" width="360.9" height="253.9" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feColorMatrix in="SourceAlpha" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/><feOffset dy="-3.1"/><feGaussianBlur stdDeviation="12.2"/><feColorMatrix values="0 0 0 0 0.164706 0 0 0 0 0.223529 0 0 0 0 0.294118 0 0 0 0.21 0"/><feBlend in2="BackgroundImageFix" result="effect1_dropShadow"/><feBlend in="SourceGraphic" in2="effect1_dropShadow" result="shape"/></filter></defs></svg>
<div class="frm_no_entries_header">
	<?php
	if ( $form ) {
		/* translators: %s: The form name */
		printf( esc_html__( 'No Entries for form: %s', 'formidable' ), esc_html( $form->name ) );
	} else {
		esc_html_e( 'No Entries found', 'formidable' );
	}
	?>
</div>
<p class="frm_no_entries_text">
	<?php
	printf(
		/* translators: %1$s: Start link HTML, %2$s: End link HTML, %3$s: Line break HTML */
		esc_html__( 'See the %1$sform documentation%2$s for instructions on publishing your form', 'formidable' ),
		'<a href="https://formidableforms.com/knowledgebase/publish-your-forms/?utm_source=WordPress&utm_medium=entries&utm_campaign=liteplugin" target="_blank">',
		'</a>'
	);
	?>
</p>
</div>
	<?php
} else {
	$title = __( 'You have not created any forms yet', 'formidable' );
	$info  = __( 'Start collecting leads and data today.', 'formidable' );
	include FrmAppHelper::plugin_path() . '/classes/views/frm-forms/_no_forms.php';
}
?>
</div>
