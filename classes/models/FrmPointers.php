<?php

/**
 * This class handles the pointers used in the introduction tour.
 */
class FrmPointers {

	/**
	 * @var object Instance of this class
	 */
	public static $instance;

	/**
	 * @var array Holds the buttons to be put out
	 */
	private $button_array;

	/**
	 * @var array Holds the admin pages we have pointers for and the callback that generates the pointers content
	 */
	private $admin_pages = array(
		'' => 'forms_pointer',
		'entries'   => 'entries_pointer',
		'styles'    => 'styles_pointer',
		'import'    => 'import_pointer',
		'settings'  => 'settings_pointer',
		'addons'    => 'addons_pointer',
	);

	/**
	 * Class constructor.
	 */
	private function __construct() {
		if ( current_user_can( 'manage_options' ) ) {

			if ( ! get_user_meta( get_current_user_id(), 'frm_ignore_tour' ) ) {
				wp_enqueue_style( 'wp-pointer' );
				wp_enqueue_script( 'jquery-ui' );
				wp_enqueue_script( 'wp-pointer' );
				add_action( 'admin_print_footer_scripts', array( $this, 'intro_tour' ) );
			}
		}
	}

	/**
	 * Get the singleton instance of this class
	 *
	 * @return object
	 */
	public static function get_instance() {
		if ( ! ( self::$instance instanceof self ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Load the introduction tour
	 */
	public function intro_tour() {
		global $pagenow;

		$page = preg_replace( '/^(formidable[-]?)/', '', filter_input( INPUT_GET, 'page' ) );

		if ( 'admin.php' === $pagenow && array_key_exists( $page, $this->admin_pages ) ) {
			$this->do_page_pointer( $page );
		} else {
			$this->start_tour_pointer();
		}
	}

	/**
	 * Prints the pointer script
	 *
	 * @param string $selector The CSS selector the pointer is attached to.
	 * @param array  $options  The options for the pointer.
	 */
	public function print_scripts( $selector, $options ) {
		// Button1 is the close button, which always exists.
		$default_button = array(
			'text'     => false,
			'function' => '',
		);
		$button_array_defaults = array(
			'button2' => $default_button,
			'button3' => $default_button,
		);
		$this->button_array = wp_parse_args( $this->button_array, $button_array_defaults );
		?>
		<script type="text/javascript">
			//<![CDATA[
			(function ($) {
				// Don't show the tour on screens with an effective width smaller than 1024px or an effective height smaller than 768px.
				if (jQuery(window).width() < 1024 || jQuery(window).availWidth < 1024) {
					return;
				}

				var frm_pointer_options = <?php echo json_encode( $options ); ?>, setup;

				frm_pointer_options = $.extend(frm_pointer_options, {
					buttons: function (event, t) {
						var button = jQuery('<a href="<?php echo esc_url( $this->get_ignore_url() ); ?>" id="pointer-close" style="margin:0 5px;" class="button-secondary">' + '<?php _e( 'Close', 'formidable' ) ?>' + '</a>');
						button.bind('click.pointer', function () {
							t.element.pointer('close');
						});
						return button;
					},
					close: function () {
					}
				});

				setup = function () {
					$('<?php echo esc_attr( $selector ); ?>').pointer(frm_pointer_options).pointer('open');
					var lastOpenedPointer = jQuery( '.wp-pointer').slice( -1 );
					<?php
					$this->button2();
					$this->button3();
					?>
				};

				if (frm_pointer_options.position && frm_pointer_options.position.defer_loading)
					$(window).bind('load.wp-pointers', setup);
				else
					$(document).ready(setup);
			})(jQuery);
			//]]>
		</script>
	<?php
	}

	/**
	 * Render button 2, if needed
	 */
	private function button2() {
		if ( $this->button_array['button2']['text'] ) {
			?>
			lastOpenedPointer.find( '#pointer-close' ).after('<a id="pointer-primary" class="button-primary">' +
				'<?php echo esc_attr( $this->button_array['button2']['text'] ); ?>' + '</a>');
			lastOpenedPointer.find('#pointer-primary').click(function () {
			<?php echo $this->button_array['button2']['function']; ?>
			});
		<?php
		}
	}

	/**
	 * Render button 3, if needed. This is the previous button in most cases
	 */
	private function button3() {
		if ( $this->button_array['button3']['text'] ) {
			?>
			lastOpenedPointer.find('#pointer-primary').after('<a id="pointer-ternary" style="float: left;" class="button-secondary">' +
				'<?php echo esc_attr( $this->button_array['button3']['text'] ); ?>' + '</a>');
			lastOpenedPointer.find('#pointer-ternary').click(function () {
			<?php echo $this->button_array['button3']['function']; ?>
			});
		<?php }
	}

	/**
	 * Show a pointer that starts the tour
	 */
	private function start_tour_pointer() {
		$selector = 'li.toplevel_page_formidable';

		$content  = '<h3>' . __( 'Congratulations!', 'formidable' ) . '</h3>'
		            .'<p>' . $this->opening_line() . ' ' . sprintf( __( 'Click &#8220;Start Tour&#8221; to view a quick introduction of this plugin&#8217;s core functionality.' ), 'formidable' ) . '</p>';
		$opt_arr  = array(
			'content'  => $content,
			'position' => array( 'edge' => 'top', 'align' => 'center' ),
		);

		$this->button_array['button2']['text']     = __( 'Start Tour', 'formidable' );
		$this->button_array['button2']['function'] = sprintf( 'document.location="%s";', admin_url( 'admin.php?page=formidable' ) );

		$this->print_scripts( $selector, $opt_arr );
	}

	private function opening_line() {
		$opening = __( 'You&#8217;ve just installed a new form builder plugin!', 'formidable' );
		$affiliate = FrmAppHelper::get_affiliate();
		if ( $affiliate == 'mojo' ) {
			$opening = 'Your Forms plugin has been installed by MOJO Marketplace for your convenience.';
		}
		return $opening;
	}

	/**
	 * Shows a pointer on the proper pages
	 *
	 * @param string $page Admin page key.
	 */
	private function do_page_pointer( $page ) {
		$pointer = call_user_func( array( $this, $this->admin_pages[ $page ] ) );

		$opt_arr = array(
			'content'      => $pointer['content'],
			'position'     => array(
				'edge'  => 'top',
				'align' => ( is_rtl() ) ? 'right' : 'left',
			),
			'pointerWidth' => 450,
		);

		$selector = 'h2';
		if ( isset( $pointer['selector'] ) ) {
			$selector = $pointer['selector'];
		}

		if ( isset( $pointer['position'] ) ) {
			$opt_arr['position'] = $pointer['position'];
		}

		if ( isset( $pointer['next_page'] ) ) {
			if ( ! empty( $pointer['next_page'] ) ) {
				$pointer['next_page'] = '-' . $pointer['next_page'];
			}
			$this->button_array['button2'] = array(
				'text'     => __( 'Next', 'formidable' ),
				'function' => 'window.location="' . esc_url_raw( admin_url( 'admin.php?page=formidable' . $pointer['next_page'] ) ) . '";',
			);
		}
		if ( isset( $pointer['prev_page'] ) ) {
			if ( ! empty( $pointer['prev_page'] ) ) {
				$pointer['prev_page'] = '-' . $pointer['prev_page'];
			}
			$this->button_array['button3'] = array(
				'text'     => __( 'Previous', 'formidable' ),
				'function' => 'window.location="' . esc_url_raw( admin_url( 'admin.php?page=formidable' . $pointer['prev_page'] ) ) . '";',
			);
		}
		$this->print_scripts( $selector, $opt_arr );
	}

	/**
	 * Returns the content of the Forms listing page pointer
	 *
	 * @return array
	 */
	private function forms_pointer() {
		global $current_user;

		return array(
			'content'   => '<h3>' . __( 'Forms', 'formidable' ) . '</h3>'
			               . '<p>' . __( 'All your forms will be listed on this page. Create your first form by clicking on the "Add New" button.', 'formidable' ) . '</p>'
			               . '<p><strong>' . __( 'Subscribe to our Newsletter', 'formidable' ) . '</strong><br/>'
			               . sprintf( __( 'If you would like to hear about new features and updates for %1$s, subscribe to our newsletter:', 'formidable' ), 'Formidable' ) . '</p>'
			               . '<form target="_blank" action="//formidablepro.us1.list-manage.com/subscribe/post?u=a4a913790ffb892daacc6f271&amp;id=7e7df15967" method="post" selector="newsletter-form" accept-charset="' . esc_attr( get_bloginfo( 'charset' ) ) . '">'
			               . '<p>'
			               . '<input style="margin: 5px; color:#666" name="EMAIL" value="' . esc_attr( $current_user->user_email ) . '" selector="newsletter-email" placeholder="' . esc_attr__( 'Email', 'formidable' ) . '"/>'
						   . '<input type="hidden" name="group[4505]" value="4" />'
			               . '<button type="submit" class="button-primary">' . esc_html__( 'Subscribe', 'formidable' ) . '</button>'
			               . '</p>'
			               . '</form>',
			'next_page' => 'entries',
		);
	}

	/**
	 * Returns the content of the Entries listing page pointer
	 *
	 * @return array
	 */
	private function entries_pointer() {
		return array(
			'content'   => '<h3>' . __( 'Entries', 'formidable' ) . '</h3>'
			               . '<p>' . __( 'Each time one of your forms is submitted, an entry is created. You will find every form submission listed here so you will always have a backup if an email fails.', 'formidable' ) . '</p>',
			'prev_page' => '',
			'next_page' => 'styles',
			'selector'  => '.wp-list-table',
			'position'  => array( 'edge' => 'bottom', 'align' => 'center' ),
		);
	}

	/**
	 * Returns the content of the Styles page pointer
	 *
	 * @return array
	 */
	private function styles_pointer() {
		return array(
			'content'   => '<h3>' . __( 'Styles', 'formidable' ) . '</h3>'
			               . '<p>' . __( 'Want to make changes to the way your forms look? Make all the changes you would like right here, and watch the sample form change before your eyes.', 'formidable' ) . '</p>',
			'prev_page' => 'entries',
			'next_page' => 'import',
			'selector'  => '.general-style',
			'position'  => array( 'edge' => 'left', 'align' => 'right' ),
		);
	}

	/**
	 * Returns the content of the Import/Export page pointer
	 *
	 * @return array
	 */
	private function import_pointer() {
		return array(
			'content'   => '<h3>' . __( 'Import/Export', 'formidable' ) . '</h3>'
			               . '<p>' . __( 'Import and export forms and styles when copying from one site to another or sharing with someone else. Your entries can be exported to a CSV as well. The Premium version also includes the option to import entries to your site from a CSV.', 'formidable' ) . '</p>',
			'prev_page' => 'styles',
			'next_page' => 'settings',
			'selector'  => '.inside.with_frm_style',
			'position'  => array( 'edge' => 'bottom', 'align' => 'top' ),
		);
	}

	/**
	 * Returns the content of the advanced page pointer
	 *
	 * @return array
	 */
	private function settings_pointer() {
		return array(
			'content'   => '<h3>' . __( 'Global Settings', 'formidable' ) . '</h3>'
				. '<p><strong>' . __( 'General', 'formidable' ) . '</strong><br/>'
				. __( 'Turn stylesheets and scripts off, set which user roles have access to change and create forms, setup your reCaptcha, and set default messages for new forms and fields.', 'formidable' )
				. '<p><strong>' . __( 'Plugin Licenses', 'formidable' ) . '</strong><br/>'
				. sprintf( __( 'Once you&#8217;ve purchased %1$s or any addons, you&#8217;ll have to enter a license key to get access to all of their powerful features. A Plugin Licenses tab will appear here for you to enter your license key.', 'formidable' ), 'Formidable Pro' )
           	    . '</p>',
			'prev_page' => 'import',
			'next_page' => 'addons',
		);
	}

	/**
	 * Returns the content of the extensions and licenses page pointer
	 *
	 * @return array
	 */
	private function addons_pointer() {
		return array(
			'content'   => '<h3>' . __( 'Addons', 'formidable' ) . '</h3>'
			               . '<p>' . sprintf( __( 'The powerful functions of %1$s can be extended with %2$spremium plugins%3$s. You can read all about the Formidable Premium Plugins %2$shere%3$s.', 'formidable' ), 'Formidable', '<a target="_blank" href="' . esc_url( FrmAppHelper::make_affiliate_url( 'https://formidablepro.com/' ) ) . '">', '</a>' )
						   . '</p>'
			               . '<p><strong>' . __( 'Like this plugin?', 'formidable' ) . '</strong><br/>' . sprintf( __( 'So, we&#8217;ve come to the end of the tour. If you like the plugin, please %srate it 5 stars on WordPress.org%s!', 'formidable' ), '<a target="_blank" href="https://wordpress.org/plugins/formidable/">', '</a>' ) . '</p>'
			               . '<p>' . sprintf( __( 'Thank you for using our plugin and good luck with your forms!<br/><br/>Best,<br/>Team Formidable - %1$sformidablepro.com%2$s', 'formidable' ), '<a target="_blank" href="' . esc_url( FrmAppHelper::make_affiliate_url( 'https://formidablepro.com/' ) ) . '">', '</a>' ) . '</p>',
			'prev_page' => 'settings',
		);
	}

	/**
	 * Extending the current page URL with two params to be able to ignore the tour.
	 *
	 * @return mixed
	 */
	private function get_ignore_url() {
		$arr_params = array(
			'frm_restart_tour' => false,
			'frm_ignore_tour'  => '1',
			'nonce'            => wp_create_nonce( 'frm-ignore-tour' ),
		);

		return add_query_arg( $arr_params );
	}
}
