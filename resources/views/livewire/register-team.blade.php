<div>
    <div class="container-fluid">
        <div class="row">
            <!-- Form Register Team (Left Side) -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{ $subTitle }}</h4>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="confirmRegister">
                            <!-- Name -->
                            <div class="form-group">
                                <label for="name">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" wire:model="name" required placeholder="Input name">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- NUP -->
                            <div class="form-group">
                                <label for="nup">NUP <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nup') is-invalid @enderror"
                                    id="nup" wire:model="nup" maxlength="20" required placeholder="Input NUP">
                                @error('nup')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="form-group">
                                <label for="email">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" wire:model="email" required placeholder="Input email">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Department (Read Only) -->
                            <div class="form-group">
                                <label for="department">Department</label>
                                <input type="text" class="form-control" id="department"
                                    value="Department Maintenance" readonly>
                            </div>

                            <!-- Section -->
                            <div class="form-group">
                                <label for="section">Section <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('section') is-invalid @enderror"
                                    id="section" wire:model="section" maxlength="15" required
                                    placeholder="Input section">
                                @error('section')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Planner Group -->
                            <div class="form-group">
                                <label for="planner_group_id">Planner Group <span class="text-danger">*</span></label>
                                <select class="form-control @error('planner_group_id') is-invalid @enderror"
                                    id="planner_group_id" wire:model="planner_group_id" required>
                                    <option value="">Select Planner Group</option>
                                    @foreach ($planner_groups as $group)
                                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                                    @endforeach
                                </select>
                                @error('planner_group_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Submit Button -->
                            <div class="form-group text-right">
                                <button type="submit" class="btn btn-primary btn-pill">
                                    <i class="fas fa-user-plus"></i> Register Team Member
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Team Members Table (Right Side) -->
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Team Members List</h4>
                        <small class="text-muted">All team members from Department Maintenance</small>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-borderless table-striped table-hover" id="teamMembersTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="15%">Name</th>
                                        <th width="10%">NUP</th>
                                        <th width="15%">Email</th>
                                        <th width="10%">Manhours</th>
                                        <th width="12%">Planner Group</th>
                                        <th width="8%">Status</th>
                                        <th width="20%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($teamMembers as $index => $member)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $member->name }}</strong>
                                                @if ($member->reject_reason)
                                                    <br><small class="text-danger">Reason:
                                                        {{ $member->reject_reason }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $member->nup }}</td>
                                            <td>{{ $member->email }}</td>
                                            <td>
                                                {{ $member->actualManhours->filter(function ($manhour) {
                                                        return \Carbon\Carbon::parse($manhour->date)->isSameDay(\Carbon\Carbon::today());
                                                    })->sum('actual_time') }}
                                                minutes
                                            </td>
                                            <td>{{ $member->plannerGroup->name ?? 'N/A' }}</td>
                                            <td>
                                                @if ($member->status == 'Pending')
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-clock"></i> Pending
                                                    </span>
                                                @elseif($member->status == 'Active')
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check-circle"></i> Active
                                                    </span>
                                                @elseif($member->status == 'Rejected')
                                                    <span class="badge badge-danger">
                                                        <i class="fas fa-times-circle"></i> Rejected
                                                    </span>
                                                @else
                                                    <span class="badge badge-secondary">
                                                        {{ ucfirst($member->status ?? 'Unknown') }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($canEditDelete)
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        @if ($member->status == 'Pending')
                                                            <button
                                                                onclick="confirmApprove({{ $member->id }}, '{{ addslashes($member->name) }}')"
                                                                class="btn btn-success btn-lg btn-link"
                                                                title="Approve Member">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                            <button
                                                                onclick="showRejectInput({{ $member->id }}, '{{ addslashes($member->name) }}')"
                                                                class="btn btn-danger btn-lg btn-link"
                                                                title="Reject Member">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        @elseif($member->status == 'Rejected')
                                                            <button
                                                                onclick="confirmApprove({{ $member->id }}, '{{ addslashes($member->name) }}')"
                                                                class="btn btn-success btn-lg btn-link"
                                                                title="Approve Member">
                                                                <i class="fas fa-check"></i> Re-approve
                                                            </button>
                                                        @endif

                                                        <button wire:click="edit({{ $member->id }})"
                                                            class="btn btn-warning btn-lg btn-link"
                                                            title="Edit Member">
                                                            <i class="fas fa-edit"></i>
                                                        </button>

                                                        <button
                                                            onclick="confirmResetPassword({{ $member->id }}, '{{ addslashes($member->name) }}')"
                                                            class="btn btn-info btn-lg btn-link"
                                                            title="Reset Password">
                                                            <i class="fas fa-key"></i>
                                                        </button>

                                                        <button
                                                            onclick="confirmDelete({{ $member->id }}, '{{ addslashes($member->name) }}')"
                                                            class="btn btn-danger btn-lg btn-link"
                                                            title="Delete Member">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                @else
                                                    <span class="text-muted small">
                                                        <i class="fas fa-lock"></i> No Access
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                <i class="fas fa-users fa-3x mb-3"></i><br>
                                                <h5>No team members found</h5>
                                                <p>Start by registering your first team member.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Team Modal -->
    @if ($showEditModal)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-user-edit"></i> Edit Team Member
                        </h5>
                        <button type="button" class="close text-white" wire:click="closeEditModal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Edit Name -->
                                <div class="form-group">
                                    <label for="edit_name">Name <span class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control @error('edit_name') is-invalid @enderror" id="edit_name"
                                        wire:model="edit_name" required>
                                    @error('edit_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Edit NUP -->
                                <div class="form-group">
                                    <label for="edit_nup">NUP <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('edit_nup') is-invalid @enderror"
                                        id="edit_nup" wire:model="edit_nup" maxlength="20" required>
                                    @error('edit_nup')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Edit Email -->
                                <div class="form-group">
                                    <label for="edit_email">Email <span class="text-danger">*</span></label>
                                    <input type="email"
                                        class="form-control @error('edit_email') is-invalid @enderror" id="edit_email"
                                        wire:model="edit_email" required>
                                    @error('edit_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Edit Section -->
                                <div class="form-group">
                                    <label for="edit_section">Section <span class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control @error('edit_section') is-invalid @enderror"
                                        id="edit_section" wire:model="edit_section" maxlength="15" required>
                                    @error('edit_section')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Edit Planner Group -->
                                <div class="form-group">
                                    <label for="edit_planner_group_id">Planner Group <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control @error('edit_planner_group_id') is-invalid @enderror"
                                        id="edit_planner_group_id" wire:model="edit_planner_group_id" required>
                                        <option value="">Select Planner Group</option>
                                        @foreach ($planner_groups as $group)
                                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('edit_planner_group_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Department (Read Only) -->
                                <div class="form-group">
                                    <label>Department</label>
                                    <input type="text" class="form-control" value="Department Maintenance"
                                        readonly>
                                    <small class="form-text text-muted">Department cannot be changed</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-pill" wire:click="closeEditModal">
                            <i class="fas fa-times"></i> Close
                        </button>
                        <button type="button" class="btn btn-primary btn-pill" wire:click="confirmUpdateUser">
                            <i class="fas fa-save"></i> Update Team Member
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @push('styles')
        <style>
            .modal.show {
                background: rgba(0, 0, 0, 0.5);
            }

            .badge {
                font-size: 0.75rem;
                padding: 0.35rem 0.65rem;
                border-radius: 0.5rem;
            }

            .badge i {
                margin-right: 0.25rem;
            }

            .btn-group .btn {
                border-radius: 0.25rem !important;
                margin-right: 2px;
                margin-bottom: 2px;
            }

            .btn-group .btn:last-child {
                margin-right: 0;
            }

            .table td {
                vertical-align: middle;
            }

            .table th {
                border-top: none;
                background-color: #f8f9fa;
                color: #495057;
                font-weight: 600;
            }

            .card-header {
                background-color: #f8f9fa;
                border-bottom: 1px solid #dee2e6;
            }

            .form-control:focus {
                border-color: #007bff;
                box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            }

            .btn-primary {
                background-color: #007bff;
                border-color: #007bff;
            }

            .btn-primary:hover {
                background-color: #0056b3;
                border-color: #004085;
            }

            .btn-success {
                background-color: #28a745;
                border-color: #28a745;
            }

            .btn-success:hover {
                background-color: #1e7e34;
                border-color: #1c7430;
            }

            .btn-danger {
                background-color: #dc3545;
                border-color: #dc3545;
            }

            .btn-danger:hover {
                background-color: #c82333;
                border-color: #bd2130;
            }

            .btn-warning {
                background-color: #ffc107;
                border-color: #ffc107;
                color: #212529;
            }

            .btn-warning:hover {
                background-color: #e0a800;
                border-color: #d39e00;
                color: #212529;
            }

            .btn-info {
                background-color: #17a2b8;
                border-color: #17a2b8;
            }

            .btn-info:hover {
                background-color: #117a8b;
                border-color: #10707f;
            }

            .btn-pill {
                border-radius: 2rem;
            }

            .text-danger {
                color: #dc3545 !important;
            }

            .invalid-feedback {
                width: 100%;
                margin-top: 0.25rem;
                font-size: 0.875rem;
                color: #dc3545;
            }

            .is-invalid {
                border-color: #dc3545;
            }

            .table-hover tbody tr:hover {
                background-color: rgba(0, 0, 0, 0.075);
            }

            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.875rem;
                line-height: 1.5;
                border-radius: 0.2rem;
            }
        </style>
    @endpush

    @push('scripts')
        <!-- SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            // Global Functions - Define them first
            function confirmDelete(userId, userName) {
                Swal.fire({
                    title: 'Delete Team Member?',
                    text: `Are you sure you want to delete ${userName}? This action cannot be undone.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Yes, Delete!',
                    cancelButtonText: 'Cancel',
                    cancelButtonColor: '#6c757d',
                    customClass: {
                        confirmButton: 'btn-pill',
                        cancelButton: 'btn-pill'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.delete(userId);
                    }
                });
            }

            function confirmApprove(userId, userName) {
                Swal.fire({
                    title: 'Approve Team Member?',
                    text: `Are you sure you want to approve ${userName}? They will become active members.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'Yes, Approve!',
                    cancelButtonText: 'Cancel',
                    cancelButtonColor: '#6c757d',
                    customClass: {
                        confirmButton: 'btn-pill',
                        cancelButton: 'btn-pill'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.approve(userId);
                    }
                });
            }

            function showRejectInput(userId, userName) {
                Swal.fire({
                    title: `Reject ${userName}`,
                    text: 'Please provide a reason for rejection:',
                    icon: 'warning',
                    input: 'textarea',
                    inputPlaceholder: 'Enter rejection reason...',
                    inputAttributes: {
                        'aria-label': 'Rejection reason',
                        'maxlength': '255',
                        'rows': '4'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Continue',
                    confirmButtonColor: '#dc3545',
                    cancelButtonText: 'Cancel',
                    cancelButtonColor: '#6c757d',
                    customClass: {
                        confirmButton: 'btn-pill',
                        cancelButton: 'btn-pill'
                    },
                    inputValidator: (value) => {
                        if (!value || value.trim() === '') {
                            return 'You need to provide a reason for rejection!'
                        }
                        if (value.length > 255) {
                            return 'Reason must be less than 255 characters!'
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show final confirmation with reason
                        Swal.fire({
                            title: 'Confirm Rejection',
                            html: `Are you sure you want to reject <strong>${userName}</strong>?<br><br><small class="text-muted">Reason: "${result.value}"</small>`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, Reject!',
                            confirmButtonColor: '#dc3545',
                            cancelButtonText: 'Cancel',
                            cancelButtonColor: '#6c757d',
                            customClass: {
                                confirmButton: 'btn-pill',
                                cancelButton: 'btn-pill'
                            }
                        }).then((confirmResult) => {
                            if (confirmResult.isConfirmed) {
                                @this.reject(userId, result.value);
                            }
                        });
                    }
                });
            }

            function confirmResetPassword(userId, userName) {
                Swal.fire({
                    title: 'Reset Password?',
                    html: `Are you sure you want to reset password for <strong>${userName}</strong>?<br><br><small class="text-warning">The password will be reset to their NUP.</small>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#17a2b8',
                    confirmButtonText: 'Yes, Reset!',
                    cancelButtonText: 'Cancel',
                    cancelButtonColor: '#6c757d',
                    customClass: {
                        confirmButton: 'btn-pill',
                        cancelButton: 'btn-pill'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.resetPassword(userId);
                    }
                });
            }

            document.addEventListener('livewire:initialized', function() {
                // Initialize DataTable
                let table;

                function initDataTable() {
                    if ($.fn.DataTable.isDataTable('#teamMembersTable')) {
                        $('#teamMembersTable').DataTable().destroy();
                    }
                    $('#teamMembersTable').DataTable();
                }

                // Initialize on page load
                initDataTable();

                // Reinitialize after Livewire updates
                document.addEventListener('livewire:updated', function() {
                    setTimeout(initDataTable, 100);
                });

                // Handle success messages
                window.addEventListener('teamMemberCreated', function(event) {
                    Swal.fire({
                        title: event.detail[0].title,
                        text: event.detail[0].message,
                        icon: event.detail[0].icon,
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn-pill'
                        }
                    });
                    setTimeout(initDataTable, 500);
                });

                window.addEventListener('teamMemberUpdated', function(event) {
                    Swal.fire({
                        title: event.detail[0].title,
                        text: event.detail[0].message,
                        icon: event.detail[0].icon,
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn-pill'
                        }
                    });
                    setTimeout(initDataTable, 500);
                });

                window.addEventListener('teamMemberDeleted', function(event) {
                    Swal.fire({
                        title: event.detail[0].title,
                        text: event.detail[0].message,
                        icon: event.detail[0].icon,
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn-pill'
                        }
                    });
                    setTimeout(initDataTable, 500);
                });

                window.addEventListener('passwordReset', function(event) {
                    Swal.fire({
                        title: event.detail[0].title,
                        text: event.detail[0].message,
                        icon: event.detail[0].icon,
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn-pill'
                        }
                    });
                });

                window.addEventListener('showAlert', function(event) {
                    Swal.fire({
                        title: event.detail[0].title,
                        text: event.detail[0].message,
                        icon: event.detail[0].icon,
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn-pill'
                        }
                    });
                });

                window.addEventListener('confirmUpdate', function() {
                    Swal.fire({
                        title: 'Update Team Member?',
                        text: 'Are you sure you want to update this team member?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Update!',
                        confirmButtonColor: '#007bff',
                        cancelButtonText: 'Cancel',
                        cancelButtonColor: '#6c757d',
                        customClass: {
                            confirmButton: 'btn-pill',
                            cancelButton: 'btn-pill'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            @this.updateUser();
                        }
                    });
                });

                window.addEventListener('teamMemberApproved', function(event) {
                    Swal.fire({
                        title: event.detail[0].title,
                        text: event.detail[0].message,
                        icon: event.detail[0].icon,
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn-pill'
                        }
                    });
                    setTimeout(initDataTable, 500);
                });

                window.addEventListener('teamMemberRejected', function(event) {
                    Swal.fire({
                        title: event.detail[0].title,
                        text: event.detail[0].message,
                        icon: event.detail[0].icon,
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn-pill'
                        }
                    });
                    setTimeout(initDataTable, 500);
                });

                window.addEventListener('confirmRegister', function() {
                    Swal.fire({
                        title: 'Register Team Member?',
                        text: 'Are you sure you want to register this team member? They will be pending for approval.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#007bff',
                        confirmButtonText: 'Yes, Register!',
                        cancelButtonText: 'Cancel',
                        cancelButtonColor: '#6c757d',
                        customClass: {
                            confirmButton: 'btn-pill',
                            cancelButton: 'btn-pill'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            @this.register();
                        }
                    });
                });

                window.addEventListener('showRejectInput', function(event) {
                    const userId = event.detail.userId;

                    Swal.fire({
                        title: 'Reject Team Member',
                        text: 'Please provide a reason for rejection:',
                        icon: 'warning',
                        input: 'textarea',
                        inputPlaceholder: 'Enter rejection reason...',
                        inputAttributes: {
                            'aria-label': 'Rejection reason',
                            'maxlength': '255'
                        },
                        showCancelButton: true,
                        confirmButtonText: 'Reject',
                        confirmButtonColor: '#dc3545',
                        cancelButtonText: 'Cancel',
                        cancelButtonColor: '#6c757d',
                        customClass: {
                            confirmButton: 'btn-pill',
                            cancelButton: 'btn-pill'
                        },
                        inputValidator: (value) => {
                            if (!value || value.trim() === '') {
                                return 'You need to provide a reason for rejection!'
                            }
                            if (value.length > 255) {
                                return 'Reason must be less than 255 characters!'
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Show final confirmation
                            Swal.fire({
                                title: 'Confirm Rejection',
                                text: 'Are you sure you want to reject this team member?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Yes, Reject!',
                                confirmButtonColor: '#dc3545',
                                cancelButtonText: 'Cancel',
                                cancelButtonColor: '#6c757d',
                                customClass: {
                                    confirmButton: 'btn-pill',
                                    cancelButton: 'btn-pill'
                                }
                            }).then((confirmResult) => {
                                if (confirmResult.isConfirmed) {
                                    @this.reject(userId, result.value);
                                }
                            });
                        }
                    });
                });
            }, {
                once: true
            });

            document.addEventListener('livewire:navigated', function() {
                // Initialize DataTable for navigation
                let table;

                function initDataTable() {
                    if ($.fn.DataTable.isDataTable('#teamMembersTable')) {
                        $('#teamMembersTable').DataTable().destroy();
                    }
                    $('#teamMembersTable').DataTable();
                }

                initDataTable();

                document.addEventListener('livewire:updated', function() {
                    setTimeout(initDataTable, 100);
                });
            }, {
                once: true
            });
        </script>
    @endpush
</div>
