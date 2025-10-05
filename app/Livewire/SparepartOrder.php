<?php

namespace App\Livewire;

use App\Models\SparepartList;
use App\Models\WorkOrder;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class SparepartOrder extends Component
{
    use WithPagination;

    #[Title('List of Sparepart Order')]
    // Pagination & Search
    public $perPage = 10;

    public $search = '';

    // Modal states
    public $selectedWorkOrder = null;

    public $modalMode = 'view';

    public $showScanModal = false;

    public $selectedSparepartIndex = null;

    public $scannedBarcode = '';

    public $scannedQuantity = '';

    public $scannedUom = '';

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
            $this->modalMode = 'view';
            $this->dispatch('showDetailModal');
        }
    }

    public function openEditModal($woId)
    {
        $this->selectedWorkOrder = WorkOrder::find($woId);

        if ($this->selectedWorkOrder) {
            $this->sparepartDetails = SparepartList::with('sparepart')
                ->where('wo_id', $woId)
                ->get();
            $this->modalMode = 'edit'; // Set mode edit
            $this->dispatch('showDetailModal');
        }
    }

    // Method untuk open scan modal
    public function openScanModal($index)
    {
        $this->selectedSparepartIndex = $index;
        $this->scannedBarcode = '';
        $this->scannedQuantity = '';
        $this->scannedUom = '';
        $this->showScanModal = true;
        $this->dispatch('showScanModal');
    }

    public function closeModal()
    {
        $this->selectedWorkOrder = null;
        $this->sparepartDetails = [];
        $this->modalMode = 'view';
        $this->dispatch('closeDetailModal');
    }

    // Method untuk close scan modal
    public function closeScanModal()
    {
        $this->showScanModal = false;
        $this->scannedBarcode = '';
        $this->scannedQuantity = '';
        $this->scannedUom = '';
        $this->selectedSparepartIndex = null;
        $this->dispatch('closeScanModal');
    }

    public function render()
    {
        // Get work orders that have sparepart lists using direct query
        $workOrderIds = SparepartList::select('wo_id')->distinct()->pluck('wo_id');

        $workOrders = WorkOrder::whereIn('id', $workOrderIds)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('notification_number', 'ilike', '%'.$this->search.'%')
                        ->orWhere('work_desc', 'ilike', '%'.$this->search.'%')
                        ->orWhere('status', 'ilike', '%'.$this->search.'%')
                        ->orWhereHas('equipment', function ($eq) {
                            $eq->where('name', 'ilike', '%'.$this->search.'%');
                        });
                });
            })
            ->orderBy('notification_date', 'desc')
            ->paginate($this->perPage);

        return view('livewire.sparepart-order', [
            'workOrders' => $workOrders,
        ]);
    }
}
