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
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\CodeQuality\Rector\FuncCall\SimplifyRegexPatternRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedConstructorParamRector;
use Rector\Php54\Rector\Array_\LongArrayToShortArrayRector;
use Rector\Php53\Rector\Ternary\TernaryToElvisRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Rector\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\ClassMethod\BoolReturnTypeFromBooleanStrictReturnsRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnNullableTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnDirectArrayRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector;
use Rector\TypeDeclaration\Rector\ClassMethod\StrictArrayParamDimFetchRector;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;
use Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\CodingStyle\Rector\FuncCall\StrictArraySearchRector;
use Rector\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNativeCallRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnUnionTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\StringReturnTypeFromStrictStringReturnsRector;
use Rector\TypeDeclaration\Rector\Closure\ClosureReturnTypeRector;
use Rector\CodingStyle\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector;
use Rector\TypeDeclaration\Rector\ClassMethod\StrictStringParamConcatRector;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\NumericReturnTypeFromStrictReturnsRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationBasedOnParentClassMethodRector;
use Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector;
use Rector\TypeDeclaration\Rector\ClassMethod\BoolReturnTypeFromBooleanConstReturnsRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnCastRector;
use Rector\TypeDeclaration\Rector\ClassMethod\StringReturnTypeFromStrictScalarReturnsRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictConstantReturnRector;
use Rector\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector;
use Rector\Php70\Rector\FuncCall\MultiDirnameRector;
use Rector\TypeDeclaration\Rector\Class_\ReturnTypeFromStrictTernaryRector;
use Rector\TypeDeclaration\Rector\Closure\AddClosureVoidReturnTypeWhereNoReturnRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodingStyle\Rector\If_\NullableCompareToNullRector;
use Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector;
use Rector\TypeDeclaration\Rector\ClassMethod\NumericReturnTypeFromStrictScalarReturnsRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector;
use Rector\CodingStyle\Rector\FuncCall\CallUserFuncArrayToVariadicRector;
use Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Rector\EarlyReturn\Rector\Return_\ReturnBinaryOrToEarlyReturnRector;
use Rector\EarlyReturn\Rector\StmtsAwareInterface\ReturnEarlyIfVariableRector;
use Rector\EarlyReturn\Rector\If_\ChangeOrIfContinueToMultiContinueRector;
use Rector\CodingStyle\Rector\String_\SimplifyQuoteEscapeRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddParamArrayDocblockFromAssignsParamToParamReferenceRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddParamArrayDocblockFromDimFetchAccessRector;
use Rector\CodeQuality\Rector\BooleanAnd\RepeatedAndNotEqualToNotInArrayRector;
use Rector\CodeQuality\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamFromDimFetchKeyUseRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\DocblockReturnArrayFromDirectArrayInstanceRector;
use Rector\TypeDeclaration\Rector\FuncCall\AddArrayFunctionClosureParamTypeRector;
use Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector;
use Rector\TypeDeclaration\Rector\FuncCall\AddArrowFunctionParamArrayWhereDimFetchRector;
use Rector\CodingStyle\Rector\ClassLike\NewlineBetweenClassLikeStmtsRector;
use Rector\TypeDeclaration\Rector\FunctionLike\AddClosureParamTypeForArrayMapRector;
use Rector\TypeDeclaration\Rector\ClassMethod\KnownMagicClassMethodTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamStringTypeFromSprintfUseRector;
use Rector\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector;
use Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector;

define( 'ABSPATH', '' );

return RectorConfig::configure()
	->withPaths(
		array(
			__DIR__ . '/classes',
			__DIR__ . '/stripe',
			__DIR__ . '/square',
			__DIR__ . '/paypal',
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
		true,
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
			// Enable this one soon.
			NewlineAfterStatementRector::class,
			// Try these
			RemoveUselessReturnTagRector::class,
			RenameVariableToMatchNewTypeRector::class,
			AddMethodCallBasedStrictParamTypeRector::class,
			AddVoidReturnTypeWhereNoReturnRector::class,
			BoolReturnTypeFromBooleanStrictReturnsRector::class,
			ReturnNullableTypeRector::class,
			ReturnTypeFromReturnDirectArrayRector::class,
			ReturnTypeFromStrictNewArrayRector::class,
			StrictArrayParamDimFetchRector::class,
			DeclareStrictTypesRector::class,
			NewlineBeforeNewAssignSetRector::class,
			EncapsedStringsToSprintfRector::class,
			StrictArraySearchRector::class,
			RenameVariableToMatchMethodCallReturnTypeRector::class,
			ReturnTypeFromStrictNativeCallRector::class,
			ReturnUnionTypeRector::class,
			StringReturnTypeFromStrictStringReturnsRector::class,
			ClosureReturnTypeRector::class,
			WrapEncapsedVariableInCurlyBracesRector::class,
			ReturnTypeFromStrictTypedCallRector::class,
			StrictStringParamConcatRector::class,
			ReturnBinaryOrToEarlyReturnRector::class,
			RenamePropertyToMatchTypeRector::class,
			NumericReturnTypeFromStrictReturnsRector::class,
			TypedPropertyFromAssignsRector::class,
			TypedPropertyFromStrictConstructorRector::class,
			AddReturnTypeDeclarationBasedOnParentClassMethodRector::class,
			RenameForeachValueVariableToMatchExprVariableRector::class,
			BoolReturnTypeFromBooleanConstReturnsRector::class,
			ReturnTypeFromReturnCastRector::class,
			StringReturnTypeFromStrictScalarReturnsRector::class,
			ReturnTypeFromStrictConstantReturnRector::class,
			PostIncDecToPreIncDecRector::class,
			MultiDirnameRector::class,
			ReturnTypeFromStrictTernaryRector::class,
			AddClosureVoidReturnTypeWhereNoReturnRector::class,
			FlipTypeControlToUseExclusiveTypeRector::class,
			NullableCompareToNullRector::class,
			ReturnTypeFromReturnNewRector::class,
			MakeInheritedMethodVisibilitySameAsParentRector::class,
			NumericReturnTypeFromStrictScalarReturnsRector::class,
			ReturnNeverTypeRector::class,
			CallUserFuncArrayToVariadicRector::class,
			CatchExceptionNameMatchingTypeRector::class,
			ChangeOrIfContinueToMultiContinueRector::class,
			ReturnEarlyIfVariableRector::class,
			SimplifyQuoteEscapeRector::class,
			AddParamArrayDocblockFromAssignsParamToParamReferenceRector::class,
			AddParamArrayDocblockFromDimFetchAccessRector::class,
			RepeatedAndNotEqualToNotInArrayRector::class,
			UseIdenticalOverEqualWithSameTypeRector::class,
			AddParamFromDimFetchKeyUseRector::class,
			DocblockReturnArrayFromDirectArrayInstanceRector::class,
			AddArrayFunctionClosureParamTypeRector::class,
			RenameParamToMatchTypeRector::class,
			AddArrowFunctionParamArrayWhereDimFetchRector::class,
			NewlineBetweenClassLikeStmtsRector::class,
			AddClosureParamTypeForArrayMapRector::class,
			KnownMagicClassMethodTypeRector::class,
			AddParamStringTypeFromSprintfUseRector::class,
			AbsolutizeRequireAndIncludePathRector::class,
			CompleteDynamicPropertiesRector::class,
			RemoveParentCallWithoutParentRector::class,
		)
	);
