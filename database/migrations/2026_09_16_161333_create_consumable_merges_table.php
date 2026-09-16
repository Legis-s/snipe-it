<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('consumable_merges', function (Blueprint $table) {
            $table->id();
            $table->integer('source_id')->unique();
            $table->integer('target_id')->index();
            $table->integer('source_purchase_id')->nullable()->index();
            $table->integer('created_by')->nullable();
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consumable_merges');
    }
};
