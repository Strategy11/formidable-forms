<?php

declare(strict_types=1);

namespace PHPUnit\Framework\Attributes;

/**
 * Attribute declarations for analysis jobs that do not install PHPUnit.
 */
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
