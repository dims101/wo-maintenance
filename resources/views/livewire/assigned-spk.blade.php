{{-- <x-slot:subTitile> {{ $subTitle }} </x-slot> --}}
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
                                    <th>Notification Number</th>
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
                                                {{ $workOrder->notification_number }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="3" class="text-center">
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
                        <button type="button" class="btn btn-info btn-pill" data-toggle="modal"
                            data-target="#sparepartModal" wire:click=''>Sparepart
                            reservation</button>
                        <button type="button" class="btn btn-warning btn-pill" data-toggle="modal"
                            data-target="#progressModal" wire:click=''>Update
                            progress</button>
                        <button type="button" class="btn btn-success btn-pill" data-toggle="modal"
                            data-target="#closeModal" wire:click=''>Request to
                            close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Sparepart Modal --}}
    @if ($selectedWorkOrder)
        <div wire:ignore.self class="modal fade" data-backdrop="static" id="sparepartModal" tabindex="-1"
            role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content shadow-lg">
                    <div class="modal-header bg-info">
                        <h5 class="modal-title text-white">Sparepart Reservation</h5>
                        <button type="button" class="close text-white" wire:click="resetSparepartModal"
                            data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <!-- Team Members -->
                                <div class="form-group">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="strong">Request item <span class="text-danger">*</span></label>
                                    </div>
                                    @foreach ($teamMembers as $index => $member)
                                        <div class="input-group mb-2">
                                            <select class="custom-select"
                                                wire:model="teamMembers.{{ $index }}">
                                                <option value="">Request sparepart {{ $index + 1 }}</option>
                                                @foreach ($users as $user)
                                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="input-group-append">
                                                @if ($index == 0)
                                                    @if (count($teamMembers) < 99)
                                                        <button type="button" class="btn btn-success btn-sm"
                                                            wire:click="addTeamMember" title="Add another sparepart">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    @endif
                                                @else
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        wire:click="removeTeamMember({{ $index }})"
                                                        title="Remove this sparepart">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-pill" wire:click="resetSparepartModal"
                            data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success btn-pill"
                            wire:click="submitSparepart">Approve</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($selectedWorkOrder)
        <div wire:ignore.self class="modal fade" data-backdrop="static" id="progressModal" tabindex="-1"
            role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content shadow-lg">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title text-white">Work Progress Detail</h5>
                        <button type="button" class="close text-white" wire:click="resetProgressModal"
                            data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="strong">Progress</label>
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                                            role="progressbar" aria-valuenow="75" aria-valuemin="0"
                                            aria-valuemax="100" style="width: 0%%">0%
                                        </div>
                                        <hr>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="strong">Add a new task <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control">
                                        <div class="input-group-append">
                                            <button class="btn btn-success">add</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="strong">Task List</label>
                                    <br>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="customCheck1">
                                        <label class="custom-control-label" for="customCheck1">Check this custom
                                            checkbox</label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="customCheck1">
                                        <label class="custom-control-label" for="customCheck1">Check this custom
                                            checkbox</label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="customCheck1">
                                        <label class="custom-control-label" for="customCheck1">Check this custom
                                            checkbox</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-pill" wire:click="resetProgressModal"
                            data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success btn-pill"
                            wire:click="submitProgress">Update</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Close Modal -->
    @if ($selectedWorkOrder)
        <div wire:ignore.self class="modal fade" data-backdrop="static" id="closeModal" tabindex="-1"
            role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content shadow-lg">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-white">Request to close</h5>
                        <button type="button" class="close text-white" wire:click="resetPopup"
                            data-dismiss="modal">
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
                            <button type="button" class="btn btn-secondary btn-pill" wire:click="resetPopup"
                                data-dismiss="modal">Cancel</button>
                            <button type="button" wire:click="confirmChange" class="btn btn-warning btn-pill">Planner Change</button>
                            <button type="button" wire:click="confirmClose"
                                class="btn btn-success btn-pill">Close</button>
                        </div>
                    </form>
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

            // Close all modals (used after approve/reject success)
            Livewire.on('closeAllModals', () => {
                $('.modal').modal('hide');
            });

            // Open new tab for team creation
            Livewire.on('openNewTab', (url) => {
                window.open(url, '_blank');
            });

            // Confirm approve with SweetAlert
            Livewire.on('confirmApprove', () => {
                if (!@this.reason || @this.reason.trim() === '') {
                    swal({
                        title: "Error!",
                        text: "Please provide approval reason before proceeding.",
                        icon: "error",
                        button: "OK",
                    });
                    return;
                }

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
                    }
                });
            });

            // Confirm maintenance approve
            Livewire.on('confirmMaintenanceApprove', () => {
                swal({
                    title: "Are you sure?",
                    text: "You are about to approve maintenance and assign team. This action cannot be undone.",
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
                        @this.approveMaintenance();
                    }
                });
            });

            // Confirm reject with SweetAlert
            Livewire.on('confirmReject', () => {
                if (!@this.reason || @this.reason.trim() === '') {
                    swal({
                        title: "Error!",
                        text: "Please provide rejection reason before proceeding.",
                        icon: "error",
                        button: "OK",
                    });
                    return;
                }

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
                    }
                });
            });

            Livewire.on('confirmMaintenanceReceive', () => {
                if (!@this.reason || @this.reason.trim() === '') {
                    swal({
                        title: "Error!",
                        text: "Please provide reception reason before proceeding.",
                        icon: "error",
                        button: "OK",
                    });
                    return;
                }

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
                    }
                });
            });

            Livewire.on('confirmMaintenanceReject', () => {
                if (!@this.reason || @this.reason.trim() === '') {
                    swal({
                        title: "Error!",
                        text: "Please provide rejection reason before proceeding.",
                        icon: "error",
                        button: "OK",
                    });
                    return;
                }

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
                    }
                });
            });

            // Prevent accidental modal closing
            $('#detailModal').on('hide.bs.modal', function(e) {
                if (e.target !== this) return false;
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

            Livewire.on('closeAllModals', () => {
                $('.modal').modal('hide');
            });

            Livewire.on('openNewTab', (url) => {
                window.open(url, '_blank');
            });

            Livewire.on('confirmApprove', () => {
                if (!@this.reason || @this.reason.trim() === '') {
                    swal({
                        title: "Error!",
                        text: "Please provide approval reason before proceeding.",
                        icon: "error",
                        button: "OK",
                    });
                    return;
                }

                swal({
                    title: "Are you sure?",
                    text: "You are about to approve this work order. This action cannot be undone.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true
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
                    if (result) @this.approveWorkOrder();
                });
            });

            Livewire.on('confirmMaintenanceApprove', () => {
                swal({
                    title: "Are you sure?",
                    text: "You are about to approve maintenance and assign team. This action cannot be undone.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true
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
                    if (result) @this.approveMaintenance();
                });
            });

            Livewire.on('confirmReject', () => {
                if (!@this.reason || @this.reason.trim() === '') {
                    swal({
                        title: "Error!",
                        text: "Please provide rejection reason before proceeding.",
                        icon: "error",
                        button: "OK",
                    });
                    return;
                }

                swal({
                    title: "Are you sure?",
                    text: "You are about to reject this work order. This action cannot be undone.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true
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
                    if (result) @this.rejectWorkOrder();
                });
            });

            Livewire.on('confirmMaintenanceReceive', () => {
                if (!@this.reason || @this.reason.trim() === '') {
                    swal({
                        title: "Error!",
                        text: "Please provide reception reason before proceeding.",
                        icon: "error",
                        button: "OK",
                    });
                    return;
                }

                swal({
                    title: "Are you sure?",
                    text: "You are about to receive this work order. This action cannot be undone.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true
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
                    if (result) @this.receiveMaintenance();
                });
            });

            Livewire.on('confirmMaintenanceReject', () => {
                if (!@this.reason || @this.reason.trim() === '') {
                    swal({
                        title: "Error!",
                        text: "Please provide rejection reason before proceeding.",
                        icon: "error",
                        button: "OK",
                    });
                    return;
                }

                swal({
                    title: "Are you sure?",
                    text: "You are about to reject this work order. This action cannot be undone.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true
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
                    if (result) @this.rejectMaintenance();
                });
            });
        });
    </script>
@endpush
