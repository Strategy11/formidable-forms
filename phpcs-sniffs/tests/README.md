Run the standalone sniff fixtures from the Lite checkout:

```sh
php phpcs-sniffs/tests/run.php
```

The runner uses the checkout's Composer dependencies and creates temporary copies. It checks exact violation lines, expected fixed output, PHP syntax, a second fixer pass, and a clean PHPCS pass. It also compares runtime output for adjacent PHP blocks and runtime values for boolean ternaries and conditional HTML attributes.

Each `.inc` file has a matching `.fixed` snapshot. `expected.json` lists the lines that should be reported. Negative examples deliberately include patterns that would change behavior if rewritten.

PHPCS discovers sniff files automatically, even without explicit references in the shared ruleset. The new rules therefore have severity zero in the shared ruleset during rollout. Lite's `phpcs.xml` explicitly enables them at severity five. Add-ons can do the same after reviewing existing violations. Once all consumers are clean, raise the shared severity and remove the per-plugin overrides.

Detection is deliberately conservative:

- HTML helper echo mode applies only to standalone echo statements with an omitted or literal false echo argument.
- Adjacent PHP blocks merge only when they contain no HTML output or a single literal space. Multiline HTML whitespace, indentation, comments at the boundary, short echo tags, HTML attribute contexts, and scope boundaries are left alone.
- Boolean ternaries require opposite boolean literals and a condition known to return a boolean.
- SQL placeholder lists must match the helper's comma and space delimiter exactly. Different delimiters and expressions with side effects are left alone.
- Column queries require explicit column mode, or a direct `wp_list_pluck()` of the single named column selected by `FrmDb::get_results()`. Indexed extraction and multi-column queries are left alone.
- WordPress attribute helpers replace only standalone conditional attribute output inside an HTML tag. The original condition is evaluated as a boolean, preserving strict and loose comparisons. Static attributes, ARIA values, and output containing additional attributes are left alone.
