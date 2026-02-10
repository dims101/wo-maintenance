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
                            $selectedWorkOrder->status == 'Waiting for SPV Approval' &&
                                auth()->user()->role_id == 3 &&
                                auth()->user()->dept_id != 1)
                            <button type="button" class="btn btn-danger btn-pill" data-toggle="modal"
                                data-target="#popupModal"
                                wire:click='openPopupModal("reject","spvUser")'>Reject</button>
                            <button type="button" class="btn btn-success btn-pill" data-toggle="modal"
                                data-target="#popupModal"
                                wire:click='openPopupModal("approve","spvUser")'>Approve</button>
                        @elseif (
                            $selectedWorkOrder->status == 'Waiting for Maintenance Approval' &&
                                auth()->user()->role_id == 3 &&
                                auth()->user()->dept_id == 1)
                            <button type="button" class="btn btn-info btn-pill" data-toggle="modal"
                                data-target="#popupModal"
                                wire:click='openPopupModal("receive","spvMaintenance")'>Receive</button>
                            <button type="button" class="btn btn-danger btn-pill" data-toggle="modal"
                                data-target="#popupModal"
                                wire:click='openPopupModal("reject","spvMaintenance")'>Reject</button>
                            <button class="btn btn-success btn-pill" data-toggle="modal"
                                data-target="#approvalMaintenance">Approve</button>
                        @elseif (
                            $selectedWorkOrder->status == 'Received by Maintenance' &&
                                auth()->user()->role_id == 3 &&
                                auth()->user()->dept_id == 1)
                            <button type="button" class="btn btn-danger btn-pill" data-toggle="modal"
                                data-target="#popupModal"
                                wire:click='openPopupModal("reject","spvMaintenance")'>Reject</button>
                            <button class="btn btn-success btn-pill" data-toggle="modal"
                                data-target="#approvalMaintenance">Approve</button>
                        @elseif(
                            $selectedWorkOrder->status == 'Requested to change planner group' &&
                                auth()->user()->role_id == 3 &&
                                auth()->user()->dept_id == 1)
                            <button type="button" class="btn btn-danger btn-pill" data-toggle="modal"
                                data-target="#popupModal"
                                wire:click='openPopupModal("rejectChange","spvMaintenance")'>Reject</button>
                            <button class="btn btn-success btn-pill" data-toggle="modal"
                                data-target="#approvalMaintenance">Approve</button>
                        @elseif (
                            $selectedWorkOrder->status == 'Requested to be closed' &&
                                auth()->user()->role_id == 3 &&
                                auth()->user()->dept_id != 1)
                            <button type="button" class="btn btn-primary btn-pill" data-toggle="modal"
                                data-target="#popupModal"
                                wire:click='openPopupModal("needRevision","spvMaintenance")'>Need Revision</button>
                            <button class="btn btn-success btn-pill" wire:click='confirmApproveClose'>Approve to
                                close</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
    {{-- Approve Modal --}}
    @if ($selectedWorkOrder)
        <div wire:ignore.self class="modal fade" data-backdrop="static" id="approvalMaintenance" tabindex="-1"
            role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content shadow-lg">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-white">Assign Team Member</h5>
                        <button type="button" class="close text-white" wire:click="resetMaintenanceApprovalForm"
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
                                    </div>
                                    <select class="custom-select" wire:model.live="selectedPic">
                                        <option value="">Select PIC</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Team Members -->
                                <div class="form-group">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="strong">Team Members <span class="text-danger">*</span></label>
                                    </div>

                                    @foreach ($teamMembers as $index => $member)
                                        <div class="input-group mb-2">
                                            <select class="custom-select"
                                                wire:model="teamMembers.{{ $index }}"
                                                wire:key="team-member-{{ $index }}"
                                                @disabled($selectedPic == '')>
                                                <option value="">Select Team Member {{ $index + 1 }}</option>

                                                {{-- Pastikan nilai yang sudah dipilih tetap muncul --}}
                                                @if ($member && $member != $selectedPic)
                                                    @php $current = $users->firstWhere('id', $member); @endphp
                                                    @if ($current)
                                                        <option value="{{ $current->id }}">{{ $current->name }}
                                                        </option>
                                                    @endif
                                                @endif
                                                {{-- Render user yang eligible --}}
                                                @foreach ($users as $user)
                                                    @php
                                                        // Cek apakah user sudah ada di tim (selain slot ini sendiri)
                                                        $alreadyChosen = collect($teamMembers)
                                                            ->except($index) // abaikan slot ini sendiri
                                                            ->contains($user->id);
                                                    @endphp
                                                    @if ($user->id != $selectedPic && !$alreadyChosen)
                                                        <option value="{{ $user->id }}">{{ $user->name }}
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

                                    <small class="text-muted">Jika pic atau team member tidak ditemukan <a
                                            href="{{ route('register.team') }}" target="_blank"
                                            class="badge btn-outline-primary text-decoration-none">tambahkan
                                            team</a></small>
                                </div>

                                <!-- Order Type -->
                                <div class="form-group">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="strong">Order Type <span class="text-danger">*</span></label>
                                    </div>
                                    <select class="custom-select" wire:model.live="selectedOrderType"
                                        {{ $isPgComplete == true ? 'disabled' : '' }}>
                                        <option value="">Select Order Type</option>
                                        @foreach ($orderTypes as $orderType)
                                            <option value="{{ $orderType->id }}">
                                                {{ $orderType->type ?? $orderType->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Maintenance Activity Type -->
                                <div class="form-group">
                                    <label class="strong">Maintenance Activity Type <span
                                            class="text-danger">*</span></label>
                                    <select class="custom-select" wire:model="selectedMat"
                                        {{ !$selectedOrderType ? 'disabled' : '' }}
                                        {{ $isPgComplete == true ? 'disabled' : '' }}>
                                        <option value="">Select MAT</option>
                                        @foreach ($mats as $mat)
                                            <option value="{{ $mat->id }}">{{ $mat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <!-- Start Date Time -->
                                        <div class="form-group">
                                            <label class="strong">Start Date Time <span
                                                    class="text-danger">*</span></label>
                                            <input type="date" class="form-control" wire:model="startDateTime"
                                                {{ $isPgComplete == true ? 'readonly' : '' }}>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <!-- Finish Date Time -->
                                        <div class="form-group">
                                            <label class="strong">Finish Date Time <span
                                                    class="text-danger">*</span></label>
                                            <input type="date" class="form-control" wire:model="finishDateTime"
                                                {{ $isPgComplete == true ? 'readonly' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-pill"
                            wire:click="resetMaintenanceApprovalForm" data-dismiss="modal">Cancel</button>
                        @if ($selectedWorkOrder->status == 'Requested to change planner group')
                            <button type="button" class="btn btn-success btn-pill"
                                wire:click='confirmApproveChange'>Approve & Change Planner Group</button>
                        @else
                            <button type="button" class="btn btn-success btn-pill"
                                wire:click="confirmMaintenanceApprove">Approve</button>
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
                            <label
                                for="reason">{{ $this->popupModalAction == 'receive' ? 'Delay notes' : 'Reason' }}</label>
                            <textarea wire:model='reason' name="reason" class="form-control" rows="3" required
                                placeholder="Please provide a reason..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-pill btn-secondary" wire:click="resetPopup"
                            data-dismiss="modal">Cancel</button>
                        @if ($popupModalActor == 'spvUser')
                            @if ($popupModalAction == 'reject')
                                <button type="button" wire:click="confirmReject"
                                    class="btn btn-pill btn-danger">Reject</button>
                            @elseif($popupModalAction == 'approve')
                                <button type="button" wire:click="confirmApprove"
                                    class="btn btn-pill btn-success">Approve</button>
                            @endif
                        @elseif ($popupModalActor == 'spvMaintenance')
                            @if ($popupModalAction == 'reject')
                                <button type="button" wire:click="confirmMaintenanceReject"
                                    class="btn btn-pill btn-danger">Reject</button>
                            @elseif($popupModalAction == 'receive')
                                <button type="button" wire:click="confirmMaintenanceReceive"
                                    class="btn btn-pill btn-info">Receive</button>
                            @elseif($popupModalAction == 'rejectChange')
                                <button type="button" wire:click="confirmRejectChange"
                                    class="btn btn-pill btn-danger">Reject</button>
                            @elseif($popupModalAction == 'needRevision')
                                <button type="button" wire:click="confirmNeedRevision"
                                    class="btn btn-pill btn-primary">Submit Revision</button>
                            @elseif($popupModalAction == 'approveClose')
                                <button type="button" wire:click="confirmApproveClose"
                                    class="btn btn-pill btn-success">Approve Close</button>
                            @endif
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

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
                            className: "btn btn-pill"
                        },
                        confirm: {
                            text: "Yes, Approve",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-success"
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
                            className: "btn btn-pill"
                        },
                        confirm: {
                            text: "Yes, Approve",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-success"
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
                            className: "btn btn-pill"
                        },
                        confirm: {
                            text: "Yes, Reject",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-danger"
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
                            className: "btn btn-pill"
                        },
                        confirm: {
                            text: "Yes, Receive",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-info"
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
                            className: "btn btn-pill"
                        },
                        confirm: {
                            text: "Yes, Reject",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-success"
                        }
                    },
                    dangerMode: true,
                }).then((result) => {
                    if (result) {
                        @this.rejectMaintenance();
                    }
                });
            });

            Livewire.on('confirmRejectChange', () => {
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
                    text: "You are about to reject this request to change planner group. This action cannot be undone.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill"
                        },
                        confirm: {
                            text: "Yes, Reject",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-danger"
                        }
                    },
                    dangerMode: true,
                }).then((result) => {
                    if (result) {
                        @this.rejectChange();
                    }
                });
            });

            Livewire.on('confirmApproveChange', () => {
                swal({
                    title: "Are you sure?",
                    text: "You are about to approve this request to change planner group. This action cannot be undone.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill"
                        },
                        confirm: {
                            text: "Yes, Approve",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-success"
                        }
                    },
                    dangerMode: true,
                }).then((result) => {
                    if (result) {
                        @this.approveChange();
                    }
                });
            });

            Livewire.on('confirmNeedRevision', () => {
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
                    text: "You are about to submit revision. This action cannot be undone.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill"
                        },
                        confirm: {
                            text: "Yes, Submit",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-primary"
                        }
                    },
                    dangerMode: true,
                }).then((result) => {
                    if (result) {
                        @this.needRevision();
                    }
                });
            });

            Livewire.on('confirmApproveClose', () => {
                swal({
                    title: "Are you sure?",
                    text: "You are about to close this SPK. This action cannot be undone.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill"
                        },
                        confirm: {
                            text: "Yes, Close",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-success"
                        }
                    },
                    dangerMode: true,
                }).then((result) => {
                    if (result) {
                        @this.approveClose();
                    }
                });
            });

            // Prevent accidental modal closing
            $('#detailModal').on('hide.bs.modal', function(e) {
                if (e.target !== this) return false;
            });
        }, {
            once: true
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
                            closeModal: true,
                            className: "btn btn-pill"
                        },
                        confirm: {
                            text: "Yes, Approve",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-success"
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
                            closeModal: true,
                            className: "btn btn-pill"
                        },
                        confirm: {
                            text: "Yes, Approve",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-success"
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
                            closeModal: true,
                            className: "btn btn-pill"
                        },
                        confirm: {
                            text: "Yes, Reject",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-danger"
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
                            closeModal: true,
                            className: "btn btn-pill"
                        },
                        confirm: {
                            text: "Yes, Receive",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-info"
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
                            closeModal: true,
                            className: "btn btn-pill"
                        },
                        confirm: {
                            text: "Yes, Reject",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-danger"
                        }
                    },
                    dangerMode: true,
                }).then((result) => {
                    if (result) @this.rejectMaintenance();
                });
            });

            Livewire.on('confirmRejectChange', () => {
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
                    text: "You are about to reject this request to change planner group. This action cannot be undone.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill"
                        },
                        confirm: {
                            text: "Yes, Reject",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-danger"
                        }
                    },
                    dangerMode: true,
                }).then((result) => {
                    if (result) {
                        @this.rejectChange();
                    }
                });
            });

            Livewire.on('confirmApproveChange', () => {
                swal({
                    title: "Are you sure?",
                    text: "You are about to approve this request to change planner group. This action cannot be undone.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill"
                        },
                        confirm: {
                            text: "Yes, Approve",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-success"
                        }
                    },
                    dangerMode: true,
                }).then((result) => {
                    if (result) {
                        @this.approveChange();
                    }
                });
            });

            Livewire.on('confirmNeedRevision', () => {
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
                    text: "You are about to submit revision. This action cannot be undone.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill"
                        },
                        confirm: {
                            text: "Yes, Submit",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-primary"
                        }
                    },
                    dangerMode: true,
                }).then((result) => {
                    if (result) {
                        @this.needRevision();
                    }
                });
            });

            Livewire.on('confirmApproveClose', () => {
                swal({
                    title: "Are you sure?",
                    text: "You are about to close this SPK. This action cannot be undone.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill"
                        },
                        confirm: {
                            text: "Yes, Close",
                            value: true,
                            visible: true,
                            closeModal: true,
                            className: "btn btn-pill btn-success"
                        }
                    },
                    dangerMode: true,
                }).then((result) => {
                    if (result) {
                        @this.approveClose();
                    }
                });
            });
        }, {
            once: true
        });
    </script>
@endpush
