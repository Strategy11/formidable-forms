<?php
if ( ! isset($saving) ) {
    header( 'Content-type: text/css' );

    if ( isset($css) && $css ) {
        echo $css;
        die();
    }
}

if ( ! isset($frm_style) ) {
    $frm_style = new FrmStyle();
}

$styles = $frm_style->get_all();
$default_style = $frm_style->get_default_style($styles);
$defaults = $default_style->post_content;
?>

.frm_hidden,
.with_frm_style .frm_button.frm_hidden{
    display:none;
}

legend.frm_hidden{
    display:none !important;
}

.frm_transparent{
	color:transparent;
}

.input[type=file].frm_transparent:focus, .with_frm_style input[type=file]{
	background-color:transparent;
	border:none;
	outline:none;
	box-shadow:none;
}

.with_frm_style input[type=file]{
	display:initial;
}

.frm_preview_page:before{
    content:normal !important;
}

.frm_preview_page{
    padding:25px;
}

.with_frm_style .form-field.frm_col_field{
    clear:none;
    float:left;
    margin-right:20px;
}

.with_frm_style label.frm_primary_label{
    max-width:100%;
}

.with_frm_style .frm_top_container label.frm_primary_label,
.with_frm_style .frm_hidden_container label.frm_primary_label,
.with_frm_style .frm_pos_top{
    display:block;
    float:none;
    width:auto;
}

.with_frm_style .frm_inline_container label.frm_primary_label{
    margin-right:10px;
}

.with_frm_style .frm_right_container label.frm_primary_label,
.with_frm_style .frm_pos_right{
    display:inline;
    float:right;
    margin-left:10px;
}

.with_frm_style .frm_none_container label.frm_primary_label,
.with_frm_style .frm_pos_none,
.frm_none_container label.frm_primary_label{
    display:none;
}

.with_frm_style .frm_section_heading.frm_hide_section{
	margin-top:0px !important;
}

.with_frm_style .frm_hidden_container label.frm_primary_label,
.with_frm_style .frm_pos_hidden,
.frm_hidden_container label.frm_primary_label{
    visibility:hidden;
}

.with_frm_style .frm_scale{
    margin-right:10px;
    text-align:center;
    float:left;
}

.with_frm_style .frm_scale input{
    display:block;
}

.with_frm_style select[multiple="multiple"]{
    height:auto;
    line-height:normal;
}

.with_frm_style select{
	white-space:nowrap;
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
    width:auto;
}

.with_frm_style .frm_repeat_buttons{
	white-space:nowrap;
}

.with_frm_style .frm_button{
    text-decoration:none;
    border:1px solid #eee;
	padding:5px;
	display:inline;
}

.with_frm_style .frm_submit{
    clear:both;
}

.frm_inline_form .frm_form_field.form-field{
    margin-right:2.5%;
	display:inline-block;
}

.frm_inline_form .frm_submit{
	display:inline-block;
}

.with_frm_style.frm_center_submit .frm_submit{
    text-align:center;
}

.with_frm_style.frm_center_submit .frm_submit input[type=submit], .with_frm_style.frm_center_submit .frm_submit input[type=button]{
    margin-bottom:8px !important;
}
.with_frm_style.frm_center_submit .frm_submit .frm_ajax_loading{
    display: block;
    margin: 0 auto;
}

<?php
foreach ( $styles as $style ) {
    include(dirname(__FILE__) .'/_single_theme.css.php');
    unset($style);
}
?>

.frm_ajax_loading{
    visibility:hidden;
	width:auto;
}

.frm_ajax_loading.frm_loading_now{
    visibility:visible !important;
}

.frm_form_submit_style{
    height:auto;
}

a.frm_save_draft{
    cursor:pointer;
}

.horizontal_radio .frm_radio{
    margin:0 5px 0 0;
}

.horizontal_radio .frm_checkbox{
    margin:0;
    margin-right:5px;
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
}

.with_frm_style .frm_radio label .frm_file_container,
.with_frm_style .frm_checkbox label .frm_file_container{
    display:inline-block;
    margin:5px;
    vertical-align:middle;
}

.with_frm_style .frm_radio input[type=radio]{
    border-radius:10px;
	-webkit-appearance:radio;
}

.with_frm_style .frm_checkbox input[type=checkbox]{
    border-radius:0;
	-webkit-appearance:checkbox;
}

.with_frm_style .frm_radio input[type=radio],
.with_frm_style .frm_checkbox input[type=checkbox]{
    margin-right:5px;
	width:auto;
	border:none;
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
    -moz-border-radius:4px;
    -webkit-border-radius:4px;
    border-radius:4px;
    padding:15px;
}

.with_frm_style .frm_message p{
    margin-bottom:5px;
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
    border-color:#<?php echo $defaults['border_color'] ?>;
    border-top:none;
    border-left:none;
    border-right:none;
}

table.form_results.with_frm_style{
    border:1px solid #ccc;
}

table.form_results.with_frm_style tr td{
    text-align:left;
    color:#<?php echo $defaults['text_color'] ?>;
    padding:7px 9px;
    border-top:1px solid #<?php echo $defaults['border_color'] ?>;
}

table.form_results.with_frm_style tr.frm_even,
.frm-grid .frm_even{
    background-color:#fff;
}

table.form_results.with_frm_style tr.frm_odd,
.frm-grid .frm_odd{
    background-color:#<?php echo $defaults['bg_color_active'] ?>;
}

.with_frm_style .frm_uploaded_files{
    padding:5px 0;
}

.with_frm_style .frm_file_names{
    display:block;
}

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

#frm_loading .progress-striped .progress-bar{
    background-image:linear-gradient(45deg, #<?php echo $defaults['border_color'] ?> 25%, rgba(0, 0, 0, 0) 25%, rgba(0, 0, 0, 0) 50%, #<?php echo $defaults['border_color'] ?> 50%, #<?php echo $defaults['border_color'] ?> 75%, rgba(0, 0, 0, 0) 75%, rgba(0, 0, 0, 0));
    background-size:40px 40px;
}

#frm_loading .progress-bar{
    background-color:#<?php echo $defaults['bg_color'] ?>;
    box-shadow:0 -1px 0 rgba(0, 0, 0, 0.15) inset;
    float:left;
    height:100%;
    line-height:20px;
    text-align:center;
    transition:width 0.6s ease 0s;
    width:100%;
}

.frm_pagination_cont ul.frm_pagination{
    display:inline-block;
    list-style:none;
    margin-left:0 !important;
}

.frm_pagination_cont ul.frm_pagination > li{
    display:inline;
    list-style:none;
    margin:2px;
    background-image:none;
}

ul.frm_pagination > li.active a{
	text-decoration:none;
}

.frm_pagination_cont ul.frm_pagination > li:first-child{
    margin-left:0;
}

.archive-pagination.frm_pagination_cont ul.frm_pagination > li{
    margin:0;
}

/* Calendar Styling */
.frmcal{
    padding-top:30px;
}

.frmcal-title{
    font-size:116%;
}

.frmcal table.frmcal-calendar{
    border-collapse:collapse;
    margin-top:20px;
    color:#<?php echo $defaults['text_color'] ?>;
}

.frmcal table.frmcal-calendar,
.frmcal table.frmcal-calendar tbody tr td{
    border:1px solid #<?php echo $defaults['border_color'] ?>;
}

.frmcal table.frmcal-calendar,
.frmcal,
.frmcal-header{
    width:100%;
}

.frmcal-header{
    text-align:center;
}

.frmcal-prev{
    margin-right:10px;
}

.frmcal-prev,
.frmcal-dropdown{
    float:left;
}

.frmcal-dropdown{
    margin-left:5px;
}

.frmcal-next{
    float:right;
}

.frmcal table.frmcal-calendar thead tr th{
    text-align:center;
    padding:2px 4px;
}

.frmcal table.frmcal-calendar tbody tr td{
    height:110px;
    width:14.28%;
    vertical-align:top;
    padding:0 !important;
    color:#<?php echo esc_attr( $defaults['text_color'] ) ?>;
    font-size:12px;
}

table.frmcal-calendar .frmcal_date{
    background-color:#<?php echo $defaults['bg_color'] ?>;
    padding:0 5px;
    text-align:right;
    -moz-box-shadow:0 2px 5px #<?php echo $defaults['border_color'] ?>;
    -webkit-box-shadow:0 2px 5px #<?php echo $defaults['border_color'] ?>;
    box-shadow:0 2px 5px #<?php echo $defaults['border_color'] ?>;
    -ms-filter:"progid:DXImageTransform.Microsoft.Shadow(Strength=4, Direction=180, Color='#<?php echo $defaults['border_color'] ?>')";
    filter:progid:DXImageTransform.Microsoft.Shadow(Strength=4, Direction=180, Color='#<?php echo $defaults['border_color'] ?>');
}

table.frmcal-calendar .frmcal-today .frmcal_date{
    background-color:#<?php echo $defaults['bg_color_active'] ?>;
    padding:0 5px;
    text-align:right;
    -moz-box-shadow:0 2px 5px #<?php echo $defaults['border_color_active'] ?>;
    -webkit-box-shadow:0 2px 5px #<?php echo $defaults['border_color_active'] ?>;
    box-shadow:0 2px 5px #<?php echo $defaults['border_color_active'] ?>;
    -ms-filter:"progid:DXImageTransform.Microsoft.Shadow(Strength=4, Direction=180, Color='#<?php echo $defaults['border_color_active'] ?>')";
    filter:progid:DXImageTransform.Microsoft.Shadow(Strength=4, Direction=180, Color='#<?php echo $defaults['border_color_active'] ?>');
}

.frmcal_num{
    display:inline;
}

.frmcal-content{
    padding:2px 4px;
}
/* End Calendar Styling */

.frm_image_from_url{
	height:50px;
}

.frm-loading-img{
    background:url(<?php echo FrmAppHelper::relative_plugin_url() ?>/images/ajax_loader.gif) no-repeat center center;
    padding:6px 12px;
}

#ui-datepicker-div{
    display:none;
    z-index:999999 !important;
}

.frm_form_fields div.rating-cancel{
    display:none !important;
}

.frm_form_fields div.rating-cancel,
.frm_form_fields div.star-rating{
    float:left;
    width:17px;
    height:17px;
	font-size:16px;
    line-height:normal;
    cursor:pointer;
    display:block;
    background:transparent;
    overflow:hidden;
}

.frm_form_fields div.rating-cancel a:before{
    font:16px/1 'dashicons';
    content:'\f460';
    color:#CDCDCD;
}

.frm_form_fields div.star-rating:before,
.frm_form_fields div.star-rating a:before{
    font:16px/1 'dashicons';
    content:'\f154';
    color:#F0AD4E;
}

.frm_form_fields div.rating-cancel a,
.frm_form_fields div.star-rating a{
    display:block;
    width:16px;
    height:100%;
    border:0;
}

.frm_form_fields div.star-rating-on:before,
.frm_form_fields div.star-rating-on a:before{
    content:'\f155';
}

.frm_form_fields div.star-rating-hover:before,
.frm_form_fields div.star-rating-hover a:before{
    content:'\f155';
}

.frm_form_fields div.frm_half_star:before,
.frm_form_fields div.frm_half_star a:before{
    content:'\f459';
}

.frm_form_fields div.rating-cancel.star-rating-hover a:before{
    color:#B63E3F;
}

.frm_form_fields div.star-rating-readonly,
.frm_form_fields div.star-rating-readonly a{
    cursor:default !important;
}

.frm_form_fields div.star-rating{
    overflow:hidden!important;
}

.with_frm_style .frm_form_field{
    clear:both;
}

.frm_form_field.frm_right_half,
.frm_form_field.frm_right_third,
.frm_form_field.frm_right_two_thirds,
.frm_form_field.frm_right_fourth,
.frm_form_field.frm_right_fifth,
.frm_form_field.frm_right_inline,
.frm_form_field.frm_last_half,
.frm_form_field.frm_last_third,
.frm_form_field.frm_last_two_thirds,
.frm_form_field.frm_last_fourth,
.frm_form_field.frm_last_fifth,
.frm_form_field.frm_last_sixth,
.frm_form_field.frm_last_seventh,
.frm_form_field.frm_last_eighth,
.frm_form_field.frm_last_inline,
.frm_form_field.frm_last,
.frm_form_field.frm_half,
.frm_submit.frm_half,
.frm_form_field.frm_third,
.frm_submit.frm_third,
.frm_form_field.frm_two_thirds,
.frm_form_field.frm_fourth,
.frm_submit.frm_fourth,
.frm_form_field.frm_three_fourths,
.frm_form_field.frm_fifth,
.frm_submit.frm_fifth,
.frm_form_field.frm_two_fifths,
.frm_form_field.frm_three_fifths,
.frm_form_field.frm_four_fifths,
.frm_form_field.frm_sixth,
.frm_submit.frm_sixth,
.frm_form_field.frm_seventh,
.frm_submit.frm_seventh,
.frm_form_field.frm_eighth,
.frm_submit.frm_eighth,
.frm_form_field.frm_inline,
.frm_submit.frm_inline{
    clear:none;
    float:left;
	margin-left:2.5%;
}

.frm_form_field.frm_left_half,
.frm_form_field.frm_left_third,
.frm_form_field.frm_left_two_thirds,
.frm_form_field.frm_left_fourth,
.frm_form_field.frm_left_fifth,
.frm_form_field.frm_left_inline,
.frm_form_field.frm_first_half,
.frm_form_field.frm_first_third,
.frm_form_field.frm_first_two_thirds,
.frm_form_field.frm_first_fourth,
.frm_form_field.frm_first_fifth,
.frm_form_field.frm_first_sixth,
.frm_form_field.frm_first_seventh,
.frm_form_field.frm_first_eighth,
.frm_form_field.frm_first_inline,
.frm_form_field.frm_first{
    clear:left;
    float:left;
	margin-left:0;
}

.frm_form_field.frm_alignright{
	float:right !important;
}

.frm_form_field.frm_left_half,
.frm_form_field.frm_right_half,
.frm_form_field.frm_first_half,
.frm_form_field.frm_last_half,
.frm_form_field.frm_half,
.frm_submit.frm_half{
    width:48.75%;
}

.frm_form_field.frm_left_third,
.frm_form_field.frm_third,
.frm_submit.frm_third,
.frm_form_field.frm_right_third,
.frm_form_field.frm_first_third,
.frm_form_field.frm_last_third{
    width:31.66%;
}

.frm_form_field.frm_left_two_thirds,
.frm_form_field.frm_right_two_thirds,
.frm_form_field.frm_first_two_thirds,
.frm_form_field.frm_last_two_thirds,
.frm_form_field.frm_two_thirds{
    width:65.82%;
}

.frm_form_field.frm_left_fourth,
.frm_form_field.frm_fourth,
.frm_submit.frm_fourth,
.frm_form_field.frm_right_fourth,
.frm_form_field.frm_first_fourth,
.frm_form_field.frm_last_fourth{
    width:23.12%;
}

.frm_form_field.frm_three_fourths{
	width:74.36%;
}

.frm_form_field.frm_left_fifth,
.frm_form_field.frm_fifth,
.frm_submit.frm_fifth,
.frm_form_field.frm_right_fifth,
.frm_form_field.frm_first_fifth,
.frm_form_field.frm_last_fifth{
    width:18%;
}

.frm_form_field.frm_two_fifths {
	width:38.5%;
}

.frm_form_field.frm_three_fifths {
	width:59%;
}

.frm_form_field.frm_four_fifths {
	width:79.5%;
}

.frm_form_field.frm_sixth,
.frm_submit.frm_sixth,
.frm_form_field.frm_first_sixth,
.frm_form_field.frm_last_sixth{
    width:14.58%;
}

.frm_form_field.frm_seventh,
.frm_submit.frm_seventh,
.frm_form_field.frm_first_seventh,
.frm_form_field.frm_last_seventh{
    width:12.14%;
}

.frm_form_field.frm_eighth,
.frm_submit.frm_eighth,
.frm_form_field.frm_first_eighth,
.frm_form_field.frm_last_eighth{
    width:10.31%;
}

.frm_form_field.frm_left_inline,
.frm_form_field.frm_first_inline,
.frm_form_field.frm_inline,
.frm_submit.frm_inline,
.frm_form_field.frm_right_inline,
.frm_form_field.frm_last_inline{
    width:auto;
}

.with_frm_style .frm_form_field.frm_first_half.frm_right_container div.frm_description,
.with_frm_style .frm_form_field.frm_first_half.frm_right_container .frm_error,
.with_frm_style .frm_form_field.frm_first_half .frm_right_container div.frm_description,
.with_frm_style .frm_form_field.frm_first_half .frm_right_container .frm_error,
.with_frm_style .frm_form_field.frm_last_half.frm_right_container div.frm_description,
.with_frm_style .frm_form_field.frm_last_half.frm_right_container .frm_error,
.with_frm_style .frm_form_field.frm_half.frm_right_container div.frm_description,
.with_frm_style .frm_form_field.frm_half.frm_right_container .frm_error{
	margin-right:33%;
	padding-right:12px;
}

.with_frm_style .frm_form_field.frm_first_half.frm_left_container div.frm_description,
.with_frm_style .frm_form_field.frm_first_half.frm_left_container .frm_error,
.with_frm_style .frm_form_field.frm_first_half .frm_left_container div.frm_description,
.with_frm_style .frm_form_field.frm_first_half .frm_left_container .frm_error,
.with_frm_style .frm_form_field.frm_last_half.frm_left_container div.frm_description,
.with_frm_style .frm_form_field.frm_last_half.frm_left_container .frm_error,
.with_frm_style .frm_form_field.frm_half.frm_left_container div.frm_description,
.with_frm_style .frm_form_field.frm_half.frm_left_container .frm_error{
	margin-left:33%;
	padding-left:12px;
}

.frm_full,
.frm_full .wp-editor-wrap,
.frm_full input:not([type='checkbox']):not([type='radio']):not([type='button']),
.frm_full select,
.frm_full textarea{
    width:100% !important;
}

.frm_full .wp-editor-wrap input{
    width:auto !important;
}

/* Left and right label styling for non-Formidable styling - very basic, not responsive */
.frm_form_field.frm_left_container label.frm_primary_label{
	float:left;
	display:inline;
	max-width:33%;
	margin-right:10px;
}

.frm_form_field.frm_left_container input:not([type=radio]):not([type=checkbox]),
.frm_form_field.frm_left_container:not(.frm_dynamic_select_container) select,
.frm_form_field.frm_left_container textarea,
.frm_form_field.frm_left_container .frm_opt_container,
.frm_form_field.frm_left_container .g-recaptcha,
.frm_form_field.frm_right_container input:not([type=radio]):not([type=checkbox]),
.frm_form_field.frm_right_container:not(.frm_dynamic_select_container) select,
.frm_form_field.frm_right_container textarea,
.frm_form_field.frm_right_container .frm_opt_container,
.frm_form_field.frm_right_container .g-recaptcha{
	max-width:62%;
}

.frm_form_field.frm_left_container .frm_opt_container,
.frm_form_field.frm_right_container .frm_opt_container,
.frm_form_field.frm_left_container .g-recaptcha,
.frm_form_field.frm_right_container .g-recaptcha{
	display:inline-block;
}

.frm_left_container p.description,
.frm_left_container div.description,
.frm_left_container div.frm_description,
.frm_left_container .frm_error{
    margin-left:33%;
	max-width:62%;
}

.frm_form_field.frm_left_half.frm_left_container .frm_primary_label,
.frm_form_field.frm_right_half.frm_left_container .frm_primary_label,
.frm_form_field.frm_left_half.frm_right_container .frm_primary_label,
.frm_form_field.frm_right_half.frm_right_container .frm_primary_label,
.frm_form_field.frm_first_half.frm_left_container .frm_primary_label,
.frm_form_field.frm_last_half.frm_left_container .frm_primary_label,
.frm_form_field.frm_first_half.frm_right_container .frm_primary_label,
.frm_form_field.frm_last_half.frm_right_container .frm_primary_label,
.frm_form_field.frm_half.frm_right_container .frm_primary_label,
.frm_form_field.frm_half.frm_left_container .frm_primary_label{
	-webkit-box-sizing:border-box;
	-moz-box-sizing:border-box;
	box-sizing:border-box;
	max-width:33%;
}
/* End of left and right label styling */

.wp-editor-wrap *,
.wp-editor-wrap *:after,
.wp-editor-wrap *:before{
    -webkit-box-sizing:content-box;
    -moz-box-sizing:content-box;
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
    border-color:#<?php echo $defaults['border_color'] ?>;
    border-left:none;
    border-right:none;
}

.with_frm_style .frm_grid,
.with_frm_style .frm_grid_odd{
    border-top:none;
}

.frm_grid .frm_error,
.frm_grid_first .frm_error,
.frm_grid_odd .frm_error{
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

.frm_grid_first,
.frm_grid_odd{
    background-color:#<?php echo $defaults['bg_color'] ?>;
}

.frm_grid{
    background-color:#<?php echo $defaults['bg_color_active'] ?>;
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
.frm_grid_2 label.frm_primary_label{
    width:48% !important;
}

.frm_grid_2 .frm_radio,
.frm_grid_2 .frm_checkbox{
    margin-right:4%;
}

.frm_grid_3 .frm_radio,
.frm_grid_3 .frm_checkbox,
.frm_grid_3 label.frm_primary_label{
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

.frm_grid_4 label.frm_primary_label{
    width:28% !important;
}

.frm_grid_4 .frm_radio,
.frm_grid_4 .frm_checkbox{
    margin-right:4%;
}

.frm_grid_5 label.frm_primary_label,
.frm_grid_7 label.frm_primary_label{
    width:24% !important;
}

.frm_grid_5 .frm_radio,
.frm_grid_5 .frm_checkbox{
    width:17% !important;
    margin-right:2%;
}

.frm_grid_6 label.frm_primary_label{
    width:25% !important;
}

.frm_grid_6 .frm_radio,
.frm_grid_6 .frm_checkbox{
    width:14% !important;
    margin-right:1%;
}

.frm_grid_7 label.frm_primary_label{
    width:22% !important;
}

.frm_grid_7 .frm_radio,
.frm_grid_7 .frm_checkbox{
    width:12% !important;
    margin-right:1%;
}

.frm_grid_8 label.frm_primary_label{
    width:23% !important;
}

.frm_grid_8 .frm_radio,
.frm_grid_8 .frm_checkbox{
    width:10% !important;
    margin-right:1%;
}

.frm_grid_9 label.frm_primary_label{
    width:20% !important;
}

.frm_grid_9 .frm_radio,
.frm_grid_9 .frm_checkbox{
    width:9% !important;
    margin-right:1%;
}

.frm_grid_10 label.frm_primary_label{
    width:19% !important;
}

.frm_grid_10 .frm_radio,
.frm_grid_10 .frm_checkbox{
    width:8% !important;
    margin-right:1%;
}

.with_frm_style .frm_inline_container.frm_grid_first label.frm_primary_label,
.with_frm_style .frm_inline_container.frm_grid label.frm_primary_label,
.with_frm_style .frm_inline_container.frm_grid_odd label.frm_primary_label,
.with_frm_style .frm_inline_container.frm_grid_first .frm_opt_container,
.with_frm_style .frm_inline_container.frm_grid .frm_opt_container,
.with_frm_style .frm_inline_container.frm_grid_odd .frm_opt_container{
    margin-right:0;
}

.with_frm_style .frm_inline_container.frm_scale_container label.frm_primary_label{
	float:left;
}

.with_frm_style .frm_other_input.frm_other_full{
	margin-top:10px;
}

.with_frm_style .frm_repeat_sec{
    margin-bottom:20px;
    margin-top:20px;
}

.with_frm_style .frm_repeat_inline{
	clear:both;
}

.frm_form_field .frm_repeat_sec .frm_add_form_row{
    opacity:0;
	display:none;
	*display:inline;
	display:inline\0/; /* For IE 8-9 */
	-moz-transition: opacity .15s ease-in-out;
	-webkit-transition: opacity .15s ease-in-out;
	transition: opacity .15s ease-in-out;
    pointer-events:none;
}

.frm_section_heading div.frm_repeat_sec:last-child .frm_add_form_row{
    opacity:100;
	display:inline;
    pointer-events:auto;
}

.frm_form_field .frm_repeat_grid .frm_form_field label.frm_primary_label{
    display:none !important;
}

.frm_form_field .frm_repeat_grid.frm_first_repeat .frm_form_field label.frm_primary_label{
    display:inherit !important;
}

.frm_form_field.frm_two_col .frm_radio,
.frm_form_field.frm_three_col .frm_radio,
.frm_form_field.frm_four_col .frm_radio,
.frm_form_field.frm_two_col .frm_checkbox,
.frm_form_field.frm_three_col .frm_checkbox,
.frm_form_field.frm_four_col .frm_checkbox{
    float:left;
}

.frm_form_field.frm_two_col .frm_radio,
.frm_form_field.frm_two_col .frm_checkbox{
    width:48%;
    margin-right:4%;
}

.frm_form_field.frm_three_col .frm_radio,
.frm_form_field.frm_three_col .frm_checkbox{
    width:30%;
    margin-right:5%;
}

.frm_form_field.frm_four_col .frm_radio,
.frm_form_field.frm_four_col .frm_checkbox{
    width:22%;
    margin-right:4%;
}

.frm_form_field.frm_two_col .frm_radio:nth-child(2n+2),
.frm_form_field.frm_two_col .frm_checkbox:nth-child(2n+2),
.frm_form_field.frm_three_col .frm_radio:nth-child(3n+3),
.frm_form_field.frm_three_col .frm_checkbox:nth-child(3n+3),
.frm_form_field.frm_four_col .frm_radio:nth-child(4n+4),
.frm_form_field.frm_four_col .frm_checkbox:nth-child(4n+4){
	margin-right:0;
}

.frm_form_field.frm_scroll_box .frm_opt_container{
    height:100px;
    overflow:auto;
}

.frm_form_field.frm_html_scroll_box{
    height:100px;
    overflow:auto;
    background-color:#<?php echo $defaults['bg_color'] ?>;
    border-color:#<?php echo $defaults['border_color'] ?>;
    border-width:<?php echo $defaults['field_border_width'] ?>;
    border-style:<?php echo $defaults['field_border_style'] ?>;
    -moz-border-radius:<?php echo $defaults['border_radius'] ?>;
    -webkit-border-radius:<?php echo $defaults['border_radius'] ?>;
    border-radius:<?php echo $defaults['border_radius'] ?>;
    width:<?php echo ($defaults['field_width'] == '' ? 'auto' : $defaults['field_width']) ?>;
    max-width:100%;
    font-size:<?php echo $defaults['field_font_size'] ?>;
    padding:<?php echo $defaults['field_pad'] ?>;
    -webkit-box-sizing:border-box;
    -moz-box-sizing:border-box;
    box-sizing:border-box;
    outline:none;
    font-weight:normal;
    box-shadow:0 1px 1px rgba(0, 0, 0, 0.075) inset;
}

.frm_form_field.frm_two_col .frm_opt_container:after,
.frm_form_field.frm_three_col .frm_opt_container:after,
.frm_form_field.frm_four_col .frm_opt_container:after{
    content:".";
    display:block;
    clear:both;
    visibility:hidden;
    line-height:0;
    height:0;
}

.frm_form_field.frm_total input,
.frm_form_field.frm_total textarea{
    opacity:1;
    background-color:transparent !important;
    border:none !important;
    font-weight:bold;
    -moz-box-shadow:none;
    -webkit-box-shadow:none;
    box-shadow:none !important;
    display:inline;
    width:auto !important;
	-moz-appearance:textfield;
	padding:0;
}

.frm_form_field.frm_total input::-webkit-outer-spin-button,
.frm_form_field.frm_total input::-webkit-inner-spin-button {
	    -webkit-appearance: none;
}

.frm_form_field.frm_total input:focus,
.frm_form_field.frm_total textarea:focus{
    background-color:transparent;
    border:none;
    -moz-box-shadow:none;
    -webkit-box-shadow:none;
    box-shadow:none;
}

.frm_text_block{
    margin-left:20px;
}

.frm_text_block input,
.frm_text_block label.frm_primary_label{
    margin-left:-20px;
}

.frm_text_block .frm_checkbox input[type=checkbox],
.frm_text_block .frm_radio input[type=radio]{
    margin-right:4px;
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
    display:inline-block;
}

html[xmlns] .frm_clearfix{
    display:block;
}

* html .frm_clearfix{
    height:1%;
}

/* Login form */
.with_frm_style.frm_login_form,
.with_frm_style.frm_login_form form{
	clear:both;
}

.with_frm_style.frm_login_form.frm_inline_login .login-remember input{
	vertical-align:baseline;
}

.with_frm_style.frm_login_form.frm_inline_login .login-submit{
	float:left;
}

.with_frm_style.frm_login_form.frm_inline_login label{
	display:inline;
}

.with_frm_style.frm_login_form.frm_inline_login .login-username,
.with_frm_style.frm_login_form.frm_inline_login .login-password,
.with_frm_style.frm_login_form.frm_inline_login .login-remember{
	float:left;
	margin-right:5px;
}

.with_frm_style.frm_login_form.frm_inline_login form{
	position:relative;
	clear:none;
}

.with_frm_style.frm_login_form.frm_inline_login .login-remember{
	position:absolute;
	top:35px;
}

.with_frm_style.frm_login_form.frm_inline_login input[type=submit]{
	margin:0 !important;
}

.with_frm_style.frm_login_form.frm_no_labels .login-username label,
.with_frm_style.frm_login_form.frm_no_labels .login-password label{
	display:none;
}

.with_frm_style .frm-open-login{
	float:left;
	margin-right:15px;
}

.with_frm_style .frm-open-login a{
	text-decoration:none;
	border:none;
	outline:none;
}

.with_frm_style.frm_slide.frm_login_form form{
	display:none;
}

/* Start Chosen */
.with_frm_style .chosen-container{
    font-size:<?php echo $defaults['field_font_size'] ?>;
    position:relative;
    display:inline-block;
    zoom:1;
    vertical-align:middle;
    -webkit-user-select:none;
    -moz-user-select:none;
    user-select:none;
    *display:inline;
}

.with_frm_style .chosen-container .chosen-drop{
    background:#fff;
    border:1px solid #aaa;
    border-top:0;
    position:absolute;
    top:100%;
    left:-9999px;
    box-shadow:0 4px 5px rgba(0,0,0,.15);
    z-index:1010;
    -webkit-box-sizing:border-box;
    -moz-box-sizing:border-box;
    box-sizing:border-box;
    width:100%;
}

.with_frm_style .chosen-container.chosen-with-drop .chosen-drop{
    left:0;
}

.with_frm_style .chosen-container a{
    cursor:pointer;
}

.with_frm_style .chosen-container-single .chosen-single{
    position:relative;
    display:block;
    overflow:hidden;
    padding:0 0 0 8px;
    height:25px;
    background:-webkit-gradient(linear, 50% 0%, 50% 100%, color-stop(20%, #ffffff), color-stop(50%, #f6f6f6), color-stop(52%, #eeeeee), color-stop(100%, #f4f4f4));
    background:-webkit-linear-gradient(top, #ffffff 20%, #f6f6f6 50%, #eeeeee 52%, #f4f4f4 100%);
    background:-moz-linear-gradient(top, #ffffff 20%, #f6f6f6 50%, #eeeeee 52%, #f4f4f4 100%);
    background:-o-linear-gradient(top, #ffffff 20%, #f6f6f6 50%, #eeeeee 52%, #f4f4f4 100%);
    background:linear-gradient(top, #ffffff 20%, #f6f6f6 50%, #eeeeee 52%, #f4f4f4 100%);
    background-clip:padding-box;
    box-shadow:0 0 3px white inset, 0 1px 1px rgba(0, 0, 0, 0.1);
    text-decoration:none;
    white-space:nowrap;
    line-height:24px;
}

.with_frm_style .chosen-container-single .chosen-single span{
    margin-right:26px;
    display:block;
    overflow:hidden;
    white-space:nowrap;
    text-overflow:ellipsis;
}

.with_frm_style .chosen-container-single .chosen-single-with-deselect span{
    margin-right:38px;
}

.with_frm_style .chosen-container-single .chosen-single abbr{
    display:block;
    position:absolute;
    right:26px;
    top:6px;
    width:12px;
    height:12px;
    font-size:1px;
    background:url('<?php echo FrmAppHelper::relative_plugin_url() ?>/pro/images/chosen-sprite.png') -42px 1px no-repeat;
}

.with_frm_style .chosen-container-single .chosen-single abbr:hover{
    background-position:-42px -10px;
}

.with_frm_style .chosen-container-single.chosen-disabled .chosen-single abbr:hover{
    background-position:-42px -10px;
}

.with_frm_style .chosen-container-single .chosen-single div{
    position:absolute;
    right:0;
    top:0;
    display:block;
    height:100%;
    width:18px;
}

.with_frm_style .chosen-container-single .chosen-single div b{
    background:url('<?php echo FrmAppHelper::relative_plugin_url() ?>/pro/images/chosen-sprite.png') no-repeat 0px 2px;
    display:block;
    width:100%;
    height:100%;
}

.with_frm_style .chosen-container-single .chosen-search{
    padding:3px 4px;
    position:relative;
    margin:0;
    white-space:nowrap;
    z-index:1010;
}

.with_frm_style .chosen-container-single .chosen-search input[type="text"]{
    -webkit-box-sizing:border-box;
    -moz-box-sizing:border-box;
    box-sizing:border-box;
    width:100%;
    height:auto;
    background:white url('<?php echo FrmAppHelper::relative_plugin_url() ?>/pro/images/chosen-sprite.png') no-repeat 100% -20px;
    background:url('<?php echo FrmAppHelper::relative_plugin_url() ?>/pro/images/chosen-sprite.png') no-repeat 100% -20px;
    font-size:1em;
    font-family:sans-serif;
    line-height:normal;
    border-radius:0;
}

.with_frm_style .chosen-container-single .chosen-drop{
    margin-top:-1px;
    border-radius:0 0 4px 4px;
    background-clip:padding-box;
}

.with_frm_style .chosen-container-single.chosen-container-single-nosearch .chosen-search{
    position:absolute;
    left:-9999px;
}

.with_frm_style .chosen-container .chosen-results{
    cursor:text;
    overflow-x:hidden;
    overflow-y:auto;
    position:relative;
    margin:0 4px 4px 0;
    padding:0 0 0 4px;
    max-height:240px;
    word-wrap:break-word;
    -webkit-overflow-scrolling:touch;
}

.with_frm_style .chosen-container .chosen-results li:before{
	background:none;
}

.with_frm_style .chosen-container .chosen-results li{
    display:none;
    margin:0;
    padding:5px 6px;
    list-style:none;
    line-height:15px;
    -webkit-touch-callout:none;
}

.with_frm_style .chosen-container .chosen-results li.active-result{
    display:list-item;
    cursor:pointer;
}

.with_frm_style .chosen-container .chosen-results li.disabled-result{
    display:list-item;
    color:#ccc;
    cursor:default;
}

.with_frm_style .chosen-container .chosen-results li.highlighted{
    background-color:#3875d7;
    color:#fff;
}

.with_frm_style .chosen-container .chosen-results li.no-results{
    display:list-item;
    background:#f4f4f4;
}

.with_frm_style .chosen-container .chosen-results li.group-result{
    display:list-item;
    font-weight:bold;
    cursor:default;
}

.with_frm_style .chosen-container .chosen-results li.group-option{
    padding-left:15px;
}

.with_frm_style .chosen-container .chosen-results li em{
    font-style:normal;
    text-decoration:underline;
}

.with_frm_style .chosen-container-multi .chosen-choices{
    position:relative;
    overflow:hidden;
    -webkit-box-sizing:border-box;
    -moz-box-sizing:border-box;
    box-sizing:border-box;
    margin:0;
    padding:0 5px;
    width:100%;
    height:auto !important;
    height:1%;
    cursor:text;
}

.with_frm_style .chosen-container-multi .chosen-choices li{
    float:left;
    list-style:none;
}

.with_frm_style .chosen-container-multi .chosen-choices li.search-field{
    margin:0;
    padding:0;
    white-space:nowrap;
}

.with_frm_style .chosen-container-multi .chosen-choices li.search-field input[type="text"]{
    margin:1px 0;
    padding:0;
    height:25px;
    outline:0;
    border:0 !important;
    background:transparent !important;
    box-shadow:none;
    color:#666;
    font-size:100%;
    font-family:sans-serif;
    line-height:normal;
    border-radius:0;
}

.with_frm_style .chosen-container-multi .chosen-choices li.search-choice{
    position:relative;
    margin:3px 5px 3px 0;
    padding:3px 20px 3px 5px;
    border:1px solid #aaa;
    max-width:100%;
    border-radius:3px;
    background-color:#eee;
    background-size:100% 19px;
    background-repeat:repeat-x;
    background-clip:padding-box;
    box-shadow:0 0 2px white inset, 0 1px 0 rgba(0, 0, 0, 0.05);
    color:#333;
    line-height:13px;
    cursor:default;
}

.with_frm_style .chosen-container-multi .chosen-choices li.search-choice .search-choice-close{
    position:absolute;
    top:4px;
    right:3px;
    display:block;
    width:12px;
    height:12px;
    background:url('<?php echo FrmAppHelper::relative_plugin_url() ?>/pro/images/chosen-sprite.png') -42px 1px no-repeat;
    font-size:1px;
}

.with_frm_style .chosen-container-multi .chosen-choices li.search-choice .search-choice-close:hover{
    background-position:-42px -10px;
}

.with_frm_style .chosen-container-multi .chosen-choices li.search-choice-disabled{
    padding-right:5px;
    border:1px solid #ccc;
    background-color:#e4e4e4;
    color:#666;
}

.with_frm_style .chosen-container-multi .chosen-choices li.search-choice-focus{
    background:#d4d4d4;
}

.with_frm_style .chosen-container-multi .chosen-choices li.search-choice-focus .search-choice-close{
    background-position:-42px -10px;
}

.with_frm_style .chosen-container-multi .chosen-results{
    margin:0;
    padding:0;
}

.with_frm_style .chosen-container-multi .chosen-drop .result-selected{
    display:list-item;
    color:#ccc;
    cursor:default;
}

.with_frm_style .chosen-container-active .chosen-single{
    border:1px solid #5897fb;
    box-shadow:0 0 5px rgba(0, 0, 0, 0.3);
}

.with_frm_style .chosen-container-active.chosen-with-drop .chosen-single{
    border:1px solid #aaa;
    -moz-border-radius-bottomright:0;
    border-bottom-right-radius:0;
    -moz-border-radius-bottomleft:0;
    border-bottom-left-radius:0;
    box-shadow:0 1px 0 #fff inset;
}

.with_frm_style .chosen-container-active.chosen-with-drop .chosen-single div{
    border-left:none;
    background:transparent;
}

.with_frm_style .chosen-container-active.chosen-with-drop .chosen-single div b{
    background-position:-18px 2px;
}

.with_frm_style .chosen-container-active .chosen-choices li.search-field input[type="text"]{
    color:#111 !important;
}

.with_frm_style .chosen-disabled{
    opacity:0.5 !important;
    cursor:default;
}

.with_frm_style .chosen-disabled .chosen-single{
    cursor:default;
}

.with_frm_style .chosen-disabled .chosen-choices .search-choice .search-choice-close{
    cursor:default;
}

.with_frm_style .chosen-rtl{
    text-align:right;
}

.with_frm_style .chosen-rtl .chosen-single{
    overflow:visible;
    padding:0 8px 0 0;
}

.with_frm_style .chosen-rtl .chosen-single span{
    margin-right:0;
    margin-left:26px;
    direction:rtl;
}

.with_frm_style .chosen-rtl .chosen-single-with-deselect span{
    margin-left:38px;
}

.with_frm_style .chosen-rtl .chosen-single div{
    right:auto;
    left:3px;
}

.with_frm_style .chosen-rtl .chosen-single abbr{
    right:auto;
    left:26px;
}

.with_frm_style .chosen-rtl .chosen-choices li{
    float:right;
}

.with_frm_style .chosen-rtl .chosen-choices li.search-field input[type="text"]{
    direction:rtl;
}

.with_frm_style .chosen-rtl .chosen-choices li.search-choice{
    margin:3px 5px 3px 0;
    padding:3px 5px 3px 19px;
}

.with_frm_style .chosen-rtl .chosen-choices li.search-choice .search-choice-close{
    right:auto;left:4px;
}

.with_frm_style .chosen-rtl.chosen-container-single-nosearch .chosen-search, .with_frm_style .chosen-rtl .chosen-drop{
    left:9999px;
}

.with_frm_style .chosen-rtl.chosen-container-single .chosen-results{
    margin:0 0 4px 4px;
    padding:0 4px 0 0;
}

.with_frm_style .chosen-rtl .chosen-results li.group-option{
    padding-right:15px;
    padding-left:0;
}

.with_frm_style .chosen-rtl.chosen-container-active.chosen-with-drop .chosen-single div{
    border-right:none;
}

.with_frm_style .chosen-rtl .chosen-search input[type="text"]{
    padding:4px 5px 4px 20px;
    background:white url('<?php echo FrmAppHelper::relative_plugin_url() ?>/pro/images/chosen-sprite.png') no-repeat -30px -20px;background:url('<?php echo FrmAppHelper::relative_plugin_url() ?>/pro/images/chosen-sprite.png') no-repeat -30px -20px;
    direction:rtl;
}

.with_frm_style .chosen-rtl.chosen-container-single .chosen-single div b{
    background-position:6px 2px;
}

.with_frm_style .chosen-rtl.chosen-container-single.chosen-with-drop .chosen-single div b{
    background-position:-12px 2px;
}
/* End Chosen */

/* Fonts */
@font-face {
	font-family:'s11-fp';
	src:url('<?php echo FrmAppHelper::relative_plugin_url() ?>/fonts/s11-fp.eot');
	src:local('☺'), url('<?php echo FrmAppHelper::relative_plugin_url() ?>/fonts/s11-fp.woff') format('woff'), url('<?php echo FrmAppHelper::relative_plugin_url() ?>/fonts/s11-fp.ttf') format('truetype'), url('<?php echo FrmAppHelper::relative_plugin_url() ?>/fonts/s11-fp.svg') format('svg');
	font-weight:normal;
	font-style:normal;
}

<?php include(FrmAppHelper::plugin_path() .'/css/font_icons.css'); ?>

/* Responsive */
@media only screen and (-webkit-min-device-pixel-ratio: 2), only screen and (min-resolution: 144dpi){
    .with_frm_style .chosen-rtl .chosen-search input[type="text"],
    .with_frm_style .chosen-container-single .chosen-single abbr,
    .with_frm_style .chosen-container-single .chosen-single div b,
    .with_frm_style .chosen-container-single .chosen-search input[type="text"],
    .with_frm_style .chosen-container-multi .chosen-choices .search-choice .search-choice-close,
    .with_frm_style .chosen-container .chosen-results-scroll-down span,
    .with_frm_style .chosen-container .chosen-results-scroll-up span{
        background-image:url('<?php echo FrmAppHelper::relative_plugin_url() ?>/pro/images/chosen-sprite2x.png') !important;
        background-size:52px 37px !important;
        background-repeat:no-repeat !important;
    }
}

@media only screen and (max-width: 900px) {
	.frm_form_field .frm_repeat_grid .frm_form_field.frm_sixth label.frm_primary_label,
	.frm_form_field .frm_repeat_grid .frm_form_field.frm_seventh label.frm_primary_label,
	.frm_form_field .frm_repeat_grid .frm_form_field.frm_eighth label.frm_primary_label{
    	display: block !important;
	}

	.frm_form_field .frm_repeat_grid .frm_form_field.frm_repeat_buttons.frm_seventh label.frm_primary_label{
		display:none !important;
	}

}


@media only screen and (max-width: 600px) {
	.frm_form_field.frm_half,
	.frm_submit.frm_half,
    .frm_form_field.frm_left_half,
    .frm_form_field.frm_right_half,
    .frm_form_field.frm_first_half,
    .frm_form_field.frm_last_half,
    .frm_form_field.frm_first_third,
    .frm_form_field.frm_third,
	.frm_submit.frm_third,
    .frm_form_field.frm_last_third,
    .frm_form_field.frm_first_two_thirds,
	.frm_form_field.frm_last_two_thirds,
	.frm_form_field.frm_two_thirds,
    .frm_form_field.frm_left_fourth,
    .frm_form_field.frm_fourth,
	.frm_submit.frm_fourth,
    .frm_form_field.frm_right_fourth,
    .frm_form_field.frm_first_fourth,
	.frm_form_field.frm_last_fourth,
	.frm_form_field.frm_three_fourths,
	.frm_form_field.frm_fifth,
	.frm_submit.frm_fifth,
	.frm_form_field.frm_two_fifths,
	.frm_form_field.frm_three_fifths,
	.frm_form_field.frm_four_fifths,
	.frm_form_field.frm_sixth,
	.frm_submit.frm_sixth,
	.frm_form_field.frm_seventh,
	.frm_submit.frm_seventh,
	.frm_form_field.frm_eighth,
	.frm_submit.frm_eighth,
    .frm_form_field.frm_first_inline,
    .frm_form_field.frm_inline,
	.frm_submit.frm_inline,
    .frm_form_field.frm_last_inline{
        width:100%;
        margin-left:0;
        margin-right:0;
		clear:both;
        float:none;
    }

    .frm_form_field.frm_four_col .frm_radio,
    .frm_form_field.frm_four_col .frm_checkbox{
        width:48%;
        margin-right:4%;
    }

    .frm_form_field.frm_four_col .frm_radio:nth-child(2n+2),
    .frm_form_field.frm_four_col .frm_checkbox:nth-child(2n+2){
    	margin-right:0;
    }

	.frm_form_field .frm_repeat_grid.frm_first_repeat .frm_form_field.frm_repeat_buttons:not(.frm_fourth):not(.frm_sixth):not(.frm_eighth) label.frm_primary_label{
		display:none !important;
	}

	.frm_form_field .frm_repeat_grid .frm_form_field.frm_fifth label.frm_primary_label{
		display:block !important;
	}

	.frm_form_field .frm_repeat_grid .frm_form_field.frm_repeat_buttons.frm_fifth label.frm_primary_label{
		display:none !important;
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

	.with_frm_style.frm_login_form.frm_inline_login p{
		clear:both;
		float:none;
	}

	.with_frm_style.frm_login_form.frm_inline_login form{
		position:static;
	}

	.with_frm_style.frm_login_form.frm_inline_login .login-remember{
		position:static;
	}

	.with_frm_style .g-recaptcha > div > div{
		width:inherit !important;
		display:block;
		overflow:hidden;
		max-width:302px;
		border-right:1px solid #d3d3d3;
		border-radius:4px;
		box-shadow:2px 0px 4px -1px rgba(0,0,0,.08);
		-moz-box-shadow:2px 0px 4px -1px rgba(0,0,0,.08);
	}

	.with_frm_style .g-recaptcha iframe{
		width:100%;
	}
}
<?php
echo $defaults['custom_css'];
