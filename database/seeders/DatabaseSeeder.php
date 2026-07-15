<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Deliberately empty. The client's database is the source of truth, so a
     * bare `db:seed` must not add anything to it — the default here created a
     * test@example.com user, which would land in their live users table.
     *
     * Run the seeders that exist one at a time, on purpose:
     *
     *     php artisan db:seed --class=SocialMediaSeeder
     */
    public function run(): void
    {
        //
    }
}
