@extends('layouts.coreui.app')

@section('title', 'Plant User Management')

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-left">
            <strong>Plant User Management</strong>
            <a href="#" class="btn btn-primary ajax-modal-btn" data-url="{{ route('plant-user.create') }}">
                <i class="fas fa-plus-circle me-1"></i> Add Plant User
            </a>

        </div>

        <div class="card-body">
            @if ($users->isEmpty())
                <p class="text-muted">No users found.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover datatable">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>Plants</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->mobile ?? '-' }}</td>
                                    <td>
                                        @foreach ($user->plants as $plant)
                                            <span class="badge bg-info text-dark">{{ $plant->name }}</span>
                                        @endforeach
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="#" data-url="{{ route('plant-user.show', $user) }}"
                                                class="ajax-modal-btn btn btn-sm btn-info text-white" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            <a href="#" data-url="{{ route('plant-user.edit', $user) }}"
                                                class="ajax-modal-btn btn btn-sm btn-warning text-white" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <form action="{{ route('plant-user.destroy', $user) }}" method="POST"
                                                onsubmit="return confirm('Are you sure you want to delete this user?');"
                                                style="display: inline;">
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
