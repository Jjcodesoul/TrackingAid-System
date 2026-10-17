# ResQOperation request intake and stock checks

TrackingAid currently supports an inbound request endpoint at `POST /api/incoming-requests`. A request is upserted by `tracking_reference`, so a retry updates the existing forwarded-request record instead of creating another one.

When the incoming `item_name` or `resource_type` matches exactly one TrackingAid item, the response includes `stock_availability` with the requested quantity, on-hand quantity, approved-but-unreleased reservation, available quantity, shortage, and a fulfillment status. A SKU is the preferred item reference. Exact item-name matching is accepted only when the name is unique. Unmatched or ambiguous names are returned as `unverified` or `ambiguous_item`; TrackingAid does not guess which stock record to use.

Possible stock-check statuses are `available`, `partially_available`, `insufficient`, `unverified`, `ambiguous_item`, and `unverified_quantity`. The figures are calculated from current stock batches and local approved request reservations when the endpoint is called. They are a point-in-time check, not a reservation or promise of future availability. Local approval still rechecks stock in a transaction before reserving a quantity.

There is no configured outbound ResQOperation callback or API client in this project. TrackingAid returns the stock check synchronously to the caller of its existing inbound endpoint; it does not push later approval, release, or stock changes to ResQOperation. A later outbound update requires Gwen's/ResQOperation's confirmed callback URL, authentication method, request/status payload contract, retry policy, and idempotency key. Until those are supplied and tested, no external update should be reported as sent.
