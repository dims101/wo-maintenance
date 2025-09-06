<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;

class CreateSPK extends Component
{
    // public $subTitle = "Form Maintenance Notification";

    #[Title('Form Maintenance Notification')]
    public function render()
    {
        return view('livewire.create-s-p-k');
        
    }
}
