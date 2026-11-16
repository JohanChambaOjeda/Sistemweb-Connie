<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('flower_variety_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('stem_length_cm');
            $table->unsignedSmallInteger('stems_per_bunch')->default(25);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['company_id', 'flower_variety_id', 'stem_length_cm', 'stems_per_bunch', 'deleted_at'],
                'products_company_variety_length_stems_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
