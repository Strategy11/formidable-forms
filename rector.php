<?php

use Rector\Config\RectorConfig;
use Rector\CodeQuality\Rector\Ternary\SwitchNegatedTernaryRector;
use Rector\CodeQuality\Rector\FuncCall\CompactToVariablesRector;
use Rector\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector;
use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector;
use Rector\CodeQuality\Rector\Assign\CombinedAssignRector;
use Rector\CodeQuality\Rector\ClassMethod\ExplicitReturnNullRector;
use Rector\CodeQuality\Rector\Empty_\SimplifyEmptyCheckOnEmptyArrayRector;
use Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use Rector\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector;
use Rector\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\CodeQuality\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector;
use Rector\CodeQuality\Rector\Concat\JoinStringConcatRector;
use Rector\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\Expression\RemoveDeadStmtRector;
use Rector\DeadCode\Rector\FunctionLike\RemoveDeadReturnRector;
use Rector\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector;
use Rector\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector;
use Rector\CodeQuality\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector;
use Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector;
use Rector\CodeQuality\Rector\If_\ShortenElseIfRector;
use Rector\CodeQuality\Rector\If_\CombineIfRector;
use Rector\CodeQuality\Rector\FuncCall\SingleInArrayToCompareRector;
use Rector\DeadCode\Rector\Foreach_\RemoveUnusedForeachKeyRector;
use Rector\CodeQuality\Rector\Switch_\SingularSwitchToIfRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodParameterRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\CodeQuality\Rector\FuncCall\SimplifyRegexPatternRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedConstructorParamRector;
use Rector\Php54\Rector\Array_\LongArrayToShortArrayRector;
use Rector\Php53\Rector\Ternary\TernaryToElvisRector;

define( 'ABSPATH', '' );

return RectorConfig::configure()
	->withPaths(
		array(
			__DIR__ . '/classes',
			__DIR__ . '/stripe',
			__DIR__ . '/square',
		)
	)
	// here we can define, what prepared sets of rules will be applied
	->withPreparedSets(
		// deadCode
		true,
		// codeQuality
		true
	)
	->withPhpSets(
		// PHP 8.3
		false,
		// PHP 8.2
		false,
		// PHP 8.1
		false,
		// PHP 8.0
		false,
		// PHP 7.4
		false,
		// PHP 7.3
		false,
		// PHP 7.2
		false,
		// PHP 7.1
		false,
		// PHP 7.0
		true
	)
	->withSkip(
		array(
			SwitchNegatedTernaryRector::class,
			CompactToVariablesRector::class,
			IssetOnPropertyObjectToPropertyExistsRector::class,
			ExplicitBoolCompareRector::class,
			UnusedForeachValueToArrayKeysRector::class,
			CombinedAssignRector::class,
			ExplicitReturnNullRector::class,
			SimplifyEmptyCheckOnEmptyArrayRector::class,
			SimplifyUselessVariableRector::class,
			CountArrayToEmptyArrayComparisonRector::class,
			DisallowedEmptyRuleFixerRector::class,
			SimplifyIfReturnBoolRector::class,
			SimplifyIfElseToTernaryRector::class,
			LocallyCalledStaticMethodToNonStaticRector::class,
			// This changes \t to an actual tab character. We don't want this rule.
			JoinStringConcatRector::class,
			ChangeArrayPushToArrayAssignRector::class,
			// We never want to remove a param tag. Leave this exception.
			RemoveUselessParamTagRector::class,
			RemoveDeadReturnRector::class,
			RemoveAlwaysTrueIfConditionRector::class,
			RemoveUnreachableStatementRector::class,
			// This changes if is_array() && empty() to if === [].
			SimplifyEmptyArrayCheckRector::class,
			LongArrayToShortArrayRector::class,
			TernaryToElvisRector::class,
			// TODO: Try this for some files and not others.
			RemoveUnusedPrivateMethodRector::class,
			ShortenElseIfRector::class,
			CombineIfRector::class,
			SingleInArrayToCompareRector::class,
			RemoveUnusedForeachKeyRector::class,
			SingularSwitchToIfRector::class,
			RemoveUnusedPrivateMethodParameterRector::class,
			InlineConstructorDefaultToPropertyRector::class,
			SimplifyRegexPatternRector::class,
			RemoveUnusedConstructorParamRector::class,
		)
	);
