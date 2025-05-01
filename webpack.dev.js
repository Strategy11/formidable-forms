/**
 * Formidable Forms Development Server
 *
 * Run `npm run dev:hot` to start the development server. You'll be prompted
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

/**
 * Webpack configuration
 */
const webpackConfig = require( './webpack.config' );

/**
 * Gets the CSS configuration from the webpack config array
 *
 * @return {Object} The CSS webpack configuration
 */
function getCssConfig() {
	const cssConfig = webpackConfig.find( ( conf ) => conf.name === 'css' );

	if ( ! cssConfig ) {
		console.error( 'CSS configuration not found in webpack config.' );
		process.exit( 1 );
	}

	return cssConfig;
}

/**
 * Initialize development server
 */
function init() {
	const compiler = webpack( getCssConfig() );
	const bs = browserSync.create( 'FormidableDev' );

	// Setup browser-sync server
	bs.init( {
		/**
		 * Proxy configuration
		 */
		proxy: {
			target: config.siteUrl,
			middleware: [
				webpackDevMiddleware( compiler, {
					publicPath: config.publicPath,
					writeToDisk: true
				})
			]
		},

		/**
		 * File watching configuration
		 */
		files: [
			// CSS changes - inject without full reload
			{
				match: [ `${config.cssPath}/**/*.css` ],
				fn: function( event, file ) {
					console.log( `CSS updated: ${file}` );
					this.reload( '*.css' );
				}
			},
			// Other files that need full page reload
			`${config.jsPath}/**/*.js`,
			`${config.phpPath}/**/*.php`
		],

		/**
		 * Server settings
		 */
		port: config.port,
		ui: { port: config.uiPort },
		open: true,
		notify: false,
		injectChanges: true,
		ghostMode: false,

		/**
		 * Resource handling
		 */
		serveStatic: [{ route: '/css', dir: config.cssPath }],
		rewriteRules: [{
			match: new RegExp( config.siteUrl.replace( /^https?:\/\//, '' ), 'g' ),
			replace: `localhost:${config.port}`
		}]
	});

	console.log( `Development server running at: http://localhost:${config.port}` );
}

// Start the server
init();
