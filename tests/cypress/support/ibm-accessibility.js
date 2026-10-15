/**
 * Return confirmed violations outside the page's reviewed rule allowances.
 *
 * @param {string} label    The page's stable scan label.
 * @param {Array}  results  IBM scan findings.
 * @param {Object} baseline Reviewed allowances keyed by page label.
 * @return {Array} Findings which must fail the test.
 */
export function getIbmAccessibilityFailures( label, results, baseline ) {
	if ( ! Object.hasOwn( baseline, label ) ) {
		throw new Error( `Missing IBM accessibility baseline for ${ label }.` );
	}

	const rules = baseline[ label ];
	const counts = new Map();

	for ( const [ ruleId, allowance ] of Object.entries( rules ) ) {
		const hasInvalidLimit = allowance.maxViolations !== undefined &&
			( ! Number.isInteger( allowance.maxViolations ) || allowance.maxViolations < 1 );

		if ( ! allowance.reason?.trim() ||
			hasInvalidLimit ) {
			throw new Error( `Invalid IBM baseline allowance: ${ label } / ${ ruleId }.` );
		}
	}

	return results.filter( result => {
		if ( result.level !== 'violation' ) {
			return false;
		}

		const count = ( counts.get( result.ruleId ) ?? 0 ) + 1;
		counts.set( result.ruleId, count );

		if ( ! Object.hasOwn( rules, result.ruleId ) ) {
			return true;
		}

		const { maxViolations } = rules[ result.ruleId ];
		return maxViolations !== undefined && count > maxViolations;
	} );
}
