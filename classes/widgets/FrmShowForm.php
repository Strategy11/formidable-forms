<?php

class FrmShowForm extends WP_Widget {

	public function __construct() {
		$widget_ops = array( 'description' => __( 'Display a Formidable Form', 'formidable' ) );
		parent::__construct('frm_show_form', __( 'Formidable Form', 'formidable' ), $widget_ops);
	}

	public function widget( $args, $instance ) {
        if ( empty($instance['title']) ) {
            $title = FrmForm::getName( $instance['form'] );
        } else {
            $title = $instance['title'];
        }
        $title = apply_filters('widget_title', $title);

		echo $args['before_widget'];

		echo '<div class="frm_form_widget">';
		if ( $title ) {
			echo $args['before_title'] . stripslashes($title) . $args['after_title'];
		}

		$form_atts = array(
			'id' => $instance['form'],
			'title' => false,
			'description' => isset( $instance['description'] ) ? $instance['description'] : false,
		);

		echo FrmFormsController::get_form_shortcode( $form_atts );

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
<?php
	}
}
