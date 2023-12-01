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
		'license'  => array(
			'heading'        => '',
			'copy'           => '',
			'license-status' => array(
				'status'      => '',
				'status-copy' => '',
			),
			'buttons'        => array(
				array(
					'label' => '',
					'link'  => '',
				),
				array(
					'label' => '',
					'link'  => '',
				),
			),
		),
		'entries'  => array(
			'show_placeholder' => true,
			'placeholder'      => array(
				'background' => 'entries-placeholder',
				'heading'    => 'You Have No Entries Yet',
				'copy'       => 'See the <a href="#">form documentation</a> for instructions on publishin your form',
				'button'     => array(
					'label' => 'Add New Form',
					'link'  => '#',
				),
			),
		),
		'inbox'    => array(
			'unread'    => array(),
			'dismissed' => array(),
		),
		'video'    => array(
			'id' => null,
		),
		'payments' => array(
			'template-type' => 'full-width',
			'counters'      => array(
				array(
					'heading' => 'Total earnings',
					'type'    => 'currency',
					'items'   => array(
						array(
							'counter_label' => '$',
							'counter'       => '20023',
						),
					),
				),
			),
		),
	);

	public function __construct( $data ) {
		if ( isset( $data['counters'] ) ) {
			$this->view['counters'] = $data['counters'];
		}
		if ( isset( $data['license'] ) ) {
			$this->view['license'] = $data['license'];
		}
		if ( isset( $data['payments'] ) ) {
			$this->view['payments'] = $data['payments'];
		}
		if ( isset( $data['entries'] ) ) {
			$this->view['entries'] = $data['entries'];
		}
		if ( isset( $data['inbox'] ) ) {
			$this->view['inbox'] = $data['inbox'];
		}
		if ( isset( $data['video'] ) ) {
			$this->view['video'] = $data['video'];
		}
		if ( isset( $data['payments'] ) ) {
			$this->view['payments'] = $data['payments'];
		}
	}

	public function get_main_widget( $echo = true ) {
		if ( is_callable( 'FrmProDashboardView::get_main_widget' ) ) {
			return FrmProDashboardView::get_main_widget( $this->view['entries'], $echo );
		}
		if ( false === $echo ) {
			return self::load_entries_template( $this->view['entries'] );
		}
		echo self::load_entries_template( $this->view['entries'] );
	}

	public function get_bottom_widget( $echo = true ) {
		if ( is_callable( 'FrmProDashboardView::get_bottom_widget' ) ) {
			return FrmProDashboardView::get_bottom_widget( $this->view['entries'], $echo );
		}
		return $this->get_pro_features( $echo );
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

	public function get_license_management( $echo = true ) {
		if ( false === $echo ) {
			return $this->load_license_management_template( $this->view['license'] );
		}
		echo wp_kses_post( $this->load_license_management_template( $this->view['license'] ) );
	}

	public function get_inbox( $echo = true ) {
		if ( false === $echo ) {
			return $this->load_inbox_template( $this->view['inbox'] );
		}
		echo $this->load_inbox_template( $this->view['inbox'] );
	}

	public function get_pro_features( $echo = true ) {
		if ( false === $echo ) {
			return $this->load_pro_features_template();
		}
		echo wp_kses_post( $this->load_pro_features_template() );
	}

	public function get_payments( $echo = true ) {
		if ( false === $echo ) {
			return $this->load_payments_template( $this->view['payments'] );
		}
		echo $this->load_payments_template( $this->view['payments'] );
	}

	public function get_youtube_video( $echo = true ) {
		if ( false === $echo ) {
			return $this->load_youtube_video_template( $this->view['video'] );
		}
		echo $this->load_youtube_video_template( $this->view['video'] );
	}

	public static function load_placeholder_template( $template ) {
		ob_start();
		include FrmAppHelper::plugin_path() . '/classes/views/dashboard/templates/widget-placeholder.php';
		return ob_get_clean();
	}

	private function load_payments_template( $template ) {
		if ( true === $template['show_placeholder'] ) {
			return $this->load_payments_placeholder( $template );
		}

		return $this->load_counters_template( $template );
	}

	private function load_pro_features_template() {
		ob_start();
		include FrmAppHelper::plugin_path() . '/classes/views/dashboard/templates/pro-features-list.php';
		return ob_get_clean();
	}

	private function load_welcome_template() {
		ob_start();
		include FrmAppHelper::plugin_path() . '/classes/views/dashboard/templates/notification-banner.php';
		return ob_get_clean();
	}

	private function load_payments_placeholder( $template ) {
		ob_start();
		include FrmAppHelper::plugin_path() . '/classes/views/dashboard/templates/payment-placeholder.php';
		return ob_get_clean();
	}

	private function load_counters_template( $template ) {
		ob_start();
		include FrmAppHelper::plugin_path() . '/classes/views/dashboard/templates/counters.php';
		return ob_get_clean();
	}

	private function load_license_management_template( $template ) {
		if ( is_callable( 'FrmProDashboardView::load_license_management' ) ) {
			return FrmProDashboardView::load_license_management( $template );
		}
		ob_start();
		include FrmAppHelper::plugin_path() . '/classes/views/dashboard/templates/license-management.php';
		return ob_get_clean();
	}

	private function load_inbox_template( $template ) {
		$subscribe_inbox_classnames  = 'frm-inbox-subscribe frmcenter';
		$subscribe_inbox_classnames .= ! empty( $template['unread'] ) ? ' frm_hidden' : '';
		$subscribe_inbox_classnames .= true === FrmDashboardController::email_is_subscribed( $template['user']->user_email ) ? ' frm-inbox-hide-form' : '';

		ob_start();
		include FrmAppHelper::plugin_path() . '/classes/views/dashboard/templates/inbox.php';
		return ob_get_clean();
	}

	public static function load_entries_template( $template ) {
		if ( true === $template['show_placeholder'] ) {
			return self::load_placeholder_template( $template );
		}

		$widget_heading = '<div class="frm-flex-box frm-justify-between"><h2 class="frm-widget-heading">Latest Entries </h2><a class="frm-widget-cta" href="">View all entries</a></div>';
		return $widget_heading . self::load_entries_list_template();
	}

	private static function load_entries_list_template() {
		$params                  = FrmForm::get_admin_params();
		$controler_entires_table = apply_filters( 'frm_entries_list_class', 'FrmEntriesListHelper' );
		$wp_list_table           = new $controler_entires_table( array( 'params' => $params ) );
		$wp_list_table->prepare_items( array( 'items-per-page' => 7 ) );

		ob_start();
		$wp_list_table->display(
			array(
				'display-top-nav'        => false,
				'display-bottom-nav'     => false,
				'display-bottom-headers' => false,
			),
		);
		return ob_get_clean();
	}

	private function load_youtube_video_template( $template ) {
		if ( null === $template['id'] ) {
			return '';
		}
		return '<iframe style="width: 100%; height: 224px; margin-bottom:-6px" src="https://www.youtube.com/embed/' . $template['id'] . '" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>';
	}
}
