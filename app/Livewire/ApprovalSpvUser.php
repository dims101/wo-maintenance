<?php

namespace App\Livewire;

use App\Mail\SendGeneralMail;
use App\Models\Department;
use App\Models\MaintenanceApproval;
use App\Models\Mat;
use App\Models\Order;
use App\Models\PlannerGroup;
use App\Models\TeamAssignment;
use App\Models\User;
use App\Models\WoPlannerGroup;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class ApprovalSpvUser extends Component
{
    use WithPagination;

    #[Title('SPK for Approval')]
    public $perPage = 10;

    public $search = '';

    public $reason = '';

    public $selectedWorkOrderId = null;

    public $selectedWorkOrder = null;

    public $popupModalAction = '';

    public $popupModalActor = '';

    public $popupModalHeaderClass = '';

    public $popupModalTitle = '';

    // Maintenance Approval Properties
    public $selectedOrderType = '';

    public $selectedMat = '';

    public $selectedPic = '';

    public $teamMembers = [];

    public $startDateTime = '';

    public $finishDateTime = '';

    public $duration = '';

    public $userHoursLeft = [];

    public $orderTypes = [];

    public $mats = [];

    public $users = [];

    public $isPgComplete;
    // public $users;

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        // $this->loadDropdownData();
        $this->teamMembers = [''];
    }

    private function getWorkDate()
    {
        $now = now()->setTimezone(config('app.timezone'));

        $hour = (int) $now->format('H');

        // Jika jam 00:00 - 06:59 → masih dianggap tanggal kemarin
        if ($hour < 7) {
            return $now->copy()->subDay()->toDateString();
        }

        return $now->toDateString();
    }

    // public function loadDropdownData()
    // {
    //     $this->orderTypes = Order::all();
    //     $this->mats = Mat::all();
    //     $workDate = $this->getWorkDate();

    //     $this->users = User::where('role_id', 5)
    //         ->where('planner_group_id', Auth::user()->planner_group_id)
    //         ->where('status', 'Active')
    //         ->whereRaw('
    //     COALESCE(
    //         (
    //             SELECT SUM(actual_time)
    //             FROM actual_manhours
    //             WHERE actual_manhours.user_id = users.id
    //             AND date = ?
    //             AND actual_manhours.deleted_at IS NULL
    //         ),
    //     0) < 420
    // ', [$workDate])
    //         ->get(['id', 'name']);

    // }

    public function loadDropdownData()
    {
        // Load order types dan mats (existing)
        $this->orderTypes = Order::all();
        $this->mats = Mat::all();

        // Load users dengan hours left calculation
        if ($this->selectedWorkOrder) {
            $plannerGroupId = $this->selectedWorkOrder->planner_group_id;

            $allUsers = User::where('dept_id', 1)
                ->whereIn('role_id', [4, 5])
                ->whereIn('status', ['active', 'Active', 'ACTIVE'])
                ->where('planner_group_id', $plannerGroupId)
                ->get();

            $this->users = $allUsers->map(function ($user) {
                $hoursLeft = $this->calculateUserHoursLeft($user->id);

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'nup' => $user->nup ?? '-',
                    'role_id' => $user->role_id,
                    'hours_left' => $hoursLeft,
                    'is_available' => $hoursLeft > 0,
                ];
            })
                ->filter(function ($user) {
                    return $user['is_available'];
                })
                ->values();
            // ->toArray();
        } else {
            // Fallback jika selectedWorkOrder belum ada
            $this->users = User::select('id', 'name', 'role_id')
                ->where('dept_id', 1)
                ->whereIn('role_id', [4, 5])
                ->whereIn('status', ['active', 'Active', 'ACTIVE'])
                ->get();
            // ->toArray();
        }
    }

    private function calculateUserHoursLeft($userId)
    {
        $maxHoursPerWeek = 35;

        // Get current week number dan year
        $today = \Carbon\Carbon::now('Asia/Jakarta');
        $currentWeek = $today->week();
        $currentYear = $today->year;

        // Sum duration dari team_assignments untuk user ini di minggu ini
        $totalHours = TeamAssignment::where('user_id', $userId)
            ->where('week_number', $currentWeek)
            ->where('year', $currentYear)
            ->whereNull('deleted_at')
            ->sum('duration');

        return max(0, $maxHoursPerWeek - $totalHours);
    }

    private function calculateUserHoursLeftForWeek($userId, $weekNumber, $year)
    {
        $maxHoursPerWeek = 35;

        $totalHours = TeamAssignment::where('user_id', $userId)
            ->where('week_number', $weekNumber)
            ->where('year', $year)
            ->whereNull('deleted_at')
            ->sum('duration');

        return max(0, $maxHoursPerWeek - $totalHours);
    }

    /**
     * Real-time validation saat duration diinput
     */
    public function updatedDuration()
    {
        if (empty($this->duration) || $this->duration <= 0) {
            return;
        }

        // Validate PIC
        if ($this->selectedPic) {
            $hoursLeft = $this->calculateUserHoursLeft($this->selectedPic);
            if ($this->duration > $hoursLeft) {
                $user = collect($this->users)->firstWhere('id', $this->selectedPic);
                session()->flash('error', $user['name'].' only has '.$hoursLeft.' hours left this week.');
                $this->dispatch('showAlert', [
                    'title' => 'Warning!',
                    'message' => $user['name'].' only has '.$hoursLeft.' hours left this week.',
                    'icon' => 'warning',
                ]);
            }
        }

        // Validate Team Members
        foreach ($this->teamMembers as $memberId) {
            if (! empty($memberId) && $memberId != $this->selectedPic) {
                $hoursLeft = $this->calculateUserHoursLeft($memberId);
                if ($this->duration > $hoursLeft) {
                    $user = collect($this->users)->firstWhere('id', $memberId);
                    session()->flash('error', $user['name'].' only has '.$hoursLeft.' hours left this week.');
                    $this->dispatch('showAlert', [
                        'title' => 'Warning!',
                        'message' => $user['name'].' only has '.$hoursLeft.' hours left this week.',
                        'icon' => 'warning',
                    ]);
                    break;
                }
            }
        }
    }

    public function updatedSelectedOrderType()
    {
        if ($this->selectedOrderType) {
            $this->mats = Mat::where('order_type_id', $this->selectedOrderType)->get();
            $this->selectedMat = '';
        } else {
            $this->mats = [];
            $this->selectedMat = '';
        }
    }

    public function updatedSelectedPic($value)
    {
        // Hilangkan PIC dari semua slot teamMembers
        foreach ($this->teamMembers as $i => $member) {
            if ($member === $value) {
                $this->teamMembers[$i] = ''; // kosongkan slot
            }
        }

        // Normalisasi: kalau semua slot kosong, biarkan minimal 1 slot kosong
        if (count(array_filter($this->teamMembers)) === 0) {
            $this->teamMembers = [''];
        }

        // Pastikan tidak ada duplikat antar team members
        $this->teamMembers = array_values(array_unique($this->teamMembers, SORT_REGULAR));
    }

    public function addTeamMember()
    {
        if (count($this->teamMembers) < 10) {
            $this->teamMembers[] = '';
        }
    }

    public function removeTeamMember($index)
    {
        // Index 0 tidak bisa dihapus, hanya index > 0 yang bisa dihapus
        if ($index > 0 && count($this->teamMembers) > 1) {
            unset($this->teamMembers[$index]);
            $this->teamMembers = array_values($this->teamMembers);
        }
    }

    public function openTeamCreate()
    {
        // Open new tab for team creation
        $this->dispatch('openNewTab', '/team/create');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function openDetailModal($workOrderId)
    {
        $this->selectedWorkOrderId = $workOrderId;
        $this->selectedWorkOrder = WorkOrder::with(['equipment.functionalLocation.resource.plant', 'department', 'user', 'plannerGroup', 'maintenanceApproval'])
            ->find($workOrderId);
        // dd($this->selectedWorkOrder->maintenanceApproval->start->format('Y-m-d\TH:i'));
        $maintenanceApproval = $this->selectedWorkOrder->maintenanceApproval;
        // $this->isPgComplete = $maintenanceApproval?->id ? WoPlannerGroup::where('approval_id', $maintenanceApproval->id)->count() == 2 : false;
        $this->loadDropdownData();
        $this->isPgComplete = $maintenanceApproval?->start ? true : false;
        // dd($this->isPgComplete);
        $this->selectedOrderType = $maintenanceApproval->mat->order_type_id ?? null;
        $this->selectedMat = $maintenanceApproval->mat_id ?? null;
        $this->startDateTime = $maintenanceApproval?->start?->format('Y-m-d') ?? null;
        $this->finishDateTime = $maintenanceApproval?->finish?->format('Y-m-d') ?? null;
        // dd($this->selectedOrderType);
        $this->dispatch('showDetailModal');
    }

    public function closeModal()
    {
        $this->selectedWorkOrderId = null;
        $this->selectedWorkOrder = null;
        $this->reason = '';
        $this->resetMaintenanceApprovalForm();
    }

    public function resetMaintenanceApprovalForm()
    {
        $this->selectedOrderType = '';
        $this->selectedMat = '';
        $this->selectedPic = '';
        $this->teamMembers = [''];
        $this->startDateTime = '';
        $this->finishDateTime = '';
        $this->mats = [];
        $this->startDateTime = '';
        $this->finishDateTime = '';
        $this->duration = '';
        $this->userHoursLeft = [];
    }

    public function openPopupModal($action, $actor)
    {
        $this->popupModalActor = $actor;
        $this->popupModalAction = $action;
        if ($actor == 'spvUser') {
            if ($action == 'approve') {
                $this->popupModalHeaderClass = 'bg-success';
                $this->popupModalTitle = 'Approve SPK';
                $this->popupModalAction = 'approve';
            } elseif ($action == 'reject') {
                $this->popupModalHeaderClass = 'bg-danger';
                $this->popupModalTitle = 'Reject SPK';
                $this->popupModalAction = 'reject';
            }
        } elseif ($actor == 'spvMaintenance') {
            if ($action == 'receive') {
                $this->popupModalHeaderClass = 'bg-info';
                $this->popupModalTitle = 'Receive SPK';
                $this->popupModalAction = 'receive';
            } elseif ($action == 'reject') {
                $this->popupModalHeaderClass = 'bg-danger';
                $this->popupModalTitle = 'Reject SPK';
                $this->popupModalAction = 'reject';
            } elseif ($action == 'rejectChange') {
                $this->popupModalHeaderClass = 'bg-danger';
                $this->popupModalTitle = 'Reject Planner Group Change';
                $this->popupModalAction = 'rejectChange';
            } elseif ($action == 'needRevision') {
                $this->popupModalHeaderClass = 'bg-primary';
                $this->popupModalTitle = 'Revision Notes';
                $this->popupModalAction = 'needRevision';
            }
        }
    }

    public function resetPopup()
    {
        $this->reason = '';
        $this->popupModalAction = '';
        $this->popupModalHeaderClass = '';
        $this->popupModalTitle = '';
    }

    public function getSpvDetail($spvId)
    {
        return User::find($spvId);
    }

    public function approveWorkOrder()
    {
        if (! $this->selectedWorkOrderId || ! $this->reason) {
            session()->flash('error', 'Please provide approval reason.');

            return;
        }

        // $spv = $this->getSpvDetail(Department::find(1)->spv_id);
        $spv = User::where('dept_id', 1)
            ->where('role_id', 3)
            ->where('planner_group_id', $this->selectedWorkOrder->planner_group_id)
            ->first();

        $workOrder = WorkOrder::find($this->selectedWorkOrderId);

        if ($workOrder) {
            $workOrder->update([
                'status' => 'Waiting for Maintenance Approval',
                'is_spv_rejected' => false,
                'spv_reject_reason' => null,
                'spv_approve_reason' => $this->reason,
            ]);
            $this->reason = '';

            if ($spv && $spv->email) {
                Mail::to($spv->email)->send(
                    new SendGeneralMail(
                        sapaan: 'Dear',
                        nama: $spv->name,
                        isi: 'Ada Permohonan SPK yang perlu review dari anda. Klik tombol di bawah untuk melihat detail.',
                        link: route('work-order.spv-approval'),
                        penutup: '[This message is generated by system]'
                    )
                );
            }

            $this->dispatch('closeAllModals');
            session()->flash('message', 'Work Order approved successfully.');
        }
    }

    // public function approveMaintenance()
    // {
    //     // Validation
    //     $errors = [];

    //     if (! $this->selectedOrderType) {
    //         $errors[] = 'Order Type is required.';
    //     }

    //     if (! $this->selectedMat) {
    //         $errors[] = 'Maintenance Activity Type is required.';
    //     }

    //     if (! $this->selectedPic) {
    //         $errors[] = 'PIC is required.';
    //     }

    //     if (! $this->startDateTime) {
    //         $errors[] = 'Start Date Time is required.';
    //     }

    //     if (! $this->finishDateTime) {
    //         $errors[] = 'Finish Date Time is required.';
    //     }

    //     // Filter out empty team members and validate
    //     $validTeamMembers = array_filter($this->teamMembers, function ($member) {
    //         return ! empty($member);
    //     });

    //     if (empty($validTeamMembers)) {
    //         $errors[] = 'At least one team member is required.';
    //     }

    //     // Check if PIC is in team members
    //     if ($this->selectedPic && in_array($this->selectedPic, $validTeamMembers)) {
    //         $errors[] = 'PIC cannot be a team member.';
    //     }

    //     if (! empty($errors)) {
    //         session()->flash('error', implode(' ', $errors));

    //         return;
    //     }

    //     try {
    //         DB::beginTransaction();

    //         // Create or update maintenance approval
    //         $maintenanceApproval = MaintenanceApproval::updateOrCreate(
    //             ['wo_id' => $this->selectedWorkOrderId],
    //             [
    //                 'mat_id' => $this->selectedMat,
    //                 'start' => $this->startDateTime,
    //                 'finish' => $this->finishDateTime,
    //                 'is_received' => true,
    //                 'is_rejected' => false,
    //                 'reject_reason' => null,
    //             ]
    //         );

    //         // Clear existing team assignments for this approval
    //         // TeamAssignment::where('approval_id', $maintenanceApproval->id)->delete();

    //         // Create team assignment for PIC
    //         TeamAssignment::create([
    //             'approval_id' => $maintenanceApproval->id,
    //             'user_id' => $this->selectedPic,
    //             'is_pic' => true,
    //             'is_active' => true,
    //         ]);

    //         // Create team assignments for team members
    //         foreach ($validTeamMembers as $memberId) {
    //             TeamAssignment::create([
    //                 'approval_id' => $maintenanceApproval->id,
    //                 'user_id' => $memberId,
    //                 'is_pic' => false,
    //                 'is_active' => true,
    //             ]);
    //         }

    //         // Update work order status
    //         $this->selectedWorkOrder->update([
    //             'status' => 'Planned',
    //         ]);
    //         DB::commit();

    //         WoPlannerGroup::create([
    //             'approval_id' => $this->selectedWorkOrder->maintenanceApproval->id,
    //             'planner_group_id' => $this->selectedWorkOrder->planner_group_id,
    //             'status' => 'Active',
    //         ]);

    //         // Send email to SPV
    //         $spv = $this->getSpvDetail($this->selectedWorkOrder->department->spv_id);
    //         if ($spv && $spv->email) {
    //             Mail::to($spv->email)->send(
    //                 new SendGeneralMail(
    //                     sapaan: 'Dear',
    //                     nama: $spv->name,
    //                     isi: 'SPK Maintenance telah diapprove dan dijadwalkan. Klik tombol di bawah untuk melihat detail.',
    //                     link: route('work-order.spv-approval'),
    //                     penutup: '[This message is generated by system]'
    //                 )
    //             );
    //         }

    //         $this->resetMaintenanceApprovalForm();
    //         $this->dispatch('closeAllModals');
    //         session()->flash('message', 'Maintenance approved and team assigned successfully.');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         session()->flash('error', 'An error occurred while processing the approval.');
    //     }
    // }

    public function approveMaintenance()
    {
        try {
            DB::beginTransaction();

            // ===== VALIDASI =====

            // Validate inputs
            if (! $this->selectedPic) {
                throw new \Exception('Please select a PIC.');
            }

            if (! $this->selectedOrderType || ! $this->selectedMat) {
                throw new \Exception('Please select Order Type and MAT.');
            }

            if (! $this->startDateTime || ! $this->finishDateTime) {
                throw new \Exception('Please select Start and Finish Date.');
            }

            if (empty($this->duration) || $this->duration <= 0) {
                throw new \Exception('Please input duration in hours.');
            }

            $validTeamMembers = array_filter($this->teamMembers);
            if (empty($validTeamMembers)) {
                throw new \Exception('Please select at least one team member.');
            }

            // Calculate week number dari start_date
            $startDate = \Carbon\Carbon::parse($this->startDateTime, 'Asia/Jakarta');
            $weekNumber = $startDate->week();
            $year = $startDate->year;

            // Validate ALL users (PIC + team members) hours
            $allUserIds = array_merge([$this->selectedPic], $validTeamMembers);
            $allUserIds = array_unique($allUserIds);

            foreach ($allUserIds as $userId) {
                $hoursLeft = $this->calculateUserHoursLeftForWeek($userId, $weekNumber, $year);

                if ($this->duration > $hoursLeft) {
                    $user = User::find($userId);
                    throw new \Exception($user->name.' only has '.$hoursLeft.' hours left in week '.$weekNumber.'.');
                    $this->dispatch('showAlert', [
                        'title' => 'Warning!',
                        'message' => $user['name'].' only has '.$hoursLeft.' hours left this week.',
                        'icon' => 'warning',
                    ]);
                }
            }

            // ===== CREATE/UPDATE MAINTENANCE APPROVAL =====

            $approval = MaintenanceApproval::updateOrCreate(
                ['wo_id' => $this->selectedWorkOrder->id],
                [
                    'mat_id' => $this->selectedMat,
                    'progress' => 0,
                ]
            );

            // ===== INSERT TEAM ASSIGNMENTS =====

            // Assign PIC
            TeamAssignment::create([
                'approval_id' => $approval->id,
                'user_id' => $this->selectedPic,
                'is_pic' => true,
                'is_active' => true,
                'start_date' => $this->startDateTime,
                'finish_date' => $this->finishDateTime,
                'duration' => $this->duration,
                'week_number' => $weekNumber,
                'year' => $year,
            ]);

            // Assign Team Members
            foreach ($validTeamMembers as $memberId) {
                if ($memberId != $this->selectedPic) {
                    TeamAssignment::create([
                        'approval_id' => $approval->id,
                        'user_id' => $memberId,
                        'is_pic' => false,
                        'is_active' => true,
                        'start_date' => $this->startDateTime,
                        'finish_date' => $this->finishDateTime,
                        'duration' => $this->duration,
                        'week_number' => $weekNumber,
                        'year' => $year,
                    ]);
                }
            }

            // ===== UPDATE WO PLANNER GROUP STATUS =====

            WoPlannerGroup::updateOrCreate(
                [
                    'approval_id' => $approval->id,
                    'planner_group_id' => $this->selectedWorkOrder->planner_group_id,
                ],
                [
                    'status' => 'Active',
                ]
            );

            // ===== UPDATE WORK ORDER STATUS =====

            $this->selectedWorkOrder->update([
                'status' => 'Planned',
            ]);

            // ===== SEND EMAIL (existing code) =====
            // ... keep existing email code ...

            DB::commit();

            $this->resetMaintenanceApprovalForm();
            $this->dispatch('closeAllModals');
            session()->flash('message', 'Team assigned successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', $e->getMessage());
            $this->dispatch('showAlert', [
                'title' => 'Warning!',
                'message' => $e->getMessage(),
                'icon' => 'warning',
            ]);
        }
    }

    public function receiveMaintenance()
    {
        if (! $this->selectedWorkOrderId || ! $this->reason) {
            session()->flash('error', 'Please provide delay reason.');

            return;
        }
        $wo = MaintenanceApproval::updateOrCreate(
            ['wo_id' => $this->selectedWorkOrderId],
            [
                'is_received' => true,
                'delay_reason' => $this->reason,
            ]
        );

        $spv = $this->getSpvDetail($this->selectedWorkOrder->department->spv_id);

        Mail::to($spv->email)->send(
            new SendGeneralMail(
                sapaan: 'Dear',
                nama: $spv->name,
                isi: 'SPK Maintenance diterima oleh Dept. Maintenance. Klik tombol di bawah untuk melihat detail.',
                reason: "SPK tidak dapat segera dikerjakan karena: {$this->reason}",
                link: route('work-order.spv-approval'),
                penutup: '[This message is generated by system]'
            )
        );

        $this->selectedWorkOrder->status = 'Received by Maintenance';
        $this->selectedWorkOrder->save();

        $this->reason = '';
        $this->dispatch('closeAllModals');
        session()->flash('message', 'Work Order received successfully.');
    }

    public function rejectMaintenance()
    {
        if (! $this->selectedWorkOrderId || ! $this->reason) {
            session()->flash('error', 'Please provide rejection reason.');

            return;
        }
        $wo = MaintenanceApproval::updateOrCreate(
            ['wo_id' => $this->selectedWorkOrderId],
            [
                'is_rejected' => true,
                'reject_reason' => $this->reason,
                'is_received' => false,
                'delay_reason' => null,
            ]
        );

        $spv = $this->getSpvDetail($this->selectedWorkOrder->department->spv_id);
        Mail::to($spv->email)->send(
            new SendGeneralMail(
                sapaan: 'Dear',
                nama: $spv->name,
                isi: 'SPK Maintenance ditolak oleh Dept. Maintenance. Klik tombol di bawah untuk melihat detail.',
                reason: "SPK ditolak karena: {$this->reason}",
                link: route('work-order.spv-approval'),
                penutup: '[This message is generated by system]'
            )
        );
        $this->selectedWorkOrder->status = 'Rejected by Maintenance';
        $this->selectedWorkOrder->save();

        $this->reason = '';
        $this->dispatch('closeAllModals');
        session()->flash('message', 'Work Order rejected successfully.');
    }

    public function rejectWorkOrder()
    {
        if (! $this->selectedWorkOrderId || ! $this->reason) {
            session()->flash('error', 'Please provide rejection reason.');

            return;
        }

        $workOrder = WorkOrder::find($this->selectedWorkOrderId);
        if ($workOrder) {
            $workOrder->update([
                'status' => 'Rejected',
                'is_spv_rejected' => true,
                'spv_reject_reason' => $this->reason,
                'spv_approve_reason' => null,
            ]);

            $this->reason = '';
            $this->dispatch('closeAllModals');
            session()->flash('message', 'Work Order rejected successfully.');
        }
    }

    public function confirmApproveChange()
    {
        $this->dispatch('confirmApproveChange');
    }

    public function approveChange()
    {
        $errors = [];
        $validTeamMembers = array_filter($this->teamMembers, function ($member) {
            return ! empty($member);
        });

        if (! empty($errors)) {
            session()->flash('error', implode(' ', $errors));

            return;
        }

        try {

            $approvalId = $this->selectedWorkOrder->maintenanceApproval->id;

            // 🔥 Step 1: Nonaktifkan semua assignment lama
            TeamAssignment::where('approval_id', $approvalId)
                ->update([
                    'is_active' => false,
                    'is_pic' => false,
                ]);

            // 🔥 Step 2: Aktifkan PIC baru (WAJIB)
            if (! empty($this->selectedPic)) {
                TeamAssignment::updateOrCreate(
                    [
                        'approval_id' => $approvalId,
                        'user_id' => $this->selectedPic,
                    ],
                    [
                        'is_pic' => true,
                        'is_active' => true,
                    ]
                );
            }

            // 🔥 Step 3: Aktifkan team member baru (kalau ada)
            foreach ($validTeamMembers as $memberId) {
                TeamAssignment::updateOrCreate(
                    [
                        'approval_id' => $approvalId,
                        'user_id' => $memberId,
                    ],
                    [
                        'is_pic' => false,
                        'is_active' => true,
                    ]
                );
            }

        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while processing the approval.');
        }

        // dd('test');
        $approvalId = $this->selectedWorkOrder->maintenanceApproval->id;
        $currentPgId = $this->selectedWorkOrder->planner_group_id;
        $targetPgId = $currentPgId == 1 ? 2 : 1;
        // Ambil semua planner_group_id yang ada di sistem (hanya 2)
        $allPgIds = PlannerGroup::pluck('id')->unique()->toArray();

        // Planner group yang sudah terhubung dengan approval ini
        $existingPgIds = WoPlannerGroup::where('approval_id', $approvalId)
            ->pluck('planner_group_id')
            ->toArray();
        // Jika baru ada satu planner group untuk approval ini, tambahkan yang satunya
        if (count($existingPgIds) === 1) {
            $newPgId = collect($allPgIds)->diff($existingPgIds)->first();
            WoPlannerGroup::create([
                'approval_id' => $approvalId,
                'planner_group_id' => $newPgId,
                'status' => 'Active',
            ]);
        } elseif (count($existingPgIds) === 2) {
            // Jika sudah ada dua, yang di-request jadi inactive, yang satunya jadi Requested to change
            WoPlannerGroup::where('approval_id', $approvalId)
                ->where('planner_group_id', $targetPgId)
                ->update(['status' => 'Active']);
        }

        WorkOrder::where('id', $this->selectedWorkOrderId)
            ->update(['planner_group_id' => $targetPgId, 'status' => 'Planned']);

        // $spv = $this->getSpvDetail($this->selectedWorkOrder->department->spv_id);
        $spv = User::where('dept_id', 1)
            ->where('role_id', 3)
            ->where('planner_group_id', $currentPgId)
            ->first();

        if ($spv && $spv->email) {
            Mail::to($spv->email)->send(
                new SendGeneralMail(
                    sapaan: 'Dear',
                    nama: $spv->name,
                    isi: 'Perubahan planner group diterima. Klik tombol di bawah untuk melihat detail.',
                    link: route('work-order.spv-approval'),
                    penutup: '[This message is generated by system]'
                )
            );
        }

        $this->dispatch('closeAllModals');
        session()->flash('message', 'Work Order received successfully.');
    }

    public function confirmNeedRevision()
    {
        $this->dispatch('confirmNeedRevision');
    }

    public function needRevision()
    {
        $this->selectedWorkOrder->update([
            'revision_note' => $this->reason,
            'status' => 'Need Revision',
        ]);

        $spv = User::where('dept_id', 1)
            ->where('role_id', 3)
            ->where('planner_group_id', $this->selectedWorkOrder->planner_group_id)
            ->first();

        if ($spv && $spv->email) {
            Mail::to($spv->email)->send(
                new SendGeneralMail(
                    sapaan: 'Dear',
                    nama: $spv->name,
                    isi: 'Ada revisi yang perlu anda tinjau. Klik tombol di bawah untuk melihat detail.',
                    reason: "Revisi: {$this->reason}",
                    link: route('work-order.spv-approval'),
                    penutup: '[This message is generated by system]'
                )
            );
        }
        $this->dispatch('closeAllModals');
        session()->flash('message', 'Revision for this SPK is successfuly submitted.');
    }

    public function confirmApproveClose()
    {
        $this->dispatch('confirmApproveClose');
    }

    public function approveClose()
    {
        try {
            DB::beginTransaction();

            $approval = MaintenanceApproval::find($this->selectedApprovalId);

            if (! $approval) {
                throw new \Exception('Approval not found.');
            }

            // ===== AGGREGATE START & FINISH DARI TEAM ASSIGNMENTS =====

            $teamAssignments = TeamAssignment::where('approval_id', $approval->id)
                ->whereNull('deleted_at')
                ->get();

            if ($teamAssignments->isEmpty()) {
                throw new \Exception('No team assignments found.');
            }

            // Ambil earliest start_date dan latest finish_date
            $startDate = $teamAssignments->min('start_date');
            $finishDate = $teamAssignments->max('finish_date');

            // Update approval dengan aggregated dates
            $approval->update([
                'start' => $startDate,
                'finish' => $finishDate,
                'is_closed' => true,
            ]);

            // Update WO status
            $approval->workOrder->update([
                'status' => 'Closed',
            ]);

            DB::commit();

            $spv = $this->getSpvDetail($this->selectedWorkOrder->department->spv_id);

            if ($spv && $spv->email) {
                Mail::to($spv->email)->send(
                    new SendGeneralMail(
                        sapaan: 'Dear',
                        nama: $spv->name,
                        isi: 'SPK telah selesai. Klik tombol di bawah untuk melihat detail.',
                        link: route('work-order.spv-approval'),
                        penutup: '[This message is generated by system]'
                    )
                );
            }

            $this->dispatch('closeAllModals');
            session()->flash('message', 'Work order closed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', $e->getMessage());
        }
    }

    public function confirmRejectChange()
    {
        $this->dispatch('confirmRejectChange');
    }

    public function rejectChange()
    {
        $approvalId = $this->selectedWorkOrder->maintenanceApproval->id;
        $currentPgId = $this->selectedWorkOrder->planner_group_id;
        $targetPgId = $currentPgId == 1 ? 2 : 1;

        WoPlannerGroup::where('approval_id', $approvalId)
            ->where('planner_group_id', $currentPgId)
            ->update(['status' => 'Active']);

        WorkOrder::where('id', $this->selectedWorkOrderId)
            ->update(['status' => 'Planned']);

        $spv = User::where('dept_id', 1)
            ->where('role_id', 3)
            ->where('planner_group_id', $currentPgId)
            ->first();

        if ($spv && $spv->email) {
            Mail::to($spv->email)->send(
                new SendGeneralMail(
                    sapaan: 'Dear',
                    nama: $spv->name,
                    isi: 'Perubahan planner group ditolak. Klik tombol di bawah untuk melihat detail.',
                    link: route('work-order.spv-approval'),
                    penutup: '[This message is generated by system]'
                )
            );
        }

        $this->dispatch('closeAllModals');
        session()->flash('message', "Work Order's change planner group rejection successfully submitted");
    }

    public function confirmApprove()
    {
        $this->dispatch('confirmApprove');
    }

    public function confirmReject()
    {
        $this->dispatch('confirmReject');
    }

    public function confirmMaintenanceReceive()
    {
        $this->dispatch('confirmMaintenanceReceive');
    }

    public function confirmMaintenanceReject()
    {
        $this->dispatch('confirmMaintenanceReject');
    }

    public function confirmMaintenanceApprove()
    {
        $this->dispatch('confirmMaintenanceApprove');
    }

    public function render()
    {
        $query = WorkOrder::with(['equipment.functionalLocation.resource.plant', 'department', 'user'])
            ->where('is_spv_rejected', 'false');

        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('user', function ($userQuery) {
                    $userQuery->where('name', 'ilike', '%'.$this->search.'%');
                })
                    ->orWhereHas('department', function ($deptQuery) {
                        $deptQuery->where('name', 'ilike', '%'.$this->search.'%');
                    })
                    ->orWhere('status', 'ilike', '%'.$this->search.'%')
                    ->orWhere('urgent_level', 'ilike', '%'.$this->search.'%')
                    ->orWhere('notification_number', 'ilike', '%'.$this->search.'%');
            });
        }

        $user = Auth::user();

        if ($user->dept_id != 1) {
            $query->where('req_dept_id', $user->dept_id)
                ->where('status', 'Waiting for SPV Approval')
                ->orWhere('status', 'Requested to be closed');
        }

        if ($user->dept_id == 1) {
            $query->where(function ($q) use ($user) {

                // 1️⃣ Waiting for Maintenance Approval sesuai planner group
                $q->where(function ($sub) use ($user) {
                    $sub->where('planner_group_id', $user->planner_group_id)
                        ->where('status', 'Waiting for Maintenance Approval');
                })

                // 2️⃣ Requested to change planner group
                    ->orWhere(function ($sub) use ($user) {
                        $sub->where('status', 'Requested to change planner group')
                            ->where('planner_group_id', '!=', $user->planner_group_id);
                    })
                    ->orWhere(function ($sub) use ($user) {
                        $sub->where('status', 'Received by Maintenance')
                            ->where('planner_group_id', $user->planner_group_id);
                    });

            });
        }

        $workOrders = $query->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.approval-spv-user', [
            'workOrders' => $workOrders,
        ]);
    }
}
