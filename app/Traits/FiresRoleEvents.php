<?php

namespace App\Traits;

use App\Events\RoleAssigned;
use App\Events\RoleRevoked;
use Spatie\Permission\Contracts\Role;

trait FiresRoleEvents
{
    /**
     * Assign the given role to the model and fire event.
     *
     * @param  array|string|\Spatie\Permission\Contracts\Role  ...$roles
     * @return $this
     */
    public function assignRole(...$roles)
    {
        $result = $this->assignRoleOriginal(...$roles);
        
        // Fire event for each role assigned
        foreach ($roles as $role) {
            if (is_string($role)) {
                $role = app(Role::class)->findByName($role, $this->getDefaultGuardName());
            }
            
            if ($role instanceof Role) {
                event(new RoleAssigned($this, $role));
            }
        }
        
        return $result;
    }

    /**
     * Remove the given role from the model and fire event.
     *
     * @param  string|\Spatie\Permission\Contracts\Role  $role
     * @return $this
     */
    public function removeRole($role)
    {
        // Get role instance before removing
        if (is_string($role)) {
            $roleInstance = app(Role::class)->findByName($role, $this->getDefaultGuardName());
        } else {
            $roleInstance = $role;
        }
        
        $result = $this->removeRoleOriginal($role);
        
        // Fire event after removal
        if ($roleInstance instanceof Role) {
            event(new RoleRevoked($this, $roleInstance));
        }
        
        return $result;
    }

    /**
     * Sync roles and fire events for changes.
     *
     * @param  array|\Spatie\Permission\Contracts\Role|string  ...$roles
     * @return $this
     */
    public function syncRoles(...$roles)
    {
        // Get current roles before sync
        $currentRoles = $this->roles->pluck('name')->toArray();
        
        // Normalize new roles to array of names
        $newRoles = collect($roles)->flatten()->map(function ($role) {
            if ($role instanceof Role) {
                return $role->name;
            }
            return $role;
        })->toArray();
        
        // Perform the sync
        $result = $this->syncRolesOriginal(...$roles);
        
        // Determine which roles were added and removed
        $addedRoles = array_diff($newRoles, $currentRoles);
        $removedRoles = array_diff($currentRoles, $newRoles);
        
        // Fire events for added roles
        foreach ($addedRoles as $roleName) {
            $role = app(Role::class)->findByName($roleName, $this->getDefaultGuardName());
            if ($role) {
                event(new RoleAssigned($this, $role));
            }
        }
        
        // Fire events for removed roles
        foreach ($removedRoles as $roleName) {
            $role = app(Role::class)->findByName($roleName, $this->getDefaultGuardName());
            if ($role) {
                event(new RoleRevoked($this, $role));
            }
        }
        
        return $result;
    }
}
