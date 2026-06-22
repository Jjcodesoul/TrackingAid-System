<?php

namespace App\Http\Controllers;

use App\Models\BorrowRelease;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\Request as SupplyRequest;
use App\Models\ReturnItem;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    private const REPORTS = [
        'inventory' => 'Inventory Report',
        'request' => 'Request Report',
        'borrow-return' => 'Borrowed / Returned',
        'distribution' => 'Supply Distribution',
    ];

    public function index(HttpRequest $request)
    {
        $activeReport = $this->activeReport($request);
        $report = $this->buildReport($activeReport);

        return view('reports.index', [
            'tabs' => self::REPORTS,
            'activeReport' => $activeReport,
            'report' => $report,
        ]);
    }

    public function exportCsv(HttpRequest $request)
    {
        $activeReport = $this->activeReport($request);
        $report = $this->buildReport($activeReport);
        $filename = $activeReport . '-report-' . now()->format('Ymd-His') . '.csv';

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
        $report = (string) $request->query('report', 'inventory');

        return array_key_exists($report, self::REPORTS) ? $report : 'inventory';
    }

    private function buildReport(string $report): array
    {
        return match ($report) {
            'request' => $this->requestReport(),
            'borrow-return' => $this->borrowReturnReport(),
            'distribution' => $this->distributionReport(),
            default => $this->inventoryReport(),
        };
    }

    private function inventoryReport(): array
    {
        $items = Item::with('stockBatches')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $stockFor = fn (Item $item): int => (int) $item->stockBatches->sum('quantity');
        $totalStock = $items->sum(fn (Item $item): int => $stockFor($item));
        $lowStock = $items->filter(fn (Item $item): bool => $stockFor($item) > 0 && $stockFor($item) < 10)->count();
        $outOfStock = $items->filter(fn (Item $item): bool => $stockFor($item) <= 0)->count();
        $expiring = $items->filter(function (Item $item): bool {
            return $item->expiration_date
                && Carbon::parse($item->expiration_date)->lte(now()->addDays(30));
        })->count();

        $categoryTotals = $items
            ->groupBy(fn (Item $item): string => strtoupper($item->category ?: 'Uncategorized'))
            ->map(fn (Collection $items): int => $items->sum(fn (Item $item): int => $stockFor($item)))
            ->sortDesc();

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
                $this->cell($item->expiration_date ? Carbon::parse($item->expiration_date)->format('M d, Y') : '-'),
            ]);
        })->values()->all();

        return [
            'title' => 'Inventory Levels',
            'subtitle' => 'Current item stock by category, location, and stock status.',
            'chartTitle' => 'Stock Levels by Category',
            'chart' => $this->chart(
                $categoryTotals->keys()->values()->all(),
                [[
                    'name' => 'Stock',
                    'color' => '#10b981',
                    'values' => $categoryTotals->values()->map(fn ($value): int => (int) $value)->all(),
                ]]
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

    private function requestReport(): array
    {
        $requests = SupplyRequest::with('inventory')->latest()->get();
        $months = $this->monthBuckets();
        $approvedStatuses = ['Approved', 'Released'];

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
                $this->cell($approvalRate . '%'),
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

    private function borrowReturnReport(): array
    {
        $releases = BorrowRelease::with(['inventory', 'request'])->get();
        $returns = ReturnItem::with('inventory')->get();
        $months = $this->monthBuckets();

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
            $outstanding = max($borrowed - $goodReturns, 0);
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
                $this->stat('Outstanding', number_format(max($totalBorrowed - $returns->where('condition', 'Good')->sum('quantity'), 0)), 'Borrowed minus good returns', 'fa-solid fa-scale-balanced', 'warning'),
                $this->stat('Damaged/Missing', number_format($damagedOrMissing), 'Logged return issues', 'fa-solid fa-triangle-exclamation', 'danger'),
            ],
            'headers' => ['SKU', 'Item', 'Borrowed', 'Returned Good', 'Damaged/Missing', 'Outstanding', 'Last Activity'],
            'rows' => $rows,
            'empty' => 'No borrow or return records found.',
        ];
    }

    private function distributionReport(): array
    {
        $releases = BorrowRelease::with(['inventory', 'request'])
            ->latest('released_at')
            ->get();

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

    private function chart(array $labels, array $series): array
    {
        $max = collect($series)
            ->flatMap(fn (array $set): array => $set['values'])
            ->max();

        return [
            'labels' => $labels,
            'series' => $series,
            'max' => max((int) $max, 1),
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
