/**
 * External dependencies
 */
const webpack = require( 'webpack' );
const path = require( 'path' );
const terser = require('terser-webpack-plugin');

// Webpack configuration.
const config = {
	mode: process.env.NODE_ENV === 'production' ? 'production' : 'development',
	devtool: process.env.NODE_ENV !== 'production' ? 'source-map' : undefined,
	resolve: {
		extensions: [ '.json', '.js', '.jsx' ],
		modules: [
			`${ __dirname }/js`,
			'node_modules'
		]
	},
	entry: {
		formidable_blocks: './js/src/blocks.js',
		formidable_overlay: './js/src/overlay.js',
		'form-templates': './js/src/form-templates/index.js'
	},
	optimization: {
		minimizer: [
			new terser({
				extractComments: false,
				terserOptions: {
					format: {
						comments: false,
					},
				},
			})
		],
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
			}
		]
	},
	externals: {
		jquery: 'jQuery',
		$: 'jQuery'
	}
};

module.exports = config;
