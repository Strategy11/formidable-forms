<div class="posttypediv">
		<ul class="posttype-tabs add-menu-item-tabs">
			<li <?php echo ( 'default' == $current_tab ? ' class="tabs"' : '' ); ?>>
				<a href="?page=formidable-styles&page-tab=default#tabs-panel-default-color" class="nav-tab-link" data-type="tabs-panel-default-color"><?php _e( 'Default', 'formidable' ) ?></a>
			</li>
			<li <?php echo ( 'active-color' == $current_tab ? ' class="tabs"' : '' ); ?>>
				<a href="<?php echo esc_url('?page=formidable-styles&page-tab=active-color#page-active-color') ?>" class="nav-tab-link" data-type="tabs-panel-active-color"><?php _e( 'Active', 'formidable' ) ?></a>
			</li>
			<li <?php echo ( 'active-error' == $current_tab ? ' class="tabs"' : '' ); ?>>
				<a href="?page=formidable-styles&page-tab=active-error#tabs-panel-active-error" class="nav-tab-link" data-type="tabs-panel-active-error"><?php _e( 'Error', 'formidable' ) ?></a>
			</li>
			<li <?php echo ( 'read-only' == $current_tab ? ' class="tabs"' : '' ); ?>>
				<a href="?page=formidable-styles&page-tab=read-only#tabs-panel-read-only" class="nav-tab-link" data-type="tabs-panel-read-only"><?php _e( 'Read Only', 'formidable' ) ?></a>
			</li>
		</ul><!-- .posttype-tabs -->

		<div id="tabs-panel-default-color" class="tabs-panel <?php
			echo ( 'default' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' );
		?>">
			<div class="field-group field-group-border clearfix">
            	<label class="background"><?php _e( 'BG color', 'formidable' ) ?></label>
            	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('bg_color') ) ?>" id="frm_bg_color" class="hex" value="<?php echo esc_attr( $style->post_content['bg_color'] ) ?>" />
            </div>
            <div class="field-group clearfix">
            	<label><?php _e( 'Text', 'formidable' ) ?></label>
            	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('text_color') ) ?>" id="frm_text_color" class="hex" value="<?php echo esc_attr( $style->post_content['text_color'] ) ?>" />
            </div>

            <div class="field-group field-group-border clearfix">
            	<label><?php _e( 'Border', 'formidable' ) ?></label>
            	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('border_color') ) ?>" id="frm_border_color" class="hex" value="<?php echo esc_attr( $style->post_content['border_color'] ) ?>" />
            </div>
            <div class="field-group clearfix">
            	<label><?php _e( 'Thickness', 'formidable' ) ?></label>
            	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('field_border_width') ) ?>" id="frm_field_border_width" value="<?php echo esc_attr( $style->post_content['field_border_width'] ) ?>" size="4" />
            </div>
            <div class="field-group clearfix">
				<label><?php _e( 'Style', 'formidable' ) ?></label>
            	<select name="<?php echo esc_attr( $frm_style->get_field_name('field_border_style') ) ?>" id="frm_field_border_style">
            	    <option value="solid" <?php selected($style->post_content['field_border_style'], 'solid') ?>><?php _e( 'solid', 'formidable' ) ?></option>
            		<option value="dotted" <?php selected($style->post_content['field_border_style'], 'dotted') ?>><?php _e( 'dotted', 'formidable' ) ?></option>
            		<option value="dashed" <?php selected($style->post_content['field_border_style'], 'dashed') ?>><?php _e( 'dashed', 'formidable' ) ?></option>
            		<option value="double" <?php selected($style->post_content['field_border_style'], 'double') ?>><?php _e( 'double', 'formidable' ) ?></option>
            	</select>
            </div>
            <div class="clear"></div>
			<p class="frm_no_bottom_margin">
				<label>
					<input type="checkbox" name="<?php echo esc_attr( $frm_style->get_field_name('remove_box_shadow') ) ?>" id="frm_remove_box_shadow" value="1" <?php checked($style->post_content['remove_box_shadow'], 1) ?> />
					<?php _e( 'Remove box shadow', 'formidable' ) ?>
				</label>
			</p>
		</div><!-- /.tabs-panel -->

		<div id="tabs-panel-active-color" class="tabs-panel <?php
			echo ( 'active-color' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' );
		?>">
            <div class="field-group field-group-border clearfix">
            	<label class="background"><?php _e( 'BG color', 'formidable' ) ?></label>
            	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('bg_color_active') ) ?>" id="frm_bg_color_active" class="hex" value="<?php echo esc_attr( $style->post_content['bg_color_active'] ) ?>" />
            </div>
            <div class="field-group clearfix">
            	<label><?php _e( 'Border', 'formidable' ) ?></label>
            	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('border_color_active') ) ?>" id="frm_border_color_active" class="hex" value="<?php echo esc_attr( $style->post_content['border_color_active'] ) ?>" />
            </div>
			<div class="clear"></div>
			<p class="frm_no_bottom_margin">
				<label>
					<input type="checkbox" name="<?php echo esc_attr( $frm_style->get_field_name('remove_box_shadow_active') ) ?>" id="frm_remove_box_shadow_active" value="1" <?php checked($style->post_content['remove_box_shadow_active'], 1) ?> />
					<?php _e( 'Remove box shadow', 'formidable' ) ?>
				</label>
			</p>
		</div><!-- /.tabs-panel -->

		<div id="tabs-panel-active-error" class="tabs-panel <?php
			echo ( 'active-error' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' );
		?>">
		    <div class="field-group field-group-border clearfix">
        	    <label class="background"><?php _e( 'BG color', 'formidable' ) ?></label>
        	    <input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('bg_color_error') ) ?>" id="frm_bg_color_error" class="hex" value="<?php echo esc_attr( $style->post_content['bg_color_error'] ) ?>" />
            </div>
            <div class="field-group clearfix">
        	    <label><?php _e( 'Text', 'formidable' ) ?></label>
        	    <input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('text_color_error') ) ?>" id="frm_text_color_error" class="hex" value="<?php echo esc_attr( $style->post_content['text_color_error'] ) ?>" />
            </div>

            <div class="field-group field-group-border clearfix">
                <label><?php _e( 'Border', 'formidable' ) ?></label>
            	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('border_color_error') ) ?>" id="frm_border_color_error" class="hex" value="<?php echo esc_attr( $style->post_content['border_color_error'] ) ?>" />
            </div>
            <div class="field-group clearfix">
            	<label><?php _e( 'Thickness', 'formidable' ) ?></label>
            	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('border_width_error') ) ?>" id="frm_border_width_error" value="<?php echo esc_attr( $style->post_content['border_width_error'] ) ?>" size="4" />
            </div>
            <div class="field-group clearfix">
            	<label><?php _e( 'Style', 'formidable' ) ?></label>
            	<select name="<?php echo esc_attr( $frm_style->get_field_name('border_style_error') ) ?>" id="frm_border_style_error">
            	    <option value="solid" <?php selected($style->post_content['border_style_error'], 'solid') ?>><?php _e( 'solid', 'formidable' ) ?></option>
            		<option value="dotted" <?php selected($style->post_content['border_style_error'], 'dotted') ?>><?php _e( 'dotted', 'formidable' ) ?></option>
            		<option value="dashed" <?php selected($style->post_content['border_style_error'], 'dashed') ?>><?php _e( 'dashed', 'formidable' ) ?></option>
            		<option value="double" <?php selected($style->post_content['border_style_error'], 'double') ?>><?php _e( 'double', 'formidable' ) ?></option>
            	</select>
            </div>

            <div class="clear"></div>
		</div><!-- /.tabs-panel -->

		<div id="tabs-panel-read-only" class="tabs-panel <?php
			echo ( 'read-only' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' );
		?>">
		    <div class="field-group field-group-border clearfix">
        	    <label class="background"><?php _e( 'BG color', 'formidable' ) ?></label>
        	    <input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('bg_color_disabled') ) ?>" id="frm_bg_color_disabled" class="hex" value="<?php echo esc_attr( $style->post_content['bg_color_disabled'] ) ?>" />
            </div>
            <div class="field-group clearfix">
        	    <label><?php _e( 'Text', 'formidable' ) ?></label>
        	    <input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('text_color_disabled') ) ?>" id="frm_text_color_disabled" class="hex" value="<?php echo esc_attr( $style->post_content['text_color_disabled'] ) ?>" />
            </div>

            <div class="field-group clearfix">
                <label><?php _e( 'Border', 'formidable' ) ?></label>
            	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('border_color_disabled') ) ?>" id="frm_border_color_disabled" class="hex" value="<?php echo esc_attr( $style->post_content['border_color_disabled'] ) ?>" />
            </div>
            <div class="clear"></div>
		</div><!-- /.tabs-panel -->
</div>
