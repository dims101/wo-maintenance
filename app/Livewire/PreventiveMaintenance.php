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

    public $duration = '';

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
        $this->loadAvailableUsers();
        $this->dispatch('showDetailModal');
    }

    public function closeModal()
    {
        $this->selectedPmId = null;
        $this->selectedPm = null;
        $this->rescheduleDate = '';  // KEEP jika masih ada reschedule
        $this->selectedPic = null;
        $this->teamMembers = [''];
        $this->duration = '';
        $this->showAssignModal = false;
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
            $allUsers = User::where('dept_id', 1)
                ->whereIn('role_id', [4, 5])
                ->whereIn('status', ['active', 'Active', 'ACTIVE'])
                ->get();

            $this->users = $allUsers->map(function ($user) {
                $hoursLeft = $this->calculateUserHoursLeft($user->id);

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'nup' => $user->nup ?? '-',
                    'hours_left' => $hoursLeft,
                    'is_available' => $hoursLeft > 0,
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

    /**
     * Calculate hours left for user in specific week based on PM basic_start_date
     * Gabung dari WO (approval_id) dan PM (pm_id)
     */
    private function calculateUserHoursLeft($userId)
    {
        $maxHoursPerWeek = 35;

        // Get week number dari basic_start_date PM ini
        if ($this->selectedPm && $this->selectedPm->basic_start_date) {
            $startDate = \Carbon\Carbon::parse($this->selectedPm->basic_start_date, 'Asia/Jakarta');
            $weekNumber = $startDate->week();
            $year = $startDate->year;
        } else {
            // Fallback ke current week jika PM belum punya basic_start_date
            $today = \Carbon\Carbon::now('Asia/Jakarta');
            $weekNumber = $today->week();
            $year = $today->year;
        }

        // Sum duration dari team_assignments untuk user ini di week ini
        // Gabung WO (approval_id) dan PM (pm_id)
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
        $this->duration = '';
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

            // Validate duration
            if (empty($this->duration) || $this->duration <= 0) {
                throw new \Exception('Please input duration in hours.');
            }

            // Get valid team members
            $validTeamMembers = array_filter($this->teamMembers);

            if (empty($validTeamMembers)) {
                throw new \Exception('Please select at least one team member.');
            }

            // Calculate week number dari basic_start_date
            if (! $pm->basic_start_date) {
                throw new \Exception('PM must have a basic start date.');
            }

            $startDate = \Carbon\Carbon::parse($pm->basic_start_date, 'Asia/Jakarta');
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

            // Assign PIC
            TeamAssignment::create([
                'pm_id' => $this->selectedPmId,
                'user_id' => $this->selectedPic,
                'is_pic' => true,
                'is_active' => true,
                'start_date' => null,  // NULL untuk PM
                'finish_date' => null, // NULL untuk PM
                'duration' => $this->duration,
                'week_number' => $weekNumber,
                'year' => $year,
            ]);

            // Assign Team Members
            foreach ($validTeamMembers as $memberId) {
                if ($memberId != $this->selectedPic) {
                    TeamAssignment::create([
                        'pm_id' => $this->selectedPmId,
                        'user_id' => $memberId,
                        'is_pic' => false,
                        'is_active' => true,
                        'start_date' => null,  // NULL untuk PM
                        'finish_date' => null, // NULL untuk PM
                        'duration' => $this->duration,
                        'week_number' => $weekNumber,
                        'year' => $year,
                    ]);
                }
            }

            // Update PM status dan actual_start_date
            $pm->update([
                'user_status' => 'ASSIGNED',
                'actual_start_date' => $startDate->toDateString(),
                'actual_start_time' => $startDate->toTimeString(),
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

    /**
     * Calculate hours left untuk specific week
     */
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

    public function canAssignTeam()
    {
        return $this->selectedPm &&
               ! in_array($this->selectedPm->user_status, ['COMPLETED', 'CLOSED']) &&
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
            'PLAN' => 'badge-secondary',
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
