<?php

namespace Marufsharia\Hyro\Console\Commands\User;

use Marufsharia\Hyro\Console\Commands\BaseCommand;

class ListPrivilegesCommand extends BaseCommand
{
    protected $signature = 'hyro:users:list-privileges
                            {users : User email or ID}
                            {--format=table : Output format (table, json, csv)}
                            {--effective : Show effective privileges from all roles}
                            {--direct : Show only directly assigned privileges}
                            {--group-by=scope : Group by (scope, category, none)}';

    protected $description = 'List privileges for a users';

    protected function executeCommand(): void
    {
        $user = $this->findUser($this->argument('users'));
        if (!$user) {
            $this->error("User not found: " . $this->argument('users'));
            return;
        }

        $privileges = $this->option('effective')
            ? $user->getAllPrivileges()
            : $user->privileges;

        $data = $privileges->map(function ($privilege) use ($user) {
            return [
                'id' => $privilege->id,
                'name' => $privilege->name,
                'slug' => $privilege->slug,
                'scope' => $privilege->scope,
                'description' => $privilege->description,
                'source' => $this->getPrivilegeSource($privilege, $user),
                'assigned_at' => $this->getAssignmentDate($privilege, $user),
                'inherited' => $this->isInherited($privilege, $user),
            ];
        });

        if ($this->option('group-by') !== 'none') {
            $data = $this->groupData($data, $this->option('group-by'));
        }

        $format = $this->option('format');

        switch ($format) {
            case 'json':
                $this->line($data->toJson(JSON_PRETTY_PRINT));
                break;
            case 'csv':
                $this->outputCsv($data->toArray());
                break;
            default:
                if ($this->option('group-by') === 'none') {
                    $this->table(
                        ['ID', 'Name', 'Slug', 'Scope', 'Description', 'Source', 'Assigned', 'Inherited'],
                        $data->map(function ($item) {
                            return [
                                $item['id'],
                                $item['name'],
                                $item['slug'],
                                $item['scope'],
                                $item['description'],
                                $item['source'],
                                $item['assigned_at'],
                                $item['inherited'] ? 'Yes' : 'No'
                            ];
                        })
                    );
                } else {
                    $this->outputGroupedTable($data);
                }
        }

        $this->info("Total privileges: " . $privileges->count());
        $this->info("Source: " . ($this->option('effective') ? 'All roles (effective)' : 'Direct only'));

        $this->stats['processed'] = $privileges->count();
        $this->stats['succeeded'] = $privileges->count();
    }

    protected function getPrivilegeSource($privilege, $user): string
    {
        if ($user->privileges->contains('id', $privilege->id)) {
            return 'Direct';
        }

        foreach ($user->roles as $role) {
            if ($role->privileges->contains('id', $privilege->id)) {
                return "Role: {$role->name}";
            }
        }

        return 'Unknown';
    }

    protected function getAssignmentDate($privilege, $user): string
    {
        // Check direct assignment
        $directAssignment = $user->privileges()->where('privileges.id', $privilege->id)->first();
        if ($directAssignment) {
            return $directAssignment->pivot->created_at->format('Y-m-d H:i:s');
        }

        // Check role assignment
        foreach ($user->roles as $role) {
            if ($role->privileges->contains('id', $privilege->id)) {
                return $role->pivot->created_at->format('Y-m-d H:i:s');
            }
        }

        return 'Unknown';
    }

    protected function isInherited($privilege, $user): bool
    {
        return !$user->privileges->contains('id', $privilege->id);
    }

    protected function groupData($data, $groupBy)
    {
        return $data->groupBy($groupBy);
    }

    protected function outputGroupedTable($groupedData): void
    {
        foreach ($groupedData as $group => $items) {
            $this->info("\n" . strtoupper($group) . ":");
            $this->table(
                ['ID', 'Name', 'Slug', 'Description', 'Source'],
                $items->map(function ($item) {
                    return [
                        $item['id'],
                        $item['name'],
                        $item['slug'],
                        $item['description'],
                        $item['source']
                    ];
                })
            );
        }
    }

    protected function outputCsv(array $data): void
    {
        if (empty($data)) {
            return;
        }

        $handle = fopen('php://output', 'w');
        fputcsv($handle, array_keys($data[0]));

        foreach ($data as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);
    }
}
