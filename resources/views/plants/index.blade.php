@extends('layouts.admin')

@section('title', 'Plant Management')
@section('page-title', 'Plant Management')

@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Plants</h3>
            <a href="#" data-url="{{ route('plants.create') }}" class="btn btn-primary btn-sm ml-auto ajax-modal-btn">
                <i class="fas fa-plus mr-1"></i> Create New Plant
            </a>

        </div>

        <div class="card-body">
            @if ($plants->isEmpty())
                <p>No plants found.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Location</th>
                                <th style="width: 180px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($plants as $plant)
                                <tr>
                                    <td>{{ $plant->name }}</td>
                                    <td>{{ $plant->code }}</td>
                                    <td>{{ $plant->location }}</td>
                                    <td>
                                        <a  href="#"  data-url="{{ route('plants.show', $plant) }}" class="btn btn-sm btn-info  ajax-modal-btn">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="#"  data-url="{{ route('plants.edit', $plant) }}" class="btn btn-sm btn-warning ajax-modal-btn">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('plants.destroy', $plant) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button onclick="return confirm('Are you sure?')" type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Pagination --}}
        <div class="card-footer clearfix">
            {{ $plants->links('pagination::bootstrap-4') }}
        </div>
    </div>

@endsection
