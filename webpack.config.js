/**
 * External dependencies
 */
const webpack = require( 'webpack' );
const path = require( 'path' );
const UglifyJsPlugin = require( 'terser-webpack-plugin' );

// Webpack configuration.
const config = {
	mode: process.env.NODE_ENV === 'production' ? 'production' : 'development',
	resolve: {
		extensions: [ '.json', '.js', '.jsx' ],
		modules: [
			`${ __dirname }/js`,
			'node_modules',
		],
	},
	entry: {
		formidable_blocks: './js/src/blocks.js',
		'formidable.min': './js/formidable.js',
	},
	output: {
		filename: '[name].js',
		path: path.resolve( __dirname, 'js' ),
	},
	module: {
		rules: [
			{
				test: /.js$/,
				exclude: /node_modules/,
				include: /js/,
				use: [
					{
						loader: 'babel-loader',
					},
				],
			},
		],
	},
	optimization: {
		minimizer: [
			new UglifyJsPlugin({
				terserOptions: {
					compress: false,
					mangle: false,
					ie8: true,
					keep_fnames: true,
				}
			})
		]
	},
	externals: {
		jquery: 'jQuery',
		$: 'jQuery',
	},
};

module.exports = config;
