---
name: wp-browser-testing
description: >
  Browser automation for testing Strategy11 Labs WordPress plugins via the
  Claude in Chrome extension. Load when verifying a plugin after install,
  testing admin UI, checking frontend output, submitting forms, reading a
  remote site's debug.log, or debugging why a feature isn't working. Contains
  ready-to-run JavaScript snippets for each stage of the Labs plugin test cycle
  plus the known browser-tool constraints.
---

# WordPress Browser Testing — Strategy11 Labs

The Claude in Chrome extension runs these checks against Nathanael's own
Formidable test site, in his browser, at his request. Once the plugin is
installed the functional test cycle below runs end to end; he picks the browser
at the start and can stop it at any point.

---

## Session start

1. `list_connected_browsers` → ask the user which to use → `select_browser`.
2. `tabs_context_mcp({ createIfEmpty: true })` before any page action.
3. Use standalone `navigate` for the first hop to an external domain
   (browser_batch enforces domain permission more strictly).

## How to write probes that work

**Assert, don't dump.** Every snippet below returns a derived value — a boolean,
a count, a short label — rather than raw page content. That is what makes a
readable test result, and it keeps session identifiers and URL parameters out of
the transcript. When a probe needs to reference a script or resource, reduce it
to a pathname or test it with a regex instead of returning the full `src`.

Other confirmed behaviours of the tooling:

- Returning raw base64 binary isn't supported, so there is no browser→server
  file transfer. Move builds via download + re-upload, or a public URL.
- `file_upload` to the WP plugin-upload form isn't permitted — Nathanael
  installs the zip himself, then the browser drives the rest.
- Chrome silently drops the 2nd+ rapid programmatic download. One at a time,
  waiting for each result.
- Top-level `await` is not allowed; wrap in `(async () => { ... })()`.
- The MCP can wedge (Windows background throttling). Recovery: fully quit Claude
  Desktop from the system tray and reopen.

---

## Plugin / version checks

```javascript
// Installed version from the plugins list (navigate to plugins.php first):
const row = [...document.querySelectorAll('tr')]
  .find(r => r.textContent.includes('My Plugin') && r.textContent.includes('Strategy11'));
row?.querySelector('.plugin-version-author-uri, .second')?.textContent?.trim().slice(0,60);
```

## Frontend config verification

```javascript
// On a page with the form — is the inline data present and correct?
typeof myPluginData !== 'undefined'
  ? Object.keys(myPluginData.config || {}).join(',')
  : 'myPluginData NOT DEFINED — assets did not enqueue';
```

```javascript
// Confirm script order: inline data MUST come before the external script.
const s = [...document.querySelectorAll('script')];
const dataIdx = s.findIndex(x => !x.src && x.textContent.includes('myPluginData'));
const jsIdx   = s.findIndex(x => x.src && x.src.includes('my-plugin'));
'dataBeforeScript: ' + (dataIdx < jsIdx);
```

## Form submission + post-submit assertion

```javascript
// Real button click (drives the full Formidable pipeline — a raw fetch may skip
// validation and never reach frm_after_create_entry):
document.querySelectorAll('input[type="text"]:not([class*="frm_verify"])')
  .forEach((f,i) => { f.value = 'Test'+i; });
document.querySelector('button.frm_final_submit, button[type="submit"]').click();
```

```javascript
// Test instrumentation: wrap XHR so the submit response can be asserted on.
// Formidable submits over admin-ajax, so pass/errors are only visible in the
// response body. Install this BEFORE submitting; it lasts for the page load.
window.__resp = null;
const o = XMLHttpRequest.prototype.open, s = XMLHttpRequest.prototype.send;
XMLHttpRequest.prototype.open = function(m,u){ this._u=u; return o.apply(this,arguments); };
XMLHttpRequest.prototype.send = function(){
  this.addEventListener('load', () => {
    if (this._u?.includes('admin-ajax')) { try { window.__resp = JSON.parse(this.responseText); } catch(e){} }
  });
  return s.apply(this,arguments);
};
// after submit: window.__resp.pass  // must be true for frmFormComplete to fire
```

## Reading the test site's debug.log (fatal diagnosis)

When the test site is remote (filesystem MCP can't reach it), read the tail of
its own debug log in-page. This pinpoints the exact failing file+line of a
critical error that code inspection alone may miss.

Keep it to the last few lines and reduce server paths to `<path>` — the goal is
the error and its location, not the host's directory layout:

```javascript
(async () => {
  const r = await fetch('/wp-content/debug.log');
  const t = await r.text();
  return t.trim().split('\n').slice(-15).join('\n')
    .replace(/[A-Za-z]:\\[^\s]+|\/[^\s]+\.php/g, '<path>').replace(/\?[^\s'"]+/g, '');
})()
```

Enabling it first (if absent): add to wp-config.php
`define('WP_DEBUG', true); define('WP_DEBUG_LOG', true);`

## Full post-install test sequence

```
1. navigate → plugins.php; assert version active
2. navigate → form builder; assert custom tab renders
3. navigate → frontend page; assert myPluginData defined + config correct
4. submit form (real click); capture AJAX response; assert pass:true
5. assert post-submit effect (DOM nodes / cookie / overlay)
6. read debug.log tail; assert no new fatals
7. repeat for: AJAX form, non-AJAX form, preview URL, preview &theme=1
```

---

## Verifying View / Views-editor rendering (don't trust the pipeline, observe it)

When a feature renders through a Formidable View, verify BOTH surfaces in the
browser before declaring success — they behave differently (see frm-gotchas ->
"Maps/JS in Formidable Views"):

- **Front end** (`/frm_display/<view>/`): does the inline/external script execute,
  is the library loaded, did the container initialise?
- **Views editor preview** (`wp-admin/?page=formidable-views-editor&view=<id>`):
  the preview is injected via JS, so inline scripts never run and only
  form/admin-path assets are present. Confirm the external initializer's asset
  URL is actually on the editor page.

Useful read-only probes. As above, each returns a derived boolean or count
rather than raw `src`/`href` — reduce to a pathname or test with a regex:

```js
// Which of the add-on's scripts actually loaded (paths only, no query string):
[...document.querySelectorAll('script[src]')]
  .map(n => { try { return new URL(n.src).pathname; } catch(e){ return ''; } })
  .filter(p => /my-plugin/.test(p));

// Did a specific container initialise? (Leaflet example)
(function(){ var c=document.querySelector('.my-map');
  return JSON.stringify({ present:!!c, initialized: c?c.classList.contains('leaflet-container'):false,
    tiles: c?c.querySelectorAll('img.leaflet-tile').length:0 }); })();

// Confirm the library is available and a manual init works (isolates "script
// didn't run" from "library missing"):
typeof window.L !== 'undefined';
```

Read the console too: a recurring SyntaxError across loads that disappears on
the latest load is your before/after signal that the installed build changed.

<!-- skills-sync: 2026-08-06 skill-language-reframe -->
