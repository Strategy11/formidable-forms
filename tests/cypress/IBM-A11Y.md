# IBM accessibility baseline

`fixtures/ibm-a11y-baseline.json` lists every scanned page by the label used in
`cy.checkIbmAccessibility()`. Each page contains only its reviewed rule allowances.
An empty object means no confirmed violations are allowed on that page.

The baseline was reviewed from all twelve page scans on October 2, 2026, with
only Lite active in wp-env on WordPress 7.1.2. It records 51 page/rule allowances
covering 743 confirmed findings under the existing IBM policy. The WordPress
sidebar collapse-button issue is identified separately in each affected page's
reason. The styles editor has its own label/name findings.

All IBM rules continue to run under the `IBM_Accessibility` policy in
`.achecker.yml`. Confirmed `violation` findings fail unless the page allows that
rule, within its recorded count when a limit is present. Potential violations
remain informational.
Allowances do not affect axe tests or other pages. Missing page entries and
invalid allowances fail rather than silently skipping checks.

## Capture current findings

Run from the plugin root with the Lite wp-env site running:

```sh
npm run e2e:run -- --spec 'tests/cypress/e2e/admin-a11y.cy.js,tests/cypress/e2e/form-preview-a11y.cy.js'
```

Each IBM scan writes all non-pass findings to
`tests/cypress/reports/ibm-a11y/<page-label>.json` before asserting. This includes
allowed violations and potential violations. Assertion messages include the
unexpected rule IDs, messages and DOM paths in CI output. Retry scans use unique
IBM labels while retaining the same page allowance and report filename.

Review each page's confirmed violations. For an existing rule with a stable
number of affected controls, record the count and a specific explanation in the
fixture, for example:

```json
{
  "formidable-dashboard": {
    "input_label_exists": {
      "reason": "Describe the observed input and why its label needs fixing.",
      "maxViolations": 1
    }
  }
}
```

This example describes the format, not an observed dashboard issue. Do not copy
axe rule IDs or allow potential violations. No command automatically accepts
findings or rewrites the baseline. Verify all twelve page reports were produced,
then rerun both specs after reviewing the allowances.

Repeated gallery cards and form-row labels depend on live catalog content or
fixture data. These allowances omit `maxViolations` and record an informational
`observedViolations` count instead. Their reasons explain the variable content.
The named rule is allowed on that page regardless of the count. Other rules and
pages remain enforced. Do not cap counts controlled by remote APIs.

## Fix one issue at a time

Choose a page and rule from the fixture, fix the markup, and rerun its spec.
Remove the allowance when that rule has no remaining confirmed violations.
Lower `maxViolations` when a partial fix reduces the number of findings in a
count-limited allowance. Counts may decrease without failing the suite, but
unlisted rules, violations on another page and counts above a limit fail.
Uncapped allowances skip the named rule on that page. A count allowance cannot
detect one issue replacing another under the same rule, so review DOM paths when
editing affected markup. Remove the allowance after fixing the whole rule.

The enforcement regression spec can run without WordPress or the IBM engine:

```sh
npm run e2e:run -- --config baseUrl=null,supportFile=false --spec tests/cypress/e2e/ibm-a11y-baseline.cy.js
```
