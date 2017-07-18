<?php

$settings = FrmStylesHelper::get_settings_for_output( $style );
extract( $settings );

$important = empty($important_style) ? '' : ' !important';
$label_margin = (int) $width + 10;

$minus_icons = FrmStylesHelper::minus_icons();
$arrow_icons = FrmStylesHelper::arrow_icons();

?>

.frm_forms.<?php echo esc_html( $style_class ) ?>{
    max-width:<?php echo esc_html( $form_width . $important ) ?>;
    direction:<?php echo esc_html( $direction . $important ) ?>;
    <?php if ( 'rtl' == $direction ) { ?>
    unicode-bidi:embed;
    <?php } ?>
    <?php if ( $center_form ) { ?>
    margin:0 auto;
    <?php } ?>
}

.<?php echo esc_html( $style_class ) ?>,
.<?php echo esc_html( $style_class ) ?> form,
.<?php echo esc_html( $style_class ) ?> .frm-show-form div.frm_description p {
    text-align:<?php echo esc_html( $form_align . $important ) ?>;
}

<?php if ( $center_form ) {?>
.frm_inline_form.<?php echo esc_html( $style_class ) ?> form{
	text-align:center;
}
<?php } ?>

.<?php echo esc_html( $style_class ) ?> fieldset{
	border-width:<?php echo esc_html( $fieldset . $important ) ?>;
	border-style:solid;
	border-color:<?php echo esc_html( $fieldset_color . $important ) ?>;
    margin:0;
    padding:<?php echo esc_html( $fieldset_padding . $important ) ?>;
	background-color:<?php echo esc_html( $fieldset_bg_color ); ?>;
	font-family:<?php echo FrmAppHelper::kses( $font ) ?>;
}

.<?php echo esc_html( $style_class ) ?> legend + h3,
.<?php echo esc_html( $style_class ) ?> h3.frm_form_title{
    font-size:<?php echo esc_html( $title_size . $important ) ?>;
    color:<?php echo esc_html( $title_color . $important ) ?>;
	font-family:<?php echo FrmAppHelper::kses( $font ) ?>;
	margin-top:<?php echo esc_html( $title_margin_top . $important ) ?>;
	margin-bottom:<?php echo esc_html( $title_margin_bottom . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm-show-form  .frm_section_heading h3{
    padding:<?php echo esc_html( $section_pad . $important ) ?>;
    margin:0<?php echo esc_html( $important ) ?>;
    font-size:<?php echo esc_html( $section_font_size . $important ) ?>;
	font-family:<?php echo FrmAppHelper::kses( $font ) ?>;
    font-weight:<?php echo esc_html( $section_weight . $important ) ?>;
    color:<?php echo esc_html( $section_color . $important ) ?>;
    border:none<?php echo esc_html( $important ) ?>;
    border<?php echo esc_html( $section_border_loc ) ?>:<?php echo esc_html( $section_border_width . ' ' . $section_border_style . ' #' . $section_border_color . $important ) ?>;
    background-color:<?php echo esc_html( $section_bg_color . $important ); ?>
}

.<?php echo esc_html( $style_class ) ?> h3 .frm_<?php echo esc_html( $collapse_pos ) ?>_collapse{
    display:inline;
}
.<?php echo esc_html( $style_class ) ?> h3 .frm_<?php echo ( 'after' == $collapse_pos ) ? 'before' : 'after'; ?>_collapse{
    display:none;
}

.menu-edit #post-body-content .<?php echo esc_html( $style_class ) ?> .frm_section_heading h3{
    margin:0;
}

.<?php echo esc_html( $style_class ) ?> .frm_section_heading{
    margin-top:<?php echo esc_html( $section_mar_top . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?>  .frm-show-form .frm_section_heading .frm_section_spacing,
.menu-edit #post-body-content .<?php echo esc_html( $style_class ) ?>  .frm-show-form .frm_section_heading .frm_section_spacing{
    margin-bottom:<?php echo esc_html( $section_mar_bottom . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_repeat_sec{
	margin-bottom:<?php echo esc_html( $field_margin . $important ) ?>;
	margin-top:<?php echo esc_html( $field_margin . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> label.frm_primary_label,
.<?php echo esc_html( $style_class ) ?>.frm_login_form label{
    font-family:<?php echo FrmAppHelper::kses( $font ) ?>;
    font-size:<?php echo esc_html( $font_size . $important ) ?>;
    color:<?php echo esc_html( $label_color . $important ) ?>;
    font-weight:<?php echo esc_html( $weight . $important ) ?>;
    text-align:<?php echo esc_html( $align . $important ) ?>;
    margin:0;
    padding:<?php echo esc_html( $label_padding . $important ) ?>;
    width:auto;
    display:block;
}

.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_html_container,
.<?php echo esc_html( $style_class ) ?> .frm_form_field .frm_show_it{
	font-family:<?php echo FrmAppHelper::kses( $font . $important ) ?>;
	color:<?php echo esc_html( $form_desc_color . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_html_container{
	font-size:<?php echo esc_html( $form_desc_size . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_form_field .frm_show_it{
	font-size:<?php echo esc_html( $field_font_size . $important ) ?>;
	font-weight:<?php echo esc_html( $field_weight ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_icon_font{
    color:<?php echo esc_html( $label_color . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_icon_font.frm_minus_icon:before{
	content:"\e<?php echo esc_html( isset( $minus_icons[ $repeat_icon ] ) ? $minus_icons[ $repeat_icon ]['-'] : $minus_icons[1]['-'] ) ?>";
}

.<?php echo esc_html( $style_class ) ?> .frm_icon_font.frm_plus_icon:before{
	content:"\e<?php echo esc_html( isset( $minus_icons[ $repeat_icon ] ) ? $minus_icons[ $repeat_icon ]['+'] : $minus_icons[1]['+'] ) ?>";
}

.<?php echo esc_html( $style_class ) ?> .frm_icon_font.frm_minus_icon:before,
.<?php echo esc_html( $style_class ) ?> .frm_icon_font.frm_plus_icon:before{
	color:<?php echo esc_html( $submit_text_color . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_trigger.active .frm_icon_font.frm_arrow_icon:before{
	content:"\e<?php echo esc_html( isset( $arrow_icons[ $collapse_icon ] ) ? $arrow_icons[ $collapse_icon ]['-'] : $arrow_icons[1]['-'] ) ?>";
	color:<?php echo esc_html( $section_color . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_trigger .frm_icon_font.frm_arrow_icon:before{
	content:"\e<?php echo esc_html( isset( $arrow_icons[ $collapse_icon ] ) ? $arrow_icons[ $collapse_icon ]['+'] : $arrow_icons[1]['+'] ) ?>";
	color:<?php echo esc_html( $section_color . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .form-field{
	margin-bottom:<?php echo esc_html( $field_margin . $important ) ?>;
}
.<?php echo esc_html( $style_class ) ?> .frm_grid,
.<?php echo esc_html( $style_class ) ?> .frm_grid_first,
.<?php echo esc_html( $style_class ) ?> .frm_grid_odd {
    margin-bottom:0<?php echo esc_html( $important ) ?>;
}
.<?php echo esc_html( $style_class ) ?> .form-field.frm_section_heading{
    margin-bottom:0<?php echo esc_html( $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> p.description,
.<?php echo esc_html( $style_class ) ?> div.description,
.<?php echo esc_html( $style_class ) ?> div.frm_description,
.<?php echo esc_html( $style_class ) ?> .frm-show-form > div.frm_description,
.<?php echo esc_html( $style_class ) ?> .frm_error{
    margin:<?php echo esc_html( $description_margin . $important ) ?>;
    padding:0;
    font-family:<?php echo FrmAppHelper::kses( $font . $important ) ?>;
    font-size:<?php echo esc_html( $description_font_size . $important ) ?>;
    color:<?php echo esc_html( $description_color . $important ) ?>;
    font-weight:<?php echo esc_html( $description_weight . $important ) ?>;
    text-align:<?php echo esc_html( $description_align . $important ) ?>;
    font-style:<?php echo esc_html( $description_style . $important ) ?>;
    max-width:100%;
}

/* Form description */
.<?php echo esc_html( $style_class ) ?> .frm-show-form div.frm_description p{
    font-size:<?php echo esc_html( $form_desc_size . $important ) ?>;
    color:<?php echo esc_html( $form_desc_color . $important ) ?>;
	margin-top:<?php echo esc_html( $form_desc_margin_top . $important ) ?>;
	margin-bottom:<?php echo esc_html( $form_desc_margin_bottom . $important ) ?>;

}


/* Left and right labels */
.<?php echo esc_html( $style_class ) ?> .frm_left_container label.frm_primary_label{
	float:left;
	display:inline<?php echo esc_html( $important ) ?>;
	width:<?php echo esc_html( $width . $important ) ?>;
	max-width:33%<?php echo esc_html( $important ) ?>;
	margin-right:10px<?php echo esc_html( $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_right_container label.frm_primary_label{
	display:inline<?php echo esc_html( $important ) ?>;
	width:<?php echo esc_html( $width . $important ); ?>;
	max-width:33%<?php echo esc_html( $important ) ?>;
	margin-left:10px<?php echo esc_html( $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_left_container input:not([type=radio]):not([type=checkbox]),
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_left_container:not(.frm_dynamic_select_container) select,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_left_container textarea,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_left_container .frm_opt_container,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_left_container .frm-g-recaptcha,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_left_container .g-recaptcha,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_left_container .chosen-container,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_right_container input:not([type=radio]):not([type=checkbox]),
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_right_container:not(.frm_dynamic_select_container) select,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_right_container textarea,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_right_container .frm_opt_container,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_right_container .g-recaptcha,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_right_container .frm-g-recaptcha,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_right_container .chosen-container{
	max-width:62%<?php echo esc_html( $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_left_container .frm_combo_inputs_container .frm_form_field input,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_left_container .frm_combo_inputs_container .frm_form_field select,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_right_container .frm_combo_inputs_container .frm_form_field input,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_right_container .frm_combo_inputs_container .frm_form_field select{
	max-width:100%<?php echo esc_html( $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_left_container .frm_opt_container,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_right_container .frm_opt_container,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_left_container .g-recaptcha,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_right_container .g-recaptcha,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_left_container .frm-g-recaptcha,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_right_container .frm-g-recaptcha{
	display:inline-block<?php echo esc_html( $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_left_container > p.description::before,
.<?php echo esc_html( $style_class ) ?> .frm_left_container > div.description::before,
.<?php echo esc_html( $style_class ) ?> .frm_left_container > div.frm_description::before,
.<?php echo esc_html( $style_class ) ?> .frm_left_container > .frm_error::before,
.<?php echo esc_html( $style_class ) ?> .frm_right_container > p.description::after,
.<?php echo esc_html( $style_class ) ?> .frm_right_container > div.description::after,
.<?php echo esc_html( $style_class ) ?> .frm_right_container > div.frm_description::after,
.<?php echo esc_html( $style_class ) ?> .frm_right_container > .frm_error::after{
	content:''<?php echo esc_html( $important ) ?>;
	display:inline-block<?php echo esc_html( $important ) ?>;
	width:<?php echo esc_html( $width . $important ) ?>;
	max-width:33%<?php echo esc_html( $important ) ?>;
	margin-right:10px<?php echo esc_html( $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_left_container.frm_inline label.frm_primary_label{
	max-width:90%<?php echo esc_html( $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .form-field.frm_col_field div.frm_description{
	width:<?php echo esc_html( ( $field_width == '' ? 'auto' : $field_width ) . $important ) ?>;
    max-width:100%;
}

.<?php echo esc_html( $style_class ) ?> .frm_inline_container label.frm_primary_label,
.<?php echo esc_html( $style_class ) ?> .frm_inline_container.frm_dynamic_select_container .frm_opt_container{
    display:inline<?php echo esc_html( $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_inline_container label.frm_primary_label{
	margin-right:10px<?php echo esc_html( $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_pos_right{
    display:inline<?php echo esc_html( $important ) ?>;
    width:<?php echo esc_html( $width . $important ); ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_none_container label.frm_primary_label,
.<?php echo esc_html( $style_class ) ?> .frm_pos_none{
    display:none<?php echo esc_html( $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_scale label{
    font-weight:<?php echo esc_html( $check_weight . $important ) ?>;
    font-family:<?php echo FrmAppHelper::kses( $font . $important ) ?>;
    font-size:<?php echo esc_html( $check_font_size . $important ) ?>;
    color:<?php echo esc_html( $check_label_color . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_required{
    color:#<?php echo esc_html( $required_color . $important ); ?>;
    font-weight:<?php echo esc_html( $required_weight . $important ); ?>;
}

.<?php echo esc_html( $style_class ) ?> input[type=text],
.<?php echo esc_html( $style_class ) ?> input[type=password],
.<?php echo esc_html( $style_class ) ?> input[type=email],
.<?php echo esc_html( $style_class ) ?> input[type=number],
.<?php echo esc_html( $style_class ) ?> input[type=url],
.<?php echo esc_html( $style_class ) ?> input[type=tel],
.<?php echo esc_html( $style_class ) ?> input[type=search],
.<?php echo esc_html( $style_class ) ?> select,
.<?php echo esc_html( $style_class ) ?> textarea,
.<?php echo esc_html( $style_class ) ?> .chosen-container{
	font-family:<?php echo FrmAppHelper::kses( $font . $important ) ?>;
    font-size:<?php echo esc_html( $field_font_size ) ?>;
    margin-bottom:0<?php echo esc_html( $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> textarea{
	vertical-align:top;
}

.<?php echo esc_html( $style_class ) ?> input[type=text],
.<?php echo esc_html( $style_class ) ?> input[type=password],
.<?php echo esc_html( $style_class ) ?> input[type=email],
.<?php echo esc_html( $style_class ) ?> input[type=number],
.<?php echo esc_html( $style_class ) ?> input[type=url],
.<?php echo esc_html( $style_class ) ?> input[type=tel],
.<?php echo esc_html( $style_class ) ?> input[type=phone],
.<?php echo esc_html( $style_class ) ?> input[type=search],
.<?php echo esc_html( $style_class ) ?> select,
.<?php echo esc_html( $style_class ) ?> textarea,
.frm_form_fields_style,
.<?php echo esc_html( $style_class ) ?> .frm_scroll_box .frm_opt_container,
.frm_form_fields_active_style,
.frm_form_fields_error_style,
.<?php echo esc_html( $style_class ) ?> .chosen-container-multi .chosen-choices,
.<?php echo esc_html( $style_class ) ?> .chosen-container-single .chosen-single{
    color:<?php echo esc_html( $text_color . $important ) ?>;
	background-color:<?php echo esc_html( $bg_color . $important ); ?>;
<?php if ( ! empty($important) ) {
    echo esc_html( 'background-image:none' . $important . ';' );
}
?>
    border-color: <?php echo esc_html( $border_color . $important ) ?>;
    border-width:<?php echo esc_html( $field_border_width . $important ) ?>;
    border-style:<?php echo esc_html( $field_border_style . $important ) ?>;
    -moz-border-radius:<?php echo esc_html( $border_radius . $important ) ?>;
    -webkit-border-radius:<?php echo esc_html( $border_radius . $important ) ?>;
    border-radius:<?php echo esc_html( $border_radius . $important ) ?>;
    width:<?php echo esc_html( ( $field_width == '' ? 'auto' : $field_width ) . $important ) ?>;
    max-width:100%;
    font-size:<?php echo esc_html( $field_font_size . $important ) ?>;
    padding:<?php echo esc_html( $field_pad . $important ) ?>;
    -webkit-box-sizing:border-box;
    -moz-box-sizing:border-box;
    box-sizing:border-box;
    outline:none<?php echo esc_html( $important ) ?>;
    font-weight:<?php echo esc_html( $field_weight ) ?>;
    box-shadow:<?php echo esc_html( ( isset( $remove_box_shadow ) && $remove_box_shadow ) ? 'none' : '0 1px 1px rgba(0, 0, 0, 0.075) inset' )?>;
}

.<?php echo esc_html( $style_class ) ?> input[type=file]::-webkit-file-upload-button{
    color:<?php echo esc_html( $text_color . $important ) ?>;
	background-color:<?php echo esc_html( $bg_color . $important ); ?>;
	padding:<?php echo esc_html( $field_pad . $important ) ?>;
	border-radius:<?php echo esc_html( $border_radius . $important ) ?>;
	border-color: <?php echo esc_html( $border_color . $important ) ?>;
	border-width:<?php echo esc_html( $field_border_width . $important ) ?>;
	border-style:<?php echo esc_html( $field_border_style . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> input[type=text],
.<?php echo esc_html( $style_class ) ?> input[type=password],
.<?php echo esc_html( $style_class ) ?> input[type=email],
.<?php echo esc_html( $style_class ) ?> input[type=number],
.<?php echo esc_html( $style_class ) ?> input[type=url],
.<?php echo esc_html( $style_class ) ?> input[type=tel],
.<?php echo esc_html( $style_class ) ?> input[type=file],
.<?php echo esc_html( $style_class ) ?> input[type=search],
.<?php echo esc_html( $style_class ) ?> select{
    height:<?php echo esc_html( ( $field_height == '' ? 'auto' : $field_height ) . $important ) ?>;
    line-height:1.3<?php echo esc_html( $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> select[multiple="multiple"]{
    height:auto <?php echo esc_html( $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> input[type=file]{
    color: <?php echo esc_html( $text_color . $important ) ?>;
    padding:0px;
    font-family:<?php echo FrmAppHelper::kses( $font . $important ) ?>;
    font-size:<?php echo esc_html( $field_font_size . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> input[type=file].frm_transparent{
    color:transparent<?php echo esc_html( $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_default,
.<?php echo esc_html( $style_class ) ?> .placeholder,
.<?php echo esc_html( $style_class ) ?> .chosen-container-multi .chosen-choices li.search-field .default,
.<?php echo esc_html( $style_class ) ?> .chosen-container-single .chosen-default{
    color: <?php echo esc_html( $text_color . $important ) ?>;
    font-style:italic;
}

.<?php echo esc_html( $style_class ) ?> select{
    width:<?php echo esc_html( ( $auto_width ? 'auto' : $field_width ) . $important ) ?>;
    max-width:100%;
}

.<?php echo esc_html( $style_class ) ?> input.frm_other_input:not(.frm_other_full){
    width:auto <?php echo esc_html( $important ) ?>;
    margin-left:5px <?php echo esc_html( $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .horizontal_radio input.frm_other_input:not(.frm_other_full):not(.frm_pos_none) {
	display:inline-block<?php echo esc_html( $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_full input.frm_other_input:not(.frm_other_full){
    margin-left:0 <?php echo esc_html( $important ) ?>;
    margin-top:8px;
}

.<?php echo esc_html( $style_class ) ?> .frm_other_container select:not([multiple="multiple"]){
    width:auto;
}

.<?php echo esc_html( $style_class ) ?> .wp-editor-wrap{
    width:<?php echo esc_html( $field_width . $important ) ?>;
    max-width:100%;
}

.<?php echo esc_html( $style_class ) ?> .wp-editor-container textarea{
    border:none<?php echo esc_html( $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .mceIframeContainer{
	background-color:<?php echo esc_html( $bg_color . $important ); ?>;
}

.<?php echo esc_html( $style_class ) ?> .auto_width input,
.<?php echo esc_html( $style_class ) ?> input.auto_width,
.<?php echo esc_html( $style_class ) ?> select.auto_width,
.<?php echo esc_html( $style_class ) ?> textarea.auto_width{
    width:auto<?php echo esc_html( $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> input[disabled],
.<?php echo esc_html( $style_class ) ?> select[disabled],
.<?php echo esc_html( $style_class ) ?> textarea[disabled],
.<?php echo esc_html( $style_class ) ?> input[readonly],
.<?php echo esc_html( $style_class ) ?> select[readonly],
.<?php echo esc_html( $style_class ) ?> textarea[readonly]{
	background-color:<?php echo esc_html( $bg_color_disabled . $important ); ?>;
    color: <?php echo esc_html( $text_color_disabled . $important ) ?>;
    border-color:#<?php echo esc_html( $border_color_disabled . $important ) ?>;
}

/* These do not work if they are combined */
.<?php echo esc_html( $style_class ) ?> input::placeholder,
.<?php echo esc_html( $style_class ) ?> textarea::placeholder{
	color: <?php echo esc_html( $text_color_disabled . $important ) ?>;
}
.<?php echo esc_html( $style_class ) ?> input::-webkit-input-placeholder,
.<?php echo esc_html( $style_class ) ?> textarea::-webkit-input-placeholder{
	color: <?php echo esc_html( $text_color_disabled . $important ) ?>;
}
.<?php echo esc_html( $style_class ) ?> input::-moz-placeholder,
.<?php echo esc_html( $style_class ) ?> textarea::-moz-placeholder{
	color: <?php echo esc_html( $text_color_disabled . $important ) ?>;
}
.<?php echo esc_html( $style_class ) ?> input:-ms-input-placeholder,
<?php echo esc_html( $style_class ) ?> textarea:-ms-input-placeholder{
	color: <?php echo esc_html( $text_color_disabled . $important ) ?>;
}
.<?php echo esc_html( $style_class ) ?> input:-moz-placeholder,
.<?php echo esc_html( $style_class ) ?> textarea:-moz-placeholder{
	color: <?php echo esc_html( $text_color_disabled . $important ) ?>;
}


.<?php echo esc_html( $style_class ) ?> .form-field input:not([type=file]):focus,
.<?php echo esc_html( $style_class ) ?> select:focus,
.<?php echo esc_html( $style_class ) ?> textarea:focus,
.<?php echo esc_html( $style_class ) ?> .frm_focus_field input[type=text],
.<?php echo esc_html( $style_class ) ?> .frm_focus_field input[type=password],
.<?php echo esc_html( $style_class ) ?> .frm_focus_field input[type=email],
.<?php echo esc_html( $style_class ) ?> .frm_focus_field input[type=number],
.<?php echo esc_html( $style_class ) ?> .frm_focus_field input[type=url],
.<?php echo esc_html( $style_class ) ?> .frm_focus_field input[type=tel],
.<?php echo esc_html( $style_class ) ?> .frm_focus_field input[type=search],
.frm_form_fields_active_style,
.<?php echo esc_html( $style_class ) ?> .chosen-container-active .chosen-choices{
	background-color:<?php echo esc_html( $bg_color_active . $important ); ?>;
    border-color:#<?php echo esc_html( $border_color_active . $important ) ?>;
    <?php if ( isset( $remove_box_shadow_active ) && $remove_box_shadow_active ) { ?>
    box-shadow:none;
    <?php } else { ?>
    box-shadow:0 1px 1px rgba(0, 0, 0, 0.075) inset, 0 0 8px rgba(<?php echo esc_html( FrmStylesHelper::hex2rgb( $border_color_active ) ) ?>, 0.6);
    <?php } ?>
}

.<?php echo esc_html( $style_class ) ?> .frm_submit.frm_inline_submit::before {
	content:"before";
	font-family:<?php echo FrmAppHelper::kses( $font ) ?>;
	font-size:<?php echo esc_html( $font_size . $important ) ?>;
	color:<?php echo esc_html( $label_color . $important ) ?>;
	font-weight:<?php echo esc_html( $weight . $important ) ?>;
	margin:0;
	padding:<?php echo esc_html( $label_padding . $important ) ?>;
	width:auto;
	display:block;
	visibility:hidden;
}

.<?php echo esc_html( $style_class ) ?> .frm_submit.frm_inline_submit input,
.<?php echo esc_html( $style_class ) ?> .frm_submit.frm_inline_submit button {
	margin-top: 0 <?php echo esc_html( $important ) ?>;
}

<?php
if ( ! $submit_style ) { ?>
.<?php echo esc_html( $style_class ) ?> .frm_compact .frm_dropzone.dz-clickable .dz-message,
.<?php echo esc_html( $style_class ) ?> input[type=submit],
.<?php echo esc_html( $style_class ) ?> .frm_submit input[type=button],
.<?php echo esc_html( $style_class ) ?> .frm_submit button,
.frm_form_submit_style,
.<?php echo esc_html( $style_class ) ?>.frm_login_form input[type=submit]{
    width:<?php echo esc_html( ( $submit_width == '' ? 'auto' : $submit_width ) . $important ) ?>;
    font-family:<?php echo FrmAppHelper::kses( $font ) ?>;
    font-size:<?php echo esc_html( $submit_font_size . $important ); ?>;
    height:<?php echo esc_html( $submit_height . $important ) ?>;
    line-height:normal<?php echo esc_html( $important ) ?>;
    text-align:center;
    background: <?php echo esc_html( $submit_bg_color );
	if ( ! empty($submit_bg_img) ) {
		echo esc_html( ' url(' . $submit_bg_img . ')' );
	}
	echo esc_html( $important ); ?>;
    border-width:<?php echo esc_html( $submit_border_width ) ?>;
    border-color: <?php echo esc_html( $submit_border_color . $important ) ?>;
    border-style:solid;
    color:<?php echo esc_html( $submit_text_color . $important ) ?>;
    cursor:pointer;
    font-weight:<?php echo esc_html( $submit_weight . $important ) ?>;
    -moz-border-radius:<?php echo esc_html( $submit_border_radius . $important ) ?>;
    -webkit-border-radius:<?php echo esc_html( $submit_border_radius . $important ) ?>;
    border-radius:<?php echo esc_html( $submit_border_radius . $important ) ?>;
    text-shadow:none;
    padding:<?php echo esc_html( $submit_padding . $important ) ?>;
    -moz-box-sizing:border-box;
    box-sizing:border-box;
    -ms-box-sizing:border-box;
	<?php if ( ! empty( $submit_shadow_color ) ) { ?>
    -moz-box-shadow:0 1px 1px #<?php echo esc_html( $submit_shadow_color ); ?>;
    -webkit-box-shadow:0 1px 1px #<?php echo esc_html( $submit_shadow_color ); ?>;
    box-shadow:0 1px 1px #<?php echo esc_html( $submit_shadow_color ); ?>;
	<?php } ?>
	margin:<?php echo esc_html( $submit_margin ) ?>;
	margin-left:0;
	margin-right:0;
    vertical-align:middle;
}

.<?php echo esc_html( $style_class ) ?> .frm_compact .frm_dropzone.dz-clickable .dz-message{
	margin:0;
}

<?php
	if ( empty( $submit_bg_img ) ) {
?>.<?php echo esc_html( $style_class ) ?> input[type=submit]:hover,
.<?php echo esc_html( $style_class ) ?> .frm_submit input[type=button]:hover,
.<?php echo esc_html( $style_class ) ?> .frm_submit button:hover,
.<?php echo esc_html( $style_class ) ?>.frm_login_form input[type=submit]:hover{
    background: <?php echo esc_html( $submit_hover_bg_color . $important ) ?>;
    border-color: <?php echo esc_html( $submit_hover_border_color . $important ) ?>;
    color: <?php echo esc_html( $submit_hover_color . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?>.frm_center_submit .frm_submit .frm_ajax_loading{
    margin-bottom:<?php echo esc_html( $submit_margin ) ?>;
}

.<?php echo esc_html( $style_class ) ?> input[type=submit]:focus,
.<?php echo esc_html( $style_class ) ?> .frm_submit input[type=button]:focus,
.<?php echo esc_html( $style_class ) ?> .frm_submit button:focus,
.<?php echo esc_html( $style_class ) ?>.frm_login_form input[type=submit]:focus,
.<?php echo esc_html( $style_class ) ?> input[type=submit]:active,
.<?php echo esc_html( $style_class ) ?> .frm_submit input[type=button]:active,
.<?php echo esc_html( $style_class ) ?> .frm_submit button:active,
.<?php echo esc_html( $style_class ) ?>.frm_login_form input[type=submit]:active{
    background: <?php echo esc_html( $submit_active_bg_color . $important ) ?>;
    border-color: <?php echo esc_html( $submit_active_border_color . $important ) ?>;
    color: <?php echo esc_html( $submit_active_color . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_loading_form .frm_button_submit,
.<?php echo esc_html( $style_class ) ?> .frm_loading_form .frm_button_submit:hover,
.<?php echo esc_html( $style_class ) ?> .frm_loading_form .frm_button_submit:active,
.<?php echo esc_html( $style_class ) ?> .frm_loading_form .frm_button_submit:focus{
	color: transparent <?php echo esc_html( $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_loading_form .frm_button_submit:before {
	border-bottom-color: <?php echo esc_html( $submit_text_color . $important ) ?>;
	border-right-color: <?php echo esc_html( $submit_text_color . $important ) ?>;
	max-height:<?php echo esc_html( $submit_height ) ?>;
	max-width:<?php echo esc_html( $submit_height ) ?>;
}
<?php
    }
}
?>

.<?php echo esc_html( $style_class ) ?> a.frm_save_draft{
    font-family:<?php echo FrmAppHelper::kses( $font ) ?>;
    font-size:<?php echo esc_html( $submit_font_size ) ?>;
    font-weight:<?php echo esc_html( $submit_weight ) ?>;
}

.<?php echo esc_html( $style_class ) ?> #frm_field_cptch_number_container{
    font-family:<?php echo FrmAppHelper::kses( $font ) ?>;
    font-size:<?php echo esc_html( $font_size . $important ) ?>;
    color:<?php echo esc_html( $label_color . $important ) ?>;
    font-weight:<?php echo esc_html( $weight . $important ) ?>;
    clear:both;
}

.<?php echo esc_html( $style_class ) ?> .frm_radio{
    display:<?php echo esc_html( $radio_align . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .horizontal_radio .frm_radio{
    margin:0 5px 0 0<?php echo esc_html( $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_checkbox{
    display:<?php echo esc_html( $check_align . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .vertical_radio .frm_checkbox,
.<?php echo esc_html( $style_class ) ?> .vertical_radio .frm_radio,
.vertical_radio .frm_catlevel_1{
	display:block<?php echo esc_html( $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .horizontal_radio .frm_checkbox,
.<?php echo esc_html( $style_class ) ?> .horizontal_radio .frm_radio,
.horizontal_radio .frm_catlevel_1{
    display:inline-block<?php echo esc_html( $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_radio label,
.<?php echo esc_html( $style_class ) ?> .frm_checkbox label{
    font-family:<?php echo FrmAppHelper::kses( $font . $important ) ?>;
    font-size:<?php echo esc_html( $check_font_size . $important ) ?>;
    color:<?php echo esc_html( $check_label_color . $important ) ?>;
    font-weight:<?php echo esc_html( $check_weight . $important ) ?>;
    display:inline;
	white-space:normal;
}

.<?php echo esc_html( $style_class ) ?> .frm_radio input[type=radio],
.<?php echo esc_html( $style_class ) ?> .frm_checkbox input[type=checkbox] {
	font-size: <?php echo esc_html( $check_font_size . $important ) ?>;
	position: static<?php echo esc_html( $important ) ?>;;
}

.<?php echo esc_html( $style_class ) ?> .frm_blank_field input[type=text],
.<?php echo esc_html( $style_class ) ?> .frm_blank_field input[type=password],
.<?php echo esc_html( $style_class ) ?> .frm_blank_field input[type=url],
.<?php echo esc_html( $style_class ) ?> .frm_blank_field input[type=tel],
.<?php echo esc_html( $style_class ) ?> .frm_blank_field input[type=number],
.<?php echo esc_html( $style_class ) ?> .frm_blank_field input[type=email],
.<?php echo esc_html( $style_class ) ?> .frm_blank_field textarea,
.<?php echo esc_html( $style_class ) ?> .frm_blank_field .mce-edit-area iframe,
.<?php echo esc_html( $style_class ) ?> .frm_blank_field select,
.frm_form_fields_error_style,
.<?php echo esc_html( $style_class ) ?> .frm_blank_field .frm-g-recaptcha iframe,
.<?php echo esc_html( $style_class ) ?> .frm_blank_field .g-recaptcha iframe,
.<?php echo esc_html( $style_class ) ?> .frm_blank_field .chosen-container-multi .chosen-choices,
.<?php echo esc_html( $style_class ) ?> .frm_form_field :invalid{
    color:#<?php echo esc_html( $text_color_error . $important ) ?>;
	background-color:<?php echo esc_html( $bg_color_error . $important ); ?>;
    border-color:#<?php echo esc_html( $border_color_error . $important ) ?>;
    border-width:<?php echo esc_html( $border_width_error . $important ) ?>;
    border-style:<?php echo esc_html( $border_style_error . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_error{
    font-weight:<?php echo esc_html( $weight . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_blank_field label,
.<?php echo esc_html( $style_class ) ?> .frm_error{
    color:#<?php echo esc_html( $border_color_error . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_error_style{
	background-color:<?php echo esc_html( $error_bg . $important ); ?>;
    border:1px solid #<?php echo esc_html( $error_border . $important ) ?>;
	border-radius:<?php echo esc_html( $border_radius . $important ) ?>;
    color: <?php echo esc_html( $error_text . $important ) ?>;
    font-size:<?php echo esc_html( $error_font_size . $important ) ?>;
    margin:0;
    margin-bottom:<?php echo esc_html( $field_margin ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_message,
.frm_success_style{
    border:1px solid #<?php echo esc_html( $success_border_color ) ?>;
	background-color:<?php echo esc_html( $success_bg_color . $important ); ?>;
	color:#<?php echo esc_html( $success_text_color . $important ) ?>;
	border-radius:<?php echo esc_html( $border_radius . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_message p{
	color:#<?php echo esc_html( $success_text_color . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_message{
    margin:5px 0 15px;
    font-size:<?php echo esc_html( $success_font_size . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm-grid td,
.frm-grid th{
    border-color:<?php echo esc_html( $border_color ) ?>;
}

.form_results.<?php echo esc_html( $style_class ) ?>{
    border:<?php echo esc_html( $field_border_width ) ?> solid <?php echo esc_html( $border_color . $important ) ?>;
}

.form_results.<?php echo esc_html( $style_class ) ?> tr td{
    color: <?php echo esc_html( $text_color . $important ) ?>;
    border-top:<?php echo esc_html( $field_border_width ) ?> solid <?php echo esc_html( $border_color . $important ) ?>;
}

.form_results.<?php echo esc_html( $style_class ) ?> tr.frm_even,
.frm-grid .frm_even{
	background-color:<?php echo esc_html( $bg_color . $important ); ?>;
}

.<?php echo esc_html( $style_class ) ?> #frm_loading .progress-striped .progress-bar{
    background-image:linear-gradient(45deg, <?php echo esc_html( $border_color ) ?> 25%, rgba(0, 0, 0, 0) 25%, rgba(0, 0, 0, 0) 50%, #<?php echo esc_html( $border_color ) ?> 50%, <?php echo esc_html( $border_color ) ?> 75%, rgba(0, 0, 0, 0) 75%, rgba(0, 0, 0, 0));
}

.<?php echo esc_html( $style_class ) ?> #frm_loading .progress-bar{
	background-color:<?php echo esc_html( $bg_color . $important ); ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_grid,
.<?php echo esc_html( $style_class ) ?> .frm_grid_first,
.<?php echo esc_html( $style_class ) ?> .frm_grid_odd{
    border-color: <?php echo esc_html( $border_color ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_grid.frm_blank_field,
.<?php echo esc_html( $style_class ) ?> .frm_grid_first.frm_blank_field,
.<?php echo esc_html( $style_class ) ?> .frm_grid_odd.frm_blank_field{
	background-color:<?php echo esc_html( $error_bg . $important ); ?>;
    border-color:#<?php echo esc_html( $error_border ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_grid_first,
.<?php echo esc_html( $style_class ) ?> .frm_grid_odd{
	background-color:<?php echo esc_html( $bg_color . $important ); ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_grid{
	background-color:<?php echo esc_html( $bg_color_active . $important ); ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_html_scroll_box{
	background-color:<?php echo esc_html( $bg_color . $important ); ?>;
    border-color: <?php echo esc_html( $border_color . $important ) ?>;
    border-width:<?php echo esc_html( $field_border_width . $important ) ?>;
    border-style:<?php echo esc_html( $field_border_style . $important ) ?>;
    -moz-border-radius:<?php echo esc_html( $border_radius . $important ) ?>;
    -webkit-border-radius:<?php echo esc_html( $border_radius . $important ) ?>;
    border-radius:<?php echo esc_html( $border_radius . $important ) ?>;
    width:<?php echo esc_html( ( $field_width == '' ? 'auto' : $field_width ) . $important ) ?>;
    font-size:<?php echo esc_html( $field_font_size . $important ) ?>;
    padding:<?php echo esc_html( $field_pad . $important ) ?>;
    outline:none<?php echo esc_html( $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_total input,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_total textarea{
    color: <?php echo esc_html( $text_color . $important ) ?>;
    background-color:transparent<?php echo esc_html( $important ) ?>;
    border:none<?php echo esc_html( $important ) ?>;
    display:inline<?php echo esc_html( $important ) ?>;
	width:auto<?php echo esc_html( $important ) ?>;
	padding:0<?php echo esc_html( $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_text_block .frm_checkbox label,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_text_block .frm_radio label{
	padding-left:20px;
	display:block;
}

.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_text_block .frm_checkbox input[type=checkbox],
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_text_block .frm_radio input[type=radio]{
	margin-left:-20px;
}

.<?php echo esc_html( $style_class ) ?> .frm_button{
    padding:<?php echo esc_html( $submit_padding . $important ) ?>;
    -moz-border-radius:<?php echo esc_html( $border_radius . $important ) ?>;
    -webkit-border-radius:<?php echo esc_html( $border_radius . $important ) ?>;
    border-radius:<?php echo esc_html( $border_radius . $important ) ?>;
    font-size:<?php echo esc_html( $submit_font_size . $important ) ?>;
    font-family:<?php echo FrmAppHelper::kses( $font . $important ) ?>;
    font-weight:<?php echo esc_html( $submit_weight . $important ) ?>;
    color:<?php echo esc_html( $submit_text_color . $important ) ?>;
    background: <?php echo esc_html( $submit_bg_color . $important ) ?>;
    border-width:<?php echo esc_html( $submit_border_width ) ?>;
    border-color: <?php echo esc_html( $submit_border_color . $important ) ?>;
	height:<?php echo esc_html( $submit_height . $important ) ?>;
}
.<?php echo esc_html( $style_class ) ?> .frm_button .frm_icon_font:before{
    font-size:<?php echo esc_html( $submit_font_size . $important ) ?>;
}

/* Dropzone */
.<?php echo esc_html( $style_class ) ?> .frm_dropzone{
	border-color: <?php echo esc_html( $border_color . $important ) ?>;
	border-radius:<?php echo esc_html( $border_radius . $important ) ?>;
	color: <?php echo esc_html( $text_color . $important ) ?>;
	background-color:<?php echo esc_html( $bg_color . $important ); ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_dropzone .frm_upload_icon:before,
.<?php echo esc_html( $style_class ) ?> .frm_dropzone .dz-remove{
	color: <?php echo esc_html( $text_color . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_blank_field .frm_dropzone{
	border-color:#<?php echo esc_html( $border_color_error . $important ) ?>;
	color:#<?php echo esc_html( $text_color_error . $important ) ?>;
	background-color:<?php echo esc_html( $bg_color_error . $important ); ?>;
}

/* RTL Grids */
<?php if ( 'rtl' == $direction ) { ?>
.<?php echo esc_html( $style_class ) ?> .frm_form_fields div.rating-cancel,
.<?php echo esc_html( $style_class ) ?> .frm_form_fields div.star-rating{
    float:right;
}

.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_half,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_third,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_two_thirds,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_fourth,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_fifth,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_sixth,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_seventh,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_eighth,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_inline,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_left_half,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_left_third,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_left_two_thirds,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_left_fourth,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_left_fifth,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_left_inline,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_first_half,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_first_third,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_first_two_thirds,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_first_fourth,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_first_fifth,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_first_inline,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_right_half,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_right_third,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_right_two_thirds,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_right_fourth,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_right_fifth,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_right_inline,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_last_half,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_last_third,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_last_two_thirds,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_last_fourth,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_last_fifth,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_last_inline{
    float:right;
    margin-right:2.5%;
    margin-left:0;
	clear:none;
}

.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_left_half,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_first_half,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_first,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_left_third,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_first_third,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_first_two_thirds,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_left_two_thirds,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_first_fourth,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_left_fourth,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_left_fifth,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_first_fifth,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_left_inline,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_first_inline{
	clear:right;
    margin-right:0;
}

.<?php echo esc_html( $style_class ) ?> .frm_grid .frm_primary_label,
.<?php echo esc_html( $style_class ) ?> .frm_grid_first .frm_primary_label,
.<?php echo esc_html( $style_class ) ?> .frm_grid_odd .frm_primary_label,
.<?php echo esc_html( $style_class ) ?> .frm_grid .frm_radio,
.<?php echo esc_html( $style_class ) ?> .frm_grid_first .frm_radio,
.<?php echo esc_html( $style_class ) ?> .frm_grid_odd .frm_radio,
.<?php echo esc_html( $style_class ) ?> .frm_grid .frm_checkbox,
.<?php echo esc_html( $style_class ) ?> .frm_grid_first .frm_checkbox,
.<?php echo esc_html( $style_class ) ?> .frm_grid_odd .frm_checkbox{
    float:right !important;
    margin-right:0 !important;
}

.<?php echo esc_html( $style_class ) ?> .frm_grid_first .frm_radio label input,
.<?php echo esc_html( $style_class ) ?> .frm_grid .frm_radio label input,
.<?php echo esc_html( $style_class ) ?> .frm_grid_odd .frm_radio label input,
.<?php echo esc_html( $style_class ) ?> .frm_grid_first .frm_checkbox label input,
.<?php echo esc_html( $style_class ) ?> .frm_grid .frm_checkbox label input,
.<?php echo esc_html( $style_class ) ?> .frm_grid_odd .frm_checkbox label input{
    float:left;
}

.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_two_col .frm_radio,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_three_col .frm_radio,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_four_col .frm_radio,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_two_col .frm_checkbox,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_three_col .frm_checkbox,
.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_four_col .frm_checkbox{
    float:right;
}

.<?php echo esc_html( $style_class ) ?> .frm_catlevel_2,
.<?php echo esc_html( $style_class ) ?> .frm_catlevel_3,
.<?php echo esc_html( $style_class ) ?> .frm_catlevel_4,
.<?php echo esc_html( $style_class ) ?> .frm_catlevel_5{
	margin-right:18px;
}
<?php } ?>
/* Start Chosen */
.<?php echo esc_html( $style_class ) ?> .chosen-container{
    font-size:<?php echo esc_html( $field_font_size . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .chosen-container .chosen-results li,
.<?php echo esc_html( $style_class ) ?> .chosen-container .chosen-results li span{
	color:<?php echo esc_html( $text_color . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .chosen-container-single .chosen-single{
    height:<?php echo esc_html( ( $field_height == 'auto' || $field_height == '' ) ? '25px' : $field_height ) ?>;
    line-height:1.3<?php echo esc_html( $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .chosen-container-single .chosen-single div{
<?php
    // calculate the top position based on field padding
    $top_pad = explode(' ', $field_pad);
    $top_pad = reset($top_pad); // the top padding is listed first
    $pad_unit = preg_replace('/[0-9]+/', '', $top_pad); //px, em, rem...
    $top_margin = (int) str_replace($pad_unit, '', $top_pad) / 2;
?>
    top:<?php echo esc_html( $top_margin . $pad_unit . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .chosen-container-single .chosen-search input[type="text"]{
    height:<?php echo esc_html( ( $field_height == 'auto' || $field_height == '' ) ? 'auto' : $field_height ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .chosen-container-multi .chosen-choices li.search-field input[type="text"]{
    height:15px<?php echo esc_html( $important ) ?>;
}
/* End Chosen */

<?php if ( isset( $progress_color ) ) { ?>
/* Progress Bars */
.<?php echo esc_html( $style_class ) ?> .frm_page_bar input,
.<?php echo esc_html( $style_class ) ?> .frm_page_bar input:disabled{
	color: <?php echo esc_html( $progress_color . $important ) ?>;
	background-color: <?php echo esc_html( $progress_bg_color . $important ) ?>;
	border-color: <?php echo esc_html( $progress_border_color . $important ) ?>;
	border-width: <?php echo esc_html( $progress_border_size . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_progress_line input.frm_page_back{
	background-color: <?php echo esc_html( $progress_active_bg_color . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_page_bar .frm_current_page input[type="button"]{
	background-color: <?php echo esc_html( $progress_bg_color . $important ) ?>;
	border-color: <?php echo esc_html( $progress_border_color . $important ) ?>;
	opacity:1<?php echo esc_html( $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_current_page .frm_rootline_title{
	color: <?php echo esc_html( $progress_active_bg_color . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_rootline_title,
.<?php echo esc_html( $style_class ) ?> .frm_pages_complete,
.<?php echo esc_html( $style_class ) ?> .frm_percent_complete{
	color: <?php echo esc_html( $description_color . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_progress_line input,
.<?php echo esc_html( $style_class ) ?> .frm_progress_line input:disabled {
	border-color: <?php echo esc_html( $progress_border_color . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_progress_line.frm_show_lines input {
	border-left-color: <?php echo esc_html( $progress_color . $important ) ?>;
	border-right-color: <?php echo esc_html( $progress_color . $important ) ?>;
	border-left-width: 1px <?php echo esc_html( $important ) ?>;
	border-right-width: 1px <?php echo esc_html( $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_progress_line li:first-of-type input {
	border-left-color: <?php echo esc_html( $progress_active_bg_color . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_progress_line li:last-of-type input {
	border-right-color: <?php echo esc_html( $progress_active_bg_color . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_progress_line li:last-of-type input.frm_page_skip {
	border-right-color: <?php echo esc_html( $progress_border_color . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_progress_line .frm_current_page input[type="button"] {
	border-left-color: <?php echo esc_html( $progress_border_color . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_progress_line.frm_show_lines .frm_current_page input[type="button"] {
	border-right-color: <?php echo esc_html( $progress_color . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_progress_line input.frm_page_back {
	border-color: <?php echo esc_html( $progress_active_bg_color . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_progress_line.frm_show_lines input.frm_page_back{
	border-left-color: <?php echo esc_html( $progress_active_bg_color . $important ) ?>;
	border-right-color: <?php echo esc_html( $progress_color . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_rootline.frm_show_lines:before {
    border-color: <?php echo esc_html( $progress_border_color . $important ) ?>;
	border-top-width: <?php echo esc_html( $progress_border_size . $important ) ?>;
    top: <?php echo esc_html( absint( $progress_size ) / 2 ) ?>px;
}

.<?php echo esc_html( $style_class ) ?> .frm_rootline input,
.<?php echo esc_html( $style_class ) ?> .frm_rootline input:hover {
	width: <?php echo esc_html( $progress_size . $important ) ?>;
	height: <?php echo esc_html( $progress_size . $important ) ?>;
	border-radius: <?php echo esc_html( $progress_size . $important ) ?>;
	padding: 0<?php echo esc_html( $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_rootline input:focus {
	border-color: <?php echo esc_html( $progress_active_bg_color . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_rootline .frm_current_page input[type="button"] {
	border-color: <?php echo esc_html( FrmStylesHelper::adjust_brightness( $progress_active_bg_color, -20 ) . $important ); ?>;
	background-color: <?php echo esc_html( $progress_active_bg_color . $important ) ?>;
	color: <?php echo esc_html( $progress_active_color . $important ) ?>;
}

.<?php echo esc_html( $style_class ) ?> .frm_progress_line input,
.<?php echo esc_html( $style_class ) ?> .frm_progress_line input:disabled,
.<?php echo esc_html( $style_class ) ?> .frm_progress_line .frm_current_page input[type="button"],
.<?php echo esc_html( $style_class ) ?> .frm_rootline.frm_no_numbers input,
.<?php echo esc_html( $style_class ) ?> .frm_rootline.frm_no_numbers .frm_current_page input[type="button"] {
	color: transparent !important;
}
<?php } ?>

/* Responsive CSS */
@media only screen and (max-width: 600px){
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_half.frm_left_container input:not([type=radio]):not([type=checkbox]),
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_half.frm_left_container select,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_half.frm_left_container textarea,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_half.frm_left_container .frm_opt_container,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_half.frm_left_container.frm-g-recaptcha,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_half.frm_left_container.g-recaptcha,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_half.frm_left_container .chosen-container,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_half.frm_right_container input:not([type=radio]):not([type=checkbox]),
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_half.frm_right_container select,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_half.frm_right_container textarea,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_half.frm_right_container .frm_opt_container,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_half.frm_right_container.frm-g-recaptcha,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_half.frm_right_container.g-recaptcha,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_half.frm_right_container .chosen-container{
		max-width:100%<?php echo esc_html( $important ) ?>;
	}
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_left_half.frm_left_container .frm_primary_label,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_right_half.frm_left_container .frm_primary_label,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_first_half.frm_left_container .frm_primary_label,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_last_half.frm_left_container .frm_primary_label,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_half.frm_left_container .frm_primary_label,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_left_half.frm_right_container .frm_primary_label,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_right_half.frm_right_container .frm_primary_label,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_first_half.frm_right_container .frm_primary_label,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_last_half.frm_right_container .frm_primary_label,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_half.frm_right_container .frm_primary_label{
		max-width:100%<?php echo esc_html( $important ) ?>;
		margin-right:0;
		margin-left:0;
		padding-right:0;
		padding-left:0;
		width:100%<?php echo esc_html( $important ) ?>;
	}


	.<?php echo esc_html( $style_class ) ?> .frm_repeat_inline,
	.<?php echo esc_html( $style_class ) ?> .frm_repeat_grid{
		margin: 20px 0;
	}

	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_first_half.frm_right_container div.frm_description,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_first_half.frm_right_container .frm_error,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_first_half .frm_right_container div.frm_description,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_first_half .frm_right_container .frm_error,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_last_half.frm_right_container div.frm_description,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_last_half.frm_right_container .frm_error,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_half.frm_right_container div.frm_description,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_half.frm_right_container .frm_error,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_first_half.frm_left_container div.frm_description,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_first_half.frm_left_container .frm_error,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_first_half .frm_left_container div.frm_description,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_first_half .frm_left_container .frm_error,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_last_half.frm_left_container div.frm_description,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_last_half.frm_left_container .frm_error,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_half.frm_left_container div.frm_description,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_half.frm_left_container .frm_error{
		margin-right:0;
		margin-left:0;
		padding-right:0;
		padding-left:0;
	}
}

@media only screen and (max-width: 500px) {
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_left_container input:not([type=radio]):not([type=checkbox]),
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_left_container:not(.frm_dynamic_select_container) select,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_left_container textarea,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_left_container .frm_opt_container,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_left_container .frm-g-recaptcha,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_left_container .g-recaptcha,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_left_container .chosen-container,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_right_container input:not([type=radio]):not([type=checkbox]),
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_right_container:not(.frm_dynamic_select_container) select,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_right_container textarea,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_right_container .frm_opt_container,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_right_container .frm-g-recaptcha,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_right_container .g-recaptcha,
	.<?php echo esc_html( $style_class ) ?> .frm_form_field.frm_right_container .chosen-container{
		max-width:100%<?php echo esc_html( $important ) ?>;
	}

	.<?php echo esc_html( $style_class ) ?> .frm_left_container > p.description::before,
	.<?php echo esc_html( $style_class ) ?> .frm_left_container > div.description::before,
	.<?php echo esc_html( $style_class ) ?> .frm_left_container > div.frm_description::before,
	.<?php echo esc_html( $style_class ) ?> .frm_left_container > .frm_error::before,
	.<?php echo esc_html( $style_class ) ?> .frm_right_container > p.description::after,
	.<?php echo esc_html( $style_class ) ?> .frm_right_container > div.description::after,
	.<?php echo esc_html( $style_class ) ?> .frm_right_container > div.frm_description::after,
	.<?php echo esc_html( $style_class ) ?> .frm_right_container > .frm_error::after{
		display:none<?php echo esc_html( $important ) ?>;
	}

	.<?php echo esc_html( $style_class ) ?> .frm_left_container label.frm_primary_label,
	.<?php echo esc_html( $style_class ) ?> .frm_right_container label.frm_primary_label{
		width:100%<?php echo esc_html( $important ) ?>;
		max-width:100%<?php echo esc_html( $important ) ?>;
		margin-right:0<?php echo esc_html( $important ) ?>;
		margin-left:0<?php echo esc_html( $important ) ?>;
		padding-right:0<?php echo esc_html( $important ) ?>;
		padding-left:0<?php echo esc_html( $important ) ?>;
	}
}
/* End Responsive CSS*/
