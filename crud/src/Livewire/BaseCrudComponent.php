<?php

namespace Marufsharia\Hyro\Crud\Livewire;

use Livewire\Component;
use Marufsharia\Hyro\Crud\Livewire\Traits\HasCrud;
use Marufsharia\Hyro\Core\Traits\WithAlerts;

/**
 * Base Component for CRUD Operations
 *
 * @abstract
 */
abstract class BaseCrudComponent extends Component
{
    use HasCrud, WithAlerts;

    /**
     * Listeners for Livewire events
     */
    protected $listeners = [
        'refreshComponent' => '$refresh',
        'notify' => 'handleNotification',
    ];

    /**
     * Query string parameters for persistent state
     */
    protected function queryStringHasCrud(): array
    {
        return [
            'search' => ['except' => ''],
            'sortField' => ['except' => 'created_at'],
            'sortDirection' => ['except' => 'desc'],
            'perPage' => ['except' => 15],
        ];
    }

    /**
     * Handle incoming notifications
     */
    public function handleNotification($type, $message)
    {
        $this->alert($type, $message);
    }
}
