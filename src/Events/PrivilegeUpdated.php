<?php

namespace Marufsharia\Hyro\Events;

use Marufsharia\Hyro\Models\Privilege;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrivilegeUpdated
{
    use Dispatchable, SerializesModels;

    /**
     * The privilege instance.
     *
     * @var Privilege
     */
    public $privilege;

    /**
     * Create a new event instance.
     *
     * @param  Privilege  $privilege
     * @return void
     */
    public function __construct(Privilege $privilege)
    {
        $this->privilege = $privilege;
    }
}
