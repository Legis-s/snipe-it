# Customization Completion Plan

Baseline: `8d285d254fcb03aaa2111872e19aaca6e1030c67`.
Preserve the existing worktree and custom workflows. Do not merge a newer upstream
revision or change dependencies as part of this cleanup.

## Scope Boundary

- Remove unused code only when comparison with the baseline confirms it belongs
  to our customizations, including custom additions inside upstream files.
- Do not delete, clean up or refactor upstream code merely because it appears unused.
- Preserve upstream implementations. Keep only the smallest necessary integration
  changes in upstream files; place independent custom behavior in custom files.
- Before each removal, verify its origin against the baseline and its callers.
  If origin or usage is uncertain, leave it in place pending clarification.
- Upstream defects unrelated to our customizations are out of scope; report them
  separately instead of fixing them as part of this cleanup.

## Completion Criteria

- Every remaining change to an upstream file has a necessary customization purpose.
- Independent custom workflows use existing controllers, actions, presenters and
  opt-in traits rather than expanding common helpers or introducing a new framework.
- Destructive operations validate all inputs and permissions before changing data,
  preserve inventory/history, and roll back on failure.
- Legacy and custom checkout records agree with displayed stock and reports.
- The affected tests, broader regression suite, production frontend build and
  desktop/mobile browser checks pass. Pre-existing failures are identified separately.
- No live Bitrix synchronization, production data mutation or destructive migration
  is run as verification.

## Remaining Work

### 1. Data Safety and Destructive Operations

- [x] Consumable compaction: isolate the custom endpoint; validate IDs and permissions;
  prevent self/double merges; preserve legacy checkouts, custom assignments, orders,
  files and history; test rollback and company boundaries.
- [x] Compaction transfers kit membership, sums duplicate kit quantities and retains
  separate pending requests with their fulfillment progress. Require kit update
  permission and roll back all changes on overflow or persistence failure.
- [x] Transfer purchase JSON references without combining lines or changing prices,
  quantities and receipt progress. Check purchase update permission, reject ambiguous
  row IDs and roll back all transfers on persistence or later stock validation failure.
- [x] Support legacy purchase_id references in compaction, purchase counts/details
  and the consumable API purchase filter. Preserve original provenance and resolve
  successive merges to the current target while retaining company scoping.
- [x] Make legacy bulk consumable receipt transactional and compatible with the
  upstream Orders/OrderItems schema. Validate each line, require creation permission,
  prevent duplicate creation after soft deletion and reject partially company-visible
  purchases. Preserve ordinary creation observers and roll back failed saves.
- [x] Add source-to-target merge history with actor/date and a unique source ID;
  reject stale receipts and reuse of recorded sources even after manual restoration.
  Preserve the individual edges of successive merges for provenance.
- [x] Asset-model conversion: determine whether it is reachable/used; remove dormant
  unsafe code or implement an authorized, transactional workflow with tests.
- [x] Purchase payment: remove global event-dispatcher shutdown, handle missing
  statuses, validation failures and partial updates; test idempotency.
- [x] Audit routes/api_custom.php for existing public handlers, authentication and
  HTTP methods; restrict resource registrations to implemented operations.
- [x] Remove custom web resource registrations with missing handlers; verify route
  resolution and preserve implemented operations and names.
- [x] Replace GET purchases/delete_all_rejected with confirmed, CSRF-protected POST;
  transactionally soft-delete rejected purchases and their unassigned accessible
  assets, retaining history and files. Reject the whole batch on permission/company
  mismatch, assigned assets, failed deletes or persistence exceptions.
- [x] Remove the obsolete contract closing-documents UI, counts and unused custom
  helpers as explicitly approved. Do not implement replacement functionality.
- [ ] Finish custom web HTTP-method and object-level authorization audit, including
  contracts, deals and their selectlists.
- [x] Single-purchase deletion uses the same transaction, asset authorization,
  company checks and soft deletion as bulk deletion. Recheck the allowed status
  under the purchase row lock; retain the rejected/error eligibility rule.

### 2. End-to-End Custom Workflows

- [ ] Purchase, invoice recognition, payment, asset inventory, review and consumable receipt.
- [ ] Single/bulk checkout, sale, rent and return, including rollback and permissions.
- [ ] Legacy/custom consumable quantities across account, location, user, reports and kits.
- [ ] Bitrix credentials, synchronization, devices and cleanup commands without live calls.

### 3. Remaining Upstream Differences

- [ ] Classify remaining controller/model/observer/listener/policy/transformer changes.
- [ ] Isolate custom Bootstrap-table formatters and layout/menu additions where useful.
- [ ] Review custom form fields and preserve standard validation, old input and redirects.
- [ ] Remove unreachable custom methods/imports and duplicate custom logic only;
  preserve upstream code and retain necessary integration hooks.

### 4. UI and Localization

- [ ] Check custom English/Russian keys, hard-coded labels and missing translations.
- [ ] Browser-check map height/zoom/markers and location sidebar map/tab navigation.
- [ ] Browser-check purchases, user forms, consumable workflows and permission states.
- [ ] Verify desktop/mobile layout, browser errors and generated assets.

### 5. Final Verification

- [ ] Run Composer autoload/package discovery, route checks and migration status.
- [ ] Run custom regression suites followed by the broad applicable PHPUnit suite.
- [ ] Run `vendor/bin/pint --dirty --format agent` and `git diff --check`.
- [ ] Run `npm run prod` and verify the running application.
- [ ] Review the final diff against the baseline and record justified remaining hooks,
  test results and unresolved restrictions here.

## Progress

- Earlier passes restored upstream implementations in ViewAssetsController,
  DepreciationsController and SelectlistTransformer; isolated map transformation,
  purchase receipt, purchase asset actions, sale/rent and Bitrix token handling.
- Regression fixes already cover inventory labels, workflow permissions, stock sorting,
  archive-filter settings isolation, orphan photo writes and check-in cost preservation.
- Current batch: compaction isolated in ConsumableCompactController and
  CompactConsumablesAction. Ordinary merges preserve both checkout ledgers, order
  items and uploaded-file logs. Source files remain intact; retired source stock is
  zeroed to avoid duplication on restore. Purchase-linked merges remain rejected;
  source kits and pending requests are now transferred as described below.
- Removed the unrouted/uncalled AssetModelsController::convert method and its
  imports. The controller now exactly matches the baseline. This does not implement
  a new conversion feature; adding one would require explicit workflow rules.
- Other checklist items remain open. Final verification has not been completed.

### Verification of This Batch

- Consumables API, AssetModels API, API consumable checkout and customizations:
  145 tests, 617 assertions passed in the isolated `snipeit_codex_test` database.
- Pint passed; `git diff --check` passed; the compaction route resolves to the new
  controller. Its URL and route name are unchanged.
- Purchase payment now uses PayPurchaseAction with purchase/asset row locks and
  a single transaction. Scoped quiet asset saves preserve explicit validation and
  restore model events; purchase validation or persistence failures roll back assets.
  Missing status labels return the standard API error. Repeated payment retains the
  first payment timestamp and does not regress inventory/review/finished states.
  Partially company-visible purchases are rejected without mutation.
- Purchase suites, asset purchase workflow, consumables API, Purchase and Asset unit
  tests: 110 tests, 622 assertions passed in `snipeit_codex_test` after this change.
  No live Bitrix requests or production database changes were used for verification.
- Custom API route audit removed 36 registrations pointing to missing methods,
  including unused asset closesell and assignment close_documents endpoints.
  Existing implemented operations retain their names and URLs. Resource controllers
  now explicitly list supported actions; unsupported writes return HTTP 405.
  Tests check every custom API handler, actual route resolution, authentication and
  non-GET mutation methods, plus read-only denial for additional purchase/asset actions.
- Customizations, purchases, map, asset purchase workflow and consumables API:
  150 tests, 874 assertions passed in `snipeit_codex_test`. Pint and diff checks passed;
  route cache cleared. No live synchronization was invoked.
- Next: compaction support for dependent workflows, custom web-route/object permission
  audit and the unresolved contract closing-documents workflow.
- Custom web cleanup removed 24 registrations pointing to missing methods in
  routes/web/custom.php. Kept existing invoice-type editing, purchase creation/editing
  through POST store, inventory deletion and all implemented resource actions.
  Removed the unreachable invoice-type creation branch from its edit form.
- Rendering tests uncovered obsolete Form facade calls in two custom invoice-type
  partials. Replaced them with ordinary HTML fields, preserving disabled Bitrix ID
  and adding an explicit unchecked activity value. No dependency changes.
- All production files changed in this web cleanup are absent from the upstream
  baseline; no upstream implementation was edited. The contract closing-documents
  UI remains pending workflow confirmation, not classified as unused code.
- Customizations, purchases, map and impersonation: 130 tests, 815 assertions passed
  in the isolated database. Existing invoice-type editing and activity updates have
  rendering, permission and persistence coverage.
- Removed the unused custom Api/BulkAssetsController (121 lines). It is absent from
  the upstream baseline and had no routes or callers. The active upstream
  Assets/BulkAssetsController and its routes were not changed in this pass.
  Added a regression check for the standard bulk-checkout GET/POST handlers.
- Customizations, UI bulk asset checkout and Asset unit tests: 139 tests,
  786 assertions passed in the isolated database. Composer autoload regeneration
  (`--no-scripts`), Pint and diff checks passed. Dependencies were not changed.
- Removed the obsolete custom user_id entry from AssetModel::$fillable. The upstream
  migration renamed this column to created_by; no current custom workflow uses the
  old attribute. A regression test reproduced SQL error 1054 before removal and now
  covers API creation/update with legacy payloads. AssetModel.php exactly matches
  the upstream baseline again; no upstream logic was removed or refactored.
- Customizations, AssetModels API, purchases and AssetModel unit tests: 176 tests,
  947 assertions passed in the isolated database. Pint and diff checks passed.
- Approved execution started with removal of the contract closing-documents UI,
  obsolete count columns/API fields and unused helpers. No document workflow is
  being added; the user's later clarification supersedes earlier pending notes.
- Bulk rejected-purchase deletion now uses DeletePurchasesAction, row locks,
  permission/company checks and one transaction. Asset deletion is soft rather than
  permanent; files remain and normal delete history/events are preserved. GET does
  not delete. POST requires explicit confirmation and the existing CSRF middleware.
- Purchases, customizations, bulk asset checkout and Purchase unit tests:
  146 tests, 900 assertions passed in the isolated database. Coverage includes GET,
  missing confirmation, CSRF failure, permissions, company boundaries, assigned
  assets, model refusal, late exception rollback and page rendering. Pint and diff
  checks passed; route/view caches cleared.
- Interactive browser verification was attempted but no connected browser is
  available. It remains pending. Full compaction, remaining permission audits,
  broader workflow checks, production build and final acceptance are not complete.
- Single and bulk purchase deletion now share DeletePurchasesAction. The single
  controller no longer force-deletes assets or suppresses their events. Existing
  URLs are unchanged; single deletion still accepts rejected/error purchases only,
  with eligibility checked again against the locked database row. Both operations
  retain history and roll back on failed deletion.
- Purchases, customizations and Purchase unit tests: 134 tests, 874 assertions
  passed. Focused deletion coverage: 20 tests, 134 assertions. Pint and diff checks
  passed. No upstream implementation was changed in this pass.
- Compaction now combines kit quantities into one target row per affected kit and
  checks kit update permission. Pending requests keep their separate IDs, users,
  quantities, fulfillment progress and reservation dates; completed requests stay
  on the source for history. Target validation failure and a failed request save
  roll back kit/request changes together with the merge. Repeated merges and kit
  quantity overflow are covered. Upstream kit/request implementations are unchanged.
- Purchase provenance mapping, purchase-line transfer, stale receipt protection
  and concurrent workflow verification remain pending.
- Consumables API, predefined kits, consumable requests and checkout request suites:
  124 tests, 514 assertions passed in the isolated database. Pint and diff checks
  passed. No production database changes or live external calls were made.
- Added the custom consumable_merges table migration. New merges write immutable
  source/target edges, actor, timestamp and a nullable legacy purchase ID snapshot
  inside the same transaction as stock/history changes. Failed audit persistence
  rolls back the mapping too. Previously retired sources cannot be used as either
  side of another merge, even if manually restored. Valid target-to-next-target
  merges preserve both historical edges.
- Receipt rejects recorded merged sources before restoring or changing stock.
  Ordinary deleted consumables remain receivable. Tests cover stale purchase data,
  manually restored sources, unchanged receipt ledgers and rollback of merge history.
  Existing merges predating this table are not automatically backfilled. Purchase
  JSON transfer and legacy purchase relationship resolution are still pending;
  purchase-linked merges remain blocked.
- The new migration was applied only by the isolated test suite. Deploy it before
  using the changed compaction/receipt endpoints in an application database.
- Consumables API, customizations, purchases, kits and request suites: 257 tests,
  1429 assertions passed in the isolated database. Focused merge/receipt regression:
  59 tests, 291 assertions passed. Pint and diff checks passed.
- JSON-linked purchase merges are now enabled, including references in soft-deleted
  purchases. Only consumable_id changes in each JSON line; distinct row IDs, prices,
  quantities, taxes and receipt progress remain intact. Missing, invalid or duplicate
  row IDs reject a merge when multiple lines would refer to the same target.
- Purchase locks precede consumable locks, matching receipt lock order. Because
  consumables_json is not a normalized relation, the merge currently locks all
  purchases with non-null JSON while inspecting references. Reducing this broad
  locking and checking concurrent purchase editing remain outstanding.
- Coverage includes partial receipt against the selected merged line, stale source
  and repeated receipt rejection, another merge into a third target, archived
  purchase references, permissions and rollback after a failed purchase save or
  late stock validation failure. Legacy purchase_id-linked merges remain blocked.
- Verification after JSON transfer: 264 tests, 1486 assertions passed across
  consumables API, customizations, purchases, kits and request suites. Focused
  compaction tests: 26 tests, 217 assertions. Pint and diff checks passed. Only
  the isolated test database was used; no upstream implementation was changed.
- Legacy receipt audit found writes to removed parent order/cost/date columns,
  unchecked consumable saves and a purchase status saved before receipt completed.
  Replaced the custom controller body with ReceiveLegacyConsumablesAction using
  purchase/asset/consumable locks and one transaction. Standard creation observers
  still create initial stock orders and audit links; the action fills purchase
  details and prices into those order records and checks every save it performs.
- Legacy receipt now rejects catalog-format JSON (handled by the separate receipt
  endpoint), invalid quantities/prices, missing creation permission and partially
  company-visible records. Soft-deleted source cards and recorded legacy merge
  provenance prevent re-creation. Purchase status changes only inside the transaction.
  Existing legacy purchase list/relationship resolution still needs adaptation;
  legacy-linked compaction remains blocked.
- Purchases, customizations, consumables API and Purchase unit tests: 200 tests,
  1382 assertions passed in the isolated database. Pint and diff checks passed.
  No application database changes or live Bitrix calls were made.
- Legacy-linked compaction is now enabled. The new current_target_id migration
  resolves existing recorded merge chains; subsequent merges update this pointer
  transactionally without changing original target_id, source_purchase_id, actor
  or timestamp. Missing purchase references and missing purchase update permission
  reject the operation, including inherited purchase references in later merges.
- HasLegacyPurchaseLinks supplies a grouped, company-scoped query for direct and
  historical purchase membership. Current targets appear once even if both routes
  match; manually restored retired sources do not reappear. Purchase::consumables
  retains its physical legacy relation; currentConsumables is used for the purchase
  page, and API counts/filtering use the same scope. Ordinary unfiltered consumable
  queries remain unchanged.
- Compaction now locks all purchase rows before consumables, including legacy
  purchases with null JSON. Lock narrowing and concurrent-edit stress tests remain
  pending. The custom bulk-checkout assignment workflow is unchanged and still
  needs its separate end-to-end audit.
- The current-target migration has only run in the isolated test database. Apply
  both merge migrations before deploying the changed endpoints to another database.
- Verification: 279 tests, 1595 assertions passed across purchases, customizations,
  consumables API, kits, requests and Purchase unit tests. Coverage includes purchase
  page rendering, API list/detail counts, chained legacy links, duplicate suppression,
  restored sources, company boundaries, permissions and late rollback. Pint and
  diff checks passed. Interactive browser and concurrency checks remain pending.
- Purchase line editing now reads JSON under the purchase row lock shared by receipt
  and compaction and saves within one transaction. Deleting a line no longer
  renumbers surviving row IDs, preventing stale requests from selecting another
  line. Missing/duplicate IDs and invalid actions/numeric values are rejected;
  received lines cannot be deleted or reduced below current receipt progress.
- Line edits accept only quantity, price and tax changes; stale submitted item IDs
  or receipt progress cannot overwrite current references. Error messages use
  English/Russian translation keys. Save failure rolls back JSON and status.
- Tests exercise sequential stale requests and receipt/merge interleavings, not
  simultaneous-process contention. Full purchase form POST store still needs a
  separate stale-JSON/concurrency audit before the broader work can be considered done.
- Verification of line editing: purchases, customizations, consumables API and
  Purchase unit suites passed with 213 tests and 1490 assertions. Pint and diff
  checks passed. Only the isolated test database was used.
- Inventory review fixes: removed invalid snapshot search relations; item updates
  now use UpdateInventoryItemAction with immutable asset/inventory references,
  company-scoped asset lookup, object audit authorization and a transaction locking
  the parent inventory before the item and asset. All saves are checked. Success
  follows the label's success flag and checked state; completion is recalculated,
  including reopening and unsuccessful completion. Existing data is not backfilled.
- Inventory photos are validated as bounded JPEG/PNG data, use the public storage
  disk and are removed on rollback. Map coordinates are numeric/range checked and
  serialized safely; container and script share the same visibility condition.
- Inventory/customization/transformer/asset-workflow regression: 122 tests,
  801 assertions passed. Pint and diff checks passed; Blade cache cleared. A read-only
  search against inventory 219 now succeeds. No inventory records were mutated in
  the application database. Creation/deletion endpoints and broader read permissions
  remain separate audit work; interactive browser verification is still unavailable.
