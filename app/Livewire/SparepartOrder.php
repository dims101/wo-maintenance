<?php

namespace App\Livewire;

use App\Models\SparepartList;
use App\Models\WorkOrder;
use DB;
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

    public $sparepartSearch = '';

    public $sparepartSearchResults = [];

    public $selectedSparepartListId = null;

    public $editBarcode = '';

    public $editQuantity = '';

    public $editUom = '';

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
        $this->selectedWorkOrder = WorkOrder::find($woId);

        if ($this->selectedWorkOrder) {
            // Ambil semua sparepart yang direquest (termasuk yang barcode NULL)
            $this->sparepartDetails = SparepartList::where('wo_id', $woId)
                ->get();
            $this->modalMode = 'view';
            $this->dispatch('showDetailModal');
        }
    }

    public function openEditModal($woId)
    {
        $this->selectedWorkOrder = WorkOrder::find($woId);

        if ($this->selectedWorkOrder) {
            // Ambil semua sparepart yang direquest
            $this->sparepartDetails = SparepartList::where('wo_id', $woId)
                ->get();
            $this->modalMode = 'edit';
            $this->dispatch('showDetailModal');
        }
    }

    public function openAddBarcodeModal($sparepartListId)
    {
        $sparepartList = SparepartList::find($sparepartListId);

        if ($sparepartList) {
            $this->selectedSparepartListId = $sparepartListId;
            $this->editBarcode = $sparepartList->barcode ?? '';
            $this->editQuantity = $sparepartList->qty;
            $this->editUom = $sparepartList->uom ?? '';
            $this->sparepartSearch = '';
            $this->sparepartSearchResults = [];
            $this->showScanModal = true;
            $this->dispatch('showScanModal');
        }
    }

    public function searchSparepartByBarcode()
    {
        if (strlen($this->sparepartSearch) < 3) {
            $this->sparepartSearchResults = [];

            return;
        }

        $this->sparepartSearchResults = \App\Models\Sparepart::where(function ($q) {
            $q->where('barcode', 'ilike', '%'.$this->sparepartSearch.'%')
                ->orWhere('code', 'ilike', '%'.$this->sparepartSearch.'%')
                ->orWhere('name', 'ilike', '%'.$this->sparepartSearch.'%');
        })
            ->limit(10)
            ->get(['id', 'barcode', 'code', 'name', 'uom', 'stock']);
    }

    public function selectSparepart($sparepartId)
    {
        $sparepart = \App\Models\Sparepart::find($sparepartId);

        if ($sparepart) {
            $this->editBarcode = $sparepart->barcode;
            $this->editUom = $sparepart->uom;
            $this->sparepartSearch = $sparepart->code.' - '.$sparepart->name;
            $this->sparepartSearchResults = [];
        }
    }

    public function saveBarcodeMapping()
    {
        // Validasi
        if (empty($this->editBarcode) || empty($this->editQuantity)) {
            session()->flash('error', 'Barcode and quantity are required.');

            return;
        }

        try {
            $sparepartList = SparepartList::find($this->selectedSparepartListId);

            if ($sparepartList) {
                $sparepartList->update([
                    'barcode' => $this->editBarcode,
                    'qty' => $this->editQuantity,
                    'uom' => $this->editUom,
                ]);

                // Reload sparepart details
                $this->sparepartDetails = SparepartList::where('wo_id', $this->selectedWorkOrder->id)
                    ->get();

                $this->closeScanModal();
                session()->flash('message', 'Barcode mapped successfully.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    public function closeModal()
    {
        $this->selectedWorkOrder = null;
        $this->sparepartDetails = [];
        $this->modalMode = 'view';
        $this->selectedSparepartListId = null;
        $this->editBarcode = '';
        $this->editQuantity = '';
        $this->editUom = '';
        $this->sparepartSearch = '';
        $this->sparepartSearchResults = [];
        $this->dispatch('closeDetailModal');
    }

    public function closeScanModal()
    {
        $this->showScanModal = false;
        $this->selectedSparepartListId = null;
        $this->editBarcode = '';
        $this->editQuantity = '';
        $this->editUom = '';
        $this->sparepartSearch = '';
        $this->sparepartSearchResults = [];
        $this->dispatch('closeScanModal');
    }

    public function confirmSubmit()
    {
        $this->dispatch('confirmSubmitOrder');
    }

    public function submitSparepartOrder()
    {
        try {
            \DB::beginTransaction();

            // Ambil semua sparepart list untuk WO ini
            $sparepartLists = SparepartList::where('wo_id', $this->selectedWorkOrder->id)
                ->whereNotNull('barcode')
                ->get();

            if ($sparepartLists->isEmpty()) {
                session()->flash('error', 'No sparepart with barcode to process.');

                return;
            }

            foreach ($sparepartLists as $sparepartList) {
                // Cari sparepart di master berdasarkan barcode
                $sparepart = \App\Models\Sparepart::where('barcode', $sparepartList->barcode)->first();

                if ($sparepart) {
                    // Kurangi stock
                    if ($sparepart->stock >= $sparepartList->qty) {
                        $sparepart->decrement('stock', $sparepartList->qty);

                        // Update status menjadi completed
                        $sparepartList->update(['is_completed' => true]);
                    } else {
                        \DB::rollBack();
                        session()->flash('error', 'Insufficient stock for '.$sparepart->name.'. Available: '.$sparepart->stock);

                        return;
                    }
                } else {
                    \DB::rollBack();
                    session()->flash('error', 'Sparepart with barcode '.$sparepartList->barcode.' not found.');

                    return;
                }
            }

            \DB::commit();

            $this->closeModal();
            session()->flash('message', 'Sparepart order submitted successfully. Stock updated.');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    public function render()
    {
        // Get work orders that have sparepart lists (with requested_sparepart)
        $workOrderIds = SparepartList::whereNotNull('requested_sparepart')
            ->select('wo_id')
            ->distinct()
            ->pluck('wo_id');

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
