<div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <!-- Flash Messages -->
                    @if (session()->has('message'))
                        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center"
                            role="alert">
                            {{ session('message') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center"
                            role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <!-- Header Controls -->
                    <div class="row mb-3">
                        <!-- Show Entries Dropdown -->
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <button type="button" class="btn btn-pill btn-primary mr-3" wire:click="openAddModal">
                                    <i class="fas fa-plus mr-2"></i>Add Sparepart
                                </button>
                                <span class="mr-2">Show</span>
                                <select wire:model.live="perPage" class="form-control"
                                    style="width: auto; display: inline-block;">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                <span class="ml-2">entries</span>
                            </div>
                        </div>
                        <!-- Search Box -->
                        <div class="col-md-6">
                            <div class="d-flex justify-content-end">
                                <div class="form-group mb-0" style="width: 250px;">
                                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control"
                                        placeholder="Search barcode, code, name, uom...">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-borderless">
                            <thead class="thead-light">
                                <tr>
                                    <th width="50">No</th>
                                    <th>Barcode</th>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Stock</th>
                                    <th>UOM</th>
                                    <th width="120" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($spareparts as $index => $sparepart)
                                    <tr>
                                        <td>{{ $spareparts->firstItem() + $index }}</td>
                                        <td>
                                            <span class="badge badge-info">{{ $sparepart->barcode }}</span>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold">{{ $sparepart->code }}</span>
                                        </td>
                                        <td>{{ $sparepart->name }}</td>
                                        <td>
                                            @if ($sparepart->stock <= 10)
                                                <span class="badge badge-danger">{{ $sparepart->stock }}</span>
                                            @elseif ($sparepart->stock <= 50)
                                                <span class="badge badge-warning">{{ $sparepart->stock }}</span>
                                            @else
                                                <span class="badge badge-success">{{ $sparepart->stock }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $sparepart->uom }}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-link btn-lg btn-warning p-1"
                                                title="Edit Sparepart"
                                                wire:click="openEditModal({{ $sparepart->id }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-link btn-lg btn-danger p-1"
                                                title="Delete Sparepart"
                                                wire:click="confirmDelete({{ $sparepart->id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <div class="py-4">
                                                <i class="fas fa-box-open fa-2x text-muted mb-3"></i>
                                                <p class="text-muted">No spareparts found</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if ($spareparts->hasPages())
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <span class="text-muted">
                                        Showing {{ $spareparts->firstItem() }} to {{ $spareparts->lastItem() }} of
                                        {{ $spareparts->total() }} entries
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-end">
                                    {{ $spareparts->links() }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Sparepart Modal -->
    <div wire:ignore.self class="modal fade" id="sparepartModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header {{ $modalMode === 'edit' ? 'bg-warning' : 'bg-primary' }}">
                    <h5 class="modal-title text-white">
                        <i class="fas {{ $modalMode === 'edit' ? 'fa-edit' : 'fa-plus-circle' }} mr-2"></i>
                        {{ $modalMode === 'edit' ? 'Edit Sparepart' : 'Add New Sparepart' }}
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" wire:click="closeModal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="confirmSave">
                        <div class="form-group">
                            <label for="barcode">Barcode <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('barcode') is-invalid @enderror"
                                id="barcode" wire:model="barcode" placeholder="Enter barcode">
                            @error('barcode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="code">Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror"
                                id="code" wire:model="code" placeholder="Enter code">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                id="name" wire:model="name" placeholder="Enter name">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="stock">Stock <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('stock') is-invalid @enderror"
                                        id="stock" wire:model="stock" placeholder="Enter stock" min="0"
                                        step="0.01">
                                    @error('stock')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="uom">UOM <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('uom') is-invalid @enderror"
                                        id="uom" wire:model="uom" placeholder="e.g. pcs, ltr, kg">
                                    @error('uom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-pill btn-secondary" wire:click="closeModal">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-pill btn-success" wire:click="confirmSave">
                        <i class="fas fa-save mr-2"></i>{{ $modalMode === 'edit' ? 'Update' : 'Save' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div wire:loading class="position-fixed"
        style="top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.1); z-index: 9999;">
        <div class="d-flex justify-content-center align-items-center h-100">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', function() {
            // Show modal
            Livewire.on('showSparepartModal', () => {
                setTimeout(() => {
                    $('#sparepartModal').modal('show');
                }, 100);
            });

            // Close modal
            Livewire.on('closeSparepartModal', () => {
                $('#sparepartModal').modal('hide');
            });

            // Confirm Save (Add/Edit)
            Livewire.on('confirmSaveSparepart', () => {
                const mode = @this.modalMode;
                const title = mode === 'edit' ? 'Update Sparepart?' : 'Save New Sparepart?';
                const text = mode === 'edit' ?
                    'This will update the sparepart information.' :
                    'This will add a new sparepart to the database.';

                swal({
                    title: title,
                    text: text,
                    icon: "info",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill",
                        },
                        confirm: {
                            text: mode === 'edit' ? "Yes, Update" : "Yes, Save",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-success",
                        }
                    },
                }).then((result) => {
                    if (result) {
                        @this.saveSparepart();
                    }
                });
            });

            // Confirm Delete
            Livewire.on('confirmDeleteSparepart', () => {
                swal({
                    title: "Delete Sparepart?",
                    text: "This action cannot be undone. Are you sure you want to delete this sparepart?",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill",
                        },
                        confirm: {
                            text: "Yes, Delete",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-danger",
                        }
                    },
                    dangerMode: true,
                }).then((result) => {
                    if (result) {
                        @this.deleteSparepart();
                    }
                });
            });
        });

        document.addEventListener('livewire:navigated', function() {
            // Duplicate event listeners for SPA navigation
            Livewire.on('showSparepartModal', () => {
                setTimeout(() => {
                    $('#sparepartModal').modal('show');
                }, 100);
            });

            Livewire.on('closeSparepartModal', () => {
                $('#sparepartModal').modal('hide');
            });

            Livewire.on('confirmSaveSparepart', () => {
                const mode = @this.modalMode;
                const title = mode === 'edit' ? 'Update Sparepart?' : 'Save New Sparepart?';
                const text = mode === 'edit' ?
                    'This will update the sparepart information.' :
                    'This will add a new sparepart to the database.';

                swal({
                    title: title,
                    text: text,
                    icon: "info",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill",
                        },
                        confirm: {
                            text: mode === 'edit' ? "Yes, Update" : "Yes, Save",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-success",
                        }
                    },
                }).then((result) => {
                    if (result) {
                        @this.saveSparepart();
                    }
                });
            });

            Livewire.on('confirmDeleteSparepart', () => {
                swal({
                    title: "Delete Sparepart?",
                    text: "This action cannot be undone. Are you sure you want to delete this sparepart?",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill",
                        },
                        confirm: {
                            text: "Yes, Delete",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-danger",
                        }
                    },
                    dangerMode: true,
                }).then((result) => {
                    if (result) {
                        @this.deleteSparepart();
                    }
                });
            });
        });
    </script>
@endpush
