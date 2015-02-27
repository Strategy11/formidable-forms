<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
    <title><?php bloginfo('name'); ?></title>
    <?php 
    wp_admin_css( 'global' );
    wp_admin_css();
    wp_admin_css( 'colors' );
    wp_admin_css( 'ie' );
    if ( is_multisite() )
    	wp_admin_css( 'ms' );
    wp_enqueue_script('utils');

    do_action('admin_print_styles');
    do_action('admin_print_scripts');
    
    ?>
<style type="text/css">
#wpadminbar{display:none;}
.prepop{
    float:left;
    width:235px;
    list-style:none;
    overflow:auto;
    border-right: 2px solid #DEDEDE;
    padding-right:10px;
    margin:0;
}
.prepop li{
    margin: 0 0 3px;
}

.prepop li a{
    background:#F5F5F5;
    border:1px solid #EEEEEE;
    border-color:#EEEEEE #DEDEDE #DEDEDE #EEEEEE;
    display: block;
    font-weight: bold;
    height: 30px;
    line-height: 30px;
    margin: 0 10px 0 0;
    text-align: center;
    text-decoration: none;
    cursor:pointer;
}
</style>
</head>
<body class="wp-admin no-js wp-core-ui <?php echo apply_filters( 'admin_body_class', '' ) . " $admin_body_class"; ?>" style="min-width:300px;background-color:#fff;">
<div style="padding:10px;">
<p class="howto"><?php _e('Edit or add field options (one per line)', 'formidable') ?></p>
<ul class="prepop">
    <?php foreach($prepop as $label => $pop){ ?>
    <li><a onclick='frmPrePop(<?php echo str_replace("'", '&#145;', json_encode($pop)) ?>); return false;'><?php echo $label ?></a></li>
    <?php } ?>
</ul>
<textarea name="frm_bulk_options" id="frm_bulk_options" style="height:240px;width:335px;float:right;">
<?php foreach($field->options as $fopt){
if(is_array($fopt)){
    $label = (isset($fopt['label'])) ? $fopt['label'] : reset($fopt);
    $value = (isset($fopt['value'])) ? $fopt['value'] : $label;
    if($label != $value and isset($field->field_options['separate_value']) and $field->field_options['separate_value'])
        echo "$label|$value\n";
    else
        echo $label ."\n";        
}else{
    echo $fopt ."\n";
}   
} ?>
</textarea>

<p class="submit clear">
<input type="button" onclick="window.top.frmUpdateOpts(<?php echo $field->id ?>,jQuery('#frm_bulk_options').val()); window.top.tb_remove();" class="button-primary" value="<?php _e('Update Field Choices', 'formidable') ?>" />
</p>
</div>

<script type="text/javascript">
function frmPrePop(opts){
    jQuery('#frm_bulk_options').val(opts.join("\n"));
    return false;
}
</script>
</body>
</html>