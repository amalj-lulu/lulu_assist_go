@extends('layouts.coreui.app')

@section('title', 'Plant Management')

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Plant Management</strong>
            <a href="#" class="btn btn-primary btn-sm ajax-modal-btn" data-url="{{ route('plants.create') }}">
                <i class="fas fa-plus-circle me-1"></i> Create New Plant
            </a>
        </div>

        <div class="card-body">
            @if ($plants->isEmpty())
                <p class="text-muted">No plants found.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped datatable">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th class="text-center" style="width: 180px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($plants as $plant)
                                <tr>
                                    <td>{{ $plant->name }}</td>
                                    <td>{{ $plant->code }}</td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="#" data-url="{{ route('plants.show', $plant) }}"
                                               class="btn btn-sm btn-info text-white ajax-modal-btn" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="#" data-url="{{ route('plants.edit', $plant) }}"
                                               class="btn btn-sm btn-warning text-white ajax-modal-btn" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('plants.destroy', $plant) }}" method="POST"
                                                  style="display:inline;" onsubmit="return confirm('Are you sure?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
