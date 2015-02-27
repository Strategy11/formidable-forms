<div class="clear"></div>
<?php if (isset($message) && $message != ''){ if(is_admin() and !defined('DOING_AJAX')){ ?><div id="message" class="updated" style="padding:5px;"><?php } echo $message; if(is_admin() and !defined('DOING_AJAX')){ ?></div><?php } } ?>

<?php if( isset($errors) && is_array($errors) && count($errors) > 0 ){ ?>
    <div class="error">
        <ul id="frm_errors">
            <?php foreach( $errors as $error )
                echo '<li>' . $error . '</li>';
            ?>
        </ul>
    </div>
<?php } ?>