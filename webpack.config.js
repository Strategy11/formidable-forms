/**
 * Webpack Configuration
 */
const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');
const TerserPlugin = require( 'terser-webpack-plugin' );
const fs = require( 'fs' );

// Generate web component SCSS entries: every .scss file directly inside each web component folder.
const webComponentsDir = './js/src/web-components';
const webComponentScssFiles = fs.readdirSync( webComponentsDir, { withFileTypes: true } )
  .filter( dir => dir.isDirectory() )
  .reduce( ( scssList, dir ) => {
    fs.readdirSync( `${ webComponentsDir }/${ dir.name }` )
      .filter( file => file.endsWith( '.scss' ) )
      .forEach( file => {
        scssList[`../js/src/web-components/${ dir.name }/${ file.slice( 0, -5 ) }`] = `${ webComponentsDir }/${ dir.name }/${ file }`;
      } );
    return scssList;
}, {});

/**
 * Environment configuration
 */
const isDevelopment = process.env.NODE_ENV !== 'production';

/**
 * Path configurations
 */
const paths = {
  js: path.resolve(__dirname, 'js'),
  jsSource: path.resolve(__dirname, 'js/src'),
  css: path.resolve(__dirname, 'css'),
  scss: path.resolve(__dirname, 'resources/scss')
};

/**
 * Entry points configuration
 */
const entries = {
  // JavaScript entries
  js: {
    formidable_blocks: './js/src/blocks.js',
    formidable_overlay: './js/src/overlay.js',
    'form-templates': './js/src/form-templates/index.js',
    formidable_dashboard: './js/src/dashboard.js',
    'onboarding-wizard': './js/src/onboarding-wizard/index.js',
    'addons-page': './js/src/addons-page/index.js',
    formidable_styles: './js/src/admin/styles.js',
    'formidable-color-picker': './js/src/color-picker/index.js',
    formidable_admin: './js/src/admin/admin.js',
    frm_testing_mode: './js/src/frm_testing_mode.js',
    'formidable-settings-components': './js/src/settings-components/index.js',
    'formidable-web-components': './js/src/web-components/index.js',
    'welcome-tour': './js/src/welcome-tour',
  },
  // SCSS entries
  scss: {
    frm_admin: './resources/scss/admin/frm_admin.scss',
    'admin/frm-settings-components': './resources/scss/admin/frm-settings-components.scss',
    'admin/frm-legacy-paypal': './resources/scss/admin/frm-legacy-paypal.scss',
    font_icons: './resources/scss/font_icons.scss',
    // Dynamically generated web component SCSS files
    frm_testing_mode: './resources/scss/test-mode/frm_testing_mode.scss',
    'admin/welcome-tour': './resources/scss/admin/welcome-tour.scss',
    ...webComponentScssFiles
  }
};

/**
 * Shared configuration used in both configs
 */
const sharedConfig = {
  mode: isDevelopment ? 'development' : 'production',
  devtool: isDevelopment ? 'source-map' : undefined,
  resolve: {
    extensions: ['.json', '.js', '.jsx', '.scss', '.css'],
    modules: [paths.jsSource, 'node_modules'],
    alias: {
      core: path.resolve(paths.jsSource, 'core'),
      css: paths.css
    }
  },
  externals: {
    jquery: 'jQuery',
    $: 'jQuery'
  },
  optimization: {
    // Scope hoisting renames the `__` locals of concatenated modules, which hides strings from make-pot.
    concatenateModules: false,
    minimizer: [
      // Keep i18n function names and translator comments so translate.wordpress.org can extract strings from the built files.
      new TerserPlugin( {
        terserOptions: {
          compress: {
            // Prevents folding `a ? __( x ) : __( y )` into `__( a ? x : y )`, which make-pot cannot read.
            conditionals: false
          },
          mangle: {
            reserved: [ '__', '_n', '_nx', '_x' ]
          },
          format: {
            comments: /translators:/i
          }
        }
      } )
    ]
  }
};

/**
 * Sass loader configuration with shared options
 */
const sassLoaderOptions = {
  // Reuse one sass-embedded compiler process for every file.
  api: 'modern-compiler',
  sassOptions: {
    // The SCSS still uses @import. Hide its deprecation notice until it moves to @use.
    silenceDeprecations: [ 'import' ],
    includePaths: [
      paths.scss,
      paths.css,
      __dirname // Root directory for absolute paths
    ]
  }
};

/**
 * JavaScript specific config
 */
const jsConfig = {
  ...sharedConfig,
  name: 'js',
  entry: entries.js,
  output: {
    filename: '[name].js',
    path: paths.js,
    chunkFormat: false,
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        include: /js/,
        use: [{ loader: 'babel-loader' }]
      },
      {
        test: /\.svg$/,
        use: ['@svgr/webpack']
      },
      {
        test: /\.css$/i,
        use: ['style-loader', 'css-loader'],
        exclude: /-component\.css$/
      },
	  {
		test: /-component\.css$/i,
		use: ['raw-loader']
	  },
      {
        test: /\.scss$/,
        use: [
          'style-loader',
          {
            loader: 'css-loader',
            options: { url: false }
          },
          {
            loader: 'sass-loader',
            options: sassLoaderOptions
          }
        ]
      }
    ]
  }
};

/**
 * SCSS/CSS specific config
 */
const cssConfig = {
  ...sharedConfig,
  name: 'css',
  entry: entries.scss,
  plugins: [
    // Prevents JS files from being generated for SCSS entries
    new RemoveEmptyScriptsPlugin(),
    new MiniCssExtractPlugin({
      filename: '../css/[name].css'
    })
  ],
  module: {
    rules: [
      {
        test: /\.scss$/,
        use: [
          MiniCssExtractPlugin.loader,
          {
            loader: 'css-loader',
            options: {
              url: false,
              sourceMap: isDevelopment
            }
          },
          'css-unicode-loader', // Handle unicode escape sequences in CSS
          {
            loader: 'sass-loader',
            options: sassLoaderOptions
          }
        ]
      }
    ]
  },
  output: {
    path: paths.css,
    filename: '[name].js' // This won't be generated due to RemoveEmptyScriptsPlugin
  }
};

/**
 * Export both configurations
 */
module.exports = [jsConfig, cssConfig];
