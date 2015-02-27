<div class="field-group clearfix frm-half frm-first-row">
	<label><?php _e('Radio', 'formidable') ?></label>
	<select name="<?php echo $frm_style->get_field_name('radio_align') ?>" id="frm_radio_align">
	    <?php foreach (array('block' => 'Multiple Rows', 'inline' => 'Single Row') as $pos => $pos_label){ ?>
	        <option value="<?php echo $pos ?>" <?php selected($style->post_content['radio_align'], $pos) ?>><?php echo $pos_label ?></option>
	    <?php }?>
	</select>
</div>

<div class="field-group clearfix frm-half frm-first-row">
	<label><?php _e('Check Box', 'formidable') ?></label>
	<select name="<?php echo $frm_style->get_field_name('check_align') ?>" id="frm_check_align">
	    <?php foreach (array('block' => 'Multiple Rows', 'inline' => 'Single Row') as $pos => $pos_label){ ?>
	        <option value="<?php echo $pos ?>" <?php selected($style->post_content['check_align'], $pos) ?>><?php echo $pos_label ?></option>
	    <?php }?>
	</select>
</div>

<div class="field-group field-group-background clearfix">
	<label><?php _e('Color', 'formidable') ?></label>
	<input type="text" name="<?php echo $frm_style->get_field_name('check_label_color') ?>" id="frm_check_label_color" class="hex" value="<?php echo esc_attr($style->post_content['check_label_color']) ?>" />
</div>
<div class="field-group clearfix">
	<label><?php _e('Weight', 'formidable') ?></label>
	<select name="<?php echo $frm_style->get_field_name('check_weight') ?>" id="frm_check_weight">
		<option value="normal" <?php selected($style->post_content['check_weight'], 'normal') ?>><?php _e('normal', 'formidable') ?></option>
		<option value="bold" <?php selected($style->post_content['check_weight'], 'bold') ?>><?php _e('bold', 'formidable') ?></option>
	</select>
</div>
<div class="field-group clearfix">
	<label><?php _e('Size', 'formidable') ?></label>
	<input type="text" name="<?php echo $frm_style->get_field_name('check_font_size') ?>" id="frm_check_font_size" value="<?php echo esc_attr($style->post_content['check_font_size']) ?>"  size="3" />
</div>