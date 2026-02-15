<?php

namespace App\Livewire;

use App\Models\ActivityList;
use App\Models\PreventiveMaintenance as PreventiveMaintenanceModel;
use App\Models\Sparepart;
use App\Models\SparepartList;
use Illuminate\Support\Facades\Auth;
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

    // Sparepart properties
    public $sparepartItems = [];

    public $sparepartSearch = [];

    public $sparepartResults = [];

    // Reschedule property
    public $rescheduleDate = '';

    public $showRescheduleModal = false;

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->sparepartItems = [['sparepart_id' => '', 'quantity' => '']];
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
        $this->loadSparepartItems();
        $this->dispatch('showDetailModal');
    }

    public function closeModal()
    {
        $this->selectedPmId = null;
        $this->selectedPm = null;
        $this->rescheduleDate = '';
        $this->showRescheduleModal = false;
        $this->resetActivityModal();
        $this->resetSparepartModal();
    }

    // ==================== SPAREPART METHODS ====================

    public function addSparepartItem()
    {
        $this->sparepartItems[] = ['sparepart_id' => '', 'quantity' => ''];
    }

    public function removeSparepartItem($index)
    {
        unset($this->sparepartItems[$index]);
        unset($this->sparepartResults[$index]);
        unset($this->sparepartSearch[$index]);
        $this->sparepartItems = array_values($this->sparepartItems);
    }

    public function searchSpareparts($index, $query)
    {
        if (strlen($query) < 3) {
            $this->sparepartResults[$index] = [];

            return;
        }

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
    }

    public function selectSparepart($index, $sparepartId)
    {
        $sparepart = Sparepart::find($sparepartId);
        if ($sparepart) {
            $this->sparepartItems[$index]['sparepart_id'] = $sparepartId;
            $this->sparepartSearch[$index] = $sparepart->code.' - '.$sparepart->name;
            $this->sparepartResults[$index] = [];
        }
    }

    public function hideSparepartDropdown($index)
    {
        $this->sparepartResults[$index] = [];
    }

    public function submitSparepart()
    {
        $validItems = array_filter($this->sparepartItems, function ($item) {
            return ! empty($item['sparepart_id']) && ! empty($item['quantity']);
        });

        if (empty($validItems)) {
            session()->flash('error', 'Please add at least one sparepart with quantity.');

            return;
        }

        $this->dispatch('confirmSparepartSubmit');
    }

    public function saveSparepartReservation()
    {
        $validItems = array_filter($this->sparepartItems, function ($item) {
            return ! empty($item['sparepart_id']) && ! empty($item['quantity']);
        });

        try {
            DB::beginTransaction();

            foreach ($validItems as $item) {
                $sparepart = Sparepart::find($item['sparepart_id']);
                SparepartList::create([
                    'pm_id' => $this->selectedPmId,
                    'barcode' => $sparepart->barcode,
                    'qty' => $item['quantity'],
                    'uom' => $sparepart->uom,
                    'is_completed' => false,
                    'planner_group_id' => null,
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

    public function loadSparepartItems()
    {
        $savedItems = SparepartList::where('pm_id', $this->selectedPmId)->get();

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
            $this->sparepartItems = [['sparepart_id' => '', 'quantity' => '']];
            $this->sparepartSearch = [];
        }

        $this->sparepartResults = [];
    }

    public function resetSparepartModal()
    {
        $this->loadSparepartItems();
        $this->sparepartResults = [];
    }

    // ==================== ACTIVITY METHODS ====================

    public function loadActivityLists()
    {
        if ($this->selectedPmId) {
            $this->activityLists = ActivityList::where('pm_id', $this->selectedPmId)->get()->toArray();
        }
    }

    public function addNewTask()
    {
        if (trim($this->newTask) === '') {
            return;
        }

        ActivityList::create([
            'pm_id' => $this->selectedPmId,
            'task' => trim($this->newTask),
            'is_done' => false,
            'planner_group_id' => null,
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

    public function confirmActivityUpdate()
    {
        $this->dispatch('confirmActivityUpdate');
    }

    public function updateActivity()
    {
        $this->dispatch('closeAllModals');
        session()->flash('message', 'Activity updated successfully.');
    }

    public function resetActivityModal()
    {
        $this->newTask = '';
        $this->editingTaskId = null;
        $this->editingTaskName = '';
    }

    // ==================== RESCHEDULE METHOD ====================

    public function openRescheduleModal()
    {
        $this->rescheduleDate = $this->selectedPm->basic_start_date
            ? $this->selectedPm->basic_start_date->format('Y-m-d')
            : now()->format('Y-m-d');
        $this->showRescheduleModal = true;
        $this->dispatch('showRescheduleModal');
    }

    public function closeRescheduleModal()
    {
        $this->rescheduleDate = '';
        $this->showRescheduleModal = false;
        $this->dispatch('closeRescheduleModal');
    }

    public function confirmReschedule()
    {
        // Validasi tanggal tidak boleh kosong
        if (empty($this->rescheduleDate)) {
            session()->flash('error', 'Please select a reschedule date.');

            return;
        }

        // Validasi tanggal tidak boleh yang sudah lewat
        $selectedDate = \Carbon\Carbon::parse($this->rescheduleDate);
        $today = \Carbon\Carbon::today();

        if ($selectedDate->lt($today)) {
            session()->flash('error', 'Reschedule date cannot be in the past.');

            return;
        }

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

            $pm->update([
                'basic_start_date' => $this->rescheduleDate,
                'user_status' => 'RESCHEDULED',
            ]);

            $this->selectedPm = $pm;

            DB::commit();

            $this->closeRescheduleModal();
            $this->dispatch('closeAllModals');
            session()->flash('message', 'Preventive Maintenance rescheduled successfully to '.
                \Carbon\Carbon::parse($this->rescheduleDate)->format('d-m-Y').'.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred while rescheduling: '.$e->getMessage());
        }
    }

    // ==================== START/STOP METHODS ====================

    public function confirmStart()
    {
        if (! $this->canStart()) {
            session()->flash('error', 'Cannot start this preventive maintenance.');

            return;
        }

        $this->dispatch('confirmStart');
    }

    public function startMaintenance()
    {
        try {
            DB::beginTransaction();

            $pm = PreventiveMaintenanceModel::find($this->selectedPmId);

            if (! $pm) {
                throw new \Exception('Preventive Maintenance not found.');
            }

            if (! $this->canStart()) {
                throw new \Exception('This preventive maintenance has already been started.');
            }

            $now = now();
            $pm->update([
                'actual_start_date' => $now->toDateString(),
                'actual_start_time' => $now->format('H:i'),
                'entered_by' => Auth::user()->name,
                'user_status' => 'ON PROGRESS',
            ]);

            $this->selectedPm = $pm;

            DB::commit();

            $this->dispatch('closeAllModals');
            session()->flash('message', 'Preventive Maintenance started successfully at '.$now->format('d-m-Y H:i').'.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred while starting maintenance: '.$e->getMessage());
        }
    }

    public function confirmStop()
    {
        if (! $this->canStop()) {
            session()->flash('error', 'Cannot stop this preventive maintenance.');

            return;
        }

        $this->dispatch('confirmStop');
    }

    public function stopMaintenance()
    {
        try {
            DB::beginTransaction();

            $pm = PreventiveMaintenanceModel::find($this->selectedPmId);

            if (! $pm) {
                throw new \Exception('Preventive Maintenance not found.');
            }

            if (! $this->canStop()) {
                throw new \Exception('This preventive maintenance has not been started or already completed.');
            }

            $pm->update([
                'actual_finish' => now(),
                'user_status' => 'COMPLETED',
            ]);

            $this->selectedPm = $pm;

            DB::commit();

            $this->dispatch('closeAllModals');
            session()->flash('message', 'Preventive Maintenance completed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred while stopping maintenance: '.$e->getMessage());
        }
    }

    // ==================== HELPER METHODS ====================

    public function isCompleted()
    {
        return $this->selectedPm &&
               $this->selectedPm->user_status === 'COMPLETED';
    }

    public function isRescheduled()
    {
        return $this->selectedPm &&
               $this->selectedPm->user_status === 'RESCHEDULED';
    }

    public function isOnProgress()
    {
        return $this->selectedPm &&
               $this->selectedPm->user_status === 'ON PROGRESS';
    }

    public function canReschedule()
    {
        return $this->selectedPm &&
               $this->selectedPm->actual_start_date === null &&
               ! $this->isCompleted();
    }

    public function canStart()
    {
        return $this->selectedPm &&
               $this->selectedPm->actual_start_date === null &&
               ! $this->isCompleted();
    }

    public function canStop()
    {
        return $this->selectedPm &&
               $this->selectedPm->actual_start_date !== null &&
               $this->selectedPm->actual_finish === null &&
               ! $this->isCompleted();
    }

    public function getUserStatusBadgeClass($status)
    {
        return match (strtoupper($status ?? '')) {
            'COMPLETED' => 'badge-success',
            'RESCHEDULED' => 'badge-info',
            'ON PROGRESS' => 'badge-primary',
            default => 'badge-warning',
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
