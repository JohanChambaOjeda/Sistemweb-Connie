<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cargo_agency_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code');
            $table->string('consignee')->nullable();
            $table->string('destination_country')->nullable();
            $table->string('destination_city')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'customer_id', 'code', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marks');
    }
};
