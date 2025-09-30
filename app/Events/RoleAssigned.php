<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Spatie\Permission\Contracts\Role;

class RoleAssigned
{
    use Dispatchable, SerializesModels;

    /**
     * The model that was assigned the role.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    public $model;

    /**
     * The role that was assigned.
     *
     * @var \Spatie\Permission\Contracts\Role
     */
    public $role;

    /**
     * Create a new event instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  \Spatie\Permission\Contracts\Role  $role
     * @return void
     */
    public function __construct($model, Role $role)
    {
        $this->model = $model;
        $this->role = $role;
    }
}
