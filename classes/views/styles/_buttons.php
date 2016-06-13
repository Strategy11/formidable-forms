<p class="frm_no_top_margin">
	<label for="frm_submit_style">
		<input type="checkbox" name="<?php echo esc_attr( $frm_style->get_field_name('submit_style') ) ?>" id="frm_submit_style" <?php checked( $style->post_content['submit_style'], 1 ) ?> value="1" />
		<?php esc_html_e( 'Disable submit button styling', 'formidable' ); ?>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Note: If disabled, you may not see the change take effect until you make 2 more styling changes or click "Update Options".', 'formidable' ) ?>"></span>
	</label>
</p>

<div class="posttypediv">
	<ul class="posttype-tabs add-menu-item-tabs">
		<li <?php echo ( 'default' == $current_tab ? ' class="tabs"' : '' ); ?>>
    		<a href="<?php echo esc_url('?page=formidable-styles&page-tab=default#tabs-panel-button-default') ?>" class="nav-tab-link" data-type="tabs-panel-button-default" ><?php _e( 'Default', 'formidable' ) ?></a>
    	</li>
		<li <?php echo ( 'button-hover' == $current_tab ? ' class="tabs"' : '' ); ?>>
			<a href="<?php echo esc_url('?page=formidable-styles&page-tab=button-hover#page-button-hover') ?>" class="nav-tab-link" data-type="tabs-panel-button-hover" ><?php _e( 'Hover', 'formidable' ) ?></a>
		</li>
		<li <?php echo ( 'button-click' == $current_tab ? ' class="tabs"' : '' ); ?>>
			<a href="?page=formidable-styles&page-tab=button-click#tabs-panel-button-click" class="nav-tab-link" data-type="tabs-panel-button-click"><?php _e( 'Click', 'formidable' ) ?></a>
		</li>
	</ul><!-- .posttype-tabs -->

	<div id="tabs-panel-button-default" class="tabs-panel <?php
		echo ( 'default' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' );
	?>">
	    <div class="field-group field-group-border clearfix">
        	<label><?php _e( 'Size', 'formidable' ) ?></label>
        	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('submit_font_size') ) ?>" id="frm_submit_font_size" value="<?php echo esc_attr( $style->post_content['submit_font_size'] ) ?>"  size="3" />
        </div>

        <div class="field-group clearfix">
        	<label><?php _e( 'Width', 'formidable' ) ?></label>
        	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('submit_width') ) ?>" id="frm_submit_width" value="<?php echo esc_attr( $style->post_content['submit_width'] ) ?>"  size="5" />
        </div>

        <div class="field-group clearfix">
        	<label><?php _e( 'Height', 'formidable' ) ?></label>
        	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('submit_height') ) ?>" id="frm_submit_height" value="<?php echo esc_attr( $style->post_content['submit_height'] ) ?>"  size="5" />
        </div>

        <div class="field-group clearfix">
        	<label><?php _e( 'Weight', 'formidable' ) ?></label>
        	<select name="<?php echo esc_attr( $frm_style->get_field_name('submit_weight') ) ?>" id="frm_submit_weight">
				<?php foreach ( FrmStyle::get_bold_options() as $value => $name ) { ?>
				<option value="<?php echo esc_attr( $value ) ?>" <?php selected( $style->post_content['submit_weight'], $value ) ?>><?php echo esc_html( $name ) ?></option>
				<?php } ?>
        	</select>
        </div>

        <div class="field-group clearfix">
        	<label><?php _e( 'Corners', 'formidable' ) ?></label>
        	<input type="text" value="<?php echo esc_attr( $style->post_content['submit_border_radius'] ) ?>" name="<?php echo esc_attr( $frm_style->get_field_name('submit_border_radius') ) ?>" id="frm_submit_border_radius" size="4"/>
        </div>

        <div class="field-group field-group-border clearfix">
        	<label><?php _e( 'BG color', 'formidable' ) ?></label>
        	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('submit_bg_color') ) ?>" id="frm_submit_bg_color" class="hex" value="<?php echo esc_attr( $style->post_content['submit_bg_color'] ) ?>" />
        </div>

        <div class="field-group clearfix">
        	<label><?php _e( 'Text', 'formidable' ) ?></label>
        	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('submit_text_color') ) ?>" id="frm_submit_text_color" class="hex" value="<?php echo esc_attr( $style->post_content['submit_text_color'] ) ?>" />
        </div>

        <div class="field-group field-group-border clearfix">
        	<label><?php _e( 'Border', 'formidable' ) ?></label>
        	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('submit_border_color') ) ?>" id="frm_submit_border_color" class="hex" value="<?php echo esc_attr( $style->post_content['submit_border_color'] ) ?>" />
        </div>

        <div class="field-group clearfix">
        	<label><?php _e( 'Thickness', 'formidable' ) ?></label>
        	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('submit_border_width') ) ?>" id="frm_submit_border_width" value="<?php echo esc_attr( $style->post_content['submit_border_width'] ) ?>" size="4" />
        </div>

        <div class="field-group clearfix">
        	<label><?php _e( 'Shadow', 'formidable' ) ?></label>
        	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('submit_shadow_color') ) ?>" id="frm_submit_shadow_color" class="hex" value="<?php echo esc_attr( $style->post_content['submit_shadow_color'] ) ?>" />
        </div>

        <div class="clear"></div>
        <div class="field-group field-group-border frm-full">
        	<label><?php _e( 'BG Image', 'formidable' ) ?></label>
        	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('submit_bg_img') ) ?>" id="frm_submit_bg_img" value="<?php echo esc_attr( $style->post_content['submit_bg_img'] ) ?>"  />
        </div>

        <div class="field-group field-group-border clearfix">
        	<label><?php _e( 'Margin', 'formidable' ) ?></label>
        	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('submit_margin') ) ?>" id="frm_submit_margin" value="<?php echo esc_attr( $style->post_content['submit_margin'] ) ?>" size="6" />
        </div>

        <div class="field-group clearfix">
        	<label><?php _e( 'Padding', 'formidable' ) ?></label>
        	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('submit_padding') ) ?>" id="frm_submit_padding" value="<?php echo esc_attr( $style->post_content['submit_padding'] ) ?>" size="6" />
        </div>
        <div class="clear"></div>
	</div><!-- /.tabs-panel -->

	<div id="tabs-panel-button-hover" class="tabs-panel <?php
		echo ( 'button-hover' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' );
	?>">
	    <div class="field-group clearfix">
        	<label><?php _e( 'BG color', 'formidable' ) ?></label>
        	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('submit_hover_bg_color') ) ?>" id="frm_submit_hover_bg_color" class="hex" value="<?php echo esc_attr( $style->post_content['submit_hover_bg_color'] ) ?>" />
        </div>

        <div class="field-group clearfix">
    	    <label><?php _e( 'Text', 'formidable' ) ?></label>
    	    <input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('submit_hover_color') ) ?>" id="frm_submit_hover_color" class="hex" value="<?php echo esc_attr( $style->post_content['submit_hover_color'] ) ?>" />
        </div>

        <div class="field-group clearfix">
            <label><?php _e( 'Border', 'formidable' ) ?></label>
        	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('submit_hover_border_color') ) ?>" id="frm_submit_hover_border_color" class="hex" value="<?php echo esc_attr( $style->post_content['submit_hover_border_color'] ) ?>" />
        </div>

	    <div class="clear"></div>
	</div><!-- /.tabs-panel -->

	<div id="tabs-panel-button-click" class="tabs-panel <?php
		echo ( 'button-click' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' );
	?>">
	    <div class="field-group clearfix">
        	<label><?php _e( 'BG color', 'formidable' ) ?></label>
        	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('submit_active_bg_color') ) ?>" id="frm_submit_active_bg_color" class="hex" value="<?php echo esc_attr( $style->post_content['submit_active_bg_color'] ) ?>" />
        </div>

        <div class="field-group clearfix">
    	    <label><?php _e( 'Text', 'formidable' ) ?></label>
    	    <input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('submit_active_color') ) ?>" id="frm_submit_active_color" class="hex" value="<?php echo esc_attr( $style->post_content['submit_active_color'] ) ?>" />
        </div>

        <div class="field-group clearfix">
            <label><?php _e( 'Border', 'formidable' ) ?></label>
        	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('submit_active_border_color') ) ?>" id="frm_submit_active_border_color" class="hex" value="<?php echo esc_attr( $style->post_content['submit_active_border_color'] ) ?>" />
        </div>

	    <div class="clear"></div>
	</div><!-- /.tabs-panel -->

</div>
