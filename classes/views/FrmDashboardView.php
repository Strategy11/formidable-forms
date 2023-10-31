<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmDashboardView {

	private $view = array(
		'counters' => array(
			'template-type' => '',
			'counters'      => array( array() ),
		),
		'payments' => array(
			'template-type' => 'full-width',
			'counters'      => array(
				array(
					'heading'       => 'Total earnings',
					'counter_label' => '$',
					'type'          => 'currency',
					'counter'       => '20023',
				),
			),
		),
	);

	public function __construct( $data ) {
		if ( isset( $data['counters'] ) ) {
			$this->view['counters'] = $data['counters'];
		}
	}

	public function get_welcome_banner( $echo = true ) {
		if ( true === FrmDashboardController::welcome_banner_has_closed() ) {
			return;
		}
		if ( false === $echo ) {
			return $this->load_welcome_template();
		}
		echo wp_kses_post( $this->load_welcome_template() );
	}

	public function get_counters( $echo = true ) {
		if ( false === $echo ) {
			return $this->load_counters_template( $this->view['counters'] );
		}
		echo wp_kses_post( $this->load_counters_template( $this->view['counters'] ) );
	}

	public function get_chart( $echo = true ) {
		if ( false === $echo ) {
			return $this->load_chart_template();
		}
		echo wp_kses_post( $this->load_chart_template() );
	}

	public function get_license_management( $echo = true ) {
		if ( false === $echo ) {
			return $this->load_license_management_template();
		}
		echo wp_kses_post( $this->load_license_management_template() );
	}

	public function get_inbox( $echo = true ) {
		if ( false === $echo ) {
			return $this->load_inbox_template();
		}
		echo wp_kses_post( $this->load_inbox_template() );
	}

	public function get_entries( $echo = true ) {
		if ( false === $echo ) {
			return $this->load_entries_template();
		}
		echo wp_kses_post( $this->load_entries_template() );
	}

	public function get_payments( $echo = true ) {
		if ( false === $echo ) {
			return $this->load_counters_template( $this->view['payments'] );
		}
		echo wp_kses_post( $this->load_counters_template( $this->view['payments'] ) );
	}

	public function get_youtube_video( $echo = true ) {
		if ( false === $echo ) {
			return $this->load_youtube_video_template();
		}
		echo $this->load_youtube_video_template();
	}

	private function load_welcome_template() {
		ob_start();
		include FrmAppHelper::plugin_path() . '/classes/views/dashboard/templates/notification-banner.php';
		return ob_get_clean();
	}

	private function load_counters_template( $template ) {
		ob_start();
		include FrmAppHelper::plugin_path() . '/classes/views/dashboard/templates/counter-item.php';
		return ob_get_clean();
	}

	private function load_chart_template() {
		ob_start();
		include FrmAppHelper::plugin_path() . '/classes/views/dashboard/templates/chart-placeholder.php';
		return ob_get_clean();
	}

	private function load_license_management_template() {
		ob_start();
		include FrmAppHelper::plugin_path() . '/classes/views/dashboard/templates/license-management.php';
		return ob_get_clean();
	}

	private function load_inbox_template() {
		ob_start();
		include FrmAppHelper::plugin_path() . '/classes/views/dashboard/templates/inbox.php';
		return ob_get_clean();
	}

	private function load_entries_template() {
		ob_start();
		include FrmAppHelper::plugin_path() . '/classes/views/dashboard/templates/pro-features-list.php';
		return ob_get_clean();
	}

	private function load_youtube_video_template() {
		ob_start();
		include FrmAppHelper::plugin_path() . '/classes/views/dashboard/templates/youtube-video.php';
		return ob_get_clean();
	}

}
