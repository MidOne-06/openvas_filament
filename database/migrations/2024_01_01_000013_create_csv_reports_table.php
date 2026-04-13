<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('csv_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_id')->unique()->comment('ID generado al procesar el CSV');
            $table->string('original_filename')->nullable();
            $table->integer('total_vulns')->default(0);
            $table->integer('critical')->default(0);
            $table->integer('high')->default(0);
            $table->integer('medium')->default(0);
            $table->integer('low')->default(0);
            $table->integer('info_count')->default(0);
            $table->integer('unique_hosts')->default(0);
            $table->json('metrics')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('csv_reports');
    }
};
