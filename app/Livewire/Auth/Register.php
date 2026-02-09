<?php

namespace App\Livewire\Auth;

use App\Models\Department;
use App\Models\PlannerGroup;
use App\Models\Role;
use App\Models\RoleAssignment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Register User')]
class Register extends Component
{
    public $subTitle = 'Register a new user';

    public $name;

    public $dept_id;

    public $nup;

    public $email;

    public $company;

    public $role_id;

    public $departments = [];

    public $roles = [];

    public $showEditModal = false;

    public $section;

    public $edit_section;

    public $editUserId;

    public $edit_name;

    public $edit_email;

    public $edit_nup;

    public $edit_dept_id;

    public $edit_company;

    public $edit_role_id;

    public $planner_group_id;

    public $planner_groups = [];

    public $edit_planner_group_id;

    public function mount()
    {
        $this->departments = Department::orderBy('name')->get();
        $this->roles = Role::orderBy('name')->get();
        $this->planner_groups = PlannerGroup::orderBy('name')->get();
    }

    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:100',
            'dept_id' => 'required|integer',
            'section' => 'required|string|max:15',
            'nup' => 'required|string|max:20|unique:users',
            'email' => 'required|email|max:50|unique:users',
            'company' => 'required|string|max:50',
            'role_id' => 'required|integer',
        ];

        // TAMBAHAN BARU - Conditional validation untuk planner_group_id
        if ($this->dept_id == 1) {
            $rules['planner_group_id'] = 'required|integer';
        }

        return $rules;
    }

    public function assignRole($userId, $roleId, $deptId)
    {
        // dd($userId, $roleId, $deptId);
        try {
            $existingUser = User::where('role_id', $roleId)
                ->where('dept_id', $deptId)
                ->first() ?? null;

            if ($existingUser && $existingUser->id != $userId) {
                $existingUser->update(['role_id' => 4]);
            }
            RoleAssignment::create([
                'user_id' => $userId,
                'role_id' => $roleId,
            ]);

            if ($roleId == 2) {
                Department::findOrFail($deptId)->update([
                    'manager_id' => $userId,
                ]);
            } elseif ($roleId == 3) {
                Department::findOrFail($deptId)->update([
                    'spv_id' => $userId,
                ]);

            } elseif ($roleId == 4) {
                Department::findOrFail($deptId)->update([
                    'pic_id' => $userId,
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'title' => 'Error!',
                'message' => 'Failed to assign role. '.$e->getMessage(),
                'icon' => 'error',
            ]);
        }

    }

    public function register()
    {
        $this->validate();
        // dd($this->role_id);

        try {
            $user = User::create([
                'name' => $this->name,
                'dept_id' => $this->dept_id,
                'section' => $this->section,
                'nup' => $this->nup,
                'email' => $this->email,
                'company' => $this->company,
                'role_id' => $this->role_id,
                'planner_group_id' => $this->dept_id == 1 ? $this->planner_group_id : null, // TAMBAHAN BARU
                'is_default_password' => true,
                'password' => bcrypt($this->nup),
            ]);

            if ($this->role_id == 2 || $this->role_id == 3) {
                // Assign role to user if needed
                $this->assignRole($user->id, $this->role_id, $this->dept_id);
            }
            // Reset form fields
            $this->reset(['name', 'dept_id', 'nup', 'email', 'company', 'role_id', 'section', 'planner_group_id']);
            $this->resetValidation();

            // Dispatch success event for SweetAlert
            $this->dispatch('userCreated', [
                'title' => 'Success!',
                'message' => 'User registered successfully.',
                'icon' => 'success',
            ]);

            $this->dispatch('registerModalClosed');

            // Auto login (if this is for registration page)
            // Auth::login($user, true);
            // return redirect()->route('dashboard');

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
        $user = User::findOrFail($id);
        $this->editUserId = $user->id;
        $this->edit_name = $user->name;
        $this->edit_email = $user->email;
        $this->edit_nup = $user->nup;
        $this->edit_dept_id = $user->dept_id;
        $this->edit_section = $user->section;
        $this->edit_company = $user->company;
        $this->edit_role_id = $user->role_id;
        $this->edit_planner_group_id = $user->planner_group_id;
        $this->showEditModal = true;
    }

    public function confirmUpdateUser()
    {
        $rules = [
            'edit_name' => 'required|string|max:100',
            'edit_email' => 'required|email|max:50|unique:users,email,'.$this->editUserId,
            'edit_nup' => 'required|string|max:20|unique:users,nup,'.$this->editUserId,
            'edit_dept_id' => 'required|integer',
            'edit_section' => 'required|string|max:15',
            'edit_company' => 'required|string|max:50',
            'edit_role_id' => 'required|integer',
        ];

        // TAMBAHAN BARU - Conditional validation untuk edit
        if ($this->edit_dept_id == 1) {
            $rules['edit_planner_group_id'] = 'required|integer';
        }

        $this->validate($rules);

        // Dispatch confirmation dialog
        $this->dispatch('confirmUpdate');
    }

    public function updateUser()
    {

        try {
            $user = User::findOrFail($this->editUserId);
            if ($this->edit_role_id == 2 || $this->edit_role_id == 3) {
                // Assign role to user if needed

                $this->assignRole($user->id, $this->edit_role_id, $this->edit_dept_id);
            }
            $user->update([
                'name' => $this->edit_name,
                'email' => $this->edit_email,
                'nup' => $this->edit_nup,
                'dept_id' => $this->edit_dept_id,
                'section' => $this->edit_section,
                'company' => $this->edit_company,
                'role_id' => $this->edit_role_id,
                'planner_group_id' => $this->edit_dept_id == 1 ? $this->edit_planner_group_id : null, // TAMBAHAN BARU
            ]);

            $this->closeRegisterModal();

            $this->dispatch('userUpdated', [
                'title' => 'Updated!',
                'message' => 'User updated successfully.',
                'icon' => 'success',
            ]);
        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'title' => 'Error!',
                'message' => 'Failed to update user. Please try again. '.$e->getMessage(),
                'icon' => 'error',
            ]);
        }
    }

    public function delete($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->role_id = 4; // Set to 'Guest' role
            $user->save();
            Department::where('manager_id', $id)->update(['manager_id' => null]);
            Department::where('pic_id', $id)->update(['pic_id' => null]);
            Department::where('spv_id', $id)->update(['spv_id' => null]);

            $user->delete();

            $this->dispatch('userDeleted', [
                'title' => 'Deleted!',
                'message' => 'User deleted successfully.',
                'icon' => 'success',
            ]);
        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'title' => 'Error!',
                'message' => 'Failed to delete user. Please try again.',
                'icon' => 'error',
            ]);
        }
    }

    public function resetPassword($userId)
    {
        try {
            $user = User::findOrFail($userId);
            $user->password = bcrypt($user->nup);
            $user->is_default_password = true;
            $user->save();

            $this->dispatch('userPasswordReset', [
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

    public function closeRegisterModal()
    {
        $this->showEditModal = false;
        $this->reset(['editUserId', 'edit_name', 'edit_email', 'edit_nup', 'edit_dept_id', 'edit_section', 'edit_company', 'edit_role_id', 'edit_planner_group_id']); // TAMBAH 'edit_planner_group_id'
        $this->resetValidation();
        $this->dispatch('registerModalClosed');
    }

    public function updatedDeptId()
    {
        if ($this->dept_id != 1) {
            $this->planner_group_id = null;
        }
        $this->resetValidation(['planner_group_id']);
        // dd('tst');
        $this->dispatch('reinitTable');
    }

    public function updatedEditDeptId()
    {
        if ($this->edit_dept_id != 1) {
            $this->edit_planner_group_id = null;
        }
        $this->resetValidation(['edit_planner_group_id']);
    }

    public function render()
    {
        return view('livewire.auth.register', [
            'users' => User::where('role_id', '!=', 1)->get(),
        ]);
    }
}
