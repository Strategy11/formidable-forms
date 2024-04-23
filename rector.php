<?php

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Php54\Rector\Array_\LongArrayToShortArrayRector;
use Rector\Php53\Rector\Ternary\TernaryToElvisRector;
use Rector\Php56\Rector\FuncCall\PowToExpRector;
use Rector\Php55\Rector\Class_\ClassConstantToSelfClassRector;
use Rector\Php70\Rector\StmtsAwareInterface\IfIssetToCoalescingRector;
use Rector\CodeQuality\Rector\If_\ConsecutiveNullCompareReturnsToNullCoalesceQueueRector;
use Rector\Php70\Rector\Ternary\TernaryToNullCoalescingRector;
use Rector\Php70\Rector\FuncCall\RandomFunctionRector;
use Rector\Php70\Rector\FuncCall\MultiDirnameRector;

define( 'ABSPATH', '' );

return RectorConfig::configure()
	->withPaths([
		__DIR__ . '/classes',
		__DIR__ . '/stripe',
	])
    // register single rule
    ->withSets([
        LevelSetList::UP_TO_PHP_70,
    ])
    ->withSkip([
        // This rule changes array() to [].
        LongArrayToShortArrayRector::class,
        // This rule changes ternaries to elvis.
        TernaryToElvisRector::class,
        // This rule changes pow() to **.
        PowToExpRector::class,
        // This rule changes __CLASS__ to self::class
        ClassConstantToSelfClassRector::class,
        // This rule changes isset checks to ??.
        IfIssetToCoalescingRector::class,
        ConsecutiveNullCompareReturnsToNullCoalesceQueueRector::class,
        TernaryToNullCoalescingRector::class,
        // This changes rand() to random_int().
        RandomFunctionRector::class,
        MultiDirnameRector::class,
    ]);
