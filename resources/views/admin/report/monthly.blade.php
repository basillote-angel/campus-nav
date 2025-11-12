<h2>Lost & Found Monthly Report - {{ $month }}</h2>
<p>Total Lost Items: {{ $lostItems->count() }}</p>
<p>Total Found Items: {{ $foundItems->count() }}</p>

<table width="100%" border="1" cellspacing="0" cellpadding="5">
    <thead>
        <tr>
            <th>Item Name</th>
            <th>Status</th>
            <th>Reported By</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        @foreach($lostItems as $item)
            <tr>
                <td>{{ $item->name }}</td>
                <td>Lost</td>
                <td>{{ $item->user->name ?? 'N/A' }}</td>
                <td>{{ $item->created_at->format('M d, Y') }}</td>
            </tr>
        @endforeach
        @foreach($foundItems as $item)
            <tr>
                <td>{{ $item->name }}</td>
                <td>Found</td>
                <td>{{ $item->user->name ?? 'N/A' }}</td>
                <td>{{ $item->created_at->format('M d, Y') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
