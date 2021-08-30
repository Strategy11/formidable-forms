<?php

if ( class_exists( '\Elementor\Widget_Base' ) ) {
	class FrmElementorWidget extends \Elementor\Widget_Base {

		public function __construct( $data = array(), $args = null ) {
			parent::__construct( $data, $args );
		}

		public function get_name() {
			return 'formidable';
		}

		public function get_title() {
			return __( 'Formidable Forms', 'formidable' );
		}

		public function get_icon() {
			return 'frmfont frm_logo_icon';
		}

		public function get_categories() {
			return array( 'general' );
		}

		protected function _register_controls() {
			$this->start_controls_section(
				'section_form_dropdown',
				array(
					'label' => __( 'Select Form', 'formidable' ),
					'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
				)
			);

			$this->add_control(
				'form_dropdown',
				array(
					'label'   => __( 'Form', 'formidable' ),
					'type'    => \Elementor\Controls_Manager::SELECT,
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

			$this->add_control(
				'title',
				array(
					'label' => __( 'Show Form Title', 'formidable' ),
					'type'  => \Elementor\Controls_Manager::SWITCHER,
				)
			);

			$this->add_control(
				'description',
				array(
					'label' => __( 'Show Form Description', 'formidable' ),
					'type'  => \Elementor\Controls_Manager::SWITCHER,
				)
			);

			$this->add_control(
				'minimize',
				array(
					'label' => __( 'Minimize HTML', 'formidable' ),
					'type'  => \Elementor\Controls_Manager::SWITCHER,
				)
			);

			$this->end_controls_section();
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
			$form_id     = isset( $settings['form_dropdown'] ) ? absint( $settings['form_dropdown'] ) : 0;
			$title       = isset( $settings['title'] ) && 'yes' === $settings['title'] ? 1 : 0;
			$description = isset( $settings['description'] ) && 'yes' === $settings['description'] ? 1 : 0;
			$minimize    = isset( $settings['minimize'] ) && 'yes' === $settings['minimize'] ? 1 : 0;

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