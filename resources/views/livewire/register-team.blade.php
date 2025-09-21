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
                                    id="name" wire:model="name" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- NUP -->
                            <div class="form-group">
                                <label for="nup">NUP <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nup') is-invalid @enderror"
                                    id="nup" wire:model="nup" maxlength="20" required>
                                @error('nup')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="form-group">
                                <label for="email">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" wire:model="email" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Department (Read Only) -->
                            <div class="form-group">
                                <label for="department">Department</label>
                                <input type="text" class="form-control" id="department" value="Department 1"
                                    readonly>
                                <small class="form-text text-muted">Team members are automatically assigned to
                                    Department 1</small>
                            </div>

                            <!-- Section -->
                            <div class="form-group">
                                <label for="section">Section <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('section') is-invalid @enderror"
                                    id="section" wire:model="section" maxlength="15" required>
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
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-block">
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
                        <small class="text-muted">All team members from Department 1</small>
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
                                        <th width="10%">Section</th>
                                        <th width="12%">Planner Group</th>
                                        <th width="8%">Status</th>
                                        <th width="10%">Rejected</th>
                                        <th width="15%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($teamMembers as $index => $member)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $member->name }}</strong>
                                                @if ($member->is_default_password)
                                                    <br><small class="badge badge-warning">Default Pass</small>
                                                @endif
                                            </td>
                                            <td>{{ $member->nup }}</td>
                                            <td>{{ $member->email }}</td>
                                            <td>{{ $member->section }}</td>
                                            <td>{{ $member->plannerGroup->name ?? 'N/A' }}</td>
                                            <td>
                                                @if ($member->status == 'pending')
                                                    <span class="badge badge-warning">Pending</span>
                                                @elseif($member->status == 'approved')
                                                    <span class="badge badge-success">Approved</span>
                                                @elseif($member->status == 'rejected')
                                                    <span class="badge badge-danger">Rejected</span>
                                                @else
                                                    <span
                                                        class="badge badge-secondary">{{ ucfirst($member->status ?? 'Active') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($member->is_rejected)
                                                    <span class="badge badge-danger">Yes</span>
                                                    @if ($member->reject_reason)
                                                        <br><small class="text-muted"
                                                            title="{{ $member->reject_reason }}">
                                                            {{ Str::limit($member->reject_reason, 20) }}
                                                        </small>
                                                    @endif
                                                @else
                                                    <span class="badge badge-success">No</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($canEditDelete)
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        @if ($member->status == 'pending')
                                                            <button
                                                                onclick="confirmApprove({{ $member->id }}, '{{ $member->name }}')"
                                                                class="btn btn-link btn-success" title="Approve">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                            <button
                                                                onclick="confirmReject({{ $member->id }}, '{{ $member->name }}')"
                                                                class="btn btn-danger btn-link" title="Reject">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        @endif
                                                        <button wire:click="edit({{ $member->id }})"
                                                            class="btn btn-link btn-warning" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button wire:click="resetPassword({{ $member->id }})"
                                                            class="btn btn-link btn-info" title="Reset Password">
                                                            <i class="fas fa-key"></i>
                                                        </button>
                                                        <button
                                                            onclick="confirmDelete({{ $member->id }}, '{{ $member->name }}')"
                                                            class="btn btn-link btn-danger" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                @else
                                                    <span class="text-muted small">No Access</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">
                                                <i class="fas fa-users fa-2x mb-2"></i><br>
                                                No team members found
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
                                    <input type="text" class="form-control" value="Department 1" readonly>
                                    <small class="form-text text-muted">Department cannot be changed</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeEditModal">
                            <i class="fas fa-times"></i> Close
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="confirmUpdateUser">
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
                padding: 0.25rem 0.5rem;
            }

            .btn-group-vertical .btn {
                border-radius: 0.25rem !important;
                margin-bottom: 2px;
            }

            .btn-group-vertical .btn:last-child {
                margin-bottom: 0;
            }

            .table td {
                vertical-align: middle;
            }

            .table th {
                border-top: none;
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

            .thead-dark th {
                background-color: #343a40;
                border-color: #454d55;
                color: #fff;
            }
        </style>
    @endpush

    @push('scripts')
        <!-- SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize DataTable
                let table;

                function initDataTable() {
                    if ($.fn.DataTable.isDataTable('#teamMembersTable')) {
                        $('#teamMembersTable').DataTable().destroy();
                    }

                    // Use your existing DataTable setup
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
                        timer: 3000,
                        timerProgressBar: true
                    });
                    setTimeout(initDataTable, 500);
                });

                window.addEventListener('teamMemberUpdated', function(event) {
                    Swal.fire({
                        title: event.detail[0].title,
                        text: event.detail[0].message,
                        icon: event.detail[0].icon,
                        confirmButtonText: 'OK',
                        timer: 3000,
                        timerProgressBar: true
                    });
                    setTimeout(initDataTable, 500);
                });

                window.addEventListener('teamMemberDeleted', function(event) {
                    Swal.fire({
                        title: event.detail[0].title,
                        text: event.detail[0].message,
                        icon: event.detail[0].icon,
                        confirmButtonText: 'OK',
                        timer: 3000,
                        timerProgressBar: true
                    });
                    setTimeout(initDataTable, 500);
                });

                window.addEventListener('passwordReset', function(event) {
                    Swal.fire({
                        title: event.detail[0].title,
                        text: event.detail[0].message,
                        icon: event.detail[0].icon,
                        confirmButtonText: 'OK',
                        timer: 3000,
                        timerProgressBar: true
                    });
                });

                window.addEventListener('showAlert', function(event) {
                    Swal.fire({
                        title: event.detail[0].title,
                        text: event.detail[0].message,
                        icon: event.detail[0].icon,
                        confirmButtonText: 'OK'
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
                        cancelButtonColor: '#6c757d'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            @this.updateUser();
                        }
                    });
                });
            });

            function confirmDelete(userId, userName) {
                Swal.fire({
                    title: 'Delete Team Member?',
                    text: `Are you sure you want to delete ${userName}? This action cannot be undone.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Yes, Delete!',
                    cancelButtonText: 'Cancel',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.delete(userId);
                    }
                });
            }

            // Tambahkan function ini di dalam script
            function confirmApprove(userId, userName) {
                Swal.fire({
                    title: 'Approve Team Member?',
                    text: `Are you sure you want to approve ${userName}?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'Yes, Approve!',
                    cancelButtonText: 'Cancel',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.approve(userId);
                    }
                });
            }

            // Tambahkan event listener ini setelah event listener yang sudah ada
            window.addEventListener('teamMemberApproved', function(event) {
                Swal.fire({
                    title: event.detail[0].title,
                    text: event.detail[0].message,
                    icon: event.detail[0].icon,
                    confirmButtonText: 'OK',
                    timer: 3000,
                    timerProgressBar: true
                });
                setTimeout(initDataTable, 500);
            });

            // Tambahkan function ini di dalam script
            function confirmReject(userId, userName) {
                Swal.fire({
                    title: 'Reject Team Member?',
                    text: `Are you sure you want to reject ${userName}?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Yes, Reject!',
                    cancelButtonText: 'Cancel',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.reject(userId);
                    }
                });
            }

            // Event listener untuk rejected
            window.addEventListener('teamMemberRejected', function(event) {
                Swal.fire({
                    title: event.detail[0].title,
                    text: event.detail[0].message,
                    icon: event.detail[0].icon,
                    confirmButtonText: 'OK',
                    timer: 3000,
                    timerProgressBar: true
                });
                setTimeout(initDataTable, 500);
            });

            // Tambahkan event listener ini di dalam script
            window.addEventListener('confirmRegister', function() {
                Swal.fire({
                    title: 'Register Team Member?',
                    text: 'Are you sure you want to register this team member? They will be pending for approval.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#007bff',
                    confirmButtonText: 'Yes, Register!',
                    cancelButtonText: 'Cancel',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.register();
                    }
                });
            });
        </script>
    @endpush
</div>
