<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Core reference tables: exhibitions, contacts (entities / clients /
 * organizers / suppliers), financial accounts and key-value settings.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exhibitions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('location')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('Upcoming');
            $table->string('tag')->nullable();
            $table->string('tag_color')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Single table for all directory contacts, split by `type`.
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('type');              // entity | client | organizer | supplier
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('representative')->nullable(); // entities
            $table->string('category')->nullable();        // suppliers
            $table->string('vat_no')->nullable();
            $table->string('address')->nullable();
            $table->string('country')->nullable()->default('سلطنة عُمان');
            $table->foreignId('entity_id')->nullable()->constrained('contacts')->nullOnDelete(); // clients -> entity
            $table->unsignedInteger('persons')->nullable();  // entities
            $table->unsignedInteger('events')->nullable();   // organizers
            $table->string('status')->default('Active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->default('bi-bank');
            $table->decimal('book_balance', 15, 3)->default(0);
            $table->decimal('statement_balance', 15, 3)->default(0);
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('exhibitions');
    }
};
