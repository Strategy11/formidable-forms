<?php
if ( ! is_array($field['options']) ) {
    return;
}

foreach ( $field['options'] as $opt_key => $opt ) {
    $field_val = apply_filters('frm_field_value_saved', $opt, $opt_key, $field);
    $opt = apply_filters('frm_field_label_seen', $opt, $opt_key, $field);

    // Get string for Other text field, if needed
	$other_val = FrmFieldsHelper::get_other_val( compact( 'opt_key', 'field' ) );

    $checked = ( $other_val || isset($field['value']) &&  (( ! is_array($field['value']) && $field['value'] == $field_val ) || (is_array($field['value']) && in_array($field_val, $field['value']) ) ) ) ? ' checked="checked"':'';

	if ( FrmFieldsHelper::is_other_opt( $opt_key ) ) {
		include( FrmAppHelper::plugin_path() . '/pro/classes/views/frmpro-fields/other-option.php' );
    } else {
		include( FrmAppHelper::plugin_path() . '/classes/views/frm-fields/single-option.php' );
    }

    unset($checked, $other_val);
}
