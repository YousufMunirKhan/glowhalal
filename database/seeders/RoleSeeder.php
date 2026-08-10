<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Idempotent. Safe to re-run — it never creates a second admin and never
 * touches an existing user's email or password.
 */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['super_admin', 'staff', 'customer'] as $name) {
            Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Provision the owner admin deterministically. On a fresh install this
        // is the FIRST user (id 1), which gives the store both a reachable admin
        // panel AND a user that content seeders reference for authorship
        // (e.g. ingredients.author_id). firstOrCreate is idempotent: it never
        // duplicates the admin and never rewrites an existing password.
        $admin = User::firstOrCreate(
            ['email' => 'admin@glowhalal.com'],
            [
                'name' => 'Glow Halal Admin',
                'password' => \Illuminate\Support\Facades\Hash::make('glowhalal12345'),
                'email_verified_at' => now(),
            ],
        );
        $admin->assignRole('super_admin');

        // Belt and braces: if the store somehow has no super_admin at all,
        // promote the lowest-id user rather than leaving the panel unreachable.
        //
        // This is gated to the local environment ONLY. In production the lowest
        // -id user is no longer guaranteed to be the owner — once Google sign-in
        // starts minting customer accounts, re-running this seeder could hand
        // super_admin (and the OAuth secret, PII, Settings) to a random shopper.
        // The explicit admin@glowhalal.com assignment above is what production
        // relies on; if that user is absent, provision the admin deliberately
        // rather than letting a seeder guess.
        if (app()->environment('local')
            && ! User::role('super_admin')->exists()
            && ($first = User::orderBy('id')->first())) {
            $first->assignRole('super_admin');
        }
    }
}
