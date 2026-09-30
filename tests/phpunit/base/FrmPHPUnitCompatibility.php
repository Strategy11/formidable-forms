<?php

/**
 * Adapt WordPress deprecation expectations to newer PHPUnit versions.
 *
 * WordPress still reads these expectations through an API removed in PHPUnit 10.
 * Keep its collectors and assertions while reading the two WordPress tags directly.
 */
trait FrmPHPUnitCompatibility {

	/**
	 * Register the WordPress deprecation and incorrect usage expectations.
	 *
	 * @return void
	 */
	protected function set_up_deprecation_expectations() {
		$class     = new ReflectionClass( $this );
		$docblocks = array( $class->getDocComment(), $class->getMethod( $this->name() )->getDocComment() );

		foreach ( $docblocks as $docblock ) {
			if ( ! is_string( $docblock ) ) {
				continue;
			}

			preg_match_all( '/@expected(Deprecated|IncorrectUsage)\s+([^\s*]+)/', $docblock, $matches, PREG_SET_ORDER );

			foreach ( $matches as $match ) {
				if ( 'Deprecated' === $match[1] ) {
					$this->setExpectedDeprecated( $match[2] );
				} else {
					$this->setExpectedIncorrectUsage( $match[2] );
				}
			}
		}

		foreach ( array( 'function', 'argument', 'class', 'file', 'hook' ) as $type ) {
			$hook = 'file' === $type ? 'deprecated_file_included' : 'deprecated_' . $type . '_run';
			add_action( $hook, array( $this, 'deprecated_function_run' ), 10, 'file' === $type ? 4 : 3 );
			add_filter( 'deprecated_' . $type . '_trigger_error', '__return_false' );
		}

		add_action( 'doing_it_wrong_run', array( $this, 'doing_it_wrong_run' ), 10, 3 );
		add_filter( 'doing_it_wrong_trigger_error', '__return_false' );
	}
}
