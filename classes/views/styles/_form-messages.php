<div class="posttypediv">
	<ul class="posttype-tabs add-menu-item-tabs">
		<li <?php echo ( 'default' == $current_tab ? ' class="tabs"' : '' ); ?>>
    		<a href="<?php echo esc_url('?page=formidable-styles&page-tab=default#tabs-panel-success-msg') ?>" class="nav-tab-link" data-type="tabs-panel-success-msg" ><?php _e( 'Success', 'formidable' ) ?></a>
    	</li>
		<li <?php echo ( 'error-msg' == $current_tab ? ' class="tabs"' : '' ); ?>>
			<a href="<?php echo esc_url('?page=formidable-styles&page-tab=error-msge#page-error-msg') ?>" class="nav-tab-link" data-type="tabs-panel-error-msg" ><?php _e( 'Error', 'formidable' ) ?></a>
		</li>
	</ul><!-- .posttype-tabs -->

	<div id="tabs-panel-success-msg" class="tabs-panel <?php
		echo ( 'default' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' );
	?>">
	    <div class="field-group field-group-border clearfix">
        	<label><?php _e( 'BG Color', 'formidable' ) ?></label>
            <div class="hasPicker">
                <input name="<?php echo esc_attr( $frm_style->get_field_name('success_bg_color') ) ?>" id="frm_success_bg_color" class="hex" value="<?php echo esc_attr( $style->post_content['success_bg_color'] ) ?>" type="text" /></div>
        </div>
        <div class="field-group clearfix">
        	<label><?php _e( 'Border', 'formidable' ) ?></label>
        	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('success_border_color') ) ?>" id="frm_success_border_color" class="hex" value="<?php echo esc_attr( $style->post_content['success_border_color'] ) ?>" />
        </div>
        <div class="field-group clearfix">
        	<label><?php _e( 'Text', 'formidable' ) ?></label>
        	<input name="<?php echo esc_attr( $frm_style->get_field_name('success_text_color') ) ?>" id="frm_success_text_color" class="hex" value="<?php echo esc_attr( $style->post_content['success_text_color'] ) ?>" type="text" />
        </div>
        <div class="field-group clearfix">
        	<label><?php _e( 'Size', 'formidable' ) ?></label>
        	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('success_font_size') ) ?>" id="frm_success_font_size" value="<?php echo esc_attr( $style->post_content['success_font_size'] ) ?>"  size="3" />
        </div>
        <div class="clear"></div>
	</div><!-- /.tabs-panel -->

	<div id="tabs-panel-error-msg" class="tabs-panel <?php
		echo ( 'error-msg' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' );
	?>">
	    <div class="field-group field-group-border clearfix">
        	<label><?php _e( 'BG Color', 'formidable' ) ?></label>
            <div class="hasPicker">
                <input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('error_bg') ) ?>" id="frm_error_bg" class="hex" value="<?php echo esc_attr($style->post_content['error_bg']) ?>" /></div>
        </div>
        <div class="field-group clearfix">
        	<label><?php _e( 'Border', 'formidable' ) ?></label>
        	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('error_border') ) ?>" id="frm_error_border" class="hex" value="<?php echo esc_attr( $style->post_content['error_border'] ) ?>" />
        </div>
        <div class="field-group clearfix">

        	<label><?php _e( 'Text', 'formidable' ) ?></label>
        	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('error_text') ) ?>" id="frm_error_text" class="hex" value="<?php echo esc_attr( $style->post_content['error_text'] ) ?>" />
        </div>

        <div class="field-group clearfix">
        	<label><?php _e( 'Size', 'formidable' ) ?></label>
        	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('error_font_size') ) ?>" id="frm_error_font_size" value="<?php echo esc_attr( $style->post_content['error_font_size'] ) ?>"  size="3" />
        </div>
        <div class="clear"></div>
	</div><!-- /.tabs-panel -->

</div>
