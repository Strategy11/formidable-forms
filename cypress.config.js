const { defineConfig } = require("cypress");

module.exports = defineConfig({
  fixturesFolder: "tests/cypress/fixtures",

  e2e: {
    supportFile: "tests/cypress/support/e2e.js",
    specPattern: "tests/cypress/e2e/**/*.cy.{js,jsx,ts,tsx}",

    setupNodeEvents(on, config) {
      // implement node event listeners here
    },
  },
});
