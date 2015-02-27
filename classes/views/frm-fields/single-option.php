<div id="frm_delete_field_<?php echo $field['id']; ?>-<?php echo $opt_key ?>_container" class="frm_single_option">
<a href="javascript:void(0)" class="frm_single_visible_hover frm_icon_font frm_delete_icon"> </a>
<?php if ($field['type'] != 'select'){ ?>
<input type="<?php echo $field['type'] ?>" name="<?php echo $field_name ?><?php echo ($field['type'] == 'checkbox')?'[]':''; ?>" value="<?php echo esc_attr($field_val) ?>"<?php echo isset($checked)? $checked : ''; ?>/> 
<?php } ?>
<label class="frm_ipe_field_option field_<?php echo $field['id']?>_option <?php echo $field['separate_value'] ? 'frm_with_key' : ''; ?>" id="field_<?php echo $field['id']?>-<?php echo $opt_key ?>"><?php echo ($opt == '') ? __('(Blank)', 'formidable') : $opt ?></label>
<span class="frm_option_key field_<?php echo $field['id']?>_option_key" <?php echo $field['separate_value'] ? '' : "style='display:none;'"; ?>>
<label class="frm-show-click frm_ipe_field_option_key" id="field_key_<?php echo $field['id']?>-<?php echo $opt_key ?>"><?php echo ($field_val == '') ? __('(Blank)', 'formidable') : $field_val ?></label>
</span>
</div>
<div class="clear"></div>
<?php
unset($field_val);
unset($opt);
unset($opt_key);
?>