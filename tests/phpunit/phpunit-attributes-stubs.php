<?php

declare(strict_types=1);

/**
 * PHPUnit declarations for analysis jobs that do not install PHPUnit.
 */
namespace PHPUnit\Framework\Attributes {

	#[\Attribute( \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE )]
	class Group {

		/**
		 * @param string $name The group name.
		 */
		public function __construct( $name ) {
		}
	}

	#[\Attribute( \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE )]
	class DataProvider {

		/**
		 * @param string $methodName The static provider method name.
		 */
		public function __construct( $methodName ) {
		}
	}

	#[\Attribute( \Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE )]
	class CoversClass {

		/**
		 * @param class-string $className The covered class name.
		 */
		public function __construct( $className ) {
		}
	}

	/**
	 * Marks tests whose target is outside the code coverage source directories.
	 */
	#[\Attribute( \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD )]
	class CoversNothing {
	}

	/**
	 * Runs a test, or every test in a class, in its own PHP process.
	 */
	#[\Attribute( \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD )]
	class RunInSeparateProcess {
	}

	#[\Attribute( \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD )]
	class PreserveGlobalState {

		/**
		 * @param bool $enabled Whether the parent process globals are copied into the child process.
		 */
		public function __construct( $enabled ) {
		}
	}
}

namespace PHPUnit\Framework {

	/**
	 * Thrown when an assertion fails.
	 */
	class ExpectationFailedException extends \Exception {
	}
}
