<?php

use Rector\Config\RectorConfig;

// CodeQuality
use Rector\CodeQuality\Rector\Assign\CombinedAssignRector;
use Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector;
use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\CodeQuality\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector;
use Rector\CodeQuality\Rector\Concat\JoinStringConcatRector;
use Rector\CodeQuality\Rector\Empty_\SimplifyEmptyCheckOnEmptyArrayRector;
use Rector\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector;
use Rector\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector;
use Rector\CodeQuality\Rector\FuncCall\CompactToVariablesRector;
use Rector\CodeQuality\Rector\FuncCall\SimplifyRegexPatternRector;
use Rector\CodeQuality\Rector\FuncCall\SingleInArrayToCompareRector;
use Rector\CodeQuality\Rector\If_\CombineIfRector;
use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\CodeQuality\Rector\If_\ShortenElseIfRector;
use Rector\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector;
use Rector\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector;
use Rector\CodeQuality\Rector\Switch_\SingularSwitchToIfRector;
use Rector\CodeQuality\Rector\Ternary\SwitchNegatedTernaryRector;

// CodingStyle
use Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Rector\CodingStyle\Rector\ClassLike\NewlineBetweenClassLikeStmtsRector;
use Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector;
use Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\CodingStyle\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector;
use Rector\CodingStyle\Rector\FuncCall\CallUserFuncArrayToVariadicRector;
use Rector\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector;
use Rector\CodingStyle\Rector\FuncCall\StrictArraySearchRector;
use Rector\CodingStyle\Rector\If_\NullableCompareToNullRector;
use Rector\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\CodingStyle\Rector\String_\SimplifyQuoteEscapeRector;

// DeadCode
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedConstructorParamRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodParameterRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Rector\DeadCode\Rector\Foreach_\RemoveUnusedForeachKeyRector;
use Rector\DeadCode\Rector\FunctionLike\RemoveDeadReturnRector;
use Rector\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector;
use Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector;

// EarlyReturn
use Rector\EarlyReturn\Rector\If_\ChangeOrIfContinueToMultiContinueRector;
use Rector\EarlyReturn\Rector\Return_\ReturnBinaryOrToEarlyReturnRector;
use Rector\EarlyReturn\Rector\StmtsAwareInterface\ReturnEarlyIfVariableRector;

// Php53, Php54, Php70
use Rector\Php53\Rector\Ternary\TernaryToElvisRector;
use Rector\Php54\Rector\Array_\LongArrayToShortArrayRector;
use Rector\Php70\Rector\FuncCall\MultiDirnameRector;

// Strict
use Rector\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector;

// TypeDeclarationDocblocks
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddReturnDocblockForDimFetchArrayFromAssignsRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddParamArrayDocblockFromAssignsParamToParamReferenceRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddParamArrayDocblockFromDimFetchAccessRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\DocblockReturnArrayFromDirectArrayInstanceRector;

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
		false,
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
			// CodeQuality
			AbsolutizeRequireAndIncludePathRector::class,
			ChangeArrayPushToArrayAssignRector::class,
			CombinedAssignRector::class,
			CombineIfRector::class,
			CompactToVariablesRector::class,
			CompleteDynamicPropertiesRector::class,
			ExplicitBoolCompareRector::class,
			InlineConstructorDefaultToPropertyRector::class,
			IssetOnPropertyObjectToPropertyExistsRector::class,
			// This changes \t to an actual tab character. We don't want this rule.
			JoinStringConcatRector::class,
			LocallyCalledStaticMethodToNonStaticRector::class,
			ShortenElseIfRector::class,
			SimplifyEmptyCheckOnEmptyArrayRector::class,
			SimplifyRegexPatternRector::class,
			SingleInArrayToCompareRector::class,
			SingularSwitchToIfRector::class,
			// This flips ! empty to empty in ternary statements, but ! empty statements are typically easier to read.
			SwitchNegatedTernaryRector::class,
			UnusedForeachValueToArrayKeysRector::class,

			// CodingStyle
			CallUserFuncArrayToVariadicRector::class,
			CatchExceptionNameMatchingTypeRector::class,
			CountArrayToEmptyArrayComparisonRector::class,
			EncapsedStringsToSprintfRector::class,
			MakeInheritedMethodVisibilitySameAsParentRector::class,
			// We do not need these many new lines.
			NewlineAfterStatementRector::class,
			NewlineBeforeNewAssignSetRector::class,
			NewlineBetweenClassLikeStmtsRector::class,
			NullableCompareToNullRector::class,
			PostIncDecToPreIncDecRector::class,
			SimplifyQuoteEscapeRector::class,
			StrictArraySearchRector::class,
			WrapEncapsedVariableInCurlyBracesRector::class,

			// DeadCode
			RemoveAlwaysTrueIfConditionRector::class,
			// The FrmFieldType.php file has a few empty functions with only a return; line.
			// We may want to remove that at some point, but I think it's there to prevent another
			// static analysis error.
			RemoveDeadReturnRector::class,
			RemoveParentCallWithoutParentRector::class,
			RemoveUnusedConstructorParamRector::class,
			RemoveUnusedForeachKeyRector::class,
			RemoveUnusedPrivateMethodParameterRector::class,
			RemoveUnusedPrivateMethodRector::class,
			// We never want to remove a valid param tag. Leave this exception.
			RemoveUselessParamTagRector::class,
			RemoveUselessReturnTagRector::class,

			// EarlyReturn
			// This breaks if statements up into multiple if statements. It adds too many lines.
			ChangeOrIfContinueToMultiContinueRector::class,
			// This breaks a return statement up into multiple return statements. It adds too many lines.
			ReturnBinaryOrToEarlyReturnRector::class,
			ReturnEarlyIfVariableRector::class,

			// Php53, Php54, Php70
			// The WP standard still uses the long array syntax, so ignore this for now.
			LongArrayToShortArrayRector::class,
			MultiDirnameRector::class,
			// The WP standard does not encourage the Elvis operator for readability.
			TernaryToElvisRector::class,

			// Strict
			DisallowedEmptyRuleFixerRector::class,

			// TypeDeclarationDocblocks
			AddParamArrayDocblockFromAssignsParamToParamReferenceRector::class,
			AddParamArrayDocblockFromDimFetchAccessRector::class,
			DocblockReturnArrayFromDirectArrayInstanceRector::class,
			AddReturnDocblockForDimFetchArrayFromAssignsRector::class,
		)
	);
