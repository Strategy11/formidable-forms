const { defineConfig } = require( "cypress" );
const htmlvalidate = require( "cypress-html-validate/plugin" );
const { lighthouse, prepareAudit } = require( "cypress-audit" );

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
      on('task', {
        log(message) {
          console.log(message)
          return null
        },
        table(message) {
          console.table(message)
          return null
        }
      });
      htmlvalidate.install( on );
      on( 'before:browser:launch', ( browser, launchOptions ) => {
        prepareAudit( launchOptions );
        return launchOptions;
      } );
      on( 'task', {
        lighthouse: lighthouse( results => {
          // Printed so the CI log carries the raw scores even when a test
          // passes - the only place to see them without recording a video.
          console.log( JSON.stringify( results.lhr.categories ) );
        } ),
      } );
    },
    experimentalRunAllSpecs: true
  },
});
