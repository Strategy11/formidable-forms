import { appendFileSync, mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import path from 'node:path';

const baseline = JSON.parse( readFileSync( new URL( 'baseline.json', import.meta.url ), 'utf8' ) );

/**
 * Save the complete audit and enforce the reviewed exceptions for this page.
 *
 * @param {Object} results        Lighthouse reports and audit results.
 * @param {Object} results.lhr    Lighthouse audit result.
 * @param {Array}  results.report Rendered HTML and JSON reports.
 */
export function reportLighthouse( { lhr, report } ) {
	const url = new URL( lhr.requestedUrl );
	const page = url.searchParams.get( 'page' ) === 'formidable-dashboard'
		? 'dashboard' : 'form-preview';
	const ignored = baseline.pages[ page ];
	const directory = path.resolve( 'artifacts/lighthouse' );
	mkdirSync( directory, { recursive: true } );
	writeFileSync( path.resolve( directory, `${ page }.json` ), JSON.stringify( lhr, null, '\t' ) );
	writeFileSync( path.resolve( directory, `${ page }.html` ), report[ 0 ] );

	const regressions = [];
	const auditErrors = [];
	const rows = [];
	const seen = new Set();
	const summary = [ `## Lighthouse: ${ page }`, '', `Version: ${ lhr.lighthouseVersion }`, '' ];
	for ( const category of Object.values( lhr.categories ) ) {
		summary.push( `${ category.title }: ${ Math.round( category.score * 100 ) }/100` );
		for ( const { id } of category.auditRefs ) {
			const audit = lhr.audits[ id ];
			if ( seen.has( id ) ) {
				continue;
			}
			seen.add( id );
			if ( audit.scoreDisplayMode === 'error' ) {
				auditErrors.push( `${ id }: ${ audit.errorMessage }` );
			}
			if ( typeof audit.score !== 'number' || audit.score >= 1 ) {
				continue;
			}
			const status = baseline.informationalAudits[ id ]
				? 'Informational' : ( ignored[ id ] ? 'Baseline' : 'NEW FAILURE' );
			rows.push( `| ${ category.title } | ${ id } | ${ status } | ${ audit.title } |` );
			if ( status === 'NEW FAILURE' ) {
				regressions.push( id );
			}
		}
	}
	summary.push( '', '| Category | Audit | Status | Finding |', '| --- | --- | --- | --- |', ...rows, '' );
	summary.push( 'Timing metrics are informational while runner variance is measured. Other audits enforce the reviewed page baseline.', '' );
	if ( auditErrors.length ) {
		summary.push( 'Audit errors:', ...auditErrors.map( error => `- ${ error }` ), '' );
	}
	const markdown = summary.join( '\n' );
	writeFileSync( path.resolve( directory, `${ page }.md` ), markdown );
	console.log( markdown );
	if ( process.env.GITHUB_STEP_SUMMARY ) {
		appendFileSync( process.env.GITHUB_STEP_SUMMARY, markdown );
	}

	if ( auditErrors.length ) {
		throw new Error( `Lighthouse ${ page } audit errors: ${ auditErrors.join( ', ' ) }` );
	}
	if ( lhr.runtimeError ) {
		throw new Error( `Lighthouse ${ page }: ${ lhr.runtimeError.message }` );
	}
	const finalUrl = new URL( lhr.finalDisplayedUrl || lhr.finalUrl );
	if ( finalUrl.pathname !== url.pathname || finalUrl.search !== url.search ) {
		throw new Error( `Lighthouse ${ page } audited an unexpected redirect: ${ finalUrl }` );
	}
	if ( lhr.lighthouseVersion !== baseline.lighthouseVersion ) {
		throw new Error( 'Lighthouse version changed. Review the reports and refresh the baseline.' );
	}
	if ( process.env.LIGHTHOUSE_COLLECT !== '1' && regressions.length ) {
		throw new Error( `Lighthouse ${ page } failures: ${ regressions.join( ', ' ) }` );
	}
}
