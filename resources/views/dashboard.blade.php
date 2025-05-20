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
@endsection
