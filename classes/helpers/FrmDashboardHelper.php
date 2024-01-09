<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmDashboardHelper {

	/**
	 * The dashboard default view args
	 *
	 * @var array
	 */
	private $view = array();

	/**
	 * Init all dashboard's widgets view args.
	 *
	 * @param array $data {
	 *    An array of view args required to construct dashboard view.
	 *
	 *    @type array $counters Array of counters.
	 *        @type string $counters['counters'][]['heading']
	 *        @type int    $counters['counters'][]['counter']
	 *        @type string $counters['counters'][]['type'] The counter template type: default|currency.
	 *        @type array  $counters['counters'][]['cta']
	 *            @type string  $counters['counters'][]['cta']['title']
	 *            @type string  $counters['counters'][]['cta']['link']
	 *            @type boolean $counters['counters'][]['cta']['display'] If true a CTA will be displayed instead of the counter.
	 *    @type array $license Array of license args
	 *        @type string $license['heading']
	 *        @type string $license['copy']
	 *        @type array  $license['buttons']
	 *            @type string $license['buttons'][]['label']
	 *            @type string $license['buttons'][]['link']
	 *            @type string $license['buttons'][]['type'] primary|secondary
	 *    @type array $inbox Array of inbox args
	 *        @type array $inbox['unread']
	 *            @type string $inbox['unread'][]['message']
	 *            @type string $inbox['unread'][]['subject']
	 *            @type string $inbox['unread'][]['icon']
	 *            @type string $inbox['unread'][]['cta']
	 *            @type string $inbox['unread'][]['expires']
	 *            @type array  $inbox['unread'][]['who']
	 *            @type int    $inbox['unread'][]['starts']
	 *            @type int    $inbox['unread'][]['created']
	 *        @type array $inbox['dismissed']
	 *            @type string $inbox['dismissed'][]['message']
	 *            @type string $inbox['dismissed'][]['subject']
	 *            @type string $inbox['dismissed'][]['icon']
	 *            @type string $inbox['dismissed'][]['cta']
	 *            @type string $inbox['dismissed'][]['expires']
	 *            @type array  $inbox['dismissed'][]['who']
	 *            @type int    $inbox['dismissed'][]['starts']
	 *            @type int    $inbox['dismissed'][]['created']
	 *        @type int   $inbox['user']
	 *    @type array $entries Array of entries args
	 *        @type string $entries['widget-heading']
	 *        @type array  $entries['cta']
	 *            @type string $entries['cta']['label']
	 *            @type string $entries['cta']['link']
	 *        @type bool   $entries['show-placeholder']
	 *        @type int    $entries['count']
	 *        @type array  $entries['placeholder']
	 *            @type string     $entries['placeholder']['background']
	 *            @type string     $entries['placeholder']['heading']
	 *            @type string     $entries['placeholder']['copy']
	 *            @type null|array $entries['placeholder']['button']
	 *                @type string $entries['placeholder']['button']['label']
	 *                @type string $entries['placeholder']['button']['link']
	 *    @type array $payments Array of payments args
	 *        @type boolean $payments['show-placeholder']
	 *        @type array   $payments['placeholder']
	 *            @type string $payments['placeholder']['copy']
	 *            @type array  $payments['placeholder']['cta']
	 *                @type string $payments['placeholder']['cta']['link']
	 *                @type string $payments['placeholder']['cta']['label']
	 *        @type array   $payments['counters']
	 *            @type string $payments['counters'][]['heading']
	 *            @type string $payments['counters'][]['type'] currency|default
	 *            @type array  $payments['counters'][]['items']
	 *                @type string $payments['counters'][]['items'][]['counter_label']
	 *                @type int    $payments['counters'][]['items'][]['counter']
	 *    @type array $video Array of video args
	 *        @type string $video['id'] YouTube video ID
	 * }
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
	 * @return void
	 */
	public function get_main_widget() {
		if ( FrmAppHelper::pro_is_installed() && is_callable( 'FrmProDashboardHelper::get_main_widget' ) ) {
			FrmProDashboardHelper::get_main_widget( $this->view['entries'] );
			return;
		}

		self::load_entries_template( $this->view['entries'] );
	}

	/**
	 * The dashboard widget that will show on the bottom.
	 *
	 * @return void
	 */
	public function get_bottom_widget() {
		if ( FrmAppHelper::pro_is_installed() && is_callable( 'FrmProDashboardHelper::get_bottom_widget' ) ) {
			echo '<div class="frm-dashboard-widget frm-card-item frm-px-0">';
			FrmProDashboardHelper::get_bottom_widget( $this->view['entries'] );
			echo '</div>';
		} elseif ( ! FrmAppHelper::pro_is_installed() ) {
			$this->get_pro_features();
		}
	}

	/**
	 * Dashboard - welcome banner template.
	 *
	 * @return void
	 */
	public function get_welcome_banner() {
		if ( true === FrmDashboardController::welcome_banner_has_closed() || FrmForm::get_forms_count() ) {
			return;
		}

		include FrmAppHelper::plugin_path() . '/classes/views/dashboard/templates/notification-banner.php';
	}

	/**
	 * Dashboard - top counters card widgets template.
	 *
	 * @return void
	 */
	public function get_counters() {
		$this->load_counters_template( $this->view['counters'] );
	}

	/**
	 * Dashboard -license management widget template.
	 *
	 * @return void
	 */
	public function get_license_management() {
		$template = $this->view['license'];
		if ( is_callable( 'FrmProDashboardHelper::load_license_management' ) ) {
			FrmProDashboardHelper::load_license_management( $template );
			return;
		}

		include FrmAppHelper::plugin_path() . '/classes/views/dashboard/templates/license-management.php';
	}

	/**
	 * Dashboard - license buttons for Lite.
	 *
	 * @since x.x
	 *
	 * @return array
	 */
	public function get_license_buttons() {
		return array(
			array(
				'label'   => __( 'Connect Account', 'formidable' ),
				'link'    => FrmAddonsController::connect_link(),
				'classes' => 'frm-button-primary frm-show-unauthorized',
			),
			array(
				'label'   => __( 'Get Formidable PRO', 'formidable' ),
				'link'    => FrmAppHelper::admin_upgrade_link( 'settings-license' ),
				'classes' => 'frm-button-secondary frm-show-unauthorized',
			),
		);
	}

	/**
	 * Dashboard - inbox widget template.
	 *
	 * @return void
	 */
	public function get_inbox() {
		$template = $this->view['inbox'];

		$subscribe_inbox_classnames  = 'frm-inbox-subscribe frmcenter';
		$subscribe_inbox_classnames .= ! empty( $template['unread'] ) ? ' frm_hidden' : '';
		$subscribe_inbox_classnames .= true === FrmDashboardController::email_is_subscribed( $template['user']->user_email ) ? ' frm-inbox-hide-form' : '';

		include FrmAppHelper::plugin_path() . '/classes/views/inbox/list.php';
	}

	/**
	 * Dashboard - pro features list widget template.
	 *
	 * @return void
	 */
	private function get_pro_features() {
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

		include FrmAppHelper::plugin_path() . '/classes/views/dashboard/templates/pro-features-list.php';
	}

	/**
	 * Dashboard - total earnings widget template.
	 *
	 * @return void
	 */
	public function get_payments() {
		$this->load_payments_template( $this->view['payments'] );
	}

	/**
	 * Dashboard - YouTube video widget template.
	 *
	 * @param string $classes The widget's classes.
	 *
	 * @return void
	 */
	public function get_youtube_video( $classes ) {
		$template = $this->view['video'];
		if ( null === $template['id'] ) {
			return;
		}

		echo '<div class="' . esc_attr( $classes ) . '">' .
			'<iframe src="https://www.youtube.com/embed/' . esc_attr( $template['id'] ) . '" title="YouTube video player" frameborder="0" allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>' .
			'</div>';
	}

	/**
	 * Dashboard - load placeholder template.
	 *
	 * @param array $template
	 *
	 * @return void
	 */
	public static function load_placeholder_template( $template ) {
		include FrmAppHelper::plugin_path() . '/classes/views/dashboard/templates/widget-placeholder.php';
	}

	/**
	 * Dashboard - load total earnings/payments template or placeholder.
	 *
	 * @param array $template
	 *
	 * @return void
	 */
	private function load_payments_template( $template ) {
		if ( true === $template['show-placeholder'] ) {
			include FrmAppHelper::plugin_path() . '/classes/views/dashboard/templates/payment-placeholder.php';
			return;
		}

		$this->load_counters_template( $template );
	}

	/**
	 * Dashboard - load counters template. This function handles the top counters template and total earnings template.
	 *
	 * @param array $template
	 *
	 * @return void
	 */
	private function load_counters_template( $template ) {
		include FrmAppHelper::plugin_path() . '/classes/views/dashboard/templates/counters.php';
	}

	/**
	 * Dashboard - the entries widget template. Load entries placeholder if there are no entries or load the entries list template.
	 *
	 * @param array $template
	 *
	 * @return void
	 */
	public static function load_entries_template( $template ) {
		if ( true === $template['show-placeholder'] ) {
			self::load_placeholder_template( $template );
			return;
		}

		echo '<div class="frm-flex-box frm-justify-between"><h2 class="frm-widget-heading">' . esc_html( $template['widget-heading'] ) . '</h2><a class="frm-widget-cta" href="' . esc_url( $template['cta']['link'] ) . '">' . esc_html( $template['cta']['label'] ) . '</a></div>';
		self::load_entries_list_template();
	}

	/**
	 * Dashboard - load the entries list template.
	 *
	 * @return void
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

		$wp_list_table->display(
			array(
				'display-top-nav'        => false,
				'display-bottom-nav'     => false,
				'display-bottom-headers' => false,
			)
		);

	}
}
