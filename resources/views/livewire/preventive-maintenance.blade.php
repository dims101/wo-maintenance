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
                                    <th>Order</th>
                                    <th>Notification Number</th>
                                    <th>Description</th>
                                    <th>Basic Start Date</th>
                                    <th>User Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($preventiveMaintenances && $preventiveMaintenances->count() > 0)
                                    @foreach ($preventiveMaintenances as $pm)
                                        <tr>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-link btn-lg btn-info"
                                                        title="View Details"
                                                        wire:click="openDetailModal({{ $pm->id }})">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td>{{ $pm->order }}</td>
                                            <td>{{ $pm->notification_number }}</td>
                                            <td>{{ $pm->description }}</td>
                                            <td>{{ $pm->basic_start_date ? $pm->basic_start_date->format('d-m-Y') : '-' }}
                                            </td>
                                            <td>
                                                @php
                                                    $badgeClass = match (strtoupper($pm->user_status ?? '')) {
                                                        'COMPLETED' => 'badge-success',
                                                        'RESCHEDULED' => 'badge-info',
                                                        'ON PROGRESS' => 'badge-primary',
                                                        default => 'badge-warning',
                                                    };
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">
                                                    {{ $pm->user_status ?? 'Not Set' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            <div class="py-4">
                                                <i class="fas fa-search fa-2x text-muted mb-3"></i>
                                                <p class="text-muted">No preventive maintenance found</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if ($preventiveMaintenances->hasPages())
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <span class="text-muted">
                                        Showing {{ $preventiveMaintenances->firstItem() }} to
                                        {{ $preventiveMaintenances->lastItem() }} of
                                        {{ $preventiveMaintenances->total() }} entries
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-end">
                                    {{ $preventiveMaintenances->links() }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    @if ($selectedPm)
        <div wire:ignore.self class="modal fade" id="detailModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title">Preventive Maintenance - {{ $selectedPm->order }}</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" wire:click="closeModal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="strong">Order</label>
                                    <input type="text" class="form-control" value="{{ $selectedPm->order }}"
                                        readonly>
                                </div>

                                <div class="form-group">
                                    <label class="strong">Notification Number</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedPm->notification_number }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label class="strong">Main Work Center</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedPm->main_workctr ?? '-' }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label class="strong">Description</label>
                                    <textarea class="form-control" rows="3" readonly>{{ $selectedPm->description ?? '-' }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label class="strong">System Status</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedPm->system_status ?? '-' }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label class="strong">Basic Start Date</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedPm->basic_start_date ? $selectedPm->basic_start_date->format('d-m-Y') : '-' }}"
                                        readonly>
                                </div>

                                <div class="form-group">
                                    <label class="strong">Start Time</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedPm->start_time ? $selectedPm->start_time->format('H:i') : '-' }}"
                                        readonly>
                                </div>

                                <div class="form-group">
                                    <label class="strong">Functional Location</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedPm->functional_location ?? '-' }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label class="strong">FL Description</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedPm->fl_desc ?? '-' }}" readonly>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="strong">Plan Total Cost</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedPm->plan_total_cost ?? '-' }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label class="strong">Actual Total Cost</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedPm->actual_total_cost ?? '-' }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label class="strong">Actual Start Date</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedPm->actual_start_date ? $selectedPm->actual_start_date->format('d-m-Y') : '-' }}"
                                        readonly>
                                </div>

                                <div class="form-group">
                                    <label class="strong">Actual Start Time</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedPm->actual_start_time ? $selectedPm->actual_start_time->format('H:i') : '-' }}"
                                        readonly>
                                </div>

                                <div class="form-group">
                                    <label class="strong">Actual Finish</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedPm->actual_finish ? $selectedPm->actual_finish->format('d-m-Y H:i') : '-' }}"
                                        readonly>
                                </div>

                                <div class="form-group">
                                    <label class="strong">Entered By</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedPm->entered_by ?? '-' }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label class="strong">Order Type</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedPm->order_type ?? '-' }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label class="strong">User Status</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedPm->user_status ?? '-' }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label class="strong">Equipment</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedPm->equipment ?? '-' }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label class="strong">Equipment Description</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedPm->eq_desc ?? '-' }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-pill" data-dismiss="modal"
                            wire:click="closeModal">Close</button>

                        {{-- @if (!$this->isCompleted())
                            <button type="button" class="btn btn-info btn-pill" data-toggle="modal"
                                data-target="#sparepartModal">
                                <i class="fas fa-tools"></i> Sparepart
                            </button>
                            <button type="button" class="btn btn-warning btn-pill" data-toggle="modal"
                                data-target="#activityModal">
                                <i class="fas fa-tasks"></i> Activity
                            </button>
                        @endif --}}

                        @if ($this->canReschedule())
                            <button type="button" class="btn btn-primary btn-pill" wire:click="openRescheduleModal">
                                <i class="fas fa-calendar-alt"></i> Reschedule
                            </button>
                        @endif

                        @if ($this->canStart())
                            <button type="button" class="btn btn-success btn-pill" wire:click="confirmStart">
                                <i class="fas fa-play"></i> Start PM
                            </button>
                        @elseif ($this->canStop())
                            <button type="button" class="btn btn-danger btn-pill" wire:click="confirmStop">
                                <i class="fas fa-stop"></i> Stop PM
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Sparepart Modal -->
    @if ($selectedPm)
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
                                <div class="form-group">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="strong">Request Sparepart <span
                                                class="text-danger">*</span></label>
                                    </div>
                                    @foreach ($sparepartItems as $index => $item)
                                        <div class="row mb-3">
                                            <div class="col-md-7">
                                                <div class="position-relative">
                                                    <input type="text" class="form-control"
                                                        placeholder="Search sparepart..."
                                                        wire:model.live="sparepartSearch.{{ $index }}"
                                                        wire:input="searchSpareparts({{ $index }}, $event.target.value)"
                                                        autocomplete="off" id="sparepart-input-{{ $index }}">

                                                    @if (isset($sparepartResults[$index]))
                                                        @if (!empty($sparepartResults[$index]))
                                                            <div class="dropdown-menu show w-100"
                                                                style="max-height: 200px; overflow-y: auto;"
                                                                id="dropdown-{{ $index }}">
                                                                @foreach ($sparepartResults[$index] as $sparepart)
                                                                    <button type="button" class="dropdown-item"
                                                                        onmousedown="event.preventDefault();"
                                                                        wire:click="selectSparepart({{ $index }}, {{ $sparepart['id'] }})">
                                                                        {{ $sparepart['code'] }} -
                                                                        {{ $sparepart['name'] }}
                                                                    </button>
                                                                @endforeach
                                                            </div>
                                                        @elseif(isset($sparepartSearch[$index]) &&
                                                                strlen($sparepartSearch[$index]) >= 3 &&
                                                                !$this->sparepartItems[$index]['sparepart_id']
                                                        )
                                                            <div class="dropdown-menu show w-100"
                                                                id="dropdown-{{ $index }}">
                                                                <div class="dropdown-item-text text-muted">
                                                                    <i class="fas fa-exclamation-circle"></i> Sparepart
                                                                    tidak ditemukan
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="number" class="form-control" placeholder="Qty"
                                                    wire:model="sparepartItems.{{ $index }}.quantity"
                                                    min="1">
                                            </div>
                                            <div class="col-md-2">
                                                <div class="btn-group w-100">
                                                    @if ($index == 0)
                                                        <button type="button" class="btn btn-success btn-sm w-100"
                                                            wire:click="addSparepartItem" title="Add sparepart">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    @else
                                                        <button type="button" class="btn btn-danger btn-sm w-100"
                                                            wire:click="removeSparepartItem({{ $index }})"
                                                            title="Remove sparepart">
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                    @endif
                                                </div>
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
                            wire:click="submitSparepart">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Activity Modal -->
    @if ($selectedPm)
        <div wire:ignore.self class="modal fade" data-backdrop="static" id="activityModal" tabindex="-1"
            role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content shadow-lg">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title text-white">Activity List</h5>
                        <button type="button" class="close text-white" wire:click="resetActivityModal"
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
                                            role="progressbar" aria-valuenow="{{ $this->calculateProgress() }}"
                                            aria-valuemin="0" aria-valuemax="100"
                                            style="width: {{ $this->calculateProgress() }}%">
                                            {{ $this->calculateProgress() }}%
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="strong">Add a new task <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" wire:model="newTask"
                                            wire:keydown.enter="addNewTask" placeholder="Enter task name">
                                        <div class="input-group-append">
                                            <button class="btn btn-success" wire:click="addNewTask">Add</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="strong">Task List</label>
                                    <br>
                                    @forelse ($activityLists as $task)
                                        <div class="d-flex align-items-center mb-2">
                                            @if ($editingTaskId == $task['id'])
                                                <div class="flex-grow-1 d-flex align-items-center">
                                                    <input type="text" class="form-control mr-2"
                                                        wire:model="editingTaskName"
                                                        wire:keydown.enter="updateTaskName" style="max-width: 300px;">
                                                    <button class="btn btn-sm btn-pill btn-success mr-1"
                                                        wire:click="updateTaskName">Save</button>
                                                    <button class="btn btn-sm btn-secondary btn-pill"
                                                        wire:click="cancelEditTask">Cancel</button>
                                                </div>
                                            @else
                                                <div class="custom-control custom-checkbox flex-grow-1">
                                                    <input type="checkbox" class="custom-control-input"
                                                        id="task{{ $task['id'] }}"
                                                        @if ($task['is_done']) checked @endif
                                                        wire:click="toggleTask({{ $task['id'] }})">
                                                    <label class="custom-control-label"
                                                        for="task{{ $task['id'] }}">
                                                        {{ $task['task'] }}
                                                    </label>
                                                </div>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-primary"
                                                        wire:click="editTask({{ $task['id'] }})" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger"
                                                        wire:click="deleteTask({{ $task['id'] }})" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <p class="text-muted">No tasks added yet.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-pill" wire:click="resetActivityModal"
                            data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success btn-pill"
                            wire:click="confirmActivityUpdate">Update</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Reschedule Modal -->
    @if ($selectedPm)
        <div wire:ignore.self class="modal fade" data-backdrop="static" id="rescheduleModal" tabindex="-1"
            role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content shadow-lg">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white">
                            <i class="fas fa-calendar-alt"></i> Reschedule Preventive Maintenance
                        </h5>
                        <button type="button" class="close text-white" wire:click="closeRescheduleModal"
                            data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        {{-- <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle"></i>
                            Please select a new date for this preventive maintenance. The date must not be in the past.
                        </div> --}}

                        <div class="form-group">
                            <label class="strong">Current Basic Start Date</label>
                            <input type="text" class="form-control"
                                value="{{ $selectedPm->basic_start_date ? $selectedPm->basic_start_date->format('d-m-Y') : 'Not Set' }}"
                                readonly>
                        </div>

                        <div class="form-group">
                            <label class="strong">New Basic Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" wire:model="rescheduleDate"
                                min="{{ now()->format('Y-m-d') }}" required>
                            @error('rescheduleDate')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- <div class="alert alert-warning" role="alert">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Warning:</strong> This action will update the basic start date and change the status
                            to "RESCHEDULED".
                        </div> --}}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-pill" wire:click="closeRescheduleModal"
                            data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-primary btn-pill" wire:click="confirmReschedule">
                            <i class="fas fa-check"></i> Confirm Reschedule
                        </button>
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
            // Show detail modal
            Livewire.on('showDetailModal', () => {
                setTimeout(() => {
                    $('#detailModal').modal('show');
                }, 100);
            });

            // Close detail modal
            Livewire.on('closeDetailModal', () => {
                $('#detailModal').modal('hide');
            });

            // Close all modals
            Livewire.on('closeAllModals', () => {
                $('.modal').modal('hide');
            });

            // Confirm Sparepart Submit
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
                            className: "btn btn-pill",
                        },
                        confirm: {
                            text: "Yes, Submit",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-success",
                        }
                    },
                    dangerMode: false,
                }).then((result) => {
                    if (result) {
                        @this.saveSparepartReservation();
                    }
                });
            });

            // Confirm Activity Update
            Livewire.on('confirmActivityUpdate', () => {
                swal({
                    title: "Are you sure?",
                    text: "You are about to update activity list. This action cannot be undone.",
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
                            text: "Yes, Update",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-success",
                        }
                    },
                    dangerMode: false,
                }).then((result) => {
                    if (result) {
                        @this.updateActivity();
                    }
                });
            });

            // Confirm Reschedule
            Livewire.on('confirmReschedule', () => {
                swal({
                    title: "Are you sure?",
                    text: "You are about to reschedule this preventive maintenance.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "No",
                            value: false,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-secondary",
                        },
                        confirm: {
                            text: "Yes",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-primary",
                        }
                    },
                    dangerMode: false,
                }).then((result) => {
                    if (result) {
                        @this.reschedule();
                    }
                });
            });

            // Confirm Start
            Livewire.on('confirmStart', () => {
                swal({
                    title: "Are you sure?",
                    text: "You are about to start this preventive maintenance.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "No",
                            value: false,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-secondary",
                        },
                        confirm: {
                            text: "Yes, Start",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-success",
                        }
                    },
                    dangerMode: false,
                }).then((result) => {
                    if (result) {
                        @this.startMaintenance();
                    }
                });
            });

            // Confirm Stop
            Livewire.on('confirmStop', () => {
                swal({
                    title: "Are you sure?",
                    text: "You are about to stop this preventive maintenance.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "No",
                            value: false,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-secondary",
                        },
                        confirm: {
                            text: "Yes, Stop",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-danger",
                        }
                    },
                    dangerMode: true,
                }).then((result) => {
                    if (result) {
                        @this.stopMaintenance();
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

            Livewire.on('showRescheduleModal', () => {
                setTimeout(() => {
                    $('#rescheduleModal').modal('show');
                }, 100);
            });

            Livewire.on('closeRescheduleModal', () => {
                $('#rescheduleModal').modal('hide');
            });

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
                            className: "btn btn-pill",
                        },
                        confirm: {
                            text: "Yes, Submit",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-success",
                        }
                    },
                    dangerMode: false,
                }).then((result) => {
                    if (result) {
                        @this.saveSparepartReservation();
                    }
                });
            });

            Livewire.on('confirmActivityUpdate', () => {
                swal({
                    title: "Are you sure?",
                    text: "You are about to update activity list. This action cannot be undone.",
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
                            text: "Yes, Update",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-success",
                        }
                    },
                    dangerMode: false,
                }).then((result) => {
                    if (result) {
                        @this.updateActivity();
                    }
                });
            });

            Livewire.on('confirmReschedule', () => {
                swal({
                    title: "Are you sure?",
                    text: "You are about to reschedule this preventive maintenance.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "No",
                            value: false,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-secondary",
                        },
                        confirm: {
                            text: "Yes",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-primary",
                        }
                    },
                    dangerMode: false,
                }).then((result) => {
                    if (result) {
                        @this.reschedule();
                    }
                });
            });

            Livewire.on('confirmStart', () => {
                swal({
                    title: "Are you sure?",
                    text: "You are about to start this preventive maintenance.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "No",
                            value: false,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-secondary",
                        },
                        confirm: {
                            text: "Yes, Start",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-success",
                        }
                    },
                    dangerMode: false,
                }).then((result) => {
                    if (result) {
                        @this.startMaintenance();
                    }
                });
            });

            Livewire.on('confirmStop', () => {
                swal({
                    title: "Are you sure?",
                    text: "You are about to stop this preventive maintenance.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "No",
                            value: false,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-secondary",
                        },
                        confirm: {
                            text: "Yes, Stop",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-danger",
                        }
                    },
                    dangerMode: true,
                }).then((result) => {
                    if (result) {
                        @this.stopMaintenance();
                    }
                });
            });
        });

        Livewire.on('clearDropdown', (index) => {
            setTimeout(() => {
                @this.hideSparepartDropdown(index);
            }, 100);
        });
    </script>
@endpush
