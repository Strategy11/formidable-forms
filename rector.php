<?php

use Rector\Config\RectorConfig;
use Rector\CodeQuality\Rector\Ternary\SwitchNegatedTernaryRector;
use Rector\CodeQuality\Rector\FuncCall\CompactToVariablesRector;
use Rector\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector;
use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector;
use Rector\CodeQuality\Rector\Assign\CombinedAssignRector;
use Rector\CodeQuality\Rector\Empty_\SimplifyEmptyCheckOnEmptyArrayRector;
use Rector\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector;
use Rector\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector;
use Rector\CodeQuality\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector;
use Rector\CodeQuality\Rector\Concat\JoinStringConcatRector;
use Rector\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\FunctionLike\RemoveDeadReturnRector;
use Rector\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector;
use Rector\CodeQuality\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector;
use Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector;
use Rector\CodeQuality\Rector\If_\ShortenElseIfRector;
use Rector\CodeQuality\Rector\If_\CombineIfRector;
use Rector\CodeQuality\Rector\FuncCall\SingleInArrayToCompareRector;
use Rector\DeadCode\Rector\Foreach_\RemoveUnusedForeachKeyRector;
use Rector\CodeQuality\Rector\Switch_\SingularSwitchToIfRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodParameterRector;
use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\CodeQuality\Rector\FuncCall\SimplifyRegexPatternRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedConstructorParamRector;
use Rector\Php54\Rector\Array_\LongArrayToShortArrayRector;
use Rector\Php53\Rector\Ternary\TernaryToElvisRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Rector\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector;
use Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\CodingStyle\Rector\FuncCall\StrictArraySearchRector;
use Rector\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector;
use Rector\CodingStyle\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector;
use Rector\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector;
use Rector\Php70\Rector\FuncCall\MultiDirnameRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodingStyle\Rector\If_\NullableCompareToNullRector;
use Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector;
use Rector\CodingStyle\Rector\FuncCall\CallUserFuncArrayToVariadicRector;
use Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Rector\EarlyReturn\Rector\Return_\ReturnBinaryOrToEarlyReturnRector;
use Rector\EarlyReturn\Rector\StmtsAwareInterface\ReturnEarlyIfVariableRector;
use Rector\EarlyReturn\Rector\If_\ChangeOrIfContinueToMultiContinueRector;
use Rector\CodingStyle\Rector\String_\SimplifyQuoteEscapeRector;
use Rector\CodeQuality\Rector\BooleanAnd\RepeatedAndNotEqualToNotInArrayRector;
use Rector\CodeQuality\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector;
use Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector;
use Rector\CodingStyle\Rector\ClassLike\NewlineBetweenClassLikeStmtsRector;
use Rector\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector;
use Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector;

define( 'ABSPATH', '' );

return RectorConfig::configure()
	->withPaths(
		array(
			__DIR__ . '/classes',
			__DIR__ . '/stripe',
			__DIR__ . '/square',
			__DIR__ . '/tests',
		)
	)
	// here we can define, what prepared sets of rules will be applied
	->withPreparedSets(
		// deadCode
		true,
		// codeQuality
		true,
		// codingStyle
		true,
		// typeDeclarations
		false,
		// typeDeclarationDocblocks
		true,
		// privatization
		true,
		// naming
		true,
		// instanceOf
		true,
		// earlyReturn
		true,
		// strictBooleans
		false,
		// carbon
		false,
		// rectorPreset
		true,
		// phpunitCodeQuality
		true,
		// doctrineCodeQuality
		false,
		// symfonyCodeQuality
		false,
		// symfonyConfigs
		false
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
			SimplifyEmptyCheckOnEmptyArrayRector::class,
			CountArrayToEmptyArrayComparisonRector::class,
			DisallowedEmptyRuleFixerRector::class,
			LocallyCalledStaticMethodToNonStaticRector::class,
			// This changes \t to an actual tab character. We don't want this rule.
			JoinStringConcatRector::class,
			ChangeArrayPushToArrayAssignRector::class,
			// We never want to remove a param tag. Leave this exception.
			RemoveUselessParamTagRector::class,
			RemoveDeadReturnRector::class,
			RemoveAlwaysTrueIfConditionRector::class,
			// This changes if is_array() && empty() to if === [].
			SimplifyEmptyArrayCheckRector::class,
			LongArrayToShortArrayRector::class,
			// WP argues that the Elvis operator does not help with readability.
			TernaryToElvisRector::class,
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
			NewlineAfterStatementRector::class,
			RemoveUselessReturnTagRector::class,
			RenameVariableToMatchNewTypeRector::class,
			NewlineBeforeNewAssignSetRector::class,
			EncapsedStringsToSprintfRector::class,
			StrictArraySearchRector::class,
			RenameVariableToMatchMethodCallReturnTypeRector::class,
			WrapEncapsedVariableInCurlyBracesRector::class,
			ReturnBinaryOrToEarlyReturnRector::class,
			RenamePropertyToMatchTypeRector::class,
			RenameForeachValueVariableToMatchExprVariableRector::class,
			PostIncDecToPreIncDecRector::class,
			MultiDirnameRector::class,
			FlipTypeControlToUseExclusiveTypeRector::class,
			NullableCompareToNullRector::class,
			MakeInheritedMethodVisibilitySameAsParentRector::class,
			CallUserFuncArrayToVariadicRector::class,
			CatchExceptionNameMatchingTypeRector::class,
			ChangeOrIfContinueToMultiContinueRector::class,
			ReturnEarlyIfVariableRector::class,
			SimplifyQuoteEscapeRector::class,
			RepeatedAndNotEqualToNotInArrayRector::class,
			UseIdenticalOverEqualWithSameTypeRector::class,
			RenameParamToMatchTypeRector::class,
			NewlineBetweenClassLikeStmtsRector::class,
			AbsolutizeRequireAndIncludePathRector::class,
			CompleteDynamicPropertiesRector::class,
			RemoveParentCallWithoutParentRector::class,
		)
	);
