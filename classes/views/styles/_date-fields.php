<?php
$jquery_themes = FrmStylesHelper::jquery_themes();

$alt_img_name = array(
    'ui-lightness'  => 'ui_light',
    'ui-darkness'   => 'ui_dark',
    'start'         => 'start_menu',
    'redmond'       => 'windoze',
    'vader'         => 'black_matte',
    'mint-choc'     => 'mint_choco',
);
$theme_names = array_keys($jquery_themes);
$theme_names = array_combine($theme_names, $theme_names);

foreach ( $theme_names as $k => $v ) {
	$theme_names[ $k ] = str_replace( '-', '_', $v );
    unset($k, $v);
}

$alt_img_name = array_merge($theme_names, $alt_img_name);
unset($theme_names);

?>

<div class="field-group clearfix frm-half frm-first-row">
	<label><?php _e( 'Theme', 'formidable' ) ?></label>
	<select name="<?php echo esc_attr( $frm_style->get_field_name('theme_selector') ) ?>">
	    <?php foreach ( $jquery_themes as $theme_name => $theme_title ) { ?>
        <option value="<?php echo esc_attr( $theme_name ) ?>" id="90_<?php echo esc_attr( $alt_img_name[ $theme_name ] ); ?>" <?php selected($theme_title, $style->post_content['theme_name']) ?>><?php echo esc_html( $theme_title ) ?></option>
        <?php } ?>
        <option value="-1" <?php selected('-1', $style->post_content['theme_name']) ?>>&mdash; <?php _e( 'None', 'formidable' ) ?> &mdash;</option>
	</select>
</div>

<div class="field-group clearfix frm-half frm-first-row frm_right_text">
    <img id="frm_show_cal" src="//jqueryui.com/resources/images/themeGallery/theme_90_<?php echo esc_attr( $alt_img_name[ $style->post_content['theme_css'] ] ) ?>.png" alt="" />
	<input type="hidden" value="<?php echo esc_attr($style->post_content['theme_css']) ?>" id="frm_theme_css" name="<?php echo esc_attr( $frm_style->get_field_name('theme_css') ) ?>" />
    <input type="hidden" value="<?php echo esc_attr($style->post_content['theme_name']) ?>" id="frm_theme_name" name="<?php echo esc_attr( $frm_style->get_field_name('theme_name') ) ?>" />
</div>
<div class="clear"></div>
