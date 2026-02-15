<?php

namespace App\Livewire;

use App\Models\ActivityList;
use App\Models\PreventiveMaintenance as PreventiveMaintenanceModel;
use App\Models\TeamAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class PreventiveMaintenance extends Component
{
    use WithPagination;

    #[Title('Preventive Maintenance')]
    public $perPage = 10;

    public $search = '';

    public $selectedPmId = null;

    public $selectedPm = null;

    // Activity properties
    public $newTask = '';

    public $activityLists = [];

    public $editingTaskId = null;

    public $editingTaskName = '';

    // Team Assignment properties
    public $showAssignModal = false;

    public $users = [];

    public $selectedPic = null;

    public $teamMembers = [''];

    // reschedule
    public $rescheduleDate = '';

    public $showRescheduleModal = false;

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->loadAvailableUsers();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function openDetailModal($pmId)
    {
        $this->selectedPmId = $pmId;
        $this->selectedPm = PreventiveMaintenanceModel::find($pmId);

        $this->loadActivityLists();
        $this->loadAvailableUsers();  // ✅ TAMBAHKAN INI
        $this->dispatch('showDetailModal');
    }

    public function closeModal()
    {
        $this->selectedPmId = null;
        $this->selectedPm = null;
        $this->rescheduleDate = '';  // KEEP jika masih ada reschedule
        $this->selectedPic = null;   // ✅ TAMBAHKAN
        $this->teamMembers = [''];   // ✅ TAMBAHKAN
        $this->showAssignModal = false;  // ✅ TAMBAHKAN
        $this->resetActivityModal();
        // HAPUS: $this->resetSparepartModal();
    }

    public function resetActivityModal()
    {
        $this->newTask = '';  // HAPUS jika property newTask dihapus
        $this->editingTaskId = null;
        $this->editingTaskName = '';
    }
    // ==================== ACTIVITY METHODS ====================

    public function loadActivityLists()
    {
        if ($this->selectedPmId) {
            $this->activityLists = ActivityList::where('pm_id', $this->selectedPmId)->get()->toArray();
        }
    }

    public function calculateProgress()
    {
        if (empty($this->activityLists)) {
            return 0.0;
        }

        $completedTasks = array_filter($this->activityLists, function ($task) {
            return $task['is_done'] == true;
        });

        return round((count($completedTasks) / count($this->activityLists)) * 100, 1);
    }

    // ==================== TEAM ASSIGNMENT METHODS ====================

    public function loadAvailableUsers()
    {
        try {
            // ✅ Terima 'active', 'Active', 'ACTIVE'
            $allUsers = User::where('dept_id', 1)
                ->whereIn('role_id', [4, 5])
                ->whereIn('status', ['active', 'Active', 'ACTIVE']) // ✅ Cover semua case
                ->get();

            $this->users = $allUsers->map(function ($user) {
                try {
                    $manhourToday = $user->getTotalManhourToday();
                } catch (\Exception $e) {
                    \Log::warning("Error getting manhour for user {$user->id}: ".$e->getMessage());
                    $manhourToday = 0;
                }

                $hasActiveAssignment = $user->hasActiveAssignment();

                $is_available = $manhourToday < 420 && ! $hasActiveAssignment;

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'nup' => $user->nup ?? '-',
                    'manhour_today' => $manhourToday,
                    'is_available' => $is_available,
                    'reason' => $manhourToday >= 420
                        ? 'Manhour limit reached ('.$manhourToday.' min)'
                        : ($hasActiveAssignment ? 'Has active assignment' : ''),
                ];
            })
                ->filter(function ($user) {
                    return $user['is_available'];
                })
                ->values()
                ->toArray();

            \Log::info('Available users loaded: '.count($this->users));

        } catch (\Exception $e) {
            \Log::error('Error loading users: '.$e->getMessage());
            $this->users = [];
            session()->flash('error', 'Error loading users: '.$e->getMessage());
        }
    }

    public function openAssignModal()
    {
        // Reset dulu
        $this->selectedPic = null;
        $this->teamMembers = [''];

        // Load users
        $this->loadAvailableUsers();

        // Debug: Cek jumlah users
        if (empty($this->users)) {
            session()->flash('error', 'No available users found. All maintenance staff are either at manhour limit or have active assignments.');

            return;
        }

        $this->showAssignModal = true;
        $this->dispatch('showAssignModal');
    }

    public function closeAssignModal()
    {
        $this->selectedPic = null;
        $this->teamMembers = [''];
        $this->showAssignModal = false;
        $this->dispatch('closeAssignModal');
    }

    public function addTeamMember()
    {
        $this->teamMembers[] = '';
    }

    public function removeTeamMember($index)
    {
        unset($this->teamMembers[$index]);
        $this->teamMembers = array_values($this->teamMembers);
    }

    public function updatedSelectedPic($value)
    {
        // Remove PIC from team members if selected as PIC
        $this->teamMembers = array_filter($this->teamMembers, function ($memberId) use ($value) {
            return $memberId != $value;
        });
        $this->teamMembers = array_values($this->teamMembers);
    }

    public function confirmAssignTeam()
    {
        // Validasi PIC harus dipilih
        if (! $this->selectedPic) {
            session()->flash('error', 'Please select a PIC.');

            return;
        }

        // Validasi minimal ada 1 team member
        $validTeamMembers = array_filter($this->teamMembers);
        if (empty($validTeamMembers)) {
            session()->flash('error', 'Please select at least one team member.');

            return;
        }

        $this->dispatch('confirmAssignTeam');
    }

    public function assignTeamToPm()
    {
        try {
            DB::beginTransaction();

            $pm = PreventiveMaintenanceModel::find($this->selectedPmId);

            if (! $pm) {
                throw new \Exception('Preventive Maintenance not found.');
            }

            // Check if already assigned
            $existingAssignment = TeamAssignment::where('pm_id', $this->selectedPmId)->exists();
            if ($existingAssignment) {
                throw new \Exception('Team already assigned to this PM.');
            }

            // Validate PIC
            if (! $this->selectedPic) {
                throw new \Exception('Please select a PIC.');
            }

            // Get valid team members (exclude empty values)
            $validTeamMembers = array_filter($this->teamMembers);

            if (empty($validTeamMembers)) {
                throw new \Exception('Please select at least one team member.');
            }

            // Double check all users availability
            $allUserIds = array_merge([$this->selectedPic], $validTeamMembers);
            foreach ($allUserIds as $userId) {
                $user = User::find($userId);
                if (! $user) {
                    throw new \Exception('User not found.');
                }

                // Check manhour limit
                if ($user->getTotalManhourToday() >= 420) {
                    throw new \Exception($user->name.' has reached manhour limit.');
                }

                // Check active assignment
                if ($user->hasActiveAssignment()) {
                    throw new \Exception($user->name.' already has an active assignment.');
                }
            }

            // Assign PIC
            TeamAssignment::create([
                'pm_id' => $this->selectedPmId,
                'user_id' => $this->selectedPic,
                'is_pic' => true,
            ]);

            // Assign Team Members
            foreach ($validTeamMembers as $memberId) {
                if ($memberId != $this->selectedPic) { // Pastikan tidak double assign PIC
                    TeamAssignment::create([
                        'pm_id' => $this->selectedPmId,
                        'user_id' => $memberId,
                        'is_pic' => false,
                    ]);
                }
            }

            // Update PM status
            $pm->update([
                'user_status' => 'ASSIGNED',
            ]);

            $this->selectedPm = $pm;

            DB::commit();

            $this->closeAssignModal();
            $this->dispatch('closeAllModals');
            session()->flash('message', 'Team assigned successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to assign team: '.$e->getMessage());
        }
    }

    public function canAssignTeam()
    {
        return $this->selectedPm &&
               in_array($this->selectedPm->user_status, ['RELEASED', null]) &&
               ! TeamAssignment::where('pm_id', $this->selectedPmId)->exists();
    }

    public function getAssignedTeam()
    {
        if (! $this->selectedPmId) {
            return collect();
        }

        return TeamAssignment::with('user')
            ->where('pm_id', $this->selectedPmId)
            ->get();
    }

    // ==================== RESCHEDULE METHOD ====================

    public function canReschedule()
    {
        return $this->selectedPm &&
               ! in_array($this->selectedPm->user_status, ['ON PROGRESS', 'COMPLETED', 'CLOSED']) &&
               ! TeamAssignment::where('pm_id', $this->selectedPmId)->exists();
    }

    public function confirmReschedule($newDate)
    {
        // Method ini akan dipanggil dari blade dengan parameter tanggal
        $this->rescheduleDate = $newDate;
        $this->dispatch('confirmReschedule');
    }

    public function reschedule()
    {
        try {
            DB::beginTransaction();

            $pm = PreventiveMaintenanceModel::find($this->selectedPmId);

            if (! $pm) {
                throw new \Exception('Preventive Maintenance not found.');
            }

            if (! $this->canReschedule()) {
                throw new \Exception('Cannot reschedule this PM.');
            }

            // Validasi tanggal
            if (empty($this->rescheduleDate)) {
                throw new \Exception('Please select a reschedule date.');
            }

            $selectedDate = \Carbon\Carbon::parse($this->rescheduleDate);
            $today = \Carbon\Carbon::today();

            if ($selectedDate->lt($today)) {
                throw new \Exception('Reschedule date cannot be in the past.');
            }

            $pm->update([
                'basic_start_date' => $this->rescheduleDate,
                'user_status' => 'RESCHEDULED',
            ]);

            $this->selectedPm = $pm;

            DB::commit();

            $this->dispatch('closeAllModals');
            session()->flash('message', 'PM rescheduled successfully to '.
                $selectedDate->format('d-m-Y').'.');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', $e->getMessage());
        }
    }

    // ==================== CLOSE PM METHOD ====================

    public function canClosePm()
    {
        return $this->selectedPm &&
               $this->selectedPm->user_status === 'REQUESTED TO BE CLOSED' &&
               $this->calculateProgress() == 100;
    }

    public function confirmClosePm()
    {
        if (! $this->canClosePm()) {
            session()->flash('error', 'Cannot close this PM. Check status and progress.');

            return;
        }

        $this->dispatch('confirmClosePm');
    }

    public function closePm()
    {
        try {
            DB::beginTransaction();

            $pm = PreventiveMaintenanceModel::find($this->selectedPmId);

            if (! $pm) {
                throw new \Exception('Preventive Maintenance not found.');
            }

            if (! $this->canClosePm()) {
                throw new \Exception('Cannot close this PM. Status must be REQUESTED TO BE CLOSED and progress must be 100%.');
            }

            $pm->update([
                'user_status' => 'COMPLETED',
                'actual_finish' => now(),
            ]);

            $this->selectedPm = $pm;

            DB::commit();

            $this->dispatch('closeAllModals');
            session()->flash('message', 'PM closed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', $e->getMessage());
        }
    }
    // ==================== HELPER METHODS ====================

    // public function canReschedule()
    // {
    //     return $this->selectedPm &&
    //            $this->selectedPm->actual_start_date === null &&
    //            ! $this->isCompleted();
    // }

    // ==================== HELPER METHODS ====================

    public function isCompleted()
    {
        return $this->selectedPm &&
               in_array($this->selectedPm->user_status, ['COMPLETED', 'CLOSED']);
    }

    public function getUserStatusBadgeClass($status)
    {
        return match (strtoupper($status ?? '')) {
            'COMPLETED', 'CLOSED' => 'badge-success',
            'RESCHEDULED' => 'badge-info',
            'ASSIGNED' => 'badge-info',
            'ON PROGRESS' => 'badge-primary',
            'REQUESTED TO BE CLOSED' => 'badge-warning',
            'RELEASED' => 'badge-secondary',
            default => 'badge-secondary',
        };
    }

    public function render()
    {
        $query = PreventiveMaintenanceModel::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('order', 'ilike', '%'.$this->search.'%')
                    ->orWhere('notification_number', 'ilike', '%'.$this->search.'%')
                    ->orWhere('description', 'ilike', '%'.$this->search.'%')
                    ->orWhere('user_status', 'ilike', '%'.$this->search.'%');
            });
        }

        $preventiveMaintenances = $query->orderBy('basic_start_date', 'desc')
            ->paginate($this->perPage);

        return view('livewire.preventive-maintenance', [
            'preventiveMaintenances' => $preventiveMaintenances,
        ]);
    }
}
