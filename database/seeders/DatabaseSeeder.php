<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Essential data for a fresh, usable system — the admin account and
 * default settings only. No business/demo records.
 *
 * For sample data run: php artisan db:seed --class=DemoSeeder
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'admin@eventpuls.sa'],
            ['name' => 'Admin', 'password' => bcrypt('password')],
        );

        foreach ([
            'system_name' => 'Event Puls',
            'default_currency' => 'SAR',
            'vat_rate' => '15',
            'invoice_prefix' => 'INV-',
            'timezone' => 'Asia/Riyadh',
        ] as $key => $value) {
            Setting::put($key, $value);
        }
    }
}
