<?php

namespace Marufsharia\Hyro\Console\Commands\Role;

use Marufsharia\Hyro\Console\Commands\BaseCommand;

class ListRolesCommand extends BaseCommand
{
    protected $signature = 'hyro:user:list-roles
                            {user : User email or ID}
                            {--format=table : Output format (table, json, csv)}
                            {--with-privileges : Include privileges for each role}
                            {--detailed : Show detailed role information}';

    protected $description = 'List roles assigned to a user';

    protected function executeCommand(): void
    {
        $user = $this->findUser($this->argument('user'));
        if (!$user) {
            $this->error("User not found: " . $this->argument('user'));
            return;
        }

        $roles = $user->roles;

        if ($this->option('with-privileges')) {
            $roles->load('privileges');
        }

        $data = $roles->map(function ($role) {
            $row = [
                'id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
                'description' => $role->description,
                'privilege_count' => $role->privileges_count ?? $role->privileges()->count(),
                'assigned_at' => $role->pivot->created_at->format('Y-m-d H:i:s'),
            ];

            if ($this->option('detailed')) {
                $row['created_at'] = $role->created_at->format('Y-m-d H:i:s');
                $row['updated_at'] = $role->updated_at->format('Y-m-d H:i:s');
                $row['is_protected'] = $this->isProtectedRole($role) ? 'Yes' : 'No';
            }

            if ($this->option('with-privileges')) {
                $row['privileges'] = $role->privileges->pluck('slug')->implode(', ');
            }

            return $row;
        });

        $format = $this->option('format');

        switch ($format) {
            case 'json':
                $this->line($data->toJson(JSON_PRETTY_PRINT));
                break;
            case 'csv':
                $this->outputCsv($data->toArray());
                break;
            default:
                $headers = ['ID', 'Name', 'Slug', 'Description', 'Privileges', 'Assigned At'];

                if ($this->option('detailed')) {
                    array_splice($headers, 4, 0, ['Created', 'Updated', 'Protected']);
                }

                $tableData = $data->map(function ($item) {
                    $row = [
                        $item['id'],
                        $item['name'],
                        $item['slug'],
                        $item['description'],
                        $item['privilege_count'],
                        $item['assigned_at']
                    ];

                    if ($this->option('detailed')) {
                        array_splice($row, 4, 0, [
                            $item['created_at'] ?? 'N/A',
                            $item['updated_at'] ?? 'N/A',
                            $item['is_protected'] ?? 'No'
                        ]);
                    }

                    if ($this->option('with-privileges')) {
                        $row[] = $item['privileges'] ?? 'N/A';
                    }

                    return $row;
                });

                if ($this->option('with-privileges') && !$this->option('detailed')) {
                    $headers[] = 'Privilege List';
                }

                $this->table($headers, $tableData);
        }

        $this->info("Total roles: " . $roles->count());

        $this->stats['processed'] = $roles->count();
        $this->stats['succeeded'] = $roles->count();
    }

    protected function isProtectedRole($role): bool
    {
        $protectedRoles = config('hyro.protected_roles', ['super-admin', 'admin', 'user']);
        return in_array($role->slug, $protectedRoles);
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
