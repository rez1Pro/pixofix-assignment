<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subfolders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->boolean('is_open')->default(true);
            $table->timestamps();

            // Unique name per folder
            $table->unique(['folder_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subfolders');
    }
};