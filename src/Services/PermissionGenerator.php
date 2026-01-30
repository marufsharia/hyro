<?php
namespace Marufsharia\Hyro\Services;

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
