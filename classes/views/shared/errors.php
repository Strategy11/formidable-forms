<div class="clear"></div>
<?php
if ( isset($message) && $message != '' ) {
    if ( FrmAppHelper::is_admin() ) {
        ?><div id="message" class="updated frm_msg_padding"><?php
        echo $message;
        ?></div><?php
    } else {
        echo $message;
    }
}

if ( isset($errors) && is_array($errors) && count($errors) > 0 ) { ?>
    <div class="error">
        <ul id="frm_errors">
            <?php
            foreach ( $errors as $error ) {
                echo '<li>' . $error . '</li>';
            }
            ?>
        </ul>
    </div>
<?php
}
