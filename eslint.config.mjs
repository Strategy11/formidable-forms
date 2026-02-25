import { FlatCompat } from '@eslint/eslintrc';
import { fileURLToPath } from 'url';
import { dirname } from 'path';
import babelParser from '@babel/eslint-parser';
import reactPlugin from 'eslint-plugin-react';
import jsxA11yPlugin from 'eslint-plugin-jsx-a11y';
import sonarjsPlugin from 'eslint-plugin-sonarjs';
import cypressPlugin from 'eslint-plugin-cypress';
import noJqueryPlugin from 'eslint-plugin-no-jquery';
import compatPlugin from 'eslint-plugin-compat';
import unicornPlugin from 'eslint-plugin-unicorn';
import formidablePlugin from './eslint-rules/index.js';
import globals from 'globals';

const __filename = fileURLToPath( import.meta.url );
const __dirname = dirname( __filename );

const compat = new FlatCompat( {
	baseDirectory: __dirname,
	resolvePluginsRelativeTo: __dirname,
} );

export default [
	// Global ignores (replaces .eslintignore)
	{
		ignores: [
			'**/*.min.js',
			'**/*.js.map',
			'js/formidable_blocks.js',
			'js/formidable_overlay.js',
			'js/form-templates.js',
			'js/formidable_dashboard.js',
			'js/onboarding-wizard.js',
			'js/addons-page.js',
			'js/formidable_styles.js',
			'js/formidable_admin.js',
			'js/bootstrap-multiselect.js',
			'js/formidable-settings-components.js',
			'js/formidable-web-components.js',
			'js/frm_testing_mode.js',
			'js/welcome-tour.js',
			'*.config.js',
			'*.config.mjs',
			'webpack*.js',
			'**/node_modules/**',
			'**/vendor/**',
			'**/venv/**',
			'eslint-rules/**',
			'build/**',
			'coverage/**',
		],
	},

	// WordPress recommended-with-formatting preset
	// @wordpress/eslint-plugin uses legacy config format (no flat/ exports exist).
	// FlatCompat is the official ESLint bridge to use legacy configs in ESLint 9 flat config.
	// See: https://eslint.org/docs/latest/use/configure/migration-guide#using-eslintrc-configs-in-flat-config
	...compat.extends( 'plugin:@wordpress/eslint-plugin/recommended-with-formatting' ),

	// Base config for all JS files
	{
		files: ['**/*.js', '**/*.jsx', '**/*.mjs'],
		languageOptions: {
			parser: babelParser,
			parserOptions: {
				requireConfigFile: false,
				sourceType: 'module',
				babelOptions: {
					presets: ['@babel/preset-env', '@babel/preset-react'],
					plugins: ['@babel/plugin-syntax-jsx'],
				},
				ecmaFeatures: {
					jsx: true,
				},
			},
			globals: {
				...globals.browser,
				...globals.es2021,
				...globals.node,
				...globals.mocha,
				wp: 'readonly',
				wpApiSettings: 'readonly',
				window: 'readonly',
				document: 'readonly',
				cy: 'readonly',
				Cypress: 'readonly',
				expect: 'readonly',
				assert: 'readonly',
				chai: 'readonly',
			},
		},
		plugins: {
			// NOTE: react, jsx-a11y, jsdoc, import, react-hooks are registered by FlatCompat
			// via the WordPress preset chain, do not register them here (would cause
			// ESLint 9 "Cannot redefine plugin" error due to different require() instances).
			sonarjs: sonarjsPlugin,
			cypress: cypressPlugin,
			'no-jquery': noJqueryPlugin,
			compat: compatPlugin,
			unicorn: unicornPlugin,
			formidable: formidablePlugin,
		},
		settings: {
			'import/resolver': {
				webpack: {
					config: './webpack.config.js',
				},
			},
			react: {
				pragma: 'wp',
			},
		},
		rules: {
			// React recommended
			...reactPlugin.configs.recommended.rules,
			// JSX a11y recommended
			...jsxA11yPlugin.configs.recommended.rules,
			// Sonarjs recommended
			...( sonarjsPlugin.configs?.recommended?.rules ?? {} ),
			// Cypress recommended
			...( cypressPlugin.configs?.recommended?.rules ?? {} ),
			// No-jquery deprecated
			...( noJqueryPlugin.configs?.deprecated?.rules ?? {} ),
			// Compat recommended
			...( compatPlugin.configs?.['flat/recommended']?.[0]?.rules ?? compatPlugin.configs?.recommended?.rules ?? {} ),
			// Unicorn all
			...unicornPlugin.configs['flat/all'].rules,

			// Sonar overrides
			'sonarjs/cognitive-complexity': 'off',
			// Add additional Sonar JS rules that are not included in the recommended preset.
			'sonarjs/no-inverted-boolean-check': 'error',

			// Cypress overrides
			'cypress/unsafe-to-chain-command': 'off',
			'cypress/no-assigning-return-values': 'off',

			// Core ESLint rules
			'camelcase': [
				'error',
				{
					properties: 'never',
					allow: [
						'frm_admin_js',
						'frm_stripe_vars',
						'frm_trans_vars',
						'formidable_form_selector',
						'frm_js',
						'frm_password_checks',
						'formidable_block_calculator',
						'formidable_view_selector',
						'frmdates_admin_js',
						'frm_abdn',
					],
				},
			],
			'lines-around-comment': 'off',
			'vars-on-top': 'warn',
			'yoda': 'off',
			'linebreak-style': 'off',
			'object-shorthand': 'error',
			'no-unused-vars': 'off',
			'no-console': 'off',
			'eqeqeq': 'off',
			'no-alert': 'off',
			'no-undef': 'off',
			'no-shadow': 'off',
			'comma-dangle': 'off',
			'arrow-parens': ['error', 'as-needed'],

			// Enforce frm-javascript.md patterns
			'no-var': 'warn',
			'prefer-const': 'warn',
			'prefer-destructuring': ['warn', {
				'array': true,
				'object': true,
			}, {
				'enforceForRenamedProperties': false,
			}],
			'prefer-spread': 'warn',
			'prefer-rest-params': 'error',
			'prefer-template': 'warn',
			'no-eval': 'error',
			'no-implied-eval': 'error',
			'no-new-func': 'error',
			'no-extend-native': 'error',
			'one-var': ['error', 'never'],
			'default-param-last': 'warn',

			// WordPress overrides
			'@wordpress/no-global-active-element': 'off',
			// Disabled: use context.* APIs removed in ESLint 9 flat config (getScope, getAncestors,
			// getDeclaredVariables, getCommentsBefore). Re-enable when @wordpress/eslint-plugin
			// migrates to sourceCode.* APIs for ESLint 9 support.
			'@wordpress/no-unused-vars-before-return': 'off',
			'@wordpress/data-no-store-string-literals': 'off',
			'@wordpress/react-no-unsafe-timeout': 'off',
			'@wordpress/i18n-translator-comments': 'off',

			// Prettier
			'prettier/prettier': 'off',

			// JSDoc
			'jsdoc/check-tag-names': 'error',

			// React overrides
			'react/display-name': 'off',
			'react/jsx-curly-spacing': [
				'error',
				{
					when: 'always',
					children: true,
				},
			],
			'react/jsx-equals-spacing': 'error',
			'react/jsx-indent': ['error', 'tab'],
			'react/jsx-indent-props': ['error', 'tab'],
			'react/jsx-key': 'error',
			'react/jsx-tag-spacing': 'error',
			'react/no-children-prop': 'off',
			'react/no-find-dom-node': 'warn',
			'react/prop-types': 'off',
			'react/jsx-no-target-blank': 'off',

			// Sonar overrides
			'sonarjs/no-duplicate-string': 'off',
			'sonarjs/prefer-single-boolean-return': 'off',
			'sonarjs/no-collapsible-if': 'off',
			'sonarjs/no-duplicated-branches': 'off',
			'sonarjs/no-nested-template-literals': 'off',

			// No-jquery overrides
			'no-jquery/no-ready-shorthand': 'off',
			'no-jquery/no-ajax-events': 'error',
			'no-jquery/no-animate-toggle': 'error',
			'no-jquery/no-bind': 'error',
			'no-jquery/no-box-model': 'error',
			'no-jquery/no-browser': 'error',
			'no-jquery/no-camel-case': 'error',
			'no-jquery/no-constructor-attributes': 'error',
			'no-jquery/no-contains': 'error',
			'no-jquery/no-deferred': 'error',
			'no-jquery/no-delegate': 'error',
			'no-jquery/no-error': 'error',
			'no-jquery/no-escape-selector': 'error',
			'no-jquery/no-extend': 'error',
			'no-jquery/no-fx-interval': 'error',
			'no-jquery/no-global-eval': 'error',
			'no-jquery/no-has': 'error',
			'no-jquery/no-hold-ready': 'error',
			'no-jquery/no-is-array': 'error',
			'no-jquery/no-is-empty-object': 'error',
			'no-jquery/no-is-function': 'error',
			'no-jquery/no-is-numeric': 'error',
			'no-jquery/no-is-plain-object': 'error',
			'no-jquery/no-is-window': 'error',
			'no-jquery/no-load': 'error',
			'no-jquery/no-load-shorthand': 'error',
			'no-jquery/no-map': 'error',
			'no-jquery/no-map-collection': 'error',
			'no-jquery/no-map-util': 'error',
			'no-jquery/no-merge': 'error',
			'no-jquery/no-node-name': 'error',
			'no-jquery/no-noop': 'error',
			'no-jquery/no-now': 'error',
			'no-jquery/no-other-utils': 'error',
			'no-jquery/no-param': 'error',
			'no-jquery/no-parse-json': 'error',
			'no-jquery/no-parse-xml': 'error',
			'no-jquery/no-proxy': 'error',
			'no-jquery/no-selector-prop': 'error',
			'no-jquery/no-sub': 'error',
			'no-jquery/no-trim': 'error',
			'no-jquery/no-type': 'error',
			'no-jquery/no-unique': 'error',
			'no-jquery/no-when': 'error',

			// Unicorn overrides
			'unicorn/filename-case': 'off',
			'unicorn/prefer-query-selector': 'off',
			'unicorn/no-null': 'off',
			'unicorn/prefer-module': 'off',
			'unicorn/explicit-length-check': 'off',
			'unicorn/better-regex': 'off',
			'unicorn/no-keyword-prefix': 'off',
			'unicorn/no-array-for-each': 'off',
			'unicorn/prevent-abbreviations': 'off',
			'unicorn/no-this-assignment': 'off',
			'unicorn/no-document-cookie': 'off',
			'unicorn/prefer-number-properties': 'off',
			'unicorn/prefer-spread': 'off',
			'unicorn/consistent-function-scoping': 'off',
			'unicorn/prefer-export-from': 'off',
			'unicorn/no-array-callback-reference': 'off',
			'unicorn/prefer-ternary': 'off',
			'unicorn/no-for-loop': 'off',
			'unicorn/no-array-reduce': 'off',
			'unicorn/prefer-at': 'off',
			'unicorn/consistent-destructuring': 'off',
			'unicorn/prefer-string-slice': 'off',
			'unicorn/catch-error-name': 'off',
			'unicorn/no-static-only-class': 'off',
			'unicorn/prefer-optional-catch-binding': 'off',
			'unicorn/no-lonely-if': 'off',
			'unicorn/prefer-set-has': 'off',
			'unicorn/prefer-switch': 'off',
			'unicorn/no-useless-switch-case': 'off',
			'unicorn/prefer-prototype-methods': 'off',
			// Fix this soon
			'unicorn/prefer-dom-node-dataset': 'off',
			// Look more into this one.
			'unicorn/numeric-separators-style': 'off',
			// Look into this
			'unicorn/no-unsafe-regex': 'off',
			// Consider this one.
			'unicorn/prefer-regexp-test': 'off',
			'unicorn/prefer-logical-operator-over-ternary': 'off',
			// Maybe fix
			'unicorn/prefer-dom-node-text-content': 'off',
			'unicorn/empty-brace-spaces': 'off',
			// Probably fix
			'unicorn/no-array-push-push': 'off',
			'unicorn/prefer-array-find': 'off',
			'unicorn/prefer-add-event-listener': 'off',
			'unicorn/text-encoding-identifier-case': 'off',
			'unicorn/prefer-string-replace-all': 'off',
			'unicorn/no-useless-promise-resolve-reject': 'off',

			// Whitespace cleanup
			'no-trailing-spaces': 'error',
			'no-multiple-empty-lines': ['error', { max: 1, maxEOF: 1, maxBOF: 0 }],

			// TODO: New breaking changes after updating to look into.
			'no-jquery/no-sizzle': 'off',
			'sonarjs/anchor-precedence': 'off',
			'sonarjs/class-name': 'off',
			'sonarjs/concise-regex': 'off',
			'sonarjs/constructor-for-side-effects': 'off',
			'sonarjs/duplicates-in-character-class': 'off',
			'sonarjs/empty-string-repetition': 'off',
			'sonarjs/inconsistent-function-call': 'off',
			'sonarjs/no-commented-code': 'off',
			'sonarjs/no-dead-store': 'off',
			'sonarjs/no-ignored-exceptions': 'off',
			'sonarjs/no-implicit-global': 'off',
			'sonarjs/no-invariant-returns': 'off',
			'sonarjs/no-nested-assignment': 'off',
			'sonarjs/no-nested-conditional': 'off',
			'sonarjs/no-nested-functions': 'off',
			'sonarjs/regex-complexity': 'off',
			'sonarjs/slow-regex': 'off',
			'sonarjs/todo-tag': 'off',
			'sonarjs/unused-import': 'off',
			'unicorn/consistent-existence-index-check': 'off',
			'unicorn/no-negated-condition': 'off',
//			'unicorn/no-typeof-undefined': 'error',
			'unicorn/prefer-global-this': 'off',
			'unicorn/prefer-string-raw': 'off',
			'unicorn/switch-case-braces': 'off',

			// Custom Formidable rules
			'formidable/prefer-strict-comparison': 'error',
			'formidable/no-redundant-undefined-check': 'error',
			'formidable/prefer-includes': 'error',
			'formidable/no-typeof-undefined': 'error',
			'formidable/no-optional-chaining-queryselectorall': 'error',
			'formidable/no-repeated-selector': 'warn',
			'formidable/prefer-document-fragment': 'warn',

			// Import rules
			'import/no-default-export': 'warn',
		},
	},

	// Override for js/formidable.js
	{
		files: ['js/formidable.js'],
		rules: {
			'no-jquery/no-find': 'error',
			'no-jquery/no-visibility': 'error',
			'no-jquery/no-slide': 'error',
			'no-jquery/no-css': 'error',
			'no-jquery/no-each': 'error',
			'no-jquery/no-append-html': 'error',
			'no-jquery/no-animate': 'error',
			'no-jquery/no-prop': 'error',
			'no-jquery/no-filter': 'error',
			'no-jquery/no-data': 'error',
			'no-jquery/no-parents': 'error',
			'no-jquery/no-val': 'error',
			'no-jquery/no-serialize': 'error',
			'no-jquery/no-class': 'error',
			'no-jquery/no-closest': 'error',
			'no-jquery/no-ajax': 'error',
			'no-jquery/no-fade': 'error',
			'no-jquery/no-is': 'error',
		},
	},
];
