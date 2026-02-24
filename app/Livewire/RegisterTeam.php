<?php

namespace App\Livewire;

use App\Models\PlannerGroup;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Register Team Member')]
class RegisterTeam extends Component
{
    public $subTitle = 'Register a new team member';

    public $name;

    public $nup;

    public $email;

    public $planner_group_id;

    public $section;

    public $planner_groups = [];

    public $showEditModal = false;

    public $editUserId;

    public $edit_name;

    public $edit_email;

    public $edit_nup;

    public $edit_planner_group_id;

    public $edit_section;

    public $canEditDelete = false;

    public $selectedWeek = 'current'; // 'current', 'week_1', 'week_2', 'week_3', 'week_4'

    public $weekOptions = [];

    public function mount()
    {
        $this->authorize('access-by-role-dept', ['2,3,4', '1']);
        $this->planner_groups = PlannerGroup::orderBy('name')->get();

        // Check if current user can edit/delete (role 1 or 2)
        $currentUser = Auth::user();
        $this->canEditDelete = $currentUser && in_array($currentUser->role_id, [1, 2]);
        $this->loadWeekOptions();
    }

    /**
     * Load 5 weeks options (current + 4 weeks ago)
     */
    private function loadWeekOptions()
    {
        $this->weekOptions = [];
        $now = \Carbon\Carbon::now('Asia/Jakarta');

        for ($i = 0; $i < 5; $i++) {
            $weekStart = $now->copy()->subWeeks($i)->startOfWeek(\Carbon\Carbon::MONDAY);
            $weekEnd = $weekStart->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);

            $weekNumber = $weekStart->week();
            $year = $weekStart->year;

            $key = $i === 0 ? 'current' : 'week_'.$i;

            $this->weekOptions[$key] = [
                'label' => 'Week '.$weekNumber.' ('.$weekStart->format('d M').' - '.$weekEnd->format('d M').')',
                'week_number' => $weekNumber,
                'year' => $year,
                'start_date' => $weekStart->toDateString(),
                'end_date' => $weekEnd->toDateString(),
            ];
        }
    }

    public function updatedSelectedWeek()
    {
        // Refresh view with new filter
    }

    /**
     * Get week_number dan year dari selected week
     */
    private function getSelectedWeekData()
    {
        if (isset($this->weekOptions[$this->selectedWeek])) {
            return [
                'week_number' => $this->weekOptions[$this->selectedWeek]['week_number'],
                'year' => $this->weekOptions[$this->selectedWeek]['year'],
            ];
        }

        // Fallback ke current week
        $now = \Carbon\Carbon::now('Asia/Jakarta');

        return [
            'week_number' => $now->week(),
            'year' => $now->year,
        ];
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:100',
            'nup' => 'required|string|max:20|unique:users',
            'email' => 'required|email|max:50|unique:users',
            'planner_group_id' => 'required|integer',
            'section' => 'required|string|max:15',
        ];
    }

    public function confirmRegister()
    {
        $this->validate();

        // Dispatch confirmation dialog
        $this->dispatch('confirmRegister');
    }

    public function register()
    {
        try {
            $user = User::create([
                'name' => $this->name,
                'nup' => $this->nup,
                'email' => $this->email,
                'dept_id' => 1, // Always department 1
                'planner_group_id' => $this->planner_group_id,
                'section' => $this->section,
                'role_id' => 5, // Default role 5
                'status' => 'Pending', // Status pending
                'is_rejected' => false,
                'is_default_password' => true,
                'password' => bcrypt($this->nup),
            ]);

            // Reset form fields
            $this->reset(['name', 'nup', 'email', 'planner_group_id', 'section']);
            $this->resetValidation();

            // Dispatch success event
            $this->dispatch('teamMemberCreated', [
                'title' => 'Success!',
                'message' => 'Team member registered successfully and pending for approval.',
                'icon' => 'success',
            ]);

        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'title' => 'Error!',
                'message' => 'Registration failed. Please try again. '.$e->getMessage(),
                'icon' => 'error',
            ]);
        }
    }

    public function edit($id)
    {
        if (! $this->canEditDelete) {
            $this->dispatch('showAlert', [
                'title' => 'Access Denied!',
                'message' => 'You do not have permission to edit team members.',
                'icon' => 'error',
            ]);

            return;
        }

        $user = User::findOrFail($id);
        $this->editUserId = $user->id;
        $this->edit_name = $user->name;
        $this->edit_email = $user->email;
        $this->edit_nup = $user->nup;
        $this->edit_planner_group_id = $user->planner_group_id;
        $this->edit_section = $user->section;
        $this->showEditModal = true;
    }

    public function confirmUpdateUser()
    {
        $this->validate([
            'edit_name' => 'required|string|max:100',
            'edit_email' => 'required|email|max:50|unique:users,email,'.$this->editUserId,
            'edit_nup' => 'required|string|max:20|unique:users,nup,'.$this->editUserId,
            'edit_planner_group_id' => 'required|integer',
            'edit_section' => 'required|string|max:15',
        ]);

        // Dispatch confirmation dialog
        $this->dispatch('confirmUpdate');
    }

    public function updateUser()
    {
        if (! $this->canEditDelete) {
            $this->dispatch('showAlert', [
                'title' => 'Access Denied!',
                'message' => 'You do not have permission to update team members.',
                'icon' => 'error',
            ]);

            return;
        }

        try {
            $user = User::findOrFail($this->editUserId);

            $user->update([
                'name' => $this->edit_name,
                'email' => $this->edit_email,
                'nup' => $this->edit_nup,
                'planner_group_id' => $this->edit_planner_group_id,
                'section' => $this->edit_section,
            ]);

            $this->closeEditModal();

            $this->dispatch('teamMemberUpdated', [
                'title' => 'Updated!',
                'message' => 'Team member updated successfully.',
                'icon' => 'success',
            ]);

        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'title' => 'Error!',
                'message' => 'Failed to update team member. Please try again. '.$e->getMessage(),
                'icon' => 'error',
            ]);
        }

    }

    public function approve($id)
    {
        if (! $this->canEditDelete) {
            $this->dispatch('showAlert', [
                'title' => 'Access Denied!',
                'message' => 'You do not have permission to approve team members.',
                'icon' => 'error',
            ]);

            return;
        }

        try {
            $user = User::findOrFail($id);

            $user->update([
                'status' => 'Active', // Changed from 'approved' to 'active'
                'is_rejected' => false,
                'reject_reason' => null,
            ]);

            $this->dispatch('teamMemberApproved', [
                'title' => 'Approved!',
                'message' => "Team member {$user->name} has been approved and is now active.",
                'icon' => 'success',
            ]);

        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'title' => 'Error!',
                'message' => 'Failed to approve team member. Please try again.',
                'icon' => 'error',
            ]);
        }
    }

    public function reject($id, $reason = null)
    {
        if (! $this->canEditDelete) {
            $this->dispatch('showAlert', [
                'title' => 'Access Denied!',
                'message' => 'You do not have permission to reject team members.',
                'icon' => 'error',
            ]);

            return;
        }

        // If no reason provided, dispatch event to show reason input
        if (empty($reason)) {
            $this->dispatch('showRejectInput', ['userId' => $id]);

            return;
        }

        try {
            $user = User::findOrFail($id);

            $user->update([
                'status' => 'Rejected',
                'is_rejected' => true,
                'reject_reason' => $reason,
            ]);

            $this->dispatch('teamMemberRejected', [
                'title' => 'Rejected!',
                'message' => "Team member {$user->name} has been rejected.",
                'icon' => 'success',
            ]);

        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'title' => 'Error!',
                'message' => 'Failed to reject team member. Please try again.',
                'icon' => 'error',
            ]);
        }
    }

    public function delete($id)
    {
        if (! $this->canEditDelete) {
            $this->dispatch('showAlert', [
                'title' => 'Access Denied!',
                'message' => 'You do not have permission to delete team members.',
                'icon' => 'error',
            ]);

            return;
        }

        try {
            $user = User::findOrFail($id);
            $user->delete();

            $this->dispatch('teamMemberDeleted', [
                'title' => 'Deleted!',
                'message' => 'Team member deleted successfully.',
                'icon' => 'success',
            ]);

        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'title' => 'Error!',
                'message' => 'Failed to delete team member. Please try again.',
                'icon' => 'error',
            ]);
        }
    }

    public function resetPassword($userId)
    {
        if (! $this->canEditDelete) {
            $this->dispatch('showAlert', [
                'title' => 'Access Denied!',
                'message' => 'You do not have permission to reset passwords.',
                'icon' => 'error',
            ]);

            return;
        }

        try {
            $user = User::findOrFail($userId);
            $user->password = bcrypt($user->nup);
            $user->is_default_password = true;
            $user->save();

            $this->dispatch('passwordReset', [
                'title' => 'Password Reset',
                'message' => "Password for {$user->name} has been reset to their NUP.",
                'icon' => 'success',
            ]);

        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'title' => 'Error!',
                'message' => 'Failed to reset password. '.$e->getMessage(),
                'icon' => 'error',
            ]);
        }
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->reset(['editUserId', 'edit_name', 'edit_email', 'edit_nup', 'edit_planner_group_id', 'edit_section']);
        $this->resetValidation();
    }

    public function render()
    {
        $selectedWeekData = $this->getSelectedWeekData();
        $weekNumber = $selectedWeekData['week_number'];
        $year = $selectedWeekData['year'];

        // Get team members dengan total duration dari team_assignments
        $teamMembers = User::with(['teamAssignments' => function ($query) use ($weekNumber, $year) {
            $query->where('week_number', $weekNumber)
                ->where('year', $year)
                ->whereNull('deleted_at');
        }])
            ->where('dept_id', 1)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($user) {
                // Calculate total duration (WO + PM) untuk week ini
                $totalDuration = $user->teamAssignments->sum('duration');

                // Add computed properties
                $user->week_duration = $totalDuration;
                $user->hours_left = max(0, 35 - $totalDuration);
                $user->utilization_percentage = $totalDuration > 0 ? round(($totalDuration / 35) * 100, 1) : 0;

                return $user;
            });

        return view('livewire.register-team', [
            'teamMembers' => $teamMembers,
        ]);
    }
}
