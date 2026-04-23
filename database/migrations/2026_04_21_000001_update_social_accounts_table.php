<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('social_accounts', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->after('id');
            $table->string('provider', 30)->after('user_id');           // google, apple
            $table->string('provider_user_id')->after('provider');
            $table->string('provider_email')->nullable()->after('provider_user_id');
            $table->string('provider_name')->nullable()->after('provider_email');
            $table->string('avatar_url')->nullable()->after('provider_name');
            $table->text('access_token')->nullable()->after('avatar_url');
            $table->text('refresh_token')->nullable()->after('access_token');

            $table->unique(['provider', 'provider_user_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('social_accounts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['provider', 'provider_user_id']);
            $table->dropIndex(['user_id']);
            $table->dropColumn([
                'user_id', 'provider', 'provider_user_id', 'provider_email',
                'provider_name', 'avatar_url', 'access_token', 'refresh_token',
            ]);
        });
    }
};
