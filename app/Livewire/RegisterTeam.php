<?php

namespace App\Livewire;

use App\Models\Department;
use App\Models\PlannerGroup;
use App\Models\Role;
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

    public function mount()
    {
        $this->planner_groups = PlannerGroup::orderBy('name')->get();

        // Check if current user can edit/delete (role 1 or 2)
        $currentUser = Auth::user();
        $this->canEditDelete = $currentUser && in_array($currentUser->role_id, [1, 2]);
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
        return view('livewire.register-team', [
            'teamMembers' => User::with('actualManhours')->where('dept_id', 1)->orderBy('created_at', 'desc')->get(),
        ]);
    }
}
