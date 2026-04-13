<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('openvas_reports', function (Blueprint $table) {
            $table->id();
            $table->string('gvm_id')->unique()->comment('ID del reporte en OpenVAS');
            $table->string('task_id')->nullable();
            $table->string('task_name')->nullable();
            $table->string('scan_start')->nullable();
            $table->string('scan_end')->nullable();
            $table->integer('total_vulns')->default(0);
            $table->integer('critical')->default(0);
            $table->integer('high')->default(0);
            $table->integer('medium')->default(0);
            $table->integer('low')->default(0);
            $table->integer('info_count')->default(0);
            $table->json('kpis')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index('task_id');
            $table->index('scan_start');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('openvas_reports');
    }
};
