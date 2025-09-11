<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use App\Models\WorkOrder;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Models\MaintenanceApproval;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMaintenanceApproval;

class ApprovalSpvUser extends Component
{
    use WithPagination;

    #[Title('SPK for Approval')]

    public $perPage = 10;
    public $search = '';
    public $reason = '';
    public $selectedWorkOrderId = null;
    public $selectedWorkOrder = null;
    public $popupModalAction = '';
    public $popupModalActor = '';
    public $popupModalHeaderClass = '';
    public $popupModalTitle = '';

    protected $paginationTheme = 'bootstrap';

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
    }

    public function openPopupModal($action, $actor)
    {
        // dd($action,$actor);
        $this->popupModalActor = $actor;
        $this->popupModalAction = $action;
        if ($actor == 'spvUser') {
            // $this->popupModalAction = $action;
            // dd("tidak hit");
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
            // $this->popupModalAction = $action;
            // dd($action,$actor);
            if ($action == 'receive') {
                $this->popupModalHeaderClass = 'bg-info';
                $this->popupModalTitle = 'Recevice SPK';
                $this->popupModalAction = 'receive';
                // dd($this->popupModalHeaderClass);       
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


    // public function closeDetailModal() 
    // {

    // }

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
            //Mailer
            //Mail::to($spv->email)->send(new SendMaintenanceApproval($spv->name, route('work-order.spv-approval')));

            // $this->closeDetailModal();
            $this->dispatch('closeAllModals');
            session()->flash('message', 'Work Order approved successfully.');
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
        //missing algorithm here (approval histories) and email
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
        //missing algorithm here (approval histories) and email
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

            // $this->closeDetailModal();
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

    public function render()
    {
        $query = WorkOrder::with(['equipment.functionalLocation.resource.plant', 'department', 'user'])
            ->where('is_spv_rejected', 'false'); // Filter untuk work order yang perlu approval

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

        return view('livewire.approval-spv-user', [
            'workOrders' => $workOrders
        ]);
    }
}
