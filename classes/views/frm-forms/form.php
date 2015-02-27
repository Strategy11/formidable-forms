<?php wp_nonce_field('frm_save_form_nonce', 'frm_save_form'); ?>

<div id="frm_form_editor_container">
<div id="titlediv">
    <input type="text" name="name" value="<?php echo esc_attr($form->name); ?>" id="title" placeholder="<?php esc_attr_e('Enter title here') ?>" />
    <div id="edit-slug-box" class="hide-if-no-js">
        <div class="alignright" style="width:13em;max-width:30%">
        <strong><?php _e('Form Key:', 'formidable') ?></strong>
        <div id="editable-post-name" class="frm_ipe_form_key" title="<?php _e('Click to edit.', 'formidable') ?>"><?php echo $form->form_key; ?></div>
        </div>
        <div class="frm_ipe_form_desc alignleft" style="width:70%"><?php echo ($form->description == '') ? __('(Click here to add a description or instructions)', 'formidable') : force_balance_tags($form->description); ?></div>
        <div style="clear:both"></div>
    </div>
</div>

<div <?php echo version_compare( $GLOBALS['wp_version'], '3.7.2', '>') ? 'class="postbox"' : ''; ?>>
<div class="frm_no_fields <?php echo ( isset($values['fields']) && ! empty($values['fields']) ) ? 'frm_hidden' : ''; ?>">
    <div class="frm_brace">{</div>
	<div class="alignright frm_nofields_text">	
    	<div class="frm_all_field_inst"><?php _e('Add fields here', 'formidable') ?></div>
    	<div class="frm_drag_inst"><?php _e('(Just click or drag)', 'formidable') ?></div>
	</div>
</div>
<ul id="new_fields" class="<?php echo version_compare( $GLOBALS['wp_version'], '3.7.2', '>') ? 'inside' : ''; ?>">
<?php
if ( isset($values['fields']) && ! empty($values['fields']) ) {
    $count = 0;
    foreach ( $values['fields'] as $field ) {
        $count++;
        $field_name = 'item_meta['. $field['id'] .']';
        require(FrmAppHelper::plugin_path() .'/classes/views/frm-forms/add_field.php');
        unset($field, $field_name);
    }
    unset($count);
} ?>
</ul>
</div>

</div>