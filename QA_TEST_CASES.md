# TrackingAid QA Test Plan

This document defines repeatable automated and manual acceptance checks for inventory, reports, request workflows, data accuracy, and UI/UX. The fixtures in this plan are synthetic. They verify calculations and behavior; they are not evidence that operational records are accurate.

## Test conventions

- **Environment:** Run automated tests against the project's isolated test database. Perform manual checks in a staging copy with approved test accounts and data. Never use destructive test actions against live operational records.
- **Roles:** Use one admin account and one staff account. Record role, browser, viewport, date/time, and build/commit for manual runs.
- **Inventory stock:** Unless a case says otherwise, item on-hand quantity is the sum of its stock batches. Low stock means 1–9 units; out of stock means 0 units. Expiring means the earliest applicable expiration date is today through 30 days from today, inclusive. Expired stock must be identified separately from future-expiring stock.
- **Report pagination:** The summary table displays 15 rows per page. CSV and print/PDF output must include the full report dataset.
- **Evidence:** For each manual case, save the case ID, pass/fail/blocked status, actual values, screenshot or export where useful, and a defect link. Do not mark a test passed based only on a page loading.

## A. Inventory and stock accuracy

| ID | Priority | Preconditions and test data | Steps | Expected result |
| --- | --- | --- | --- | --- |
| INV-001 | P0 | Empty test database; admin account | Create an item with valid name, category, unit, beneficiary, type, and location. | Item is saved once, fields are preserved, and generated SKU is deterministic from the submitted attributes. Inventory list shows the new item with zero on-hand stock. |
| INV-002 | P1 | Existing item with a unique SKU | Submit the create form again with attributes that generate the same SKU. | Duplicate is rejected with a clear message; existing item is unchanged and no second record is created. |
| INV-003 | P1 | Admin; valid and invalid item form values | Submit with missing required fields, invalid item type, an overlong value, and a malformed expiration date. | Invalid submissions return validation feedback, preserve entered values where supported, and create no item. Valid optional fields may be blank. |
| INV-004 | P0 | One item with stock batches of 12 and 8 units | Open inventory and inventory report. | The item displays 20 units. Report totals and category totals include 20 exactly once; quantities are summed across batches. |
| INV-005 | P0 | Items with quantities 0, 1, 9, 10, and 11 | Open inventory, apply each stock status filter, and inspect report status counts. | 0 is out of stock; 1 and 9 are low stock; 10 and 11 are in stock. No item appears in an incorrect status filter. |
| INV-006 | P1 | One item with batches at 10 and 20 units | Add another batch of 5 through stock-in workflow. | On-hand value becomes 35; existing batches remain intact and the stock-in action is represented once in the appropriate records. |
| INV-007 | P1 | Existing item with stock; admin | Edit name/category/unit/location/type and save. | Updated values appear consistently in inventory and relevant reports; SKU and linked inventory data remain consistent with documented SKU policy. |
| INV-008 | P1 | Existing item; admin | Delete item using the UI and inspect the list and linked records. | Confirmation/feedback is clear; item disappears. Linked/derived records follow the approved retention/deletion policy with no orphaned inventory record. |
| INV-009 | P0 | Item A: 5 units expiring in 10 days. Item B: 20 units expired yesterday. Item C: 4 units, no expiry. | Open inventory summary and inspect rows and filters. | On-hand total is 29; two items are low stock; one is expired; one is expiring within 30 days. A/B/C dates and statuses are correct; C is not falsely marked expired or expiring. |
| INV-010 | P1 | Positive-stock batches expiring yesterday, today, in 30 days, and in 31 days; zero-stock item with future expiry | Apply Expired and Expiring filters and inspect summary counts. | Yesterday is expired; today and day 30 are expiring; day 31 is neither. Zero-stock item is not counted as an active expiry alert. Boundary is inclusive at day 30. |
| INV-011 | P1 | Same item has one expired batch and one future batch; both have positive quantity | Inspect item-level expiration date, expiry flags, and report. | The displayed item expiry reflects the earliest relevant batch date, and expired stock is not mislabeled as future-expiring. Any aggregate alert policy is consistent between inventory and report. |
| INV-012 | P1 | Several categories, including a custom category; at least 3 items in one category | Search by SKU/name, filter by category and status, then combine all three. | Each control narrows results correctly; combined filters use AND behavior; unrelated category headers and rows are hidden; clearing filters restores current category pagination. |
| INV-013 | P1 | More items in one category than its visible page size | Use category next/previous controls, collapse/expand category, and use Collapse All/Expand All. | Correct rows are shown for each category page. Other categories retain their page state. Toggle controls work by keyboard and expose accurate expanded state. |
| INV-014 | P1 | Search/filter combination that returns no items | Enter a no-match search and optionally select category/status. | A clear no-results state is shown; no empty category headers remain; clearing filters restores rows without a reload. |
| INV-015 | P1 | Inventory with multiple batches and custom category/unit values | Compare list and inventory report values to a hand-calculated fixture sheet. | Item count, summed units, stock statuses, category totals, and expiry data match the fixture exactly; no category or unit is silently dropped. |

## B. Reports, pagination, print, and exports

| ID | Priority | Preconditions and test data | Steps | Expected result |
| --- | --- | --- | --- | --- |
| RPT-001 | P0 | Inventory report fixture: 17 items with unique ordered SKUs | Open Inventory Report page 1 and page 2. | Page 1 contains rows 1–15 only; page 2 contains rows 16–17 only. Range labels are 1–15 of 17 and 16–17 of 17. No duplicates or omissions. |
| RPT-002 | P0 | Same 17-row fixture | Open `?page=999`, `?page=0`, and a non-numeric page value. | Request is handled safely; valid last-page or first-page results are shown as appropriate; response does not error or claim an impossible page/range. |
| RPT-003 | P0 | Inventory fixture with 0, 1, 15, 16, 30, and 31 rows (run separately) | Open report and navigate pages for each dataset size. | Empty state is accurate for 0; a single page is shown up to 15; second/third pages contain the exact remainder; no unnecessary pagination controls appear. |
| RPT-004 | P0 | 17+ report rows | Export CSV while viewing page 1, then page 2. | Both exports contain the header and every report row, independent of current page. CSV row count equals source row count + 1; cells and totals are not truncated. |
| RPT-005 | P0 | 17+ report rows | Use Print/PDF from page 1 and page 2; inspect print preview or saved PDF. | Print output contains all report rows exactly once, not just the visible page. Screen output remains paginated. Navigation, sidebar, and controls do not appear in the printout. |
| RPT-006 | P1 | Each of the four report types has at least one known fixture row | Open each report tab, switch tabs, then export each CSV. | Correct report title, active tab, columns, rows, and CSV filename correspond to the selected report. Invalid report key safely falls back to Inventory Report. |
| RPT-007 | P0 | Item with two batches: 12 and 8; separate item with 5 expired units | Compare Inventory Report cards/table/chart and CSV to hand calculation. | Total stock is 25; item count is 2; low-stock/out-of-stock/expiry counts follow defined rules; batch quantities are not lost or double-counted. |
| RPT-008 | P0 | Requests: 2 created this month (one Approved, one Rejected), plus one from prior month | Open Request Report and compare monthly rows and cards. | Current month total is 2, approved 1, rejected 1, approval rate 50%. Prior-month request is attributed to its correct month. |
| RPT-009 | P0 | Release of 10 units; returns: 6 Good, 2 Damaged, 1 Missing | Open Borrowed / Returned report and compare row and summary. | Borrowed is 10; all returned is 9; damaged/missing is 3. Physical quantity not yet returned is 1 (10−9). The report's current **Outstanding** metric is defined as borrowed minus Good returns, so it is 4 (10−6) and includes damaged/missing quantities; verify the label/help text makes this distinction clear. |
| RPT-010 | P0 | Two releases of 5 and 7 units for the same item/destination | Open Supply Distribution report and export CSV. | Distributed quantity is 12; release count is 2; distinct items is 1; destination is Barangay 1; item/destination aggregation is correct. |
| RPT-011 | P1 | No source records for a selected report | Open each empty report and export. | Empty-state text is understandable, cards show zero/appropriate empty values, chart does not imply fabricated data, and CSV contains a header with no fabricated data rows. |
| RPT-012 | P1 | Dataset with category values tied in totals and dates at month/year boundary | Compare sorted report rows, chart labels, and CSV to source records. | Sorting is stable and labels/period boundaries are correct; chart totals reconcile to table and do not omit tied categories. |

## C. Request, release, and return workflows

| ID | Priority | Preconditions and test data | Steps | Expected result |
| --- | --- | --- | --- | --- |
| WRK-001 | P0 | Staff user; available stock; valid request form | Submit a request for a known quantity and purpose. | Request is created once with correct requester, item, quantity, priority, and initial status. Confirmation appears and inventory is not reduced before release. |
| WRK-002 | P0 | Pending request; admin | Approve request, then inspect status, request list, notifications, and dashboard. | Status changes once to Approved; audit/decision information is accurate; related views update consistently. Repeating approval does not duplicate effects. |
| WRK-003 | P0 | Pending request; admin | Reject request and provide any required reason. | Status changes to Rejected; reason and decision are visible where applicable; stock is not reduced; staff sees correct status. |
| WRK-004 | P0 | Approved request; quantity 10 | Release 10 units and inspect release record, request status, stock and report. | Release quantity is 10, request advances to Released, stock effect matches policy exactly once, destination/purpose are retained, and report reconciles. |
| WRK-005 | P0 | Available quantity 10 | Attempt to release 11 units, zero, negative, and a nonnumeric quantity. | Invalid or over-available release is rejected with useful validation; no partial or duplicate release is recorded and stock is unchanged. |
| WRK-006 | P1 | Released returnable item, 10 released | Return 6 Good, 2 Damaged, 1 Missing; then try to return more than remaining quantity. | Each condition and quantity is recorded correctly; return totals reconcile; over-return is blocked; stock restoration follows condition-specific policy. |
| WRK-007 | P1 | Requests across Pending, Approved, Rejected, Released | Search by code/requester and select each status filter; then combine search and status. | Results match both criteria; Released is available; no-match feedback appears; clearing filters restores the full list. |
| WRK-008 | P1 | Repeated form submission/network retry scenario | Submit a request, stock-in, release, or return, then refresh/back/submit again. | User receives clear success state; a browser retry does not create duplicate business records or apply stock changes twice. |

## D. UI/UX, accessibility, and responsive behavior

| ID | Priority | Preconditions and test data | Steps | Expected result |
| --- | --- | --- | --- | --- |
| UX-001 | P1 | Admin and staff accounts | Open dashboards with known test records. | KPI values reconcile to source data; labels and empty states are clear; no demo claims, stale numbers, or perpetual loading messages appear. Admin and staff see the correct dashboard. |
| UX-002 | P1 | Authenticated inventory and request pages | At 360×800, 768×1024, and 1366×900, inspect layout and use filters/forms. | No clipped primary actions or overlapping text. Forms and cards reflow; wide tables scroll within their container; page remains usable without horizontal page-wide overflow. |
| UX-003 | P1 | Keyboard only | Tab through navigation, search/filter controls, category toggles, pagination, report tabs, and primary actions; activate with Enter/Space. | Focus order follows visual order; visible focus indicator is present; every action is keyboard-operable; focus is not trapped or lost. |
| UX-004 | P1 | Screen reader or accessibility inspector | Inspect labels, headings, table structure, status messages, and pagination. | Inputs have programmatic labels; tables have captions and column headers; current page and expanded states are announced; icons are decorative when adjacent text provides the name. |
| UX-005 | P1 | Mobile viewport | Open/close sidebar with button, backdrop, Escape, and keyboard; navigate to a page. | Sidebar opens/closes predictably, toggle state is announced, background content is not unintentionally blocked after close, and navigation remains reachable. |
| UX-006 | P2 | Slow connection or empty dataset in staging | Load dashboard/report and submit one form with a validation error. | Loading, empty, validation, and success states are distinct and understandable; errors are associated with fields; no raw exception or broken layout is shown. |
| UX-007 | P1 | Inventory table with all columns and admin actions | Inspect at desktop and mobile widths, including category header and no-results states. | Header/body column counts align, labels remain readable, category rows span the table, and actions remain reachable without obscuring data. |
| UX-008 | P1 | Paginated report with multiple pages | Resize viewport and use pagination by keyboard and touch. | Controls remain visible, targets are easy to activate, current page is announced, query/report selection is retained, and range text remains accurate. |

## E. Access control and operational safety

| ID | Priority | Preconditions and test data | Steps | Expected result |
| --- | --- | --- | --- | --- |
| SEC-001 | P0 | Signed-out browser | Request dashboard, inventory, request, report, stock-in, release, return, profile, and user-management routes directly. | Protected pages/actions redirect to sign-in; no private records or mutation occurs. |
| SEC-002 | P0 | Staff account | Directly request admin-only inventory mutations, stock-in, approve/reject, reports/export, and user-management routes (GET and POST as applicable). | Staff is denied with an appropriate response/redirect; no item, batch, request decision, or user is changed. Record any route that permits the action as a release-blocking defect. |
| SEC-003 | P1 | Admin and staff in separate sessions | Open inventory, reports, profile, and notifications. | Each role sees only authorized data and controls; direct URL access does not bypass role restrictions. |
| SEC-004 | P1 | Forms with text fields | Enter HTML/script-like text into allowed text values and render saved record. | Text is safely escaped; no script executes; validation limits and error feedback remain usable. |

## F. Manual operational data verification

These checks require authorized operational source records and an independent reviewer. Automated tests and seed data cannot establish real-world accuracy.

| ID | Check | Procedure | Acceptance evidence |
| --- | --- | --- | --- |
| DATA-001 | Item master | Compare SKU, name, category, unit, type, beneficiary, variant, and location for every item with the approved item register. | Signed reconciliation sheet; unexplained mismatches = 0. |
| DATA-002 | Physical stock | Freeze a count time; count each location/batch and compare to recorded quantities. Record damaged, expired, and quarantined stock separately. | Count sheet with SKU, location, batch, system quantity, counted quantity, variance, and reviewer. All variances dispositioned. |
| DATA-003 | Receiving and expiry | Sample/trace every batch in scope to receiving documents; verify supplier, quantity, received date, and expiry date. | Document references for each sampled batch; boundary dates independently checked. |
| DATA-004 | Requests and decisions | Trace requests to submitted/approved/rejected records; verify requester, item, quantity, purpose, status, decision maker, and timestamps. | Request-level reconciliation; status and quantities match source evidence. |
| DATA-005 | Releases and destinations | Trace releases to dispatch documents; verify item, quantity, destination, release date, and purpose. | Release register reconciles to dispatch records and distribution totals. |
| DATA-006 | Returns and condition | Trace returned quantities to inspection logs; verify Good, Damaged, and Missing are mutually exclusive and no quantity is counted twice. | Return reconciliation with quantity equation and condition evidence. |
| DATA-007 | Report-to-source reconciliation | Independently total each report from approved source data; compare dashboard, table, chart, pagination, and CSV. | Source query/spreadsheet, generated export, row counts, and totals agree exactly or have documented explanations. |
| DATA-008 | Date and timezone checks | Compare records near midnight, month end, leap day, and 30-day expiry boundary using the agreed business timezone. | Documented business timezone and consistent date/month attribution across UI, report, and export. |

## Current automated coverage and execution

Feature tests currently cover representative inventory batch/expiry totals, report calculations, report pagination and complete CSV data, seed request linkage, and selected interface smoke checks. Run the full suite with:

```sh
php artisan test
```

Build frontend assets with:

```sh
npm run build
```

Automated feature tests do not exercise real browser rendering, print dialog output, touch interactions, screen-reader behavior, or live operational data. Complete the manual sections above before acceptance. Treat P0 failures as blockers; file defects with test ID, exact fixture/source, reproduction steps, expected vs actual values, and screenshot/export evidence.
