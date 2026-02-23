<?php

namespace Marufsharia\Hyro\AdminPanel\Services;

class SidebarRegistry
{
    /**
     * Sidebar items are auto-built from enabled modules.
     */
    public static function items(): array
    {
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
                [
                    "title" => "Privileges",
                    "icon"  => "key",
                    "route" => "admin.privileges",
                ],
                [
                    "title" => "Plugin Manager",
                    "icon"  => "puzzle",
                    "route" => "hyro.admin.plugins.index",
                ],
                [
                    "title" => "Settings",
                    "icon"  => "cog",
                    "route" => "admin.settings",
                ],
            ],
        ];

        return $menu;
    }
}
