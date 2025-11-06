<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\LostItem;
use App\Models\FoundItem;
use App\Models\ActivityLog;
use App\Models\ItemMatch;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $ttl = now()->addSeconds((int) env('DASHBOARD_CACHE_TTL', 30));

        // Basic Statistics
        $totalUsers = Cache::remember('dash.totalUsers', $ttl, fn () => User::count());
        $totalUsersLastMonth = Cache::remember('dash.totalUsersLastMonth', $ttl, fn () => User::where('created_at', '>=', now()->subMonth())->count());
        $usersGrowthPercent = $totalUsers > 0 ? round((($totalUsers - $totalUsersLastMonth) / max($totalUsersLastMonth, 1)) * 100, 1) : 0;

        $claimedItems = Cache::remember('dash.claimedItems', $ttl, fn () => FoundItem::where('status', 'returned')->count());
        $claimedItemsLastWeek = Cache::remember('dash.claimedItemsLastWeek', $ttl, fn () => FoundItem::where('status', 'returned')->where('approved_at', '>=', now()->subWeek())->count());
        $claimedGrowthPercent = $claimedItems > 0 ? round((($claimedItems - $claimedItemsLastWeek) / max($claimedItemsLastWeek, 1)) * 100, 1) : 0;

        $unclaimedItems = Cache::remember('dash.unclaimedItems', $ttl, fn () => FoundItem::where('status', 'unclaimed')->count());
        $oldestUnclaimed = Cache::remember('dash.oldestUnclaimed', $ttl, fn () => FoundItem::where('status', 'unclaimed')->oldest('date_found')->first());
        $oldestUnclaimedDays = $oldestUnclaimed ? now()->diffInDays($oldestUnclaimed->date_found) : 0;

        $foundItems = Cache::remember('dash.foundItems', $ttl, fn () => FoundItem::count());
        $foundItemsLastMonth = Cache::remember('dash.foundItemsLastMonth', $ttl, fn () => FoundItem::where('created_at', '>=', now()->subMonth())->count());
        $foundGrowthPercent = $foundItems > 0 ? round((($foundItems - $foundItemsLastMonth) / max($foundItemsLastMonth, 1)) * 100, 1) : 0;

        $lostItems = Cache::remember('dash.lostItems', $ttl, fn () => LostItem::count());
        $lostItemsLastWeek = Cache::remember('dash.lostItemsLastWeek', $ttl, fn () => LostItem::where('created_at', '>=', now()->subWeek())->count());
        $lostGrowthPercent = $lostItems > 0 ? round((($lostItems - $lostItemsLastWeek) / max($lostItemsLastWeek, 1)) * 100, 1) : 0;

        // Pending Claims Statistics
        $pendingClaims = Cache::remember('dash.pendingClaims', $ttl, fn () => FoundItem::where('status', 'matched')->count());
        $urgentClaims = Cache::remember('dash.urgentClaims', $ttl, fn () => FoundItem::where('status', 'matched')
            ->whereNotNull('claimed_at')
            ->where('claimed_at', '<', now()->subHours(24))
            ->count());

        // Successful Matches
        $successfulMatches = Cache::remember('dash.successfulMatches', $ttl, fn () => ItemMatch::where('status', 'confirmed')->count());
        $totalMatches = Cache::remember('dash.totalMatches', $ttl, fn () => ItemMatch::count());
        $matchSuccessRate = $totalMatches > 0 ? round(($successfulMatches / $totalMatches) * 100, 1) : 0;

        // Items Collected This Month
        $collectedThisMonth = Cache::remember('dash.collectedThisMonth', $ttl, fn () => FoundItem::where('status', 'returned')
            ->whereNotNull('collected_at')
            ->where('collected_at', '>=', now()->startOfMonth())
            ->count());
        $collectedLastMonth = Cache::remember('dash.collectedLastMonth', $ttl, fn () => FoundItem::where('status', 'returned')
            ->whereNotNull('collected_at')
            ->whereBetween('collected_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->count());
        $collectedGrowthPercent = $collectedThisMonth > 0 ? round((($collectedThisMonth - $collectedLastMonth) / max($collectedLastMonth, 1)) * 100, 1) : 0;

        // AI Match Success Rate (Claimed / Found Items)
        $matchSuccessRatePercent = $foundItems > 0 ? round(($claimedItems / $foundItems) * 100, 1) : 0;
        $matchSuccessRateLastWeek = Cache::remember('dash.matchSuccessRateLastWeek', $ttl, function() use ($foundItems) {
            $claimedLastWeek = FoundItem::where('status', 'returned')->where('approved_at', '>=', now()->subWeek())->count();
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
        $activeLostItems = Cache::remember('dash.activeLostItems', $ttl, fn () => LostItem::where('status', 'open')->count());
        $activeFoundItems = Cache::remember('dash.activeFoundItems', $ttl, fn () => FoundItem::where('status', 'unclaimed')->count());
        $itemStatusData = [
            'activeLost' => $activeLostItems,
            'activeFound' => $activeFoundItems,
            'claimed' => $claimedItems,
            'returned' => $claimedItems, // Same as claimed for now
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
        $pendingClaimsList = Cache::remember('dash.pendingClaimsList', $ttl, fn () => FoundItem::where('status', 'matched')
            ->with(['claimedBy', 'category'])
            ->orderBy('claimed_at', 'asc')
            ->take(5)
            ->get());

        // Items Near Collection Deadline
        $itemsNearDeadline = Cache::remember('dash.itemsNearDeadline', $ttl, fn () => FoundItem::where('status', 'returned')
            ->whereNull('collected_at')
            ->whereNotNull('collection_deadline')
            ->where('collection_deadline', '>=', now())
            ->where('collection_deadline', '<=', now()->addDays(3))
            ->with(['claimedBy', 'category'])
            ->orderBy('collection_deadline', 'asc')
            ->take(5)
            ->get());

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
            'postsOverTimeData',
            'itemStatusData',
            'topCategories',
            'recentActivities',
            'pendingClaimsList',
            'itemsNearDeadline'
        ));
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
        ];
        
        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
        
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
            
            $lostItems = LostItem::count();
            $foundItems = FoundItem::count();
            $claimedItems = FoundItem::where('status', 'returned')->count();
            $pendingClaims = FoundItem::where('status', 'matched')->count();
            $unclaimedItems = FoundItem::where('status', 'unclaimed')->count();
            $collectedThisMonth = FoundItem::where('status', 'returned')
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
        $totalUsers = User::count();
        $totalUsersLastMonth = User::where('created_at', '>=', now()->subMonth())->count();
        $usersGrowthPercent = $totalUsers > 0 ? round((($totalUsers - $totalUsersLastMonth) / max($totalUsersLastMonth, 1)) * 100, 1) : 0;

        $claimedItems = FoundItem::where('status', 'returned')->count();
        $claimedItemsLastWeek = FoundItem::where('status', 'returned')->where('approved_at', '>=', now()->subWeek())->count();
        $claimedGrowthPercent = $claimedItems > 0 ? round((($claimedItems - $claimedItemsLastWeek) / max($claimedItemsLastWeek, 1)) * 100, 1) : 0;

        $unclaimedItems = FoundItem::where('status', 'unclaimed')->count();
        $oldestUnclaimed = FoundItem::where('status', 'unclaimed')->oldest('date_found')->first();
        $oldestUnclaimedDays = $oldestUnclaimed ? now()->diffInDays($oldestUnclaimed->date_found) : 0;

        $foundItems = FoundItem::count();
        $foundItemsLastMonth = FoundItem::where('created_at', '>=', now()->subMonth())->count();
        $foundGrowthPercent = $foundItems > 0 ? round((($foundItems - $foundItemsLastMonth) / max($foundItemsLastMonth, 1)) * 100, 1) : 0;

        $lostItems = LostItem::count();
        $lostItemsLastWeek = LostItem::where('created_at', '>=', now()->subWeek())->count();
        $lostGrowthPercent = $lostItems > 0 ? round((($lostItems - $lostItemsLastWeek) / max($lostItemsLastWeek, 1)) * 100, 1) : 0;

        $pendingClaims = FoundItem::where('status', 'matched')->count();
        $urgentClaims = FoundItem::where('status', 'matched')
            ->whereNotNull('claimed_at')
            ->where('claimed_at', '<', now()->subHours(24))
            ->count();

        $successfulMatches = ItemMatch::where('status', 'confirmed')->count();
        $totalMatches = ItemMatch::count();
        $matchSuccessRate = $totalMatches > 0 ? round(($successfulMatches / $totalMatches) * 100, 1) : 0;

        $collectedThisMonth = FoundItem::where('status', 'returned')
            ->whereNotNull('collected_at')
            ->where('collected_at', '>=', now()->startOfMonth())
            ->count();
        $collectedLastMonth = FoundItem::where('status', 'returned')
            ->whereNotNull('collected_at')
            ->whereBetween('collected_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->count();
        $collectedGrowthPercent = $collectedThisMonth > 0 ? round((($collectedThisMonth - $collectedLastMonth) / max($collectedLastMonth, 1)) * 100, 1) : 0;

        $matchSuccessRatePercent = $foundItems > 0 ? round(($claimedItems / $foundItems) * 100, 1) : 0;
        $claimedLastWeek = FoundItem::where('status', 'returned')->where('approved_at', '>=', now()->subWeek())->count();
        $foundLastWeek = FoundItem::where('created_at', '>=', now()->subWeek())->count();
        $matchSuccessRateLastWeek = $foundLastWeek > 0 ? round(($claimedLastWeek / $foundLastWeek) * 100, 1) : 0;
        $matchSuccessGrowthPercent = round($matchSuccessRatePercent - $matchSuccessRateLastWeek, 1);

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
        $pendingClaimsList = FoundItem::where('status', 'matched')
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
        $itemsNearDeadline = FoundItem::where('status', 'returned')
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
            ],
            'recentActivities' => $recentActivities,
            'pendingClaimsList' => $pendingClaimsList,
            'itemsNearDeadline' => $itemsNearDeadline,
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
