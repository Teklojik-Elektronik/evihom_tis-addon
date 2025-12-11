<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->boolean('is_grouped')->default(false)->after('gateway');
            $table->json('available_channels')->nullable()->after('is_grouped');
        });

        Schema::table('appliances', function (Blueprint $table) {
            $table->string('channel_identifier')->nullable()->after('device_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn(['is_grouped', 'available_channels']);
        });

        Schema::table('appliances', function (Blueprint $table) {
            $table->dropColumn('channel_identifier');
        });
    }
};
