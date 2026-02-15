<?php

namespace App\Livewire;

use App\Models\Sparepart;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class SparepartMaster extends Component
{
    use WithPagination;

    #[Title('Master Sparepart')]
    
    // Pagination & Search
    public $perPage = 10;
    public $search = '';

    // Modal states
    public $showModal = false;
    public $modalMode = 'add'; // 'add' or 'edit'
    public $sparepartId = null;

    // Form fields
    public $barcode = '';
    public $code = '';
    public $name = '';
    public $stock = '';
    public $uom = '';

    protected $paginationTheme = 'bootstrap';

    protected function rules()
    {
        $sparepartId = $this->sparepartId;

        return [
            'barcode' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($sparepartId) {
                    $exists = Sparepart::where('barcode', $value)
                        ->when($sparepartId, function ($q) use ($sparepartId) {
                            $q->where('id', '!=', $sparepartId);
                        })
                        ->exists();
                    
                    if ($exists) {
                        $fail('The barcode has already been taken.');
                    }
                },
            ],
            'code' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($sparepartId) {
                    $exists = Sparepart::where('code', $value)
                        ->when($sparepartId, function ($q) use ($sparepartId) {
                            $q->where('id', '!=', $sparepartId);
                        })
                        ->exists();
                    
                    if ($exists) {
                        $fail('The code has already been taken.');
                    }
                },
            ],
            'name' => 'required|string|max:255',
            'stock' => 'required|numeric|min:0',
            'uom' => 'required|string|max:50',
        ];
    }

    protected $messages = [
        'barcode.required' => 'Barcode is required.',
        'code.required' => 'Code is required.',
        'name.required' => 'Name is required.',
        'stock.required' => 'Stock is required.',
        'stock.numeric' => 'Stock must be a number.',
        'stock.min' => 'Stock must be at least 0.',
        'uom.required' => 'UOM is required.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function openAddModal()
    {
        $this->resetForm();
        $this->modalMode = 'add';
        $this->showModal = true;
        $this->dispatch('showSparepartModal');
    }

    public function openEditModal($id)
    {
        $sparepart = Sparepart::find($id);

        if ($sparepart) {
            $this->sparepartId = $sparepart->id;
            $this->barcode = $sparepart->barcode;
            $this->code = $sparepart->code;
            $this->name = $sparepart->name;
            $this->stock = $sparepart->stock;
            $this->uom = $sparepart->uom;
            $this->modalMode = 'edit';
            $this->showModal = true;
            $this->dispatch('showSparepartModal');
        }
    }

    public function confirmSave()
    {
        $this->validate();
        $this->dispatch('confirmSaveSparepart');
    }

    public function saveSparepart()
    {
        $this->validate();

        try {
            if ($this->modalMode === 'add') {
                Sparepart::create([
                    'barcode' => $this->barcode,
                    'code' => $this->code,
                    'name' => $this->name,
                    'stock' => $this->stock,
                    'uom' => $this->uom,
                ]);
                session()->flash('message', 'Sparepart added successfully.');
            } else {
                $sparepart = Sparepart::find($this->sparepartId);
                if ($sparepart) {
                    $sparepart->update([
                        'barcode' => $this->barcode,
                        'code' => $this->code,
                        'name' => $this->name,
                        'stock' => $this->stock,
                        'uom' => $this->uom,
                    ]);
                    session()->flash('message', 'Sparepart updated successfully.');
                }
            }

            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function confirmDelete($id)
    {
        $this->sparepartId = $id;
        $this->dispatch('confirmDeleteSparepart');
    }

    public function deleteSparepart()
    {
        try {
            $sparepart = Sparepart::find($this->sparepartId);
            
            if ($sparepart) {
                $sparepart->delete();
                session()->flash('message', 'Sparepart deleted successfully.');
            }
            
            $this->sparepartId = null;
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('closeSparepartModal');
    }

    private function resetForm()
    {
        $this->sparepartId = null;
        $this->barcode = '';
        $this->code = '';
        $this->name = '';
        $this->stock = '';
        $this->uom = '';
        $this->resetValidation();
    }

    public function render()
    {
        $spareparts = Sparepart::when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('barcode', 'ilike', '%' . $this->search . '%')
                    ->orWhere('code', 'ilike', '%' . $this->search . '%')
                    ->orWhere('name', 'ilike', '%' . $this->search . '%')
                    ->orWhere('uom', 'ilike', '%' . $this->search . '%');
            });
        })
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);

        return view('livewire.sparepart-master', [
            'spareparts' => $spareparts,
        ]);
    }
}