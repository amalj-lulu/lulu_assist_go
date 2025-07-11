<div data-modal-title=" User Details"></div>


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

            @if ($user->profile_picture)
                <tr>
                    <th>Profile Picture</th>
                    <td colspan="2">
                        <img src="{{ asset('storage/' . $user->profile_picture) }}" width="100" class="rounded">
                    </td>
                </tr>
            @endif

            <tr>
                <th>Assigned Plants</th>
                <td>
                    @if ($user->plants->isEmpty())
                        <p>No plants assigned.</p>
                    @else
                        <ul class="mb-0">
                            @foreach ($user->plants as $plant)
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
