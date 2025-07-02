@extends('layouts.admin')

@section('title', 'Plant Details')
@section('page-title', 'Plant Details: ' . $plant->name)

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Plant Details</h3>
            <a href="{{ route('plants.index') }}" class="btn btn-sm btn-secondary float-right">
                <i class="fas fa-arrow-left"></i> Back to Plants
            </a>
        </div>

        <div class="card-body">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th>Name</th>
                        <td>{{ $plant->name }}</td>
                    </tr>
                    <tr>
                        <th>Code</th>
                        <td>{{ $plant->code }}</td>
                    </tr>
                    <tr>
                        <th>Location</th>
                        <td>{{ $plant->location }}</td>
                    </tr>
                    <tr>
                        <th>Assigned Users</th>
                        <td>
                            @if ($plant->users->isEmpty())
                                <p>No users assigned.</p>
                            @else
                                <ul class="mb-0">
                                    @foreach ($plant->users as $user)
                                        <li>{{ $user->name }} ({{ $user->email }})</li>
                                    @endforeach
                                </ul>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
