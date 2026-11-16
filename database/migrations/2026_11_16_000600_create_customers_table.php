<?php

use App\Enums\Incoterm;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('locale', 5)->default('en');
            $table->string('invoice_email')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('incoterm', 10)->default(Incoterm::Fob->value);
            $table->unsignedSmallInteger('credit_days')->default(0);
            $table->unsignedBigInteger('credit_limit_cents')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // deleted_at keeps the index from blocking a name reused after a soft delete.
            $table->unique(['company_id', 'name', 'deleted_at']);
            $table->index(['company_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
