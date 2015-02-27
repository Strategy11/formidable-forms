<?php global $frm_settings; 
if (isset($message) && $message != ''){ 
    if(is_admin() and !defined('DOING_AJAX')){ 
        ?><div id="message" class="frm_message updated" style="padding:5px;"><?php echo $message ?></div><?php 
    }else{ 
        ?><script type="text/javascript">jQuery(document).ready(function($){frmScrollMsg(<?php echo $form->id ?>);})</script><?php
        echo $message; 
    }
} 

if( isset($errors) && is_array($errors) && !empty($errors) ){
    global $frm_settings;

if ( isset($form) && is_object($form) ) { ?>
<script type="text/javascript">jQuery(document).ready(function($){frmScrollMsg(<?php echo $form->id ?>);})</script>
<?php } ?>
<div class="frm_error_style"> 
<?php
$img = '';
if(!is_admin() or defined('DOING_AJAX')){ 
    $img = apply_filters('frm_error_icon', $img);
    if($img and !empty($img)){
    ?><img src="<?php echo $img ?>" alt="" />
<?php 
    }
} 
    
if(empty($frm_settings->invalid_msg)){
    $show_img = false;
    foreach( $errors as $error ){
        if($show_img and !empty($img)){ 
            ?><img src="<?php echo $img ?>" alt="" /><?php 
        }else{
            $show_img = true;
        }
        echo $error . '<br/>';
    }
}else{
    echo $frm_settings->invalid_msg;

    $show_img = true;
    foreach( $errors as $err_key => $error ){
        if(!is_numeric($err_key) and ($err_key == 'cptch_number' or strpos($err_key, 'field') === 0 or strpos($err_key, 'captcha') === 0 ))
            continue;
          
        echo '<br/>'; 
        if($show_img and !empty($img)){ 
            ?><img src="<?php echo $img ?>" alt="" /><?php 
        }else{
            $show_img = true;
        }
        echo $error;
    }
} ?>
</div>
<?php } ?>