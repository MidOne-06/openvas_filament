<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('openvas_targets', function (Blueprint $table) {
            $table->id();
            $table->string('gvm_id')->unique()->comment('ID del target en OpenVAS');
            $table->string('name');
            $table->text('hosts')->nullable();
            $table->string('port_list_id')->nullable();
            $table->string('port_list_name')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('openvas_targets');
    }
};
