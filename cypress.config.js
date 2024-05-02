const { defineConfig } = require("cypress");

module.exports = defineConfig({
  fixturesFolder: "tests/cypress/fixtures",
  defaultCommandTimeout: 10000,
  e2e: {
    //baseUrl: 'http://localhost:8889',
    //baseUrl: 'http://devsite.formidableforms.com:8889',
    supportFile: "tests/cypress/support/index.js",
    specPattern: "tests/cypress/e2e/**/*.cy.{js,jsx,ts,tsx}",
    video: false,
    retries: {
      runMode: 1,
    },
    async setupNodeEvents(on, config) {
			const { loadConfig } = require('@wordpress/env/lib/config');

			const wpEnvConfig = await loadConfig('../..');

			if (wpEnvConfig) {
				const port = wpEnvConfig.env.tests.port || null;

				if (port) {
					config.baseUrl = wpEnvConfig.env.tests.config.WP_SITEURL;
				}
			}

			return config;
		}
  },
});
