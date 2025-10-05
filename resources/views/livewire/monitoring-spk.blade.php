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
                                    <th>Progress</th>
                                    <th>Urgent Level</th>
                                    <th>Notification Date</th>
                                    <th>Requester Department</th>
                                    <th>Requester Name</th>
                                    <th>Malfunction start</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($workOrders && $workOrders->count() > 0)
                                    @foreach ($workOrders as $workOrder)
                                        <tr>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-link btn-lg btn-info"
                                                        title="View Details"
                                                        wire:click="openDetailModal({{ $workOrder->id }})">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td>
                                                @switch($workOrder->status)
                                                    @case('Waiting for SPV Approval')
                                                        <span
                                                            class="badge badge-warning">{{ $workOrder->status ?? 'Not Set' }}</span>
                                                    @break

                                                    @case('Requested to change planner group')
                                                        <span
                                                            class="badge badge-warning">{{ $workOrder->status ?? 'Not Set' }}</span>
                                                    @break

                                                    @case('Planned')
                                                        <span
                                                            class="badge badge-success">{{ $workOrder->status ?? 'Not Set' }}</span>
                                                    @break

                                                    @case('Waiting for Maintenance Approval')
                                                        <span
                                                            class="badge badge-warning">{{ $workOrder->status ?? 'Not Set' }}</span>
                                                    @break

                                                    @case('Rejected by Maintenance')
                                                        <span
                                                            class="badge badge-danger">{{ $workOrder->status ?? 'Not Set' }}</span>
                                                    @break

                                                    @case('Received by Maintenance')
                                                        <span
                                                            class="badge badge-info">{{ $workOrder->status ?? 'Not Set' }}</span>
                                                    @break

                                                    @case('Requested to be closed')
                                                        <span
                                                            class="badge badge-info">{{ $workOrder->status ?? 'Not Set' }}</span>
                                                    @break

                                                    @case('Need Revision')
                                                        <span
                                                            class="badge badge-primary">{{ $workOrder->status ?? 'Not Set' }}</span>
                                                    @break

                                                    @case('Close')
                                                        <span
                                                            class="badge badge-success">{{ $workOrder->status ?? 'Not Set' }}</span>
                                                    @break

                                                    @default
                                                        <span
                                                            class="badge badge-secondary">{{ $workOrder->status ?? 'Not Set' }}</span>
                                                @endswitch
                                            </td>
                                            <td class="text-center">
                                                <div class="progress">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                                                        role="progressbar" aria-valuenow="{{ $workOrder->progress }}"
                                                        aria-valuemin="0" aria-valuemax="100"
                                                        style="width: {{ $workOrder->progress }}%">
                                                        {{ $workOrder->progress > 0 ? $workOrder->progress . '%' : '0%' }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                {{ $workOrder->urgent_level }}
                                            </td>
                                            <td>
                                                {{ $workOrder->notification_date->format('d M Y') }}
                                            </td>
                                            <td>
                                                {{ $workOrder->department->name }}
                                            </td>
                                            <td>
                                                {{ $workOrder->user->name }}
                                            </td>
                                            <td>
                                                {{ $workOrder->malfunction_start->format('d M Y H:i') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="8" class="text-center">
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

                                @if ($selectedWorkOrder->revision_note)
                                    <div class="form-group">
                                        <label class="strong">Revision notes</label>
                                        <textarea class="form-control" rows="3" readonly>{{ $selectedWorkOrder->revision_note }}</textarea>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-pill" data-dismiss="modal"
                            wire:click="closeModal">Close</button>
                        @if (
                            $selectedWorkOrder->status == 'Planned' ||
                                $selectedWorkOrder->status == 'Requested to be closed' ||
                                $selectedWorkOrder->status == 'Requested to change planner group')
                            <button type="button" class="btn btn-warning btn-pill" data-toggle="modal"
                                data-target="#progressModal">Detail
                                progress</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (
        $selectedWorkOrder &&
            ($selectedWorkOrder->status == 'Planned' ||
                $selectedWorkOrder->status == 'Requested to change planner group' ||
                $selectedWorkOrder->status == 'Requested to be closed'))
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
                                            role="progressbar"
                                            aria-valuenow="{{ $selectedWorkOrder->maintenanceApproval->progress }}"
                                            aria-valuemin="0" aria-valuemax="100"
                                            style="width: {{ $selectedWorkOrder->maintenanceApproval->progress }}%">
                                            {{ $selectedWorkOrder->maintenanceApproval->progress }}%
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="strong">Task List</label>
                                    <br>
                                    @php
                                        $groupedTasks = collect($activityLists)->groupBy('planner_group_id');
                                        $plannerGroupNames = [
                                            1 => 'Elektrik',
                                            2 => 'Mekanik',
                                        ];
                                    @endphp
                                    @forelse ($groupedTasks as $plannerGroupId => $tasks)
                                        <div class="mb-2 container">
                                            <h6 class="font-weight-bold text-primary">
                                                {{ $plannerGroupNames[$plannerGroupId] ?? 'Planner Group ' . $plannerGroupId }}
                                            </h6>
                                            @foreach ($tasks as $task)
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="custom-control custom-checkbox flex-grow-1">
                                                        <input type="checkbox" class="custom-control-input"
                                                            id="task{{ $task['id'] }}"
                                                            @if ($task['is_done']) checked @endif disabled>
                                                        <label class="custom-control-label"
                                                            for="task{{ $task['id'] }}">
                                                            {{ $task['task'] }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @empty
                                        <p class="text-muted">No tasks added yet.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-pill" wire:click="resetProgressModal"
                            data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif


    <!-- Loading Indicator -->
    <div wire:loading.delay class="position-fixed"
        style="top: 0; left: 0; width: 100%; height: 100%; background: rgba(178, 188, 202, 0.1); z-index: 9999;">
        <div class="d-flex justify-content-center align-items-center h-100">
            <div class="text-center">
                <div class="mb-3"
                    style="width: 3rem; height: 3rem; margin: 0 auto; border-radius: 50%; background: #1572e8; animation: grow 1.5s ease-in-out infinite;">
                </div>
                <h5 style="color: #1572e8;"></h5>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <style>
        @keyframes grow {

            0%,
            100% {
                transform: scale(0.5);
                opacity: 0.3;
            }

            50% {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', function() {
            // Show detail modal when data is ready

            // Tambah setelah event listener confirmProgress
            Livewire.on('confirmSparepartSubmit', () => {
                swal({
                    title: "Are you sure?",
                    text: "You are about to submit sparepart reservation. This action cannot be undone.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true,
                        },
                        confirm: {
                            text: "Yes, Submit",
                            value: true,
                            visible: true,
                            closeModal: true
                        }
                    },
                    dangerMode: false,
                }).then((result) => {
                    if (result) {
                        @this.saveSparepartReservation();
                    }
                });
            });

            // Tambah setelah event listener yang sudah ada
            Livewire.on('confirmProgress', () => {
                swal({
                    title: "Are you sure?",
                    text: "You are about to update work progress. This action cannot be undone.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true,
                        },
                        confirm: {
                            text: "Yes, Update",
                            value: true,
                            visible: true,
                            closeModal: true
                        }
                    },
                    dangerMode: false,
                }).then((result) => {
                    if (result) {
                        @this.updateProgress();
                    }
                });
            });
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
            Livewire.on('confirmClose', () => {

                if (!@this.reason || @this.reason.trim() === '') {
                    swal({
                        title: "Error!",
                        text: "Please provide close reason before proceeding.",
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
                        @this.closeWorkOrder();
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
            // Tambah setelah event listener confirmProgress
            Livewire.on('confirmSparepartSubmit', () => {
                swal({
                    title: "Are you sure?",
                    text: "You are about to submit sparepart reservation. This action cannot be undone.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true,
                        },
                        confirm: {
                            text: "Yes, Submit",
                            value: true,
                            visible: true,
                            closeModal: true
                        }
                    },
                    dangerMode: false,
                }).then((result) => {
                    if (result) {
                        @this.saveSparepartReservation();
                    }
                });
            });
            // Tambah setelah event listener yang sudah ada
            Livewire.on('confirmProgress', () => {
                swal({
                    title: "Are you sure?",
                    text: "You are about to update work progress. This action cannot be undone.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true,
                        },
                        confirm: {
                            text: "Yes, Update",
                            value: true,
                            visible: true,
                            closeModal: true
                        }
                    },
                    dangerMode: false,
                }).then((result) => {
                    if (result) {
                        @this.updateProgress();
                    }
                });
            });
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

            Livewire.on('confirmClose', () => {
                if (!@this.reason || @this.reason.trim() === '') {
                    swal({
                        title: "Error!",
                        text: "Please provide close reason before proceeding.",
                        icon: "error",
                        button: "OK",
                    });
                    return;
                }

                swal({
                    title: "Are you sure?",
                    text: "You are about to close this work order. This action cannot be undone.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true
                        },
                        confirm: {
                            text: "Yes, close",
                            value: true,
                            visible: true,
                            closeModal: true
                        }
                    },
                    dangerMode: false,
                }).then((result) => {
                    if (result) @this.closeWorkOrder();
                });
            });


            Livewire.on('clearDropdown', (index) => {
                setTimeout(() => {
                    @this.hideSparepartDropdown(index);
                }, 100);
            });
        });
    </script>
@endpush
