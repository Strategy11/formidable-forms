/**
 * Webpack Configuration
 */
const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');
const glob = require( 'glob' );

// Generate web component SCSS entries using glob
const webComponentScssFiles = glob.sync('./js/src/web-components/*/**.scss')
  .reduce( ( scssList, file ) => {
    const match = file.match(/web-components\/(.+)\/(.+)\.scss$/);
    if (match) {
      scssList[`../js/src/web-components/${match[1]}/${match[2]}`] = './' + file;
    }
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
  }
};

/**
 * Sass loader configuration with shared options
 */
const sassLoaderOptions = {
  sassOptions: {
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
