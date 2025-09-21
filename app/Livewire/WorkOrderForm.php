<?php

namespace App\Livewire;

use App\Mail\SpvUserApproval;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\FunctionalLocation;
use App\Models\PlannerGroup;
use App\Models\Plant;
use App\Models\Resource;
use App\Models\User;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Title;
use Livewire\Component;

class WorkOrderForm extends Component
{
    #[Title('Form Maintenance Notification')]

    // search text
    public $plant_search = '';

    public $resource_search = '';

    public $func_search = '';

    public $equipment_search = '';

    // selected ids
    public $plant_id = null;

    public $resource_id = null;

    public $functional_location_id = null;

    public $equipment_id = null;

    // other fields
    public $planner_group_id = null;

    public $notification_number = null;

    public $work_desc = null;

    public $notification_date = null;

    public $malfunction_start = null;

    public $priority = 'low'; // default value

    public $notes = null;

    public $breakdown = false;

    public $req_dept_id = null;

    public $req_user_id = null;

    public $urgent_level = 'Produksi & Delivery'; // default value

    // options loaded once or computed
    public $planner_groups = [];

    public $departments = [];

    // limits
    public $perPage = 10;

    // show dropdown flags
    public $showPlantDropdown = false;

    public $showResourceDropdown = false;

    public $showFuncDropdown = false;

    public $showEquipmentDropdown = false;

    protected $rules = [
        'equipment_id' => 'required|integer',
        'planner_group_id' => 'required|integer',
        'notification_date' => 'required|date',
        'malfunction_start' => 'required|date',
        'priority' => 'required|string',
        'req_dept_id' => 'required|integer',
        'req_user_id' => 'required|integer',
        'urgent_level' => 'required|string',
        'notes' => 'required|string',
    ];

    protected $messages = [
        'plant_id.required' => 'Plant wajib dipilih',
        'plant_id.exists' => 'Plant yang dipilih tidak valid',
        'resource_id.required' => 'Resource wajib dipilih',
        'resource_id.exists' => 'Resource yang dipilih tidak valid',
        'functional_location_id.required' => 'Functional Location wajib dipilih',
        'functional_location_id.exists' => 'Functional Location yang dipilih tidak valid',
        'equipment_id.required' => 'Equipment wajib dipilih',
        'equipment_id.exists' => 'Equipment yang dipilih tidak valid',
        'planner_group_id.required' => 'Planner Group wajib dipilih',
        'planner_group_id.exists' => 'Planner Group yang dipilih tidak valid',
        'notification_number.required' => 'Notification Number wajib diisi',
        'notification_number.max' => 'Notification Number maksimal 255 karakter',
        'work_desc.required' => 'Notification Description wajib diisi',
        'notification_date.required' => 'Notification Date wajib diisi',
        'notification_date.after_or_equal' => 'Notification Date tidak boleh kurang dari hari ini',
        'malfunction_start.required' => 'Malfunction Start wajib diisi',
        'malfunction_start.before_or_equal' => 'Malfunction Start tidak boleh melebihi Notification Date',
        'priority.required' => 'Priority wajib dipilih',
        'priority.in' => 'Priority harus salah satu dari: low, medium, high',
        'req_dept_id.required' => 'Department Requester wajib dipilih',
        'req_dept_id.exists' => 'Department yang dipilih tidak valid',
        'req_user_id.required' => 'Requester Name wajib dipilih',
        'req_user_id.exists' => 'User yang dipilih tidak valid',
        'urgent_level.required' => 'Urgent Level wajib dipilih',
        'urgent_level.in' => 'Urgent Level tidak valid',
        'notes.required' => 'Notes wajib diisi',
        'notes.min' => 'Notes minimal 10 karakter',
        'notes.max' => 'Notes maksimal 1000 karakter',
    ];

    public function mount()
    {
        $this->planner_groups = PlannerGroup::orderBy('name')->get();
        $this->departments = Department::orderBy('name')->get();

        // Set default dates
        $this->notification_date = now()->format('Y-m-d\TH:i');
        $this->malfunction_start = now()->format('Y-m-d\TH:i');
    }

    // Updated methods for search
    public function updatedPlantSearch()
    {
        $this->showPlantDropdown = ! empty(trim($this->plant_search));
        if (empty(trim($this->plant_search))) {
            $this->plant_id = null;
            $this->resetDependentSelects();
        }
    }

    public function updatedResourceSearch()
    {
        $this->showResourceDropdown = ! empty(trim($this->resource_search)) && $this->plant_id;
        if (empty(trim($this->resource_search))) {
            $this->resource_id = null;
            $this->resetFuncAndEquipment();
        }
    }

    public function updatedFuncSearch()
    {
        $this->showFuncDropdown = ! empty(trim($this->func_search)) && $this->resource_id;
        if (empty(trim($this->func_search))) {
            $this->functional_location_id = null;
            $this->resetEquipment();
        }
    }

    public function updatedEquipmentSearch()
    {
        $this->showEquipmentDropdown = ! empty(trim($this->equipment_search)) && $this->functional_location_id;
        if (empty(trim($this->equipment_search))) {
            $this->equipment_id = null;
        }
    }

    public function updatedPlantId($value)
    {
        $this->resetDependentSelects();
    }

    public function updatedResourceId($value)
    {
        $this->resetFuncAndEquipment();
    }

    public function updatedFunctionalLocationId($value)
    {
        $this->resetEquipment();
    }

    public function updatedReqDeptId($value)
    {
        // reset requester when department changed
        $this->req_user_id = null;
    }

    // Fixed reset methods
    private function resetDependentSelects()
    {
        $this->resource_id = null;
        $this->resource_search = '';
        $this->showResourceDropdown = false;
        $this->resetFuncAndEquipment();
    }

    private function resetFuncAndEquipment()
    {
        $this->functional_location_id = null;
        $this->func_search = '';
        $this->showFuncDropdown = false;
        $this->resetEquipment();
    }

    private function resetEquipment()
    {
        $this->equipment_id = null;
        $this->equipment_search = '';
        $this->showEquipmentDropdown = false;
    }

    // Fixed computed properties
    public function getPlantsProperty()
    {
        if (empty(trim($this->plant_search))) {
            return collect();
        }

        return Plant::where('name', 'ilike', "%{$this->plant_search}%")
            ->orderBy('name')
            ->limit($this->perPage)
            ->get();
    }

    public function getResourcesProperty()
    {
        if (! $this->plant_id || empty(trim($this->resource_search))) {
            return collect();
        }

        return Resource::where('plant_id', $this->plant_id)
            ->where('name', 'ilike', "%{$this->resource_search}%")
            ->orderBy('name')
            ->limit($this->perPage)
            ->get();
    }

    public function getFunctionalLocationsProperty()
    {
        if (! $this->resource_id || empty(trim($this->func_search))) {
            return collect();
        }

        return FunctionalLocation::where('resources_id', $this->resource_id)
            ->where('name', 'ilike', "%{$this->func_search}%")
            ->orderBy('name')
            ->limit($this->perPage)
            ->get();
    }

    public function getEquipmentsProperty()
    {
        if (! $this->functional_location_id || empty(trim($this->equipment_search))) {
            return collect();
        }

        return Equipment::where('func_loc_id', $this->functional_location_id)
            ->where('name', 'ilike', "%{$this->equipment_search}%")
            ->orderBy('name')
            ->limit($this->perPage)
            ->get();
    }

    public function getRequestersProperty()
    {
        if (! $this->req_dept_id) {
            return collect();
        }

        return User::where('dept_id', $this->req_dept_id)->orderBy('name')->get();
    }

    // Fixed SPV Email computation
    public function getSpvEmailProperty()
    {

        // if (!$this->planner_group_id) {
        //     return null;
        // }

        // // Find user with matching planner_group_id and is SPV in their department
        // $user = User::where('planner_group_id', $this->planner_group_id)
        //            ->whereHas('department', function($query) {
        //                $query->whereColumn('spv_id', 'users.id');
        //            })
        //            ->first();

        // return $user ? $user->email : null;
        // dd(Auth::user()->dept_id);
        if (! $this->req_dept_id) {
            return null;
        }
        // $user = Department::find(Auth::user()->dept_id)->spv->email;
        $user = Department::find($this->req_dept_id)->spv;

        return $user ? $user->email : null;
    }

    // Fixed select methods
    public function selectPlant($id)
    {
        $plant = Plant::find($id);
        if ($plant) {
            $this->plant_id = $plant->id;
            $this->plant_search = $plant->name;
            $this->showPlantDropdown = false;
            $this->resetDependentSelects();
        }
    }

    public function selectResource($id)
    {
        $resource = Resource::find($id);
        if ($resource) {
            $this->resource_id = $resource->id;
            $this->resource_search = $resource->name;
            $this->showResourceDropdown = false;
            $this->resetFuncAndEquipment();
        }
    }

    public function selectFunctionalLocation($id)
    {
        $functionalLocation = FunctionalLocation::find($id);
        if ($functionalLocation) {
            $this->functional_location_id = $functionalLocation->id;
            $this->func_search = $functionalLocation->name;
            $this->showFuncDropdown = false;
            $this->resetEquipment();
        }
    }

    public function selectEquipment($id)
    {
        $equipment = Equipment::find($id);
        if ($equipment) {
            $this->equipment_id = $equipment->id;
            $this->equipment_search = $equipment->name;
            $this->showEquipmentDropdown = false;
        }
    }

    public function hideDropdowns()
    {
        $this->showPlantDropdown = false;
        $this->showResourceDropdown = false;
        $this->showFuncDropdown = false;
        $this->showEquipmentDropdown = false;
    }

    public function confirmSubmit()
    {
        $this->validate();
        $this->dispatch('confirmSubmit');
    }

    public function submit()
    {
        $this->validate();

        try {
            $workOrder = WorkOrder::create([
                'notification_number' => $this->notification_number,
                'work_desc' => $this->work_desc,
                'notification_date' => $this->notification_date ? Carbon::parse($this->notification_date) : null,
                'priority' => $this->priority,
                'malfunction_start' => $this->malfunction_start ? Carbon::parse($this->malfunction_start) : null,
                'equipment_id' => $this->equipment_id,
                'planner_group_id' => $this->planner_group_id,
                'is_breakdown' => (bool) $this->breakdown,
                'notes' => $this->notes,
                'req_dept_id' => $this->req_dept_id,
                'req_user_id' => $this->req_user_id,
                'urgent_level' => $this->urgent_level,
                'status' => 'Waiting for SPV Approval',
                'is_spv_rejected' => false,
                'spv_reject_reason' => null,
            ]);

            // Mailer

            $department = Department::where('id', $this->req_dept_id)->first();
            $email = $department->spv->email;
            $name = $department->spv->name;
            // update to general
            Mail::to($email)->send(new SpvUserApproval($name, route('work-order.spv-approval')));

            $this->dispatch('show-success-alert', [
                'title' => 'Submitted!',
                'message' => 'Form successfuly submitted',
                'redirect' => route('work-order'),
            ]);

        } catch (\Exception $e) {
            $this->dispatch('show-error-alert', [
                'title' => 'Error!',
                'message' => 'Error saving data: '.$e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.work-order-form', [
            'plants' => $this->plants,
            'resources' => $this->resources,
            'functionalLocations' => $this->functionalLocations,
            'equipments' => $this->equipments,
            'requesters' => $this->requesters,
            'spv_email' => $this->spvEmail,
        ]);
    }
}
