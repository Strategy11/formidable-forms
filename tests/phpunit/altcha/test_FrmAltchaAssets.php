<?php
/**
 * Widget asset selection and translation dependencies.
 *
 * @package Formidable
 */

/**
 * @group altcha
 */
class test_FrmAltchaAssets extends WP_UnitTestCase {

	/**
	 * Language selected for the current request.
	 *
	 * @var string
	 */
	private $language = '';

	/**
	 * Override the widget language for an asset request.
	 *
	 * @return string
	 */
	public function filter_language() {
		return $this->language;
	}

	/**
	 * The widget loads only the selected translation, before the main bundle.
	 *
	 * @dataProvider translation_cases
	 *
	 * @param string $language    Selected widget language.
	 * @param string $translation Expected translation filename, or empty for English.
	 */
	public function test_translation_dependency( $language, $translation ) {
		global $wp_scripts;

		$previous_scripts = $wp_scripts;
		$wp_scripts       = null;
		$this->language   = $language;
		add_filter( 'frm_altcha_language', array( $this, 'filter_language' ) );

		try {
			wp_register_script( 'formidable', false );
			$url = FrmAltchaController::api_url();

			// Lite registers the same handle after the URL filter has run.
			wp_register_script( 'captcha-api', $url, array( 'formidable' ), '3', true );
			wp_enqueue_script( 'captcha-api' );

			$scripts      = wp_scripts();
			$dependencies = array( 'formidable' );

			if ( '' !== $translation ) {
				$handle         = 'frm-altcha-language-' . $translation;
				$dependencies[] = $handle;
				$this->assertStringContainsString( '/js/altcha/i18n/' . $translation . '.js', $scripts->registered[ $handle ]->src );
			}

			$this->assertSame( $dependencies, $scripts->registered['captcha-api']->deps );
			$this->assertStringContainsString( '/js/altcha.js', $url );
			$this->assertTrue( $scripts->all_deps( array( 'captcha-api' ) ) );

			if ( '' !== $translation ) {
				$this->assertLessThan( array_search( 'captcha-api', $scripts->to_do, true ), array_search( $handle, $scripts->to_do, true ) );
			}
		} finally {
			remove_filter( 'frm_altcha_language', array( $this, 'filter_language' ) );
			$wp_scripts = $previous_scripts;
		}
	}

	/**
	 * @return array<string, array{string, string}>
	 */
	public static function translation_cases() {
		return array(
			'English'           => array( 'en-US', '' ),
			'German region'     => array( 'de-CH', 'de' ),
			'French region'     => array( 'fr_FR', 'fr-fr' ),
			'French base'       => array( 'fr', 'fr-ca' ),
			'Portuguese base'   => array( 'pt', 'pt-pt' ),
			'Portuguese region' => array( 'pt-BR', 'pt-br' ),
			'Spanish base'      => array( 'es', 'es-es' ),
			'Chinese region'    => array( 'zh-SG', 'zh-cn' ),
			'Unknown language'  => array( 'xx-YY', '' ),
			'Invalid language'  => array( '../../de', '' ),
		);
	}
}
