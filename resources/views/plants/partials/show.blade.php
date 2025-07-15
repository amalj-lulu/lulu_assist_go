<div data-modal-title="Plant Details"></div>

{{-- Close button (optional for modal layout) --}}
<div class="d-flex justify-content-end pt-3 pe-3 mb-2">
    <button type="button" class="btn btn-sm btn-outline-primary shadow-sm px-3" data-bs-dismiss="modal" aria-label="Close">
        &times; Close
    </button>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><strong>Plant Information</strong></h5>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle mb-0 border">
                <tbody>
                    <tr>
                        <th scope="row" class="text-muted">Name</th>
                        <td>{{ $plant->name }}</td>
                    </tr>
                    <tr>
                        <th scope="row" class="text-muted">Code</th>
                        <td>{{ $plant->code }}</td>
                    </tr>
                    <tr>
                        <th scope="row" class="text-muted">Is Warehouse</th>
                        <td>
                            @if ($plant->is_warehouse)
                                <span class="badge bg-success">Yes</span>
                            @else
                                <span class="badge bg-secondary">No</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
