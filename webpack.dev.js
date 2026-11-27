/**
 * Formidable Forms Development Server
 *
 * Run `npm run serve` to start the development server. You'll be prompted
 * for your WordPress site URL, then a dev server starts at localhost:8880.
 *
 * - CSS/SCSS changes are injected in real-time without page refresh
 * - JavaScript is rebuilt on change. Reload the page to see it.
 * - Browser opens automatically to localhost:8880
 */

/**
 * External dependencies
 */
const browserSync = require( 'browser-sync' );
const webpack = require( 'webpack' );
const webpackDevMiddleware = require( 'webpack-dev-middleware' );

/**
 * Config
 */
const config = {
	// WordPress site URL from environment variable or default
	siteDomain: process.env.SITE_DOMAIN || 'formidable.local',

	// Server ports (8880/8881 to avoid conflicts with common dev tools)
	port: 8880,
	uiPort: 8881,

	// Paths to watch for changes
	cssPath: '../formidable*/**/*.css',
	jsSrcPath: '../formidable*/*/js/src/**/*.js',

	// Public path for webpack
	publicPath: '/wp-content/plugins/formidable/css/'
};

// Import webpack config
const [ jsConfig, cssConfig ] = require( './webpack.config' );

/**
 * Initialize development server
 */
const init = () => {
	const browserSyncInstance = browserSync.create( 'FormidableDev' );

	// Create separate webpack compilers for CSS and JS
	const jsCompiler = webpack( jsConfig );
	const cssCompiler = webpack( cssConfig );

	// Watch JS with webpack
	jsCompiler.watch(
		{ aggregateTimeout: 300 },
		( err, stats ) => err || stats.hasErrors()
			? console.log( `JS compilation ${ err ? 'error: ' + err : 'has errors' }` )
			: console.log( 'JS compiled successfully' )
	);

	browserSyncInstance.init( {
		proxy: {
			target: config.siteDomain,
			middleware: [
				webpackDevMiddleware( cssCompiler, {
					publicPath: config.publicPath,
					writeToDisk: true
				} )
			]
		},

		// Exclude 3rd-party directories from all file watchers
		watchOptions: {
			ignored: [ '**/node_modules/**', '**/vendor/**' ]
		},

		// File watching
		files: [
			// CSS changes, inject without reload
			{
				match: [ config.cssPath ],
				fn: ( event, file ) => {
					console.log( `CSS updated: ${ file }` );
					browserSyncInstance.reload( '*.css' );
				}
			},
			// JS source changes log rebuild progress (webpack handles actual compilation)
			{
				match: [ config.jsSrcPath ],
				fn: ( event, file ) => console.log( `JS source updated: ${ file }\nRebuilding JS bundles...` )
			}
		],

		// Server settings
		port: config.port,
		ui: { port: config.uiPort },
		open: true,
		notify: false,
		injectChanges: true,
		ghostMode: false,

		// Resource handling
		serveStatic: [ { route: '/css', dir: './css' } ],
		rewriteRules: [ {
			match: new RegExp( config.siteDomain.replace( /^https?:\/\//, '' ), 'g' ),
			replace: `localhost:${ config.port }`
		} ]
	} );

	console.log( `Development server running at http://localhost:${ config.port }` );
};

// Start the server
init();
