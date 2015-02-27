<?php

class FrmShowForm extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'description' => __( "Display a Formidable Form", 'formidable') );
		$this->WP_Widget('frm_show_form', __('Formidable Form', 'formidable'), $widget_ops);
	}

	function widget( $args, $instance ) {
        extract($args);
        
        $frm_form = new FrmForm();
        $form_name = $frm_form->getName( $instance['form'] );
		$title = apply_filters('widget_title', empty($instance['title']) ? $form_name : $instance['title']);
        $instance['description'] = isset($instance['description']) ? $instance['description'] : false;
        
		echo $before_widget;
		$select_class = (isset($instance['select_width']) and $instance['select_width']) ? ' frm_set_select' : '';
		echo '<div class="frm_form_widget'.$select_class.'">';
		if ( $title )
			echo $before_title . stripslashes($title) . $after_title;
			
		if(isset($instance['size']) and is_numeric($instance['size'])){
            global $frm_vars;
            $frm_vars['sidebar_width'] = $instance['size'];
        }
        
		echo FrmFormsController::show_form($instance['form'], '', false, $instance['description']);
        $frm_vars['sidebar_width'] = '';
		echo '</div>';
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		return $new_instance;
	}

	function form( $instance ) { 
	    //Defaults
		$instance = wp_parse_args( (array) $instance, array('title' => false, 'form' => false, 'description' => false, 'size' => 20, 'select_width' => false) );
?>
	<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'formidable') ?>:</label>
	<input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr( stripslashes($instance['title']) ); ?>" /></p>
	
	<p><label for="<?php echo $this->get_field_id('form'); ?>"><?php _e('Form', 'formidable') ?>:</label>
	    <?php FrmFormsHelper::forms_dropdown( $this->get_field_name('form'), $instance['form'], false, $this->get_field_id('form') )?>
	</p>
	
	<p><label for="<?php echo $this->get_field_id('description'); ?>"><input class="checkbox" type="checkbox" <?php checked($instance['description'], true) ?> id="<?php echo $this->get_field_id('description'); ?>" name="<?php echo $this->get_field_name('description'); ?>" value="1" />
	<?php _e('Show Description', 'formidable') ?></label></p>
	
	<p><label for="<?php echo $this->get_field_id('select_width'); ?>"><input class="checkbox" type="checkbox" <?php checked($instance['select_width'], true) ?> id="<?php echo $this->get_field_id('select_width'); ?>" name="<?php echo $this->get_field_name('select_width'); ?>" value="1" />
	<?php _e('Fit Select Boxes into SideBar', 'formidable') ?></label></p>
	
	<p><label class="checkbox" for="<?php echo $this->get_field_id('size'); ?>"><?php _e('Field Size', 'formidable') ?>:</label>
	
	<input type="text" size="3" id="<?php echo $this->get_field_id('size'); ?>" name="<?php echo $this->get_field_name('size'); ?>" value="<?php echo esc_attr( $instance['size'] ); ?>" /> <span class="howto" style="display:inline;"><?php _e('characters wide', 'formidable') ?></span></p>
	<p class="description"><?php _e('If your text fields are too big for your sidebar insert a size here.', 'formidable') ?></p>
<?php 
	}
}
