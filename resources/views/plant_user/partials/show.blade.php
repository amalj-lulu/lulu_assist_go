<div data-modal-title="User Details"></div>

<div class="card-body">
    <div class="table-responsive">
        <table class="table align-middle mb-0 border">
            <tbody>
                <tr>
                    <th scope="row" class="text-muted">Name</th>
                    <td>{{ $user->name }}</td>
                </tr>
                <tr>
                    <th scope="row" class="text-muted">Email</th>
                    <td>{{ $user->email }}</td>
                </tr>
                <tr>
                    <th scope="row" class="text-muted">Mobile</th>
                    <td>{{ $user->mobile ?? '-' }}</td>
                </tr>

                @if ($user->profile_picture)
                    <tr>
                        <th scope="row" class="text-muted">Profile Picture</th>
                        <td>
                            <img src="{{ asset('storage/' . $user->profile_picture) }}" width="100" class="rounded shadow-sm">
                        </td>
                    </tr>
                @endif

                <tr>
                    <th scope="row" class="text-muted">Assigned Plants</th>
                    <td>
                        @if ($user->plants->isEmpty())
                            <span class="text-muted">No plants assigned.</span>
                        @else
                            <ul class="list-unstyled mb-0">
                                @foreach ($user->plants as $plant)
                                    <li>
                                        <svg class="icon me-1">
                                            <use xlink:href="{{ asset('coreui/icons/svg/free.svg#cil-factory') }}"></use>
                                        </svg>
                                        {{ $plant->name }} <small class="text-muted">({{ $plant->code ?? '—' }})</small>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
