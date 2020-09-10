<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$settings = FrmStylesHelper::get_settings_for_output( $style );
extract( $settings );

$important = empty( $important_style ) ? '' : ' !important';

$minus_icons = FrmStylesHelper::minus_icons();
$arrow_icons = FrmStylesHelper::arrow_icons();

?>
.<?php echo esc_html( $style_class ); ?>{
<?php FrmStylesHelper::output_vars( $settings, $defaults ); ?>
}

.frm_forms.<?php echo esc_html( $style_class ); ?>{
	max-width:<?php echo esc_html( $form_width . $important ); ?>;
	direction:<?php echo esc_html( $direction . $important ); ?>;
	<?php if ( 'rtl' == $direction ) { ?>
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

<?php if ( ! empty( $label_color ) ) { ?>
.<?php echo esc_html( $style_class ); ?> .frm_icon_font{
	color:<?php echo esc_html( $label_color . $important ); ?>;
}
<?php } ?>

.<?php echo esc_html( $style_class ); ?> .frm_icon_font.frm_minus_icon:before{
	content:"\e<?php echo esc_html( isset( $minus_icons[ $repeat_icon ] ) ? $minus_icons[ $repeat_icon ]['-'] : $minus_icons[1]['-'] ); ?>";
}

.<?php echo esc_html( $style_class ); ?> .frm_icon_font.frm_plus_icon:before{
	content:"\e<?php echo esc_html( isset( $minus_icons[ $repeat_icon ] ) ? $minus_icons[ $repeat_icon ]['+'] : $minus_icons[1]['+'] ); ?>";
}

.<?php echo esc_html( $style_class ); ?> .frm_icon_font.frm_minus_icon:before,
.<?php echo esc_html( $style_class ); ?> .frm_icon_font.frm_plus_icon:before{
	<?php if ( ! empty( $submit_text_color ) ) { ?>
		color:<?php echo esc_html( $submit_text_color . $important ); ?>;
	<?php } ?>
	vertical-align:middle;
}

.<?php echo esc_html( $style_class ); ?> .frm_trigger.active .frm_icon_font.frm_arrow_icon:before{
	content:"\e<?php echo esc_html( isset( $arrow_icons[ $collapse_icon ] ) ? $arrow_icons[ $collapse_icon ]['-'] : $arrow_icons[1]['-'] ); ?>";
	<?php if ( ! empty( $section_color ) ) { ?>
		color:<?php echo esc_html( $section_color . $important ); ?>;
	<?php } ?>
}

.<?php echo esc_html( $style_class ); ?> .frm_trigger .frm_icon_font.frm_arrow_icon:before{
	content:"\e<?php echo esc_html( isset( $arrow_icons[ $collapse_icon ] ) ? $arrow_icons[ $collapse_icon ]['+'] : $arrow_icons[1]['+'] ); ?>";
	<?php if ( ! empty( $section_color ) ) { ?>
		color:<?php echo esc_html( $section_color . $important ); ?>;
	<?php } ?>
}

<?php if ( ! empty( $field_margin ) ) { ?>
.<?php echo esc_html( $style_class ); ?> .form-field{
	margin-bottom:<?php echo esc_html( $field_margin . $important ); ?>;
}
<?php } ?>

.<?php echo esc_html( $style_class ); ?> .form-field.frm_section_heading{
	margin-bottom:0<?php echo esc_html( $important ); ?>;
}

.<?php echo esc_html( $style_class ); ?> p.description,
.<?php echo esc_html( $style_class ); ?> div.description,
.<?php echo esc_html( $style_class ); ?> div.frm_description,
.<?php echo esc_html( $style_class ); ?> .frm-show-form > div.frm_description,
.<?php echo esc_html( $style_class ); ?> .frm_error{
	<?php if ( ! empty( $description_margin ) ) { ?>
		margin:<?php echo esc_html( $description_margin . $important ); ?>;
	<?php } ?>
	padding:0;
	<?php if ( ! empty( $font ) ) { ?>
		font-family:<?php echo FrmAppHelper::kses( $font . $important ); // WPCS: XSS ok. ?>;
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

$frm_settings = FrmAppHelper::get_settings();

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
	<?php if ( $frm_settings->old_css ) { ?>
		height:auto<?php echo esc_html( $important ); ?>;
	<?php } else { ?>
		height:fit-content<?php echo esc_html( $important ); ?>;
	<?php } ?>
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
	width:<?php echo esc_html( $width . $important ); ?>;
}

.<?php echo esc_html( $style_class ); ?> .frm_none_container .frm_primary_label,
.<?php echo esc_html( $style_class ); ?> .frm_pos_none{
	display:none<?php echo esc_html( $important ); ?>;
}

.<?php echo esc_html( $style_class ); ?> .frm_scale label{
	<?php if ( ! empty( $check_weight ) ) { ?>
		font-weight:<?php echo esc_html( $check_weight . $important ); ?>;
	<?php } ?>
	<?php if ( ! empty( $font ) ) { ?>
		font-family:<?php echo FrmAppHelper::kses( $font . $important ); // WPCS: XSS ok. ?>;
	<?php } ?>
	<?php if ( ! empty( $check_font_size ) ) { ?>
		font-size:<?php echo esc_html( $check_font_size . $important ); ?>;
	<?php } ?>
	<?php if ( ! empty( $check_label_color ) ) { ?>
		color:<?php echo esc_html( $check_label_color . $important ); ?>;
	<?php } ?>
}

/* These do not work if they are combined */
.<?php echo esc_html( $style_class ); ?> input::placeholder,
.<?php echo esc_html( $style_class ); ?> textarea::placeholder{
	color: <?php echo esc_html( $text_color_disabled . $important ); ?>;
}
.<?php echo esc_html( $style_class ); ?> input::-webkit-input-placeholder,
.<?php echo esc_html( $style_class ); ?> textarea::-webkit-input-placeholder{
	color: <?php echo esc_html( $text_color_disabled . $important ); ?>;
}
.<?php echo esc_html( $style_class ); ?> input::-moz-placeholder,
.<?php echo esc_html( $style_class ); ?> textarea::-moz-placeholder{
	color: <?php echo esc_html( $text_color_disabled . $important ); ?>;
}
.<?php echo esc_html( $style_class ); ?> input:-ms-input-placeholder,
<?php echo esc_html( $style_class ); ?> textarea:-ms-input-placeholder{
	color: <?php echo esc_html( $text_color_disabled . $important ); ?>;
}
.<?php echo esc_html( $style_class ); ?> input:-moz-placeholder,
.<?php echo esc_html( $style_class ); ?> textarea:-moz-placeholder{
	color: <?php echo esc_html( $text_color_disabled . $important ); ?>;
}

.<?php echo esc_html( $style_class ); ?> .frm_default,
.<?php echo esc_html( $style_class ); ?> input.frm_default,
.<?php echo esc_html( $style_class ); ?> textarea.frm_default,
.<?php echo esc_html( $style_class ); ?> select.frm_default,
.<?php echo esc_html( $style_class ); ?> .placeholder,
.<?php echo esc_html( $style_class ); ?> .chosen-container-multi .chosen-choices li.search-field .default,
.<?php echo esc_html( $style_class ); ?> .chosen-container-single .chosen-default{
	color: <?php echo esc_html( $text_color_disabled . $important ); ?>;
}

.<?php echo esc_html( $style_class ); ?> .form-field input:not([type=file]):focus,
.<?php echo esc_html( $style_class ); ?> select:focus,
.<?php echo esc_html( $style_class ); ?> textarea:focus,
.<?php echo esc_html( $style_class ); ?> .frm_focus_field input[type=text],
.<?php echo esc_html( $style_class ); ?> .frm_focus_field input[type=password],
.<?php echo esc_html( $style_class ); ?> .frm_focus_field input[type=email],
.<?php echo esc_html( $style_class ); ?> .frm_focus_field input[type=number],
.<?php echo esc_html( $style_class ); ?> .frm_focus_field input[type=url],
.<?php echo esc_html( $style_class ); ?> .frm_focus_field input[type=tel],
.<?php echo esc_html( $style_class ); ?> .frm_focus_field input[type=search],
.frm_form_fields_active_style,
.<?php echo esc_html( $style_class ); ?> .frm_focus_field .frm-card-element.StripeElement,
.<?php echo esc_html( $style_class ); ?> .chosen-container-single.chosen-container-active .chosen-single,
.<?php echo esc_html( $style_class ); ?> .chosen-container-active .chosen-choices{
	background-color:<?php echo esc_html( $bg_color_active . $important ); ?>;
	border-color:<?php echo esc_html( $border_color_active . $important ); ?>;
	<?php if ( isset( $remove_box_shadow_active ) && $remove_box_shadow_active ) { ?>
	box-shadow:none;
	<?php } else { ?>
	box-shadow:0 1px 1px rgba(0, 0, 0, 0.075) inset, 0 0 8px rgba(<?php echo esc_html( FrmStylesHelper::hex2rgb( $border_color_active ) ); ?>, 0.6);
	<?php } ?>
}

<?php if ( ! $submit_style ) { ?>
.<?php echo esc_html( $style_class ); ?> .frm_compact .frm_dropzone.dz-clickable .dz-message,
.<?php echo esc_html( $style_class ); ?> input[type=submit],
.<?php echo esc_html( $style_class ); ?> .frm_submit input[type=button],
.<?php echo esc_html( $style_class ); ?> .frm_submit button,
.frm_form_submit_style,
.<?php echo esc_html( $style_class ); ?> .frm-edit-page-btn {
	width:<?php echo esc_html( ( $submit_width == '' ? 'auto' : $submit_width ) . $important ); ?>;
	<?php if ( ! empty( $font ) ) { ?>
		font-family:<?php echo FrmAppHelper::kses( $font ); // WPCS: XSS ok. ?>;
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
	border-width:<?php echo esc_html( $submit_border_width ); ?>;
	border-color: <?php echo esc_html( $submit_border_color . $important ); ?>;
	border-style:solid;
	color:<?php echo esc_html( $submit_text_color . $important ); ?>;
	cursor:pointer;
	font-weight:<?php echo esc_html( $submit_weight . $important ); ?>;
	-moz-border-radius:<?php echo esc_html( $submit_border_radius . $important ); ?>;
	-webkit-border-radius:<?php echo esc_html( $submit_border_radius . $important ); ?>;
	border-radius:<?php echo esc_html( $submit_border_radius . $important ); ?>;
	text-shadow:none;
	padding:<?php echo esc_html( $submit_padding . $important ); ?>;
	-moz-box-sizing:border-box;
	box-sizing:border-box;
	-ms-box-sizing:border-box;
	<?php if ( ! empty( $submit_shadow_color ) ) { ?>
	-moz-box-shadow:0 1px 1px <?php echo esc_html( $submit_shadow_color ); ?>;
	-webkit-box-shadow:0 1px 1px <?php echo esc_html( $submit_shadow_color ); ?>;
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

.<?php echo esc_html( $style_class ); ?> .frm_compact .frm_dropzone.dz-clickable .dz-message{
	margin:0;
}

	<?php if ( empty( $submit_bg_img ) ) { ?>
.<?php echo esc_html( $style_class ); ?> .frm-edit-page-btn:hover,
.<?php echo esc_html( $style_class ); ?> input[type=submit]:hover,
.<?php echo esc_html( $style_class ); ?> .frm_submit input[type=button]:hover,
.<?php echo esc_html( $style_class ); ?> .frm_submit button:hover{
	background: <?php echo esc_html( $submit_hover_bg_color . $important ); ?>;
	border-color: <?php echo esc_html( $submit_hover_border_color . $important ); ?>;
	color: <?php echo esc_html( $submit_hover_color . $important ); ?>;
}

.<?php echo esc_html( $style_class ); ?>.frm_center_submit .frm_submit .frm_ajax_loading{
	margin-bottom:<?php echo esc_html( $submit_margin ); ?>;
}

.<?php echo esc_html( $style_class ); ?> .frm-edit-page-btn:focus,
.<?php echo esc_html( $style_class ); ?> input[type=submit]:focus,
.<?php echo esc_html( $style_class ); ?> .frm_submit input[type=button]:focus,
.<?php echo esc_html( $style_class ); ?> .frm_submit button:focus,
.<?php echo esc_html( $style_class ); ?> input[type=submit]:active,
.<?php echo esc_html( $style_class ); ?> .frm_submit input[type=button]:active,
.<?php echo esc_html( $style_class ); ?> .frm_submit button:active{
	background: <?php echo esc_html( $submit_active_bg_color . $important ); ?>;
	border-color: <?php echo esc_html( $submit_active_border_color . $important ); ?>;
	color: <?php echo esc_html( $submit_active_color . $important ); ?>;
}

.<?php echo esc_html( $style_class ); ?> .frm_loading_prev .frm_prev_page,
.<?php echo esc_html( $style_class ); ?> .frm_loading_prev .frm_prev_page:hover,
.<?php echo esc_html( $style_class ); ?> .frm_loading_prev .frm_prev_page:active,
.<?php echo esc_html( $style_class ); ?> .frm_loading_prev .frm_prev_page:focus,
.<?php echo esc_html( $style_class ); ?> .frm_loading_form .frm_button_submit,
.<?php echo esc_html( $style_class ); ?> .frm_loading_form .frm_button_submit:hover,
.<?php echo esc_html( $style_class ); ?> .frm_loading_form .frm_button_submit:active,
.<?php echo esc_html( $style_class ); ?> .frm_loading_form .frm_button_submit:focus{
	color: transparent <?php echo esc_html( $important ); ?>;
	background: <?php echo esc_html( $submit_bg_color . $important ); ?>;
}

.<?php echo esc_html( $style_class ); ?> .frm_loading_prev .frm_prev_page:before,
.<?php echo esc_html( $style_class ); ?> .frm_loading_form .frm_button_submit:before {
	border-bottom-color: <?php echo esc_html( $submit_text_color . $important ); ?>;
	border-right-color: <?php echo esc_html( $submit_text_color . $important ); ?>;
		<?php if ( $submit_height !== 'auto' ) { ?>
			max-height:<?php echo esc_html( $submit_height ); ?>;
		<?php } ?>
		<?php if ( $submit_width !== 'auto' ) { ?>
			max-width:<?php echo esc_html( $submit_width ); ?>;
		<?php } ?>
}
		<?php
	}
}
?>

.<?php echo esc_html( $style_class ); ?>.frm_inline_top .frm_submit::before,
.<?php echo esc_html( $style_class ); ?> .frm_submit.frm_inline_submit::before {
	content:"before";
	<?php if ( ! empty( $font ) ) { ?>
		font-family:<?php echo FrmAppHelper::kses( $font ); // WPCS: XSS ok. ?>;
	<?php } ?>
	font-size:<?php echo esc_html( $font_size . $important ); ?>;
	color:<?php echo esc_html( $label_color . $important ); ?>;
	font-weight:<?php echo esc_html( $weight . $important ); ?>;
	margin:0;
	padding:<?php echo esc_html( $label_padding . $important ); ?>;
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

.<?php echo esc_html( $style_class ); ?> #frm_field_cptch_number_container{
	<?php if ( ! empty( $font ) ) { ?>
		font-family:<?php echo FrmAppHelper::kses( $font ); // WPCS: XSS ok. ?>;
	<?php } ?>
	font-size:<?php echo esc_html( $font_size . $important ); ?>;
	color:<?php echo esc_html( $label_color . $important ); ?>;
	font-weight:<?php echo esc_html( $weight . $important ); ?>;
	clear:both;
}

.<?php echo esc_html( $style_class ); ?> .frm_blank_field input[type=text],
.<?php echo esc_html( $style_class ); ?> .frm_blank_field input[type=password],
.<?php echo esc_html( $style_class ); ?> .frm_blank_field input[type=url],
.<?php echo esc_html( $style_class ); ?> .frm_blank_field input[type=tel],
.<?php echo esc_html( $style_class ); ?> .frm_blank_field input[type=number],
.<?php echo esc_html( $style_class ); ?> .frm_blank_field input[type=email],
.<?php echo esc_html( $style_class ); ?> .frm_blank_field textarea,
.<?php echo esc_html( $style_class ); ?> .frm_blank_field .mce-edit-area iframe,
.<?php echo esc_html( $style_class ); ?> .frm_blank_field select,
.frm_form_fields_error_style,
.<?php echo esc_html( $style_class ); ?> .frm_blank_field .frm-g-recaptcha iframe,
.<?php echo esc_html( $style_class ); ?> .frm_blank_field .g-recaptcha iframe,
.<?php echo esc_html( $style_class ); ?> .frm_blank_field .frm-card-element.StripeElement,
.<?php echo esc_html( $style_class ); ?> .frm_blank_field .chosen-container-multi .chosen-choices,
.<?php echo esc_html( $style_class ); ?> .frm_blank_field .chosen-container-single .chosen-single,
.<?php echo esc_html( $style_class ); ?> .frm_form_field :invalid{
	color:<?php echo esc_html( $text_color_error . $important ); ?>;
	background-color:<?php echo esc_html( $bg_color_error . $important ); ?>;
	border-color:<?php echo esc_html( $border_color_error . $important ); ?>;
	border-width:<?php echo esc_html( $border_width_error . $important ); ?>;
	border-style:<?php echo esc_html( $border_style_error . $important ); ?>;
}

.<?php echo esc_html( $style_class ); ?> .frm_blank_field .sigWrapper{
	border-color:<?php echo esc_html( $border_color_error ); ?> !important;
}

.<?php echo esc_html( $style_class ); ?> .frm_error{
	font-weight:<?php echo esc_html( $weight . $important ); ?>;
}

.<?php echo esc_html( $style_class ); ?> .frm_blank_field label,
.<?php echo esc_html( $style_class ); ?> .frm_error{
	color:<?php echo esc_html( $border_color_error . $important ); ?>;
}

.<?php echo esc_html( $style_class ); ?> .frm_error_style{
	background-color:<?php echo esc_html( $error_bg . $important ); ?>;
	border:1px solid <?php echo esc_html( $error_border . $important ); ?>;
	border-radius:<?php echo esc_html( $border_radius . $important ); ?>;
	color: <?php echo esc_html( $error_text . $important ); ?>;
	font-size:<?php echo esc_html( $error_font_size . $important ); ?>;
	margin:0;
	margin-bottom:<?php echo esc_html( $field_margin ); ?>;
}

.<?php echo esc_html( $style_class ); ?> #frm_loading .progress-striped .progress-bar{
	background-image:linear-gradient(45deg, <?php echo esc_html( $border_color ); ?> 25%, rgba(0, 0, 0, 0) 25%, rgba(0, 0, 0, 0) 50%, <?php echo esc_html( $border_color ); ?> 50%, <?php echo esc_html( $border_color ); ?> 75%, rgba(0, 0, 0, 0) 75%, rgba(0, 0, 0, 0));
}

.<?php echo esc_html( $style_class ); ?> #frm_loading .progress-bar{
	background-color:<?php echo esc_html( $bg_color . $important ); ?>;
}

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

<?php do_action( 'frm_output_single_style', $settings ); ?>
