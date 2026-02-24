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

                                <!-- Assigned Team Section / hide this-->
                                @if ($this->getAssignedTeam()->count() > 0)
                                    <div class="form-group">
                                        <label class="strong">Assigned Team</label>
                                        <div class="card">
                                            <div class="card-body">
                                                @foreach ($this->getAssignedTeam() as $assignment)
                                                    <div
                                                        class="d-flex justify-content-between align-items-center mb-2">
                                                        <div>
                                                            <i class="fas fa-user"></i>
                                                            {{ $assignment->user->name }}
                                                            <small
                                                                class="text-muted">({{ $assignment->user->nup }})</small>
                                                        </div>
                                                        @if ($assignment->is_pic)
                                                            <span class="badge badge-primary">
                                                                <i class="fas fa-star"></i> PIC
                                                            </span>
                                                        @else
                                                            <span class="badge badge-secondary">
                                                                <i class="fas fa-users"></i> Team
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif

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

                        @if (!$this->isCompleted())
                            <button type="button" class="btn btn-warning btn-pill" data-toggle="modal"
                                data-target="#activityModal">
                                <i class="fas fa-tasks"></i> Activity List
                            </button>
                        @endif

                        @if ($this->canAssignTeam())
                            <button type="button" class="btn btn-success btn-pill" wire:click="openAssignModal">
                                <i class="fas fa-users"></i> Assign Team
                            </button>
                        @endif

                        @if ($this->canReschedule())
                            <button type="button" class="btn btn-primary btn-pill" data-toggle="modal"
                                data-target="#rescheduleModal">
                                <i class="fas fa-calendar-alt"></i> Reschedule
                            </button>
                        @endif

                        @if ($this->canClosePm())
                            <button type="button" class="btn btn-danger btn-pill" wire:click="confirmClosePm">
                                <i class="fas fa-check-circle"></i> Close PM
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Activity Modal -->
    <!-- Activity Modal -->
    @if ($selectedPm)
        <div wire:ignore.self class="modal fade" data-backdrop="static" id="activityModal" tabindex="-1"
            role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content shadow-lg">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title text-white">
                            <i class="fas fa-tasks"></i> Activity List (Read-Only)
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">
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
                                    <label class="strong">Task List</label>
                                    @forelse ($activityLists as $task)
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="custom-control custom-checkbox flex-grow-1">
                                                <input type="checkbox" class="custom-control-input"
                                                    id="task{{ $task['id'] }}"
                                                    @if ($task['is_done']) checked @endif disabled>
                                                <label class="custom-control-label" for="task{{ $task['id'] }}">
                                                    {{ $task['task'] }}
                                                </label>
                                            </div>
                                            @if ($task['is_done'])
                                                <span class="badge badge-success ml-2">
                                                    <i class="fas fa-check"></i> Done
                                                </span>
                                            @else
                                                <span class="badge badge-secondary ml-2">
                                                    <i class="fas fa-clock"></i> Pending
                                                </span>
                                            @endif
                                        </div>
                                    @empty
                                        <p class="text-muted text-center">
                                            <i class="fas fa-inbox"></i> No tasks added yet.
                                        </p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-pill" data-dismiss="modal">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Assign Team Modal -->
    @if ($selectedPm)
        <div wire:ignore.self class="modal fade" data-backdrop="static" id="assignTeamModal" tabindex="-1"
            role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content shadow-lg">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-white">
                            <i class="fas fa-users"></i> Assign Team to PM
                        </h5>
                        <button type="button" class="close text-white" wire:click="closeAssignModal"
                            data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <!-- PIC -->
                                <div class="form-group">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="strong">PIC <span class="text-danger">*</span></label>
                                        <small class="text-muted">{{ count($users) }} available</small>
                                    </div>
                                    <select class="custom-select" wire:model.live="selectedPic">
                                        <option value="">Select PIC</option>
                                        @forelse ($users as $user)
                                            <option value="{{ $user['id'] }}">
                                                {{ $user['name'] }} ({{ $user['hours_left'] }} hours left)
                                            </option>
                                        @empty
                                            <option value="" disabled>No users available</option>
                                        @endforelse
                                    </select>
                                    <small class="text-muted">
                                        PIC will lead the maintenance team
                                    </small>
                                </div>

                                <!-- Team Members -->
                                <div class="form-group">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="strong">Team Members <span class="text-danger">*</span></label>
                                    </div>

                                    @foreach ($teamMembers as $index => $member)
                                        <div class="input-group mb-2">
                                            <select class="custom-select"
                                                wire:model="teamMembers.{{ $index }}">
                                                <option value="">Select Team Member {{ $index + 1 }}</option>
                                                @foreach ($users as $user)
                                                    @php
                                                        // Check if user already selected as PIC or in other team member slots
                                                        $alreadyChosen =
                                                            $selectedPic == $user['id'] ||
                                                            collect($teamMembers)
                                                                ->except($index)
                                                                ->contains($user['id']);
                                                    @endphp

                                                    @if (!$alreadyChosen)
                                                        <option value="{{ $user['id'] }}">
                                                            {{ $user['name'] }} ({{ $user['hours_left'] }} hours left)
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>

                                            <div class="input-group-append">
                                                @if ($index == 0)
                                                    @if (count($teamMembers) < count($users) - 1)
                                                        <button type="button" class="btn btn-success btn-sm"
                                                            wire:click="addTeamMember"
                                                            title="Add another team member">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    @endif
                                                @else
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        wire:click="removeTeamMember({{ $index }})"
                                                        title="Remove this team member">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <!-- Duration -->
                                <div class="form-group mt-3">
                                    <label class="strong">Duration (Hours) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control"
                                        wire:model.live.debounce.500ms="duration"
                                        placeholder="Enter duration in hours" min="1" step="1">
                                    @error('duration')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                @if (count($users) == 0)
                                    <div class="alert alert-warning" role="alert">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        No available users. All maintenance staff are either at manhour limit or have
                                        active assignments.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-pill" wire:click="closeAssignModal"
                            data-dismiss="modal">
                            Cancel
                        </button>
                        <button type="button" class="btn btn-success btn-pill" wire:click="confirmAssignTeam">
                            <i class="fas fa-check"></i> Assign Team
                        </button>
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
                            <i class="fas fa-calendar-alt"></i> Reschedule PM
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
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

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-pill" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-primary btn-pill" wire:click="reschedule">
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

            Livewire.on('showAlert', (data) => {
                data = data[0];
                swal({
                    title: data.title,
                    text: data.message,
                    icon: data.icon,
                    button: "OK",
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
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-secondary",
                        },
                        confirm: {
                            text: "Yes, Reschedule",
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

            // Prevent accidental modal closing
            $('#detailModal').on('hide.bs.modal', function(e) {
                if (e.target !== this) return false;
            });

            Livewire.on('showAssignModal', () => {
                setTimeout(() => {
                    $('#assignTeamModal').modal('show');
                }, 100);
            });

            Livewire.on('closeAssignModal', () => {
                $('#assignTeamModal').modal('hide');
            });

            Livewire.on('confirmAssignTeam', () => {
                swal({
                    title: "Are you sure?",
                    text: "You are about to assign team to this preventive maintenance.",
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
                            text: "Yes, Assign",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-success",
                        }
                    },
                    dangerMode: false,
                }).then((result) => {
                    if (result) {
                        @this.assignTeamToPm();
                    }
                });
            });

            Livewire.on('confirmClosePm', () => {
                swal({
                    title: "Are you sure?",
                    text: "You are about to close this preventive maintenance. This action cannot be undone.",
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
                            text: "Yes, Close PM",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-danger",
                        }
                    },
                    dangerMode: true,
                }).then((result) => {
                    if (result) {
                        @this.closePm();
                    }
                });
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

            Livewire.on('showAlert', (data) => {
                data = data[0];
                swal({
                    title: data.title,
                    text: data.message,
                    icon: data.icon,
                    button: "OK",
                });
            });

            Livewire.on('confirmReschedule', () => {
                swal({
                    title: "Are you sure?",
                    text: "You are about to reschedule this preventive maintenance.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-secondary",
                        },
                        confirm: {
                            text: "Yes, Reschedule",
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

            Livewire.on('showAssignModal', () => {
                setTimeout(() => {
                    $('#assignTeamModal').modal('show');
                }, 100);
            });

            Livewire.on('closeAssignModal', () => {
                $('#assignTeamModal').modal('hide');
            });

            Livewire.on('confirmAssignTeam', () => {
                swal({
                    title: "Are you sure?",
                    text: "You are about to assign team to this preventive maintenance.",
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
                            text: "Yes, Assign",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-success",
                        }
                    },
                    dangerMode: false,
                }).then((result) => {
                    if (result) {
                        @this.assignTeamToPm();
                    }
                });
            });

            Livewire.on('confirmClosePm', () => {
                swal({
                    title: "Are you sure?",
                    text: "You are about to close this preventive maintenance. This action cannot be undone.",
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
                            text: "Yes, Close PM",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-danger",
                        }
                    },
                    dangerMode: true,
                }).then((result) => {
                    if (result) {
                        @this.closePm();
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
