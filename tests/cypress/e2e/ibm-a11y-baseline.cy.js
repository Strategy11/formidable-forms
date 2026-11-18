import { getIbmAccessibilityFailures } from '../support/ibm-accessibility';
import baseline from '../fixtures/ibm-a11y-baseline.json';

describe( 'IBM accessibility baseline enforcement', () => {
	const violation = { ruleId: 'input_label_exists', level: 'violation' };
	const reviewedBaseline = {
		dashboard: {
			input_label_exists: {
				reason: 'One existing input needs a visible label.',
				maxViolations: 1,
			},
		},
		preview: {},
	};

	it( 'Fails an unreviewed rule on a known page', () => {
		expect( getIbmAccessibilityFailures( 'preview', [ violation ], reviewedBaseline ) )
			.to.deep.equal( [ violation ] );
	} );

	it( 'Allows a reviewed rule within its recorded count', () => {
		expect( getIbmAccessibilityFailures( 'dashboard', [ violation ], reviewedBaseline ) )
			.to.deep.equal( [] );
	} );

	it( 'Fails an increase in a reviewed rule', () => {
		expect( getIbmAccessibilityFailures( 'dashboard', [ violation, violation ], reviewedBaseline ) )
			.to.deep.equal( [ violation ] );
	} );

	it( 'Allows uncapped rules whose counts depend on page data', () => {
		const dataDependentBaseline = {
			preview: {
				input_label_exists: { reason: 'Known labels in a variable number of rows.' },
			},
		};
		expect( getIbmAccessibilityFailures( 'preview', [ violation, violation ], dataDependentBaseline ) )
			.to.deep.equal( [] );
	} );

	it( 'Fails a different rule on a page with allowances', () => {
		const newViolation = { ruleId: 'image_alt_exists', level: 'violation' };
		expect( getIbmAccessibilityFailures( 'dashboard', [ newViolation ], reviewedBaseline ) )
			.to.deep.equal( [ newViolation ] );
	} );

	it( 'Does not share allowances between pages', () => {
		expect( getIbmAccessibilityFailures( 'preview', [ violation ], reviewedBaseline ) )
			.to.deep.equal( [ violation ] );
	} );

	it( 'Keeps potential violations informational', () => {
		const potential = { ...violation, level: 'potentialviolation' };
		expect( getIbmAccessibilityFailures( 'dashboard', [ potential, violation ], reviewedBaseline ) )
			.to.deep.equal( [] );
	} );

	it( 'Fails when a page has no baseline entry, even with no findings', () => {
		expect( () => getIbmAccessibilityFailures( 'missing', [], reviewedBaseline ) )
			.to.throw( 'Missing IBM accessibility baseline for missing.' );
	} );

	it( 'Rejects missing reasons and invalid count limits', () => {
		for ( const allowance of [
			{ maxViolations: 1 },
			{ reason: ' ', maxViolations: 1 },
			{ reason: 'Missing label.', maxViolations: 0 },
			{ reason: 'Missing label.', maxViolations: 1.5 },
		] ) {
			expect( () => getIbmAccessibilityFailures( 'preview', [], {
				preview: { input_label_exists: allowance },
			} ) ).to.throw( 'Invalid IBM baseline allowance' );
		}
	} );

	it( 'Validates every checked-in allowance', () => {
		for ( const label of Object.keys( baseline ) ) {
			expect( getIbmAccessibilityFailures( label, [], baseline ) ).to.deep.equal( [] );
		}
	} );
} );
