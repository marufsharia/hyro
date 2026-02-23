<?php
namespace Marufsharia\Hyro\Crud\Services;

class PermissionGenerator
{
    public static function generate(string $slug): array
    {
        return [
            "$slug.access",
            "$slug.create",
            "$slug.edit",
            "$slug.delete",
        ];
    }
}
