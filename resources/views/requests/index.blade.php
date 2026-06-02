@extends('layouts.app')

@section('title', 'Requests from ResQOperation')

@section('content')
    <h1 class="page-title">Requests from ResQOperation</h1>
    <div class="page-subtitle">Review and manage external inventory requests</div>

    <section class="panel">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Source</th>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $request)
                        <tr>
                            <td>{{ $request->request_code }}</td>
                            <td>{{ $request->source }}</td>
                            <td>{{ $request->inventory?->sku ?? 'Missing item' }}</td>
                            <td>{{ number_format($request->quantity) }}</td>
                            <td>
                                <span class="badge-pill priority-{{ strtolower($request->priority) }}">
                                    {{ $request->priority }}
                                </span>
                            </td>
                            <td>
                                <span class="badge-pill status-{{ strtolower($request->status) }}">
                                    {{ $request->status }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-link p-0 text-secondary" title="{{ $request->notification_status ?? 'No notification yet' }}">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>

                                    @if($request->status === 'Pending')
                                        <form action="{{ route('requests.approve', $request) }}" method="POST">
                                            @csrf
                                            <button class="btn btn-success rounded-pill fw-bold px-3">Approve</button>
                                        </form>

                                        <form action="{{ route('requests.reject', $request) }}" method="POST">
                                            @csrf
                                            <button class="btn btn-danger rounded-pill fw-bold px-3">Reject</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                No ResQOperation requests found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
