<?php

namespace App\Http\Controllers;

use App\Models\BorrowRelease;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\ReportRequest;
use App\Models\Request as SupplyRequest;
use App\Models\ReturnItem;
use App\Models\StockBatch;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use RuntimeException;
use Throwable;

class ReportController extends Controller
{
    private const REPORTS = [
        'inventory' => 'Inventory Report',
        'request' => 'Request Report',
        'borrow-return' => 'Borrowed / Returned',
        'distribution' => 'Supply Distribution',
    ];

    private const UI_REPORTS = [
        'inventory' => 'Inventory Report',
        'request' => 'Request Report',
        'borrow-return' => 'Borrowed / Returned',
        'distribution' => 'Supply Distribution',
        'stock-movements' => 'Stock Movements',
    ];

    public function index(HttpRequest $request)
    {
        $activeReport = $this->activeReport($request);
        $filters = $this->validatedFilters($request);
        $report = $this->buildReport($activeReport, $filters);
        $allRows = collect($report['rows']);
        $perPage = 15;
        $lastPage = max(1, (int) ceil($allRows->count() / $perPage));
        $currentPage = min(LengthAwarePaginator::resolveCurrentPage(), $lastPage);

        $report['rows'] = new LengthAwarePaginator(
            $allRows->forPage($currentPage, $perPage)->values(),
            $allRows->count(),
            $perPage,
            $currentPage,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );

        return view('reports.index', [
            'tabs' => self::UI_REPORTS,
            'activeReport' => $activeReport,
            'report' => $report,
            'allRows' => $allRows,
            'filters' => $filters,
            'categories' => Item::query()->whereNotNull('category')->where('category', '!=', '')->distinct()->orderBy('category')->pluck('category'),
            'statusOptions' => $this->statusOptions($activeReport),
        ]);
    }

    public function exportCsv(HttpRequest $request)
    {
        $activeReport = $this->activeReport($request);
        $filters = $this->validatedFilters($request);
        $report = $this->buildReport($activeReport, $filters);
        $filename = $activeReport.'-report-'.now()->format('Ymd-His').'.csv';

        return Response::streamDownload(function () use ($report) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, $report['headers']);

            foreach ($report['rows'] as $row) {
                fputcsv($handle, $row['csv']);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportPdf(HttpRequest $request)
    {
        $activeReport = $this->activeReport($request);
        $filters = $this->validatedFilters($request);
        $report = $this->buildReport($activeReport, $filters);
        $filename = $activeReport.'-report-'.now()->format('Ymd-His').'.pdf';

        return Pdf::loadView('reports.pdf', [
            'reportType' => $activeReport,
            'report' => $report,
            'filters' => $filters,
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape')->download($filename);
    }

    public function apiIndex(HttpRequest $request)
    {
        $reportType = $this->reportNameFromRequest((string) $request->query('report', 'inventory'));
        $report = $this->buildReport($reportType);
        $payload = $this->externalReportPayload($reportType, $report);
        $payload['report'] = $payload['report_type'];
        unset($payload['report_type']);

        return response()->json($payload);
    }

    public function apiStore(HttpRequest $request)
    {
        $validated = $request->validate([
            'report' => ['sometimes', 'string', 'max:80'],
            'requested_by' => ['nullable', 'string', 'max:120'],
            'system_name' => ['nullable', 'string', 'max:120'],
            'format' => ['sometimes', 'in:json,csv'],
            'filters' => ['sometimes', 'array'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $reportType = $this->reportNameFromRequest($validated['report'] ?? 'inventory');

        $record = ReportRequest::create([
            'report_type' => $reportType,
            'requested_by' => $validated['requested_by'] ?? null,
            'system_name' => $validated['system_name'] ?? null,
            'status' => 'queued',
            'filters' => $validated['filters'] ?? [],
            'notes' => $validated['notes'] ?? null,
        ]);

        $payload = $this->apiIndex($request->merge(['report' => $reportType]));

        return response()->json([
            'status' => 'success',
            'request_id' => $record->id,
            'report' => $reportType,
            'data' => $payload->getData(true),
        ]);
    }

    public function generateExternalReport(HttpRequest $request)
    {
        $validated = $request->validate([
            'report_type' => ['required', 'string', 'in:'.implode(',', array_keys(self::REPORTS))],
            'format' => ['required', 'string', 'in:json,csv,pdf'],
            'member_id' => ['nullable', 'string', 'max:120'],
            'requested_by' => ['nullable', 'string', 'max:120'],
            'system_name' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:255'],
            'filters' => ['prohibited'],
            'start_date' => ['prohibited'],
            'end_date' => ['prohibited'],
        ]);

        $reportType = $validated['report_type'];
        $format = $validated['format'];
        $report = $this->buildReport($reportType);
        $extension = $format;

        $record = ReportRequest::create([
            'report_type' => $reportType,
            'member_id' => $validated['member_id'] ?? null,
            'requested_by' => $validated['requested_by'] ?? null,
            'system_name' => $validated['system_name'] ?? null,
            'status' => 'generating',
            'format' => $format,
            'filters' => [],
            'notes' => $validated['notes'] ?? null,
        ]);

        $fileName = "trackingaid-{$reportType}-{$record->id}.{$extension}";
        $filePath = "external_reports/{$fileName}";

        try {
            $contents = match ($format) {
                'csv' => $this->reportCsvContents($report),
                'pdf' => Pdf::loadView('reports.external-pdf', ['report' => $report])
                    ->setPaper('a4', 'landscape')
                    ->output(),
                'json' => json_encode(
                    $this->externalReportPayload($reportType, $report),
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
                ),
            };

            if (! Storage::disk('local')->put($filePath, $contents)) {
                throw new RuntimeException('The generated report could not be saved.');
            }

            $record->update([
                'status' => 'completed',
                'file_name' => $fileName,
                'file_path' => $filePath,
            ]);
        } catch (Throwable $exception) {
            $record->update(['status' => 'failed']);
            throw $exception;
        }

        URL::forceRootUrl(config('app.url'));

        $downloadUrl = URL::signedRoute(
            'api.external-reports.download',
            ['reportRequest' => $record->id]
        );

        return response()->json([
            'status' => 'success',
            'request_id' => $record->id,
            'member_id' => $record->member_id,
            'request_status' => $record->status,
            'report_type' => $reportType,
            'format' => $format,
            'file_name' => $fileName,
            'download_url' => $downloadUrl,
        ]);
    }

    public function downloadExternalReport(ReportRequest $reportRequest)
    {
        abort_unless(
            $reportRequest->status === 'completed'
                && filled($reportRequest->file_path)
                && filled($reportRequest->file_name)
                && Storage::disk('local')->exists($reportRequest->file_path),
            404
        );

        return Storage::disk('local')->download($reportRequest->file_path, $reportRequest->file_name);
    }

    public function apiExport(HttpRequest $request)
    {
        $reportType = $this->reportNameFromRequest((string) $request->query('report', 'inventory'));
        $report = $this->buildReport($reportType);
        $filename = $reportType.'-report-'.now()->format('Ymd-His').'.csv';

        return Response::streamDownload(function () use ($report) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $report['headers']);

            foreach ($report['rows'] as $row) {
                fputcsv($handle, $row['csv']);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function activeReport(HttpRequest $request): string
    {
        $report = (string) $request->query('report', $request->input('report', 'inventory'));

        return array_key_exists($report, self::UI_REPORTS) ? $report : 'inventory';
    }

    private function reportNameFromRequest(string $report): string
    {
        return array_key_exists($report, self::REPORTS) ? $report : 'inventory';
    }

    private function externalReportPayload(string $reportType, array $report): array
    {
        return [
            'status' => 'success',
            'report_type' => $reportType,
            'title' => $report['title'] ?? null,
            'subtitle' => $report['subtitle'] ?? null,
            'headers' => $report['headers'] ?? [],
            'rows' => collect($report['rows'] ?? [])
                ->map(fn (array $row): array => collect($row['cells'] ?? [])
                    ->map(fn (array $cell): mixed => $cell['value'] ?? null)
                    ->values()
                    ->all())
                ->values()
                ->all(),
            'stats' => $report['stats'] ?? [],
            'empty' => $report['empty'] ?? null,
        ];
    }

    private function reportCsvContents(array $report): string
    {
        $handle = fopen('php://temp', 'r+');

        if ($handle === false) {
            throw new RuntimeException('Unable to prepare the CSV report.');
        }

        fputcsv($handle, $report['headers']);

        foreach ($report['rows'] as $row) {
            fputcsv($handle, $row['csv']);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);

        if ($contents === false) {
            throw new RuntimeException('Unable to read the generated CSV report.');
        }

        return $contents;
    }

    private function validatedFilters(HttpRequest $request): array
    {
        $filters = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'category' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:80'],
        ]);

        return array_filter($filters, fn ($value): bool => $value !== null && $value !== '');
    }

    private function statusOptions(string $reportType): array
    {
        return match ($reportType) {
            'request' => ['Pending', 'Approved', 'Partially Released', 'Released', 'Rejected', 'Cancelled'],
            'borrow-return' => ['Good', 'Damaged', 'Missing'],
            'distribution' => ['Loading', 'Dispatched', 'In Transit', 'Arrived'],
            'stock-movements' => ['stock-in', 'release', 'good-return', 'damaged-return', 'missing'],
            default => ['In Stock', 'Low Stock', 'Out of Stock', 'Expiring', 'Expired'],
        };
    }

    private function buildReport(string $report, array $filters = []): array
    {
        return match ($report) {
            'inventory' => $this->inventoryReport($filters),
            'request' => $this->requestReport($filters),
            'borrow-return' => $this->borrowReturnReport($filters),
            'stock-movements' => $this->stockMovementReport($filters),
            'distribution' => $this->distributionReport($filters),
        };
    }

    private function inventoryReport(array $filters = []): array
    {
        $itemsQuery = Item::with('stockBatches')->orderBy('category')->orderBy('name');

        if (! empty($filters['category'])) {
            $itemsQuery->where('category', $filters['category']);
        }

        $items = $itemsQuery->get();

        if (! empty($filters['status'])) {
            $items = $items->filter(fn (Item $item): bool => in_array(
                $filters['status'],
                $this->inventoryStatuses($item),
                true
            ))->values();
        }

        $stockFor = fn (Item $item): int => (int) $item->stockBatches->sum('quantity');
        $totalStock = $items->sum(fn (Item $item): int => $stockFor($item));
        $lowStock = $items->filter(fn (Item $item): bool => $stockFor($item) > 0 && $stockFor($item) < 10)->count();
        $outOfStock = $items->filter(fn (Item $item): bool => $stockFor($item) <= 0)->count();
        $expiring = $items->filter(function (Item $item): bool {
            if (! $item->next_expiration_date) {
                return false;
            }

            $expirationDate = Carbon::parse($item->next_expiration_date)->startOfDay();
            $today = now()->startOfDay();

            return $expirationDate->between($today, $today->copy()->addDays(30), true);
        })->count();

        $categoryTotals = $items
            ->groupBy(fn (Item $item): string => strtoupper($item->category ?: 'Uncategorized'))
            ->map(fn (Collection $items): int => $items->sum(fn (Item $item): int => $stockFor($item)))
            ->sortDesc();

        $categoryCounts = $items
            ->groupBy(fn (Item $item): string => strtoupper($item->category ?: 'Uncategorized'))
            ->map(fn (Collection $items): int => $items->count());

        $rows = $items->map(function (Item $item) use ($stockFor): array {
            $stock = $stockFor($item);
            $status = $stock <= 0 ? 'Out of Stock' : ($stock < 10 ? 'Low Stock' : 'In Stock');
            $statusTone = $stock <= 0 ? 'danger' : ($stock < 10 ? 'warning' : 'success');

            return $this->tableRow([
                $this->cell($item->sku, mono: true),
                $this->cell($item->name),
                $this->cell(ucfirst(strtolower($item->category))),
                $this->cell(number_format($stock), $statusTone),
                $this->cell($item->unit_type),
                $this->cell(ucfirst($item->type)),
                $this->cell($status, $statusTone),
                $this->cell($item->storage_location),
                $this->cell($item->next_expiration_date ? Carbon::parse($item->next_expiration_date)->format('M d, Y') : '-'),
            ]);
        })->values()->all();

        return [
            'title' => 'Inventory Levels',
            'subtitle' => 'Current item stock by category, location, and stock status.',
            'chartTitle' => 'Total Stock Units by Category',
            'chart' => $this->chart(
                $categoryTotals->keys()->values()->all(),
                [[
                    'name' => 'Stock',
                    'color' => '#10b981',
                    'values' => $categoryTotals->values()->map(fn ($value): int => (int) $value)->all(),
                ]],
                $categoryTotals->keys()->map(fn ($category) => $categoryCounts[$category].' item'.($categoryCounts[$category] === 1 ? '' : 's'))->values()->all()
            ),
            'stats' => [
                $this->stat('Total Items', number_format($items->count()), 'Registered SKUs', 'fa-solid fa-boxes-stacked'),
                $this->stat('Total Stock', number_format($totalStock), 'Across all batches', 'fa-solid fa-layer-group', 'success'),
                $this->stat('Low Stock', number_format($lowStock), 'Below 10 units', 'fa-solid fa-triangle-exclamation', 'warning'),
                $this->stat('Expiring Soon', number_format($expiring), 'Within 30 days', 'fa-regular fa-clock', 'danger'),
            ],
            'headers' => ['SKU', 'Item', 'Category', 'Stock', 'Unit', 'Type', 'Status', 'Location', 'Expiration'],
            'rows' => $rows,
            'empty' => 'No inventory items found.',
        ];
    }

    private function inventoryStatuses(Item $item): array
    {
        $stock = (int) $item->total_stock;
        $statuses = [$stock <= 0 ? 'Out of Stock' : ($stock < 10 ? 'Low Stock' : 'In Stock')];

        if ($stock > 0 && $item->next_expiration_date) {
            $expiration = Carbon::parse($item->next_expiration_date)->startOfDay();
            $today = now()->startOfDay();

            if ($expiration->lt($today)) {
                $statuses[] = 'Expired';
            } elseif ($expiration->between($today, $today->copy()->addDays(30), true)) {
                $statuses[] = 'Expiring';
            }
        }

        return $statuses;
    }

    private function requestReport(array $filters = []): array
    {
        $requestsQuery = SupplyRequest::with('inventory')->latest();

        if (! empty($filters['category'])) {
            $requestsQuery->whereHas('inventory', fn ($query) => $query->where('category', $filters['category']));
        }
        if (! empty($filters['status'])) {
            $requestsQuery->where('status', $filters['status']);
        }
        $this->applyDateFilters($requestsQuery, 'created_at', $filters);

        $requests = $requestsQuery->get();
        $months = $this->monthBucketsFor($filters);
        $approvedStatuses = ['Approved', 'Partially Released', 'Released'];

        $approvedValues = [];
        $rejectedValues = [];

        $rows = $months->map(function (array $month) use ($requests, $approvedStatuses, &$approvedValues, &$rejectedValues): array {
            $monthRequests = $requests->filter(fn (SupplyRequest $request): bool => $this->dateInRange($request->created_at, $month['start'], $month['end']));
            $approved = $monthRequests->whereIn('status', $approvedStatuses)->count();
            $rejected = $monthRequests->where('status', 'Rejected')->count();
            $total = $monthRequests->count();
            $decided = max($approved + $rejected, 1);
            $approvalRate = (int) round(($approved / $decided) * 100);

            $approvedValues[] = $approved;
            $rejectedValues[] = $rejected;

            return $this->tableRow([
                $this->cell($month['fullLabel']),
                $this->cell(number_format($total)),
                $this->cell(number_format($approved), 'success'),
                $this->cell(number_format($rejected), 'danger'),
                $this->cell($approvalRate.'%'),
            ]);
        })->all();

        $approvedTotal = $requests->whereIn('status', $approvedStatuses)->count();
        $rejectedTotal = $requests->where('status', 'Rejected')->count();

        return [
            'title' => 'Request History',
            'subtitle' => 'Monthly ResQOperation request decisions and approval performance.',
            'chartTitle' => 'Monthly Requests - Approved vs. Rejected',
            'chart' => $this->chart(
                $months->pluck('label')->all(),
                [
                    ['name' => 'Approved', 'color' => '#10b981', 'values' => $approvedValues],
                    ['name' => 'Rejected', 'color' => '#e11d48', 'values' => $rejectedValues],
                ]
            ),
            'stats' => [
                $this->stat('Total Requests', number_format($requests->count()), 'All statuses', 'fa-regular fa-clipboard'),
                $this->stat('Approved', number_format($approvedTotal), 'Includes released requests', 'fa-regular fa-circle-check', 'success'),
                $this->stat('Rejected', number_format($rejectedTotal), 'Declined requests', 'fa-regular fa-circle-xmark', 'danger'),
                $this->stat('Pending', number_format($requests->where('status', 'Pending')->count()), 'Awaiting review', 'fa-regular fa-hourglass-half', 'warning'),
            ],
            'headers' => ['Month', 'Total', 'Approved', 'Rejected', 'Approval Rate'],
            'rows' => $rows,
            'empty' => 'No request history found.',
        ];
    }

    private function borrowReturnReport(array $filters = []): array
    {
        $releasesQuery = BorrowRelease::with(['inventory', 'request']);
        if (! empty($filters['category'])) {
            $releasesQuery->whereHas('inventory', fn ($query) => $query->where('category', $filters['category']));
        }
        $this->applyDateFilters($releasesQuery, 'released_at', $filters);
        $releases = $releasesQuery->get();

        $returnsQuery = ReturnItem::with('inventory');
        if (! empty($filters['category'])) {
            $returnsQuery->whereHas('inventory', fn ($query) => $query->where('category', $filters['category']));
        }
        if (! empty($filters['status'])) {
            $returnsQuery->where('condition', $filters['status']);
        }
        $this->applyDateFilters($returnsQuery, 'created_at', $filters);
        $returns = $returnsQuery->get();
        $months = $this->monthBucketsFor($filters);

        $borrowedValues = [];
        $returnedValues = [];

        $months->each(function (array $month) use ($releases, $returns, &$borrowedValues, &$returnedValues): void {
            $borrowedValues[] = (int) $releases
                ->filter(fn (BorrowRelease $release): bool => $this->dateInRange($release->released_at, $month['start'], $month['end']))
                ->sum('quantity');

            $returnedValues[] = (int) $returns
                ->filter(fn (ReturnItem $return): bool => $this->dateInRange($return->created_at, $month['start'], $month['end']))
                ->sum('quantity');
        });

        $inventoryIds = $releases->pluck('inventory_id')
            ->merge($returns->pluck('inventory_id'))
            ->filter()
            ->unique()
            ->values();

        $inventories = Inventory::whereIn('id', $inventoryIds)->orderBy('name')->get();

        $rows = $inventories->map(function (Inventory $inventory) use ($releases, $returns): array {
            $itemReleases = $releases->where('inventory_id', $inventory->id);
            $itemReturns = $returns->where('inventory_id', $inventory->id);
            $borrowed = (int) $itemReleases->sum('quantity');
            $goodReturns = (int) $itemReturns->where('condition', 'Good')->sum('quantity');
            $otherReturns = (int) $itemReturns->whereIn('condition', ['Damaged', 'Missing'])->sum('quantity');
            $processedReturns = (int) $itemReturns->sum('quantity');
            $outstanding = max($borrowed - $processedReturns, 0);
            $lastActivity = $this->latestDate([
                $itemReleases->max('released_at'),
                $itemReturns->max('created_at'),
            ]);

            return $this->tableRow([
                $this->cell($inventory->sku, mono: true),
                $this->cell($inventory->name),
                $this->cell(number_format($borrowed), 'danger'),
                $this->cell(number_format($goodReturns), 'success'),
                $this->cell(number_format($otherReturns), $otherReturns > 0 ? 'warning' : ''),
                $this->cell(number_format($outstanding), $outstanding > 0 ? 'danger' : 'success'),
                $this->cell($lastActivity ? $lastActivity->format('M d, Y') : '-'),
            ]);
        })->values()->all();

        $totalBorrowed = (int) $releases->sum('quantity');
        $totalReturned = (int) $returns->sum('quantity');
        $damagedOrMissing = (int) $returns->whereIn('condition', ['Damaged', 'Missing'])->sum('quantity');

        return [
            'title' => 'Borrowed / Returned Items',
            'subtitle' => 'Released items, returned quantities, and outstanding balances.',
            'chartTitle' => 'Monthly Borrowed vs. Returned Items',
            'chart' => $this->chart(
                $months->pluck('label')->all(),
                [
                    ['name' => 'Borrowed', 'color' => '#e11d48', 'values' => $borrowedValues],
                    ['name' => 'Returned', 'color' => '#10b981', 'values' => $returnedValues],
                ]
            ),
            'stats' => [
                $this->stat('Borrowed', number_format($totalBorrowed), 'Released quantity', 'fa-solid fa-arrow-up-right-from-square', 'danger'),
                $this->stat('Returned', number_format($totalReturned), 'All returned items', 'fa-solid fa-rotate-left', 'success'),
                $this->stat('Outstanding', number_format(max($totalBorrowed - $totalReturned, 0)), 'Not yet processed as returned, damaged, or missing', 'fa-solid fa-scale-balanced', 'warning'),
                $this->stat('Damaged/Missing', number_format($damagedOrMissing), 'Logged return issues', 'fa-solid fa-triangle-exclamation', 'danger'),
            ],
            'headers' => ['SKU', 'Item', 'Borrowed', 'Returned Good', 'Damaged/Missing', 'Outstanding', 'Last Activity'],
            'rows' => $rows,
            'empty' => 'No borrow or return records found.',
        ];
    }

    private function stockMovementReport(array $filters = []): array
    {
        $events = collect();

        $batchesQuery = StockBatch::with('item');
        if (! empty($filters['category'])) {
            $batchesQuery->whereHas('item', fn ($query) => $query->where('category', $filters['category']));
        }
        $this->applyDateFilters($batchesQuery, 'date_received', $filters);
        foreach ($batchesQuery->get() as $batch) {
            $events->push([
                'date' => Carbon::parse($batch->date_received),
                'kind' => 'stock-in',
                'label' => 'Stock In',
                'reference' => $batch->supplier ?: 'Receiving',
                'name' => $batch->item?->name ?? 'Unknown item',
                'sku' => $batch->item?->sku ?? '—',
                'change' => (int) $batch->quantity,
                'detail' => 'Received',
                'location' => $batch->item?->storage_location ?? '—',
            ]);
        }

        $releasesQuery = BorrowRelease::with(['inventory', 'request']);
        if (! empty($filters['category'])) {
            $releasesQuery->whereHas('inventory', fn ($query) => $query->where('category', $filters['category']));
        }
        $this->applyDateFilters($releasesQuery, 'released_at', $filters);
        foreach ($releasesQuery->get() as $release) {
            $events->push([
                'date' => Carbon::parse($release->released_at),
                'kind' => 'release',
                'label' => 'Release',
                'reference' => $release->request?->request_code ?? 'Release #'.$release->id,
                'name' => $release->inventory?->name ?? 'Unknown item',
                'sku' => $release->inventory?->sku ?? '—',
                'change' => -((int) $release->quantity),
                'detail' => $release->delivery_status ?: 'Released',
                'location' => $release->location ?: '—',
            ]);
        }

        $returnsQuery = ReturnItem::with(['inventory', 'borrowRelease.request']);
        if (! empty($filters['category'])) {
            $returnsQuery->whereHas('inventory', fn ($query) => $query->where('category', $filters['category']));
        }
        $this->applyDateFilters($returnsQuery, 'created_at', $filters);
        foreach ($returnsQuery->get() as $return) {
            $kind = match ($return->condition) {
                'Good' => 'good-return',
                'Damaged' => 'damaged-return',
                default => 'missing',
            };
            $events->push([
                'date' => Carbon::parse($return->created_at),
                'kind' => $kind,
                'label' => match ($kind) {
                    'good-return' => 'Good Return',
                    'damaged-return' => 'Damaged Return',
                    default => 'Missing / Lost',
                },
                'reference' => $return->borrowRelease?->request?->request_code ?? 'Unlinked legacy return',
                'name' => $return->inventory?->name ?? 'Unknown item',
                'sku' => $return->inventory?->sku ?? '—',
                'quantity' => (int) $return->quantity,
                'change' => $return->condition === 'Good' ? (int) $return->quantity : 0,
                'detail' => $return->condition,
                'location' => $return->borrowRelease?->location ?? '—',
            ]);
        }

        if (! empty($filters['status'])) {
            $events = $events->where('kind', $filters['status']);
        }

        $events = $events->sortByDesc(fn (array $event): int => $event['date']->getTimestamp())->values();
        $monthly = $events->groupBy(fn (array $event): string => $event['date']->format('Y-m'));
        $labels = $monthly->keys()->map(fn (string $month): string => Carbon::createFromFormat('Y-m', $month)->format('M Y'))->all();
        $received = $monthly->map(fn (Collection $group): int => (int) $group->where('kind', 'stock-in')->sum('change'))->values()->all();
        $released = $monthly->map(fn (Collection $group): int => (int) abs($group->where('kind', 'release')->sum('change')))->values()->all();
        $restocked = $monthly->map(fn (Collection $group): int => (int) $group->where('kind', 'good-return')->sum('change'))->values()->all();
        $rows = $events->map(fn (array $event): array => $this->tableRow([
            $this->cell($event['date']->format('M d, Y')),
            $this->cell($event['label']),
            $this->cell($event['reference']),
            $this->cell($event['name']),
            $this->cell($event['sku'], mono: true),
            $this->cell($event['change'] > 0 ? '+'.number_format($event['change']) : number_format($event['change']), $event['change'] > 0 ? 'success' : ($event['change'] < 0 ? 'danger' : 'warning')),
            $this->cell($event['detail']),
            $this->cell($event['location']),
        ]))->all();

        $totalReceived = (int) $events->where('kind', 'stock-in')->sum('change');
        $totalReleased = (int) abs($events->where('kind', 'release')->sum('change'));
        $totalRestocked = (int) $events->where('kind', 'good-return')->sum('change');
        $nonRestocked = (int) $events->whereIn('kind', ['damaged-return', 'missing'])->sum('quantity');

        return [
            'title' => 'Stock Movement Ledger',
            'subtitle' => 'Receipts, releases, and returns from recorded batch and transaction history.',
            'chartTitle' => 'Monthly Quantity Movement',
            'chart' => $this->chart($labels, [
                ['name' => 'Received', 'color' => '#176b57', 'values' => $received],
                ['name' => 'Released', 'color' => '#b42318', 'values' => $released],
                ['name' => 'Good Returns', 'color' => '#175c75', 'values' => $restocked],
            ]),
            'stats' => [
                $this->stat('Received', number_format($totalReceived), 'Stock batches', 'fa-solid fa-arrow-down', 'success'),
                $this->stat('Released', number_format($totalReleased), 'Recorded releases', 'fa-solid fa-arrow-up-right-from-square', 'danger'),
                $this->stat('Restocked', number_format($totalRestocked), 'Good returns only', 'fa-solid fa-rotate-left', 'success'),
                $this->stat('Not Restocked', number_format(abs($nonRestocked)), 'Damaged or missing', 'fa-solid fa-triangle-exclamation', 'warning'),
            ],
            'headers' => ['Date', 'Movement', 'Reference', 'Item', 'SKU', 'Stock Effect', 'Condition / Status', 'Location'],
            'rows' => $rows,
            'empty' => 'No stock movements match the selected filters.',
        ];
    }

    private function distributionReport(array $filters = []): array
    {
        $releasesQuery = BorrowRelease::with(['inventory', 'request'])->latest('released_at');
        if (! empty($filters['category'])) {
            $releasesQuery->whereHas('inventory', fn ($query) => $query->where('category', $filters['category']));
        }
        if (! empty($filters['status'])) {
            $releasesQuery->where('delivery_status', $filters['status']);
        }
        $this->applyDateFilters($releasesQuery, 'released_at', $filters);
        $releases = $releasesQuery->get();

        $locationTotals = $releases
            ->groupBy(fn (BorrowRelease $release): string => trim($release->location ?: 'Unassigned'))
            ->map(fn (Collection $records): int => (int) $records->sum('quantity'))
            ->sortDesc();

        $rows = $releases
            ->groupBy(fn (BorrowRelease $release): string => trim($release->location ?: 'Unassigned'))
            ->map(function (Collection $records, string $location): array {
                $topItem = $records
                    ->groupBy('inventory_id')
                    ->map(fn (Collection $itemRecords): int => (int) $itemRecords->sum('quantity'))
                    ->sortDesc()
                    ->keys()
                    ->first();

                $topItemRecord = $records->firstWhere('inventory_id', $topItem);
                $latestRelease = $records->sortByDesc('released_at')->first();

                return [
                    'quantity' => (int) $records->sum('quantity'),
                    'row' => $this->tableRow([
                        $this->cell($location),
                        $this->cell(number_format($records->count())),
                        $this->cell(number_format($records->pluck('inventory_id')->unique()->count())),
                        $this->cell(number_format($records->sum('quantity')), 'success'),
                        $this->cell($topItemRecord?->inventory?->name ?? '-'),
                        $this->cell($latestRelease?->released_at ? $latestRelease->released_at->format('M d, Y') : '-'),
                    ]),
                ];
            })
            ->sortByDesc('quantity')
            ->pluck('row')
            ->values()
            ->all();

        $topLocation = $locationTotals->keys()->first() ?: '-';

        return [
            'title' => 'Supply Distribution',
            'subtitle' => 'Where released supplies were distributed and which locations received the most.',
            'chartTitle' => 'Distributed Quantity by Destination',
            'chart' => $this->chart(
                $locationTotals->keys()->take(8)->values()->all(),
                [[
                    'name' => 'Distributed',
                    'color' => '#0ea5e9',
                    'values' => $locationTotals->take(8)->values()->map(fn ($value): int => (int) $value)->all(),
                ]]
            ),
            'stats' => [
                $this->stat('Distributed', number_format($releases->sum('quantity')), 'Total released quantity', 'fa-solid fa-truck-ramp-box', 'success'),
                $this->stat('Destinations', number_format($locationTotals->count()), 'Unique locations', 'fa-solid fa-location-dot'),
                $this->stat('Releases', number_format($releases->count()), 'Distribution records', 'fa-solid fa-right-left'),
                $this->stat('Top Destination', $topLocation, 'Highest quantity received', 'fa-solid fa-ranking-star', 'warning'),
            ],
            'headers' => ['Destination', 'Releases', 'Items', 'Quantity Distributed', 'Top Item', 'Latest Release'],
            'rows' => $rows,
            'empty' => 'No supply distribution records found.',
        ];
    }

    private function monthBuckets(int $months = 6): Collection
    {
        $start = now()->startOfMonth()->subMonths($months - 1);

        return collect(range(0, $months - 1))->map(function (int $offset) use ($start): array {
            $month = $start->copy()->addMonths($offset);

            return [
                'label' => $month->format('M'),
                'fullLabel' => $month->format('M Y'),
                'start' => $month->copy()->startOfMonth(),
                'end' => $month->copy()->endOfMonth(),
            ];
        });
    }

    private function monthBucketsFor(array $filters): Collection
    {
        if (empty($filters['start_date']) && empty($filters['end_date'])) {
            return $this->monthBuckets();
        }

        $start = ! empty($filters['start_date'])
            ? Carbon::parse($filters['start_date'])->startOfMonth()
            : Carbon::parse($filters['end_date'])->subMonths(5)->startOfMonth();
        $end = ! empty($filters['end_date'])
            ? Carbon::parse($filters['end_date'])->endOfMonth()
            : now()->endOfMonth();
        $months = collect();

        while ($start->lte($end) && $months->count() < 120) {
            $month = $start->copy();
            $months->push([
                'label' => $month->format('M'),
                'fullLabel' => $month->format('M Y'),
                'start' => $month->copy()->startOfMonth(),
                'end' => $month->copy()->endOfMonth(),
            ]);
            $start->addMonth();
        }

        return $months;
    }

    private function applyDateFilters($query, string $column, array $filters): void
    {
        if (! empty($filters['start_date'])) {
            $query->whereDate($column, '>=', $filters['start_date']);
        }
        if (! empty($filters['end_date'])) {
            $query->whereDate($column, '<=', $filters['end_date']);
        }
    }

    private function dateInRange($date, CarbonInterface $start, CarbonInterface $end): bool
    {
        if (! $date) {
            return false;
        }

        $date = $date instanceof CarbonInterface ? $date : Carbon::parse($date);

        return $date->between($start, $end, true);
    }

    private function latestDate(array $dates): ?CarbonInterface
    {
        return collect($dates)
            ->filter()
            ->map(fn ($date): CarbonInterface => $date instanceof CarbonInterface ? $date : Carbon::parse($date))
            ->sortByDesc(fn (CarbonInterface $date): int => $date->getTimestamp())
            ->first();
    }

    private function chart(array $labels, array $series, array $labelMeta = []): array
    {
        $max = collect($series)
            ->flatMap(fn (array $set): array => $set['values'])
            ->max();

        return [
            'labels' => $labels,
            'series' => $series,
            'max' => max((int) $max, 1),
            'labelMeta' => $labelMeta,
        ];
    }

    private function stat(string $label, string $value, string $hint, string $icon, string $tone = ''): array
    {
        return compact('label', 'value', 'hint', 'icon', 'tone');
    }

    private function cell($value, string $tone = '', bool $mono = false): array
    {
        return [
            'value' => $value === null || $value === '' ? '-' : (string) $value,
            'tone' => $tone,
            'mono' => $mono,
        ];
    }

    private function tableRow(array $cells): array
    {
        return [
            'cells' => $cells,
            'csv' => collect($cells)->pluck('value')->all(),
        ];
    }
}
