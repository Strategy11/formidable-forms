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
	if ( is_multisite() ) {
    	wp_admin_css( 'ms' );
	}

    do_action('admin_print_styles');
    do_action('admin_print_scripts');

    ?>
</head>
<body class="wp-admin no-js wp-core-ui frm_field_opts_popup <?php echo esc_attr( apply_filters( 'admin_body_class', '' ) . ' ' . $admin_body_class ); ?>">
<div class="frm_med_padding">
<p class="howto"><?php _e( 'Edit or add field options (one per line)', 'formidable' ) ?></p>
<ul class="frm_prepop">
	<?php foreach ( $prepop as $label => $pop ) { ?>
    <li><a href="javascript:void(0)" onclick='frmPrePop(<?php echo str_replace("'", '&#145;', json_encode($pop)) ?>); return false;'><?php echo esc_html( $label ) ?></a></li>
    <?php } ?>
</ul>
<textarea name="frm_bulk_options" id="frm_bulk_options">
<?php
$other_array = array();
foreach ( $field->options as $fkey => $fopt ) {
    //If it is an other option, don't include it
    if ( $fkey && strpos( $fkey, 'other') !== false ) {
        continue;
    }
	if ( is_array( $fopt ) ) {
        $label = (isset($fopt['label'])) ? $fopt['label'] : reset($fopt);
        $value = (isset($fopt['value'])) ? $fopt['value'] : $label;
		if ( $label != $value && FrmField::is_option_true( $field, 'separate_value' ) ) {
            echo "$label|$value\n";
		} else {
            echo $label ."\n";
        }
	} else {
        echo $fopt ."\n";
    }
} ?>
</textarea>

<p class="submit frm_clear">
<input type="button" onclick="frmUpdateBulkOpts(<?php echo (int) $field->id ?>)" class="button-primary" value="<?php esc_attr_e( 'Update Field Choices', 'formidable' ) ?>" />
</p>
</div>

</body>
</html>
