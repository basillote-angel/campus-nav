@extends('layouts.app')

@section('content')
<div class="bg-gradient-to-br from-blue-50 via-white to-indigo-50 min-h-full">
    <div class="bg-white shadow-sm border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-[#123A7D]">Dashboard</h1>
                    <p class="mt-1 text-sm text-gray-600">Welcome! Here's what's happening with your Lost & Found + Campus Navigation system.</p>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="bg-[rgba(59,130,246,0.08)] text-[rgba(59,130,246,0.8)] px-3 py-1 rounded-full text-sm font-medium flex items-center">
                        <svg class="w-4 h-4 text-[rgba(59,130,246,0.8)] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                            </path>
                        </svg>
                        {{ Auth::user()->name }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            {{-- Registered Users --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Registered Users</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalUsers }}</p>
                        <p class="text-xs text-green-600 mt-1">+12% from last month</p>
                    </div>
                    <div class="bg-[rgba(59,130,246,0.08)] p-3 rounded-lg">
                        <svg class="w-8 h-8 text-[rgba(59,130,246,0.8)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5.121 17.804A9.937 9.937 0 0112 15c2.082 0 4.005.635 5.585 1.712M15 11a3 3 0 11-6 0 3 3 0 016 0z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Claimed Items --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Claimed Items</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $claimedItems }}</p>
                        <p class="text-xs text-green-600 mt-1">+8% from last week</p>
                    </div>
                    <div class="bg-[rgba(59,130,246,0.08)] p-3 rounded-lg">
                        <svg class="w-8 h-8 text-[rgba(59,130,246,0.8)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Match Rate --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Match Rate</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $matchedItems }}</p>
                        <p class="text-xs text-blue-600 mt-1">+15% success rate</p>
                    </div>
                    <div class="bg-[rgba(59,130,246,0.08)] p-3 rounded-lg">
                        <svg class="w-8 h-8 text-[rgba(59,130,246,0.8)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                d="M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083 12.083 0 01.665 5.752A12.042 12.042 0 0112 20.944 12.042 12.042 0 015.175 16.33a12.082 12.082 0 01.665-5.752L12 14z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Unclaimed Items --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Unclaimed Items</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $unclaimedItems }}</p>
                        <p class="text-xs text-amber-600 mt-1">Needs attention</p>
                    </div>
                    <div class="bg-[rgba(59,130,246,0.08)] p-3 rounded-lg">
                        <svg class="w-8 h-8 text-[rgba(59,130,246,0.8)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h11M9 21V3"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Found Items --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Found Items</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $foundItems }}</p>
                        <p class="text-xs text-blue-600 mt-1">+5% this month</p>
                    </div>
                    <div class="bg-[rgba(59,130,246,0.08)] p-3 rounded-lg">
                        <svg class="w-8 h-8 text-[rgba(59,130,246,0.8)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Lost Items --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Lost Items</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $lostItems }}</p>
                        <p class="text-xs text-red-600 mt-1">-3% from last week</p>
                    </div>
                    <div class="bg-[rgba(59,130,246,0.08)] p-3 rounded-lg">
                        <svg class="w-8 h-8 text-[rgba(59,130,246,0.8)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-[#123A7D]">Posts Over Time</h2>
                    <span class="text-sm text-gray-500 font-medium">Last 7 Days</span>
                </div>
                <div class="relative" style="height: 350px;">
                    <canvas id="postsOverTimeChart"></canvas>
                </div>
            </div>

            <div class="flex flex-col gap-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-xl font-bold text-[#123A7D] mb-4">Item Status</h2>
                    <div class="relative" style="height: 200px; max-height: 200px;">
                        <canvas id="itemStatusChart"></canvas>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-xl font-bold text-[#123A7D] mb-4">AI Match Success</h2>
                    <div class="text-center my-4">
                        <div class="text-5xl font-bold text-green-600">
                            {{-- Example: ( $claimedItems / $foundItems ) * 100 --}}
                            78% 
                        </div>
                        <p class="text-sm text-gray-600 mt-2">Claimed / Found Items</p>
                        <p class="text-xs text-green-500 mt-1">+5% from last week</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-xl font-bold text-[#123A7D] mb-4">Top Lost Categories</h2>
                <div class="relative" style="height: 400px;">
                    <canvas id="topCategoriesChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-xl font-bold text-[#123A7D] mb-4">Pending Posts</h2>
                <div class="space-y-4 overflow-y-auto" style="max-height: 360px;">
                    {{-- This should be a @foreach loop in Laravel --}}
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-semibold text-sm text-gray-800">Black Wallet</p>
                            <p class="text-xs text-gray-500">Reported by: J. Dela Cruz (Lost)</p>
                        </div>
                        <div class="flex space-x-2">
                            <button class="text-xs bg-red-100 text-red-700 px-3 py-1 rounded-full hover:bg-red-200">Deny</button>
                            <button class="text-xs bg-green-100 text-green-700 px-3 py-1 rounded-full hover:bg-green-200">Approve</button>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-semibold text-sm text-gray-800">JBL Earphones</p>
                            <p class="text-xs text-gray-500">Posted by: Admin (Found)</p>
                        </div>
                        <div class="flex space-x-2">
                            <button class="text-xs bg-red-100 text-red-700 px-3 py-1 rounded-full hover:bg-red-200">Deny</button>
                            <button class="text-xs bg-green-100 text-green-700 px-3 py-1 rounded-full hover:bg-green-200">Approve</button>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-semibold text-sm text-gray-800">Blue Umbrella</p>
                            <p class="text-xs text-gray-500">Reported by: M. Santos (Lost)</p>
                        </div>
                        <div class="flex space-x-2">
                            <button class="text-xs bg-red-100 text-red-700 px-3 py-1 rounded-full hover:bg-red-200">Deny</button>
                            <button class="text-xs bg-green-100 text-green-700 px-3 py-1 rounded-full hover:bg-green-200">Approve</button>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-semibold text-sm text-gray-800">School ID</p>
                            <p class="text-xs text-gray-500">Reported by: C. Reyes (Lost)</p>
                        </div>
                        <div class="flex space-x-2">
                            <button class="text-xs bg-red-100 text-red-700 px-3 py-1 rounded-full hover:bg-red-200">Deny</button>
                            <button class="text-xs bg-green-100 text-green-700 px-3 py-1 rounded-full hover:bg-green-200">Approve</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-xl font-bold text-[#123A7D] mb-4">Recent Activity</h2>
                <ul class="space-y-5 overflow-y-auto" style="max-height: 360px;">
                    {{-- This should be a @foreach loop in Laravel --}}
                    <li class="flex items-center space-x-3">
                        <div class="p-2 bg-blue-100 rounded-full flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16l2.879-2.879a3 3 0 014.242 0L18 16m-6-8a3 3 0 100-6 3 3 0 000 6z"></path></svg>
                        </div>
                        <p class="text-sm text-gray-600"><b>User "m_santos"</b> reported a lost <b>"iPhone 12"</b>.</p>
                    </li>
                    <li class="flex items-center space-x-3">
                        <div class="p-2 bg-green-100 rounded-full flex-shrink-0">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <p class="text-sm text-gray-600"><b>Admin "Kalai S."</b> approved <b>"Black Wallet"</b> post.</p>
                    </li>
                    <li class="flex items-center space-x-3">
                        <div class="p-2 bg-purple-100 rounded-full flex-shrink-0">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m12.728 0l-.707.707M12 21v-1m-6.657-3.343l.707-.707m12.728 0l.707.707"></path></svg>
                        </div>
                        <p class="text-sm text-gray-600"><b>AI Match:</b> "JBL Earphones" (Found) matched with "Lost Earphones" (Lost).</p>
                    </li>
                    <li class="flex items-center space-x-3">
                        <div class="p-2 bg-yellow-100 rounded-full flex-shrink-0">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <p class="text-sm text-gray-600"><b>New User "c_reyes"</b> just registered.</p>
                    </li>
                </ul>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-gradient-to-r from-[rgba(59,130,246,0.8)] to-[rgba(59,130,246,1)] rounded-xl p-6 text-white cursor-pointer hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold">Add New Item</h3>
                        <p class="text-blue-100 text-sm mt-1">Report a lost or found item</p>
                    </div>
                    <svg class="w-8 h-8 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
            </div>

            <div class="bg-gradient-to-r from-[rgba(59,130,246,0.8)] to-[rgba(59,130,246,1)] rounded-xl p-6 text-white cursor-pointer hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold">View Reports</h3>
                        <p class="text-blue-100 text-sm mt-1">Check recent activity</p>
                    </div>
                    <svg class="w-8 h-8 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                        </path>
                    </svg>
                </div>
            </div>

            <div class="bg-gradient-to-r from-[rgba(59,130,246,0.8)] to-[rgba(59,130,246,1)] rounded-xl p-6 text-white cursor-pointer hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold">Manage Users</h3>
                        <p class="text-blue-100 text-sm mt-1">User administration</p>
                    </div>
                    <svg class="w-8 h-8 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>
        </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Helper function for chart tooltips
    const getChartTooltip = () => ({
        backgroundColor: 'rgba(0,0,0,0.8)',
        titleColor: 'white',
        bodyColor: 'white',
        borderColor: 'rgba(255,255,255,0.1)',
        borderWidth: 1,
        cornerRadius: 8,
        padding: 10,
    });

    // Helper function for X-axis
    const getChartTicksX = () => ({
        ticks: { color: '#6B7280', font: { size: 12, weight: '500' }, padding: 10 },
        grid: { display: false },
    });

    // Helper function for Y-axis
    const getChartTicksY = () => ({
        beginAtZero: true,
        ticks: { color: '#6B7280', font: { size: 12, weight: '500' }, padding: 10 },
        grid: { color: '#F3F4F6', drawBorder: false }
    });


    // 1. Posts Over Time (Line Chart)
    const postsCtx = document.getElementById('postsOverTimeChart').getContext('2d');
    const gradient = postsCtx.createLinearGradient(0, 0, 0, 350);
    gradient.addColorStop(0, 'rgba(59, 130, 246, 0.3)');
    gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');

    new Chart(postsCtx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [
                {
                    label: 'Lost Items',
                    data: [5, 7, 10, 8, 12, 11, 15], // Replace with $lost_items_data
                    borderColor: 'rgba(239, 68, 68, 1)', // red-600
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(239, 68, 68, 1)',
                    pointRadius: 4,
                    tension: 0.4
                },
                {
                    label: 'Found Items',
                    data: [3, 5, 8, 6, 9, 10, 12], // Replace with $found_items_data
                    borderColor: 'rgba(59, 130, 246, 1)', // blue-500
                    backgroundColor: gradient, // Use the gradient
                    fill: true, // Fill the area under the line
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(59, 130, 246, 1)',
                    pointRadius: 4,
                    tension: 0.4
                }
        ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', align: 'start', labels: { usePointStyle: true, padding: 20 } },
                tooltip: getChartTooltip()
            },
            scales: {
                x: getChartTicksX(),
                y: getChartTicksY()
            }
        }
    });

    // 2. Item Status (Donut Chart)
    const statusCtx = document.getElementById('itemStatusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Active Lost', 'Active Found', 'Claimed'],
            datasets: [{
                data: [30, 25, 45], // Replace with [$active_lost_count, $active_found_count, $claimed_count]
                backgroundColor: [
                    'rgba(239, 68, 68, 0.8)', // Red
                    'rgba(59, 130, 246, 0.8)', // Blue
                    'rgba(22, 163, 74, 0.8)'  // Green
                ],
                borderColor: '#FFFFFF',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 15 } },
                tooltip: getChartTooltip()
            }
        }
    });

    // 3. Top Lost Categories (Horizontal Bar Chart)
    const categoriesCtx = document.getElementById('topCategoriesChart').getContext('2d');
    new Chart(categoriesCtx, {
        type: 'bar',
        data: {
            labels: ['Wallets', 'Phones', 'ID Cards', 'Books', 'Earphones', 'Umbrellas'], // Replace with $category_labels
            datasets: [{
                label: 'Number of Items',
                data: [18, 15, 12, 10, 7, 5], // Replace with $category_data
                backgroundColor: 'rgba(18, 58, 125, 0.8)',
                borderColor: 'rgba(18, 58, 125, 1)',
                borderWidth: 1,
                borderRadius: 8,
                barThickness: 15,
            }]
        },
        options: {
            indexAxis: 'y', // This makes it a horizontal bar chart
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: getChartTooltip()
            },
            scales: {
                x: getChartTicksY(), // Y-axis config is used for X-axis in horizontal chart
                y: {
                    ticks: { color: '#6B7280', font: { size: 12, weight: '500' }, padding: 10 },
                    grid: { display: false }
                }
            }
        }
    });

</script>
@endsection