<?php

namespace {
	define( 'MINUTE_IN_SECONDS', 60 );
	define( 'HOUR_IN_SECONDS', 60 * MINUTE_IN_SECONDS );
	define( 'DAY_IN_SECONDS', 24 * HOUR_IN_SECONDS );
	define( 'WEEK_IN_SECONDS', 7 * DAY_IN_SECONDS );
	define( 'MONTH_IN_SECONDS', 30 * DAY_IN_SECONDS );
	define( 'YEAR_IN_SECONDS', 365 * DAY_IN_SECONDS );
	define( 'EMPTY_TRASH_DAYS', 30 );
	define( 'ABSPATH', realpath( __FILE__ . '/../../../../' ) );
	define( 'WP_PLUGIN_DIR', realpath( __FILE__ . '/../' ) );
	define( 'WPINC', 'wp-includes' );
	define( 'OBJECT', 'OBJECT' );
	define( 'OBJECT_K', 'OBJECT_K' );
	define( 'ARRAY_A', 'ARRAY_A' );
	define( 'EP_PAGES', 4096 );
	define( 'EP_PERMALINK', 1 );
	define( 'COOKIEHASH', '' );
	define( 'COOKIE_DOMAIN', false );
	define( 'WP_IMPORTING', false );
	define( 'ICL_PLUGIN_INACTIVE', false );

	class FrmProFormState {
		public static function get_from_request( $key, $default ) {}
		/**
		 * @param string $key
		 * @param mixed  $value
		 *
		 * @return void
		 */
		public static function set_initial_value( $key, $value ) {
		}
	}

	class FrmProEntryShortcodeFormatter extends FrmEntryShortcodeFormatter {
	}
	class FrmProSettings extends FrmSettings {
	}
	class FrmProApplicationsHelper {
		public static function get_custom_applications_capability() {}
		/**
		 * @return string
		 */
		public static function get_required_templates_capability() {
		}
	}
	class FrmProFileImport {
		public static function import_attachment( $val, $field ) {
		}
	}
	class FrmProAppHelper {
		public static function get_settings() {
		}
		public static function convert_date( $date_str, $from_format, $to_format ) {
		}
		/**
		 * @return bool
		 */
		public static function views_is_installed() {
		}
		/**
		 * @return string
		 */
		public static function plugin_path() {
		}
		/**
		 * @return bool
		 */
		public static function use_chosen_js() {
		}
		/**
		 * @param array|string $selected
		 * @param string       $current
		 * @param bool         $echo
		 *
		 * @return string
		 */
		public static function selected( $selected, $current, $echo = true ) {
		}
		/**
		 * @return string
		 */
		public static function plugin_url() {
		}
		/**
		 * @return FrmProEddController
		 */
		public static function get_updater() {
		}
		public static function use_jquery_datepicker() {
		}
	}
	class FrmProEntryMetaHelper {
		public static function get_post_or_meta_value( $entry, $field, $atts = array() ) {
		}
		/**
		 * @param int|object|string $field_id
		 * @param array|string      $value
		 * @param false|int|string  $entry_id
		 *
		 * @return array|object|string|null
		 */
		public static function value_exists( $field_id, $value, $entry_id = false ) {
		}
		public static function get_post_value( $post_id, $post_field, $custom_field, $atts ) {
		}
	}
	class FrmProFormActionsController {
		/**
		 * @param WP_Post  $action
		 * @param stdClass $entry
		 *
		 * @return bool
		 */
		public static function action_conditions_met( $action, $entry ) {
		}
	}
	class FrmViewsLayout {
		/**
		 * @param int    $view_id
		 * @param string $listing_layout
		 * @param string $detail_layout
		 */
		public static function maybe_create_layouts_for_view( $view_id, $listing_layout, $detail_layout ) {
		}
		/**
		 * @param int          $view_id
		 * @param false|string $type
		 *
		 * @return array|false|object
		 */
		public static function get_layouts_for_view( $view_id, $type = false ) {
		}
	}
	class FrmProDisplaysHelper {
		public static function get_shortcodes( $content, $form_id ) {
		}
	}
	class FrmProAddonsController {
		/**
		 * @param bool $force_type
		 *
		 * @return string
		 */
		public static function license_type( $force_type = false ) {
		}
		/**
		 * @return bool|int
		 */
		public static function is_license_expiring() {
		}
		/**
		 * @param string       $plugin
		 * @param array|string $upgrade_link_args
		 *
		 * @return void
		 */
		public static function conditional_action_button( $plugin, $upgrade_link_args ) {
		}
		/**
		 * @param array $atts
		 *
		 * @return void
		 */
		public static function show_conditional_action_button( $atts ) {
		}
		/**
		 * @return bool
		 */
		public static function admin_banner() {
		}
		/**
		 * @return string
		 */
		public static function get_readable_license_type() {
		}
	}
	class FrmProCurrencyHelper {
		public static function normalize_formatted_numbers( $field, $formatted_value ) {}
	}
	class FrmProDb {
		/**
		 * @var string
		 */
		public static $plug_version;
	}
	class FrmProStylesController extends FrmStylesController {
		/**
		 * @param int $form_id
		 *
		 * @return WP_Post
		 */
		public static function get_active_style_for_form( $form_id ) {
		}
		/**
		 * @param stdClass|WP_Post $active_style
		 *
		 * @return array<WP_Post>
		 */
		public static function get_styles_for_styler( $active_style ) {
		}
		/**
		 * @return array<string>
		 */
		public static function get_notes_for_styler_preview() {
		}
		/**
		 * @return false|string
		 */
		public static function get_disabled_javascript_features() {
		}
	}
	class FrmProPost {
		/**
		 * @param array $field
		 * @param array $args
		 *
		 * @return string
		 */
		public static function get_category_dropdown( $field, $args ) {
		}
	}
	class FrmProEntriesController {
		public static function show_entry_shortcode( $atts ) {
		}
		/**
		 * @param array $atts
		 *
		 * @return string
		 */
		public static function entry_delete_link( $atts ) {
		}
		/**
		 * @param string   $method
		 * @param stdClass $form
		 * @param int      $entry_id
		 * @param array    $args
		 * @param array    $form_options
		 */
		public static function confirmation( $method, $form, $form_options, $entry_id, $args = array() ) {
		}
		/**
		 * @param object $form
		 *
		 * @return bool
		 */
		public static function is_form_displayed_after_edit( $form ) {
		}
		/**
		 * @param object $entry
		 * @param array  $args
		 *
		 * @return void
		 */
		public static function show_front_end_form_with_entry( $entry, $args ) {
		}
	}
	class FrmProFormsHelper {
		public static function post_type( $form ) {
		}
		/**
		 * @return array
		 */
		public static function get_default_opts() {
		}
	}
	class FrmProEntry {
		/**
		 * @param array|false $values
		 * @param string      $location
		 *
		 * @return array
		 */
		public static function mod_other_vals( $values = false, $location = 'front' ) {
		}
	}
	class FrmProEntryFormatter extends FrmEntryFormatter {
	}
	class FrmProEntriesHelper {
		public static function get_search_str( $where_clause, $search_str, $form_id = 0, $fid = '' ) {
		}
		/**
		 * @param object           $field
		 * @param object           $entry
		 * @param array|int|string $field_value
		 *
		 * @return void
		 */
		public static function get_dynamic_list_values( $field, $entry, &$field_value ) {
		}

		/**
		 * @param object $entry
		 * @param object $field
		 * @param array  $atts
		 *
		 * @return string
		 */
		public static function prepare_child_display_value( $entry, $field, $atts ) {
		}
	}
	class FrmProFieldsHelper {
		/**
		 * @param array  $args
		 * @param string $value
		 *
		 * @return void
		 */
		public static function replace_non_standard_formidable_shortcodes( $args, &$value ) {
		}
		/**
		 * @param array|object $field
		 *
		 * @return bool
		 */
		public static function is_field_visible_to_user( $field ) {
		}
	}
	class FrmViewsAppHelper {
		/**
		 * @return string
		 */
		public static function plugin_version() {
		}
	}
	class FrmViewsDisplay {
		/**
		 * @param int $form_id
		 *
		 * @return array
		 */
		public static function get_display_ids_by_form( $form_id ) {
		}
	}
	class FrmProCreditCardsController {
		/**
		 * @param array  $field
		 * @param string $field_name
		 * @param array  $atts
		 *
		 * @return void
		 */
		public static function show_in_form( $field, $field_name, $atts ) {
		}
	}
	class FrmProAppController {
		/**
		 * @return bool
		 */
		public static function has_combo_js_file() {
		}
	}
	class Akismet {
		/**
		 * @param string $request
		 * @param string $path
		 * @param string $ip
		 *
		 * @return array
		 */
		public static function http_post( $request, $path, $ip = null ) {
		}
		public static function get_user_roles( $user_id ) {
		}
	}
	class PHPMailer {
		public function __construct( $exceptions = null ) {
		}
	}
	class FrmPaymentsController {
		/**
		 * @var string
		 */
		public static $db_opt_name = 'frm_pay_db_version';
		/**
		 * @param array $cols
		 *
		 * @return array
		 */
		public static function payment_columns( $cols = array() ) {
		}
	}
	class FrmProDashboardHelper {
		/**
		 * @return bool
		 */
		public static function should_display_videos() {
		}
		/**
		 * @param array $entries_template
		 *
		 * @return void
		 */
		public static function get_main_widget( $entries_template ) {
		}
		/**
		 * @param array $entries_template
		 *
		 * @return void
		 */
		public static function get_bottom_widget( $entries_template ) {
		}
		/**
		 * @param array $template
		 *
		 * @return void
		 */
		public static function load_license_management( $template ) {
		}
	}
	class FrmProEddController extends FrmAddon {
	}
	class FrmProFieldSettings {
	}
	function load_formidable_pro() {
	}
	/**
	 * @return WPMailSMTP\Core
	 */
	function wp_mail_smtp() {
	}
	/**
	 * @return bool
	 */
	function akismet_test_mode() {
	}

	/** WP Optimize plugin */
	class WP_Optimize {
		/**
		 * @return WPO_Page_Cache
		 */
		public function get_page_cache() {
		}
	}
	class WPO_Page_Cache {
		/**
		 * @return bool
		 */
		public function purge() {
		}
	}
	class FrmLog {
		/**
		 * @param array<string> $values values.
		 *
		 * @return void
		 */
		public function add( $values ) {
		}
	}
	/**
	 * @return WP_Optimize
	 */
	function WP_Optimize() {
	}

	/**
	 * Function from W3 Total cache.
	 *
	 * @param array|null $extras Extras.
	 *
	 * @return void
	 */
	function w3tc_flush_all( $extras = null ) {
	}
	class FrmTransListsController {
		/**
		 * @param array $columns
		 *
		 * @return array
		 */
		public static function payment_columns( $columns = array() ) {
		}
	}
	class FrmProSettingsController {
		/**
		 * @param string $count
		 *
		 * @return string
		 */
		public static function inbox_badge( $count ) {}
		/**
		 * @return void
		 */
		public static function add_currency_settings() {}
	}
	class FrmProAddressesController extends FrmProComboFieldsController {
		/**
		 * @param string $country
		 *
		 * @return string
		 */
		public static function get_country_code( $country ) {
		}
	}

	class FrmProComboFieldsController {
	}

	class FrmProEntryMeta {
		/**
		 * @param object $field
		 *
		 * @return bool
		 */
		public static function skip_required_validation( $field ) {
		}
	}

	class FrmProDashboardController {
		/**
		 * @return array
		 */
		public static function get_counters() {
		}
	}

	class FrmProFormsController{
		public static function enqueue_pro_web_components_script(){
		}
	}

	/**
	 * This class is in the PayPal add-on.
	 */
	class FrmPaymentSettingsController {
		/**
		 * @return void
		 */
		public static function route() {

		}
	}

	/**
	 * DeepSource's PHP analyzer excludes the vendor directory from its scan (see the
	 * exclude_patterns in .deepsource.toml), so it never sees PHPUnit\Framework\TestCase's real
	 * methods even though this class extends it - that extends clause only helps PHPStan, which
	 * does load vendor/. Every PHPUnit method the plugin's tests actually call is therefore
	 * re-declared concretely below, with a real (if simplified) body: an empty body would trip
	 * DeepSource's PHP-W1080, and an unused parameter would trip PHP-W1037, on every one of these.
	 */
	class WP_UnitTestCase_Base extends PHPUnit\Framework\TestCase {
		/**
		 * FrmUnitTest::setUp() replaces this with a FrmUnitTestFactory, which is what every
		 * plugin test actually sees and calls ->field/->entry/->form on, so it is typed as that
		 * rather than the base WP_UnitTest_Factory - DeepSource and mago both scan tests/ and
		 * resolve FrmUnitTestFactory fine. Redeclaring ->field/->entry/->form directly on
		 * WP_UnitTest_Factory instead was tried and reverted: mago requires a redeclared
		 * property's docblock type to match its parent exactly, and that broke the REAL
		 * FrmUnitTestFactory in tests/phpunit/base/frm_factory.php, which types them more
		 * specifically (Field_Factory|null, etc). PHPStan can't load FrmUnitTestFactory itself,
		 * since phpstan.neon excludes tests/ from its analysis paths - that single-line ignore
		 * is scoped here rather than added to phpstan.neon.
		 *
		 * @var FrmUnitTestFactory
		 */
		// @phpstan-ignore class.notFound
		protected $factory;

		/**
		 * Real PHPUnit\Framework\TestCase declares every assertion method static, so an override
		 * has to match that or PHP fatals with "Cannot make static method ... non static".
		 *
		 * @param bool   $passed
		 * @param string $message
		 */
		protected static function stub_check( $passed, $message = '' ) {
			if ( ! $passed ) {
				throw new Exception( $message );
			}
		}

		/**
		 * The parent parameter is array|ArrayAccess. Any concrete spelling of that PHPStan can
		 * check - including a fully generic ArrayAccess<mixed,mixed> - reads as narrower than the
		 * parent's bare, unparameterized ArrayAccess and trips the contravariance rule, so this is
		 * typed mixed: the widest possible type, trivially at least as wide as the parent's.
		 *
		 * @param mixed $key
		 * @param mixed $array
		 */
		public static function assertArrayHasKey( $key, $array, string $message = '' ): void {
			self::stub_check( is_array( $array ) && array_key_exists( $key, $array ), $message );
		}

		/**
		 * @param mixed $key
		 * @param mixed $array
		 */
		public static function assertArrayNotHasKey( $key, $array, string $message = '' ): void {
			self::stub_check( ! ( is_array( $array ) && array_key_exists( $key, $array ) ), $message );
		}

		public static function assertContains( $needle, iterable $haystack, string $message = '' ): void {
			self::stub_check( in_array( $needle, is_array( $haystack ) ? $haystack : iterator_to_array( $haystack ), true ), $message );
		}

		public static function assertNotContains( $needle, iterable $haystack, string $message = '' ): void {
			self::stub_check( ! in_array( $needle, is_array( $haystack ) ? $haystack : iterator_to_array( $haystack ), true ), $message );
		}

		public static function assertCount( int $expected_count, $haystack, string $message = '' ): void {
			self::stub_check( is_countable( $haystack ) && count( $haystack ) === $expected_count, $message );
		}

		public static function assertEmpty( $actual, string $message = '' ): void {
			self::stub_check( empty( $actual ), $message );
		}

		public static function assertNotEmpty( $actual, string $message = '' ): void {
			self::stub_check( ! empty( $actual ), $message );
		}

		public static function assertEquals( $expected, $actual, string $message = '' ): void {
			self::stub_check( $expected == $actual, $message ); // phpcs:ignore Universal.Operators.StrictComparisons
		}

		public static function assertTrue( $condition, string $message = '' ): void {
			self::stub_check( $condition === true, $message );
		}

		public static function assertFalse( $condition, string $message = '' ): void {
			self::stub_check( $condition === false, $message );
		}

		public static function assertNotFalse( $condition, string $message = '' ): void {
			self::stub_check( $condition !== false, $message );
		}

		public static function assertFileExists( string $filename, string $message = '' ): void {
			self::stub_check( file_exists( $filename ), $message );
		}

		public static function assertGreaterThan( $expected, $actual, string $message = '' ): void {
			self::stub_check( $actual > $expected, $message );
		}

		public static function assertGreaterThanOrEqual( $expected, $actual, string $message = '' ): void {
			self::stub_check( $actual >= $expected, $message );
		}

		public static function assertLessThan( $expected, $actual, string $message = '' ): void {
			self::stub_check( $actual < $expected, $message );
		}

		public static function assertLessThanOrEqual( $expected, $actual, string $message = '' ): void {
			self::stub_check( $actual <= $expected, $message );
		}

		public static function assertInstanceOf( string $expected, $actual, string $message = '' ): void {
			self::stub_check( $actual instanceof $expected, $message );
		}

		public static function assertNotInstanceOf( string $expected, $actual, string $message = '' ): void {
			self::stub_check( ! ( $actual instanceof $expected ), $message );
		}

		public static function assertIsArray( $actual, string $message = '' ): void {
			self::stub_check( is_array( $actual ), $message );
		}

		public static function assertIsBool( $actual, string $message = '' ): void {
			self::stub_check( is_bool( $actual ), $message );
		}

		public static function assertIsObject( $actual, string $message = '' ): void {
			self::stub_check( is_object( $actual ), $message );
		}

		public static function assertIsString( $actual, string $message = '' ): void {
			self::stub_check( is_string( $actual ), $message );
		}

		public static function assertIsNumeric( $actual, string $message = '' ): void {
			self::stub_check( is_numeric( $actual ), $message );
		}

		public static function assertIsNotNumeric( $actual, string $message = '' ): void {
			self::stub_check( ! is_numeric( $actual ), $message );
		}

		public static function assertNotNull( $actual, string $message = '' ): void {
			self::stub_check( $actual !== null, $message );
		}

		public static function assertNull( $actual, string $message = '' ): void {
			self::stub_check( $actual === null, $message );
		}

		public static function assertSame( $expected, $actual, string $message = '' ): void {
			self::stub_check( $expected === $actual, $message );
		}

		public static function assertNotSame( $expected, $actual, string $message = '' ): void {
			self::stub_check( $expected !== $actual, $message );
		}

		/**
		 * assertObjectNotHasProperty is deliberately not overridden here: PHPUnit declares it
		 * final, so any override at all is a fatal "Cannot override final method" - not just a
		 * signature mismatch. It is only used in test_FrmEntry.php, which this stub rewrite does
		 * not need to cover.
		 */

		public static function assertStringContainsString( string $needle, string $haystack, string $message = '' ): void {
			self::stub_check( strpos( $haystack, $needle ) !== false, $message );
		}

		public static function assertStringNotContainsString( string $needle, string $haystack, string $message = '' ): void {
			self::stub_check( strpos( $haystack, $needle ) === false, $message );
		}

		public static function assertStringStartsWith( string $prefix, string $string, string $message = '' ): void {
			self::stub_check( strncmp( $string, $prefix, strlen( $prefix ) ) === 0, $message );
		}

		public static function fail( string $message = '' ): void {
			throw new Exception( $message );
		}

		public static function markTestSkipped( string $message = '' ): void {
			throw new Exception( $message );
		}

		/**
		 * Real WP_UnitTestCase_Base declares this one an instance method, not static.
		 */
		public function go_to( $url ) {
			self::stub_check( is_string( $url ) );
		}

		/**
		 * Real WP_UnitTestCase_Base declares this one an instance method, not static.
		 */
		public function clean_up_global_scope() {
			self::stub_check( true );
		}
	}

	class WP_UnitTestCase extends WP_UnitTestCase_Base {
	}

	class WP_UnitTest_Factory {
		/**
		 * @var WP_UnitTest_Factory_For_Post
		 */
		public $post;

		/**
		 * @var WP_UnitTest_Factory_For_Attachment
		 */
		public $attachment;

		/**
		 * @var WP_UnitTest_Factory_For_Comment
		 */
		public $comment;

		/**
		 * @var WP_UnitTest_Factory_For_User
		 */
		public $user;

		/**
		 * @var WP_UnitTest_Factory_For_Term
		 */
		public $term;

		/**
		 * @var WP_UnitTest_Factory_For_Term
		 */
		public $category;

		/**
		 * @var WP_UnitTest_Factory_For_Term
		 */
		public $tag;

		/**
		 * @var WP_UnitTest_Factory_For_Bookmark
		 */
		public $bookmark;

		/**
		 * @var WP_UnitTest_Factory_For_Blog
		 */
		public $blog;

		/**
		 * @var WP_UnitTest_Factory_For_Network
		 */
		public $network;
	}

	/**
	 * The leaf *_For_* classes below are deliberately left abstract with no override of
	 * create_object()/update_object()/get_object_by_id(): they exist only so property access
	 * like $factory->post resolves to a type that inherits create()/create_and_get(), and an
	 * abstract class is never instantiated from this file, so leaving them unimplemented is
	 * fine for static analysis and avoids stubbing empty method bodies DeepSource flags as
	 * PHP-W1080 (no body) with unused-parameter findings on top.
	 */
	abstract class WP_UnitTest_Factory_For_Thing {
		public $default_generation_definitions;
		public $factory;

		public function __construct( $factory, $default_generation_definitions = array() ) {
			$this->factory                        = $factory;
			$this->default_generation_definitions = $default_generation_definitions;
		}

		abstract public function create_object( $args );
		abstract public function update_object( $object_id, $fields );
		abstract public function get_object_by_id( $object_id );

		public function create( $args = array(), $generation_definitions = null ) {
			if ( $generation_definitions === null ) {
				$generation_definitions = $this->default_generation_definitions;
			}

			return $this->create_object( array_merge( (array) $generation_definitions, $args ) );
		}

		public function create_and_get( $args = array(), $generation_definitions = null ) {
			return $this->get_object_by_id( $this->create( $args, $generation_definitions ) );
		}

		public function create_many( $count, $args = array(), $generation_definitions = null ) {
			return array_fill( 0, $count, $this->create( $args, $generation_definitions ) );
		}
	}

	abstract class WP_UnitTest_Factory_For_Post extends WP_UnitTest_Factory_For_Thing {
	}

	abstract class WP_UnitTest_Factory_For_Attachment extends WP_UnitTest_Factory_For_Post {
	}

	abstract class WP_UnitTest_Factory_For_Comment extends WP_UnitTest_Factory_For_Thing {
	}

	abstract class WP_UnitTest_Factory_For_User extends WP_UnitTest_Factory_For_Thing {
	}

	abstract class WP_UnitTest_Factory_For_Term extends WP_UnitTest_Factory_For_Thing {
	}

	abstract class WP_UnitTest_Factory_For_Bookmark extends WP_UnitTest_Factory_For_Thing {
	}

	abstract class WP_UnitTest_Factory_For_Blog extends WP_UnitTest_Factory_For_Thing {
	}

	abstract class WP_UnitTest_Factory_For_Network extends WP_UnitTest_Factory_For_Thing {
	}

	/**
	 * frm_factory.php uses this to generate unique default values (field names, entry names).
	 * mago.toml's [source] paths includes "tests" directly - unlike PHPStan, mago analyzes that
	 * file itself and needs this class to exist, not just its name.
	 */
	class WP_UnitTest_Generator_Sequence {
		public static $incr = -1;
		public $next;
		public $template_string;

		public function __construct( $template_string = '%s', $start = null ) {
		}

		public function next() {
		}

		public function get_incr() {
		}

		public function get_template_string() {
		}
	}

	/**
	 * frm_factory.php uses this to generate a random entry value, for the same reason as
	 * WP_UnitTest_Generator_Sequence above.
	 */
	function rand_str( $length = 32 ) {
	}
}

namespace Elementor {
	abstract class Widget_Base {
		public function start_controls_section( $section_id, array $args = array() ) {
		}
		public function add_control( $id, array $args, $options = array() ) {
		}
		public function end_controls_section() {
		}
		public function get_settings_for_display( $setting_key = null ) {
		}
	}

	class Plugin {
		/**
		 * @return Plugin
		 */
		public static function instance() {
		}
	}

	class Controls_Manager {
		const TAB_CONTENT = 'content';
		const SELECT2     = 'select2';
		const SWITCHER    = 'switcher';
	}
}

namespace WPMailSMTP {
	class Options {
	   /**
	    * @return Options
	    */
	   public static function init() {
	   }
		/**
		 * @param string $group
		 * @param string $key
		 * @param bool   $strip_slashes
		 *
		 * @return mixed|null
		 */
		public function get( $group, $key, $strip_slashes = true ) {
		}
	}
	class Core {
		/**
		 * @return Providers\Loader
		 */
		public function get_providers() {
		}
	}
}

namespace WPMailSMTP\Providers {
	interface MailerInterface {
		/**
		 * @return bool
		 */
		public function is_mailer_complete();
	}
	abstract class MailerAbstract implements MailerInterface {
	}
	class Loader {
		/**
		 * @param string               $provider  The provider name.
		 * @param MailCatcherInterface $phpmailer The MailCatcher object.
		 *
		 * @return MailerAbstract|null
		 */
		public function get_mailer( $provider, $phpmailer ) {
		}
	}
}
