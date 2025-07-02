@extends('layouts.admin')

@section('title', 'User Details')
@section('page-title', 'User Details: ' . $user->name)

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">User Details</h3>
        <a href="{{ route('plant-user.index') }}" class="btn btn-sm btn-secondary float-right">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
    </div>

    <div class="card-body">
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <th>Name</th>
                    <td>{{ $user->name }}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $user->email }}</td>
                </tr>
                <tr>
                    <th>Mobile</th>
                    <td>{{ $user->mobile ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Assigned Plants</th>
                    <td>
                        @if($user->plants->isEmpty())
                            <p>No plants assigned.</p>
                        @else
                            <ul class="mb-0">
                                @foreach($user->plants as $plant)
                                    <li>{{ $plant->name }} ({{ $plant->code ?? '' }})</li>
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
