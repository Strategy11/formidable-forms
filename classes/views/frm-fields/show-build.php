<?php if ( in_array( $display['type'], array( 'text', 'website', 'email', 'url' ) ) ) { ?>
    <input type="text" name="<?php echo esc_attr( $field_name ) ?>" id="<?php echo esc_attr( $html_id ) ?>" value="<?php echo esc_attr( $field['default_value'] ); ?>" <?php echo ( FrmField::is_option_true( $field, 'size' ) ) ? esc_attr( 'style="width:'. $field['size'] . ( is_numeric($field['size']) ? 'px' : '') .';"' ) : ''; ?> class="dyn_default_value" />
<?php } else if ( $field['type'] == 'textarea' ) { ?>
    <textarea name="<?php echo esc_attr( $field_name ) ?>" <?php
    echo ( FrmField::is_option_true( $field, 'size' ) ) ? esc_attr( 'style="width:'. $field['size'] . ( is_numeric($field['size']) ? 'px' : '') .';"' ) : '';
    ?> rows="<?php echo esc_attr( $field['max'] ); ?>" id="<?php echo esc_attr( $html_id ) ?>" class="dyn_default_value"><?php echo FrmAppHelper::esc_textarea(force_balance_tags($field['default_value'])); ?></textarea>

<?php

} else if ( $field['type'] == 'radio' || $field['type'] == 'checkbox' ) {
    $field['default_value'] = maybe_unserialize($field['default_value']);
    if ( isset($field['post_field']) && $field['post_field'] == 'post_category' ) {
		do_action( 'frm_after_checkbox', array( 'field' => $field, 'field_name' => $field_name, 'type' => $field['type'] ) );
    } else {
        do_action('frm_add_multiple_opts_labels', $field); ?>
        <ul id="frm_field_<?php echo esc_attr( $field['id'] ) ?>_opts" class="frm_sortable_field_opts frm_clear<?php echo (count($field['options']) > 10) ? ' frm_field_opts_list' : ''; ?>">
        <?php include(FrmAppHelper::plugin_path() .'/classes/views/frm-fields/radio.php'); ?>
        </ul>
    <?php
    }
} else if ( $field['type'] == 'select' ) {
    if ( isset($field['post_field']) && $field['post_field'] == 'post_category' ) {
		echo FrmFieldsHelper::dropdown_categories( array( 'name' => $field_name, 'field' => $field ) );
    } else { ?>
	<select name="<?php echo esc_attr( $field_name ) . ( FrmField::is_option_true( $field, 'multiple' ) ? '[]' : '' ); ?>" <?php
		echo FrmField::is_option_true( $field, 'size' ) ? 'class="auto_width"' : '';
		echo FrmField::is_option_true( $field, 'multiple' ) ? ' multiple="multiple"' : ''; ?> >
		<?php foreach ( $field['options'] as $opt_key => $opt ) {
            $field_val = apply_filters('frm_field_value_saved', $opt, $opt_key, $field);
            $opt = apply_filters('frm_field_label_seen', $opt, $opt_key, $field);
			$selected = ( $field['default_value'] == $field_val || FrmFieldsHelper::get_other_val( array( 'opt_key', 'field' ) ) ) ? ' selected="selected"' : ''; ?>
            <option value="<?php echo esc_attr( $field_val ) ?>"<?php echo $selected ?>><?php echo esc_html( $opt ) ?> </option>
        <?php } ?>
    </select>
<?php }

    if ( $display['default_blank'] ) { ?>
        <span id="frm_clear_on_focus_<?php echo esc_attr( $field['id'] ) ?>" class="frm_clear_on_focus frm-show-click">
		<?php FrmFieldsHelper::show_default_blank_js( $field['default_blank'] ); ?>
        </span>
    <?php } ?>
    <div class="clear"></div>
    <div class="frm-show-click frm_small_top_margin">
    <?php

	if ( ! isset( $field['post_field'] ) || ! in_array( $field['post_field'], array( 'post_category' ) ) ) { ?>
        <?php do_action('frm_add_multiple_opts_labels', $field); ?>
        <ul id="frm_field_<?php echo esc_attr( $field['id'] ) ?>_opts" class="frm_sortable_field_opts<?php echo ( count($field['options']) > 10 ) ? ' frm_field_opts_list' : ''; ?>">
        <?php FrmFieldsHelper::show_single_option($field); ?>
        </ul>
<?php
    } ?>
    </div>
<?php
} else if ( $field['type'] == 'captcha' ) {
	if ( empty($frm_settings->pubkey) ) { ?>
    <div class="howto frm_no_captcha_text"><?php printf(__( 'Your captcha will not appear on your form until you %1$sset up%2$s the Site and Private Keys', 'formidable' ), '<a href="?page=formidable-settings">', '</a>') ?></div>
    <?php
    } ?>
	<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/recaptcha.png' ) ?>" class="recaptcha_placeholder" alt="reCaptcha"/>
    <input type="hidden" name="<?php echo esc_attr( $field_name ) ?>" value="1" />
<?php
} else {
    do_action( 'frm_display_added_fields', $field );
}
