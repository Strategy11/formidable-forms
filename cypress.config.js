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
    async setupNodeEvents(on) {
      const { reportLighthouse } = await import( './tests/lighthouse/report.mjs' );
      const { default: lighthouse, desktopConfig } = await import( 'lighthouse' );
      let lighthousePort;
      on('task', {
        log(message) {
          console.log(message)
          return null
        },
        table(message) {
          console.table(message)
          return null
        },
        accessibilityChecker: require('cypress-accessibility-checker/plugin')
      });
      htmlvalidate.install( on );
      on( 'before:browser:launch', ( browser, launchOptions ) => {
        const debugging = launchOptions.args.find( arg => arg.startsWith( '--remote-debugging-port=' ) );
        lighthousePort = debugging ? Number( debugging.split( '=' )[ 1 ] ) : undefined;
        return launchOptions;
      } );
      on( 'task', {
        async lighthouse( { url, opts } ) {
          if ( ! lighthousePort ) {
            throw new Error( 'Lighthouse requires a Chromium remote debugging port.' );
          }
          const results = await lighthouse( url, { ...opts, port: lighthousePort }, desktopConfig );
          reportLighthouse( results );
          return null;
        },
      } );
    },
    experimentalRunAllSpecs: true
  },
});
