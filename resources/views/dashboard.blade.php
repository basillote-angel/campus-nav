@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-bold mb-6">Dashboard</h1>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    {{-- Registered Users --}}
    <x-dashboard-card label="Registered Users" value="{{ $totalUsers }}" iconPath="M5.121 17.804A9.937 9.937 0 0112 15c2.082 0 4.005.635 5.585 1.712M15 11a3 3 0 11-6 0 3 3 0 016 0z" />

    {{-- Claimed Items --}}
    <x-dashboard-card label="Claimed Items" value="{{ $claimedItems }}" iconPath="M5 13l4 4L19 7" />

    {{-- Match Items --}}
    <x-dashboard-card label="Match Items" value="{{ $matchedItems }}" iconPath="M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083 12.083 0 01.665 5.752A12.042 12.042 0 0112 20.944 12.042 12.042 0 015.175 16.33a12.082 12.082 0 01.665-5.752L12 14z" />

    {{-- Unclaimed Items --}}
    <x-dashboard-card label="Unclaimed Items" value="{{ $unclaimedItems }}" iconPath="M3 10h11M9 21V3" />

    {{-- Found Items --}}
    <x-dashboard-card label="Found Items" value="{{ $foundItems }}" iconPath="M12 4v16m8-8H4" />

    {{-- Lost Items --}}
    <x-dashboard-card label="Lost Items" value="{{ $lostItems }}" iconPath="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
</div>
<div class="mt-12 max-w-4xl mx-auto bg-white p-6 rounded-lg shadow border border-gray-200">
    <h2 class="text-xl font-bold text-gray-700 mb-4">Item Status Overview</h2>
    <canvas id="itemBarChart" height="100"></canvas>
</div>

 <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('itemBarChart').getContext('2d');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Lost Items', 'Found Items', 'Claimed Items', 'Unclaimed Items'],
            datasets: [{
                label: 'Number of Items',
                data: [
                    {{ $lostItems }},
                    {{ $foundItems }},
                    {{ $claimedItems }},
                    {{ $unclaimedItems }}
                ],
                backgroundColor: [
                    'rgba(239, 68, 68, 0.7)',   // red-500
                    'rgba(59, 130, 246, 0.7)',  // blue-500
                    'rgba(16, 185, 129, 0.7)',  // emerald-500
                    'rgba(245, 158, 11, 0.7)'   // amber-500
                ],
                borderColor: [
                    'rgba(239, 68, 68, 1)',
                    'rgba(59, 130, 246, 1)',
                    'rgba(16, 185, 129, 1)',
                    'rgba(245, 158, 11, 1)'
                ],
                borderWidth: 2,
                borderRadius: 8,
                barPercentage: 0.6
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    labels: {
                        color: '#374151',
                        font: {
                            size: 14
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#4B5563',
                        stepSize: 1
                    },
                    grid: {
                        color: '#E5E7EB'
                    }
                },
                x: {
                    ticks: {
                        color: '#4B5563'
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
</script>

@endsection
