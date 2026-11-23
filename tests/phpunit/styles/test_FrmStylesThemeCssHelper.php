<?php

/**
 * @group styles
 *
 * @covers FrmStylesThemeCssHelper
 */
#[\PHPUnit\Framework\Attributes\Group( 'styles' )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmStylesThemeCssHelper::class )]
class test_FrmStylesThemeCssHelper extends FrmUnitTest {

	/**
	 * A quoted data URI that holds parentheses, followed by a rule that must survive it.
	 *
	 * @var string
	 */
	private static $svg_css = '.a{background:url("data:image/svg+xml,%3Csvg fill=\'rgb(0,0,0)\'%3E")}.b{color:red}';

	/**
	 * @dataProvider selector_list_provider
	 *
	 * @param string $selector_list A selector list as a theme would write it.
	 * @param string $expected      The selector list for the scoped preview. Empty when the rule is dropped.
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'selector_list_provider' )]
	public function test_rewrite_selector_list( $selector_list, $expected ) {
		$this->assertSame( $expected, $this->run_private_method( array( 'FrmStylesThemeCssHelper', 'rewrite_selector_list' ), array( $selector_list ) ) );
	}

	/**
	 * @return array
	 */
	public static function selector_list_provider() {
		return array(
			'body'                         => array( 'body', ':scope' ),
			'html'                         => array( 'html', ':scope' ),
			'root'                         => array( ':root', ':scope' ),
			'html and body collapse'       => array( 'html, body', ':scope' ),
			'body descendant'              => array( 'body .entry-content', ':scope .entry-content' ),
			'html body descendant'         => array( 'html body p', ':scope p' ),
			'child combinators'            => array( 'html > body > p', ':scope p' ),
			'body pseudo element'          => array( 'body::before', ':scope::before' ),
			'root with where'              => array( ':root :where(a)', ':scope :where(a)' ),
			'condition later in selector'  => array( 'body p:not(.x)', ':scope p:not(.x)' ),
			'no root selector'             => array( '.a, .b[data-x="1,2"]', '.a, .b[data-x="1,2"]' ),
			'body is not the first part'   => array( '.x body', '.x body' ),
			'custom element named html-el' => array( 'html-el p', 'html-el p' ),
			'html class is dropped'        => array( 'html.dark-mode .btn', '' ),
			'body class is dropped'        => array( 'body.home .x', '' ),
			'body class alone is dropped'  => array( 'body.custom-bg', '' ),
			'html not() is dropped'        => array( 'html:not(.no-js) p', '' ),
			'body attribute is dropped'    => array( 'body[data-theme="dark"] p', '' ),
			'body id is dropped'           => array( 'body#top p', '' ),
			'html then body class dropped' => array( 'html body.dark p', '' ),
			'only conditional part drops'  => array( '.a, body.dark .b, body .c', '.a, :scope .c' ),
		);
	}

	/**
	 * @dataProvider url_provider
	 *
	 * @param string $css      CSS as a theme would write it.
	 * @param string $expected The CSS with relative URLs resolved against https://example.com/theme/.
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'url_provider' )]
	public function test_absolutize_urls( $css, $expected ) {
		$this->assertSame( $expected, $this->run_private_method( array( 'FrmStylesThemeCssHelper', 'absolutize_urls' ), array( $css, 'https://example.com/theme/' ) ) );
	}

	/**
	 * @return array
	 */
	public static function url_provider() {
		return array(
			'relative'                  => array( '.a{background:url(img/a.png)}', '.a{background:url(https://example.com/theme/img/a.png)}' ),
			'relative with quotes'      => array( '.a{background:url( "img/a.png" )}', '.a{background:url("https://example.com/theme/img/a.png")}' ),
			'quoted with parentheses'   => array( ".a{background:url('img/a (1).png')}", ".a{background:url('https://example.com/theme/img/a (1).png')}" ),
			'data URI with parentheses' => array( self::$svg_css, self::$svg_css ),
			'unquoted data URI'         => array( '.a{background:url(data:image/png;base64,AAAA)}', '.a{background:url(data:image/png;base64,AAAA)}' ),
			'absolute'                  => array( '.a{background:url(https://cdn.example.com/a.png)}', '.a{background:url(https://cdn.example.com/a.png)}' ),
			'protocol relative'         => array( '.a{background:url(//cdn.example.com/a.png)}', '.a{background:url(//cdn.example.com/a.png)}' ),
			'root relative'             => array( '.a{background:url(/a.png)}', '.a{background:url(/a.png)}' ),
			'fragment'                  => array( '.a{filter:url(#blur)}', '.a{filter:url(#blur)}' ),
		);
	}

	/**
	 * Removing a discovered stylesheet changes the preview version even if the theme version is unchanged.
	 */
	public function test_removing_a_discovered_stylesheet_changes_the_preview_version() {
		$path   = wp_tempnam( 'frm-theme-preview.css' );
		$key    = $this->run_private_method( array( 'FrmStylesThemeCssHelper', 'get_stylesheet_paths_cache_key' ) );
		$filter = static function () use ( $path ) {
			return array(
				array(
					'path'     => $path,
					'base_url' => 'https://example.com/theme/',
				),
			);
		};

		add_filter( 'frm_preview_theme_stylesheets', $filter );
		try {
			FrmStylesThemeCssHelper::get_scoped_theme_css( '#preview' );
			$version = FrmStylesThemeCssHelper::get_version();
			wp_delete_file( $path );
			clearstatcache( true, $path );

			$this->assertNotSame( $version, FrmStylesThemeCssHelper::get_version() );
		} finally {
			remove_filter( 'frm_preview_theme_stylesheets', $filter );
			delete_transient( $key );
			wp_delete_file( $path );
		}
	}

	public function test_scope_css_drops_conditional_root_rules() {
		$css    = 'body{color:#111}body.dark-mode{background:#000;color:#fff}@media (min-width:600px){html.js .x{color:red}.y{color:blue}}';
		$scoped = $this->run_private_method( array( 'FrmStylesThemeCssHelper', 'scope_css' ), array( $css, '#preview' ) );

		$this->assertStringContainsString( ':scope{color:#111}', $scoped );
		$this->assertStringContainsString( '.y{color:blue}', $scoped );
		$this->assertStringNotContainsString( '#fff', $scoped, 'A rule for a body class must not apply to every preview.' );
		$this->assertStringNotContainsString( 'color:red', $scoped, 'A rule for an html class must not apply to every preview.' );
	}

	public function test_scope_css_hoists_font_faces_and_keeps_root_font_size() {
		$css    = '@import "other.css";@font-face{font-family:x;src:url(x.woff)}html{font-size:62.5%}body{background:#fff;margin:0 1rem}';
		$scoped = $this->run_private_method( array( 'FrmStylesThemeCssHelper', 'scope_css' ), array( $css, '#preview' ) );

		$this->assertStringStartsWith( '@font-face {font-family:x;src:url(x.woff)}', $scoped, 'A font face has to stay outside of the scope block.' );
		$this->assertStringContainsString( '@scope (#preview) {', $scoped );
		$this->assertStringNotContainsString( '@import', $scoped );
		$this->assertStringContainsString( ':scope{font-size:10px;' . FrmStylesThemeCssHelper::REM_PROPERTY . ':10px}', $scoped );
		$this->assertStringContainsString( ':scope{margin:0 calc(1 * var(' . FrmStylesThemeCssHelper::REM_PROPERTY . '))}', $scoped, 'The page background is removed.' );
	}
}
