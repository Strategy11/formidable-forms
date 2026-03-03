'use strict';

const preferStrictComparison = require( './rules/prefer-strict-comparison' );
const noRedundantUndefinedCheck = require( './rules/no-redundant-undefined-check' );
const preferIncludes = require( './rules/prefer-includes' );
const noTypeofUndefined = require( './rules/no-typeof-undefined' );

module.exports = {
	rules: {
		'prefer-strict-comparison': preferStrictComparison,
		'no-redundant-undefined-check': noRedundantUndefinedCheck,
		'prefer-includes': preferIncludes,
		'no-typeof-undefined': noTypeofUndefined,
	},
};
