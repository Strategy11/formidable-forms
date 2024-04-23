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
use Rector\Php71\Rector\ClassConst\PublicConstantVisibilityRector;
use Rector\Php71\Rector\List_\ListToArrayDestructRector;
use Rector\Php73\Rector\FuncCall\ArrayKeyFirstLastRector;
use Rector\Php73\Rector\FuncCall\StringifyStrNeedlesRector;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php80\Rector\Identical\StrStartsWithRector;
use Rector\Php80\Rector\FunctionLike\MixedTypeRector;
use Rector\Php80\Rector\NotIdentical\StrContainsRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php80\Rector\Identical\StrEndsWithRector;
use Rector\Php80\Rector\Switch_\ChangeSwitchToMatchRector;
use Rector\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector;
use Rector\Php80\Rector\FuncCall\ClassOnObjectRector;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;
use Rector\Php81\Rector\Array_\FirstClassCallableRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;

define( 'ABSPATH', '' );

return RectorConfig::configure()
	->withPaths([
		__DIR__ . '/classes',
		__DIR__ . '/stripe',
	])
    // register single rule
    ->withSets([
        LevelSetList::UP_TO_PHP_84,
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
        // This rule uses the second param of dirname().
        MultiDirnameRector::class,
        // This rule adds public to all constants missing scope.
        PublicConstantVisibilityRector::class,
        // This rule changes list( $var1, $var2 ) = to [ $var1, $var2 ] =.
        ListToArrayDestructRector::class,
        // This rule enforces array_key_first.
        ArrayKeyFirstLastRector::class,
        // This casts cost to strings before they are passed as a string parameter.
        StringifyStrNeedlesRector::class,
        // This converts closures to arrow functions.
        ClosureToArrowFunctionRector::class,
        // This changes strpos to str_starts_with.
        StrStartsWithRector::class,
        // This adds mixed type to params
        MixedTypeRector::class,
        // This changes strpos to str_contains.
        StrContainsRector::class,
        // This implmenets PHP 8.0 constructor promotion.
        ClassPropertyAssignToConstructorPromotionRector::class,
        // This changes strpos to str_ends_with.
        StrEndsWithRector::class,
        // This changes switch statements to match.
        ChangeSwitchToMatchRector::class,
        RemoveUnusedVariableInCatchRector::class,
        ClassOnObjectRector::class,
        NullToStrictStringFuncCallArgRector::class,
        FirstClassCallableRector::class,
        ReturnNeverTypeRector::class,
        AddOverrideAttributeToOverriddenMethodsRector::class,
    ]);
