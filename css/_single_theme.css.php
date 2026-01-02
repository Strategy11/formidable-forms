<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$settings = FrmStylesHelper::get_settings_for_output( $style );
extract( $settings ); // phpcs:ignore WordPress.PHP.DontExtract

$is_loaded_via_ajax = $is_loaded_via_ajax ?? false;
FrmStylesPreviewHelper::get_additional_preview_style( $settings, $is_loaded_via_ajax );

$important = empty( $important_style ) ? '' : ' !important';

$submit_bg_img = FrmStylesHelper::get_submit_image_bg_url( $settings );
$use_chosen_js = FrmStylesHelper::use_chosen_js();

$pro_is_installed = FrmAppHelper::pro_is_installed();
?>
.<?php echo esc_html( $style_class ); ?>{
<?php FrmStylesHelper::output_vars( $settings, $defaults ); ?>
}

.frm_forms.<?php echo esc_html( $style_class ); ?>{
	max-width:var(--form-width)<?php echo esc_html( $important ); ?>;
	direction:var(--direction)<?php echo esc_html( $important ); ?>;
	<?php if ( 'rtl' === $direction ) { ?>
	unicode-bidi:embed;
	<?php } ?>
	<?php if ( $center_form ) { ?>
	margin:0 auto;
	<?php } ?>
}

<?php if ( $center_form ) { ?>
.frm_inline_form.<?php echo esc_html( $style_class ); ?> form{
	text-align:center;
}
<?php } ?>

<?php if ( ! empty( $field_margin ) ) { ?>
.<?php echo esc_html( $style_class ); ?> .form-field{
	margin-bottom:var(--field-margin)<?php echo esc_html( $important ); ?>;
}
<?php } ?>

<?php if ( $pro_is_installed ) { ?>
.<?php echo esc_html( $style_class ); ?> .form-field.frm_section_heading{
	margin-bottom:0<?php echo esc_html( $important ); ?>;
}
<?php } ?>

.<?php echo esc_html( $style_class ); ?> p.description,
.<?php echo esc_html( $style_class ); ?> div.description,
.<?php echo esc_html( $style_class ); ?> div.frm_description,
.<?php echo esc_html( $style_class ); ?> .frm-show-form > div.frm_description,
.<?php echo esc_html( $style_class ); ?> .frm_error,
.<?php echo esc_html( $style_class ); ?> .frm_pro_max_limit_desc{
	<?php if ( ! empty( $description_margin ) ) { ?>
		margin:<?php echo esc_html( $description_margin . $important ); ?>;
	<?php } else { ?>
		margin-top: 6px;
	<?php } ?>
	padding:0;
	<?php if ( ! empty( $font ) ) { ?>
		font-family:<?php FrmAppHelper::kses_echo( $font . $important ); ?>;
	<?php } ?>
	<?php if ( ! empty( $description_font_size ) ) { ?>
		font-size:<?php echo esc_html( $description_font_size . $important ); ?>;
	<?php } ?>
	<?php if ( ! empty( $description_color ) ) { ?>
		color:<?php echo esc_html( $description_color . $important ); ?>;
	<?php } ?>
	<?php if ( ! empty( $description_weight ) ) { ?>
		font-weight:<?php echo esc_html( $description_weight . $important ); ?>;
	<?php } ?>
	<?php if ( ! empty( $description_align ) ) { ?>
		text-align:<?php echo esc_html( $description_align . $important ); ?>;
	<?php } ?>
	<?php if ( ! empty( $description_style ) ) { ?>
		font-style:<?php echo esc_html( $description_style . $important ); ?>;
	<?php } ?>
	max-width:100%;
}

/* Left and right labels */
<?php

if ( '' === $field_height || 'auto' === $field_height ) {
	foreach ( array( 'left', 'right', 'inline' ) as $alignit ) {
		?>
.<?php echo esc_html( $style_class ); ?> .frm_<?php echo esc_html( $alignit ); ?>_container input[type=text],
.<?php echo esc_html( $style_class ); ?> .frm_<?php echo esc_html( $alignit ); ?>_container input[type=password],
.<?php echo esc_html( $style_class ); ?> .frm_<?php echo esc_html( $alignit ); ?>_container input[type=email],
.<?php echo esc_html( $style_class ); ?> .frm_<?php echo esc_html( $alignit ); ?>_container input[type=number],
.<?php echo esc_html( $style_class ); ?> .frm_<?php echo esc_html( $alignit ); ?>_container input[type=url],
.<?php echo esc_html( $style_class ); ?> .frm_<?php echo esc_html( $alignit ); ?>_container input[type=tel],
.<?php echo esc_html( $style_class ); ?> .frm_<?php echo esc_html( $alignit ); ?>_container input[type=file],
.<?php echo esc_html( $style_class ); ?> .frm_<?php echo esc_html( $alignit ); ?>_container input[type=search],
.<?php echo esc_html( $style_class ); ?> .frm_<?php echo esc_html( $alignit ); ?>_container select,
<?php } ?>
.<?php echo esc_html( $style_class ); ?> .frm_left_container select{
	height:fit-content<?php echo esc_html( $important ); ?>;
}
<?php } ?>

.<?php echo esc_html( $style_class ); ?> .frm_form_field.frm_left_container{
	grid-template-columns: <?php echo esc_html( $width ); ?> auto;
}

.<?php echo esc_html( $style_class ); ?> .frm_form_field.frm_right_container{
	grid-template-columns: auto <?php echo esc_html( $width ); ?>;
}

.frm_form_field.frm_right_container{
	grid-template-columns: auto 25%;
}

.<?php echo esc_html( $style_class ); ?> .frm_inline_container.frm_dynamic_select_container .frm_data_container,
.<?php echo esc_html( $style_class ); ?> .frm_inline_container.frm_dynamic_select_container .frm_opt_container{
	display:inline<?php echo esc_html( $important ); ?>;
}

.<?php echo esc_html( $style_class ); ?> .frm_pos_right{
	display:inline<?php echo esc_html( $important ); ?>;
	width:var(--width)<?php echo esc_html( $important ); ?>;
}

.<?php echo esc_html( $style_class ); ?> .frm_none_container .frm_primary_label,
.<?php echo esc_html( $style_class ); ?> .frm_pos_none{
	display:none<?php echo esc_html( $important ); ?>;
}

<?php if ( $pro_is_installed ) : ?>
.<?php echo esc_html( $style_class ); ?> .frm_scale label{
	<?php if ( ! empty( $check_weight ) ) { ?>
		font-weight:<?php echo esc_html( $check_weight . $important ); ?>;
	<?php } ?>
	<?php if ( ! empty( $font ) ) { ?>
		font-family:<?php FrmAppHelper::kses_echo( $font . $important ); ?>;
	<?php } ?>
	<?php if ( ! empty( $check_font_size ) ) { ?>
		font-size:<?php echo esc_html( $check_font_size . $important ); ?>;
	<?php } ?>
	<?php if ( ! empty( $check_label_color ) ) { ?>
		color:<?php echo esc_html( $check_label_color . $important ); ?>;
	<?php } ?>
}
<?php endif; ?>

.<?php echo esc_html( $style_class ); ?> input::placeholder,
.<?php echo esc_html( $style_class ); ?> textarea::placeholder{
	color:var(--text-color-disabled)<?php echo esc_html( $important ); ?>;
}

.<?php echo esc_html( $style_class ); ?> .frm_default,
.<?php echo esc_html( $style_class ); ?> input.frm_default,
.<?php echo esc_html( $style_class ); ?> textarea.frm_default,
.<?php echo esc_html( $style_class ); ?> select.frm_default,
<?php if ( $use_chosen_js ) { ?>
.<?php echo esc_html( $style_class ); ?> .chosen-container-multi .chosen-choices li.search-field .default,
.<?php echo esc_html( $style_class ); ?> .chosen-container-single .chosen-default,
<?php } ?>
.<?php echo esc_html( $style_class ); ?> .placeholder {
	color:var(--text-color-disabled)<?php echo esc_html( $important ); ?>;
}

.<?php echo esc_html( $style_class ); ?> .form-field input:not([type=file]):not([type=range]):not([readonly]):focus,
.<?php echo esc_html( $style_class ); ?> select:focus,
.<?php echo esc_html( $style_class ); ?> .form-field textarea:focus,
.<?php echo esc_html( $style_class ); ?> .frm_focus_field input[type=text],
.<?php echo esc_html( $style_class ); ?> .frm_focus_field input[type=password],
.<?php echo esc_html( $style_class ); ?> .frm_focus_field input[type=email],
.<?php echo esc_html( $style_class ); ?> .frm_focus_field input[type=number],
.<?php echo esc_html( $style_class ); ?> .frm_focus_field input[type=url],
.<?php echo esc_html( $style_class ); ?> .frm_focus_field input[type=tel],
.<?php echo esc_html( $style_class ); ?> .frm_focus_field input[type=search],
.frm_form_fields_active_style,
<?php if ( $use_chosen_js ) { ?>
.<?php echo esc_html( $style_class ); ?> .chosen-container-single.chosen-container-active .chosen-single,
.<?php echo esc_html( $style_class ); ?> .chosen-container-active .chosen-choices,
<?php } ?>
.<?php echo esc_html( $style_class ); ?> .frm_focus_field .frm-card-element.StripeElement {
	background-color:var(--bg-color-active)<?php echo esc_html( $important ); ?>;
	border-color:var(--border-color-active)<?php echo esc_html( $important ); ?>;
	color:var(--text-color);
	<?php if ( isset( $remove_box_shadow_active ) && $remove_box_shadow_active ) { ?>
	box-shadow:none;
	outline: none;
	<?php } else { ?>
	box-shadow:0px 0px 5px 0px rgba(<?php echo esc_html( FrmStylesHelper::hex2rgb( $border_color_active ) ); ?>, 0.6);
	<?php } ?>
}

<?php if ( ! $submit_style ) { ?>
	<?php if ( $pro_is_installed ) { ?>
.<?php echo esc_html( $style_class ); ?> .frm_compact .frm_dropzone.dz-clickable .dz-message,
	<?php } ?>
.<?php echo esc_html( $style_class ); ?> input[type=submit],
.<?php echo esc_html( $style_class ); ?> .frm_submit input[type=button],
.<?php echo esc_html( $style_class ); ?> .frm_submit button,
.frm_form_submit_style,
.<?php echo esc_html( $style_class ); ?> .frm-edit-page-btn {
	width:<?php echo esc_html( ( $submit_width == '' ? 'auto' : $submit_width ) . $important ); ?>;
	<?php if ( ! empty( $font ) ) { ?>
		font-family:<?php FrmAppHelper::kses_echo( $font ); ?>;
	<?php } ?>
	font-size:<?php echo esc_html( $submit_font_size . $important ); ?>;
	height:<?php echo esc_html( $submit_height . $important ); ?>;
	line-height:normal<?php echo esc_html( $important ); ?>;
	text-align:center;
	background:
	<?php
	echo esc_html( $submit_bg_color );

	if ( ! empty( $submit_bg_img ) ) {
		echo esc_html( ' url(' . $submit_bg_img . ')' );
	}
	echo esc_html( $important );
	?>
	;
	<?php if ( '' !== $submit_border_width ) : ?>
	border-width:<?php echo esc_html( $submit_border_width ); ?>;
	<?php endif; ?>
	border-color: <?php echo esc_html( $submit_border_color . $important ); ?>;
	border-style:solid;
	color:<?php echo esc_html( $submit_text_color . $important ); ?>;
	cursor:pointer;
	font-weight:<?php echo esc_html( $submit_weight . $important ); ?>;
	border-radius:<?php echo esc_html( $submit_border_radius . $important ); ?>;
	text-shadow:none;
	padding:<?php echo esc_html( $submit_padding . $important ); ?>;
	box-sizing:border-box;
	<?php if ( ! empty( $submit_shadow_color ) ) { ?>
	box-shadow:0 1px 1px <?php echo esc_html( $submit_shadow_color ); ?>;
	<?php } ?>
	margin:<?php echo esc_html( $submit_margin ); ?>;
	<?php
	// For reverse compatibility... But allow "10px 10px".
	if ( strpos( trim( $submit_margin ), ' ' ) === false ) {
		?>
		margin-left:0;
		margin-right:0;
	<?php } ?>
	vertical-align:middle;
}

	<?php if ( $pro_is_installed ) { ?>
.<?php echo esc_html( $style_class ); ?> .frm_compact .frm_dropzone.dz-clickable .dz-message{
	margin:0;
}
	<?php } ?>

	<?php if ( empty( $submit_bg_img ) ) { ?>
.<?php echo esc_html( $style_class ); ?> .frm-edit-page-btn:hover,
.<?php echo esc_html( $style_class ); ?> input[type=submit]:hover,
.<?php echo esc_html( $style_class ); ?> .frm_submit input[type=button]:hover,
.<?php echo esc_html( $style_class ); ?> .frm_submit button:hover{
	background:var(--submit-hover-bg-color)<?php echo esc_html( $important ); ?>;
	border-color:var(--submit-hover-border-color)<?php echo esc_html( $important ); ?>;
	color:var(--submit-hover-color)<?php echo esc_html( $important ); ?>;
}

.<?php echo esc_html( $style_class ); ?>.frm_center_submit .frm_submit .frm_ajax_loading{
	margin-bottom:<?php echo esc_html( FrmStylesHelper::get_bottom_value( $submit_margin ) ); ?>;
}

.<?php echo esc_html( $style_class ); ?> .frm-edit-page-btn:focus,
.<?php echo esc_html( $style_class ); ?> input[type=submit]:focus,
.<?php echo esc_html( $style_class ); ?> .frm_submit input[type=button]:focus,
.<?php echo esc_html( $style_class ); ?> .frm_submit button:focus,
.<?php echo esc_html( $style_class ); ?> input[type=submit]:active,
.<?php echo esc_html( $style_class ); ?> .frm_submit input[type=button]:active,
.<?php echo esc_html( $style_class ); ?> .frm_submit button:active{
	background:var(--submit-active-bg-color)<?php echo esc_html( $important ); ?>;
	border-color:var(--submit-active-border-color)<?php echo esc_html( $important ); ?>;
	color:var(--submit-active-color)<?php echo esc_html( $important ); ?>;
	outline: none;
}

<?php if ( $pro_is_installed ) { ?>
.<?php echo esc_html( $style_class ); ?> .frm_loading_prev .frm_prev_page,
.<?php echo esc_html( $style_class ); ?> .frm_loading_prev .frm_prev_page:hover,
.<?php echo esc_html( $style_class ); ?> .frm_loading_prev .frm_prev_page:active,
.<?php echo esc_html( $style_class ); ?> .frm_loading_prev .frm_prev_page:focus,
<?php } ?>
.<?php echo esc_html( $style_class ); ?> .frm_loading_form .frm_button_submit,
.<?php echo esc_html( $style_class ); ?> .frm_loading_form .frm_button_submit:hover,
.<?php echo esc_html( $style_class ); ?> .frm_loading_form .frm_button_submit:active,
.<?php echo esc_html( $style_class ); ?> .frm_loading_form .frm_button_submit:focus{
	color: transparent<?php echo esc_html( $important ); ?>;
	background:var(--submit-bg-color)<?php echo esc_html( $important ); ?>;
	border-color:var(--submit-bg-color)<?php echo esc_html( $important ); ?>;
}

<?php if ( $pro_is_installed ) { ?>
.<?php echo esc_html( $style_class ); ?> .frm_loading_prev .frm_prev_page:before,
<?php } ?>
.<?php echo esc_html( $style_class ); ?> .frm_loading_form .frm_button_submit:before {
	border-bottom-color:var(--submit-text-color)<?php echo esc_html( $important ); ?>;
	border-right-color:var(--submit-text-color)<?php echo esc_html( $important ); ?>;
		<?php if ( $submit_height !== 'auto' ) { ?>
			max-height:var(--submit-height)<?php echo esc_html( $important ); ?>;
		<?php } ?>
		<?php if ( $submit_width !== 'auto' ) { ?>
			max-width:var(--submit-width)<?php echo esc_html( $important ); ?>;
		<?php } ?>
}
		<?php
	}//end if
}//end if
?>

.<?php echo esc_html( $style_class ); ?>.frm_inline_top .frm_submit::before,
.<?php echo esc_html( $style_class ); ?> .frm_submit.frm_inline_submit::before {
	content:"before";
	<?php if ( ! empty( $font ) ) { ?>
		font-family:<?php FrmAppHelper::kses_echo( $font ); ?>;
	<?php } ?>
	font-size:var(--font-size)<?php echo esc_html( $important ); ?>;
	color:var(--label-color)<?php echo esc_html( $important ); ?>;
	font-weight:var(--weight)<?php echo esc_html( $important ); ?>;
	margin:0;
	padding:var(--label-padding)<?php echo esc_html( $important ); ?>;
	width:auto;
	display:block;
	visibility:hidden;
}

.<?php echo esc_html( $style_class ); ?>.frm_inline_form .frm_submit input,
.<?php echo esc_html( $style_class ); ?>.frm_inline_form .frm_submit button,
.<?php echo esc_html( $style_class ); ?> .frm_submit.frm_inline_submit input,
.<?php echo esc_html( $style_class ); ?> .frm_submit.frm_inline_submit button {
	margin: 0 !important;
}

<?php
// Only include this CSS when the math captcha plugin is active.
if ( class_exists( 'FrmCptController' ) ) :
	?>
.<?php echo esc_html( $style_class ); ?> #frm_field_cptch_number_container{
	<?php if ( ! empty( $font ) ) { ?>
		font-family:<?php FrmAppHelper::kses_echo( $font ); ?>;
	<?php } ?>
	font-size:var(--font-size)<?php echo esc_html( $important ); ?>;
	color:var(--label-color)<?php echo esc_html( $important ); ?>;
	font-weight:var(--weight)<?php echo esc_html( $important ); ?>;
	clear:both;
}
	<?php
endif;
?>

.<?php echo esc_html( $style_class ); ?> .frm_blank_field input[type=text],
.<?php echo esc_html( $style_class ); ?> .frm_blank_field input[type=password],
.<?php echo esc_html( $style_class ); ?> .frm_blank_field input[type=url],
.<?php echo esc_html( $style_class ); ?> .frm_blank_field input[type=tel],
.<?php echo esc_html( $style_class ); ?> .frm_blank_field input[type=number],
.<?php echo esc_html( $style_class ); ?> .frm_blank_field input[type=email],
.<?php echo esc_html( $style_class ); ?> .frm_blank_field input[type=checkbox],
.<?php echo esc_html( $style_class ); ?> .frm_blank_field input[type=radio],
.<?php echo esc_html( $style_class ); ?> .frm_blank_field textarea,
<?php if ( $pro_is_installed ) { ?>
.<?php echo esc_html( $style_class ); ?> .frm_blank_field .mce-edit-area iframe,
<?php } ?>
.<?php echo esc_html( $style_class ); ?> .frm_blank_field select:not(.ui-datepicker-month):not(.ui-datepicker-year),
.frm_form_fields_error_style,
.<?php echo esc_html( $style_class ); ?> .frm_blank_field .frm-g-recaptcha iframe,
.<?php echo esc_html( $style_class ); ?> .frm_blank_field .g-recaptcha iframe,
.<?php echo esc_html( $style_class ); ?> .frm_blank_field .frm-card-element.StripeElement,
<?php if ( $use_chosen_js ) { ?>
.<?php echo esc_html( $style_class ); ?> .frm_blank_field .chosen-container-multi .chosen-choices,
.<?php echo esc_html( $style_class ); ?> .frm_blank_field .chosen-container-single .chosen-single,
<?php } ?>
.<?php echo esc_html( $style_class ); ?> .frm_form_field :invalid {
	color:var(--text-color-error)<?php echo esc_html( $important ); ?>;
	background-color:var(--bg-color-error)<?php echo esc_html( $important ); ?>;
	border-color:var(--border-color-error)<?php echo esc_html( $important ); ?>;
	border-width:var(--border-width-error)<?php echo esc_html( $important ); ?>;
	border-style:var(--border-style-error)<?php echo esc_html( $important ); ?>;
}

<?php
// Only include this style when the signatures add-on is active
if ( class_exists( 'FrmSigField' ) ) :
	?>
.<?php echo esc_html( $style_class ); ?> .frm_blank_field .sigWrapper{
	border-color:var(--border-color-error) !important;
}
	<?php
endif;
?>

.<?php echo esc_html( $style_class ); ?> .frm_error,
.<?php echo esc_html( $style_class ); ?> .frm_limit_error{
	font-weight:var(--weight)<?php echo esc_html( $important ); ?>;
	color:var(--border-color-error)<?php echo esc_html( $important ); ?>;
}

.<?php echo esc_html( $style_class ); ?> .frm_error_style{
	background-color:var(--error-bg)<?php echo esc_html( $important ); ?>;
	border:1px solid var(--error-border)<?php echo esc_html( $important ); ?>;
	border-radius:var(--border-radius)<?php echo esc_html( $important ); ?>;
	color:var(--error-text)<?php echo esc_html( $important ); ?>;
	font-size:var(--error-font-size)<?php echo esc_html( $important ); ?>;
	margin:0;
	margin-bottom:var(--field-margin);
}

<?php if ( $pro_is_installed ) { ?>
.<?php echo esc_html( $style_class ); ?> #frm_loading .progress-striped .progress-bar{
	background-image:linear-gradient(45deg, <?php echo esc_html( $border_color ); ?> 25%, rgba(0, 0, 0, 0) 25%, rgba(0, 0, 0, 0) 50%, <?php echo esc_html( $border_color ); ?> 50%, <?php echo esc_html( $border_color ); ?> 75%, rgba(0, 0, 0, 0) 75%, rgba(0, 0, 0, 0));
}

.<?php echo esc_html( $style_class ); ?> #frm_loading .progress-bar{
	background-color:var(--bg-color)<?php echo esc_html( $important ); ?>;
}
<?php } ?>

.<?php echo esc_html( $style_class ); ?> .frm_form_field.frm_total_big input,
.<?php echo esc_html( $style_class ); ?> .frm_form_field.frm_total_big textarea,
.<?php echo esc_html( $style_class ); ?> .frm_form_field.frm_total input,
.<?php echo esc_html( $style_class ); ?> .frm_form_field.frm_total textarea{
	color: <?php echo esc_html( $text_color . $important ); ?>;
	background-color:transparent<?php echo esc_html( $important ); ?>;
	border:none<?php echo esc_html( $important ); ?>;
	display:inline<?php echo esc_html( $important ); ?>;
	width:auto<?php echo esc_html( $important ); ?>;
	padding:0<?php echo esc_html( $important ); ?>;
}

<?php echo strip_tags( FrmStylesController::get_custom_css( $settings ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<?php do_action( 'frm_output_single_style', $settings ); ?>
