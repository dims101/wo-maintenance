{{-- <x-slot:subTitile> {{ $subTitle }} </x-slot> --}}
<div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h2>Ticket Maintenance - Open</h2>
                    <hr>

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
                                        placeholder="Search...">
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
                                    <th>Status</th>
                                    <th>Urgent Level</th>
                                    <th>Notification Date</th>
                                    <th>Department Requester</th>
                                    <th>Requester Name</th>
                                    <th>Malfunction Start</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($workOrders && $workOrders->count() > 0)
                                    @foreach ($workOrders as $workOrder)
                                        <tr>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-info"
                                                        title="View Details"
                                                        wire:click="openDetailModal({{ $workOrder->id }})">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td>
                                                <span> {{ $workOrder->status }} </span>
                                            </td>
                                            <td>
                                                @switch($workOrder->urgent_level)
                                                    @case('High')
                                                    @case('Critical')
                                                        <span class="badge badge-danger">{{ $workOrder->urgent_level }}</span>
                                                    @break

                                                    @case('Medium')
                                                        <span class="badge badge-warning">{{ $workOrder->urgent_level }}</span>
                                                    @break

                                                    @case('Low')
                                                        <span class="badge badge-success">{{ $workOrder->urgent_level }}</span>
                                                    @break

                                                    @default
                                                        <span
                                                            class="badge badge-secondary">{{ $workOrder->urgent_level ?? 'Not Set' }}</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                {{ $workOrder->notification_date ? $workOrder->notification_date->format('d M Y') : '-' }}
                                            </td>
                                            <td>{{ $workOrder->department->name ?? '-' }}</td>
                                            <td>{{ $workOrder->user->name ?? '-' }}</td>
                                            <td>
                                                {{ $workOrder->malfunction_start ? $workOrder->malfunction_start->format('d M Y H:i') : '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <div class="py-4">
                                                <i class="fas fa-search fa-2x text-muted mb-3"></i>
                                                <p class="text-muted">No work orders found</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
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

    <!-- Single Detail Modal -->
    @if ($selectedWorkOrder)
        <div wire:ignore.self class="modal fade" id="detailModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title">Assign Approval - {{ $selectedWorkOrder->notification_number }}</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" wire:click="closeModal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="strong">Notification Number</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedWorkOrder->notification_number }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label class="strong">Urgent Level</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedWorkOrder->urgent_level }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label class="strong">Requester Name</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedWorkOrder->user->name ?? '-' }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label class="strong">Functional Location</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedWorkOrder->equipment->functionalLocation->name ?? '-' }}"
                                        readonly>
                                </div>

                                <div class="form-group">
                                    <label class="strong">Malfunction Start</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedWorkOrder->malfunction_start ? $selectedWorkOrder->malfunction_start->format('d-m-Y - H:i') : '-' }}"
                                        readonly>
                                </div>

                                <div class="form-group">
                                    <label class="strong">Priority</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedWorkOrder->priority ?? '-' }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label class="strong">Equipment</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedWorkOrder->equipment->name ?? '-' }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label class="strong">Notes</label>
                                    <textarea class="form-control" rows="3" readonly>{{ $selectedWorkOrder->notes ?? '-' }}</textarea>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="strong">Notification Date</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedWorkOrder->notification_date ? $selectedWorkOrder->notification_date->format('d-m-Y - H:i') : '-' }}"
                                        readonly>
                                </div>

                                <div class="form-group">
                                    <label class="strong">Department Requester</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedWorkOrder->department->name ?? '-' }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label class="strong">Planner Group</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedWorkOrder->plannerGroup->name ?? '-' }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label class="strong">Plant</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedWorkOrder->equipment->functionalLocation->resource->plant->name ?? '-' }}"
                                        readonly>
                                </div>

                                <div class="form-group">
                                    <label class="strong">Work Description</label>
                                    <textarea class="form-control" rows="3" readonly>{{ $selectedWorkOrder->work_desc ?? '-' }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label class="strong">Is Breakdown</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedWorkOrder->is_breakdown ? 'Yes' : 'No' }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label class="strong">Resource</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedWorkOrder->equipment->functionalLocation->resource->name ?? '-' }}"
                                        readonly>
                                </div>

                                @if ($selectedWorkOrder->is_spv_rejected)
                                    <div class="form-group">
                                        <label class="strong">Reject Reason</label>
                                        <textarea class="form-control" rows="3" readonly>{{ $selectedWorkOrder->spv_reject_reason }}</textarea>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-pill" data-dismiss="modal"
                            wire:click="closeModal">Close</button>
                        @if ($selectedWorkOrder->status == 'Waiting for SPV Approval' && !$selectedWorkOrder->is_spv_rejected)
                            <button type="button" class="btn btn-danger btn-pill" data-toggle="modal"
                                data-target="#popupModal"
                                wire:click='openPopupModal("reject","spvUser")'>Reject</button>
                            <button type="button" class="btn btn-success btn-pill" data-toggle="modal"
                                data-target="#popupModal"
                                wire:click='openPopupModal("approve","spvUser")'>Approve</button>
                        @elseif ($selectedWorkOrder->status == 'Waiting for Maintenance Approval' && !$selectedWorkOrder->is_spv_rejected)
                            <button type="button" class="btn btn-info btn-pill" data-toggle="modal"
                                data-target="#popupModal"
                                wire:click='openPopupModal("receive","spvMaintenance")'>Receive</button>
                            <button type="button" class="btn btn-danger btn-pill" data-toggle="modal"
                                data-target="#popupModal"
                                wire:click='openPopupModal("reject","spvMaintenance")'>Reject</button>
                        @elseif ($selectedWorkOrder->status == 'Received by Maintenance' && !$selectedWorkOrder->is_spv_rejected)
                            <button type="button" class="btn btn-danger btn-pill" data-toggle="modal"
                                data-target="#popupModal"
                                wire:click='openPopupModal("reject","spvMaintenance")'>Reject</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Popup Modal -->
    <div wire:ignore.self class="modal fade" data-backdrop="static" id="popupModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content shadow-lg">
                <div class="modal-header {{ $popupModalHeaderClass }}">
                    <h5 class="modal-title text-white">{{ $popupModalTitle }}</h5>
                    <button type="button" class="close text-white" wire:click="resetPopup" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="reason">Reason:</label>
                            <textarea wire:model='reason' name="reason" class="form-control" rows="3" required
                                placeholder="Please provide a reason..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="resetPopup"
                            data-dismiss="modal">Cancel</button>
                        @if ($popupModalActor == 'spvUser')
                            @if ($popupModalAction == 'reject')
                                <button type="button" wire:click="confirmReject"
                                    class="btn btn-danger">Reject</button>
                            @elseif($popupModalAction == 'approve')
                                <button type="button" wire:click="confirmApprove"
                                    class="btn btn-success">Approve</button>
                            @endif
                        @elseif ($popupModalActor == 'spvMaintenance')
                            @if ($popupModalAction == 'reject')
                                <button type="button" wire:click="confirmMaintenanceReject"
                                    class="btn btn-danger">Reject</button>
                            @elseif($popupModalAction == 'receive')
                                <button type="button" wire:click="confirmMaintenanceReceive"
                                    class="btn btn-info">Receive</button>
                            @endif
                        @endif
                    </div>
                </form>
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
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script> --}}
    <script>
        // Helper functions for modal management
        // function showApproveModal() {
        //     $('#approveModal').modal('show');
        // }

        // function hideApproveModal() {
        //     $('#approveModal').modal('hide');
        //     @this.closeActionModal();
        // }

        // function showRejectModal() {
        //     $('#rejectModal').modal('show');
        // }

        // function hideRejectModal() {
        //     $('#rejectModal').modal('hide');
        //     @this.closeActionModal();
        // }

        document.addEventListener('livewire:initialized', function() {
            // Show detail modal when data is ready
            Livewire.on('showDetailModal', () => {
                // Small delay to ensure DOM is updated
                setTimeout(() => {
                    $('#detailModal').modal('show');
                }, 100);
            });

            // Close detail modal specifically
            Livewire.on('closeDetailModal', () => {
                $('#detailModal').modal('hide');
            });

            // Close all modals (used after approve/reject success)
            Livewire.on('closeAllModals', () => {
                $('.modal').modal('hide');
            });

            // Confirm approve with SweetAlert
            Livewire.on('confirmApprove', () => {
                // Validate reason first
                if (!@this.reason || @this.reason.trim() === '') {
                    swal({
                        title: "Error!",
                        text: "Please provide approval reason before proceeding.",
                        icon: "error",
                        button: "OK",
                    });
                    return;
                }

                // Close approve modal first
                // $('#approveModal').modal('hide');

                swal({
                    title: "Are you sure?",
                    text: "You are about to approve this work order. This action cannot be undone.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true,
                        },
                        confirm: {
                            text: "Yes, Approve",
                            value: true,
                            visible: true,
                            closeModal: true
                        }
                    },
                    dangerMode: false,
                }).then((result) => {
                    if (result) {
                        @this.approveWorkOrder();
                    } else {
                        // If cancelled, show approve modal again
                        // setTimeout(() => {
                        //     $('#approveModal').modal('show');
                        // }, 200);
                    }
                });
            });

            // Confirm reject with SweetAlert
            Livewire.on('confirmReject', () => {
                // Validate reason first
                if (!@this.reason || @this.reason.trim() === '') {
                    swal({
                        title: "Error!",
                        text: "Please provide rejection reason before proceeding.",
                        icon: "error",
                        button: "OK",
                    });
                    return;
                }
                // Close reject modal first
                // $('#rejectModal').modal('hide');

                swal({
                    title: "Are you sure?",
                    text: "You are about to reject this work order. This action cannot be undone.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true,
                        },
                        confirm: {
                            text: "Yes, Reject",
                            value: true,
                            visible: true,
                            closeModal: true
                        }
                    },
                    dangerMode: true,
                }).then((result) => {
                    if (result) {
                        @this.rejectWorkOrder();
                    } else {
                        // If cancelled, show reject modal again
                        // setTimeout(() => {
                        //     $('#rejectModal').modal('show');
                        // }, 200);
                    }
                });
            });

            Livewire.on('confirmMaintenanceReceive', () => {
                // Validate reason first
                if (!@this.reason || @this.reason.trim() === '') {
                    swal({
                        title: "Error!",
                        text: "Please provide reception reason before proceeding.",
                        icon: "error",
                        button: "OK",
                    });
                    return;
                }
                // Close reject modal first
                // $('#rejectModal').modal('hide');

                swal({
                    title: "Are you sure?",
                    text: "You are about to receive this work order. This action cannot be undone.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true,
                        },
                        confirm: {
                            text: "Yes, Receive",
                            value: true,
                            visible: true,
                            closeModal: true
                        }
                    },
                    dangerMode: false,
                }).then((result) => {
                    if (result) {
                        @this.receiveMaintenance();
                    } else {
                        // If cancelled, show reject modal again
                        // setTimeout(() => {
                        //     $('#rejectModal').modal('show');
                        // }, 200);
                    }
                });
            });

            Livewire.on('confirmMaintenanceReject', () => {
                // Validate reason first
                if (!@this.reason || @this.reason.trim() === '') {
                    swal({
                        title: "Error!",
                        text: "Please provide rejection reason before proceeding.",
                        icon: "error",
                        button: "OK",
                    });
                    return;
                }
                // Close reject modal first
                // $('#rejectModal').modal('hide');

                swal({
                    title: "Are you sure?",
                    text: "You are about to reject this work order. This action cannot be undone.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true,
                        },
                        confirm: {
                            text: "Yes, Reject",
                            value: true,
                            visible: true,
                            closeModal: true
                        }
                    },
                    dangerMode: true,
                }).then((result) => {
                    if (result) {
                        @this.rejectMaintenance();
                    } else {
                        // If cancelled, show reject modal again
                        // setTimeout(() => {
                        //     $('#rejectModal').modal('show');
                        // }, 200);
                    }
                });
            });

            // Prevent accidental modal closing
            $('#detailModal').on('hide.bs.modal', function(e) {
                // Allow closing only via button clicks or explicit calls
                if (e.target !== this) return false;
            });

            $('#approveModal, #rejectModal').on('hide.bs.modal', function(e) {
                // Allow closing only via button clicks
                if (e.target !== this) return false;
            });
        });

        document.addEventListener('livewire:navigated', function() {
            // Show detail modal when data is ready
            Livewire.on('showDetailModal', () => {
                // Small delay to ensure DOM is updated
                setTimeout(() => {
                    $('#detailModal').modal('show');
                }, 100);
            });

            // Close detail modal specifically
            Livewire.on('closeDetailModal', () => {
                $('#detailModal').modal('hide');
            });

            // Close all modals (used after approve/reject success)
            Livewire.on('closeAllModals', () => {
                $('.modal').modal('hide');
            });

            // Confirm approve with SweetAlert
            Livewire.on('confirmApprove', () => {
                // Validate reason first
                if (!@this.reason || @this.reason.trim() === '') {
                    swal({
                        title: "Error!",
                        text: "Please provide approval reason before proceeding.",
                        icon: "error",
                        button: "OK",
                    });
                    return;
                }

                // Close approve modal first
                // $('#approveModal').modal('hide');

                swal({
                    title: "Are you sure?",
                    text: "You are about to approve this work order. This action cannot be undone.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true,
                        },
                        confirm: {
                            text: "Yes, Approve",
                            value: true,
                            visible: true,
                            closeModal: true
                        }
                    },
                    dangerMode: false,
                }).then((result) => {
                    if (result) {
                        @this.approveWorkOrder();
                    } else {
                        // If cancelled, show approve modal again
                        // setTimeout(() => {
                        //     $('#approveModal').modal('show');
                        // }, 200);
                    }
                });
            });

            Livewire.on('confirmMaintenanceReceive', () => {
                // Validate reason first
                if (!@this.reason || @this.reason.trim() === '') {
                    swal({
                        title: "Error!",
                        text: "Please provide reception reason before proceeding.",
                        icon: "error",
                        button: "OK",
                    });
                    return;
                }
                // Close reject modal first
                // $('#rejectModal').modal('hide');

                swal({
                    title: "Are you sure?",
                    text: "You are about to receive this work order. This action cannot be undone.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true,
                        },
                        confirm: {
                            text: "Yes, Receive",
                            value: true,
                            visible: true,
                            closeModal: true
                        }
                    },
                    dangerMode: false,
                }).then((result) => {
                    if (result) {
                        @this.receiveMaintenance();
                    } else {
                        // If cancelled, show reject modal again
                        // setTimeout(() => {
                        //     $('#rejectModal').modal('show');
                        // }, 200);
                    }
                });
            });

            Livewire.on('confirmMaintenanceReject', () => {
                // Validate reason first
                if (!@this.reason || @this.reason.trim() === '') {
                    swal({
                        title: "Error!",
                        text: "Please provide rejection reason before proceeding.",
                        icon: "error",
                        button: "OK",
                    });
                    return;
                }
                // Close reject modal first
                // $('#rejectModal').modal('hide');

                swal({
                    title: "Are you sure?",
                    text: "You are about to reject this work order. This action cannot be undone.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true,
                        },
                        confirm: {
                            text: "Yes, Reject",
                            value: true,
                            visible: true,
                            closeModal: true
                        }
                    },
                    dangerMode: true,
                }).then((result) => {
                    if (result) {
                        @this.rejectMaintenance();
                    } else {
                        // If cancelled, show reject modal again
                        // setTimeout(() => {
                        //     $('#rejectModal').modal('show');
                        // }, 200);
                    }
                });
            });

            // Confirm reject with SweetAlert
            Livewire.on('confirmReject', () => {
                // Validate reason first
                if (!@this.reason || @this.reason.trim() === '') {
                    swal({
                        title: "Error!",
                        text: "Please provide rejection reason before proceeding.",
                        icon: "error",
                        button: "OK",
                    });
                    return;
                }

                // Close reject modal first
                // $('#rejectModal').modal('hide');

                swal({
                    title: "Are you sure?",
                    text: "You are about to reject this work order. This action cannot be undone.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true,
                        },
                        confirm: {
                            text: "Yes, Reject",
                            value: true,
                            visible: true,
                            closeModal: true
                        }
                    },
                    dangerMode: true,
                }).then((result) => {
                    if (result) {
                        @this.rejectWorkOrder();
                    } else {
                        // If cancelled, show reject modal again
                        // setTimeout(() => {
                        //     $('#rejectModal').modal('show');
                        // }, 200);
                    }
                });
            });

            // Prevent accidental modal closing
            $('#detailModal').on('hide.bs.modal', function(e) {
                // Allow closing only via button clicks or explicit calls
                if (e.target !== this) return false;
            });

            // $('#approveModal, #rejectModal').on('hide.bs.modal', function(e) {
            //     // Allow closing only via button clicks
            //     if (e.target !== this) return false;
            // });
        });
    </script>
@endpush
