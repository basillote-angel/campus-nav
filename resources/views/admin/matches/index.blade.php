@extends('layouts.app')

@section('content')
<div class="container py-3">
  <h1 class="mb-3">AI Matches Queue</h1>

  <form method="get" class="row g-2 mb-3">
    <div class="col-auto">
      <label class="form-label">Days</label>
      <input type="number" min="1" name="days" class="form-control" value="{{ $days }}">
    </div>
    <div class="col-auto">
      <label class="form-label">Min Score</label>
      <input type="number" step="0.01" min="0" max="1" name="minScore" class="form-control" value="{{ $minScore }}">
    </div>
    <div class="col-auto align-self-end">
      <button class="btn btn-primary">Apply</button>
    </div>
  </form>

  @if(empty($suggestions))
    <div class="alert alert-info">No suggestions found for the current filters.</div>
  @else
  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th>Score</th>
          <th>Found Item</th>
          <th>Lost Candidate</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($suggestions as $row)
          <tr>
            <td><span class="badge bg-success">{{ number_format($row['score'] * 100, 0) }}%</span></td>
            <td>
              <div><strong>{{ $row['found']->name }}</strong></div>
              <div class="small text-muted">
                {{ ucfirst($row['found']->category) }} • {{ $row['found']->location }} • {{ optional($row['found']->created_at)->diffForHumans() }}
              </div>
            </td>
            <td>
              <div><strong>{{ $row['lost']->name }}</strong></div>
              <div class="small text-muted">
                {{ ucfirst($row['lost']->category) }} • {{ $row['lost']->location }} • {{ optional($row['lost']->created_at)->diffForHumans() }}
              </div>
            </td>
            <td class="text-nowrap">
              <a href="{{ url('/items/'.$row['found']->id.'/edit') }}" class="btn btn-sm btn-outline-secondary">View Found</a>
              <a href="{{ url('/items/'.$row['lost']->id.'/edit') }}" class="btn btn-sm btn-outline-secondary">View Lost</a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>
@endsection


