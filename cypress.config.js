const { defineConfig } = require( "cypress" );
const htmlvalidate = require( "cypress-html-validate/plugin" );

module.exports = defineConfig({
  fixturesFolder: "tests/cypress/fixtures",
  defaultCommandTimeout: 4000,
  e2e: {
    baseUrl: 'http://localhost:3000',
    supportFile: "tests/cypress/support/index.js",
    specPattern: "tests/cypress/e2e/**/*.cy.{js,jsx,ts,tsx}",
    video: false,
    retries: {
      runMode: 1,
    },
    setupNodeEvents(on) {
      htmlvalidate.install( on );
    },
    experimentalRunAllSpecs: true
  },
});
