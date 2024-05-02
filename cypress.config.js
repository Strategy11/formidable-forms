const { defineConfig } = require("cypress");

module.exports = defineConfig({
  fixturesFolder: "tests/cypress/fixtures",
  defaultCommandTimeout: 10000,
  e2e: {
    baseUrl: 'http://localhost:8080',
    //baseUrl: 'http://devsite.formidableforms.com:8889',
    supportFile: "tests/cypress/support/e2e.js",
    specPattern: "tests/cypress/e2e/**/*.cy.{js,jsx,ts,tsx}",

    setupNodeEvents(on, config) {
      // implement node event listeners here
    },

    env: {

    }
  },
});
