#!/usr/bin/env node
const fs = require("fs-extra");
const { execSync } = require("child_process");
const npmAddScript = require("npm-add-script");

const destPath = process.cwd();
const sourcePath = __dirname.concat("/src");
const testsDir = "/tests";
const binDir = "/tests/bin";
const cypressDir = "/tests/cypress";

const testsPath = destPath.concat(testsDir);
const testsBinPath = destPath.concat(binDir);
const testsCypressPath = destPath.concat(cypressDir);

// Check if NPM is initialized
try {
  fs.accessSync(destPath.concat("/package.json"));
} catch (err) {
  console.error("package.json does not exist. Please run npm init first.");
  process.exit(1);
}

try {
  execSync(
    "npm install --save-dev cypress@13 @wordpress/env @10up/cypress-wp-utils",
    { stdio: "inherit" }
  );
} catch (e) {
  console.error(e);
  process.exit(1);
}

const scripts = {
  "cypress:open": "cypress open --config-file tests/cypress/config.js --e2e --browser chrome",
  "cypress:run": "cypress run --config-file tests/cypress/config.js",
  "env": "wp-env",
  "env:start": "wp-env start",
  "env:stop": "wp-env stop",
  "env:destroy": "wp-env destroy",
  "postenv:start": "./tests/bin/initialize.sh",
};

// Add scripts to package.json
Object.keys(scripts).forEach((k, _) => {
  try {
    npmAddScript({
      key: k,
      value: scripts[k],
    });
  } catch (err) {
    console.error(err);
  }
}, scripts);

// Create /tests directory or use existing one.
fs.access(testsPath, (err) => {
  if (err) {
    console.log("./tests directory does not exist, creating it.");
    fs.mkdir(testsPath);
  } else {
    console.log("./tests directory already exists.");
  }

  fs.access(testsBinPath, (err) => {
    if (err) {
      console.log("./tests/bin directory does not exist, creating it.");
      fs.mkdir(testsBinPath);
    } else {
      console.log("./tests/bin directory already exists.");
    }

    /**
     * Files to copy from src to dest
     * {"filename": chmod (octal number)}
     */
    const filesToCopy = {
      "/.github/workflows/cypress.yml": null,
      "/tests/bin/initialize.sh": 0o755,
      "/tests/bin/set-core-version.js": 0o755,
      "/tests/bin/wp-cli.yml": null,
      "/.wp-env.json": null,
    };

    Object.keys(filesToCopy).forEach((element, _) => {
      const source = sourcePath.concat(element);
      const dest = destPath.concat(element);
      const chmod = filesToCopy[element];

      fs.access(dest, (err) => {
        if (err) {
          // Copy file if not exist.
          fs.copy(source, dest, (err) => {
            if (err) {
              console.error(err);
            } else {
              console.log("." + element.concat(" created."));

              // Optionally set chmod.
              if (null !== chmod) {
                fs.chmod(dest, chmod, (err) => {
                  if (err) {
                    console.error(err);
                  } else {
                    console.log("chmod " + chmod.toString(8) + " ." + element);
                  }
                });
              }
            }
          });
        } else {
          console.log("." + element.concat(" exists, skipping."));
        }
      });
    }, filesToCopy);
  });

  // Copy cypress example test if no tests exist yet.
  fs.access(testsCypressPath, (err) => {
    if (err) {
      fs.copy(sourcePath.concat(cypressDir), testsCypressPath)
        .then(() => {
          console.log("Copied test example to ./tests/cypress");
        })
        .catch((err) => {
          console.error(err);
        });
    } else {
      console.log(
        "./tests/cypress already exists. Skipping copy test example."
      );
    }
  });
});
