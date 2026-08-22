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
        $configurationFile = config_path('admin_navigation.php');

        if (is_file($configurationFile)) {
            $navigation = require $configurationFile;

            if (is_array($navigation) && $navigation !== []) {
                return $navigation;
            }
        }

        $navigation = config('admin_navigation');

        return is_array($navigation) ? $navigation : [];
    }
}
