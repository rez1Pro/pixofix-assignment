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
        Schema::table('orders', function (Blueprint $table) {
            // Add customer_name and deadline columns if they don't exist
            if (!Schema::hasColumn('orders', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('description');
            }

            if (!Schema::hasColumn('orders', 'deadline')) {
                $table->date('deadline')->nullable()->after('customer_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop columns if they exist
            if (Schema::hasColumn('orders', 'customer_name')) {
                $table->dropColumn('customer_name');
            }

            if (Schema::hasColumn('orders', 'deadline')) {
                $table->dropColumn('deadline');
            }
        });
    }
};