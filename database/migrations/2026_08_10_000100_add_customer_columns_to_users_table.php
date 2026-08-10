<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('phone_country', 2)->default('PK')->after('phone');
            $table->timestamp('phone_verified_at')->nullable()->after('phone_country');
            $table->date('date_of_birth')->nullable()->after('phone_verified_at');
            $table->string('avatar_path')->nullable()->after('date_of_birth');
            $table->boolean('accepts_marketing')->default(false)->after('avatar_path');
            $table->timestamp('accepts_marketing_at')->nullable()->after('accepts_marketing');
            $table->string('preferred_locale', 5)->default('en')->after('accepts_marketing_at');
            $table->timestamp('last_login_at')->nullable()->after('preferred_locale');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->boolean('is_blocked')->default(false)->after('last_login_ip');
            $table->softDeletes();

            $table->unique('phone', 'users_phone_unique');
            $table->index(['is_blocked', 'created_at'], 'users_blocked_created_index');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_phone_unique');
            $table->dropIndex('users_blocked_created_index');
            $table->dropSoftDeletes();
            $table->dropColumn([
                'phone', 'phone_country', 'phone_verified_at', 'date_of_birth',
                'avatar_path', 'accepts_marketing', 'accepts_marketing_at',
                'preferred_locale', 'last_login_at', 'last_login_ip', 'is_blocked',
            ]);
        });
    }
};
