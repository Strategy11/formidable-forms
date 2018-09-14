<?php
if ( ! isset( $saving ) ) {
	header( 'Content-type: text/css' );

	if ( isset( $css ) && $css ) {
		echo strip_tags( $css, 'all' ); // WPCS: XSS ok.
		die();
	}
}

if ( ! isset( $frm_style ) ) {
    $frm_style = new FrmStyle();
}

$styles = $frm_style->get_all();
$default_style = $frm_style->get_default_style( $styles );
$defaults = FrmStylesHelper::get_settings_for_output( $default_style );

?>

.frm_form_field .grecaptcha-badge,
.frm_hidden,
.frm_remove_form_row.frm_hidden,
.with_frm_style .frm_button.frm_hidden{
    display:none;
}

form input.frm_verify{
	display:none !important;
}

.with_frm_style fieldset{
	min-width:0;
}

.with_frm_style fieldset fieldset{
	border:none;
	margin:0;
	padding:0;
	background-color:transparent;
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
	margin-top:0 !important;
}

.with_frm_style .frm_hidden_container label.frm_primary_label,
.with_frm_style .frm_pos_hidden,
.frm_hidden_container label.frm_primary_label{
    visibility:hidden;
}

.with_frm_style .frm_inside_container label.frm_primary_label{
	opacity:0;
	transition: opacity 0.1s linear;
}

.with_frm_style .frm_inside_container label.frm_visible,
.frm_visible{
	opacity:1;
}

.with_frm_style .frm_description{
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

.with_frm_style textarea{
    height:auto;
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

.with_frm_style.frm_center_submit .frm_submit input[type=submit],
.with_frm_style.frm_center_submit .frm_submit input[type=button],
.with_frm_style.frm_center_submit .frm_submit button{
    margin-bottom:8px !important;
}

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

.with_frm_style .frm_loading_form .frm_ajax_loading{
	/* keep this for reverse compatibility for old HTML */
	visibility:visible !important;
}

.with_frm_style .frm_loading_form .frm_button_submit {
    position: relative;
    opacity: .8;
    color: transparent !important;
    text-shadow: none !important;
}

.with_frm_style .frm_loading_form .frm_button_submit:hover,
.with_frm_style .frm_loading_form .frm_button_submit:active,
.with_frm_style .frm_loading_form .frm_button_submit:focus {
    cursor: not-allowed;
    color: transparent;
    outline: none !important;
    box-shadow: none;
}

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
    <?php $loader_size = 20; ?>
    top: 50%;
    left: 50%;
    margin-top: -<?php echo absint( $loader_size / 2 ) ?>px;
    margin-left: -<?php echo absint( $loader_size / 2 ) ?>px;
    width: <?php echo absint( $loader_size ) ?>px;
    height: <?php echo absint( $loader_size ) ?>px;

    -webkit-animation: spin 2s linear infinite;
    -moz-animation:    spin 2s linear infinite;
    -o-animation:      spin 2s linear infinite;
    animation:         spin 2s linear infinite;
}

<?php
foreach ( $styles as $style ) {
	include( dirname( __FILE__ ) . '/_single_theme.css.php' );
	unset( $style );
}
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

.with_frm_style .frm_checkbox label,
.with_frm_style .frm_radio label{
	display: inline;
	white-space:normal;
}

.with_frm_style .vertical_radio .frm_checkbox label,
.with_frm_style .vertical_radio .frm_radio label{
	display: block;
	padding-left: 20px;
	text-indent: -20px;
}

.frm_file_container .frm_file_link,
.with_frm_style .frm_radio label .frm_file_container,
.with_frm_style .frm_checkbox label .frm_file_container{
    display:inline-block;
    margin:5px;
    vertical-align:middle;
}

.with_frm_style .frm_radio input[type=radio]{
	-webkit-appearance:radio;
}

.with_frm_style .frm_checkbox input[type=checkbox]{
	-webkit-appearance:checkbox;
}

.with_frm_style .frm_radio input[type=radio],
.with_frm_style .frm_checkbox input[type=checkbox]{
	border-radius:initial;
	flex: none;
	display:inline-block;
	margin:4px 5px 0 0;
	width:auto;
	border:none;
	vertical-align:baseline;
	position: initial; /* override Bootstrap */
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
    border-color:<?php echo esc_html( $defaults['border_color'] ) ?>;
    border-top:none;
    border-left:none;
    border-right:none;
}

table.form_results.with_frm_style{
    border:1px solid #ccc;
}

table.form_results.with_frm_style tr td{
    text-align:left;
    color:<?php echo esc_html( $defaults['text_color'] ) ?>;
    padding:7px 9px;
    border-top:1px solid <?php echo esc_html( $defaults['border_color'] ) ?>;
}

table.form_results.with_frm_style tr.frm_even,
.frm-grid .frm_even{
    background-color:#fff;
}

table.form_results.with_frm_style tr.frm_odd,
.frm-grid .frm_odd{
	background-color:<?php echo esc_html( $defaults['bg_color_active'] ); ?>;
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

<?php if ( ! empty( $defaults['bg_color'] ) ) { ?>
#frm_loading .progress-striped .progress-bar{
    background-image:linear-gradient(45deg, <?php echo esc_html( $defaults['border_color'] ) ?> 25%, rgba(0, 0, 0, 0) 25%, rgba(0, 0, 0, 0) 50%, <?php echo esc_html( $defaults['border_color'] ) ?> 50%, <?php echo esc_html( $defaults['border_color'] ) ?> 75%, rgba(0, 0, 0, 0) 75%, rgba(0, 0, 0, 0));
    background-size:40px 40px;
}
<?php } ?>

#frm_loading .progress-bar{
    background-color:<?php echo esc_html( empty( $defaults['bg_color'] ) ? 'transparent' : $defaults['bg_color'] ); ?>;
    box-shadow:0 -1px 0 rgba(0, 0, 0, 0.15) inset;
    float:left;
    height:100%;
    line-height:20px;
    text-align:center;
    transition:width 0.6s ease 0s;
    width:100%;
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

<?php readfile( dirname( __FILE__ ) . '/frm_grids.css' ); ?>

.frm_conf_field.frm_left_container label.frm_primary_label{
	display:none;
}

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
    border-color:<?php echo esc_html( $defaults['border_color'] ) ?>;
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
    background-color:<?php echo esc_html( $defaults['bg_color'] ) ?>;
}

.frm_grid{
	background-color:<?php echo esc_html( $defaults['bg_color_active'] ); ?>;
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

.frm_form_field.frm_inline_container .frm_opt_container,
.frm_form_field.frm_right_container .frm_opt_container,
.frm_form_field.frm_left_container .frm_opt_container{
	padding-top:4px;
}

.with_frm_style .frm_inline_container.frm_grid_first label.frm_primary_label,
.with_frm_style .frm_inline_container.frm_grid label.frm_primary_label,
.with_frm_style .frm_inline_container.frm_grid_odd label.frm_primary_label,
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
    grid-gap: 2.5%;
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
.frm_form_field .frm_checkbox + .frm_checkbox,
.frm_form_field .frm_radio,
.frm_form_field .frm_radio + .frm_radio{
	margin-top: 0;
	margin-bottom: 0;
}

.frm_form_field.frm_scroll_box .frm_opt_container{
    height:100px;
    overflow:auto;
}

.frm_form_field.frm_html_scroll_box{
    height:100px;
    overflow:auto;
    background-color:<?php echo esc_html( $defaults['bg_color'] ) ?>;
    border-color:<?php echo esc_html( $defaults['border_color'] ) ?>;
    border-width:<?php echo esc_html( $defaults['field_border_width'] ) ?>;
    border-style:<?php echo esc_html( $defaults['field_border_style'] ) ?>;
    -moz-border-radius:<?php echo esc_html( $defaults['border_radius'] ) ?>;
    -webkit-border-radius:<?php echo esc_html( $defaults['border_radius'] ) ?>;
    border-radius:<?php echo esc_html( $defaults['border_radius'] ) ?>;
    width:<?php echo esc_html( $defaults['field_width'] == '' ? 'auto' : $defaults['field_width'] ) ?>;
    max-width:100%;
    font-size:<?php echo esc_html( $defaults['field_font_size'] ) ?>;
    padding:<?php echo esc_html( $defaults['field_pad'] ) ?>;
    -webkit-box-sizing:border-box;
    -moz-box-sizing:border-box;
    box-sizing:border-box;
    outline:none;
    font-weight:normal;
    box-shadow:0 1px 1px rgba(0, 0, 0, 0.075) inset;
}

.frm_form_field.frm_total input,
.frm_form_field.frm_total textarea{
    opacity:1;
    background-color:transparent !important;
    border:none !important;
    font-weight:bold;
    -moz-box-shadow:none;
    -webkit-box-shadow:none;
    width:auto !important;
    box-shadow:none !important;
    display:inline;
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

.frm_form_field.frm_label_justify label.frm_primary_label{
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

/* Fonts */
@font-face {
	font-family:'s11-fp';
	src:url('../fonts/s11-fp.eot?v=<?php echo esc_attr( FrmAppHelper::$font_version ); ?>');
	src:local('☺'), url('../fonts/s11-fp.woff?v=<?php echo esc_attr( FrmAppHelper::$font_version ); ?>') format('woff'), url('../fonts/s11-fp.ttf?v=<?php echo esc_attr( FrmAppHelper::$font_version ); ?>') format('truetype'), url('../fonts/s11-fp.svg?v=<?php echo esc_attr( FrmAppHelper::$font_version ); ?>') format('svg');
	font-weight:normal;
	font-style:normal;
}

<?php readfile( FrmAppHelper::plugin_path() . '/css/font_icons.css' ); ?>
<?php do_action( 'frm_include_front_css', compact( 'defaults' ) ); ?>

/* Responsive */
@media only screen and (max-width: 900px) {
	.frm_form_field .frm_repeat_grid .frm_form_field.frm_sixth label.frm_primary_label,
	.frm_form_field .frm_repeat_grid .frm_form_field.frm_seventh label.frm_primary_label,
	.frm_form_field .frm_repeat_grid .frm_form_field.frm_eighth label.frm_primary_label{
    	display: block !important;
	}
}

@media only screen and (max-width: 600px) {
    .frm_form_field.frm_four_col .frm_opt_container{
        grid-template-columns: repeat(2, 1fr);
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
		-moz-box-shadow:2px 0px 4px -1px rgba(0,0,0,.08);
	}

	.with_frm_style .g-recaptcha iframe,
	.with_frm_style .frm-g-recaptcha iframe{
		width:100%;
	}
}
<?php

$frm_settings = FrmAppHelper::get_settings();
if ( $frm_settings->old_css ) {
	readfile( dirname( __FILE__ ) . '/frm_old_grids.css' );
}

echo strip_tags( $defaults['custom_css'] ); // WPCS: XSS ok.
