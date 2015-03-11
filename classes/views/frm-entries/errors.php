<?php
if ( isset($include_extra_container) ) { ?>
<div class="<?php echo esc_attr( $include_extra_container ) ?>" id="frm_form_<?php echo esc_attr( $form->id ) ?>_container">
<?php
}
if (isset($message) && $message != ''){
    if ( FrmAppHelper::is_admin() ) {
        ?><div id="message" class="frm_message updated frm_msg_padding"><?php echo $message ?></div><?php
    }else{
        FrmFormsHelper::get_scroll_js($form->id);
        echo $message;
    }
}

if ( isset($errors) && is_array( $errors ) && ! empty( $errors ) ) {

	if ( isset( $form ) && is_object( $form ) ) {
    	FrmFormsHelper::get_scroll_js( $form->id );
	} ?>
<div class="frm_error_style">
<?php
$img = '';
if ( ! FrmAppHelper::is_admin() ) {
    $img = apply_filters('frm_error_icon', $img);
    if ( $img && ! empty($img) ) {
    ?><img src="<?php echo esc_attr( $img ) ?>" alt="" />
<?php
    }
}

$frm_settings = FrmAppHelper::get_settings();
if ( empty( $frm_settings->invalid_msg ) ) {
    $show_img = false;
	foreach ( $errors as $error ) {
        if ( $show_img && ! empty($img) ) {
            ?><img src="<?php echo esc_attr( $img ) ?>" alt="" /><?php
        }else{
            $show_img = true;
        }
        echo $error . '<br/>';
    }
}else{
    echo $frm_settings->invalid_msg;

    $show_img = true;
	foreach ( $errors as $err_key => $error ) {
        if ( ! is_numeric($err_key) && ( $err_key == 'cptch_number' || strpos($err_key, 'field') === 0 ) ) {
            continue;
        }

        echo '<br/>';
        if ( $show_img && ! empty($img) ) {
            ?><img src="<?php echo esc_attr( $img ) ?>" alt="" /><?php
        }else{
            $show_img = true;
        }
        echo $error;
    }
} ?>
</div>
<?php
}

if ( isset($include_extra_container) ) { ?>
</div>
<?php
}