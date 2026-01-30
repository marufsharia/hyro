<?php

namespace Marufsharia\Hyro\Services;

class SidebarRegistry
{
    /**
     * Sidebar items are auto-built from enabled modules.
     */
    public static function items(): array
    {
        $modules = ModuleManager::enabled();

        $menu = [];

        /**
         * Built-in System Modules
         */
        $menu[] = [
            "group" => "System",
            "items" => [
                [
                    "title" => "Dashboard",
                    "icon"  => "home",
                    "route" => "admin.dashboard",
                ],
                [
                    "title" => "Users",
                    "icon"  => "users",
                    "route" => "admin.users",
                ],
                [
                    "title" => "Roles",
                    "icon"  => "shield",
                    "route" => "admin.roles",
                ],
            ],
        ];

        /**
         * Dynamic Modules Group
         */
        $dynamicItems = [];

        foreach ($modules as $slug => $module) {

            $dynamicItems[] = [
                "title" => $module["title"] ?? ucfirst($slug),
                "icon"  => $module["icon"] ?? "folder",
                "route" => "admin.$slug",
            ];
        }

        if (!empty($dynamicItems)) {
            $menu[] = [
                "group" => "Modules",
                "items" => $dynamicItems,
            ];
        }

        return $menu;
    }
}
