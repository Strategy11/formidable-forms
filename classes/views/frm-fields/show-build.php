<?php if ( in_array( $display['type'], array( 'text', 'website', 'email', 'url' ) ) ) { ?>
	<input type="text" name="<?php echo esc_attr( $field_name ) ?>" id="<?php echo esc_attr( $html_id ) ?>" value="<?php echo esc_attr( $field['default_value'] ); ?>" class="dyn_default_value" />
<?php } else if ( $field['type'] == 'textarea' ) { ?>
    <textarea name="<?php echo esc_attr( $field_name ) ?>" <?php
		echo ( FrmField::is_option_true( $field, 'size' ) ) ? esc_attr( 'style="width:' . $field['size'] . ( is_numeric( $field['size'] ) ? 'px' : '' ) . ';"' ) : '';
    ?> rows="<?php echo esc_attr( $field['max'] ); ?>" id="<?php echo esc_attr( $html_id ) ?>" class="dyn_default_value"><?php echo FrmAppHelper::esc_textarea(force_balance_tags($field['default_value'])); ?></textarea>

<?php

} else if ( $field['type'] == 'radio' || $field['type'] == 'checkbox' ) {
    $field['default_value'] = maybe_unserialize($field['default_value']);
    if ( isset($field['post_field']) && $field['post_field'] == 'post_category' ) {
		do_action( 'frm_after_checkbox', array( 'field' => $field, 'field_name' => $field_name, 'type' => $field['type'] ) );
    } else {
        do_action('frm_add_multiple_opts_labels', $field); ?>
        <ul id="frm_field_<?php echo esc_attr( $field['id'] ) ?>_opts" class="frm_sortable_field_opts frm_clear<?php echo (count($field['options']) > 10) ? ' frm_field_opts_list' : ''; ?>">
			<?php include( FrmAppHelper::plugin_path() . '/classes/views/frm-fields/radio.php' ); ?>
        </ul>
    <?php
    }
} else if ( $field['type'] == 'select' ) {
	include( FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/dropdown-field.php' );
} else if ( $field['type'] == 'captcha' ) {
	if ( empty($frm_settings->pubkey) ) { ?>
    <div class="howto frm_no_captcha_text"><?php printf(__( 'Your captcha will not appear on your form until you %1$sset up%2$s the Site and Secret Keys', 'formidable' ), '<a href="?page=formidable-settings">', '</a>') ?></div>
    <?php
    } ?>
	<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/recaptcha.png' ) ?>" class="recaptcha_placeholder" alt="reCaptcha"/>
    <input type="hidden" name="<?php echo esc_attr( $field_name ) ?>" value="1" />
<?php
} else {
    do_action( 'frm_display_added_fields', $field );
	do_action( 'frm_display_added_' . $field['type'] . '_field', $field );
}
