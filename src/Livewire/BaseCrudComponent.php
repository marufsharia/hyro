<?php

namespace Marufsharia\Hyro\Livewire;

use Livewire\Component;
use Marufsharia\Hyro\Livewire\Traits\HasCrud;

abstract class BaseCrudComponent extends Component
{
    use HasCrud;

    /**
     * Listeners for Livewire events
     */
    protected $listeners = [
        'refreshComponent' => '$refresh',
    ];
}
