<?php

namespace Marufsharia\Hyro\Console\Commands\User;

use Marufsharia\Hyro\Console\Commands\BaseCommand;
use Marufsharia\Hyro\Models\Role;

class ListRolesCommand extends BaseCommand
{
    protected $signature = 'hyro:role:list
                            {--format=table : Output format (table, json, csv)}
                            {--search= : Search term for role name or slug}
                            {--per-page=20 : Number of roles per page}';

    protected $description = 'List all roles';

    protected function executeCommand(): void
    {
        $query = Role::query();

        if ($search = $this->option('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $roles = $query->paginate($this->option('per-page'));

        $data = $roles->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
                'description' => $role->description,
                'user_count' => $role->users()->count(),
                'privilege_count' => $role->privileges()->count(),
                'created_at' => $role->created_at->format('Y-m-d H:i:s'),
            ];
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
                $this->table(
                    ['ID', 'Name', 'Slug', 'Description', 'Users', 'Privileges', 'Created'],
                    $data->map(function ($item) {
                        return [
                            $item['id'],
                            $item['name'],
                            $item['slug'],
                            $item['description'],
                            $item['user_count'],
                            $item['privilege_count'],
                            $item['created_at']
                        ];
                    })
                );

                $this->line("Showing {$data->count()} of {$roles->total()} roles");
        }

        $this->stats['processed'] = $roles->total();
        $this->stats['succeeded'] = $roles->total();
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
