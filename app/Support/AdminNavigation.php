<?php

namespace App\Support;

final class AdminNavigation
{
    /**
     * Return the grouped admin navigation, including when the live
     * application is temporarily serving a stale Laravel config cache.
     *
     * @return array<int, array{label: string, items: array<int, array<string, mixed>>}>
     */
    public static function groups(): array
    {
        $navigation = config('admin_navigation');

        if (is_array($navigation) && $navigation !== []) {
            return $navigation;
        }

        $configurationFile = config_path('admin_navigation.php');

        if (! is_file($configurationFile)) {
            return [];
        }

        $navigation = require $configurationFile;

        return is_array($navigation) ? $navigation : [];
    }
}
