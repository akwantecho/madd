<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Contracts with their line items, payment schedule and terms.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('client_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('exhibition_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('عقد خدمات');
            $table->string('currency')->default('ر.ع');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('Upcoming');
            $table->decimal('vat_rate', 5, 2)->default(5);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contract_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->string('description')->nullable();
            $table->decimal('qty', 12, 2)->default(1);
            $table->decimal('price', 15, 2)->default(0);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('payment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->string('description')->nullable();
            $table->decimal('percent', 5, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('contract_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_terms');
        Schema::dropIfExists('payment_schedules');
        Schema::dropIfExists('contract_items');
        Schema::dropIfExists('contracts');
    }
};
