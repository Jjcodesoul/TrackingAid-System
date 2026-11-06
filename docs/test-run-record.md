# Test Run Record

## Latest refinement verification — 2026-11-07

- **Command:** `php -d extension=pdo_sqlite -d extension=sqlite3 vendor/bin/phpunit`
- **Result:** 106 tests passed, 823 assertions; exit code 0 (10.4 seconds).
- **Affected-module retest after the final SKU help-text change:** 33 tests passed, 220 assertions; exit code 0.
- **Build:** `npm.cmd run build` passed.
- **Blade cache:** `php artisan view:cache` passed.
- **Routes:** `php artisan route:list --except-vendor` passed; the separate `/storage-locations` management route is absent.
- **Diff check:** `git diff --check` passed.
- **Coverage added/updated:** six temporary Mambaling options render consistently in item creation, Stock-In, and Inventory; active location filtering shows only explicitly located batches; SKU format examples including numeric final characters pass; generated SKU output is capped at 12 characters; the old separate storage-location page is no longer exposed; location-specific inventory filtering is covered.
- **Not run:** browser-based visual inspection, browser console, manual authentication/role scenarios, and staging UAT. These require a running browser environment and approved accounts.
- **Database safety:** PHPUnit used isolated in-memory SQLite. Then only `2026_11_07_090000_seed_temporary_mambaling_storage_locations` was applied to the configured application database. A read-only follow-up confirmed all six options are active and explicitly marked temporary; item and stock-batch totals remain 68 each. No records were deleted and no database reset was run.

## Run metadata

- **Run date:** 2026-11-06
- **Code under test:** Current working-tree state at time of run; this was not a clean commit checkout.
- **Test plan:** [QA_TEST_CASES.md](../QA_TEST_CASES.md)
- **Database:** PHPUnit configured for isolated SQLite `:memory:` database.
- **Environment note:** PHP's normal CLI configuration did not load `pdo_sqlite`; the bundled DLL was available and loaded for the direct PHPUnit run using `php -d extension=C:\Backend\php\ext\php_pdo_sqlite.dll`.

## Final automated run

Command:

```powershell
php -d extension=C:\Backend\php\ext\php_pdo_sqlite.dll vendor\bin\phpunit
```

Result: **106 tests, 106 passed; 854 assertions; exit code 0.** Duration: approximately 15.0 seconds.

The run included storage-location verification/management, ID-based inventory and stock-in location validation, location-specific release deduction and return placement, inventory report balances split by batch location, request/release/return workflows, report URL signing, request-list UI checks, and ResQOperation API key behavior. The release test confirms stock in another location is not consumed when the selected location is short.

The Laravel `artisan test` wrapper does not inherit a one-off PHP `-d extension=...` option in this environment. The direct PHPUnit command above loaded the installed SQLite driver and ran the complete suite against the isolated in-memory database.

## Other verification

Additional checks on this working tree:

- `npm.cmd run build` — passed.
- `php artisan view:cache` — passed.
- `php artisan route:list -v --path=api` — passed; delivery API routes require Sanctum and active-account middleware, while report and ResQOperation intake routes require API-key middleware.
- `git diff --check` — passed.

The location-specific return behavior is implemented using the source location recorded on its release. Historical source/return locations are left blank when they cannot be established from the original transaction. The location and transaction migrations were exercised by the isolated PHPUnit database; they were not applied to the configured application database during that 2026-11-06 run. The later targeted temporary-options migration was applied on 2026-11-07, as recorded above.

## Read-only location audit

The configured application database was inspected without changing it on 2026-11-06. At that time it had no `storage_locations` rows. `items` and `inventory` each contained 68 location strings: Warehouse A (3), Warehouse B (22), Warehouse C (18), and Equipment Bay (25). All 68 stock batches had a blank `storage_location`. These legacy labels do not prove real Mambaling buildings, so they remain historical/unverified assignments. On 2026-11-07, the six user-specified temporary option labels were added; they are marked as temporary with physical locations pending confirmation.

## Manual cases and operational data

The manual browser, print/PDF, responsive/accessibility, role-session, staging UAT, and DATA-001–DATA-008 reconciliation procedures in `QA_TEST_CASES.md` were **not run or marked passed** in this record. They require browser/staging execution, approved accounts, source records, or an independent reviewer. Record case ID, actual results, evidence, defect reference, environment, and reviewer when executed.

## Retest tracking

| Issue | Resolution | Retest |
| --- | --- | --- |
| Report API URL scheme | Force the configured application scheme when generating the signed URL. | Passed in full suite on 2026-11-06 |
| Released-status UI assertion | Assert the server-rendered `status=Released` filter link used by the interface. | Passed in full suite on 2026-11-06 |
| Duplicate storage-location fixture | Use `firstOrCreate` so tests coexist with predefined locations. | Passed in full suite on 2026-11-06 |
