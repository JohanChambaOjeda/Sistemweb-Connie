<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 50);
            $table->string('original_filename');
            $table->string('stored_path');
            $table->unsignedSmallInteger('header_row')->default(1);
            // Columna del archivo asignada a cada campo del importador.
            $table->json('column_map')->nullable();
            $table->string('status', 20)->default('uploaded');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('valid_rows')->default(0);
            $table->unsignedInteger('invalid_rows')->default(0);
            $table->unsignedInteger('committed_rows')->default(0);
            $table->string('error_report_path')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_runs');
    }
};
