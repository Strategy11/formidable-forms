<?php

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;
use Rector\Instanceof_\Rector\Ternary\FlipNegatedTernaryInstanceofRector;
use Rector\CodeQuality\Rector\Ternary\SwitchNegatedTernaryRector;
use Rector\CodeQuality\Rector\FuncCall\CompactToVariablesRector;
use Rector\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector;
use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector;
use Rector\CodeQuality\Rector\Assign\CombinedAssignRector;
use Rector\CodeQuality\Rector\ClassMethod\ExplicitReturnNullRector;
use Rector\CodeQuality\Rector\Empty_\SimplifyEmptyCheckOnEmptyArrayRector;
use Rector\CodeQuality\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector;
use Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use Rector\DeadCode\Rector\If_\ReduceAlwaysFalseIfOrRector;
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
use Rector\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector;
use Rector\DeadCode\Rector\Node\RemoveNonExistingVarAnnotationRector;
use Rector\DeadCode\Rector\If_\RemoveUnusedNonEmptyArrayBeforeForeachRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedConstructorParamRector;
use Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector;

define( 'ABSPATH', '' );

return RectorConfig::configure()
	->withPaths(
		array(
			__DIR__ . '/classes',
			__DIR__ . '/stripe',
		)
	)
	// here we can define, what prepared sets of rules will be applied
	->withPreparedSets(
		// deadCode
		true,
		// codeQuality
		true
	)
	->withSkip(
		array(
			FlipNegatedTernaryInstanceofRector::class,
			SwitchNegatedTernaryRector::class,
			CompactToVariablesRector::class,
			IssetOnPropertyObjectToPropertyExistsRector::class,
			ExplicitBoolCompareRector::class,
			UnusedForeachValueToArrayKeysRector::class,
			CombinedAssignRector::class,
			ExplicitReturnNullRector::class,
			SimplifyEmptyCheckOnEmptyArrayRector::class,
			UseIdenticalOverEqualWithSameTypeRector::class,
			SimplifyUselessVariableRector::class,
			ReduceAlwaysFalseIfOrRector::class,
			CountArrayToEmptyArrayComparisonRector::class,
			DisallowedEmptyRuleFixerRector::class,
			SimplifyIfReturnBoolRector::class,
			SimplifyIfElseToTernaryRector::class,
			LocallyCalledStaticMethodToNonStaticRector::class,
			JoinStringConcatRector::class,
			ChangeArrayPushToArrayAssignRector::class,
			RemoveUselessParamTagRector::class,
			RemoveDeadStmtRector::class,
			RemoveDeadReturnRector::class,
			RemoveAlwaysTrueIfConditionRector::class,
			RemoveUnreachableStatementRector::class,
			SimplifyEmptyArrayCheckRector::class,
			CompleteDynamicPropertiesRector::class,
			TypedPropertyFromStrictConstructorRector::class,
			// TODO: Try this for some files and not others.
			RemoveUnusedPrivateMethodRector::class,
			ShortenElseIfRector::class,
			// Fix these.
			CombineIfRector::class,
			SingleInArrayToCompareRector::class,
			RemoveUnusedForeachKeyRector::class,
			SingularSwitchToIfRector::class,
			RemoveUnusedPrivateMethodParameterRector::class,
			RenameFunctionRector::class,
			InlineConstructorDefaultToPropertyRector::class,
			SimplifyRegexPatternRector::class,
			RemoveUnusedVariableAssignRector::class,
			RemoveNonExistingVarAnnotationRector::class,
			RemoveUnusedNonEmptyArrayBeforeForeachRector::class,
			RemoveUnusedConstructorParamRector::class,
			UnnecessaryTernaryExpressionRector::class,
		)
	);
