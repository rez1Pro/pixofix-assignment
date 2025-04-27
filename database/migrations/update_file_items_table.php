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
        Schema::table('file_items', function (Blueprint $table) {
            // Add new columns
            $table->foreignId('folder_id')->nullable()->after('order_id')->constrained()->nullOnDelete();
            $table->foreignId('subfolder_id')->nullable()->after('folder_id')->constrained()->nullOnDelete();

            // Rename columns to match the new structure
            $table->renameColumn('filename', 'name');
            $table->renameColumn('original_filename', 'original_name');
            $table->renameColumn('filepath', 'path');

            // Remove old columns
            $table->dropColumn('directory_path');
            $table->dropColumn('is_processed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_items', function (Blueprint $table) {
            // Add back old columns
            $table->string('directory_path')->nullable()->after('path');
            $table->boolean('is_processed')->default(false)->after('file_size');

            // Rename columns back to original names
            $table->renameColumn('name', 'filename');
            $table->renameColumn('original_name', 'original_filename');
            $table->renameColumn('path', 'filepath');

            // Drop new columns
            $table->dropForeign(['folder_id']);
            $table->dropForeign(['subfolder_id']);
            $table->dropColumn(['folder_id', 'subfolder_id']);
        });
    }
};