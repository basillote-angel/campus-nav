@extends('layouts.app')

@section('content')
<div class="container py-3">
  <h1 class="mb-3">Claims</h1>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @elseif(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  <ul class="nav nav-tabs mb-3">
    <li class="nav-item">
      <a class="nav-link {{ $tab==='pending'?'active':'' }}" href="?tab=pending">Pending</a>
    </li>
    <li class="nav-item">
      <a class="nav-link {{ $tab==='approved'?'active':'' }}" href="?tab=approved">Approved</a>
    </li>
    <li class="nav-item">
      <a class="nav-link {{ $tab==='rejected'?'active':'' }}" href="?tab=rejected">Rejected</a>
    </li>
  </ul>

  @php
    $list = $tab==='approved' ? $approved : ($tab==='rejected' ? $rejected : $pending);
  @endphp

  @if($list->isEmpty())
    <div class="alert alert-info">No records.</div>
  @else
  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th>Item</th>
          <th>Claimant</th>
          <th>Message</th>
          <th>Dates</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($list as $item)
          <tr>
            <td>
              <div><strong>{{ $item->name }}</strong> <span class="badge bg-secondary">{{ ucfirst($item->type) }}</span></div>
              <div class="small text-muted">{{ ucfirst($item->category) }} • {{ $item->location }}</div>
            </td>
            <td>
              @if($item->claimedBy)
                <div>{{ $item->claimedBy->name }}</div>
              @else
                <div class="text-muted small">Unknown</div>
              @endif
            </td>
            <td class="small">{{ $item->claim_message }}</td>
            <td class="small text-muted">
              <div>Claimed: {{ optional($item->claimed_at)->diffForHumans() }}</div>
              @if($tab==='approved')
                <div>Approved: {{ optional($item->approved_at)->diffForHumans() }}</div>
              @elseif($tab==='rejected')
                <div>Rejected: {{ optional($item->rejected_at)->diffForHumans() }}</div>
                <div>Reason: {{ $item->rejection_reason }}</div>
              @endif
            </td>
            <td class="text-nowrap">
              @if($tab==='pending')
              <form method="post" action="{{ route('admin.claims.approve', $item->id) }}" class="d-inline">
                @csrf
                <button class="btn btn-sm btn-success" type="submit">Approve</button>
              </form>
              <button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $item->id }}">Reject</button>

              <!-- Reject Modal -->
              <div class="modal fade" id="rejectModal{{ $item->id }}" tabindex="-1">
                <div class="modal-dialog">
                  <form method="post" action="{{ route('admin.claims.reject', $item->id) }}" class="modal-content">
                    @csrf
                    <div class="modal-header"><h5 class="modal-title">Reject Claim</h5></div>
                    <div class="modal-body">
                      <div class="mb-2">
                        <label class="form-label">Reason</label>
                        <textarea name="reason" class="form-control" rows="3" required></textarea>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancel</button>
                      <button class="btn btn-danger" type="submit">Reject</button>
                    </div>
                  </form>
                </div>
              </div>
              @else
                <a href="{{ url('/items/'.$item->id.'/edit') }}" class="btn btn-sm btn-outline-secondary">View</a>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>
@endsection


