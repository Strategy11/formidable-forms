const { defineConfig } = require("cypress");

module.exports = defineConfig({
  fixturesFolder: "tests/cypress/fixtures",

  e2e: {
    baseUrl: 'localhost:8889',
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
