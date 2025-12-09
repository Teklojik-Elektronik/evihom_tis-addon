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
        Schema::create('appliances', function (Blueprint $table) {
            $table->id();
            $table->string('appliance_name');
            $table->bigInteger('appliance_type')->unsigned();
            $table->bigInteger('device_id')->unsigned();
            $table->boolean('is_protected')->default(false);
            $table->decimal('min')->nullable()->default(Null);
            $table->decimal('max')->nullable()->default(Null);
            $table->json('settings')->nullable()->default(Null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appliances');
    }
};
