<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('openvas_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('gvm_id')->unique()->comment('ID de la tarea en OpenVAS');
            $table->string('name');
            $table->string('status')->default('New');
            $table->integer('progress')->default(0);
            $table->string('config_id')->nullable();
            $table->string('target_id')->nullable();
            $table->string('last_report_id')->nullable();
            $table->json('kpis')->nullable()->comment('KPIs del ultimo reporte');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('last_synced_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('openvas_tasks');
    }
};
