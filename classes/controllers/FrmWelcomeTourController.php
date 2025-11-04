<?php
/**
 * Welcome Tour Controller class.
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Handles the Welcome Tour page in the admin area.
 *
 * @since 6.25.1
 */
class FrmWelcomeTourController {

	/**
	 * Option name to store Welcome Tour data.
	 *
	 * @var string
	 */
	const CHECKLIST_OPTION = 'frm-welcome-tour';

	/**
	 * The script handle.
	 *
	 * @var string
	 */
	const SCRIPT_HANDLE = 'frm-welcome-tour';

	/**
	 * Checklist data to pass to the view.
	 *
	 * @var array
	 */
	private static $checklist = array();

	/**
	 * Steps data to pass to the view.
	 *
	 * @var array
	 */
	private static $steps = array();

	/**
	 * Whether the current page is the dashboard page.
	 *
	 * @var bool
	 */
	private static $is_dashboard_page = false;

	/**
	 * The current form ID.
	 *
	 * @var int
	 */
	private static $current_form_id = 0;

	/**
	 * Initializes the Welcome Tour.
	 *
	 * @return void
	 */
	public static function admin_init() {
		if ( ! self::should_show_welcome_tour() ) {
			return;
		}

		add_filter( 'frm_should_show_floating_links', '__return_false' );
		add_filter( 'admin_body_class', self::class . '::add_admin_body_classes', 999 );
		add_action( 'admin_enqueue_scripts', self::class . '::enqueue_assets', 15 );

		if ( self::$is_dashboard_page ) {
			add_action( 'admin_footer', self::class . '::maybe_mark_welcome_tour_as_seen', 999 );
			return;
		}

		add_action( 'admin_footer', self::class . '::render', 999 );
		add_action( 'frm_after_changed_form_style', self::class . '::mark_styler_step_as_completed' );
		add_action( 'frm_after_saved_style', self::class . '::mark_styler_step_as_completed' );
	}

	/**
	 * Determines if the welcome tour should be shown based on current page context.
	 *
	 * @return bool True if welcome tour should be shown, false otherwise.
	 */
	private static function should_show_welcome_tour() {
		// Only show welcome tour for new installs on Formidable admin pages.
		if ( ! FrmAppHelper::is_formidable_admin() || empty( FrmAppHelper::get_settings()->installed_after_welcome_tour_update ) ) {
			return false;
		}

		self::$checklist = self::get_checklist();
		if (
			self::is_tour_completed() && ( ! empty( self::$checklist['completed_seen'] ) || ! self::get_current_form_id() )
			|| ! empty( self::$checklist['dismissed'] )
		) {
			return false;
		}

		self::$is_dashboard_page = FrmDashboardController::is_dashboard_page();
		if ( self::$is_dashboard_page ) {
			return empty( self::$checklist['seen'] );
		}

		self::setup_checklist_progress();
		return self::should_show_checklist();
	}

	/**
	 * Sets up the checklist progress.
	 *
	 * @return void
	 */
	public static function setup_checklist_progress() {
		self::$steps = self::get_steps();
		$step_keys   = self::$steps['keys'];
		$active_step = 0;

		foreach ( $step_keys as $index => $step_key ) {
			$completed_step = isset( self::$checklist['completed_steps'][ $step_key ] );

			if ( false === $completed_step ) {
				switch ( $step_key ) {
					case 'create-form':
						$completed_step = self::more_than_the_default_form_exists();
						break;
					case 'embed-form':
						$completed_step = self::check_for_form_embeds();
						break;
				}
			}

			if ( $completed_step ) {
				self::$checklist['completed_steps'][ $step_key ] = true;
			}

			// Count completed steps from start until gap found.
			if ( $completed_step && $index === $active_step ) {
				$active_step++;
			}

			self::$steps['steps'][ $index ]['completed'] = $completed_step;
		}//end foreach

		self::$checklist['active_step'] = $active_step;

		if ( $active_step === count( $step_keys ) ) {
			self::$checklist['done']            = true;
			self::$checklist['active_step_key'] = 'completed';
		} else {
			self::$checklist['active_step_key'] = $step_keys[ $active_step ];
		}

		self::save_checklist();
	}

	/**
	 * Gets the checklist steps.
	 *
	 * @return array
	 */
	private static function get_steps() {
		$steps = array(
			'create-form' => array(
				'title'       => __( 'Create your first form', 'formidable' ),
				'description' => __( 'Start from scratch or jump in with one of our ready-to-use templates.', 'formidable' ),
			),
			'add-fields'  => array(
				'title'       => __( 'Add fields to your form', 'formidable' ),
				'description' => __( 'Click or drag fields from the left to add them to your form. Edit and/or delete them as needed.', 'formidable' ),
			),
			'style-form'  => array(
				'title'       => __( 'Style your form', 'formidable' ),
				'description' => __( 'Our default style looks great, but feel free to modify it! Change the color, font size, spacing, or whatever else you\'d like.', 'formidable' ), // phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
				'link'        => FrmStylesHelper::get_list_url( self::get_current_form_id() ),
			),
			'embed-form'  => array(
				'title'       => __( 'Embed in a page', 'formidable' ),
				'description' => __( 'Time to get some responses! Add your brand new form to a current page, or embed it on a new one.', 'formidable' ),
				'link'        => FrmStylesHelper::get_list_url( self::get_current_form_id() ),
			),
		);

		$steps_keys = array_keys( $steps );

		return array(
			'keys'  => $steps_keys,
			'steps' => self::fill_step_completed_data( $steps, $steps_keys ),
		);
	}

	/**
	 * Fills the steps with the completed data.
	 *
	 * @param array $steps The steps to fill.
	 * @param array $steps_keys The steps keys.
	 * @return array The steps with the completed data.
	 */
	private static function fill_step_completed_data( $steps, $steps_keys ) {
		return array_map(
			function ( $step, $step_key ) {
				$step['completed'] = isset( self::$checklist['completed_steps'][ $step_key ] );
				return $step;
			},
			$steps,
			$steps_keys
		);
	}

	/**
	 * Get spotlight data for the current active step.
	 *
	 * @return array The spotlight data.
	 */
	private static function get_spotlight_data() {
		$spotlight_data = array();

		switch ( self::$checklist['active_step_key'] ) {
			case 'create-form':
				$spotlight_data = array(
					'target'        => '#frm-form-templates-create-form-divider',
					'left-position' => 'middle',
				);
				break;
			case 'add-fields':
				$spotlight_data = array(
					'target'        => '.frm-settings-panel .frm-tabs-navs li.frm-active',
					'left-position' => '140px',
				);
				break;
			case 'style-form':
				$spotlight_data = array(
					'target'        => '#frm_style_sidebar .frm-style-card > div',
					'left-position' => 'end',
					'offset'        => array(
						'top'  => -22,
						'left' => 16,
					),
				);
				break;
			case 'embed-form':
				$spotlight_data = array(
					'target'        => '#frm-embed-action',
					'left-position' => 'middle',
					'placement'     => 'bottom',
				);
				break;
			default:
				break;
		}//end switch

		return array_merge( self::$steps['steps'][ self::$checklist['active_step'] ], $spotlight_data );
	}

	/**
	 * Marks the welcome tour as seen if it hasn't been seen yet.
	 *
	 * @return void
	 */
	public static function maybe_mark_welcome_tour_as_seen() {
		if ( ! empty( self::$checklist['seen'] ) ) {
			return;
		}

		self::$checklist['seen'] = true;
		self::save_checklist();
	}

	/**
	 * Render the welcome tour elements.
	 *
	 * @return void
	 */
	public static function render() {
		$view_path         = FrmAppHelper::plugin_path() . '/classes/views/welcome-tour/';
		$is_tour_completed = self::is_tour_completed();
		$current_form_id   = self::get_current_form_id();

		if ( $is_tour_completed ) {
			if ( ! $current_form_id ) {
				return;
			}
			self::mark_completed_as_seen();
			$steps_path = $view_path . 'steps/step-completed.php';
		} else {
			$steps_path  = $view_path . 'steps/list.php';
			$steps       = array_combine( self::$steps['keys'], self::$steps['steps'] );
			$active_step = self::$checklist['active_step_key'];
			$spotlight   = self::get_spotlight_data();
		}

		include $view_path . 'index.php';
	}

	/**
	 * Shows links after completing the Welcome tour.
	 *
	 * @param int $current_form_id Current form ID.
	 */
	public static function show_completed_links( $current_form_id ) {
		$links = array(
			'setup-email-notification'  => array(
				'url'  => admin_url( 'admin.php?page=formidable&frm_action=settings&id=' . $current_form_id . '&t=email_settings' ),
				'text' => __( 'Setup email notifications', 'formidable' ),
			),
			'customize-success-message' => array(
				'url'  => admin_url( 'admin.php?page=formidable&frm_action=settings&id=' . $current_form_id . '&t=email_settings' ),
				'text' => __( 'Customize success message', 'formidable' ),
			),
			'manage-entries'            => array(
				'url'  => admin_url( 'admin.php?page=formidable-entries' ),
				'text' => __( 'Manage form entries', 'formidable' ),
			),
			'explore-addon'             => array(
				'url'  => admin_url( 'admin.php?page=formidable-addons' ),
				'text' => __( 'Explore integrations', 'formidable' ),
			),
		);

		$button_attrs = array(
			'class'             => 'frm-usage-tracking-flow-click button frm-button-secondary frm-button-sm frm-mb-2xs',
			'target'            => '_blank',
			'rel'               => 'noopener',
			'data-tracking-key' => 'welcome_tour_completed_link_click',
		);

		foreach ( $links as $key => $link ) {
			$attrs = $button_attrs + array( 'data-tracking-value' => $key );
			?>
			<a href="<?php echo esc_url( $link['url'] ); ?>" <?php FrmAppHelper::array_to_html_params( $attrs, true ); ?>>
				<?php echo esc_html( $link['text'] ); ?>
			</a>
			<?php
		}
	}

	/**
	 * Checks if the checklist should be shown.
	 *
	 * @return bool True if the checklist should be shown, false otherwise.
	 */
	private static function should_show_checklist() {
		if ( self::is_tour_completed() ) {
			// Show the completed step only if user hasn't seen it yet.
			return empty( self::$checklist['completed_seen'] );
		}

		$active_step            = self::$checklist['active_step_key'];
		$page                   = FrmAppHelper::simple_get( 'page' );
		$is_form_templates_page = FrmFormTemplatesController::PAGE_SLUG === $page;
		$is_form_builder_page   = FrmAppHelper::is_form_builder_page();
		$is_style_editor_page   = FrmAppHelper::is_style_editor_page();

		switch ( $active_step ) {
			case 'create-form':
				return $is_form_templates_page;
			case 'add-fields':
				return $is_form_builder_page;
			case 'style-form':
			case 'embed-form':
			case 'completed':
				return $is_form_builder_page || $is_style_editor_page;
			default:
				return false;
		}
	}

	/**
	 * AJAX callback to mark a checklist step as completed.
	 *
	 * @return void
	 */
	public static function ajax_mark_checklist_step_as_completed() {
		check_ajax_referer( 'frm_ajax', 'nonce' );
		FrmAppHelper::permission_check( 'frm_edit_forms' );

		$step_key = FrmAppHelper::get_post_param( 'step_key' );
		if ( ! $step_key ) {
			wp_send_json_error( __( 'Invalid step', 'formidable' ) );
		}

		self::$checklist                                 = self::get_checklist();
		self::$checklist['completed_steps'][ $step_key ] = true;
		self::save_checklist();

		wp_send_json_success();
	}

	/**
	 * AJAX callback to dismiss the welcome tour.
	 *
	 * @return void
	 */
	public static function ajax_dismiss_welcome_tour() {
		check_ajax_referer( 'frm_ajax', 'nonce' );
		FrmAppHelper::permission_check( 'frm_edit_forms' );

		self::$checklist              = self::get_checklist();
		self::$checklist['dismissed'] = true;
		self::save_checklist();

		wp_send_json_success();
	}

	/**
	 * Checks if more than the default form exists.
	 *
	 * @return bool True if more than the default form exists, false otherwise.
	 */
	private static function more_than_the_default_form_exists() {
		$form_keys = FrmDb::get_col( 'frm_forms', array(), 'form_key' );
		if ( count( $form_keys ) > 1 ) {
			return true;
		}

		return $form_keys && ! in_array( 'contact-form', $form_keys, true );
	}

	/**
	 * Checks if there are form embeds.
	 *
	 * @return bool True if there are form embeds, false otherwise.
	 */
	private static function check_for_form_embeds() {
		global $wpdb;
		$result = $wpdb->get_var( "SELECT 1 FROM {$wpdb->posts} WHERE post_content LIKE '%[formidable %' LIMIT 1" );
		return '1' === $result;
	}

	/**
	 * Get the active step.
	 *
	 * @return int
	 */
	private static function get_active_step() {
		return self::$checklist['active_step'] ?? 0;
	}

	/**
	 * Mark the styler step as completed.
	 *
	 * @return void
	 */
	public static function mark_styler_step_as_completed() {
		if ( isset( self::$checklist['completed_steps']['style-form'] ) ) {
			return;
		}

		self::$checklist['completed_steps']['style-form'] = true;
		self::save_checklist();
	}

	/**
	 * Adds custom classes to the existing string of admin body classes.
	 *
	 * @param string $classes Existing body classes.
	 * @return string Updated list of body classes, including the newly added classes.
	 */
	public static function add_admin_body_classes( $classes ) {
		return $classes . ' frm-admin-welcome-tour';
	}

	/**
	 * Enqueues the Welcome Tour page scripts and styles.
	 *
	 * @return void
	 */
	public static function enqueue_assets() {
		$plugin_url = FrmAppHelper::plugin_url();
		$version    = FrmAppHelper::plugin_version();

		wp_enqueue_style( self::SCRIPT_HANDLE, $plugin_url . '/css/admin/welcome-tour.css', array(), $version );

		wp_register_script( self::SCRIPT_HANDLE, $plugin_url . '/js/welcome-tour.js', array( 'wp-i18n' ), $version, true );
		wp_localize_script( self::SCRIPT_HANDLE, 'frmWelcomeTourVars', self::get_js_variables() );
		wp_enqueue_script( self::SCRIPT_HANDLE );
		wp_set_script_translations( self::SCRIPT_HANDLE, 'formidable' );

		FrmAppHelper::dequeue_extra_global_scripts();
	}

	/**
	 * Get the Welcome Tour JS variables as an array.
	 *
	 * @return array
	 */
	private static function get_js_variables() {
		return array(
			'IS_DASHBOARD_PAGE'    => self::$is_dashboard_page,
			'IS_WELCOME_TOUR_SEEN' => ! empty( self::$checklist['seen'] ),
			'PROGRESS_BAR_PERCENT' => self::get_welcome_tour_progress_bar_percent(),
			'TOUR_URL'             => admin_url( 'admin.php?page=formidable-form-templates' ),
		);
	}

	/**
	 * Get the Welcome Tour progress bar percentage.
	 *
	 * @return int
	 */
	private static function get_welcome_tour_progress_bar_percent() {
		if ( ! self::$steps ) {
			return 0;
		}

		$percent = self::get_active_step() / count( self::$steps['keys'] ) * 100;

		return (int) $percent;
	}

	/**
	 * Saves the checklist data.
	 *
	 * @param array|null $checklist The checklist data to set.
	 */
	public static function save_checklist( $checklist = null ) {
		update_option( self::CHECKLIST_OPTION, $checklist ?? self::$checklist, false );
	}

	/**
	 * Gets the checklist data.
	 *
	 * @return array The checklist data.
	 */
	public static function get_checklist() {
		return get_option(
			self::CHECKLIST_OPTION,
			array(
				'completed_steps' => array(),
				'active_step_key' => 'create-form',
			)
		);
	}

	/**
	 * Build a tracked URL with UTM parameters and affiliate tracking.
	 *
	 * @param string $url The base URL to process.
	 * @return string The processed URL with UTM parameters and affiliate tracking.
	 */
	public static function make_tracked_url( $url ) {
		$utm_params = array(
			'utm_source'   => 'WordPress',
			'utm_medium'   => 'welcome-tour',
			'utm_campaign' => 'liteplugin',
		);

		return FrmAppHelper::make_affiliate_url( add_query_arg( $utm_params, $url ) );
	}

	/**
	 * Get the current form ID.
	 *
	 * @return int The current form ID.
	 */
	public static function get_current_form_id() {
		if ( self::$current_form_id ) {
			return self::$current_form_id;
		}

		self::$current_form_id = FrmAppHelper::simple_get( 'form', 'absint', 0 );
		if ( ! self::$current_form_id ) {
			self::$current_form_id = FrmAppHelper::simple_get( 'id', 'absint', 0 );
		}

		return self::$current_form_id;
	}

	/**
	 * Checks if the tour is completed.
	 *
	 * @return bool True if the tour is completed, false otherwise.
	 */
	private static function is_tour_completed() {
		return ! empty( self::$checklist['done'] );
	}

	/**
	 * Marks the completed state as seen.
	 *
	 * @return void
	 */
	private static function mark_completed_as_seen() {
		if ( ! empty( self::$checklist['completed_seen'] ) ) {
			return;
		}

		self::$checklist['completed_seen'] = true;
		self::save_checklist();
	}

	/**
	 * Gets usage tracking data.
	 *
	 * @return array
	 */
	public static function get_usage_data() {
		// Do not use the get_checklist() method to prevent adding default value.
		$option = get_option( self::CHECKLIST_OPTION );
		if ( ! $option ) {
			// Welcome tour doesn't show on this site.
			return array();
		}

		$usage_data = array();
		$steps      = self::get_steps();

		foreach ( $steps as $key => $step ) {
			$usage_data[ 'completed_step_' . $key ] = empty( $option['completed_steps'][ $key ] ) ? 0 : 1;
		}

		$usage_data['done'] = empty( $option['done'] ) ? 0 : 1;

		// If dismissed, the dismissed step is the active step.
		$usage_data['dismissed'] = empty( $option['dismissed'] ) ? 0 : $option['active_step_key'];

		return $usage_data;
	}
}
