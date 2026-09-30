# Lighthouse E2E baseline

The dashboard and standalone Contact Us preview run in the existing four-shard Cypress workflow, which requires the `run e2e tests` PR label. Audits write HTML, JSON and Markdown reports to `artifacts/lighthouse/`, append findings to the GitHub job summary, and upload reports as `lighthouse-shard-N` artifacts for 14 days, including on failure.

## Run and review

Use Node 22.19 or newer:

```sh
npm ci --legacy-peer-deps --include=dev
npm run env start
npm run e2e:lighthouse
```

To collect findings without failing on new audit findings:

```sh
npm run e2e:lighthouse:collect
```

Collection never rewrites the baseline. Review the reports, edit `baseline.json` explicitly, then rerun the enforced command. Authentication failures, unexpected redirects, runtime errors, individual audit errors, unsupported browsers and version changes still fail collection. Append `-- --config baseUrl=http://localhost:3002` to either command for a running wp-env instance on another port.

The task uses the [Lighthouse Node API](https://github.com/GoogleChrome/lighthouse/blob/v13.4.1/core/index.js), pinned to 13.4.1, with its desktop preset and the Cypress browser's debugging port. Browser storage is preserved for authentication. The old `cypress-audit` dependency ran Lighthouse 8 despite a separate, unused Lighthouse 13 dependency. The legacy adapter has been removed.

The preview reuses the fixture helper and reads the actual Preview link from the forms list. Starter and imported forms can have different keys. The spec clears WordPress's admin view-transition stylesheet before navigating into the standalone preview to avoid a Chrome rejection. The audited preview is unchanged. Audit retries are disabled. Both full-suite commands and the Lighthouse-only command select Chrome.

## Measured results

Measured on September 30, 2026 in a fresh, separate wp-env installation with Lite only, WordPress 7.1.2, Node 22.22.2, Chrome 154 headless and Lighthouse 13.4.1. The viewport is desktop 1350 by 940. These are local measurements. Reports from the existing environment are kept in `artifacts/lighthouse/existing-env/`. The first Linux CI run still needs review.

| Page | Performance | Accessibility | Best practices | SEO |
| --- | ---: | ---: | ---: | ---: |
| Dashboard | 48 to 53 | 96 | 77 | 77 |
| Form preview | 98 to 100 | 92 | 100 | 45 |

The original dashboard test passed with every category threshold at zero. The original preview failed with a 404 because it assumed a missing form key. A fresh installation also exposed a cold login-session validation timeout.

## Accepted findings

The baseline contains 27 dashboard exceptions and 13 preview exceptions. Each audit still runs and its findings remain visible. Exceptions accept an audit ID for one page, so additional affected elements within that same audit can be hidden. Inspect full reports periodically and remove exceptions as fixes land. The exact IDs and reasons are in `baseline.json`.

Dashboard accessibility findings include the Upgrade menu contrast, the welcome banner heading order, inbox links distinguished only by color, and WordPress core's Collapse Menu accessible-name mismatch. Best-practice findings include YouTube cookies and DevTools issues, plus missing core and third-party source maps. SEO findings include missing descriptions, generic link text and unavailable test-site robots.txt. Performance findings cover document latency, caching, font loading, LCP discovery and phases, request chains, render blocking, script execution, unused assets, image dimensions and back/forward caching.

The preview lacks a main landmark and its default Submit button has insufficient contrast. It also has caching, request-chain and render-blocking findings. Repeat runs exposed intermittent document latency, LCP phase delays, a 2002 ms server response and the active Twenty Twenty-Five theme Manrope font blocking text. The preview deliberately sends `x-robots-tag: noindex`, so its low SEO score should not be interpreted as the SEO score of a published form page.

Six timing metrics are informational while CI variance is measured: FCP, LCP, Speed Index, TTI, TBT and Max Potential FID. Category scores are recorded rather than enforced. Other performance audits, including layout shift, reject new findings. Worsening scores within an accepted audit do not currently fail the test.

## Fix priorities

These estimates come from audit evidence and source inspection.

| Priority | Finding | Proposed fix and source | Effort and limits |
| --- | --- | --- | --- |
| 1 | Banner image lacks height | Add dimensions preserving its aspect ratio in `classes/views/shared/get-free-templates-banner.php`. The asset is 208 by 170 and currently specifies only width 100. | Very small. Check responsive sizing and other uses of the shared banner. |
| 2 | Welcome heading skips levels | Use a sequential heading in `classes/views/dashboard/templates/notification-banner.php` and preserve its appearance in `css/admin/dashboard.css`. | Small. Check the complete heading order and margins. |
| 3 | Upgrade menu contrast is 3.06:1 | Darken its green background. Source: `resources/scss/font_icons.scss`. White text needs 4.5:1. | Small. Rebuild assets and check hover, focus, Lite and Pro states. |
| 4 | Inter font can hide text | Add `font-display: swap` in `resources/scss/admin/base/typography/_font.scss`. | Very small. Third-party fonts can keep the audit failing. Check layout shift. |
| 5 | Preview lacks a main landmark | Add a main landmark around the content in `classes/views/frm-entries/direct.php`. | Small. Check standalone and embedded preview behavior. |
| 6 | Inbox links rely only on color | Add visible link styling to paragraph links in the dashboard inbox message body. Source: `css/admin/dashboard.css`. | Small. Limit the change to message text and check hover and focus. |
| Later | Submit button contrast is 2.91:1 | Revisit white text on the default blue button in `classes/models/FrmStyle.php`. | Small code change with a visual product decision. Saved and custom styles require separate consideration. |
| Later | Imported form name label | Inspect hidden subfield labels and their associations in `classes/models/fields/FrmFieldName.php` and its combo-field rendering. | Moderate investigation. Preserve intentionally hidden labels and test Lite and Pro layouts. |
| Later | Immediate YouTube embed | Load the player after interaction in `classes/views/dashboard/templates/youtube-video.php`. | Moderate. Could improve third-party work and cookie findings. Requires a playback flow. |
| Later | Unused bundles and large DOM | Review editor/component dependencies and the shared SVG pack. | Broader work with dependencies used by admin dialogs and other plugins. |

The existing dashboard scores 66 to 73 for performance, 92 for accessibility, 77 for best practices and 77 for SEO. Its older inbox usage-data message exposes links distinguished only by color, which is accepted in the baseline. The existing environment's imported Contact Us copy scores 84 for accessibility and also fails `label`: its Last name input has a hidden explicit label. Lighthouse 13 reproduces this, so the baseline includes that observed exception too. The fresh starter form passes `label`. Inspect template data, subfield labels and styles before changing shared name-field rendering.

After a fix, collect reports, confirm the relevant audit passes, remove its exception, and rerun the enforced command. Review new CI failures before expanding the baseline.
