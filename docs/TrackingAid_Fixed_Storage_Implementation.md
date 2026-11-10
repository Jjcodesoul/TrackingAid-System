# TrackingAid Fixed Storage Implementation

## Implemented behavior

- The designated active location is **Mambaling Storage - Mambaling, Cebu City, Philippines**.
- New Inventory registrations resolve the active designated `StorageLocation` record on the server. The form displays that location without offering alternatives.
- Stock-In displays the same fixed location and resolves it again inside its database transaction. Client-supplied location IDs cannot redirect a receipt to another location.
- Each Stock-In form submission carries a unique key persisted under a unique index, preventing a retry of the same receipt from adding its quantity twice.
- Existing Inventory assignments are not changed by ordinary item edits. Existing Stock-In history remains attached to its recorded location value.
- The separate Storage Location Management page remains unavailable; the current routes expose no location creation or deletion action.
- The existing `storage_location` columns store location names as text. Eloquent relations on Item and StockBatch resolve those names against `storage_locations`; this change does not add or rewrite foreign-key columns.

## Database initialization

Migration `2026_11_10_100000_configure_fixed_mambaling_storage` reuses the unique `Mambaling Storage` row when present, otherwise creates it, activates it, and deactivates other location rows. Existing location rows and historical inventory/transaction values are retained. The migration has a non-destructive rollback.

Migration `2026_11_10_101000_add_stock_batch_submission_key` adds a nullable unique key to stock batches. Existing batch rows remain unchanged; new Stock-In requests store the form submission key.

The migration was applied by itself to avoid applying other pending migrations. The verified database has one active row: `Mambaling Storage` at `Mambaling, Cebu City, Philippines`; the six temporary named options remain present but inactive.

## Existing assignments requiring manual review

The read-only inventory audit found 68 item rows whose text assignments remain unverified:

| Existing item assignment | Records |
|---|---:|
| Warehouse A | 3 |
| Warehouse B | 22 |
| Warehouse C | 18 |
| Equipment Bay | 25 |

The stock batch audit found 68 batches without a recorded location and one batch labelled `Clothing and Essentials Storage`. These values were not automatically moved to Mambaling Storage. Confirm the physical location and correct the records through an approved data-correction process.

## Verification status

- Database migration completed and active location/legacy assignment counts were re-read afterward.
- PHP syntax checks passed for the modified controllers, model, migration, and DatabaseSeeder.
- `StorageLocationManagementTest` could not run because this PHP installation lacks the SQLite PDO driver. The test database was not reset or modified.
- Inventory/Stock-In browser testing remains pending an environment with a supported isolated test database.
- The public pages use the existing transparent TrackingAid wordmark asset at `public/images/trackingaid-logo-transparent.png`. It matches the supplied mark and colors but includes the "by ResQOperation" subline; the exact standalone image shown in the prompt was not present as a filesystem attachment.
