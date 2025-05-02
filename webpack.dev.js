/**
 * Formidable Forms Development Server
 *
 * Run `npm run serv` to start the development server. You'll be prompted
 * for your WordPress site URL, and a development server will start at localhost:3000.
 *
 * Development is streamlined with automatic updates:
 * - CSS/SCSS changes are injected in real-time without page refresh
 * - JavaScript and PHP changes trigger an automatic full page reload
 * - Browser opens automatically to localhost:3000
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
	siteUrl: process.env.SITE_URL || 'https://formidableforms.local',

	// Server ports
	port: 3000,
	uiPort: 3001,

	// Paths
	cssPath: './css',
	jsPath: './js',
	phpPath: './',

	// Public path for webpack
	publicPath: '/wp-content/plugins/formidable/css/'
};

// Import webpack config
const [ jsConfig, cssConfig ] = require( './webpack.config' );

/**
 * Initialize development server
 */
const init = () => {
	// Create separate webpack compilers for CSS and JS
	const cssCompiler = webpack( cssConfig );
	const jsCompiler = webpack( jsConfig );

	// Start browser-sync instance
	const bs = browserSync.create( 'FormidableDev' );

	// Watch JS with webpack
	jsCompiler.watch(
		{ aggregateTimeout: 300 },
		( err, stats ) => err || stats.hasErrors()
			? console.log( `JS compilation ${err ? 'error: ' + err : 'has errors'}` )
			: console.log( 'JS compiled successfully' )
	);

	// Setup browser-sync server
	bs.init({
		// Proxy configuration
		proxy: {
			target: config.siteUrl,
			middleware: [
				webpackDevMiddleware( cssCompiler, {
					publicPath: config.publicPath,
					writeToDisk: true
				})
			]
		},

		// File watching
		files: [
			// CSS changes - inject without reload
			{
				match: [ `${config.cssPath}/**/*.css` ],
				fn: ( event, file ) => {
					console.log( `CSS updated: ${file}` );
					bs.reload( '*.css' );
				}
			},
			// JS source changes
			{
				match: [ './js/src/**/*.js' ],
				fn: ( event, file ) => console.log( `JS source updated: ${file}\nRebuilding JS bundles...` )
			},
			// Compiled JS and PHP files - full page reload
			`${config.jsPath}/**/*.js`,
			`${config.phpPath}/**/*.php`
		],

		// Server settings
		port: config.port,
		ui: { port: config.uiPort },
		open: true,
		notify: false,
		injectChanges: true,
		ghostMode: false,

		// Resource handling
		serveStatic: [{ route: '/css', dir: config.cssPath }],
		rewriteRules: [{
			match: new RegExp( config.siteUrl.replace( /^https?:\/\//, '' ), 'g' ),
			replace: `localhost:${config.port}`
		}]
	});

	console.log( `Development server running at: http://localhost:${config.port}` );
};

// Start the server
init();
