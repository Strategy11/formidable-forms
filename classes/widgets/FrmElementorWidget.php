<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( class_exists( '\Elementor\Widget_Base' ) ) {
	class FrmElementorWidget extends \Elementor\Widget_Base {

		public function get_name() {
			return 'formidable';
		}

		public function get_title() {
			return FrmAppHelper::get_menu_name() . ' ' . __( 'Forms', 'formidable' );
		}

		public function get_icon() {
			return FrmAppHelper::get_menu_icon_class();
		}

		public function get_categories() {
			return array( 'general' );
		}

		protected function register_controls() {
			$this->start_controls_section(
				'section_form_dropdown',
				array(
					'label' => __( 'Select Form', 'formidable' ),
					'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
				)
			);

			$this->add_control(
				'form_id',
				array(
					'label'   => __( 'Form', 'formidable' ),
					'type'    => \Elementor\Controls_Manager::SELECT2,
					'options' => $this->get_form_options(),
				)
			);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_options',
				array(
					'label' => __( 'Options', 'formidable' ),
					'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
				)
			);

			$this->add_basic_switcher_control( 'title', __( 'Show Form Title', 'formidable' ) );
			$this->add_basic_switcher_control( 'description', __( 'Show Form Description', 'formidable' ) );
			$this->add_basic_switcher_control( 'minimize', __( 'Minimize HTML', 'formidable' ) );

			$this->end_controls_section();
		}

		private function add_basic_switcher_control( $key, $title ) {
			$this->add_control(
				$key,
				array(
					'label' => $title,
					'type'  => \Elementor\Controls_Manager::SWITCHER,
				)
			);
		}

		private function get_form_options() {
			$query   = array();
			$where   = apply_filters( 'frm_forms_dropdown', $query, 'form' );
			$forms   = FrmForm::get_published_forms( $where, 999, 'exclude' );
			$options = array( '' => '' );

			foreach ( $forms as $form ) {
				$form_title           = '' === $form->name ? __( '(no title)', 'formidable' ) : FrmAppHelper::truncate( $form->name, 50 );
				$options[ $form->id ] = esc_html( $form_title );
			}

			return $options;
		}

		protected function render() {
			$settings    = $this->get_settings_for_display();
			$form_id     = isset( $settings['form_id'] ) ? absint( $settings['form_id'] ) : 0;
			$title       = isset( $settings['title'] ) && 'yes' === $settings['title'];
			$description = isset( $settings['description'] ) && 'yes' === $settings['description'];
			$minimize    = isset( $settings['minimize'] ) && 'yes' === $settings['minimize'];

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo FrmFormsController::get_form_shortcode(
				array(
					'id'          => $form_id,
					'title'       => $title,
					'description' => $description,
					'minimize'    => $minimize,
				)
			);
		}
	}
}
