<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Order;
use App\Models\Mat;
use Livewire\Component;
use App\Models\WorkOrder;
use Livewire\WithPagination;
use App\Mail\SendGeneralMail;
use Livewire\Attributes\Title;
use App\Models\MaintenanceApproval;
use App\Models\TeamAssignment;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\SendMaintenanceApproval;

class AssignedSpk extends Component
{
    use WithPagination;

    #[Title('Assigned SPK')]

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
    public $orderTypes = [];
    public $mats = [];
    public $users = [];

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->loadDropdownData();
        $this->teamMembers = [''];
    }

    public function loadDropdownData()
    {
        $this->orderTypes = Order::all();
        $this->users = User::select('id', 'name', 'role_id')->get();
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

    public function updatedSelectedPic()
    {
        // Remove PIC from team members if selected
        if ($this->selectedPic) {
            $this->teamMembers = array_filter($this->teamMembers, function ($member) {
                return $member != $this->selectedPic;
            });
            // Ensure at least one empty slot
            if (empty($this->teamMembers)) {
                $this->teamMembers = [''];
            }
        }
    }

    public function addTeamMember()
    {
        if (count($this->teamMembers) < 99) {
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
        $this->selectedWorkOrder = WorkOrder::with(['equipment.functionalLocation.resource.plant', 'department', 'user', 'plannerGroup'])
            ->find($workOrderId);
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
        if (!$this->selectedWorkOrderId || !$this->reason) {
            session()->flash('error', 'Please provide approval reason.');
            return;
        }

        $spv = $this->getSpvDetail($this->selectedWorkOrder->department->spv_id);

        $workOrder = WorkOrder::find($this->selectedWorkOrderId);
        if ($workOrder) {
            $workOrder->update([
                'status' => 'Waiting for Maintenance Approval',
                'is_spv_rejected' => false,
                'spv_reject_reason' => null,
                'spv_approve_reason' => $this->reason,
            ]);
            $this->reason = '';

            $this->dispatch('closeAllModals');
            session()->flash('message', 'Work Order approved successfully.');
        }
    }

    public function approveMaintenance()
    {
        // Validation
        $errors = [];

        if (!$this->selectedOrderType) {
            $errors[] = 'Order Type is required.';
        }

        if (!$this->selectedMat) {
            $errors[] = 'Maintenance Activity Type is required.';
        }

        if (!$this->selectedPic) {
            $errors[] = 'PIC is required.';
        }

        if (!$this->startDateTime) {
            $errors[] = 'Start Date Time is required.';
        }

        if (!$this->finishDateTime) {
            $errors[] = 'Finish Date Time is required.';
        }

        // Filter out empty team members and validate
        $validTeamMembers = array_filter($this->teamMembers, function ($member) {
            return !empty($member);
        });

        if (empty($validTeamMembers)) {
            $errors[] = 'At least one team member is required.';
        }

        // Check if PIC is in team members
        if ($this->selectedPic && in_array($this->selectedPic, $validTeamMembers)) {
            $errors[] = 'PIC cannot be a team member.';
        }

        if (!empty($errors)) {
            session()->flash('error', implode(' ', $errors));
            return;
        }

        try {
            DB::beginTransaction();

            // Create or update maintenance approval
            $maintenanceApproval = MaintenanceApproval::updateOrCreate(
                ['wo_id' => $this->selectedWorkOrderId],
                [
                    'mat_id' => $this->selectedMat,
                    'start' => $this->startDateTime,
                    'finish' => $this->finishDateTime,
                    'is_received' => true,
                    'is_rejected' => false,
                    'reject_reason' => null,
                ]
            );

            // Clear existing team assignments for this approval
            TeamAssignment::where('approval_id', $maintenanceApproval->id)->delete();

            // Create team assignment for PIC
            TeamAssignment::create([
                'approval_id' => $maintenanceApproval->id,
                'user_id' => $this->selectedPic,
                'is_pic' => true,
            ]);

            // Create team assignments for team members
            foreach ($validTeamMembers as $memberId) {
                TeamAssignment::create([
                    'approval_id' => $maintenanceApproval->id,
                    'user_id' => $memberId,
                    'is_pic' => false,
                ]);
            }

            // Update work order status
            $this->selectedWorkOrder->update([
                'status' => 'Planned'
            ]);

            // Send email to SPV
            $spv = $this->getSpvDetail($this->selectedWorkOrder->department->spv_id);
            if ($spv && $spv->email) {
                Mail::to($spv->email)->send(
                    new SendGeneralMail(
                        sapaan: 'Dear',
                        nama: $spv->name,
                        isi: "SPK Maintenance telah diapprove dan dijadwalkan.\nKlik tombol di bawah untuk melihat detail.",
                        link: route('work-order.spv-approval'),
                        penutup: '[This message is generated by system]'
                    )
                );
            }

            DB::commit();

            $this->resetMaintenanceApprovalForm();
            $this->dispatch('closeAllModals');
            session()->flash('message', 'Maintenance approved and team assigned successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred while processing the approval.');
        }
    }

    public function receiveMaintenance()
    {
        if (!$this->selectedWorkOrderId || !$this->reason) {
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
                isi: "SPK Maintenance diterima oleh Dept. Maintenance.\nKlik tombol di bawah untuk melihat detail.",
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
        if (!$this->selectedWorkOrderId || !$this->reason) {
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
                isi: "SPK Maintenance ditolak oleh Dept. Maintenance.\nKlik tombol di bawah untuk melihat detail.",
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
        if (!$this->selectedWorkOrderId || !$this->reason) {
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
                    $userQuery->where('name', 'ilike', '%' . $this->search . '%');
                })
                    ->orWhereHas('department', function ($deptQuery) {
                        $deptQuery->where('name', 'ilike', '%' . $this->search . '%');
                    })
                    ->orWhere('status', 'ilike', '%' . $this->search . '%')
                    ->orWhere('urgent_level', 'ilike', '%' . $this->search . '%')
                    ->orWhere('notification_number', 'ilike', '%' . $this->search . '%');
            });
        }

        $workOrders = $query->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.assigned-spk', [
            'workOrders' => $workOrders
        ]);
    }
}
