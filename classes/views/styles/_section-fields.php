<div class="posttypediv">
	<ul class="posttype-tabs add-menu-item-tabs">
		<li <?php echo ( 'default' == $current_tab ? ' class="tabs"' : '' ); ?>>
    		<a href="<?php echo esc_url('?page=formidable-styles&page-tab=default#tabs-panel-headings') ?>" class="nav-tab-link" data-type="tabs-panel-headings" ><?php _e( 'Headings', 'formidable' ) ?></a>
    	</li>
		<li <?php echo ( 'collapse' == $current_tab ? ' class="tabs"' : '' ); ?>>
			<a href="<?php echo esc_url('?page=formidable-styles&page-tab=collapse#page-collapse') ?>" class="nav-tab-link" data-type="tabs-panel-collapse" ><?php _e( 'Collapsible', 'formidable' ) ?></a>
		</li>
		<li <?php echo ( 'repeat' == $current_tab ? ' class="tabs"' : '' ); ?>>
			<a href="?page=formidable-styles&page-tab=repeat#tabs-panel-repeat" class="nav-tab-link" data-type="tabs-panel-repeat"><?php _e( 'Repeatable', 'formidable' ) ?></a>
		</li>
	</ul><!-- .posttype-tabs -->

	<div id="tabs-panel-headings" class="tabs-panel <?php
		echo ( 'default' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' );
	?>">
	    <div class="field-group field-group-background">
        	<label><?php _e( 'Color', 'formidable' ) ?></label>
        	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('section_color') ) ?>" id="frm_section_color" class="hex" value="<?php echo esc_attr( $style->post_content['section_color'] ) ?>" />
        </div>

        <div class="field-group">
        	<label><?php _e( 'Weight', 'formidable' ) ?></label>
        	<select name="<?php echo esc_attr( $frm_style->get_field_name('section_weight') ) ?>" id="frm_section_weight">
				<?php foreach ( FrmStyle::get_bold_options() as $value => $name ) { ?>
				<option value="<?php echo esc_attr( $value ) ?>" <?php selected( $style->post_content['section_weight'], $value ) ?>><?php echo $name ?></option>
				<?php } ?>
        	</select>
        </div>

        <div class="field-group">
        	<label><?php _e( 'Size', 'formidable' ) ?></label>
        	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('section_font_size') ) ?>" id="frm_section_font_size" value="<?php echo esc_attr( $style->post_content['section_font_size'] ) ?>" />
        </div>

        <div class="field-group field-group-border frm-half">
        	<label><?php _e( 'Padding', 'formidable' ) ?></label>
        	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('section_pad') ) ?>" id="frm_section_pad" value="<?php echo esc_attr( $style->post_content['section_pad'] ) ?>" />
        </div>

        <div class="field-group frm-half">
    	    <label class="background"><?php _e( 'BG color', 'formidable' ) ?></label>
    	    <input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('section_bg_color') ) ?>" id="frm_section_bg_color" class="hex" value="<?php echo esc_attr( $style->post_content['section_bg_color'] ) ?>" />
        </div>

        <div class="field-group field-group-border frm-half">
        	<label><?php _e( 'Top Margin', 'formidable' ) ?></label>
        	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('section_mar_top') ) ?>" id="frm_section_mar_top" value="<?php echo esc_attr( $style->post_content['section_mar_top'] ) ?>" />
        </div>
        <div class="field-group frm-half">
        	<label><?php _e( 'Bottom Margin', 'formidable' ) ?></label>
        	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('section_mar_bottom') ) ?>" id="frm_section_mar_bottom" value="<?php echo esc_attr( $style->post_content['section_mar_bottom'] ) ?>" />
        </div>

        <div class="field-group field-group-border">
            <label><?php _e( 'Border', 'formidable' ) ?></label>
        	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('section_border_color') ) ?>" id="frm_section_border_color" class="hex" value="<?php echo esc_attr( $style->post_content['section_border_color'] ) ?>" />
        </div>
        <div class="field-group">
        	<label><?php _e( 'Thickness', 'formidable' ) ?></label>
        	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('section_border_width') ) ?>" id="frm_section_border_width" value="<?php echo esc_attr( $style->post_content['section_border_width'] ) ?>" />
        </div>
        <div class="field-group">
        	<label><?php _e( 'Style', 'formidable' ) ?></label>
        	<select name="<?php echo esc_attr( $frm_style->get_field_name('section_border_style') ) ?>" id="frm_section_border_style">
        	    <option value="solid" <?php selected($style->post_content['section_border_style'], 'solid') ?>><?php _e( 'solid', 'formidable' ) ?></option>
        		<option value="dotted" <?php selected($style->post_content['section_border_style'], 'dotted') ?>><?php _e( 'dotted', 'formidable' ) ?></option>
        		<option value="dashed" <?php selected($style->post_content['section_border_style'], 'dashed') ?>><?php _e( 'dashed', 'formidable' ) ?></option>
        		<option value="double" <?php selected($style->post_content['section_border_style'], 'double') ?>><?php _e( 'double', 'formidable' ) ?></option>
        	</select>
        </div>

        <div class="field-group field-group-border frm-half">
        	<label><?php _e( 'Border Position', 'formidable' ) ?></label>
        	<select name="<?php echo esc_attr( $frm_style->get_field_name('section_border_loc') ) ?>" id="frm_section_border_loc">
        	    <option value="-top" <?php selected($style->post_content['section_border_loc'], '-top') ?>><?php _e( 'top', 'formidable' ) ?></option>
        		<option value="-bottom" <?php selected($style->post_content['section_border_loc'], '-bottom') ?>><?php _e( 'bottom', 'formidable' ) ?></option>
        		<option value="-left" <?php selected($style->post_content['section_border_loc'], '-left') ?>><?php _e( 'left', 'formidable' ) ?></option>
        		<option value="-right" <?php selected($style->post_content['section_border_loc'], '-right') ?>><?php _e( 'right', 'formidable' ) ?></option>
        		<option value="" <?php selected($style->post_content['section_border_loc'], '') ?>><?php _e( 'all', 'formidable' ) ?></option>
        	</select>
        </div>
        <div class="clear"></div>
	</div><!-- /.tabs-panel -->

	<div id="tabs-panel-collapse" class="tabs-panel <?php
		echo ( 'collapse' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' );
	?>">
	    <div class="field-group frm-half">
        	<label><?php _e( 'Icons', 'formidable' ) ?></label>
            <?php FrmStylesHelper::bs_icon_select($style, $frm_style, 'arrow'); ?>
        </div>

        <div class="field-group frm-half">
        	<label><?php _e( 'Icon Position', 'formidable' ) ?></label>
        	<select name="<?php echo esc_attr( $frm_style->get_field_name('collapse_pos') ) ?>" id="frm_collapse_pos">
        	    <option value="after" <?php selected($style->post_content['collapse_pos'], 'after') ?>><?php _e( 'After Heading', 'formidable' ) ?></option>
        	    <option value="before" <?php selected($style->post_content['collapse_pos'], 'before') ?>><?php _e( 'Before Heading', 'formidable' ) ?></option>
        	</select>
        </div>
        <div class="clear"></div>
	</div><!-- /.tabs-panel -->

	<div id="tabs-panel-repeat" class="tabs-panel <?php
		echo ( 'repeat' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' );
	?>">
	    <div class="field-group frm-half">
        	<label><?php _e( 'Icons', 'formidable' ) ?></label>
            <?php FrmStylesHelper::bs_icon_select($style, $frm_style, 'minus'); ?>
        </div>
        <div class="clear"></div>
	</div><!-- /.tabs-panel -->

</div>
