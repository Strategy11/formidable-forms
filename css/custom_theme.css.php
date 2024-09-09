<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! isset( $saving ) ) {
	header( 'Content-type: text/css' );

	if ( ! empty( $css ) ) {
		echo strip_tags( $css, 'all' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		FrmStylesController::maybe_hide_sample_form_error_message();
		die();
	}
}

if ( ! isset( $frm_style ) ) {
	$frm_style = new FrmStyle();
}

$styles           = $frm_style->get_all();
$default_style    = $frm_style->get_default_style( $styles );
$defaults         = FrmStylesHelper::get_settings_for_output( $default_style );
$important        = empty( $defaults['important_style'] ) ? '' : ' !important';
$pro_is_installed = FrmAppHelper::pro_is_installed();
$use_chosen_js    = FrmStylesHelper::use_chosen_js();

?>
.with_frm_style{
<?php FrmStylesHelper::output_vars( $defaults ); ?>
}

.frm_hidden,
.frm_add_form_row.frm_hidden,
.frm_remove_form_row.frm_hidden,
.with_frm_style .frm_button.frm_hidden{
	display:none;
}

.with_frm_style,
.with_frm_style form,
.with_frm_style .frm-show-form div.frm_description p{
	text-align: var(--form-align)<?php echo esc_html( $important ); ?>;
}

/* Keep this. This is used for Honeypot */
input:-webkit-autofill {
	-webkit-box-shadow: 0 0 0 30px white inset;
}

/* Form description */
.with_frm_style .frm-show-form div.frm_description p{
<?php if ( ! empty( $defaults['form_desc_size'] ) ) { ?>
	font-size: var(--form-desc-size)<?php echo esc_html( $important ); ?>;
<?php } ?>
<?php if ( ! empty( $defaults['form_desc_color'] ) ) { ?>
	color: var(--form-desc-color)<?php echo esc_html( $important ); ?>;
<?php } ?>
<?php if ( ! empty( $defaults['form_desc_margin_top'] ) ) { ?>
	margin-top: var(--form-desc-margin-top)<?php echo esc_html( $important ); ?>;
<?php } ?>
<?php if ( ! empty( $defaults['form_desc_margin_bottom'] ) ) { ?>
	margin-bottom: var(--form-desc-margin-bottom)<?php echo esc_html( $important ); ?>;
<?php } ?>
<?php if ( isset( $defaults['form_desc_padding'] ) ) { ?>
	padding: var(--form-desc-padding)<?php echo esc_html( $important ); ?>;
<?php } ?>
}

form .<?php echo esc_html( FrmHoneypot::generate_class_name() ); ?> {
	overflow: hidden;
	width: 0;
	height: 0;
	position: absolute;
}

.with_frm_style fieldset{
	min-width:0;
	display: block; /* Override 2021 theme */
}

.with_frm_style fieldset fieldset{
	border:none;
	margin:0;
	padding:0;
	background-color:transparent;
}

.with_frm_style .frm_form_fields > fieldset{
<?php if ( isset( $defaults['fieldset'] ) && ( $defaults['fieldset'] || '0' === $defaults['fieldset'] ) ) { ?>
	border-width: var(--fieldset)<?php echo esc_html( $important ); ?>;
<?php } ?>
	border-style:solid;
<?php if ( ! empty( $defaults['fieldset_color'] ) ) { ?>
	border-color: var(--fieldset-color)<?php echo esc_html( $important ); ?>;
<?php } ?>
	margin:0;
<?php if ( ! empty( $defaults['fieldset_padding'] ) ) { ?>
	padding: var(--fieldset-padding)<?php echo esc_html( $important ); ?>;
<?php } ?>
<?php if ( ! empty( $defaults['fieldset_bg_color'] ) ) { ?>
	background-color: var(--fieldset-bg-color)<?php echo esc_html( $important ); ?>;
<?php } ?>
<?php if ( ! empty( $defaults['font'] ) ) { ?>
	font-family:var(--font);
<?php } ?>
}

legend.frm_hidden{
	display:none !important;
}

.with_frm_style .frm_form_fields{
	opacity:1;
	transition: opacity 0.1s linear;
}
.with_frm_style .frm_doing_ajax{
	opacity:.5;
}

.frm_transparent{
	color:transparent;
}

.with_frm_style legend + h3,
.with_frm_style h3.frm_form_title{
<?php if ( ! empty( $defaults['title_size'] ) ) { ?>
	font-size: var(--title-size)<?php echo esc_html( $important ); ?>;
<?php } ?>
<?php if ( ! empty( $defaults['title_color'] ) ) { ?>
	color: var(--title-color)<?php echo esc_html( $important ); ?>;
<?php } ?>
<?php if ( ! empty( $defaults['font'] ) ) { ?>
	font-family: var(--font);
<?php } ?>
<?php if ( ! empty( $defaults['title_margin_top'] ) ) { ?>
	margin-top: var(--title-margin-top)<?php echo esc_html( $important ); ?>;
<?php } ?>
<?php if ( ! empty( $defaults['title_margin_bottom'] ) ) { ?>
	margin-bottom: var(--title-margin-bottom)<?php echo esc_html( $important ); ?>;
<?php } ?>
}

.with_frm_style .frm_form_field.frm_html_container,
.with_frm_style .frm_form_field .frm_show_it{
<?php if ( ! empty( $defaults['font'] ) ) { ?>
	font-family: var(--font);
<?php } ?>
<?php if ( ! empty( $defaults['form_desc_color'] ) ) { ?>
	color: var(--form-desc-color)<?php echo esc_html( $important ); ?>;
<?php } ?>
}

<?php if ( ! empty( $defaults['form_desc_size'] ) ) { ?>
.with_frm_style .frm_form_field.frm_html_container{
	font-size: var(--form-desc-size)<?php echo esc_html( $important ); ?>;
}
<?php } ?>

.with_frm_style .frm_form_field .frm_show_it{
<?php if ( ! empty( $defaults['field_font_size'] ) ) { ?>
	font-size: var(--field-font-size)<?php echo esc_html( $important ); ?>;
<?php } ?>
<?php if ( ! empty( $defaults['field_weight'] ) ) { ?>
	font-weight: var(--field-weight)<?php echo esc_html( $important ); ?>;
<?php } ?>
}

.with_frm_style .frm_required {
<?php if ( ! empty( $defaults['required_color'] ) ) { ?>
	color: var(--required-color)<?php echo esc_html( $important ); ?>;
<?php } ?>
<?php if ( ! empty( $defaults['required_weight'] ) ) { ?>
	font-weight: var(--required-weight)<?php echo esc_html( $important ); ?>;
<?php } ?>
}

<?php if ( $use_chosen_js ) { ?>
.with_frm_style .chosen-container,
<?php } ?>
.with_frm_style input[type=text],
.with_frm_style input[type=password],
.with_frm_style input[type=email],
.with_frm_style input[type=number],
.with_frm_style input[type=url],
.with_frm_style input[type=tel],
.with_frm_style input[type=search],
.with_frm_style select,
.with_frm_style textarea,
.with_frm_style .frm-card-element.StripeElement {
	font-family:var(--font)<?php echo esc_html( $important ); ?>;
<?php if ( ! empty( $defaults['field_font_size'] ) ) { ?>
	font-size: var(--field-font-size)<?php echo esc_html( $important ); ?>;
<?php } ?>
	margin-bottom:0<?php echo esc_html( $important ); ?>;
}

.with_frm_style textarea{
	vertical-align:top;
	height:auto;
}

.with_frm_style input[type=text],
.with_frm_style input[type=password],
.with_frm_style input[type=email],
.with_frm_style input[type=number],
.with_frm_style input[type=url],
.with_frm_style input[type=tel],
.with_frm_style input[type=phone],
.with_frm_style input[type=search],
.with_frm_style select,
.with_frm_style textarea,
.frm_form_fields_style,
.with_frm_style .frm_scroll_box .frm_opt_container,
.frm_form_fields_active_style,
.frm_form_fields_error_style,
.with_frm_style .frm-card-element.StripeElement,
<?php if ( $use_chosen_js ) { ?>
.with_frm_style .chosen-container-multi .chosen-choices,
.with_frm_style .chosen-container-single .chosen-single,
<?php } ?>
.with_frm_style .frm_slimselect.ss-main {
	color: var(--text-color)<?php echo esc_html( $important ); ?>;
	background-color: var(--bg-color)<?php echo esc_html( $important ); ?>;
	border-color: var(--border-color)<?php echo esc_html( $important ); ?>;
	border-width: var(--field-border-width)<?php echo esc_html( $important ); ?>;
	border-style: var(--field-border-style)<?php echo esc_html( $important ); ?>;
	border-radius: var(--border-radius)<?php echo esc_html( $important ); ?>;
	width: var(--field-width)<?php echo esc_html( $important ); ?>;
	max-width: 100%;
	font-size: var(--field-font-size)<?php echo esc_html( $important ); ?>;
	padding: var(--field-pad)<?php echo esc_html( $important ); ?>;
	box-sizing: border-box;
	outline: none<?php echo esc_html( $important ); ?>;
	font-weight: var(--field-weight);
}

<?php if ( ! empty( $important ) ) : ?>
	<?php if ( $use_chosen_js ) { ?>
	.with_frm_style .chosen-container-multi .chosen-choices,
	.with_frm_style .chosen-container-single .chosen-single,
	<?php } ?>
.with_frm_style input[type=text],
.with_frm_style input[type=password],
.with_frm_style input[type=email],
.with_frm_style input[type=number],
.with_frm_style input[type=url],
.with_frm_style input[type=tel],
.with_frm_style input[type=phone],
.with_frm_style input[type=search],
.with_frm_style textarea,
.frm_form_fields_style,
.with_frm_style .frm_scroll_box .frm_opt_container,
.frm_form_fields_active_style,
.frm_form_fields_error_style,
.with_frm_style .frm-card-element.StripeElement {
	background-image:none !important;
}
<?php endif; ?>

.with_frm_style select option {
	color: var(--text-color)<?php echo esc_html( $important ); ?>;
}

.with_frm_style select option.frm-select-placeholder {
	color: var(--text-color-disabled)<?php echo esc_html( $important ); ?>;
}

.with_frm_style input[type=radio],
.with_frm_style input[type=checkbox]{
	border-color: var(--border-color)<?php echo esc_html( $important ); ?>;
	box-shadow: var(--box-shadow)<?php echo esc_html( $important ); ?>;
	float: none;
}

.with_frm_style input[type=radio]:after,
.with_frm_style input[type=checkbox]:after {
	display: none; /* 2021 conflict */
}

.with_frm_style input[type=radio]:not(:checked):focus,
.with_frm_style input[type=checkbox]:not(:checked):focus {
	border-color: var(--border-color) !important;
}

.with_frm_style input[type=radio]:focus,
.with_frm_style input[type=checkbox]:focus {
	box-shadow:0px 0px 0px 3px rgba(<?php echo esc_html( FrmStylesHelper::hex2rgb( $defaults['border_color_active'] ) ); ?>, 0.4) !important;
}

.with_frm_style input[type=text],
.with_frm_style input[type=password],
.with_frm_style input[type=email],
.with_frm_style input[type=number],
.with_frm_style input[type=url],
.with_frm_style input[type=tel],
.with_frm_style input[type=file],
.with_frm_style input[type=search],
.with_frm_style select,
.with_frm_style .frm-card-element.StripeElement{
	min-height: var(--field-height)<?php echo esc_html( $important ); ?>;
	line-height:1.3<?php echo esc_html( $important ); ?>;
}

.with_frm_style select[multiple=multiple]{
	height:auto<?php echo esc_html( $important ); ?>;
}

.input[type=file].frm_transparent:focus,
.with_frm_style input[type=file]{
	background-color:transparent;
	border:none;
	outline:none;
	box-shadow:none;
}

.with_frm_style input[type=file]{
	color: var(--text-color)<?php echo esc_html( $important ); ?>;
	padding: 0px;
<?php if ( ! empty( $defaults['font'] ) ) { ?>
	font-family: var(--font)<?php echo esc_html( $important ); ?>;
<?php } ?>
	font-size: var(--field-font-size)<?php echo esc_html( $important ); ?>;
	display: initial;
}

.with_frm_style input[type=file].frm_transparent{
	color:transparent<?php echo esc_html( $important ); ?>;
}

.with_frm_style .wp-editor-wrap{
	width: var(--field-width)<?php echo esc_html( $important ); ?>;
	max-width:100%;
}

.with_frm_style .wp-editor-container textarea{
	border:none<?php echo esc_html( $important ); ?>;
	box-shadow:none !important;
}

.with_frm_style .mceIframeContainer{
	background-color: var(--bg-color)<?php echo esc_html( $important ); ?>;
}

.with_frm_style select{
	width: var(--auto-width)<?php echo esc_html( $important ); ?>;
	max-width:100%;
	background-position-y: calc(50% + 3px);
}

.with_frm_style input[disabled],
.with_frm_style select[disabled],
.with_frm_style textarea[disabled],
.with_frm_style input[readonly],
.with_frm_style select[readonly],
.with_frm_style textarea[readonly] {
	background-color: var(--bg-color-disabled)<?php echo esc_html( $important ); ?>;
	color: var(--text-color-disabled)<?php echo esc_html( $important ); ?>;
	border-color: var(--border-color-disabled)<?php echo esc_html( $important ); ?>;
}

.frm_preview_page:before{
	content:normal !important;
}

.frm_preview_page{
	padding:25px;
}

.with_frm_style .frm_primary_label{
	max-width:100%;
<?php if ( ! empty( $defaults['font'] ) ) { ?>
	font-family: var(--font);
<?php } ?>
<?php if ( ! empty( $defaults['font_size'] ) ) { ?>
	font-size: var(--font-size)<?php echo esc_html( $important ); ?>;
<?php } ?>
<?php if ( ! empty( $defaults['label_color'] ) ) { ?>
	color: var(--label-color)<?php echo esc_html( $important ); ?>;
<?php } ?>
<?php if ( ! empty( $defaults['weight'] ) ) { ?>
	font-weight: var(--weight)<?php echo esc_html( $important ); ?>;
<?php } ?>
<?php if ( ! empty( $defaults['align'] ) ) { ?>
	text-align: var(--align)<?php echo esc_html( $important ); ?>;
<?php } ?>
<?php if ( ! empty( $defaults['label_padding'] ) ) { ?>
	padding: var(--label-padding)<?php echo esc_html( $important ); ?>;
<?php } ?>
	margin:0;
	width:auto;
	display:block;
}

.with_frm_style .frm_top_container .frm_primary_label,
.with_frm_style .frm_hidden_container .frm_primary_label,
.with_frm_style .frm_pos_top{
	display:block;
	float:none;
	width:auto;
}

.with_frm_style .frm_inline_container .frm_primary_label{
	margin-right:10px;
}

.with_frm_style .frm_right_container .frm_primary_label,
.with_frm_style .frm_pos_right{
	display:inline;
	float:right;
	margin-left:10px;
}

.with_frm_style .frm_pos_center {
	text-align: center;
}

.with_frm_style .frm_none_container .frm_primary_label,
.with_frm_style .frm_pos_none,
.frm_pos_none,
.frm_none_container .frm_primary_label{
	display:none;
}

.with_frm_style .frm_section_heading.frm_hide_section{
	margin-top:0 !important;
}

.with_frm_style .frm_hidden_container .frm_primary_label,
.with_frm_style .frm_pos_hidden,
.frm_hidden_container .frm_primary_label{
	visibility:hidden;
	white-space:nowrap;
}

.frm_visible{
	opacity:1;
}

/* Floating labels */
.with_frm_style .frm_inside_container {
	position: relative;
	padding-top: 18px;
	padding-top: calc(0.5 * var(--field-height));
}

.with_frm_style .frm_inside_container > input,
.with_frm_style .frm_inside_container > select,
.with_frm_style .frm_inside_container > textarea {
	display: block;
}

/* These do not work if they are combined */
.with_frm_style input::placeholder,
.with_frm_style textarea::placeholder {
	font-size: var(--field-font-size)<?php echo esc_html( $important ); ?>;
}

.with_frm_style .frm_inside_container > input::-moz-placeholder,
.with_frm_style .frm_inside_container > textarea::-moz-placeholder {
	opacity: 0 !important;
	transition: opacity 0.3s ease-in;
}

.with_frm_style .frm_inside_container > input:-ms-input-placeholder,
.with_frm_style .frm_inside_container > textarea:-ms-input-placeholder {
	opacity: 0;
	transition: opacity 0.3s ease-in;
}

.with_frm_style .frm_inside_container > input::placeholder,
.with_frm_style .frm_inside_container > textarea::placeholder {
	opacity: 0;
	transition: opacity 0.3s ease-in;
}

.with_frm_style .frm_inside_container > label {
	transition: all 0.3s ease-in;

	position: absolute;
	top: 19px;
	top: calc(1px + .5 * var(--field-height));
	left: 3px;
	width: 100%;

	line-height: 1.3;
	text-overflow: ellipsis;
	overflow: hidden;
	white-space: nowrap;

	padding: 8px 12px;
	padding: var(--field-pad);

	font-size: 14px;
	font-size: var(--field-font-size);
	font-weight: normal;
	font-weight: var(--field-weight);

	pointer-events: none;
}

.with_frm_style.frm_style_lines-no-boxes .frm_inside_container > label {
	line-height: 1;
}

.with_frm_style .frm_inside_container.frm_label_float_top > label {
	top: 0;
	left: 0;
	padding: 0;
	font-size: 12px;
	font-size: calc(0.85 * var(--field-font-size));
}

/* These do not work if they are combined */
.with_frm_style .frm_inside_container.frm_label_float_top > input::-moz-placeholder,
.with_frm_style .frm_inside_container.frm_label_float_top > textarea::-moz-placeholder {
	opacity: 1 !important;
	transition: opacity 0.3s ease-in;
}

.with_frm_style .frm_inside_container.frm_label_float_top > input:-ms-input-placeholder,
.with_frm_style .frm_inside_container.frm_label_float_top > textarea:-ms-input-placeholder {
	opacity: 1;
	transition: opacity 0.3s ease-in;
}

.with_frm_style .frm_inside_container.frm_label_float_top > input::placeholder,
.with_frm_style .frm_inside_container.frm_label_float_top > textarea::placeholder {
	opacity: 1;
	transition: opacity 0.3s ease-in;
}
/* End floating label */

.with_frm_style .frm_description,
.with_frm_style .frm_pro_max_limit_desc{
	clear:both;
}

.with_frm_style input[type=number][readonly]{
	-moz-appearance: textfield;
}

.with_frm_style select[multiple="multiple"]{
	height:auto;
	line-height:normal;
}

.with_frm_style .frm_catlevel_2,
.with_frm_style .frm_catlevel_3,
.with_frm_style .frm_catlevel_4,
.with_frm_style .frm_catlevel_5{
	margin-left:18px;
}

.with_frm_style .wp-editor-container{
	border:1px solid #e5e5e5;
}

.with_frm_style .quicktags-toolbar input{
	font-size:12px !important;
}

.with_frm_style .wp-editor-container textarea{
	border:none;
}

.with_frm_style .auto_width #loginform input,
.with_frm_style .auto_width input,
.with_frm_style input.auto_width,
.with_frm_style select.auto_width,
.with_frm_style textarea.auto_width{
	width:auto<?php echo esc_html( $important ); ?>;
}

.with_frm_style .frm_repeat_buttons{
	white-space:nowrap;
}

.with_frm_style .frm_button{
	text-decoration:none !important;;
	border:1px solid #eee;
	display:inline-block;
<?php if ( ! empty( $defaults['submit_padding'] ) ) { ?>
	padding: var(--submit-padding)<?php echo esc_html( $important ); ?>;
<?php } else { ?>
	padding:5px;
<?php } ?>
<?php if ( ! empty( $defaults['border_radius'] ) ) { ?>
	border-radius:<?php echo esc_html( $defaults['border_radius'] . $important ); ?>;
	border-radius:var(--border-radius)<?php echo esc_html( $important ); ?>;
<?php } ?>
<?php if ( ! empty( $defaults['submit_font_size'] ) ) { ?>
	font-size: var(--submit-font-size)<?php echo esc_html( $important ); ?>;
<?php } ?>
<?php if ( ! empty( $defaults['font'] ) ) { ?>
	font-family: var(--font)<?php echo esc_html( $important ); ?>;
<?php } ?>
<?php if ( ! empty( $defaults['submit_weight'] ) ) { ?>
	font-weight: var(--submit-weight)<?php echo esc_html( $important ); ?>;
<?php } ?>
<?php if ( ! empty( $defaults['submit_text_color'] ) ) { ?>
	color: var(--submit-text-color)<?php echo esc_html( $important ); ?>;
<?php } ?>
<?php if ( ! empty( $defaults['submit_bg_color'] ) ) { ?>
	background: var(--submit-bg-color)<?php echo esc_html( $important ); ?>;
<?php } ?>
<?php if ( ! empty( $defaults['submit_border_width'] ) ) { ?>
	border-width: var(--submit-border-width)<?php echo esc_html( $important ); ?>;
<?php } ?>
<?php if ( ! empty( $defaults['submit_border_color'] ) ) { ?>
	border-color: var(--submit-border-color)<?php echo esc_html( $important ); ?>;
<?php } ?>
<?php if ( ! empty( $defaults['submit_height'] ) ) { ?>
	height: var(--submit-height)<?php echo esc_html( $important ); ?>;
<?php } ?>
}

<?php if ( ! empty( $defaults['submit_text_color'] ) ) { ?>
.with_frm_style .frm_button.frm_inverse{
	color:var(--submit-bg-color)<?php echo esc_html( $important ); ?>;
	background:var(--submit-text-color)<?php echo esc_html( $important ); ?>;
}
<?php } ?>

.with_frm_style .frm_submit{
	clear:both;
}

.frm_inline_form .frm_form_field,
.frm_inline_form .frm_submit{
	grid-column: span 1 / span 1;
}

.frm_inline_form .frm_submit{
	margin:0;
}

.frm_submit.frm_inline_submit input[type=submit],
.frm_submit.frm_inline_submit button,
.frm_inline_form .frm_submit input[type=submit],
.frm_inline_form .frm_submit button{
	margin-top:0;
}

.with_frm_style.frm_center_submit .frm_submit{
	text-align:center;
}

.with_frm_style.frm_center_submit .frm_flex.frm_submit {
	justify-content: center;
}

.with_frm_style .frm_inline_success .frm_submit{
	display: flex;
	flex-direction: row;
	align-items: center;
	margin: 0;
}

.with_frm_style .frm_inline_success .frm_submit .frm_message{
	flex: 1;
	margin: 0;
	padding-left: 10px;
}

.with_frm_style .frm_inline_success.frm_alignright_success .frm_submit .frm_message{
	text-align: right;
}

.with_frm_style.frm_center_submit .frm_submit input[type=submit],
.with_frm_style.frm_center_submit .frm_submit input[type=button],
.with_frm_style.frm_center_submit .frm_submit button{
	margin-bottom:8px !important;
}

.with_frm_style .frm-edit-page-btn,
.with_frm_style .frm_submit input[type=submit],
.with_frm_style .frm_submit input[type=button],
.with_frm_style .frm_submit button{
	-webkit-appearance: none;
	cursor: pointer;
}

.with_frm_style.frm_center_submit .frm_submit .frm_ajax_loading{
	display: block;
	margin: 0 auto;
}

.with_frm_style .frm_loading_prev .frm_ajax_loading,
.with_frm_style .frm_loading_form .frm_ajax_loading{
	/* keep this for reverse compatibility for old HTML */
	visibility:visible !important;
}

.with_frm_style .frm_loading_prev .frm_prev_page,
.with_frm_style .frm_loading_form .frm_button_submit {
	position: relative;
	color: transparent !important;
	text-shadow: none !important;
}

.with_frm_style .frm_loading_prev .frm_prev_page:hover,
.with_frm_style .frm_loading_prev .frm_prev_page:active,
.with_frm_style .frm_loading_prev .frm_prev_page:focus,
.with_frm_style .frm_loading_form .frm_button_submit:hover,
.with_frm_style .frm_loading_form .frm_button_submit:active,
.with_frm_style .frm_loading_form .frm_button_submit:focus {
	cursor: not-allowed;
	color: transparent;
	outline: none !important;
	box-shadow: none;
}

.with_frm_style .frm_loading_prev .frm_prev_page::before,
.with_frm_style .frm_loading_form .frm_button_submit:before {
	content: '';
	display: inline-block;
	position: absolute;
	background: transparent;
	border: 1px solid #fff;
	border-top-color: transparent;
	border-left-color: transparent;
	border-radius: 50%;
	box-sizing: border-box;
	<?php $loader_size = 12; ?>
	top: 50%;
	left: 50%;
	margin-top: -<?php echo absint( $loader_size / 2 ); ?>px;
	margin-left: -<?php echo absint( $loader_size / 2 ); ?>px;
	width: <?php echo absint( $loader_size ); ?>px;
	height: <?php echo absint( $loader_size ); ?>px;
	animation: spin 2s linear infinite;
}

.with_frm_style .frm_submit.frm_flex {
	align-items: center;
	gap: 2%;
}

.with_frm_style .frm_submit.frm_flex button.frm_button_submit ~ .frm_prev_page {
	order: -1;
}

<?php
foreach ( $styles as $style ) {
	include __DIR__ . '/_single_theme.css.php';
	unset( $style );
}

// Set it again since it may have been overridden.
$important = empty( $defaults['important_style'] ) ? '' : ' !important';
?>

.frm_ajax_loading{
	visibility:hidden;
	width:auto;
}

.frm_form_submit_style{
	height:auto;
}

a.frm_save_draft{
	cursor:pointer;
}

.with_frm_style a.frm_save_draft,
.with_frm_style a.frm_start_over{
<?php if ( ! empty( $defaults['font'] ) ) { ?>
	font-family: var(--font);
<?php } ?>
	font-size: var(--submit-font-size);
	font-weight: var(--submit-weight);
}

.horizontal_radio .frm_radio{
	margin:0 5px 0 0;
}

.horizontal_radio .frm_checkbox{
	margin:0;
	margin-right:12px;
}

.vertical_radio .frm_checkbox,
.vertical_radio .frm_radio,
.vertical_radio .frm_catlevel_1{
	display:block;
}

.horizontal_radio .frm_checkbox,
.horizontal_radio .frm_radio,
.horizontal_radio .frm_catlevel_1{
	display:inline-block;
	padding-left: 0;
}

.with_frm_style .frm_radio{
	display: var(--radio-align)<?php echo esc_html( $important ); ?>;
}

.with_frm_style .frm_checkbox{
	display: var(--check-align)<?php echo esc_html( $important ); ?>;
}

.with_frm_style .vertical_radio .frm_checkbox,
.with_frm_style .vertical_radio .frm_radio,
.vertical_radio .frm_catlevel_1{
	display:block<?php echo esc_html( $important ); ?>;
	margin-bottom: 10px;
}

.with_frm_style .horizontal_radio .frm_checkbox,
.with_frm_style .horizontal_radio .frm_radio,
.horizontal_radio .frm_catlevel_1{
	display:inline-block<?php echo esc_html( $important ); ?>;
}

.with_frm_style .frm_checkbox label,
.with_frm_style .frm_radio label{
	display: flex;
	align-items: center;
	gap: 9px;
	white-space: normal;
}

.with_frm_style .frm_checkbox label:not(.frm-label-disabled),
.with_frm_style .frm_radio label:not(.frm-label-disabled) {
	cursor: pointer;
}

.with_frm_style .vertical_radio .frm_checkbox label,
.with_frm_style .vertical_radio .frm_radio label{
	width: 100%;
}

.with_frm_style .frm_radio label,
.with_frm_style .frm_checkbox label {
<?php if ( ! empty( $defaults['font'] ) ) { ?>
	font-family: var(--font);
<?php } ?>
	font-size: var(--check-font-size)<?php echo esc_html( $important ); ?>;
	color: var(--check-label-color)<?php echo esc_html( $important ); ?>;
	font-weight: var(--check-weight)<?php echo esc_html( $important ); ?>;
	line-height: 1.3;
}

.with_frm_style .frm_radio input[type=radio],
.with_frm_style .frm_checkbox input[type=checkbox] {
	font-size: var(--check-font-size)<?php echo esc_html( $important ); ?>;
	position: static<?php echo esc_html( $important ); ?>;
}

.frm_file_container .frm_file_link,
.with_frm_style .frm_radio label .frm_file_container,
.with_frm_style .frm_checkbox label .frm_file_container{
	display:inline-block;
	margin:5px;
	vertical-align:middle;
}

.with_frm_style .frm_radio input[type=radio]
<?php if ( $pro_is_installed ) : ?>
, .with_frm_style .frm_scale input[type=radio]
<?php endif; ?>
{
	border-radius:50%;
}

.with_frm_style .frm_checkbox input[type=checkbox]{
	border-radius: calc(var(--border-radius) / 2);
}

.with_frm_style .frm_radio input[type=radio],
<?php if ( $pro_is_installed ) : ?>
.with_frm_style .frm_scale input[type=radio],
<?php endif; ?>
.with_frm_style .frm_checkbox input[type=checkbox]{
	appearance: none;
	background-color: var(--bg-color);
	flex: none;
	display:inline-block !important;
	width: 16px;
	min-width: 16px;
	height: 16px;
	color: var(--border-color);
	border: 1px solid currentColor;
	border-color: var(--border-color);
	vertical-align: middle;
	position: initial; /* override Bootstrap */
	padding: 0;
	margin: 0;
}

.frm_forms.with_frm_style .frm_fields_container .frm_radio input[type=radio]:not([disabled]):checked,
<?php if ( $pro_is_installed ) : ?>
.frm_forms.with_frm_style .frm_fields_container .frm_scale input[type=radio]:not([disabled]):checked,
<?php endif; ?>
.frm_forms.with_frm_style .frm_fields_container .frm_checkbox input[type=checkbox]:not([disabled]):checked {
	border-color: var(--border-color-active) !important;
}

.frm_forms.with_frm_style .frm_fields_container .frm_checkbox input[type=checkbox]:not([disabled]):checked {
	background-color: var(--border-color-active) !important;
}

.with_frm_style .frm_radio input[type=radio][disabled]:checked,
<?php if ( $pro_is_installed ) : ?>
.with_frm_style .frm_scale input[type=radio][disabled]:checked,
<?php endif; ?>
.with_frm_style .frm_checkbox input[type=checkbox][disabled]:checked {
	border-color: var(--border-color) !important; /* Override Style Preview */
}

.with_frm_style .frm_checkbox input[type=checkbox][disabled]:checked {
	background-color: var(--border-color) !important;
}

.with_frm_style .frm_radio input[type=radio]:checked:before,
<?php if ( $pro_is_installed ) { ?>
.with_frm_style .frm_scale input[type=radio]:checked:before,
<?php } ?>
.with_frm_style .frm_checkbox input[type=checkbox]:checked:before {
	position: static !important; /* Override Style Preview */
	content: '';
	display: block;
}

.frm_forms.with_frm_style .frm_checkbox input[type=checkbox]:before {
	width: 100% !important;
	height: 100% !important;
	background-image: url("data:image/svg+xml,%3Csvg width='12' height='9' viewBox='0 0 12 9' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M10.6667 1.5L4.25001 7.91667L1.33334 5' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E%0A") !important;
	background-size: 9px !important;
	background-repeat: no-repeat !important;
	background-position: center !important;
}

<?php if ( $pro_is_installed ) { ?>
.with_frm_style .frm_scale input[type=radio]:before,
<?php } ?>
.with_frm_style .frm_radio input[type=radio]:before {
	width: 8px;
	height: 8px;
	border-radius: 50%;
	background-color: var(--border-color-active);
	margin: 3px;
}

<?php if ( $pro_is_installed ) { ?>
.with_frm_style .frm_scale input[type=radio][disabled]:before,
<?php } ?>
.with_frm_style .frm_radio input[type=radio][disabled]:before {
	background-color: var(--border-color);
}

.with_frm_style :invalid,
.with_frm_style :-moz-submit-invalid,
.with_frm_style :-moz-ui-invalid{
	box-shadow:none;
}

.with_frm_style .frm_error_style img{
	padding-right:10px;
	vertical-align:middle;
	border:none;
}

.with_frm_style .frm_trigger{
	cursor:pointer;
}

.with_frm_style .frm_error_style,
.with_frm_style .frm_message,
.frm_success_style{
	border-radius:4px;
	padding:15px;
}

.with_frm_style .frm_message p {
	margin-bottom: 5px;
	color: var(--success-text-color)<?php echo esc_html( $important ); ?>;
}

.with_frm_style .frm_message,
.frm_success_style {
	margin: 5px 0 15px;
	border: 1px solid var(--success-border-color);
	background-color: var(--success-bg-color);
	color: var(--success-text-color)<?php echo esc_html( $important ); ?>;
	border-radius: var(--border-radius);
	font-size: var(--success-font-size)<?php echo esc_html( $important ); ?>;
}

.with_frm_style .frm_plain_success .frm_message {
	background-color: transparent;
	padding:0;
	border:none;
	font-size:inherit<?php echo esc_html( $important ); ?>;
	color:inherit<?php echo esc_html( $important ); ?>;
}

.with_frm_style .frm_plain_success .frm_message p {
	color:inherit<?php echo esc_html( $important ); ?>;
}

.frm_form_fields_style,
.frm_form_fields_active_style,
.frm_form_fields_error_style,
.frm_form_submit_style{
	width:auto;
}

.with_frm_style .frm_trigger span{
	float:left;
}

.with_frm_style table.frm-grid,
#content .with_frm_style table.frm-grid{
	border-collapse:collapse;
	border:none;
}

.frm-grid td,
.frm-grid th{
	padding:5px;
	border-width:1px;
	border-style:solid;
	<?php if ( ! empty( $defaults['border_color'] ) ) { ?>
		border-color: var(--border-color);
	<?php } ?>
	border-top:none;
	border-left:none;
	border-right:none;
}

.frm-alt-table {
	width:100%;
	border-collapse:separate;
	margin-top:0.5em;
	font-size:15px;
	border-width:1px;
}

<?php if ( ! empty( $defaults['border_color'] ) ) { ?>
.with_frm_style .frm-alt-table{
	border-color: var(--border-color);
}
<?php } ?>

.frm-alt-table th {
	width:200px;
}

.frm-alt-table tr {
	background-color:transparent;
}

.frm-alt-table th,
.frm-alt-table td {
	background-color:transparent;
	vertical-align:top;
	text-align:left;
	padding:20px;
	border-color:transparent;
}

.frm-alt-table tr:nth-child(even) {
	background-color:<?php echo esc_html( FrmStylesHelper::adjust_brightness( $defaults['border_color'], 45 ) ); ?>;
}

table.form_results.with_frm_style{
	border-style: solid;
	border-width: var(--field-border-width)<?php echo esc_html( $important ); ?>;
	border-color: var(--border-color)<?php echo esc_html( $important ); ?>;
}

table.form_results.with_frm_style tr td{
	text-align:left;
	padding:7px 9px;
<?php if ( ! empty( $defaults['text_color'] ) ) { ?>
	color: var(--text-color)<?php echo esc_html( $important ); ?>;
<?php } ?>
<?php if ( ! empty( $defaults['border_color'] ) ) { ?>
	border-top-style: solid;
	border-top-width: var(--field-border-width)<?php echo esc_html( $important ); ?>;
	border-top-color: var(--border-color)<?php echo esc_html( $important ); ?>;
<?php } ?>
}

table.form_results.with_frm_style tr.frm_even,
.frm-grid .frm_even{
	background-color:#fff;
	background-color:var(--bg-color)<?php echo esc_html( $important ); ?>;
}

<?php if ( ! empty( $defaults['bg_color'] ) ) { ?>
table.form_results.with_frm_style tr.frm_odd,
.frm-grid .frm_odd {
	background-color: var(--bg-color)<?php echo esc_html( $important ); ?>;
}
<?php } ?>

<?php if ( ! empty( $defaults['border_color'] ) ) { ?>
.frm_color_block {
	background-color:<?php echo esc_html( FrmStylesHelper::adjust_brightness( $defaults['border_color'], 45 ) ); ?>;
	padding: 40px;
}

.with_frm_style .frm-show-form .frm_color_block.frm_section_heading h3,
.frm_color_block.frm_section_heading h3 {
	border-width: 0 !important;
}
<?php } ?>

.frm_collapse .ui-icon{
	display:inline-block;
}

.frm_toggle_container{
	/* Prevent the slide and bounce */
	border:1px solid transparent;
}

.frm_toggle_container ul{
	margin:5px 0;
	padding-left:0;
	list-style-type:none;
}

.frm_toggle_container .frm_month_heading{
	text-indent:15px;
}

.frm_toggle_container .frm_month_listing{
	margin-left:40px;
}

#frm_loading{
	display:none;
	position:fixed;
	top:0;
	left:0;
	width:100%;
	height:100%;
	z-index:99999;
}

#frm_loading h3{
	font-weight:500;
	padding-bottom:15px;
	color:#fff;
	font-size:24px;
}

#frm_loading_content{
	position:fixed;
	top:20%;
	left:33%;
	width:33%;
	text-align:center;
	padding-top:30px;
	font-weight:bold;
	z-index:9999999;
}

#frm_loading img{
	max-width:100%;
}

#frm_loading .progress{
	border-radius:4px;
	box-shadow:0 1px 2px rgba(0, 0, 0, 0.1) inset;
	height:20px;
	margin-bottom:20px;
	overflow:hidden;
}

#frm_loading .progress.active .progress-bar{
	animation:2s linear 0s normal none infinite progress-bar-stripes;
}

<?php if ( ! empty( $defaults['bg_color'] ) ) { ?>
#frm_loading .progress-striped .progress-bar {
	<?php if ( ! empty( $defaults['border_color'] ) ) { ?>
		background-image: linear-gradient(45deg, var(--border-color) 25%, rgba(0, 0, 0, 0) 25%, rgba(0, 0, 0, 0) 50%, var(--border-color) 50%, var(--border-color) 75%, rgba(0, 0, 0, 0) 75%, rgba(0, 0, 0, 0));
	<?php } ?>
	background-size:40px 40px;
}
<?php } ?>

#frm_loading .progress-bar {
	background-color: var(--bg-color);
	box-shadow: 0 -1px 0 rgba(0, 0, 0, 0.15) inset;
	float: left;
	height: 100%;
	line-height: 20px;
	text-align: center;
	transition: width 0.6s ease 0s;
	width: 100%;
}

.frm_image_from_url{
	height:50px;
}

.frm-loading-img{
	background:url(../images/ajax_loader.gif) no-repeat center center;
	padding:6px 12px;
}

select.frm_loading_lookup{
	background-image: url(../images/ajax_loader.gif) !important;
	background-position: 10px;
	background-repeat: no-repeat;
	color: transparent !important;
}

<?php readfile( __DIR__ . '/frm_grids.css' ); ?>

.frm_conf_field.frm_left_container .frm_primary_label{
	display:none;
}

.wp-editor-wrap *,
.wp-editor-wrap *:after,
.wp-editor-wrap *:before{
	box-sizing:content-box;
}

.with_frm_style .frm_grid,
.with_frm_style .frm_grid_first,
.with_frm_style .frm_grid_odd{
	clear:both;
	margin-bottom:0 !important;
	padding:5px;
	border-width:1px;
	border-style:solid;
<?php if ( ! empty( $defaults['border_color'] ) ) { ?>
	border-color: var(--border-color)<?php echo esc_html( $important ); ?>;
<?php } ?>
	border-left:none;
	border-right:none;
}

.with_frm_style .frm_grid,
.with_frm_style .frm_grid_odd{
	border-top:none;
}

.frm_grid .frm_error,
.frm_grid_first .frm_error,
.frm_grid_odd .frm_error,
.frm_grid .frm_limit_error,
.frm_grid_first .frm_limit_error,
.frm_grid_odd .frm_limit_error{
	display:none;
}

.frm_grid:after,
.frm_grid_first:after,
.frm_grid_odd:after{
	visibility:hidden;
	display:block;
	font-size:0;
	content:" ";
	clear:both;
	height:0;
}

.frm_grid_first{
	margin-top:20px;
}

<?php if ( ! empty( $defaults['bg_color'] ) ) { ?>
.frm_grid_first,
.frm_grid_odd {
	background-color: var(--bg-color);
}
<?php } ?>

<?php if ( ! empty( $defaults['bg_color_active'] ) ) { ?>
.frm_grid {
	background-color: var(--bg-color-active)<?php echo esc_html( $important ); ?>;
}
<?php } ?>

.with_frm_style .frm_grid.frm_blank_field,
.with_frm_style .frm_grid_first.frm_blank_field,
.with_frm_style .frm_grid_odd.frm_blank_field{
	background-color:var(--error-bg)<?php echo esc_html( $important ); ?>;
	border-color: var(--error-border);
}

.frm_grid .frm_primary_label,
.frm_grid_first .frm_primary_label,
.frm_grid_odd .frm_primary_label,
.frm_grid .frm_radio,
.frm_grid_first .frm_radio,
.frm_grid_odd .frm_radio,
.frm_grid .frm_checkbox,
.frm_grid_first .frm_checkbox,
.frm_grid_odd .frm_checkbox{
	float:left !important;
	display:block;
	margin-top:0;
	margin-left:0 !important;
}

.frm_grid_first .frm_radio label,
.frm_grid .frm_radio label,
.frm_grid_odd .frm_radio label,
.frm_grid_first .frm_checkbox label,
.frm_grid .frm_checkbox label,
.frm_grid_odd .frm_checkbox label{
	visibility:hidden;
	white-space:nowrap;
	text-align:left;
}

.frm_grid_first .frm_radio label input,
.frm_grid .frm_radio label input,
.frm_grid_odd .frm_radio label input,
.frm_grid_first .frm_checkbox label input,
.frm_grid .frm_checkbox label input,
.frm_grid_odd .frm_checkbox label input{
	visibility:visible;
	margin:2px 0 0;
	float:right;
}

.frm_grid .frm_radio,
.frm_grid_first .frm_radio,
.frm_grid_odd .frm_radio,
.frm_grid .frm_checkbox,
.frm_grid_first .frm_checkbox,
.frm_grid_odd .frm_checkbox{
	display:inline;
}

.frm_grid_2 .frm_radio,
.frm_grid_2 .frm_checkbox,
.frm_grid_2 .frm_primary_label{
	width:48% !important;
}

.frm_grid_2 .frm_radio,
.frm_grid_2 .frm_checkbox{
	margin-right:4%;
}

.frm_grid_3 .frm_radio,
.frm_grid_3 .frm_checkbox,
.frm_grid_3 .frm_primary_label{
	width:30% !important;
}

.frm_grid_3 .frm_radio,
.frm_grid_3 .frm_checkbox{
	margin-right:3%;
}

.frm_grid_4 .frm_radio,
.frm_grid_4 .frm_checkbox{
	width:20% !important;
}

.frm_grid_4 .frm_primary_label{
	width:28% !important;
}

.frm_grid_4 .frm_radio,
.frm_grid_4 .frm_checkbox{
	margin-right:4%;
}

.frm_grid_5 .frm_primary_label,
.frm_grid_7 .frm_primary_label{
	width:24% !important;
}

.frm_grid_5 .frm_radio,
.frm_grid_5 .frm_checkbox{
	width:17% !important;
	margin-right:2%;
}

.frm_grid_6 .frm_primary_label{
	width:25% !important;
}

.frm_grid_6 .frm_radio,
.frm_grid_6 .frm_checkbox{
	width:14% !important;
	margin-right:1%;
}

.frm_grid_7 .frm_primary_label{
	width:22% !important;
}

.frm_grid_7 .frm_radio,
.frm_grid_7 .frm_checkbox{
	width:12% !important;
	margin-right:1%;
}

.frm_grid_8 .frm_primary_label{
	width:23% !important;
}

.frm_grid_8 .frm_radio,
.frm_grid_8 .frm_checkbox{
	width:10% !important;
	margin-right:1%;
}

.frm_grid_9 .frm_primary_label{
	width:20% !important;
}

.frm_grid_9 .frm_radio,
.frm_grid_9 .frm_checkbox{
	width:9% !important;
	margin-right:1%;
}

.frm_grid_10 .frm_primary_label{
	width:19% !important;
}

.frm_grid_10 .frm_radio,
.frm_grid_10 .frm_checkbox{
	width:8% !important;
	margin-right:1%;
}

.frm_form_field.frm_inline_container .frm_opt_container,
.frm_form_field.frm_right_container .frm_opt_container,
.frm_form_field.frm_left_container .frm_opt_container{
	padding-top:4px;
}

.with_frm_style .frm_inline_container.frm_grid_first .frm_primary_label,
.with_frm_style .frm_inline_container.frm_grid .frm_primary_label,
.with_frm_style .frm_inline_container.frm_grid_odd .frm_primary_label,
.with_frm_style .frm_inline_container.frm_grid_first .frm_opt_container,
.with_frm_style .frm_inline_container.frm_grid .frm_opt_container,
.with_frm_style .frm_inline_container.frm_grid_odd .frm_opt_container{
	margin-right:0;
}

.frm_form_field.frm_two_col .frm_opt_container,
.frm_form_field.frm_three_col .frm_opt_container,
.frm_form_field.frm_four_col .frm_opt_container{
	display: grid;
	grid-template-columns: repeat(2, 1fr);
	grid-auto-rows: max-content;
	grid-gap: 0 2.5%;
}

.frm_form_field.frm_three_col .frm_opt_container{
	grid-template-columns: repeat(3, 1fr);
}

.frm_form_field.frm_four_col .frm_opt_container{
	grid-template-columns: repeat(4, 1fr);
}

.frm_form_field.frm_two_col .frm_radio,
.frm_form_field.frm_two_col .frm_checkbox,
.frm_form_field.frm_three_col .frm_radio,
.frm_form_field.frm_three_col .frm_checkbox,
.frm_form_field.frm_four_col .frm_radio,
.frm_form_field.frm_four_col .frm_checkbox{
	grid-column-end: span 1;
}

.frm_form_field .frm_checkbox,
.frm_form_field .frm_radio {
	margin-top: 0;
	margin-bottom: 0;
}

.frm_form_field.frm_scroll_box .frm_opt_container{
	height:100px;
	overflow:auto;
}

.frm_html_container.frm_scroll_box,
.frm_form_field.frm_html_scroll_box {
	height: 100px;
	overflow: auto;
	background-color: var(--bg-color);
	border-color: var(--border-color);
	border-width: var(--field-border-width);
	border-style: var(--field-border-style);
	border-radius: var(--border-radius);
	width: var(--field-width);
	max-width: 100%;
	font-size: var(--field-font-size);
	padding: var(--field-pad);
	box-sizing: border-box;
	outline: none<?php echo esc_html( $important ); ?>;
	font-weight: normal;
	box-shadow: var(--box-shadow);
}

.frm_form_field.frm_total_big input,
.frm_form_field.frm_total_big textarea,
.frm_form_field.frm_total input,
.frm_form_field.frm_total textarea{
	opacity:1;
	background-color:transparent !important;
	border:none !important;
	font-weight:bold;
	width:auto !important;
	height:auto !important;
	box-shadow:none !important;
	display:inline;
	-moz-appearance:textfield;
	padding:0;
}

.frm_form_field.frm_total_big input::-webkit-outer-spin-button,
.frm_form_field.frm_total_big input::-webkit-inner-spin-button,
.frm_form_field.frm_total input::-webkit-outer-spin-button,
.frm_form_field.frm_total input::-webkit-inner-spin-button {
	-webkit-appearance: none;
}

.frm_form_field.frm_total_big input:focus,
.frm_form_field.frm_total_big textarea:focus,
.frm_form_field.frm_total input:focus,
.frm_form_field.frm_total textarea:focus{
	background-color:transparent;
	border:none;
	box-shadow:none;
}

.frm_form_field.frm_label_justify .frm_primary_label{
	text-align:justify !important;
}

.frm_form_field.frm_capitalize input,
.frm_form_field.frm_capitalize select,
.frm_form_field.frm_capitalize .frm_opt_container label{
	text-transform:capitalize;
}

.frm_clearfix:after{
	content:".";
	display:block;
	clear:both;
	visibility:hidden;
	line-height:0;
	height:0;
}

.frm_clearfix{
	display:block;
}

.with_frm_style .frm_combo_inputs_container > .frm_form_subfield-first,
.with_frm_style .frm_combo_inputs_container > .frm_form_subfield-middle,
.with_frm_style .frm_combo_inputs_container > .frm_form_subfield-last {
	margin-bottom: 0 !important;
}


<?php
/**
 * Call action so other plugins can add additional CSS.
 *
 * @param array $args {
 *     @type array $defaults
 * }
 */
do_action( 'frm_include_front_css', compact( 'defaults' ) );
?>

/* Responsive */

@media only screen and (max-width: 900px) {
	.frm_form_field .frm_repeat_grid .frm_form_field.frm_sixth .frm_primary_label,
	.frm_form_field .frm_repeat_grid .frm_form_field.frm_seventh .frm_primary_label,
	.frm_form_field .frm_repeat_grid .frm_form_field.frm_eighth .frm_primary_label{
		display: block !important;
	}
}

@media only screen and (max-width: 600px) {
	.frm_form_field.frm_four_col .frm_opt_container{
		grid-template-columns: repeat(2, 1fr);
	}

	.with_frm_style .frm_repeat_inline,
	.with_frm_style .frm_repeat_grid{
		margin: 20px 0;
	}
}

@media only screen and (max-width: 500px) {
	.frm_form_field.frm_two_col .frm_radio,
	.frm_form_field.frm_two_col .frm_checkbox,
	.frm_form_field.frm_three_col .frm_radio,
	.frm_form_field.frm_three_col .frm_checkbox{
		width: auto;
		margin-right: 0;
		float: none;
		display:block;
	}

	.frm_form_field input[type=file] {
		max-width:220px;
	}

	.with_frm_style .frm-g-recaptcha > div > div,
	.with_frm_style .g-recaptcha > div > div{
		width:inherit !important;
		display:block;
		overflow:hidden;
		max-width:302px;
		border-right:1px solid #d3d3d3;
		border-radius:4px;
		box-shadow:2px 0px 4px -1px rgba(0,0,0,.08);
	}

	.with_frm_style .g-recaptcha iframe,
	.with_frm_style .frm-g-recaptcha iframe{
		width:100%;
	}
}
<?php

echo strip_tags( FrmStylesController::get_custom_css() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
