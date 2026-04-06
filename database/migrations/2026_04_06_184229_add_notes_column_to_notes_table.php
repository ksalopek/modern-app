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
        Schema::table('notes', function (Blueprint $table) {
            // We use nullable() so MySQL doesn't crash when it looks at your
            // existing rows that don't have a value for this new column.
            // We use after() just to keep the columns organized in the database.
            $table->text('notes')->nullable()->after('content');
        });
    }

    public function down(): void
    {
        // The down method tells Laravel how to undo this specific change
        Schema::table('notes', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
};
