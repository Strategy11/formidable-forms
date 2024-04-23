<?php

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;

define( 'ABSPATH', '' );

return RectorConfig::configure()
	->withPaths([
		__DIR__ . '/classes',
		__DIR__ . '/stripe',
	])
    // register single rule
    ->withRules([
        TypedPropertyFromStrictConstructorRector::class
    ])
    // here we can define, what prepared sets of rules will be applied
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true
    );
