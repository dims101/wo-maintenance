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
                        <div class="col-12 col-md-4 mb-2">
                            <div class="d-flex flex-wrap align-items-center">
                                <span class="mr-2">Show</span>
                                <select wire:model.live="perPage" class="form-control form-control-sm w-auto">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                <span class="ml-2">entries</span>
                            </div>
                        </div>

                        <div class="col-12 col-md-4 mb-2 text-md-center text-left">
                            @if ($this->canShowAllWorkOrderButton())
                                <button type="button" class="btn btn-primary btn-sm btn-block btn-md-inline-block"
                                    wire:click="openAllWorkOrdersModal">
                                    <i class="fas fa-list"></i> All Work Orders
                                </button>
                            @endif
                        </div>

                        <div class="col-12 col-md-4 mb-2">
                            <input type="text" wire:model.live.debounce.300ms="search"
                                class="form-control form-control-sm" placeholder="Search...">
                        </div>
                    </div>


                    <!-- Table -->
                    {{-- <div class="table-responsive">
                        <table class="table table-striped table-hover table-borderless">
                            <thead class="thead-light">
                                <tr>
                                    <th>Action</th>
                                    <th>Status</th>
                                    <th>Notification Number</th>
                                    <th>Functional Location</th>
                                    <th>Work Description</th>
                                    <th>Equipment</th>
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

                                                    @case('Need Revision')
                                                        <span
                                                            class="badge badge-primary">{{ $workOrder->status ?? 'Not Set' }}</span>
                                                    @break

                                                    @default
                                                        <span
                                                            class="badge badge-secondary">{{ $workOrder->status ?? 'Not Set' }}</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                {{ $workOrder->notification_number }}
                                            </td>
                                            <td>
                                                {{ $workOrder->equipment->functionalLocation->name }}
                                            </td>
                                            <td>
                                                {{ $workOrder->work_desc }}
                                            </td>
                                            <td>
                                                {{ $workOrder->equipment->name }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            <div class="py-4">
                                                <i class="fas fa-search fa-2x text-muted mb-3"></i>
                                                <p class="text-muted">No work orders found</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div> --}}

                    <!-- Bootstrap Tabs -->
                    <ul class="nav nav-tabs" id="assignedTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="wo-tab" data-toggle="tab" href="#woTab" role="tab">
                                <i class="fas fa-tools"></i> Work Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pm-tab" data-toggle="tab" href="#pmTab" role="tab">
                                <i class="fas fa-calendar-check"></i> Preventive Maintenance
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content" id="assignedTabsContent">
                        <!-- ==================== TAB 1: WORK ORDERS ==================== -->
                        <div class="tab-pane fade show active" id="woTab" role="tabpanel">
                            <div class="mt-3">
                                <!-- Table -->
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover table-borderless">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Action</th>
                                                <th>Status</th>
                                                <th>Notification Number</th>
                                                <th>Functional Location</th>
                                                <th>Work Description</th>
                                                <th>Equipment</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if ($workOrders && $workOrders->count() > 0)
                                                @foreach ($workOrders as $workOrder)
                                                    <tr>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                <button type="button"
                                                                    class="btn btn-link btn-lg btn-info"
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

                                                                @case('Planned')
                                                                    <span
                                                                        class="badge badge-success">{{ $workOrder->status ?? 'Not Set' }}</span>
                                                                @break

                                                                @case('ON PROGRESS')
                                                                    <span
                                                                        class="badge badge-primary">{{ $workOrder->status ?? 'Not Set' }}</span>
                                                                @break

                                                                @case('Requested to be closed')
                                                                    <span
                                                                        class="badge badge-warning">{{ $workOrder->status ?? 'Not Set' }}</span>
                                                                @break

                                                                @case('Need Revision')
                                                                    <span
                                                                        class="badge badge-info">{{ $workOrder->status ?? 'Not Set' }}</span>
                                                                @break

                                                                @default
                                                                    <span
                                                                        class="badge badge-secondary">{{ $workOrder->status ?? 'Not Set' }}</span>
                                                            @endswitch
                                                        </td>
                                                        <td>{{ $workOrder->notification_number }}</td>
                                                        <td>{{ $workOrder->equipment->functionalLocation->name ?? '-' }}
                                                        </td>
                                                        <td>{{ $workOrder->work_desc }}</td>
                                                        <td>{{ $workOrder->equipment->name ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="6" class="text-center">
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

                                {{-- debug --}}
                                {{-- {{ get_class($preventiveMaintenances) }} --}}
                                <!-- Pagination WO -->
                                @if ($workOrders->hasPages())
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted">
                                                    Showing {{ $workOrders->firstItem() }} to
                                                    {{ $workOrders->lastItem() }} of
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

                        <!-- ==================== TAB 2: PREVENTIVE MAINTENANCE ==================== -->
                        <div class="tab-pane fade" id="pmTab" role="tabpanel">
                            <div class="mt-3">
                                <!-- Table -->
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover table-borderless">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Action</th>
                                                <th>Status</th>
                                                <th>Order</th>
                                                <th>Notification Number</th>
                                                <th>Description</th>
                                                <th>Equipment</th>
                                                <th>Functional Location</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if ($preventiveMaintenances && $preventiveMaintenances->count() > 0)
                                                @foreach ($preventiveMaintenances as $pm)
                                                    <tr>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                <button type="button"
                                                                    class="btn btn-link btn-lg btn-success"
                                                                    title="View Details"
                                                                    wire:click="openDetailModalPm({{ $pm->id }})">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            @php
                                                                $badgeClass = match (
                                                                    strtoupper($pm->user_status ?? '')
                                                                ) {
                                                                    'ASSIGNED' => 'badge-info',
                                                                    'ON PROGRESS' => 'badge-primary',
                                                                    'REQUESTED TO BE CLOSED' => 'badge-warning',
                                                                    'COMPLETED' => 'badge-success',
                                                                    default => 'badge-secondary',
                                                                };
                                                            @endphp
                                                            <span class="badge {{ $badgeClass }}">
                                                                {{ $pm->user_status ?? 'Not Set' }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $pm->order }}</td>
                                                        <td>{{ $pm->notification_number ?? '-' }}</td>
                                                        <td>{{ $pm->description ?? '-' }}</td>
                                                        <td>{{ $pm->equipment ?? '-' }}</td>
                                                        <td>{{ $pm->functional_location ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="7" class="text-center">
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

                                <!-- Pagination PM -->
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
                        <button type="button" class="close text-white" data-dismiss="modal"
                            wire:click="closeModal">
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
                    <div class="modal-footer d-flex flex-wrap justify-content-end">
                        <button type="button" class="btn mb-2 btn-secondary btn-pill" data-dismiss="modal"
                            wire:click="closeModal">Close</button>
                        {{-- @if ($this->isUserPic($selectedWorkOrder->id)) --}}
                        <button type="button" class="btn mb-2 btn-info btn-pill" data-toggle="modal"
                            data-target="#sparepartModal">Sparepart reservation</button>
                        <button type="button" class="btn mb-2 btn-warning btn-pill" data-toggle="modal"
                            data-target="#progressModal" @disabled(!$isSparepartReserved && !$isBeingWorked)>Update
                            progress</button>
                        <button type="button" class="btn mb-2 btn-success btn-pill" data-toggle="modal"
                            data-target="#closeModal" @disabled(!$isSparepartReserved && !$isBeingWorked)>Request to
                            close</button>
                        {{-- @endif --}}
                        {{-- START/STOP BUTTONS - Hanya muncul jika status = Planned dan user di-assign --}}
                        @if (
                            ($selectedWorkOrder->status === 'Planned' || $selectedWorkOrder->status === 'Need Revision') &&
                                $this->isUserAssignedToTeam($selectedWorkOrder->id))
                            @if ($activeSessionUser)
                                @if ($activeSessionUser->wo_id == $selectedWorkOrder->id)
                                    {{-- Tombol STOP jika sedang running untuk WO INI --}}
                                    <button type="button" class="btn mb-2 btn-danger btn-pill"
                                        wire:click="confirmStop">
                                        <i class="fas fa-stop-circle"></i> Stop Work
                                        <span class="badge badge-light">{{ $this->getActiveDuration() }}</span>
                                    </button>
                                @else
                                    {{-- Disabled jika sedang running di WO LAIN --}}
                                    @php
                                        $activeWO = \App\Models\WorkOrder::find($activeSessionUser->wo_id);
                                    @endphp
                                    <button type="button" class="btn mb-2 btn-warning btn-pill" disabled
                                        title="Currently working on {{ $activeWO->notification_number }}">
                                        <i class="fas fa-exclamation-triangle"></i> Active on
                                        {{ $activeWO->notification_number }}
                                    </button>
                                @endif
                            @else
                                {{-- Tombol START jika tidak ada session aktif sama sekali --}}
                                <button type="button" class="btn mb-2 btn-success btn-pill"
                                    wire:click="confirmStart({{ $selectedWorkOrder->id }})">
                                    <i class="fas fa-play-circle"></i> Start Work
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Detail Modal PM -->
    @if ($selectedPm)
        <div wire:ignore.self class="modal fade" id="detailModalPm" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title">Preventive Maintenance - {{ $selectedPm->order }}</h5>
                        <button type="button" class="close text-white" data-dismiss="modal"
                            wire:click="closeModalPm">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Tabs for PM -->
                        <ul class="nav nav-tabs" id="pmDetailTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#pmInfoTab">
                                    <i class="fas fa-info-circle"></i> Info
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#pmActivityTab">
                                    <i class="fas fa-tasks"></i> Activity List
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#pmSparepartTab">
                                    <i class="fas fa-tools"></i> Sparepart
                                </a>
                            </li>
                            {{-- manhours here --}}
                            {{-- <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#pmManhourTab">
                                    <i class="fas fa-clock"></i> Manhour
                                </a>
                            </li> --}}
                        </ul>

                        <div class="tab-content mt-3">
                            <!-- Tab 1: Info -->
                            <div class="tab-pane fade show active" id="pmInfoTab">
                                <div class="row">
                                    <!-- Left Column -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="strong">Order</label>
                                            <input type="text" class="form-control"
                                                value="{{ $selectedPm->order }}" readonly>
                                        </div>

                                        <div class="form-group">
                                            <label class="strong">Notification Number</label>
                                            <input type="text" class="form-control"
                                                value="{{ $selectedPm->notification_number ?? '-' }}" readonly>
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
                                            <label class="strong">Equipment</label>
                                            <input type="text" class="form-control"
                                                value="{{ $selectedPm->equipment ?? '-' }}" readonly>
                                        </div>
                                    </div>

                                    <!-- Right Column -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="strong">Functional Location</label>
                                            <input type="text" class="form-control"
                                                value="{{ $selectedPm->functional_location ?? '-' }}" readonly>
                                        </div>

                                        <div class="form-group">
                                            <label class="strong">System Status</label>
                                            <input type="text" class="form-control"
                                                value="{{ $selectedPm->system_status ?? '-' }}" readonly>
                                        </div>

                                        <div class="form-group">
                                            <label class="strong">User Status</label>
                                            <input type="text" class="form-control"
                                                value="{{ $selectedPm->user_status ?? '-' }}" readonly>
                                        </div>

                                        <div class="form-group">
                                            <label class="strong">Basic Start Date</label>
                                            <input type="text" class="form-control"
                                                value="{{ $selectedPm->basic_start_date ? $selectedPm->basic_start_date->format('d-m-Y') : '-' }}"
                                                readonly>
                                        </div>

                                        <!-- Assigned Team -->
                                        <div class="form-group">
                                            <label class="strong">Assigned Team</label>
                                            <div class="card">
                                                <div class="card-body">
                                                    @foreach ($selectedPm->teamAssignments as $assignment)
                                                        <div
                                                            class="d-flex justify-content-between align-items-center mb-2">
                                                            <div>
                                                                <i class="fas fa-user"></i>
                                                                {{ $assignment->user->name }}
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
                                    </div>
                                </div>
                            </div>

                            <!-- Tab 2: Activity List -->
                            <div class="tab-pane fade" id="pmActivityTab">
                                <div class="form-group">
                                    <label class="strong">Progress</label>
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                                            role="progressbar" aria-valuenow="{{ $this->calculateProgressPm() }}"
                                            aria-valuemin="0" aria-valuemax="100"
                                            style="width: {{ $this->calculateProgressPm() }}%">
                                            {{ $this->calculateProgressPm() }}%
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="strong">Add a new task</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" wire:model="newTask"
                                            wire:keydown.enter="addNewTaskPm" placeholder="Enter task name">
                                        <div class="input-group-append">
                                            <button class="btn btn-success" wire:click="addNewTaskPm">Add</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="strong">Task List</label>
                                    @forelse ($activityLists as $task)
                                        <div class="d-flex align-items-center mb-2">
                                            @if ($editingTaskId == $task['id'])
                                                <div class="flex-grow-1 d-flex align-items-center">
                                                    <input type="text" class="form-control mr-2"
                                                        wire:model="editingTaskName"
                                                        wire:keydown.enter="updateTaskNamePm"
                                                        style="max-width: 300px;">
                                                    <button class="btn btn-sm btn-pill btn-success mr-1"
                                                        wire:click="updateTaskNamePm">Save</button>
                                                    <button class="btn btn-sm btn-secondary btn-pill"
                                                        wire:click="cancelEditTaskPm">Cancel</button>
                                                </div>
                                            @else
                                                <div class="custom-control custom-checkbox flex-grow-1">
                                                    <input type="checkbox" class="custom-control-input"
                                                        id="taskPm{{ $task['id'] }}" @disabled($task['planner_group_id'] != auth()->user()->planner_group_id)
                                                        wire:model.live="tempActivityStates.{{ $task['id'] }}">
                                                    <label class="custom-control-label"
                                                        for="taskPm{{ $task['id'] }}">
                                                        {{ $task['task'] }}
                                                    </label>
                                                </div>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-primary"
                                                        wire:click="editTaskPm({{ $task['id'] }})" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger"
                                                        wire:click="deleteTaskPm({{ $task['id'] }})"
                                                        title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <p class="text-muted">No tasks added yet.</p>
                                    @endforelse
                                </div>
                                <div class="mt-3">
                                    <button type="button" class="btn btn-success btn-pill"
                                        wire:click="confirmProgressPm">
                                        <i class="fas fa-check"></i> Update Progress
                                    </button>
                                </div>
                            </div>

                            <!-- Tab 3: Sparepart (Similar to WO) -->
                            <div class="tab-pane fade" id="pmSparepartTab">
                                <div class="form-group">
                                    <label class="strong">Sparepart Reservation</label>
                                    @foreach ($sparepartItems as $index => $item)
                                        <div class="row mb-2">
                                            <div class="col-md-7">
                                                <input type="text" class="form-control"
                                                    placeholder="Sparepart name"
                                                    wire:model="sparepartItems.{{ $index }}.requested_sparepart"
                                                    @if (isset($item['is_completed']) && $item['is_completed']) readonly @endif>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="number" class="form-control" placeholder="Qty"
                                                    wire:model="sparepartItems.{{ $index }}.quantity"
                                                    @if (isset($item['is_completed']) && $item['is_completed']) readonly @endif min="1">
                                            </div>
                                            <div class="col-md-2">
                                                <div class="btn-group w-100">
                                                    @if ($index == 0 && (!isset($item['is_completed']) || !$item['is_completed']))
                                                        <button type="button" class="btn btn-success btn-sm w-100"
                                                            wire:click="addSparepartItem" title="Add sparepart">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    @elseif(!isset($item['is_completed']) || !$item['is_completed'])
                                                        <button type="button" class="btn btn-danger btn-sm w-100"
                                                            wire:click="removeSparepartItem({{ $index }})"
                                                            title="Remove sparepart">
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                    @endif

                                                    @if (isset($item['is_completed']) && $item['is_completed'])
                                                        <span class="badge badge-success w-100 p-2">
                                                            <i class="fas fa-check"></i> Completed
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    <button type="button" class="btn btn-success btn-sm mt-2"
                                        wire:click="submitSparepartPm">
                                        <i class="fas fa-save"></i> Submit Sparepart
                                    </button>
                                </div>
                            </div>

                            <!-- Tab 4: Manhour History -->
                            <div class="tab-pane fade" id="pmManhourTab">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Start</th>
                                                <th>Stop</th>
                                                <th>Duration (min)</th>
                                                <th>Shift</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($selectedPm->actualManhours as $manhour)
                                                <tr>
                                                    <td>{{ $manhour->user->name ?? '-' }}</td>
                                                    <td>{{ $manhour->start_job ? $manhour->start_job->format('d-m-Y H:i') : '-' }}
                                                    </td>
                                                    <td>{{ $manhour->stop_job ? $manhour->stop_job->format('d-m-Y H:i') : '-' }}
                                                    </td>
                                                    <td>{{ $manhour->actual_time ?? '-' }}</td>
                                                    <td>
                                                        <span class="badge badge-info">Shift
                                                            {{ $manhour->shift ?? '-' }}</span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">No manhour
                                                        recorded yet</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-pill" data-dismiss="modal"
                            wire:click="closeModalPm">
                            Close
                        </button>

                        <!-- Active Session Indicator here-->
                        {{-- @if ($activeSessionUser && $activeSessionUser->pm_id == $selectedPm->id)
                            <span class="badge badge-success mr-2">
                                <i class="fas fa-play"></i> Active Session:
                                {{ \Carbon\Carbon::parse($startTime)->diffForHumans(null, true) }}
                            </span>
                        @endif --}}

                        <!-- Start/Stop Work Buttons -->
                        @if (!$activeSessionUser)
                            <button type="button" class="btn btn-success btn-pill" wire:click="confirmStartPm">
                                <i class="fas fa-play"></i> Start Work
                            </button>
                        @elseif($activeSessionUser->pm_id == $selectedPm->id)
                            <button type="button" class="btn btn-danger btn-pill" wire:click="confirmStopPm">
                                <i class="fas fa-stop"></i> Stop Work
                            </button>
                        @endif

                        <!-- Request Close Button -->
                        @if ($selectedPm->user_status != 'REQUESTED TO BE CLOSED' && $selectedPm->user_status != 'COMPLETED')
                            <button type="button" class="btn btn-warning btn-pill" wire:click="confirmClosePm">
                                <i class="fas fa-check-circle"></i> Request Close
                            </button>
                        @endif
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
                                <div class="form-group">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="strong">Request Sparepart <span
                                                class="text-danger">*</span></label>
                                    </div>
                                    @foreach ($sparepartItems as $index => $item)
                                        <div class="row mb-3 align-items-center">
                                            <div class="col-1 pr-1 d-flex align-items-center justify-content-center">
                                                @if (isset($sparepartItems[$index]['is_completed']) && $sparepartItems[$index]['is_completed'])
                                                    <i class="fas fa-check-circle text-success"
                                                        style="font-size: 1.2rem;" title="Completed"></i>
                                                @else
                                                    <i class="fas fa-circle text-muted"
                                                        style="font-size: 1.2rem; opacity: 0.2;"></i>
                                                @endif
                                            </div>

                                            <div class="col-6 pr-1">
                                                <input type="text" class="form-control form-control-sm"
                                                    placeholder="Enter sparepart name..."
                                                    wire:model="sparepartItems.{{ $index }}.requested_sparepart"
                                                    @if (isset($sparepartItems[$index]['is_completed']) && $sparepartItems[$index]['is_completed']) readonly style="background-color:#e9ecef; cursor:not-allowed;" @endif>
                                            </div>

                                            <div class="col-3 pr-1">
                                                <input type="number" class="form-control form-control-sm"
                                                    placeholder="Qty"
                                                    wire:model="sparepartItems.{{ $index }}.quantity"
                                                    min="1"
                                                    @if (isset($sparepartItems[$index]['is_completed']) && $sparepartItems[$index]['is_completed']) readonly style="background-color:#e9ecef; cursor:not-allowed;" @endif>
                                            </div>

                                            <div class="col-2">
                                                @if ($index == 0)
                                                    <button type="button" class="btn btn-success btn-sm btn-block"
                                                        wire:click="addSparepartItem">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-danger btn-sm btn-block"
                                                        wire:click="removeSparepartItem({{ $index }})"
                                                        @if (isset($sparepartItems[$index]['is_completed']) && $sparepartItems[$index]['is_completed']) disabled @endif>
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
                            wire:click="submitSparepart">Submit</button>
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
                                    @php
                                        // Kelompokkan task berdasarkan planner_group_id
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
                                                    @if ($editingTaskId == $task['id'])
                                                        <div class="flex-grow-1 d-flex align-items-center">
                                                            <input type="text" class="form-control mr-2"
                                                                wire:model="editingTaskName"
                                                                wire:keydown.enter="updateTaskName"
                                                                style="max-width: 300px;">
                                                            <button class="btn btn-sm btn-pill btn-success mr-1"
                                                                wire:click="updateTaskName">Save</button>
                                                            <button class="btn btn-sm btn-secondary btn-pill"
                                                                wire:click="cancelEditTask">Cancel</button>
                                                        </div>
                                                    @else
                                                        <div class="custom-control custom-checkbox flex-grow-1">
                                                            <input type="checkbox" class="custom-control-input"
                                                                id="task{{ $task['id'] }}"
                                                                @disabled($task['planner_group_id'] != auth()->user()->planner_group_id)
                                                                wire:model.live="tempActivityStates.{{ $task['id'] }}">
                                                            <label class="custom-control-label"
                                                                for="task{{ $task['id'] }}">
                                                                {{ $task['task'] }}
                                                            </label>
                                                        </div>
                                                        @if (auth()->user()->planner_group_id == $task['planner_group_id'])
                                                            <div class="btn-group" role="group">
                                                                <button class="btn btn-sm btn-outline-primary"
                                                                    wire:click="editTask({{ $task['id'] }})"
                                                                    title="Edit">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-outline-danger"
                                                                    wire:click="deleteTask({{ $task['id'] }})"
                                                                    title="Delete">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        @endif
                                                    @endif
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
                            data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success btn-pill"
                            wire:click="confirmProgress">Update</button>
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
                        <button type="button" class="close text-white" wire:click="resetCloseModal"
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
                        <div class="modal-footer d-flex flex-wrap justify-content-end">
                            <button type="button" class="btn mb-2 btn-secondary btn-pill"
                                wire:click="resetCloseModal" data-dismiss="modal">Cancel</button>
                            <button type="button"
                                wire:click="confirmChange({{ $selectedWorkOrder->planner_group_id }})"
                                class="btn mb-2 btn-warning btn-pill">Planner change</button>
                            <button type="button" wire:click="confirmClose"
                                class="btn mb-2 btn-success btn-pill">Request
                                to close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal All Work Orders --}}
    @if ($showAllWorkOrdersModal)
        <div wire:ignore.self class="modal fade show" id="allWorkOrdersModal" tabindex="-1"
            style="display: block; background: rgba(0,0,0,0.5);" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white">Available Work Orders</h5>
                        <button type="button" class="close text-white" wire:click="closeAllWorkOrdersModal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @if (count($availableWorkOrders) > 0)
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Notification Number</th>
                                            <th>Equipment</th>
                                            <th>Urgent Level</th>
                                            <th>Department</th>
                                            <th>PIC</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($availableWorkOrders as $wo)
                                            <tr>
                                                <td>{{ $wo['notification_number'] }}</td>
                                                <td>{{ $wo['equipment'] }}</td>
                                                <td>
                                                    <span
                                                        class="badge badge-{{ $wo['urgent_level'] == 'High' ? 'danger' : ($wo['urgent_level'] == 'Medium' ? 'warning' : 'info') }}">
                                                        {{ $wo['urgent_level'] }}
                                                    </span>
                                                </td>
                                                <td>{{ $wo['department'] }}</td>
                                                <td>{{ $wo['pic_name'] }}</td>
                                                <td>
                                                    <button type="button" class="btn btn-pill btn-sm btn-success"
                                                        wire:click="confirmAssignSelf({{ $wo['approval_id'] }})">
                                                        <i class="fas fa-user-plus"></i> Assign Me
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No available work orders at the moment.</p>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-pill"
                            wire:click="closeAllWorkOrdersModal">Close</button>
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

            // Confirm Assign Self
            Livewire.on('confirmAssignSelf', () => {
                swal({
                    title: "Assign Yourself?",
                    text: "You will be assigned as a team member to this work order.",
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
                            text: "Yes, Assign Me",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-success",
                        }
                    },
                }).then((result) => {
                    if (result) {
                        @this.assignSelfToWorkOrder();
                    }
                });
            });
            // Confirm Start Manhour
            Livewire.on('confirmStartManhour', () => {
                swal({
                    title: "Start Work Session?",
                    text: "This will record your work start time for this work order.",
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
                            text: "Yes, Start",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-success",
                        }
                    },
                }).then((result) => {
                    if (result) {
                        @this.startManhour();
                    }
                });
            });

            // Confirm Stop Manhour
            Livewire.on('confirmStopManhour', () => {
                swal({
                    title: "Stop Work Session?",
                    text: "This will record your work end time and calculate total duration.",
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
                        @this.stopManhour();
                    }
                });
            });

            // Auto-update durasi setiap 1 menit
            setInterval(() => {
                if (@this.activeSessionUser) {
                    @this.$refresh();
                }
            }, 60000); // Update setiap 60 detik
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

            Livewire.on('unfinished', () => {
                swal({
                    title: "Error!",
                    text: "Please finish all tasks before closing.",
                    icon: "error",
                    button: "OK",
                });
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
                            className: "btn btn-pill btn-success",
                        },
                        confirm: {
                            text: "Yes, Approve",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-success",
                        }
                    },
                    dangerMode: false,
                }).then((result) => {
                    if (result) {
                        @this.closeWorkOrder();
                    }
                });
            });

            Livewire.on('confirmChange', () => {
                swal({
                    title: "Are you sure?",
                    text: "You are about to change this planner group. This action cannot be undone.",
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
                            text: "Yes, Change Planner Group",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-success",
                        }
                    },
                    dangerMode: false,
                }).then((result) => {
                    if (result) {
                        @this.changePlannerGroup();
                    }
                });
            });

            // Prevent accidental modal closing
            $('#detailModal').on('hide.bs.modal', function(e) {
                if (e.target !== this) return false;
            });

            // ==================== PM EVENT LISTENERS ====================

            Livewire.on('showDetailModalPm', () => {
                setTimeout(() => {
                    $('#detailModalPm').modal('show');
                }, 100);
            });

            Livewire.on('confirmStartManhourPm', () => {
                swal({
                    title: "Are you sure?",
                    text: "You are about to start work on this PM.",
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
                        @this.startManhourPm();
                    }
                });
            });

            Livewire.on('confirmStopManhourPm', () => {
                swal({
                    title: "Are you sure?",
                    text: "You are about to stop work on this PM.",
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
                        @this.stopManhourPm();
                    }
                });
            });

            Livewire.on('confirmSparepartSubmitPm', () => {
                swal({
                    title: "Are you sure?",
                    text: "You are about to submit sparepart reservation.",
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
                        @this.saveSparepartReservationPm();
                    }
                });
            });

            Livewire.on('confirmClosePm', () => {
                swal({
                    title: "Are you sure?",
                    text: "You are about to request close for this PM. This action cannot be undone.",
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
                            text: "Yes, Request Close",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-warning",
                        }
                    },
                    dangerMode: false,
                }).then((result) => {
                    if (result) {
                        @this.closeWorkOrderPm();
                    }
                });
            });
            // ==================== CONFIRM PROGRESS PM ====================
            Livewire.on('confirmProgressPm', () => {
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
                        @this.updateProgressPm();
                    }
                });
            });
        });

        document.addEventListener('livewire:navigated', function() {
            // Duplicate event listeners for SPA navigation
            // Confirm Assign Self
            Livewire.on('confirmAssignSelf', () => {
                swal({
                    title: "Assign Yourself?",
                    text: "You will be assigned as a team member to this work order.",
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
                            text: "Yes, Assign Me",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-success",
                        }
                    },
                }).then((result) => {
                    if (result) {
                        @this.assignSelfToWorkOrder();
                    }
                });
            });
            // Confirm Start Manhour
            Livewire.on('confirmStartManhour', () => {
                swal({
                    title: "Start Work Session?",
                    text: "This will record your work start time for this work order.",
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
                            text: "Yes, Start",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-success",
                        }
                    },
                }).then((result) => {
                    if (result) {
                        @this.startManhour();
                    }
                });
            });

            // Confirm Stop Manhour
            Livewire.on('confirmStopManhour', () => {
                swal({
                    title: "Stop Work Session?",
                    text: "This will record your work end time and calculate total duration.",
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
                        @this.stopManhour();
                    }
                });
            });

            // Auto-update durasi setiap 1 menit
            setInterval(() => {
                if (@this.activeSessionUser) {
                    @this.$refresh();
                }
            }, 60000); // Update setiap 60 detik
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
                        @this.updateProgress();
                    }
                });
            });

            Livewire.on('confirmChange', () => {
                swal({
                    title: "Are you sure?",
                    text: "You are about to change this planner group. This action cannot be undone.",
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
                            text: "Yes, Change Planner Group",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-success",
                        }
                    },
                    dangerMode: false,
                }).then((result) => {
                    if (result) {
                        @this.changePlannerGroup();
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

            Livewire.on('unfinished', () => {
                swal({
                    title: "Error!",
                    text: "Please finish all tasks before closing.",
                    icon: "error",
                    button: "OK",
                });
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
                            closeModal: true,
                            className: "btn btn-pill",
                        },
                        confirm: {
                            text: "Yes, close",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-success",
                        }
                    },
                    dangerMode: false,
                }).then((result) => {
                    if (result) @this.closeWorkOrder();
                });
            });

            // ==================== PM EVENT LISTENERS ====================

            Livewire.on('showDetailModalPm', () => {
                setTimeout(() => {
                    $('#detailModalPm').modal('show');
                }, 100);
            });

            Livewire.on('confirmStartManhourPm', () => {
                swal({
                    title: "Are you sure?",
                    text: "You are about to start work on this PM.",
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
                        @this.startManhourPm();
                    }
                });
            });

            Livewire.on('confirmStopManhourPm', () => {
                swal({
                    title: "Are you sure?",
                    text: "You are about to stop work on this PM.",
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
                        @this.stopManhourPm();
                    }
                });
            });

            Livewire.on('confirmSparepartSubmitPm', () => {
                swal({
                    title: "Are you sure?",
                    text: "You are about to submit sparepart reservation.",
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
                        @this.saveSparepartReservationPm();
                    }
                });
            });

            Livewire.on('confirmClosePm', () => {
                swal({
                    title: "Are you sure?",
                    text: "You are about to request close for this PM. This action cannot be undone.",
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
                            text: "Yes, Request Close",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-warning",
                        }
                    },
                    dangerMode: false,
                }).then((result) => {
                    if (result) {
                        @this.closeWorkOrderPm();
                    }
                });
            });
        });

        // ==================== CONFIRM PROGRESS PM ====================
        Livewire.on('confirmProgressPm', () => {
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
                    @this.updateProgressPm();
                }
            });
        });
    </script>
@endpush
