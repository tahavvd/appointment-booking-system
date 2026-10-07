<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('duration_minutes');
        });

        Schema::table('service_addons', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('extra_duration_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('service_addons', fn(Blueprint $table) => $table->dropColumn('is_active'));
        Schema::table('services', fn(Blueprint $table) => $table->dropColumn('is_active'));
    }
};
