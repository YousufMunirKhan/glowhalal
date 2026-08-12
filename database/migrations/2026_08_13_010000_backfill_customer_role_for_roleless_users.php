<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

/**
 * Backfill: every existing user with NO role becomes a 'customer'.
 *
 * Google sign-in used to create users without assigning the 'customer' role, so
 * early Google-login customers never appeared in Admin → Customers (that list is
 * scoped to the role). Staff/admin accounts already carry super_admin and are
 * therefore untouched. Pairs with the GoogleAuthController fix that tags new
 * sign-ins going forward. Safe/idempotent — role-less users only.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Nothing to do if the role itself was never seeded.
        if (! Role::where('name', 'customer')->exists()) {
            return;
        }

        User::doesntHave('roles')->get()->each(function (User $user) {
            try {
                $user->assignRole('customer');
            } catch (\Throwable $e) {
                // ignore a single bad row — never break the deploy migration.
            }
        });
    }

    public function down(): void
    {
        // No safe rollback — we cannot tell backfilled rows from genuine ones.
    }
};
