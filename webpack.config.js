/**
 * External dependencies
 */
const webpack = require( 'webpack' );
const path = require( 'path' );

// Webpack configuration.
const config = {
	mode: process.env.NODE_ENV === 'production' ? 'production' : 'development',
	devtool: process.env.NODE_ENV !== 'production' ? 'source-map' : undefined,
	resolve: {
		extensions: [ '.json', '.js', '.jsx' ],
		modules: [
			`${ __dirname }/js`,
			'node_modules'
		],
		alias: {
			core: path.resolve( __dirname, 'js/src/core' )
		}
	},
	entry: {
		formidable_blocks: './js/src/blocks.js',
		formidable_overlay: './js/src/overlay.js',
		'form-templates': './js/src/form-templates/index.js',
		formidable_dashboard: './js/src/dashboard.js',
		'onboarding-wizard': './js/src/onboarding-wizard/index.js',
		'addons-page': './js/src/addons-page/index.js',
		formidable_styles: './js/src/admin/styles.js',
	},
	output: {
		filename: '[name].js',
		path: path.resolve( __dirname, 'js' )
	},
	module: {
		rules: [
			{
				test: /.js$/,
				exclude: /node_modules/,
				include: /js/,
				use: [
					{
						loader: 'babel-loader'
					}
				]
			},
			{
				test: /\.svg$/,
				use: [ '@svgr/webpack' ]
			},
			{
				test: /\.css$/i,
				use: [ { loader: 'style-loader' }, 'css-loader' ]
			}
		]
	},
	externals: {
		jquery: 'jQuery',
		$: 'jQuery'
	}
};

module.exports = config;
