<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmShowForm extends WP_Widget {

	public function __construct() {
		$widget_ops = array( 'description' => __( 'Display a Formidable Form', 'formidable' ) );
		parent::__construct( 'frm_show_form', __( 'Formidable Form', 'formidable' ), $widget_ops );
	}

	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		echo FrmAppHelper::kses( $args['before_widget'], 'all' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		echo '<div class="frm_form_widget">';
		if ( $title ) {
			echo FrmAppHelper::kses( $args['before_title'] . stripslashes( $title ) . $args['after_title'], 'all' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		$form_atts = array(
			'id'          => isset( $instance['form'] ) ? $instance['form'] : 0,
			'title'       => false,
			'description' => isset( $instance['description'] ) ? $instance['description'] : false,
		);

		echo FrmFormsController::get_form_shortcode( $form_atts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		echo '</div>';
		echo FrmAppHelper::kses( $args['after_widget'], 'all' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Settings to save or bool false to cancel saving.
	 */
	public function update( $new_instance, $old_instance ) {
		return $new_instance;
	}

	/**
	 * Outputs the settings update form.
	 *
	 * @param array $instance Current settings.
	 * @return string Default return is 'noform'.
	 */
	public function form( $instance ) {
		$defaults = array(
			'title'       => false,
			'form'        => false,
			'description' => false,
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_html_e( 'Title', 'formidable' ); ?>:
			</label>
			<br/>
			<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
				value="<?php echo esc_attr( stripslashes( $instance['title'] ) ); ?>"/>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'form' ) ); ?>">
				<?php esc_html_e( 'Form', 'formidable' ); ?>:
			</label><br/>
			<?php
			FrmFormsHelper::forms_dropdown(
				$this->get_field_name( 'form' ),
				$instance['form'],
				array(
					'blank'    => false,
					'field_id' => $this->get_field_id( 'form' ),
					'class'    => 'widefat',
				)
			);
			?>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>">
				<input class="checkbox" type="checkbox" <?php checked( $instance['description'], true ); ?>
					id="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>"
					name="<?php echo esc_attr( $this->get_field_name( 'description' ) ); ?>" value="1" />
				<?php esc_html_e( 'Show Description', 'formidable' ); ?>
			</label>
		</p>
		<?php
		return 'noform';
	}
}
