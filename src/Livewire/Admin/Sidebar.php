<?php

namespace Marufsharia\Hyro\Livewire\Admin;

use Livewire\Component;
use Marufsharia\Hyro\Facades\Hyro;

class Sidebar extends Component
{
    protected $listeners = ['refreshSidebar' => '$refresh'];

    public function render()
    {
        return view('hyro::admin.layouts.partials.sidebar');
    }
}
