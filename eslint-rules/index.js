'use strict';

const preferStrictComparison = require( './rules/prefer-strict-comparison' );
const noRedundantUndefinedCheck = require( './rules/no-redundant-undefined-check' );
const preferIncludes = require( './rules/prefer-includes' );
const noTypeofUndefined = require( './rules/no-typeof-undefined' );
const noOptionalChainingQueryselectorall = require( './rules/no-optional-chaining-queryselectorall' );
const noRepeatedSelector = require( './rules/no-repeated-selector' );
const preferDocumentFragment = require( './rules/prefer-document-fragment' );

module.exports = {
	rules: {
		'prefer-strict-comparison': preferStrictComparison,
		'no-redundant-undefined-check': noRedundantUndefinedCheck,
		'prefer-includes': preferIncludes,
		'no-typeof-undefined': noTypeofUndefined,
		'no-optional-chaining-queryselectorall': noOptionalChainingQueryselectorall,
		'no-repeated-selector': noRepeatedSelector,
		'prefer-document-fragment': preferDocumentFragment,
	},
};
