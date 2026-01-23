<?php

namespace Marufsharia\Hyro\Console\Commands\Privilege;

use Marufsharia\Hyro\Console\Commands\BaseCommand;
use Marufsharia\Hyro\Models\Privilege;

class ListPrivilegesCommand extends BaseCommand
{
    protected $signature = 'hyro:privilege:list
                            {--format=table : Output format (table, json, csv)}
                            {--scope= : Filter by scope (global, tenant, resource)}
                            {--search= : Search term for privilege name or slug}
                            {--with-roles : Include roles that have each privilege}
                            {--sort-by=name : Sort by field (name, slug, scope, created_at)}
                            {--sort-direction=asc : Sort direction (asc, desc)}
                            {--per-page=20 : Number of privileges per page}';

    protected $description = 'List all privileges';

    protected function executeCommand(): void
    {
        $query = Privilege::query();

        if ($scope = $this->option('scope')) {
            $query->where('scope', $scope);
        }

        if ($search = $this->option('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $sortBy = $this->option('sort-by');
        $sortDirection = $this->option('sort-direction');
        $query->orderBy($sortBy, $sortDirection);

        $privileges = $query->paginate($this->option('per-page'));

        if ($this->option('with-roles')) {
            $privileges->load('roles');
        }

        $data = $privileges->map(function ($privilege) {
            $row = [
                'id' => $privilege->id,
                'name' => $privilege->name,
                'slug' => $privilege->slug,
                'scope' => $privilege->scope,
                'description' => $privilege->description,
                'role_count' => $privilege->roles_count ?? $privilege->roles()->count(),
                'created_at' => $privilege->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $privilege->updated_at->format('Y-m-d H:i:s'),
                'is_protected' => $this->isProtectedPrivilege($privilege) ? 'Yes' : 'No',
            ];

            if ($this->option('with-roles')) {
                $row['roles'] = $privilege->roles->pluck('name')->implode(', ');
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
                $headers = ['ID', 'Name', 'Slug', 'Scope', 'Description', 'Roles', 'Created', 'Protected'];

                if ($this->option('with-roles')) {
                    $headers[] = 'Role List';
                }

                $tableData = $data->map(function ($item) {
                    $row = [
                        $item['id'],
                        $item['name'],
                        $item['slug'],
                        $item['scope'],
                        $item['description'] ?? 'N/A',
                        $item['role_count'],
                        $item['created_at'],
                        $item['is_protected'],
                    ];

                    if ($this->option('with-roles')) {
                        $row[] = $item['roles'] ?? 'None';
                    }

                    return $row;
                });

                $this->table($headers, $tableData);

                $this->line("Showing {$data->count()} of {$privileges->total()} privileges");

                if ($scope) {
                    $this->info("Filtered by scope: {$scope}");
                }
        }

        $this->stats['processed'] = $privileges->total();
        $this->stats['succeeded'] = $privileges->total();
    }

    protected function isProtectedPrivilege($privilege): bool
    {
        $protectedPrivileges = config('hyro.protected_privileges', ['*', 'user:manage', 'role:view']);
        return in_array($privilege->slug, $protectedPrivileges);
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
