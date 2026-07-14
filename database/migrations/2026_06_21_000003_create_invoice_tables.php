<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tax invoices with their line items.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('client_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('contract_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('exhibition_id')->nullable()->constrained()->nullOnDelete();
            $table->string('currency')->default('ر.س');
            $table->date('issue_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('status')->default('Unpaid');
            $table->decimal('vat_rate', 5, 2)->default(15);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('paid', 15, 2)->default(0);
            $table->string('po')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('description')->nullable();
            $table->decimal('qty', 12, 2)->default(1);
            $table->decimal('price', 15, 2)->default(0);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
