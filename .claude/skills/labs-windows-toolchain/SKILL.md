---
name: labs-windows-toolchain
description: >
  Windows dev-environment and toolchain setup for Strategy11 Labs. Load when
  composer or the review.sh "Installing review tools" step fails on Nathanael's
  Windows box, when PHP reports "unable to get local issuer certificate" /
  "certificate chain is incomplete" / curl error 60, or when launching bash or
  review.sh from the PowerShell MCP. Covers the PHP-vs-Windows CA bundle
  mismatch, the correct way to test it, and the verified bash-from-PowerShell
  launch pattern.
---

# Windows Toolchain — composer setup, review.sh, bash-from-PowerShell

Nathanael's box: XAMPP PHP at `C:\xampp\php\php.exe` (NOT on PATH) + composer
(`C:\xampp\php\composer.bat`), Git Bash at `C:\Program Files\Git\bin\bash.exe`.
No python, no bash-on-PATH, no WSL. Tests run here via the powershell +
filesystem MCP.

---

## 1. composer / PHP can't verify a certificate Windows already trusts

### Symptom
`composer` (e.g. review.sh "Installing review tools") fails with:
- `SSL certificate problem: unable to get local issuer certificate`
- or `schannel: the certificate chain is incomplete` / curl error 60

Updating `cacert.pem` to the newest Mozilla bundle does **not** fix it. `php -r`
shows `curl.cainfo` / `openssl.cafile` correctly pointed at the bundle, yet it
still fails.

### Root cause — two trust stores that disagree
Windows and PHP do not share a trust store.

Security software on the box (commonly Avast's Web/Mail Shield, or an
enterprise-managed proxy) presents locally-issued certificates signed by a root
that is installed in the **Windows certificate store**. Browsers and anything
using **schannel** read that store, so they verify fine. PHP and composer verify
against the Mozilla **`cacert.pem` file** instead, which by design contains only
public CAs and will never contain a machine-local root.

So a root the machine legitimately trusts is simply invisible to PHP, and
verification fails. This is a configuration mismatch, not a broken certificate.

### Confirm it before changing anything
Run in Git Bash (PATH includes `/c/xampp/php`) and read the **issuer**:

```bash
echo | openssl s_client -connect repo.packagist.org:443 -servername repo.packagist.org 2>/dev/null \
  | openssl x509 -noout -issuer -subject
```

If `issuer=` names local security software (e.g. `CN=Avast Web/Mail Shield Root`)
rather than a public CA (Let's Encrypt, Google Trust, DigiCert), this section
applies. If it names a public CA, the problem is something else — stop here.

**Do NOT use `curl --cacert` as the test on Windows.** Git Bash curl uses the
schannel backend (Windows cert store), so `--cacert` is ignored and the result
tells you nothing about PHP/composer (which use OpenSSL + the cafile).
Test with composer/PHP instead: `composer show -a phpunit/phpunit` (the `-a`
queries packagist; without it composer errors on a missing local composer.json
before hitting the network), or `composer diagnose`.

### Fix — align PHP's CA bundle with the machine's trust store
Copy the root Windows already trusts into the bundle PHP reads, so the two
stores agree. Certificate verification stays on throughout; this only makes PHP
aware of a root the machine's administrator has already approved.

```powershell
$dst  = 'C:\xampp\php\cacert.pem'
$root = Get-ChildItem Cert:\LocalMachine\Root, Cert:\CurrentUser\Root -ErrorAction SilentlyContinue |
        Where-Object { $_.Subject -match 'Avast' } | Sort-Object Thumbprint -Unique
foreach ($c in $root) {
    $b64 = [Convert]::ToBase64String($c.RawData,'InsertLineBreaks')
    Add-Content $dst -Encoding ascii -Value "`n# $($c.Subject)`n-----BEGIN CERTIFICATE-----`n$b64`n-----END CERTIFICATE-----"
}
```

Match on whatever name the issuer line above actually reported. Then point
php.ini at that bundle (back it up first; convention:
`php.ini.frmec-bak-<timestamp>`):

```
curl.cainfo  = "C:\xampp\php\cacert.pem"
openssl.cafile = "C:\xampp\php\cacert.pem"
```

Belt-and-suspenders for composer specifically: `export COMPOSER_CAFILE=/c/xampp/php/cacert.pem`
before invoking it.

Verify with `composer show -a phpunit/phpunit` (should print package metadata)
— NOT with `curl --cacert`.

### Alternative, if Nathanael prefers it
Turning off the HTTPS-scanning feature in the security software's own UI removes
the mismatch at the source, and the stock Mozilla bundle then works unmodified.
That is a change to his machine's security settings — his call to make, in that
UI, not something to do for him.

### Secondary php.ini gotcha seen alongside this
A duplicate OpenSSL load (`extension=openssl` AND `extension=php_openssl.dll`)
throws a startup warning. Comment the `php_openssl.dll` line.

---

## 2. Running bash / review.sh from the PowerShell MCP (verified pattern)

The PowerShell host **cannot** run bash inline — `& bash.exe -c '...'` fails with
"Cannot run a document in the middle of a pipeline". Instead:

1. Write an **LF-only** runner script to disk (CRLF breaks bash):
   ```powershell
   [IO.File]::WriteAllText($path, ($lines -join "`n") + "`n", (New-Object Text.UTF8Encoding($false)))
   ```
   Inside the script, set PATH and redirect all output to a log:
   ```bash
   exec > <work-dir>/review.log 2>&1
   export PATH=/c/xampp/php:$PATH
   export NO_COLOR=1; export TERM=dumb; export COMPOSER_NO_INTERACTION=1
   ```
2. Launch it (async for slow installs — composer tool install is the slow part):
   ```powershell
   Start-Process -FilePath 'C:\Program Files\Git\bin\bash.exe' `
     -ArgumentList '<work-dir>/run.sh' -Wait -NoNewWindow
   ```
3. Read results from the log with `Get-Content <log> -Raw`.

review.sh installs its tools (phpcs/phpcbf/phpstan via composer) on first run
into `<strategy11-labs-tools>/scripts/review-tools/vendor/bin`. That composer
step is exactly what the CA bundle mismatch above breaks — if "Installing
review tools" fails, go to section 1.

### MCP bridge stability
The powershell/filesystem MCP can wedge on focus loss (Windows EcoQoS power
throttling). Probe with a trivial `'alive'` command before heavy ops; prefer the
async powershell job tools for anything slow. If a powershell/filesystem tool is
simply *not found*, the connector is down — say so, do not silently fall back to
the container as a substitute for a real Windows run.

---

## Quick reference

| Symptom | Cause | Action |
|---|---|---|
| composer "unable to get local issuer certificate" | PHP cafile missing a machine-trusted root | §1: align cacert.pem with the Windows store |
| `curl --cacert` passes but composer still fails | curl uses schannel, not the cafile | Test with composer/PHP, not curl |
| `& bash -c` fails in PowerShell | can't run bash inline | §2: write LF .sh, Start-Process |
| review.sh "Installing review tools" fails | composer CA bundle (see above) | §1 |
| MCP tool hangs ~4 min then errors | EcoQoS focus-loss throttling | probe 'alive' first; use async job tools |

<!-- skills-sync: 2026-08-06 skill-language-reframe -->
