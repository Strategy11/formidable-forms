<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmDashboardView {

	/**
	 * The dashboard default view args
	 *
	 * @var array
	 */
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
			'widget-heading'   => 'Latest Entries',
			'show-placeholder' => true,
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
							'counter'       => '0',
						),
					),
				),
			),
		),
	);

	/**
	 * Init all dashboard's widgets view args.
	 *
	 * @param array $data.
	 *
	 * @return void
	 */
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

	/**
	 * The dashboard widget that will show right below counters.
	 *
	 * @param boolean $echo
	 *
	 * @return void|string Echo or return the widget's HTML
	 */
	public function get_main_widget( $echo = true ) {
		if ( is_callable( 'FrmProDashboardView::get_main_widget' ) ) {
			return FrmProDashboardView::get_main_widget( $this->view['entries'], $echo );
		}
		if ( false === $echo ) {
			return self::load_entries_template( $this->view['entries'] );
		}
		echo wp_kses_post( self::load_entries_template( $this->view['entries'] ) );
	}

	/**
	 * The dashboard widget that will show on the bottom.
	 *
	 * @param boolean $echo
	 *
	 * @return string The widget's HTML
	 */
	public function get_bottom_widget( $echo = true ) {
		if ( is_callable( 'FrmProDashboardView::get_bottom_widget' ) ) {
			return FrmProDashboardView::get_bottom_widget( $this->view['entries'], $echo );
		}
		if ( FrmAppHelper::pro_is_installed() ) {
			return '';
		}
		return $this->get_pro_features( $echo );
	}

	/**
	 * Dashboard - kses args for svg.
	 *
	 * @return array
	 */
	private static function wp_svg_kses_args() {
		$kses_defaults = wp_kses_allowed_html( 'post' );
		$svg_args      = array(
			'svg'   => array(
				'class'           => true,
				'aria-hidden'     => true,
				'aria-labelledby' => true,
				'role'            => true,
				'xmlns'           => true,
				'width'           => true,
				'height'          => true,
				'viewbox'         => true,
			),
			'use'   => array(
				'xlink:href' => true,
			),
			'g'     => array( 'fill' => true ),
			'title' => array( 'title' => true ),
			'path'  => array(
				'd'    => true,
				'fill' => true,
			),
		);

		return array_merge( $kses_defaults, $svg_args );
	}

	/**
	 * Dashboard - welcome banner template.
	 *
	 * @param boolean $echo
	 *
	 * @return void|string Echo or return the widget's HTML
	 */
	public function get_welcome_banner( $echo = true ) {
		if ( true === FrmDashboardController::welcome_banner_has_closed() || FrmFormsController::get_forms_count() ) {
			return;
		}
		return FrmAppHelper::clip(
			function() {
				echo wp_kses( $this->load_welcome_template(), self::wp_svg_kses_args() );
			},
			$echo
		);
	}

	/**
	 * Dashboard - top counters card widgets template.
	 *
	 * @param boolean $echo
	 *
	 * @return string Echo or return the widgets's HTML
	 */
	public function get_counters( $echo = true ) {
		return FrmAppHelper::clip(
			function() {
				echo wp_kses_post( $this->load_counters_template( $this->view['counters'] ) );
			},
			$echo
		);
	}

	/**
	 * Dashboard -license management widget template.
	 *
	 * @param boolean $echo
	 *
	 * @return string Echo or return the widgets's HTML
	 */
	public function get_license_management( $echo = true ) {
		return FrmAppHelper::clip(
			function() {
				echo wp_kses_post( $this->load_license_management_template( $this->view['license'] ) );
			},
			$echo
		);
	}

	/**
	 * Dashboard - inbox widget template.
	 *
	 * @param boolean $echo
	 *
	 * @return string Echo or return the widgets's HTML
	 */
	public function get_inbox( $echo = true ) {
		return FrmAppHelper::clip(
			function() {
				// needs kses input
				echo $this->load_inbox_template( $this->view['inbox'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			},
			$echo
		);
	}

	/**
	 * Dashboard - pro features list widget template.
	 *
	 * @param boolean $echo
	 *
	 * @return string Echo or return the widgets's HTML
	 */
	private function get_pro_features( $echo = true ) {
		return FrmAppHelper::clip(
			function() {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo FrmAppHelper::kses( $this->load_pro_features_template(), 'all' );
			},
			$echo
		);
	}

	/**
	 * Dashboard - total earnings widget template.
	 *
	 * @param boolean $echo
	 *
	 * @return string Echo or return the widgets's HTML
	 */
	public function get_payments( $echo = true ) {
		return FrmAppHelper::clip(
			function() {
				echo wp_kses_post( $this->load_payments_template( $this->view['payments'] ) );
			},
			$echo
		);
	}

	/**
	 * Dashboard - YouTube video widget template.
	 *
	 * @param boolean $echo
	 *
	 * @return string Echo or return the widgets's HTML
	 */
	public function get_youtube_video( $echo = true ) {
		return FrmAppHelper::clip(
			function() {
				echo wp_kses(
					$this->load_youtube_video_template( $this->view['video'] ),
					array(
						'iframe' => array(
							'src'             => true,
							'title'           => true,
							'alow'            => true,
							'frameborder'     => true,
							'allowfullscreen' => true,
						),
					),
				);
			},
			$echo
		);
	}

	/**
	 * Dashboard - load placeholder template.
	 *
	 * @param array $template
	 *
	 * @return string Placeholder HTML template
	 */
	public static function load_placeholder_template( $template ) {
		ob_start();
		include FrmAppHelper::plugin_path() . '/classes/views/dashboard/templates/widget-placeholder.php';
		return ob_get_clean();
	}

	/**
	 * Dashboard - load total earnings/payments template.
	 *
	 * @param array $template
	 *
	 * @return string HTML template
	 */
	private function load_payments_template( $template ) {
		if ( true === $template['show-placeholder'] ) {
			return $this->load_payments_placeholder( $template );
		}

		return $this->load_counters_template( $template );
	}

	/**
	 * Dashboard - load pro features list template.
	 *
	 * @return string HTML template
	 */
	private function load_pro_features_template() {
		$features = array(
			sprintf(
				/* translators: %d: number of form templates */
				__( '%d+ Form Templates', 'formidable' ),
				FrmFormTemplatesController::get_template_count()
			),
			__( 'Calculated Fields and Math', 'formidable' ),
			__( 'Quizzes', 'formidable' ),
			__( 'Save and Continue', 'formidable' ),
			__( 'Smart Forms with Conditional Logic', 'formidable' ),
			__( 'Ecommerce Pricing Fields', 'formidable' ),
			__( 'Advanced Fields', 'formidable' ),
			__( 'Schedule Forms & Limit Responses', 'formidable' ),
			__( 'Display Form Data with Views', 'formidable' ),
			__( 'And much more...', 'formidable' ),
		);

		ob_start();
		include FrmAppHelper::plugin_path() . '/classes/views/dashboard/templates/pro-features-list.php';
		return ob_get_clean();
	}

	/**
	 * Dashboard - load welcome banner's template.
	 *
	 * @return string HTML template
	 */
	private function load_welcome_template() {
		ob_start();
		include FrmAppHelper::plugin_path() . '/classes/views/dashboard/templates/notification-banner.php';
		return ob_get_clean();
	}

	/**
	 * Dashboard - load total earnings/payments placeholder template.
	 *
	 * @param array $template
	 *
	 * @return string HTML template
	 */
	private function load_payments_placeholder( $template ) {
		ob_start();
		include FrmAppHelper::plugin_path() . '/classes/views/dashboard/templates/payment-placeholder.php';
		return ob_get_clean();
	}

	/**
	 * Dashboard - load counters template. This function handles the top counters template and total earnings template.
	 *
	 * @param array $template
	 *
	 * @return string HTML template
	 */
	private function load_counters_template( $template ) {
		ob_start();
		include FrmAppHelper::plugin_path() . '/classes/views/dashboard/templates/counters.php';
		return ob_get_clean();
	}

	/**
	 * Dashboard - load license management template.
	 *
	 * @param array $template
	 *
	 * @return string HTML template
	 */
	private function load_license_management_template( $template ) {
		if ( is_callable( 'FrmProDashboardView::load_license_management' ) ) {
			return FrmProDashboardView::load_license_management( $template );
		}
		ob_start();
		include FrmAppHelper::plugin_path() . '/classes/views/dashboard/templates/license-management.php';
		return ob_get_clean();
	}

	/**
	 * Dashboard - load inbox template.
	 *
	 * @param array $template
	 *
	 * @return string HTML template
	 */
	private function load_inbox_template( $template ) {
		$subscribe_inbox_classnames  = 'frm-inbox-subscribe frmcenter';
		$subscribe_inbox_classnames .= ! empty( $template['unread'] ) ? ' frm_hidden' : '';
		$subscribe_inbox_classnames .= true === FrmDashboardController::email_is_subscribed( $template['user']->user_email ) ? ' frm-inbox-hide-form' : '';

		ob_start();
		include FrmAppHelper::plugin_path() . '/classes/views/dashboard/templates/inbox.php';
		return ob_get_clean();
	}

	/**
	 * Dashboard - the entries widget template. Load entries placeholder if there are no entries or load the entries list template.
	 *
	 * @param array $template
	 *
	 * @return string HTML template
	 */
	public static function load_entries_template( $template ) {
		if ( true === $template['show-placeholder'] ) {
			return self::load_placeholder_template( $template );
		}

		$widget_heading = '<div class="frm-flex-box frm-justify-between"><h2 class="frm-widget-heading">' . $template['widget-heading'] . '</h2><a class="frm-widget-cta" href="' . $template['cta']['link'] . '">' . $template['cta']['label'] . '</a></div>';
		return $widget_heading . self::load_entries_list_template();
	}

	/**
	 * Dashboard - load the entries list template.
	 *
	 * @return string HTML template
	 */
	private static function load_entries_list_template() {
		add_filter(
			'formidable_page_formidable_entries_per_page',
			function() {
				return 7;
			}
		);

		$params                  = FrmForm::get_admin_params();
		$controler_entires_table = apply_filters( 'frm_entries_list_class', 'FrmEntriesListHelper' );
		$wp_list_table           = new $controler_entires_table( array( 'params' => $params ) );
		$wp_list_table->prepare_items();

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

	/**
	 * Dashboard - load YouTube video template
	 *
	 * @param array $template
	 *
	 * @return string
	 */
	private function load_youtube_video_template( $template ) {
		if ( null === $template['id'] ) {
			return '';
		}
		return '<iframe src="https://www.youtube.com/embed/' . $template['id'] . '" title="YouTube video player" frameborder="0" allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>';
	}
}
