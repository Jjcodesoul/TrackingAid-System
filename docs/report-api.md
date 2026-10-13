# External report API

## Generate a report

**Method:** `POST`  
**Path:** `/api/v1/external/reports/generate`  
**Content type:** `application/json`  
**Authentication:** `X-API-KEY`

Configure `TRACKINGAID_REPORT_API_KEY` in the server environment. Requests without the configured key receive `503`; requests with a missing or incorrect key receive `401`. Do not commit or post the key in group chats.

Example body:

```json
{
  "report_type": "inventory",
  "format": "pdf",
  "member_id": "member-42",
  "requested_by": "partner-group",
  "system_name": "PartnerApp",
  "notes": "Monthly report"
}
```

Supported report types are `inventory`, `request`, `borrow-return`, and `distribution`. Supported formats are `json`, `csv`, and `pdf`. `member_id`, `requested_by`, `system_name`, and `notes` are optional. Date filters are not currently supported; requests containing `filters`, `start_date`, or `end_date` are rejected instead of silently ignored.

Successful response:

```json
{
  "status": "success",
  "request_id": 42,
  "member_id": "member-42",
  "request_status": "completed",
  "report_type": "inventory",
  "format": "pdf",
  "file_name": "trackingaid-inventory-42.pdf",
  "download_url": "https://your-host.example/api/v1/external/reports/42/download?expires=...&signature=...",
  "expires_at": "2026-10-13T10:30:00+00:00"
}
```

The response contains a signed download URL that expires after 30 minutes. Download the URL with `GET`; the signature authorizes that download, so no API key is needed on that GET request. The file is private to the app's local storage.

## Postman

1. Set the method to `POST`.
2. Use `https://<reachable-host>/api/v1/external/reports/generate`.
3. Add headers:
   - `X-API-KEY: <the privately shared TrackingAid key>`
   - `Content-Type: application/json`
   - `Accept: application/json`
4. Set **Body > raw > JSON** to the example body above.
5. Send the request, copy `download_url` from the JSON response, and make a `GET` request to that URL. Use **Send and Download** to save the file.

## Host and server setup

`127.0.0.1` is only reachable from the computer making the request. A partner must use a deployed host or a currently active tunnel that forwards to the TrackingAid app. Configure `APP_URL` to that same public origin so signed download links use the reachable host. Keep the app server and tunnel running while the partner tests.

For a locally generated key, run `php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"`, put the result in the server's `.env` as `TRACKINGAID_REPORT_API_KEY=<generated-value>`, and privately provide that value to the partner. Keep `.env` out of version control.

After configuring environment values, run `php artisan config:clear` and `php artisan migrate --force` on the server, then restart the app. For a local network test, the app must listen on a network-accessible interface and the firewall must allow the port; alternatively, expose it with a currently running tunnel.

This endpoint exposes only reports already implemented by TrackingAid. It does not produce `demographics`, `dromic`, or `utilization` reports unless those are separately implemented.
