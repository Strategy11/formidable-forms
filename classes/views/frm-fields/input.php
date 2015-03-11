<?php if ( in_array($field['type'], array( 'email', 'url', 'text')) ) { ?>
<input type="<?php echo ( $frm_settings->use_html || $field['type'] == 'password' ) ? $field['type'] : 'text'; ?>" id="<?php echo esc_attr( $html_id ) ?>" name="<?php echo esc_attr( $field_name ) ?>" value="<?php echo esc_attr( $field['value'] ) ?>" <?php do_action('frm_field_input_html', $field) ?>/>
<?php }else if ($field['type'] == 'textarea'){ ?>
<textarea name="<?php echo esc_attr( $field_name ) ?>" id="<?php echo esc_attr( $html_id ) ?>" <?php
if ( $field['max'] ) {
    echo 'rows="'. esc_attr( $field['max'] ) .'" ';
}
do_action('frm_field_input_html', $field);
?>><?php echo FrmAppHelper::esc_textarea($field['value']) ?></textarea>
<?php

} else if ( $field['type'] == 'radio' ) {
    $read_only = false;
    if ( isset($field['read_only']) && $field['read_only'] && ( ! isset($frm_vars['readonly']) || $frm_vars['readonly'] != 'disabled') && ! FrmAppHelper::is_admin() ) {
        $read_only = true; ?>
<input type="hidden" value="<?php echo esc_attr( $field['value'] ) ?>" name="<?php echo esc_attr( $field_name ) ?>" />
<?php
    }

    if ( isset($field['post_field']) && $field['post_field'] == 'post_category' ) {
        do_action('frm_after_checkbox', array( 'field' => $field, 'field_name' => $field_name, 'type' => $field['type']));
    } else if ( is_array($field['options']) ) {
        foreach ( $field['options'] as $opt_key => $opt ) {
            if ( isset($atts) && isset($atts['opt']) && ($atts['opt'] != $opt_key)) {
                continue;
            }

            $field_val = apply_filters('frm_field_value_saved', $opt, $opt_key, $field);
            $opt = apply_filters('frm_field_label_seen', $opt, $opt_key, $field); ?>
			<div class="<?php echo apply_filters('frm_radio_class', 'frm_radio', $field, $field_val)?>"><?php

			if ( ! isset( $atts ) || ! isset( $atts['label'] ) || $atts['label'] ) {
				?><label for="<?php echo esc_attr( $html_id ) ?>-<?php echo esc_attr( $opt_key ) ?>"><?php
            }
            $checked = FrmAppHelper::check_selected($field['value'], $field_val) ? 'checked="checked" ' : ' ';

            $other_opt = false;
            $other_args = FrmAppHelper::prepare_other_input( $field, $other_opt, $checked, array( 'field_name' => $field_name, 'opt_key' => $opt_key ) );
            ?>
            <input type="radio" name="<?php echo esc_attr( $field_name ) ?>" id="<?php echo esc_attr( $html_id ) ?>-<?php echo esc_attr( $opt_key ) ?>" value="<?php echo esc_attr( $field_val ) ?>" <?php
            echo $checked;
            do_action('frm_field_input_html', $field);
?>/><?php

			if ( ! isset( $atts ) || ! isset( $atts['label'] ) || $atts['label'] ) {
                echo ' '. $opt .'</label>';
            }

            if ( $other_opt ) { ?>
                <input type="text" <?php echo ( $read_only ? ' readonly="readonly" disabled="disabled"' : '' ); ?> class="frm_other_input <?php echo ( $checked != ' ' ? '' : ' frm_pos_none' ); ?>" name="<?php echo esc_attr( $other_args['name'] ) ?>" value="<?php echo esc_attr( $other_args['value'] ); ?>"><?php
            }
            unset( $other_opt, $other_args );
?></div>
<?php
        }
    }
}else if ($field['type'] == 'select'){
    $read_only = false;
    if ( isset($field['post_field']) && $field['post_field'] == 'post_category' ) {
        echo FrmFieldsHelper::dropdown_categories( array( 'name' => $field_name, 'field' => $field) );
    }else{
        if ( isset( $field['read_only'] ) && $field['read_only'] && ( ! isset( $frm_vars['readonly'] ) || $frm_vars['readonly'] != 'disabled' ) && ! FrmAppHelper::is_admin() ) {
            $read_only = true; ?>
<input type="hidden" value="<?php echo esc_attr($field['value']) ?>" name="<?php echo esc_attr( $field_name ) ?>" id="<?php echo esc_attr( $html_id ) ?>" />
<select disabled="disabled" <?php do_action('frm_field_input_html', $field) ?>>
<?php   }else{ ?>
<select name="<?php echo esc_attr( $field_name ) ?>" id="<?php echo esc_attr( $html_id ) ?>" <?php do_action('frm_field_input_html', $field) ?>>
<?php   }

		$other_opt = $other_checked = false;
		foreach ( $field['options'] as $opt_key => $opt ) {
			$field_val = apply_filters( 'frm_field_value_saved', $opt, $opt_key, $field );
			$opt = apply_filters( 'frm_field_label_seen', $opt, $opt_key, $field );
			$selected = FrmAppHelper::check_selected( $field['value'], $field_val );
			if ( $other_opt === false ) {
				$other_args = FrmAppHelper::prepare_other_input( $field, $other_opt, $selected, array( 'field_name' => $field_name, 'opt_key' => $opt_key ) );
				if ( FrmAppHelper::is_other_opt( $opt_key ) && $selected ) {
					$other_checked = true;
				}
			}
			?>
        <option value="<?php echo esc_attr($field_val) ?>" <?php echo $selected ? ' selected="selected"' : ''; ?><?php echo ( FrmAppHelper::is_other_opt( $opt_key ) ) ? ' class="frm_other_trigger"' : '';?>><?php echo ($opt == '') ? ' ' : $opt; ?></option>
    <?php
    	} ?>
</select>
<?php
        if ( $other_opt ) { ?>
        <input type="text" <?php echo ( $read_only ? 'readonly="readonly" disabled="disabled"' : '' );?> class="frm_other_input<?php echo ( $other_checked ? '' : ' frm_pos_none' ); echo ( $field['multiple'] ? ' frm_other_full' : '' ); ?>" name="<?php echo esc_attr( $other_args['name'] ) ?>" value="<?php echo esc_attr($other_args['value']);?>"><?php
        }
    }
}else if ($field['type'] == 'checkbox'){
    $checked_values = $field['value'];
    $read_only = false;

    if ( isset($field['read_only']) && $field['read_only'] && ( ! isset($frm_vars['readonly']) || $frm_vars['readonly'] != 'disabled') && ! FrmAppHelper::is_admin() ) {
        $read_only = true;
        if ( $checked_values ) {
            foreach ( (array) $checked_values as $checked_value ) { ?>
<input type="hidden" value="<?php echo esc_attr( $checked_value ) ?>" name="<?php echo esc_attr( $field_name ) ?>[]" />
<?php
            }
        } else { ?>
<input type="hidden" value="<?php echo esc_attr( $checked_values ) ?>" name="<?php echo esc_attr( $field_name ) ?>[]" />
<?php
        }
    }

    if ( isset($field['post_field']) && $field['post_field'] == 'post_category' ) {
        do_action('frm_after_checkbox', array( 'field' => $field, 'field_name' => $field_name, 'type' => $field['type']));
    } else if ( $field['options'] ) {
        foreach ( $field['options'] as $opt_key => $opt ) {
            if ( isset($atts) && isset($atts['opt']) && ($atts['opt'] != $opt_key) ) {
                continue;
            }

            $field_val = apply_filters('frm_field_value_saved', $opt, $opt_key, $field);
            $opt = apply_filters('frm_field_label_seen', $opt, $opt_key, $field);
            $checked = FrmAppHelper::check_selected($checked_values, $field_val) ? ' checked="checked"' : '';

            // Check if other opt, and get values for other field if needed
            $other_opt = false;
            $other_args = FrmAppHelper::prepare_other_input( $field, $other_opt, $checked, array( 'field_name' => $field_name, 'opt_key' => $opt_key ) );

            ?>
			<div class="<?php echo esc_attr( apply_filters( 'frm_checkbox_class', 'frm_checkbox', $field, $field_val ) ) ?>" id="frm_checkbox_<?php echo esc_attr( $field['id'] ) ?>-<?php echo esc_attr( $opt_key ) ?>"><?php

            if ( ! isset( $atts ) || ! isset( $atts['label'] ) || $atts['label'] ) {
                ?><label for="<?php echo esc_attr( $html_id ) ?>-<?php echo esc_attr( $opt_key ) ?>"><?php
            }

            ?><input type="checkbox" name="<?php echo esc_attr( $field_name ) ?>[<?php echo ( $other_opt ? esc_attr( $opt_key ) : '' ) ?>]" id="<?php echo esc_attr( $html_id ) ?>-<?php echo esc_attr( $opt_key ) ?>" value="<?php echo esc_attr( $field_val ) ?>" <?php echo $checked ?> <?php do_action('frm_field_input_html', $field) ?> /><?php

            if ( ! isset( $atts ) || ! isset( $atts['label'] ) || $atts['label'] ) {
                echo ' '. $opt .'</label>';
            }

            if ( $other_opt ) { ?>
                <input type="text" <?php echo ( $read_only ? ' readonly="readonly" disabled="disabled"' : '' );?> class="frm_other_input <?php echo ( $checked ? '' : 'frm_pos_none' ); ?>" name="<?php echo esc_attr( $other_args['name'] ) ?>" value="<?php echo esc_attr( $other_args['value'] );?>"><?php
            }

            unset( $other_opt, $other_args, $checked );

            ?></div>
<?php
        }
    }
} else if ( $field['type'] == 'captcha' && ! FrmAppHelper::is_admin() ) {
    $frm_settings = FrmAppHelper::get_settings();
    if ( ! empty($frm_settings->pubkey) ) {
        FrmFieldsHelper::display_recaptcha($field);
    }
} else {
    do_action('frm_form_fields', $field, $field_name, compact('errors', 'html_id'));
}