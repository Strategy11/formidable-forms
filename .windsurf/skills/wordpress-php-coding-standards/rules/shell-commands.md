# Shell Commands

**Impact: HIGH (security)**

Use of shell commands requires caution due to security implications.

## Never Use the Backtick Operator

**Incorrect:**

```php
// Backtick operator is identical to shell_exec()
$output = `ls -la`;
```

**Why:** The backtick operator is equivalent to `shell_exec()`, and most hosts disable this function in `php.ini` for security reasons. It can lead to command injection vulnerabilities if user input is involved.

**If shell commands are absolutely necessary:**
- Use `escapeshellarg()` and `escapeshellcmd()` for any user-provided input
- Prefer WordPress functions that handle this safely when available
- Document why shell access is needed

**Security Note:** Shell command execution should be avoided whenever possible. Always look for safer alternatives using built-in PHP functions or WordPress APIs.
