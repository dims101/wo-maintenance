<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\WorkOrder;
use App\Models\SparepartList;

class SparepartOrder extends Component
{
    use WithPagination;

    // Pagination & Search
    public $perPage = 10;
    public $search = '';

    // Modal states
    public $selectedWorkOrder = null;
    public $sparepartDetails = [];

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function openDetailModal($woId)
    {
        // Hapus relasi sparepartLists yang tidak ada
        $this->selectedWorkOrder = WorkOrder::find($woId);

        if ($this->selectedWorkOrder) {
            // Query langsung tanpa relasi
            $this->sparepartDetails = SparepartList::with('sparepart')
                ->where('wo_id', $woId)
                ->get();
            $this->dispatch('showDetailModal');
        }
    }

    public function closeModal()
    {
        $this->selectedWorkOrder = null;
        $this->sparepartDetails = [];
        $this->dispatch('closeDetailModal');
    }

    public function render()
    {
        // Get work orders that have sparepart lists using direct query
        $workOrderIds = SparepartList::select('wo_id')->distinct()->pluck('wo_id');

        $workOrders = WorkOrder::whereIn('id', $workOrderIds)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('notification_number', 'ilike', '%' . $this->search . '%')
                        ->orWhere('work_desc', 'ilike', '%' . $this->search . '%')
                        ->orWhere('status', 'ilike', '%' . $this->search . '%')
                        ->orWhereHas('equipment', function ($eq) {
                            $eq->where('name', 'ilike', '%' . $this->search . '%');
                        });
                });
            })
            ->orderBy('notification_date', 'desc')
            ->paginate($this->perPage);

        return view('livewire.sparepart-order', [
            'workOrders' => $workOrders
        ]);
    }
}
