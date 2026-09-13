<?php

/**
 * Admin Account Creator Script
 * Run: php admin.php
 * Creates admin account with credentials
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

echo "\n" . str_repeat("=", 60) . "\n";
echo "       ADMIN ACCOUNT CREATOR\n";
echo str_repeat("=", 60) . "\n\n";

try {
    $credentials = [
        'name' => 'LegalBruz Admin Anshul',
        'email' => 'legalbruz@gmail.com',
        'password' => 'Legalbruz@2026',
    ];

    // This script manages the primary admin account. Re-running it after changing
    // the values above will update the existing account instead of creating a
    // duplicate admin with the new email address.
    $admin = Admin::query()->oldest('id')->first();
    $wasCreated = $admin === null;

    if ($wasCreated) {
        $admin = new Admin();
    }

    $admin->name = $credentials['name'];
    $admin->email = $credentials['email'];

    if ($wasCreated || ! Hash::check($credentials['password'], $admin->password)) {
        $admin->password = Hash::make($credentials['password']);
    }

    $admin->save();

    echo $wasCreated
        ? "✅ Admin account created successfully!\n\n"
        : "✅ Admin account updated successfully!\n\n";
    echo "Email: {$admin->email}\n\n";

    // List all admins
    echo str_repeat("-", 60) . "\n";
    echo "ALL ADMIN ACCOUNTS:\n";
    echo str_repeat("-", 60) . "\n";

    $admins = Admin::all();
    if ($admins->count() > 0) {
        foreach ($admins as $admin) {
            echo "ID: {$admin->id} | Name: {$admin->name} | Email: {$admin->email}\n";
        }
    } else {
        echo "No admin accounts found\n";
    }

    echo "\n" . str_repeat("=", 60) . "\n";
    echo "🌐 LOGIN URL: http://localhost:8000/login\n";
    echo str_repeat("=", 60) . "\n\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

exit(0);
