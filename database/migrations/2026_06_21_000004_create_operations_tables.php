<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Operational tables: stock, tasks, and exhibition sub-records
 * (documents, expenses, revenues, setup steps).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_items', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('equipment'); // equipment | service
            $table->string('name');
            $table->string('sku')->nullable();
            $table->unsignedInteger('quantity')->nullable();
            $table->unsignedInteger('available')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('exhibition_id')->nullable()->constrained()->nullOnDelete();
            $table->string('assignee')->nullable();
            $table->date('due_date')->nullable();
            $table->string('priority')->default('Medium');
            $table->string('status')->default('Upcoming');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exhibition_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('type')->nullable();
            $table->string('size')->nullable();
            $table->date('doc_date')->nullable();
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exhibition_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('item');
            $table->string('vendor')->nullable();
            $table->string('category')->nullable();
            $table->decimal('amount', 15, 3)->default(0);
            $table->date('expense_date')->nullable();
            $table->string('status')->default('Unpaid');
            $table->timestamps();
        });

        Schema::create('revenues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source');
            $table->string('reference')->nullable();
            $table->decimal('amount', 15, 3)->default(0);
            $table->date('revenue_date')->nullable();
            $table->string('status')->default('Unpaid');
            $table->timestamps();
        });

        Schema::create('setup_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exhibition_id')->constrained()->cascadeOnDelete();
            $table->string('step');
            $table->string('owner')->nullable();
            $table->date('step_date')->nullable();
            $table->string('status')->default('Upcoming');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setup_steps');
        Schema::dropIfExists('revenues');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('stock_items');
    }
};
