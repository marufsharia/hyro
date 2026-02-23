<?php

namespace Marufsharia\Hyro\AdminPanel\Livewire;

use Livewire\Component;
use Marufsharia\Hyro\Facades\Hyro;

class Sidebar extends Component
{
    protected $listeners = [
        'refreshSidebar' => '$refresh',
        'avatar-updated' => '$refresh'
    ];

    public function render()
    {
        return view('hyro::admin.layouts.partials.sidebar');
    }
}
