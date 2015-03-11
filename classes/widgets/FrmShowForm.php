<?php

class FrmShowForm extends WP_Widget {

	public function __construct() {
		$widget_ops = array( 'description' => __( 'Display a Formidable Form', 'formidable' ) );
		$this->WP_Widget('frm_show_form', __( 'Formidable Form', 'formidable' ), $widget_ops);
	}

	public function widget( $args, $instance ) {
        if ( empty($instance['title']) ) {
            $title = FrmForm::getName( $instance['form'] );
        } else {
            $title = $instance['title'];
        }
        $title = apply_filters('widget_title', $title);

        $instance['description'] = isset($instance['description']) ? $instance['description'] : false;

		echo $args['before_widget'];
		$select_class = (isset($instance['select_width']) && $instance['select_width']) ? ' frm_set_select' : '';
		echo '<div class="frm_form_widget'. $select_class .'">';
		if ( $title ) {
			echo $args['before_title'] . stripslashes($title) . $args['after_title'];
		}

        global $frm_vars;
		if ( isset($instance['size']) && is_numeric($instance['size']) ) {
            $frm_vars['sidebar_width'] = $instance['size'];
        }

		echo FrmFormsController::show_form($instance['form'], '', false, $instance['description']);
        $frm_vars['sidebar_width'] = '';
		echo '</div>';
		echo $args['after_widget'];
	}

	public function update( $new_instance, $old_instance ) {
		return $new_instance;
	}

	public function form( $instance ) {
	    //Defaults
		$instance = wp_parse_args( (array) $instance, array(
		    'title' => false, 'form' => false, 'description' => false,
		    'size' => '140px', 'select_width' => false,
		) );
?>
	<p><label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>"><?php _e( 'Title', 'formidable' ) ?>:</label><br/>
	<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" value="<?php echo esc_attr( stripslashes($instance['title']) ); ?>" /></p>

	<p><label for="<?php echo esc_attr( $this->get_field_id('form') ); ?>"><?php _e( 'Form', 'formidable' ) ?>:</label><br/>
<?php
	    FrmFormsHelper::forms_dropdown( $this->get_field_name('form'), $instance['form'], array(
	        'blank' => false, 'field_id' => $this->get_field_id('form'),
            'class' => 'widefat',
	    ) );
?>
	</p>

	<p><label for="<?php echo esc_attr( $this->get_field_id('description') ); ?>"><input class="checkbox" type="checkbox" <?php checked($instance['description'], true) ?> id="<?php echo esc_attr( $this->get_field_id('description') ); ?>" name="<?php echo esc_attr( $this->get_field_name('description') ); ?>" value="1" />
	<?php _e( 'Show Description', 'formidable' ) ?></label></p>

	<p><label for="<?php echo esc_attr( $this->get_field_id('select_width') ); ?>"><input class="checkbox" type="checkbox" <?php checked($instance['select_width'], true) ?> id="<?php echo esc_attr( $this->get_field_id('select_width') ); ?>" name="<?php echo esc_attr( $this->get_field_name('select_width') ); ?>" value="1" />
	<?php _e( 'Fit Select Boxes into SideBar', 'formidable' ) ?></label></p>

	<p><label class="checkbox" for="<?php echo esc_attr( $this->get_field_id('size') ); ?>"><?php _e( 'Field Size', 'formidable' ) ?>:</label><br/>
	    <input type="text" id="<?php echo esc_attr( $this->get_field_id('size') ); ?>" name="<?php echo esc_attr( $this->get_field_name('size') ); ?>" value="<?php echo esc_attr( $instance['size'] ); ?>" /><br/>
        <span class="howto"><?php _e( 'If your text fields are too big for your sidebar insert a size here.', 'formidable' ) ?></span>
	</p>
<?php
	}
}
