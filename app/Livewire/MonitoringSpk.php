<?php

namespace App\Livewire;

use App\Mail\SendGeneralMail;
use App\Models\ActivityList;
use App\Models\MaintenanceApproval;
use App\Models\Order;
use App\Models\Sparepart;
use App\Models\SparepartList;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class MonitoringSpk extends Component
{
    use WithPagination;

    #[Title('Monitoring SPK')]
    public $perPage = 10;

    public $search = '';

    public $reason = '';

    public $selectedWorkOrderId = null;

    public $selectedWorkOrder = null;

    public $popupModalAction = '';

    public $newTask = '';

    public $activityLists = [];

    public $editingTaskId = null;

    public $editingTaskName = '';

    public $sparepartItems = [];

    public $sparepartSearch = [];

    public $sparepartResults = [];

    public $isSearching = false;

    public $users = [];

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->loadDropdownData();
        $this->sparepartItems = [['sparepart_id' => '', 'quantity' => '']];
    }

    public function loadDropdownData()
    {
        $this->users = User::select('id', 'name', 'role_id')->get();
    }

    public function addSparepartItem()
    {
        $this->sparepartItems[] = ['sparepart_id' => '', 'quantity' => ''];
    }

    public function removeSparepartItem($index)
    {
        if ($index > 0 && count($this->sparepartItems) > 1) {
            unset($this->sparepartItems[$index]);
            $this->sparepartItems = array_values($this->sparepartItems);
        }
    }

    public function searchSpareparts($index, $query)
    {
        // Only search when 3+ characters
        if (strlen($query) < 3) {
            $this->sparepartResults[$index] = [];

            return;
        }

        $this->isSearching = true;

        // Get already selected sparepart IDs to exclude
        $selectedIds = array_filter(array_column($this->sparepartItems, 'sparepart_id'));

        $results = Sparepart::where(function ($q) use ($query) {
            $q->where('name', 'ilike', '%'.$query.'%')
                ->orWhere('code', 'ilike', '%'.$query.'%')
                ->orWhere('barcode', 'ilike', '%'.$query.'%');
        })
            ->whereNotIn('id', $selectedIds)
            ->limit(10)
            ->get(['id', 'code', 'name', 'uom']);

        $this->sparepartResults[$index] = $results->toArray();
        $this->isSearching = false;
    }

    // public function focusSparepart($index)
    // {
    //     // Only show initial results if input is empty
    //     if (empty($this->sparepartSearch[$index])) {
    //         $this->showInitialSpareparts($index);
    //     }
    // }

    // public function showInitialSpareparts($index)
    // {
    //     // Get already selected sparepart IDs to exclude
    //     $selectedIds = array_filter(array_column($this->sparepartItems, 'sparepart_id'));

    //     $results = Sparepart::whereNotIn('id', $selectedIds)
    //         ->limit(5)
    //         ->get(['id', 'code', 'name', 'uom']);

    //     $this->sparepartResults[$index] = $results->toArray();
    //     // Don't set isSearching to true here
    // }

    public function selectSparepart($index, $sparepartId)
    {
        $sparepart = Sparepart::find($sparepartId);
        if ($sparepart) {
            $this->sparepartItems[$index]['sparepart_id'] = $sparepartId;
            $this->sparepartSearch[$index] = $sparepart->code.' - '.$sparepart->name;
            $this->sparepartResults[$index] = []; // Clear dropdown
        }
    }

    public function hideSparepartDropdown($index)
    {
        $this->sparepartResults[$index] = [];
    }

    public function submitSparepart()
    {
        // Validation
        $validItems = array_filter($this->sparepartItems, function ($item) {
            return ! empty($item['sparepart_id']) && ! empty($item['quantity']);
        });

        if (empty($validItems)) {
            session()->flash('error', 'Please add at least one sparepart with quantity.');

            return;
        }

        // Dispatch confirmation instead of direct save
        $this->dispatch('confirmSparepartSubmit');
    }

    public function saveSparepartReservation()
    {
        $validItems = array_filter($this->sparepartItems, function ($item) {
            return ! empty($item['sparepart_id']) && ! empty($item['quantity']);
        });

        try {
            DB::beginTransaction();

            // Delete existing sparepart lists for this work order
            SparepartList::where('wo_id', $this->selectedWorkOrderId)->delete();

            // Create new sparepart lists
            foreach ($validItems as $item) {
                $sparepart = Sparepart::find($item['sparepart_id']);
                SparepartList::create([
                    'wo_id' => $this->selectedWorkOrderId,
                    'barcode' => $sparepart->barcode,
                    'qty' => $item['quantity'],
                    'uom' => $sparepart->uom,
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
        $this->sparepartResults = [];
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
        $approval->update(['progress' => $progress]);

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
        $this->selectedWorkOrder = WorkOrder::with(['equipment.functionalLocation.resource.plant', 'department', 'user', 'plannerGroup'])
            ->find($workOrderId);

        $this->loadActivityLists();
        $this->loadSparepartItems();
        $this->dispatch('showDetailModal');
    }

    public function loadSparepartItems()
    {
        $savedItems = SparepartList::where('wo_id', $this->selectedWorkOrderId)->get();

        if ($savedItems->isNotEmpty()) {
            $this->sparepartItems = [];
            $this->sparepartSearch = [];

            foreach ($savedItems as $item) {
                $sparepart = Sparepart::where('barcode', $item->barcode)->first();
                if ($sparepart) {
                    $this->sparepartItems[] = [
                        'sparepart_id' => $sparepart->id,
                        'quantity' => $item->qty,
                    ];
                    $this->sparepartSearch[] = $sparepart->code.' - '.$sparepart->name;
                }
            }
        } else {
            // Reset to default empty state
            $this->sparepartItems = [['sparepart_id' => '', 'quantity' => '']];
            $this->sparepartSearch = [];
        }

        $this->sparepartResults = [];
    }

    public function closeModal()
    {
        $this->selectedWorkOrderId = null;
        $this->selectedWorkOrder = null;
        $this->reason = '';
    }

    public function getSpvDetail($spvId)
    {
        return User::find($spvId);
    }

    public function changePlannerGroup()
    {
        dd('test');
    }

    public function closeWorkOrder()
    {
        if (! $this->selectedWorkOrderId || ! $this->reason) {
            session()->flash('error', 'Please provide close reason.');

            return;
        }

        $spv = $this->getSpvDetail($this->selectedWorkOrder->department->spv_id);
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
                'status' => 'Closed',
                'is_closed' => true,
                'progress' => '100',
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

    public function clearAllDropdowns()
    {
        foreach ($this->sparepartResults as $index => $result) {
            $this->sparepartResults[$index] = [];
        }
    }

    public function confirmClose()
    {
        $this->dispatch('confirmClose');
    }

    public function confirmChange()
    {
        $this->dispatch('confirmChange');
    }

    public function render()
    {
        $isSpv = Auth::user()->role_id == 3 ? true : false;
        $isUser = Auth::user()->role_id == 5 ? true : false;

        $query = WorkOrder::with(['equipment.functionalLocation.resource.plant', 'department', 'user'])
            ->leftJoin('maintenance_approvals', 'maintenance_approvals.wo_id', 'work_orders.id')
            // ->where('is_spv_rejected', 'false')
            ->when($isSpv, function ($q) {
                $q->where('req_dept_id', Auth::user()->dept_id);
            })
            ->when($isUser, function ($q) {
                $q->where('req_user_id', Auth::user()->id);
            })
            ->select('work_orders.*', 'maintenance_approvals.progress');

        // Department::where('id', Auth::user()->dept_id)->value('spv_id')

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

        $workOrders = $query->orderBy('work_orders.created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.monitoring-spk', [
            'workOrders' => $workOrders,
        ]);
    }
}
