<?php

namespace App\Livewire;

use App\Mail\SendGeneralMail;
use App\Models\ActivityList;
use App\Models\ActualManhour;
use App\Models\MaintenanceApproval;
use App\Models\SparepartList;
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

class AssignedSpk extends Component
{
    use WithPagination;

    #[Title('Assigned SPK')]
    public $perPage = 10;

    public $search = '';

    public $reason = '';

    public $selectedWorkOrderId = null;

    public $selectedApprovalId = null;

    public $selectedWorkOrder = null;

    public $popupModalAction = '';

    public $newTask = '';

    public $activeSessionUser = null; // Track user yang sedang running

    public $startTime = null; // Untuk tampilan durasi

    public $showAllWorkOrdersModal = false;

    public $availableWorkOrders = [];

    public $activityLists = [];

    public $editingTaskId = null;

    public $editingTaskName = '';

    public $sparepartItems = [];

    public $users = [];

    public $isSparepartReserved = null;

    public $isBeingWorked = null;

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->loadDropdownData();
        $this->sparepartItems = [['requested_sparepart' => '', 'quantity' => '', 'is_completed' => false]];
        $this->checkActiveSession();
    }

    public function openAllWorkOrdersModal()
    {
        $this->loadAvailableWorkOrders();
        $this->showAllWorkOrdersModal = true;
    }

    public function loadAvailableWorkOrders()
    {
        $userPlannerGroupId = Auth::user()->planner_group_id;

        $this->availableWorkOrders = WorkOrder::with([
            'equipment',
            'department',
            'maintenanceApproval.teamAssignments.user',
        ])
            ->where('planner_group_id', $userPlannerGroupId)
            ->whereIn('status', ['Planned', 'Need Revision'])
            ->whereHas('maintenanceApproval')
            ->get()
            ->map(function ($wo) {
                $pic = $wo->maintenanceApproval
                    ->teamAssignments
                    ->where('is_pic', true)
                    ->first();

                return [
                    'id' => $wo->id,
                    'notification_number' => $wo->notification_number,
                    'equipment' => $wo->equipment->name ?? '-',
                    'urgent_level' => $wo->urgent_level,
                    'department' => $wo->department->name ?? '-',
                    'pic_name' => $pic ? $pic->user->name : 'Not Assigned',
                    'approval_id' => $wo->maintenanceApproval->id,
                ];
            })
            ->toArray();
    }

    public function closeAllWorkOrdersModal()
    {
        $this->showAllWorkOrdersModal = false;
        $this->availableWorkOrders = [];
    }

    public function confirmAssignSelf($approvalId)
    {
        $this->selectedApprovalId = $approvalId;
        $this->dispatch('confirmAssignSelf');
    }

    public function assignSelfToWorkOrder()
    {
        try {
            DB::beginTransaction();

            // Double check user tidak punya assignment
            if ($this->hasActiveAssignment()) {
                session()->flash('error', 'You already have an active assignment.');
                DB::rollBack();

                return;
            }

            TeamAssignment::create([
                'approval_id' => $this->selectedApprovalId,
                'user_id' => Auth::user()->id,
                'is_pic' => false,
            ]);

            DB::commit();

            $this->closeAllWorkOrdersModal();
            $this->dispatch('closeAllModals');
            session()->flash('message', 'You have been assigned to the work order successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to assign: '.$e->getMessage());
        }
    }

    public function hasActiveAssignment()
    {
        // Cek apakah user punya assignment di WO yang belum closed
        return TeamAssignment::where('user_id', Auth::user()->id)
            ->whereHas('approval.workOrder', function ($q) {
                $q->whereNotIn('status', ['Closed', 'Rejected']);
            })
            ->exists();
    }

    public function canShowAllWorkOrderButton()
    {
        // Tombol muncul jika: tidak sedang start work DAN tidak punya assignment
        return ! $this->activeSessionUser && ! $this->hasActiveAssignment();
    }

    public function checkActiveSession()
    {
        // Cek apakah user sedang punya session aktif di WO MANAPUN
        $this->activeSessionUser = ActualManhour::where('user_id', Auth::user()->id)
            ->whereNull('stop_job')
            ->first();

        if ($this->activeSessionUser) {
            $this->startTime = $this->activeSessionUser->start_job;
        }
    }

    public function isUserPic($workOrderId)
    {
        $approval = MaintenanceApproval::where('wo_id', $workOrderId)->first();

        if (! $approval) {
            return false;
        }

        return TeamAssignment::where('approval_id', $approval->id)
            ->where('user_id', Auth::user()->id)
            ->where('is_pic', true)
            ->exists();
    }

    public function isUserAssignedToTeam($workOrderId)
    {
        $approval = MaintenanceApproval::where('wo_id', $workOrderId)->first();

        if (! $approval) {
            return false;
        }

        return TeamAssignment::where('approval_id', $approval->id)
            ->where('user_id', Auth::user()->id)
            ->exists();
    }

    public function confirmStart($workOrderId)
    {
        // Validasi user harus di-assign ke team
        if (! $this->isUserAssignedToTeam($workOrderId)) {
            session()->flash('error', 'You are not assigned to this work order team.');

            return;
        }

        // Validasi user tidak boleh punya active session di WO manapun
        $activeSession = ActualManhour::where('user_id', Auth::user()->id)
            ->whereNull('stop_job')
            ->first();

        if ($activeSession) {
            $activeWO = WorkOrder::find($activeSession->wo_id);
            session()->flash('error', 'You have an active session on Work Order: '.$activeWO->notification_number.'. Please stop it first.');

            return;
        }

        $this->selectedWorkOrderId = $workOrderId;
        $this->dispatch('confirmStartManhour');
    }

    public function startManhour()
    {
        try {
            DB::beginTransaction();

            ActualManhour::create([
                'user_id' => Auth::user()->id,
                'wo_id' => $this->selectedWorkOrderId,
                'start_job' => now(),
                // shift dan date akan diset otomatis oleh mutator
            ]);

            DB::commit();

            $this->checkActiveSession();
            $this->dispatch('closeAllModals');
            session()->flash('message', 'Work session started successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to start work session: '.$e->getMessage());
        }
    }

    public function confirmStop()
    {
        if (! $this->activeSessionUser) {
            session()->flash('error', 'No active session found.');

            return;
        }

        $this->dispatch('confirmStopManhour');
    }

    public function stopManhour()
    {
        try {
            DB::beginTransaction();

            $session = ActualManhour::where('user_id', Auth::user()->id)
                ->whereNull('stop_job')
                ->where('wo_id', $this->activeSessionUser->wo_id)
                ->first();

            if (! $session) {
                session()->flash('error', 'No active session found.');

                return;
            }

            $session->update([
                'stop_job' => now(),
                // actual_time akan dihitung otomatis oleh mutator
            ]);

            DB::commit();

            $this->activeSessionUser = null;
            $this->startTime = null;
            $this->dispatch('closeAllModals');
            session()->flash('message', 'Work session stopped successfully. Duration: '.$this->formatDuration($session->actual_time).'.');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to stop work session: '.$e->getMessage());
        }
    }

    public function formatDuration($minutes)
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        return $hours.' hour(s) '.$mins.' minute(s)';
    }

    public function getActiveDuration()
    {
        if (! $this->startTime) {
            return '';
        }

        $start = \Carbon\Carbon::parse($this->startTime);
        $now = \Carbon\Carbon::now();

        $diffInMinutes = $start->diffInMinutes($now);
        $hours = floor($diffInMinutes / 60);
        $minutes = $diffInMinutes % 60;

        return "{$hours}h {$minutes}m";
    }

    public function loadDropdownData()
    {
        $this->users = User::select('id', 'name', 'role_id')->get();
    }

    public function addSparepartItem()
    {
        $this->sparepartItems[] = [
            'requested_sparepart' => '',
            'quantity' => '',
            'is_completed' => false,
        ];
    }

    public function removeSparepartItem($index)
    {
        // Cek apakah item sudah completed
        if (isset($this->sparepartItems[$index]['is_completed']) &&
            $this->sparepartItems[$index]['is_completed']) {
            session()->flash('error', 'Cannot remove completed sparepart.');

            return;
        }

        unset($this->sparepartItems[$index]);
        $this->sparepartItems = array_values($this->sparepartItems);
    }

    public function submitSparepart()
    {
        // Validation - hanya validasi item yang belum completed
        $validItems = array_filter($this->sparepartItems, function ($item) {
            $isCompleted = isset($item['is_completed']) && $item['is_completed'];

            return ! $isCompleted && ! empty($item['requested_sparepart']) && ! empty($item['quantity']);
        });

        if (empty($validItems)) {
            session()->flash('error', 'Please add at least one new sparepart with quantity.');

            return;
        }

        // Dispatch confirmation instead of direct save
        $this->dispatch('confirmSparepartSubmit');
    }

    public function saveSparepartReservation()
    {
        // Filter hanya item yang belum completed
        $validItems = array_filter($this->sparepartItems, function ($item) {
            $isCompleted = isset($item['is_completed']) && $item['is_completed'];

            return ! $isCompleted && ! empty($item['requested_sparepart']) && ! empty($item['quantity']);
        });

        try {
            DB::beginTransaction();

            // Create new sparepart lists (hanya yang baru, skip yang sudah ada ID)
            foreach ($validItems as $item) {
                if (isset($item['id'])) {
                    continue; // Skip item yang sudah tersimpan sebelumnya
                }

                SparepartList::create([
                    'wo_id' => $this->selectedWorkOrderId,
                    'barcode' => null,
                    'requested_sparepart' => $item['requested_sparepart'],
                    'qty' => $item['quantity'],
                    'uom' => null,
                    'is_completed' => false,
                    'planner_group_id' => $this->selectedWorkOrder->planner_group_id,
                ]);
            }

            DB::commit();

            $this->resetSparepartModal();
            $this->dispatch('closeAllModals');
            session()->flash('message', 'Sparepart reservation saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred while saving sparepart reservation.');
        }
    }

    public function resetSparepartModal()
    {
        $this->loadSparepartItems();
    }

    public function loadActivityLists()
    {
        if ($this->selectedWorkOrderId) {
            $approval = MaintenanceApproval::where('wo_id', $this->selectedWorkOrderId)->first();
            if ($approval) {
                $this->activityLists = ActivityList::where('approval_id', $approval->id)->get()->toArray();
            } else {
                $this->activityLists = [];
            }
        }
    }

    public function addNewTask()
    {
        if (trim($this->newTask) === '') {
            return;
        }

        $approval = MaintenanceApproval::where('wo_id', $this->selectedWorkOrderId)->first();
        if (! $approval) {
            session()->flash('error', 'Maintenance approval not found.');

            return;
        }

        ActivityList::create([
            'approval_id' => $approval->id,
            'task' => trim($this->newTask),
            'is_done' => false,
            'planner_group_id' => $this->selectedWorkOrder->planner_group_id,
        ]);

        $this->newTask = '';
        $this->loadActivityLists();
    }

    public function toggleTask($taskId)
    {
        $task = ActivityList::find($taskId);
        if ($task) {
            $task->update(['is_done' => ! $task->is_done]);
            $this->loadActivityLists();
        }
    }

    public function editTask($taskId)
    {
        $task = ActivityList::find($taskId);
        if ($task) {
            $this->editingTaskId = $taskId;
            $this->editingTaskName = $task->task;
        }
    }

    public function updateTaskName()
    {
        if (trim($this->editingTaskName) === '') {
            return;
        }

        $task = ActivityList::find($this->editingTaskId);
        if ($task) {
            $task->update(['task' => trim($this->editingTaskName)]);
            $this->editingTaskId = null;
            $this->editingTaskName = '';
            $this->loadActivityLists();
        }
    }

    public function cancelEditTask()
    {
        $this->editingTaskId = null;
        $this->editingTaskName = '';
    }

    public function deleteTask($taskId)
    {
        ActivityList::destroy($taskId);
        $this->loadActivityLists();
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

    public function updateProgress()
    {
        $approval = MaintenanceApproval::where('wo_id', $this->selectedWorkOrderId)->first();
        if (! $approval) {
            session()->flash('error', 'Maintenance approval not found.');

            return;
        }

        $progress = $this->calculateProgress();
        $approval->update([
            'progress' => $progress,
        ]);

        $this->dispatch('closeAllModals');
        session()->flash('message', 'Progress updated successfully.');
    }

    public function confirmProgress()
    {
        $this->dispatch('confirmProgress');
    }

    public function resetProgressModal()
    {
        $this->newTask = '';
        $this->editingTaskId = null;
        $this->editingTaskName = '';
    }

    public function resetCloseModal()
    {
        $this->reason = '';
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
        $this->isSparepartReserved = WorkOrder::where('id', $workOrderId)->whereHas('sparepartList')->exists();
        $this->isBeingWorked = WorkOrder::where('id', $workOrderId)->whereHas('actualManhours')->exists();
        $this->selectedWorkOrder = WorkOrder::with(['equipment.functionalLocation.resource.plant', 'department', 'user', 'plannerGroup'])
            ->find($workOrderId);
        $this->loadActivityLists();
        $this->loadSparepartItems();
        $this->checkActiveSession();
        $this->dispatch('showDetailModal');
    }

    public function loadSparepartItems()
    {
        $savedItems = SparepartList::where('wo_id', $this->selectedWorkOrderId)
            ->whereNotNull('requested_sparepart')
            ->get();

        if ($savedItems->isNotEmpty()) {
            $this->sparepartItems = [];

            foreach ($savedItems as $item) {
                $this->sparepartItems[] = [
                    'id' => $item->id,
                    'requested_sparepart' => $item->requested_sparepart,
                    'quantity' => $item->qty,
                    'is_completed' => $item->is_completed ?? false,
                ];
            }
        } else {
            // Reset to default empty state
            $this->sparepartItems = [['requested_sparepart' => '', 'quantity' => '', 'is_completed' => false]];
        }
    }

    public function closeModal()
    {
        $this->selectedWorkOrderId = null;
        $this->selectedWorkOrder = null;
        $this->isSparepartReserved = null;
        $this->isBeingWorked = null;
        $this->reason = '';
    }

    public function getSpvDetail($spvId)
    {
        return User::find($spvId);
    }

    public function confirmChange()
    {
        $this->dispatch('confirmChange');
    }

    public function changePlannerGroup()
    {
        $approvalId = $this->selectedWorkOrder->maintenanceApproval->id;
        $currentPgId = $this->selectedWorkOrder->planner_group_id;
        $targetPgId = $currentPgId == 1 ? 2 : 1;

        WoPlannerGroup::where('approval_id', $approvalId)
            ->where('planner_group_id', $currentPgId)
            ->update(['status' => 'Inactive']);

        WorkOrder::where('id', $this->selectedWorkOrderId)
            ->update(['status' => 'Requested to change planner group']);

        $spv = User::where('dept_id', 1)
            ->where('role_id', 3)
            ->where('planner_group_id', $targetPgId)
            ->first();

        if ($spv && $spv->email) {
            Mail::to($spv->email)->send(
                new SendGeneralMail(
                    sapaan: 'Dear',
                    nama: $spv->name,
                    isi: "Ada request untuk perubahan planner group yang perlu persetujuan anda.\nKlik tombol di bawah untuk melihat detail.",
                    link: route('work-order.spv-approval'),
                    penutup: '[This message is generated by system]'
                )
            );
        }

        $this->dispatch('closeAllModals');
        session()->flash('message', "Work Order's change planner group request successfully submitted");
    }

    public function closeWorkOrder()
    {
        if (! $this->selectedWorkOrderId || ! $this->reason) {
            session()->flash('error', 'Please provide close reason.');

            return;
        }
        if ($this->selectedWorkOrder->maintenanceApproval->progress != 100) {
            $this->dispatch('unfinished');

            return;
        }

        // $spv = $this->getSpvDetail($this->selectedWorkOrder->department->spv_id);
        $spv = User::where('dept_id', 1)
            ->where('role_id', 3)
            ->where('planner_group_id', $this->selectedWorkOrder->planner_group_id)
            ->first();

        if ($spv && $spv->email) {
            Mail::to($spv->email)->send(
                new SendGeneralMail(
                    sapaan: 'Dear',
                    nama: $spv->name,
                    isi: "Maintenance telah selesai dilaksanakan.\nKlik tombol di bawah untuk melihat detail.",
                    link: route('work-order.spv-approval'),
                    penutup: '[This message is generated by system]'
                )
            );
        }
        $workOrder = WorkOrder::find($this->selectedWorkOrderId);
        $approval = MaintenanceApproval::find($this->selectedWorkOrderId);

        if ($approval) {
            $approval->updated([
                'status' => 'Requested to be closed',
            ]);
        }

        if ($workOrder) {
            $workOrder->update([
                'status' => 'Requested to be closed',
            ]);
            $this->reason = '';

            $this->dispatch('closeAllModals');
            session()->flash('message', "Work Order's close request successfully submitted");
        }
    }

    public function confirmClose()
    {
        $this->dispatch('confirmClose');
    }

    public function render()
    {
        $userId = Auth::user()->id;

        $query = WorkOrder::with(['equipment.functionalLocation.resource.plant', 'department', 'user'])
            ->where('is_spv_rejected', 'false')
            ->whereIn('status', ['Planned', 'Need Revision'])
            ->whereHas('maintenanceApproval.teamAssignments', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });

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

        $workOrders = $query->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.assigned-spk', [
            'workOrders' => $workOrders,
        ]);
    }
}
