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
                                        placeholder="Search notification number, work description, status...">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-borderless">
                            <thead class="thead-light">
                                <tr>
                                    <th>Action</th>
                                    <th>Detail</th>
                                    <th>Status</th>
                                    <th>Notification Number</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($workOrders as $workOrder)
                                    <tr>
                                        <td>
                                            <button type="button" class="btn btn-link btn-lg btn-warning"
                                                title="Edit Details" wire:click="openEditModal({{ $workOrder->id }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-link btn-lg btn-info"
                                                title="View Details" wire:click="openDetailModal({{ $workOrder->id }})">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                        <td>
                                            @php
                                                $statusClass = match ($workOrder->status) {
                                                    'pending' => 'badge-warning',
                                                    'approved' => 'badge-success',
                                                    'rejected' => 'badge-danger',
                                                    'in_progress' => 'badge-info',
                                                    'completed' => 'badge-primary',
                                                    default => 'badge-secondary',
                                                };
                                            @endphp
                                            <span
                                                class="badge {{ $statusClass }}">{{ ucfirst($workOrder->status ?? 'Unknown') }}</span>
                                        </td>
                                        <td>
                                            <span
                                                class="font-weight-bold">{{ $workOrder->notification_number ?? '-' }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">
                                            <div class="py-4">
                                                <i class="fas fa-search fa-2x text-muted mb-3"></i>
                                                <p class="text-muted">No sparepart orders found</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if ($workOrders->hasPages())
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <span class="text-muted">
                                        Showing {{ $workOrders->firstItem() }} to {{ $workOrders->lastItem() }} of
                                        {{ $workOrders->total() }} entries
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-end">
                                    {{ $workOrders->links() }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Sparepart Detail Modal -->
    @if ($selectedWorkOrder)
        <div wire:ignore.self class="modal fade" id="detailModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <!-- Header berubah berdasarkan mode -->
                    <div class="modal-header {{ $modalMode === 'edit' ? 'bg-warning' : 'bg-primary' }}">
                        <h5 class="modal-title text-white">
                            <i class="fas {{ $modalMode === 'edit' ? 'fa-clipboard-check' : 'fa-cogs' }} mr-2"></i>
                            {{ $modalMode === 'edit' ? 'Submit Sparepart' : 'Sparepart Details' }} -
                            {{ $selectedWorkOrder->notification_number }}
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" wire:click="closeModal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Konten berbeda berdasarkan mode -->
                        @if ($modalMode === 'view')
                            <!-- MODE VIEW: Table biasa seperti sebelumnya -->
                            <div class="row">
                                <div class="col-md-12">
                                    <h6 class="mb-3">
                                        <i class="fas fa-list mr-2"></i>
                                        Sparepart Requirements ({{ $sparepartDetails->count() }} items)
                                    </h6>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover table-borderless">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="50">No</th>
                                                    <th>No Material</th>
                                                    <th width="100">Quantity</th>
                                                    <th width="80">UOM</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($sparepartDetails as $index => $detail)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $detail->sparepart->code ?? '-' }}</td>
                                                        <td>{{ $detail->qty ?? '-' }}</td>
                                                        <td>{{ $detail->uom ?? '-' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted">
                                                            <i class="fas fa-info-circle mr-2"></i>
                                                            No sparepart details found for this work order.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- MODE EDIT: Form dengan Request Items -->
                            <div class="row">
                                <div class="col-md-12">
                                    <h6 class="mb-3">
                                        <i class="fas fa-list-alt mr-2"></i>
                                        Requested Items ({{ $sparepartDetails->count() }} items)
                                    </h6>

                                    @foreach ($sparepartDetails as $index => $detail)
                                        <div class="form-group">
                                            <label>Request Item {{ $index + 1 }}</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" readonly
                                                    value="{{ $detail->sparepart->code ?? $detail->barcode }} - Qty: {{ $detail->qty }} {{ $detail->uom }}"
                                                    style="background-color: #e9ecef;">
                                                <div class="input-group-append">
                                                    <button class="btn btn-primary" type="button"
                                                        wire:click="openScanModal({{ $index }})"
                                                        title="Scan Barcode">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    <!-- Reference Table -->
                                    <hr class="my-4">
                                    <h6 class="mb-3">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Sparepart Details Reference
                                    </h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="50">No</th>
                                                    <th>No Material</th>
                                                    <th width="100">Quantity</th>
                                                    <th width="80">UOM</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($sparepartDetails as $index => $detail)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $detail->sparepart->code ?? '-' }}</td>
                                                        <td>{{ $detail->qty }}</td>
                                                        <td>{{ $detail->uom }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-pill btn-secondary" data-dismiss="modal"
                            wire:click="closeModal">
                            <i class="fas fa-times mr-2"></i>Close
                        </button>

                        <!-- Tombol Submit hanya muncul di mode edit -->
                        @if ($modalMode === 'edit')
                            <button type="button" class="btn btn-pill btn-success">
                                <i class="fas fa-check mr-2"></i>Submit Data
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Scan Barcode Modal -->
    @if ($showScanModal)
        <div wire:ignore.self class="modal fade" id="scanModal" tabindex="-1" role="dialog"
            data-backdrop="static">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content shadow-lg">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white">
                            <i class="fas fa-barcode mr-2"></i>Scan Barcode
                        </h5>
                        <button type="button" class="close text-white" wire:click="closeScanModal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="scannedBarcode">No Material</label>
                            <input type="text" class="form-control" id="scannedBarcode"
                                wire:model="scannedBarcode" placeholder="Autofill setelah discan">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="scannedQuantity">Quantity</label>
                                    <input type="number" class="form-control" id="scannedQuantity"
                                        wire:model="scannedQuantity" placeholder="Masukan QTY" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="scannedUom">UOM</label>
                                    <input type="text" class="form-control" id="scannedUom"
                                        wire:model="scannedUom" placeholder="Masukan UOM">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-pill btn-secondary" wire:click="closeScanModal">
                            <i class="fas fa-times mr-2"></i>Close
                        </button>
                        <button type="button" class="btn btn-pill btn-success">
                            <i class="fas fa-save mr-2"></i>Save Data
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

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
            // Show detail modal when data is ready
            Livewire.on('showDetailModal', () => {
                setTimeout(() => {
                    $('#detailModal').modal('show');
                }, 100);
            });

            // Close detail modal specifically
            Livewire.on('closeDetailModal', () => {
                $('#detailModal').modal('hide');
            });

            // Show scan modal
            Livewire.on('showScanModal', () => {
                setTimeout(() => {
                    $('#scanModal').modal('show');
                }, 100);
            });

            // Close scan modal
            Livewire.on('closeScanModal', () => {
                $('#scanModal').modal('hide');
            });

            // Prevent accidental modal closing
            $('#detailModal').on('hide.bs.modal', function(e) {
                if (e.target !== this) return false;
            });

            // Prevent backdrop click on scan modal
            $('#scanModal').on('hide.bs.modal', function(e) {
                if (e.target !== this) return false;
            });

            // Ensure body keeps modal-open class when scan modal opens over detail modal
            $('#scanModal').on('shown.bs.modal', function() {
                $('body').addClass('modal-open');
            });
        });

        document.addEventListener('livewire:navigated', function() {
            // Duplicate event listeners for SPA navigation
            Livewire.on('showDetailModal', () => {
                setTimeout(() => {
                    $('#detailModal').modal('show');
                }, 100);
            });

            Livewire.on('closeDetailModal', () => {
                $('#detailModal').modal('hide');
            });

            Livewire.on('showScanModal', () => {
                setTimeout(() => {
                    $('#scanModal').modal('show');
                }, 100);
            });

            Livewire.on('closeScanModal', () => {
                $('#scanModal').modal('hide');
            });
        });
    </script>
@endpush
