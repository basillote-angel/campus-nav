<?php

namespace App\Http\Controllers;

use App\Enums\ClaimStatus;
use App\Enums\FoundItemStatus;
use App\Enums\LostItemStatus;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\ClaimedItem;
use App\Models\CollectionReminderLog;
use App\Models\FoundItem;
use App\Models\ItemMatch;
use App\Models\LostItem;
use App\Models\User;
use App\Services\AnalyticsCounter;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $ttl = now()->addSeconds((int) env('DASHBOARD_CACHE_TTL', 30));
        $claimedStatuses = [
            FoundItemStatus::CLAIM_APPROVED->value,
            FoundItemStatus::COLLECTED->value,
        ];

        $foundStatusCounts = AnalyticsCounter::getFoundStatusCounts();
        $lostStatusCounts = AnalyticsCounter::getLostStatusCounts();

        $claimedItems = ($foundStatusCounts[FoundItemStatus::CLAIM_APPROVED->value] ?? 0)
            + ($foundStatusCounts[FoundItemStatus::COLLECTED->value] ?? 0);
        $unclaimedItems = $foundStatusCounts[FoundItemStatus::FOUND_UNCLAIMED->value] ?? 0;
        $pendingClaims = $foundStatusCounts[FoundItemStatus::CLAIM_PENDING->value] ?? 0;
        $foundItems = array_sum($foundStatusCounts);
        $lostItems = array_sum($lostStatusCounts);

        // Basic Statistics
        $totalUsers = Cache::remember('dash.totalUsers', $ttl, fn () => User::count());
        $totalUsersLastMonth = Cache::remember('dash.totalUsersLastMonth', $ttl, fn () => User::where('created_at', '>=', now()->subMonth())->count());
        $usersGrowthPercent = $totalUsers > 0 ? round((($totalUsers - $totalUsersLastMonth) / max($totalUsersLastMonth, 1)) * 100, 1) : 0;

        $claimedItemsLastWeek = Cache::remember('dash.claimedItemsLastWeek', $ttl, fn () => FoundItem::whereIn('status', $claimedStatuses)->where('approved_at', '>=', now()->subWeek())->count());
        $claimedGrowthPercent = $claimedItems > 0 ? round((($claimedItems - $claimedItemsLastWeek) / max($claimedItemsLastWeek, 1)) * 100, 1) : 0;

        $decisionMetrics = Cache::remember('dash.decisionMetrics', $ttl, function () {
            $approvedAverage = ClaimedItem::whereNotNull('approved_at')
                ->whereNotNull('created_at')
                ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, created_at, approved_at)) as minutes'))
                ->value('minutes');

            $rejectedAverage = ClaimedItem::whereNotNull('rejected_at')
                ->whereNotNull('created_at')
                ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, created_at, rejected_at)) as minutes'))
                ->value('minutes');

            $combinedAverage = ClaimedItem::where(function ($query) {
                $query->whereNotNull('approved_at')->orWhereNotNull('rejected_at');
            })
                ->whereNotNull('created_at')
                ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, created_at, COALESCE(approved_at, rejected_at))) as minutes'))
                ->value('minutes');

            return [
                'combined' => $combinedAverage ? (float) $combinedAverage : null,
                'approved' => $approvedAverage ? (float) $approvedAverage : null,
                'rejected' => $rejectedAverage ? (float) $rejectedAverage : null,
            ];
        });

        $decisionAverageLabel = $decisionMetrics['combined'] !== null
            ? CarbonInterval::minutes((int) round($decisionMetrics['combined']))->cascade()->forHumans([
                'short' => true,
                'parts' => 2,
            ])
            : 'N/A';

        $decisionBreakdown = [
            'approved' => $decisionMetrics['approved'] !== null
                ? CarbonInterval::minutes((int) round($decisionMetrics['approved']))->cascade()->forHumans([
                    'short' => true,
                    'parts' => 2,
                ])
                : 'N/A',
            'rejected' => $decisionMetrics['rejected'] !== null
                ? CarbonInterval::minutes((int) round($decisionMetrics['rejected']))->cascade()->forHumans([
                    'short' => true,
                    'parts' => 2,
                ])
                : 'N/A',
        ];

        $oldestUnclaimed = Cache::remember('dash.oldestUnclaimed', $ttl, fn () => FoundItem::where('status', FoundItemStatus::FOUND_UNCLAIMED->value)->oldest('date_found')->first());
        $oldestUnclaimedDays = $oldestUnclaimed ? now()->diffInDays($oldestUnclaimed->date_found) : 0;

        $foundItemsLastMonth = Cache::remember('dash.foundItemsLastMonth', $ttl, fn () => FoundItem::where('created_at', '>=', now()->subMonth())->count());
        $foundGrowthPercent = $foundItems > 0 ? round((($foundItems - $foundItemsLastMonth) / max($foundItemsLastMonth, 1)) * 100, 1) : 0;

        $lostItemsLastWeek = Cache::remember('dash.lostItemsLastWeek', $ttl, fn () => LostItem::where('created_at', '>=', now()->subWeek())->count());
        $lostGrowthPercent = $lostItems > 0 ? round((($lostItems - $lostItemsLastWeek) / max($lostItemsLastWeek, 1)) * 100, 1) : 0;

        // Pending Claims Statistics
        $urgentClaims = Cache::remember('dash.urgentClaims', $ttl, fn () => FoundItem::where('status', FoundItemStatus::CLAIM_PENDING->value)
            ->whereNotNull('claimed_at')
            ->where('claimed_at', '<', now()->subHours(24))
            ->count());

        // Successful Matches
        $successfulMatches = Cache::remember('dash.successfulMatches', $ttl, fn () => ItemMatch::where('status', 'confirmed')->count());
        $totalMatches = Cache::remember('dash.totalMatches', $ttl, fn () => ItemMatch::count());
        $matchSuccessRate = $totalMatches > 0 ? round(($successfulMatches / $totalMatches) * 100, 1) : 0;

        // Items Collected This Month
        $collectedThisMonth = Cache::remember('dash.collectedThisMonth', $ttl, fn () => FoundItem::where('status', FoundItemStatus::COLLECTED->value)
            ->whereNotNull('collected_at')
            ->where('collected_at', '>=', now()->startOfMonth())
            ->count());
        $collectedLastMonth = Cache::remember('dash.collectedLastMonth', $ttl, fn () => FoundItem::where('status', FoundItemStatus::COLLECTED->value)
            ->whereNotNull('collected_at')
            ->whereBetween('collected_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->count());
        $collectedGrowthPercent = $collectedThisMonth > 0 ? round((($collectedThisMonth - $collectedLastMonth) / max($collectedLastMonth, 1)) * 100, 1) : 0;

        // Collection Metrics
        $collectionMetrics = Cache::remember('dash.collectionMetrics', $ttl, function () {
            $pending = FoundItem::where('status', FoundItemStatus::CLAIM_APPROVED->value)
                ->whereNull('collected_at')
                ->count();

            $overdue = FoundItem::where('status', FoundItemStatus::CLAIM_APPROVED->value)
                ->whereNull('collected_at')
                ->whereNotNull('collection_deadline')
                ->where('collection_deadline', '<', now())
                ->count();

            $collectedTotal = FoundItem::where('status', FoundItemStatus::COLLECTED->value)
                ->whereNotNull('collected_at')
                ->count();

            $averageMinutes = FoundItem::where('status', FoundItemStatus::COLLECTED->value)
                ->whereNotNull('collected_at')
                ->whereNotNull('approved_at')
                ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, approved_at, collected_at)) as avg_minutes'))
                ->value('avg_minutes');

            return [
                'pending' => $pending,
                'overdue' => $overdue,
                'collected' => $collectedTotal,
                'avg_minutes' => $averageMinutes ? (float) $averageMinutes : null,
            ];
        });

        $collectionPending = $collectionMetrics['pending'];
        $collectionOverdue = $collectionMetrics['overdue'];
        $collectionAverageMinutes = $collectionMetrics['avg_minutes'];
        $collectionAverageLabel = $collectionAverageMinutes !== null
            ? CarbonInterval::minutes((int) round($collectionAverageMinutes))->cascade()->forHumans([
                'short' => true,
                'parts' => 2,
            ])
            : 'N/A';

        // AI Match Success Rate (Claimed / Found Items)
        $matchSuccessRatePercent = $foundItems > 0 ? round(($claimedItems / $foundItems) * 100, 1) : 0;
        $matchSuccessRateLastWeek = Cache::remember('dash.matchSuccessRateLastWeek', $ttl, function() use ($claimedStatuses) {
            $claimedLastWeek = FoundItem::whereIn('status', $claimedStatuses)->where('approved_at', '>=', now()->subWeek())->count();
            $foundLastWeek = FoundItem::where('created_at', '>=', now()->subWeek())->count();
            return $foundLastWeek > 0 ? round(($claimedLastWeek / $foundLastWeek) * 100, 1) : 0;
        });
        $matchSuccessGrowthPercent = round($matchSuccessRatePercent - $matchSuccessRateLastWeek, 1);

        // Chart Data - Posts Over Time (Last 7 Days) - Optimized with single query using GROUP BY
        $postsOverTimeData = Cache::remember('dash.postsOverTimeData', $ttl, function() {
            $startDate = now()->subDays(6)->startOfDay();
            $endDate = now()->endOfDay();
            
            // Get lost items count grouped by date in single query
            $lostCounts = LostItem::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray();
            
            // Get found items count grouped by date in single query
            $foundCounts = FoundItem::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray();
            
            $days = [];
            $lostData = [];
            $foundData = [];
            
            // Build arrays with same structure as before
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $dateKey = $date->format('Y-m-d');
                $days[] = $date->format('D');
                
                $lostData[] = $lostCounts[$dateKey] ?? 0;
                $foundData[] = $foundCounts[$dateKey] ?? 0;
            }
            
            return [
                'days' => $days,
                'lost' => $lostData,
                'found' => $foundData,
            ];
        });

        // Chart Data - Item Status Distribution
        $activeLostItems = $lostStatusCounts[LostItemStatus::LOST_REPORTED->value] ?? 0;
        $activeFoundItems = $foundStatusCounts[FoundItemStatus::FOUND_UNCLAIMED->value] ?? 0;
        $itemStatusData = [
            'activeLost' => $activeLostItems,
            'activeFound' => $activeFoundItems,
            'claimed' => $claimedItems,
            'returned' => $foundStatusCounts[FoundItemStatus::COLLECTED->value] ?? 0,
        ];

        // Chart Data - Top Categories
        $topCategories = Cache::remember('dash.topCategories', $ttl, function() {
            return Category::withCount(['lostItems', 'foundItems'])
                ->get()
                ->map(function($category) {
                    $category->total_items = $category->lost_items_count + $category->found_items_count;
                    return $category;
                })
                ->sortByDesc('total_items')
                ->take(6)
                ->values()
                ->all();
        });

        // Recent Activity
        $recentActivities = Cache::remember('dash.recentActivities', $ttl, fn () => ActivityLog::with('user')
            ->latest('created_at')
            ->take(20)
            ->get());

        // Pending Claims for Quick Actions
        $pendingClaimsList = Cache::remember('dash.pendingClaimsList', $ttl, fn () => FoundItem::where('status', FoundItemStatus::CLAIM_PENDING->value)
            ->with(['claimedBy', 'category'])
            ->orderBy('claimed_at', 'asc')
            ->take(5)
            ->get());

        // Items Near Collection Deadline
        $itemsNearDeadline = Cache::remember('dash.itemsNearDeadline', $ttl, fn () => FoundItem::where('status', FoundItemStatus::CLAIM_APPROVED->value)
            ->whereNull('collected_at')
            ->whereNotNull('collection_deadline')
            ->where('collection_deadline', '>=', now())
            ->where('collection_deadline', '<=', now()->addDays(3))
            ->with(['claimedBy', 'category'])
            ->orderBy('collection_deadline', 'asc')
            ->take(5)
            ->get());

        $claimConflictStats = Cache::remember('dash.claimConflictStats', $ttl, fn () => $this->buildClaimConflictStats());
        $reminderMetrics = Cache::remember('dash.reminderMetrics', $ttl, fn () => $this->buildReminderEffectivenessMetrics());

        return view('dashboard', compact(
            'totalUsers',
            'usersGrowthPercent',
            'claimedItems',
            'claimedGrowthPercent',
            'unclaimedItems',
            'oldestUnclaimedDays',
            'foundItems',
            'foundGrowthPercent',
            'lostItems',
            'lostGrowthPercent',
            'pendingClaims',
            'urgentClaims',
            'successfulMatches',
            'matchSuccessRate',
            'collectedThisMonth',
            'collectedGrowthPercent',
            'matchSuccessRatePercent',
            'matchSuccessGrowthPercent',
            'collectionPending',
            'collectionOverdue',
            'collectionAverageLabel',
            'decisionAverageLabel',
            'decisionMetrics',
            'decisionBreakdown',
            'postsOverTimeData',
            'itemStatusData',
            'topCategories',
            'recentActivities',
            'pendingClaimsList',
            'itemsNearDeadline',
            'claimConflictStats',
            'reminderMetrics'
        ));
    }

    protected function buildClaimConflictStats(): array
    {
        $rows = ClaimedItem::query()
            ->select([
                'found_items.id',
                'found_items.title',
                'found_items.approved_at',
                DB::raw('COUNT(claimed_items.id) as total_claims'),
                DB::raw('MIN(claimed_items.created_at) as first_claim_at'),
                DB::raw('MAX(claimed_items.created_at) as latest_claim_at'),
                'categories.name as category_name',
            ])
            ->join('found_items', 'found_items.id', '=', 'claimed_items.found_item_id')
            ->leftJoin('categories', 'categories.id', '=', 'found_items.category_id')
            ->where('claimed_items.status', ClaimStatus::PENDING->value)
            ->groupBy('found_items.id', 'found_items.title', 'found_items.approved_at', 'categories.name')
            ->havingRaw('COUNT(claimed_items.id) > 1')
            ->get();

        if ($rows->isEmpty()) {
            return [
                'total' => 0,
                'topCategories' => [],
                'oldest' => null,
                'hotlist' => [],
            ];
        }

        $categoryBreakdown = $rows->groupBy(function ($row) {
            return $row->category_name ?? 'Uncategorized';
        })->map(function ($group, $name) {
            return [
                'name' => $name,
                'count' => $group->count(),
            ];
        })->sortByDesc('count')->values();

        $oldestRow = $rows->sortBy('first_claim_at')->first();
        $oldestFirstClaim = Carbon::parse($oldestRow->first_claim_at);
        $oldest = [
            'id' => $oldestRow->id,
            'title' => $oldestRow->title,
            'category' => $oldestRow->category_name ?? 'Uncategorized',
            'totalClaims' => (int) $oldestRow->total_claims,
            'firstClaimAt' => $oldestFirstClaim->toIso8601String(),
            'ageHuman' => $oldestFirstClaim->diffForHumans(),
            'ageDays' => $oldestFirstClaim->diffInDays(now()),
        ];

        $hotlist = $rows->sortByDesc('total_claims')
            ->take(5)
            ->map(function ($row) {
                $first = Carbon::parse($row->first_claim_at);
                return [
                    'id' => $row->id,
                    'title' => $row->title,
                    'category' => $row->category_name ?? 'Uncategorized',
                    'totalClaims' => (int) $row->total_claims,
                    'firstClaimAt' => $first->toIso8601String(),
                    'ageHuman' => $first->diffForHumans(),
                ];
            })
            ->values()
            ->all();

        return [
            'total' => $rows->count(),
            'topCategories' => $categoryBreakdown->take(5)->values()->all(),
            'oldest' => $oldest,
            'hotlist' => $hotlist,
        ];
    }

    protected function buildReminderEffectivenessMetrics(): array
    {
        $windowDays = 30;
        $since = now()->subDays($windowDays);

        $rows = CollectionReminderLog::query()
            ->select([
                'stage',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as converted"),
                DB::raw("AVG(CASE WHEN status = 'converted' THEN minutes_to_collection END) as avg_minutes"),
            ])
            ->where('created_at', '>=', $since)
            ->groupBy('stage')
            ->get();

        $stageLabels = [
            'three_day' => '3-day auto',
            'one_day' => '1-day auto',
            'manual' => 'Manual',
            null => 'Unspecified',
            '' => 'Unspecified',
        ];

        $stages = $rows->map(function ($row) use ($stageLabels) {
            $label = $stageLabels[$row->stage] ?? ucfirst(str_replace('_', ' ', (string) $row->stage));
            $total = (int) $row->total;
            $converted = (int) $row->converted;
            $rate = $total > 0 ? round(($converted / $total) * 100, 1) : 0.0;
            $avgMinutes = $row->avg_minutes ? (float) $row->avg_minutes : null;

            return [
                'stage' => $row->stage ?? 'unknown',
                'label' => $label,
                'total' => $total,
                'converted' => $converted,
                'conversionRate' => $rate,
                'avgMinutes' => $avgMinutes,
            ];
        })->sortByDesc('conversionRate')->values()->all();

        $totals = CollectionReminderLog::query()
            ->where('created_at', '>=', $since)
            ->selectRaw("COUNT(*) as total, SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as converted")
            ->first();

        $totalSent = $totals?->total ?? 0;
        $totalConverted = $totals?->converted ?? 0;
        $overallRate = $totalSent > 0 ? round(($totalConverted / $totalSent) * 100, 1) : 0.0;

        return [
            'windowLabel' => "Last {$windowDays} days",
            'totalSent' => (int) $totalSent,
            'totalConverted' => (int) $totalConverted,
            'overallRate' => $overallRate,
            'stages' => $stages,
        ];
    }

    /**
     * Get chart data for Posts Over Time based on time range
     */
    public function getChartData(Request $request)
    {
        $range = $request->input('range', '7d'); // Default to 7 days
        
        // Determine days based on range
        switch($range) {
            case '30d':
                $days = 30;
                break;
            case '90d':
                $days = 90;
                break;
            case '7d':
            default:
                $days = 7;
                break;
        }

        // Optimized: Use GROUP BY instead of multiple queries in loop
        $startDate = now()->subDays($days - 1)->startOfDay();
        $endDate = now()->endOfDay();
        
        // Get lost items count grouped by date in single query
        $lostCounts = LostItem::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();
        
        // Get found items count grouped by date in single query
        $foundCounts = FoundItem::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();
        
        $labels = [];
        $lostData = [];
        $foundData = [];
        
        // Determine format based on range
        $format = $days <= 7 ? 'D' : ($days <= 30 ? 'M d' : 'M d');
        
        // Build arrays with same structure as before
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateKey = $date->format('Y-m-d');
            $labels[] = $date->format($format);
            
            $lostData[] = $lostCounts[$dateKey] ?? 0;
            $foundData[] = $foundCounts[$dateKey] ?? 0;
        }
        
        return response()->json([
            'labels' => $labels,
            'lost' => $lostData,
            'found' => $foundData,
        ]);
    }

    /**
     * Export dashboard analytics as CSV
     */
    public function exportAnalytics(Request $request)
    {
        $format = $request->input('format', 'csv');
        $type = $request->input('type', 'analytics'); // analytics or activity
        
        if ($type === 'activity') {
            return $this->exportActivityLogCsv();
        }
        
        if ($format === 'csv') {
            return $this->exportAnalyticsCsv();
        } elseif ($format === 'pdf') {
            return $this->exportDashboardPdf();
        }
        
        return redirect()->route('dashboard')->with('error', 'Invalid export format');
    }

    /**
     * Refresh dashboard data (clear cache)
     */
    public function refresh()
    {
        // Clear all dashboard cache keys
        $cacheKeys = [
            'dash.totalUsers',
            'dash.totalUsersLastMonth',
            'dash.claimedItems',
            'dash.claimedItemsLastWeek',
            'dash.unclaimedItems',
            'dash.oldestUnclaimed',
            'dash.foundItems',
            'dash.foundItemsLastMonth',
            'dash.lostItems',
            'dash.lostItemsLastWeek',
            'dash.pendingClaims',
            'dash.urgentClaims',
            'dash.successfulMatches',
            'dash.totalMatches',
            'dash.collectedThisMonth',
            'dash.collectedLastMonth',
            'dash.matchSuccessRateLastWeek',
            'dash.postsOverTimeData',
            'dash.activeLostItems',
            'dash.activeFoundItems',
            'dash.topCategories',
            'dash.recentActivities',
            'dash.pendingClaimsList',
            'dash.itemsNearDeadline',
            'dash.collectionMetrics',
            'dash.decisionMetrics',
            'dash.claimConflictStats',
            'dash.reminderMetrics',
        ];
        
        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

		Cache::forget('analytics:found_status_counts');
		Cache::forget('analytics:lost_status_counts');
		AnalyticsCounter::ensurePrimed();
        
        return redirect()->route('dashboard')->with('success', 'Dashboard data refreshed successfully.');
    }

    /**
     * Export analytics data as CSV
     */
    private function exportAnalyticsCsv()
    {
        $filename = 'dashboard_analytics_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
			$foundStatusCounts = AnalyticsCounter::getFoundStatusCounts();
			$lostStatusCounts = AnalyticsCounter::getLostStatusCounts();
			$claimedItems = ($foundStatusCounts[FoundItemStatus::CLAIM_APPROVED->value] ?? 0)
				+ ($foundStatusCounts[FoundItemStatus::COLLECTED->value] ?? 0);
			$pendingClaims = $foundStatusCounts[FoundItemStatus::CLAIM_PENDING->value] ?? 0;
			$unclaimedItems = $foundStatusCounts[FoundItemStatus::FOUND_UNCLAIMED->value] ?? 0;
			$foundItems = array_sum($foundStatusCounts);
			$lostItems = array_sum($lostStatusCounts);
            
            // Headers
            fputcsv($file, ['Dashboard Analytics Report']);
            fputcsv($file, ['Generated', now()->format('Y-m-d H:i:s')]);
            fputcsv($file, []); // Empty row
            
            // Statistics Section
            fputcsv($file, ['STATISTICS']);
            fputcsv($file, ['Metric', 'Value', 'Growth %']);
            
			$totalUsers = User::count();
			$totalUsersLastMonth = User::where('created_at', '>=', now()->subMonth())->count();
			$usersGrowth = $totalUsers > 0 ? round((($totalUsers - $totalUsersLastMonth) / max($totalUsersLastMonth, 1)) * 100, 1) : 0;
			
            $collectedThisMonth = FoundItem::where('status', FoundItemStatus::COLLECTED->value)
                ->whereNotNull('collected_at')
                ->where('collected_at', '>=', now()->startOfMonth())
                ->count();
            
            $matchSuccessRate = $foundItems > 0 ? round(($claimedItems / $foundItems) * 100, 1) : 0;
            
            fputcsv($file, ['Total Users', $totalUsers, $usersGrowth . '%']);
            fputcsv($file, ['Lost Items', $lostItems, '']);
            fputcsv($file, ['Found Items', $foundItems, '']);
            fputcsv($file, ['Claimed Items', $claimedItems, '']);
            fputcsv($file, ['Pending Claims', $pendingClaims, '']);
            fputcsv($file, ['Unclaimed Items', $unclaimedItems, '']);
            fputcsv($file, ['Collected This Month', $collectedThisMonth, '']);
            fputcsv($file, ['AI Match Success Rate', $matchSuccessRate . '%', '']);
            
            fputcsv($file, []); // Empty row
            
            // Top Categories Section
            fputcsv($file, ['TOP CATEGORIES']);
            fputcsv($file, ['Category', 'Total Items', 'Lost Items', 'Found Items']);
            
            $topCategories = Category::withCount(['lostItems', 'foundItems'])
                ->get()
                ->map(function($category) {
                    $category->total_items = $category->lost_items_count + $category->found_items_count;
                    return $category;
                })
                ->sortByDesc('total_items')
                ->take(10);
            
            foreach ($topCategories as $category) {
                fputcsv($file, [
                    $category->name,
                    $category->total_items,
                    $category->lost_items_count,
                    $category->found_items_count,
                ]);
            }
            
            fputcsv($file, []); // Empty row
            
            // Recent Activity Section
            fputcsv($file, ['RECENT ACTIVITY (Last 20)']);
            fputcsv($file, ['Date', 'User', 'Action', 'Details']);
            
            $recentActivities = ActivityLog::with('user')
                ->latest('created_at')
                ->take(20)
                ->get();
            
            foreach ($recentActivities as $activity) {
                fputcsv($file, [
                    $activity->created_at->format('Y-m-d H:i:s'),
                    $activity->user ? $activity->user->name : 'System',
                    $activity->action,
                    $activity->details ?? '',
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export dashboard report as PDF (placeholder - returns message for now)
     */
    private function exportDashboardPdf()
    {
        // PDF export would require a library like dompdf or mpdf
        // For now, return a message indicating it's coming soon
        return redirect()->route('dashboard')->with('info', 'PDF export feature is coming soon. Please use CSV export for now.');
    }

    /**
     * Get dashboard data as JSON for auto-refresh
     */
    public function getDashboardData()
    {
		// This method bypasses cache for real-time data
		$claimedStatuses = [
			FoundItemStatus::CLAIM_APPROVED->value,
			FoundItemStatus::COLLECTED->value,
		];

		$foundStatusCounts = AnalyticsCounter::getFoundStatusCounts();
		$lostStatusCounts = AnalyticsCounter::getLostStatusCounts();

		$claimedItems = ($foundStatusCounts[FoundItemStatus::CLAIM_APPROVED->value] ?? 0)
			+ ($foundStatusCounts[FoundItemStatus::COLLECTED->value] ?? 0);
		$pendingClaims = $foundStatusCounts[FoundItemStatus::CLAIM_PENDING->value] ?? 0;
		$unclaimedItems = $foundStatusCounts[FoundItemStatus::FOUND_UNCLAIMED->value] ?? 0;
		$foundItems = array_sum($foundStatusCounts);
		$lostItems = array_sum($lostStatusCounts);

		$totalUsers = User::count();
		$totalUsersLastMonth = User::where('created_at', '>=', now()->subMonth())->count();
		$usersGrowthPercent = $totalUsers > 0 ? round((($totalUsers - $totalUsersLastMonth) / max($totalUsersLastMonth, 1)) * 100, 1) : 0;

		$claimedItemsLastWeek = FoundItem::whereIn('status', $claimedStatuses)->where('approved_at', '>=', now()->subWeek())->count();
		$claimedGrowthPercent = $claimedItems > 0 ? round((($claimedItems - $claimedItemsLastWeek) / max($claimedItemsLastWeek, 1)) * 100, 1) : 0;
		$foundLastWeek = FoundItem::where('created_at', '>=', now()->subWeek())->count();

        $decisionMetrics = FoundItem::whereNotNull('approved_at')
            ->whereNotNull('created_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, created_at, approved_at)) as minutes'))
            ->value('minutes');

        $rejectedAverage = FoundItem::whereNotNull('rejected_at')
            ->whereNotNull('created_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, created_at, rejected_at)) as minutes'))
            ->value('minutes');

        $combinedAverage = FoundItem::where(function ($query) {
            $query->whereNotNull('approved_at')->orWhereNotNull('rejected_at');
        })
            ->whereNotNull('created_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, created_at, COALESCE(approved_at, rejected_at))) as minutes'))
            ->value('minutes');

		$decisionAverageLabel = $combinedAverage !== null
			? CarbonInterval::minutes((int) round($combinedAverage))->cascade()->forHumans([
				'short' => true,
				'parts' => 2,
			])
			: 'N/A';

        $oldestUnclaimed = FoundItem::where('status', FoundItemStatus::FOUND_UNCLAIMED->value)->oldest('date_found')->first();
        $oldestUnclaimedDays = $oldestUnclaimed ? now()->diffInDays($oldestUnclaimed->date_found) : 0;

        $foundItemsLastMonth = FoundItem::where('created_at', '>=', now()->subMonth())->count();
        $foundGrowthPercent = $foundItems > 0 ? round((($foundItems - $foundItemsLastMonth) / max($foundItemsLastMonth, 1)) * 100, 1) : 0;

        $lostItemsLastWeek = LostItem::where('created_at', '>=', now()->subWeek())->count();
        $lostGrowthPercent = $lostItems > 0 ? round((($lostItems - $lostItemsLastWeek) / max($lostItemsLastWeek, 1)) * 100, 1) : 0;

        $urgentClaims = FoundItem::where('status', FoundItemStatus::CLAIM_PENDING->value)
            ->whereNotNull('claimed_at')
            ->where('claimed_at', '<', now()->subHours(24))
            ->count();

        $successfulMatches = ItemMatch::where('status', 'confirmed')->count();
        $totalMatches = ItemMatch::count();
        $matchSuccessRate = $totalMatches > 0 ? round(($successfulMatches / $totalMatches) * 100, 1) : 0;

        $collectedThisMonth = FoundItem::where('status', FoundItemStatus::COLLECTED->value)
            ->whereNotNull('collected_at')
            ->where('collected_at', '>=', now()->startOfMonth())
            ->count();
        $collectedLastMonth = FoundItem::where('status', FoundItemStatus::COLLECTED->value)
            ->whereNotNull('collected_at')
            ->whereBetween('collected_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->count();
        $collectedGrowthPercent = $collectedThisMonth > 0 ? round((($collectedThisMonth - $collectedLastMonth) / max($collectedLastMonth, 1)) * 100, 1) : 0;

        $matchSuccessRatePercent = $foundItems > 0 ? round(($claimedItems / $foundItems) * 100, 1) : 0;
        $matchSuccessRateLastWeek = $foundLastWeek > 0 ? round(($claimedItemsLastWeek / $foundLastWeek) * 100, 1) : 0;
        $matchSuccessGrowthPercent = round($matchSuccessRatePercent - $matchSuccessRateLastWeek, 1);

		$collectionPending = FoundItem::where('status', FoundItemStatus::CLAIM_APPROVED->value)
			->count();

		$collectionOverdue = FoundItem::where('status', FoundItemStatus::CLAIM_APPROVED->value)
			->whereNotNull('collection_deadline')
			->where('collection_deadline', '<', now())
			->count();

		$collectionAverageMinutes = FoundItem::where('status', FoundItemStatus::COLLECTED->value)
			->whereNotNull('collected_at')
			->whereNotNull('approved_at')
			->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, approved_at, collected_at)) as avg_minutes'))
			->value('avg_minutes');

		$collectionAverageLabel = $collectionAverageMinutes !== null
			? CarbonInterval::minutes((int) round($collectionAverageMinutes))->cascade()->forHumans([
				'short' => true,
				'parts' => 2,
			])
			: 'N/A';

		$decisionAverageMinutes = ClaimedItem::where(function ($query) {
			$query->whereNotNull('approved_at')->orWhereNotNull('rejected_at');
		})
			->whereNotNull('created_at')
			->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, created_at, COALESCE(approved_at, rejected_at))) as minutes'))
			->value('minutes');

		$decisionAverageLabel = $decisionAverageMinutes !== null
			? CarbonInterval::minutes((int) round($decisionAverageMinutes))->cascade()->forHumans([
				'short' => true,
				'parts' => 2,
			])
			: 'N/A';

		$decisionBreakdown = [
			'approved' => ClaimedItem::whereNotNull('approved_at')
				->whereNotNull('created_at')
				->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, created_at, approved_at)) as minutes'))
				->value('minutes'),
			'rejected' => ClaimedItem::whereNotNull('rejected_at')
				->whereNotNull('created_at')
				->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, created_at, rejected_at)) as minutes'))
				->value('minutes'),
		];

		$decisionBreakdown = array_map(function ($minutes) {
			if ($minutes === null) {
				return 'N/A';
			}
			return CarbonInterval::minutes((int) round($minutes))->cascade()->forHumans([
				'short' => true,
				'parts' => 2,
			]);
		}, $decisionBreakdown);

        // Recent activities (last 20)
        $recentActivities = ActivityLog::with('user')
            ->latest('created_at')
            ->take(20)
            ->get()
            ->map(function($activity) {
                return [
                    'id' => $activity->id,
                    'action' => $activity->action,
                    'details' => $activity->details ?? '',
                    'user_name' => $activity->user ? $activity->user->name : 'System',
                    'created_at' => $activity->created_at->format('Y-m-d H:i:s'),
                    'created_at_human' => $activity->created_at->diffForHumans(),
                ];
            });

        // Pending claims (top 5)
        $pendingClaimsList = FoundItem::where('status', FoundItemStatus::CLAIM_PENDING->value)
            ->with(['claimedBy', 'category'])
            ->orderBy('claimed_at', 'asc')
            ->take(5)
            ->get()
            ->map(function($claim) {
                return [
                    'id' => $claim->id,
                    'title' => $claim->title,
                    'claimed_by' => $claim->claimedBy ? $claim->claimedBy->name : 'Unknown',
                    'claimed_at' => $claim->claimed_at ? $claim->claimed_at->format('Y-m-d H:i:s') : null,
                    'claimed_at_human' => $claim->claimed_at ? $claim->claimed_at->diffForHumans() : null,
                ];
            });

        // Items near deadline (top 5)
        $itemsNearDeadline = FoundItem::where('status', FoundItemStatus::CLAIM_APPROVED->value)
            ->whereNull('collected_at')
            ->whereNotNull('collection_deadline')
            ->where('collection_deadline', '>=', now())
            ->where('collection_deadline', '<=', now()->addDays(3))
            ->with(['claimedBy', 'category'])
            ->orderBy('collection_deadline', 'asc')
            ->take(5)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'deadline' => $item->collection_deadline ? $item->collection_deadline->format('M d, Y') : null,
                    'days_remaining' => $item->collection_deadline ? now()->diffInDays($item->collection_deadline, false) : null,
                ];
            });

        $claimConflictStats = $this->buildClaimConflictStats();
        $reminderMetrics = $this->buildReminderEffectivenessMetrics();

        return response()->json([
            'stats' => [
                'totalUsers' => $totalUsers,
                'usersGrowthPercent' => $usersGrowthPercent,
                'claimedItems' => $claimedItems,
                'claimedGrowthPercent' => $claimedGrowthPercent,
                'unclaimedItems' => $unclaimedItems,
                'oldestUnclaimedDays' => $oldestUnclaimedDays,
                'foundItems' => $foundItems,
                'foundGrowthPercent' => $foundGrowthPercent,
                'lostItems' => $lostItems,
                'lostGrowthPercent' => $lostGrowthPercent,
                'pendingClaims' => $pendingClaims,
                'urgentClaims' => $urgentClaims,
                'successfulMatches' => $successfulMatches,
                'matchSuccessRate' => $matchSuccessRate,
                'collectedThisMonth' => $collectedThisMonth,
                'collectedGrowthPercent' => $collectedGrowthPercent,
                'matchSuccessRatePercent' => $matchSuccessRatePercent,
                'matchSuccessGrowthPercent' => $matchSuccessGrowthPercent,
				'collectionPending' => $collectionPending,
				'collectionOverdue' => $collectionOverdue,
				'collectionAverageLabel' => $collectionAverageLabel,
                'decisionAverageLabel' => $decisionAverageLabel,
                'decisionBreakdown' => $decisionBreakdown,
            ],
            'recentActivities' => $recentActivities,
            'pendingClaimsList' => $pendingClaimsList,
            'itemsNearDeadline' => $itemsNearDeadline,
            'claimConflictStats' => $claimConflictStats,
            'reminderMetrics' => $reminderMetrics,
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Export activity log as CSV
     */
    private function exportActivityLogCsv()
    {
        $filename = 'activity_log_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, ['Activity Log Report']);
            fputcsv($file, ['Generated', now()->format('Y-m-d H:i:s')]);
            fputcsv($file, []); // Empty row
            
            // Column headers
            fputcsv($file, ['Date & Time', 'User', 'Action', 'Details', 'IP Address']);
            
            // Get all activities (or filter by date range if needed)
            $activities = ActivityLog::with('user')
                ->latest('created_at')
                ->get();
            
            foreach ($activities as $activity) {
                fputcsv($file, [
                    $activity->created_at->format('Y-m-d H:i:s'),
                    $activity->user ? $activity->user->name : 'System',
                    $activity->action,
                    $activity->details ?? '',
                    $activity->ip_address ?? 'N/A',
                ]);
            }
            
            fputcsv($file, []); // Empty row
            fputcsv($file, ['Total Records', $activities->count()]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
