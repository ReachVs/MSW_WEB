<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->string('main_category')->nullable()->after('category'); // 'maintenance', 'washing', 'engine_checkup', 'tuning'
            $table->string('sub_category')->nullable()->after('main_category'); // Parent sub-category name
            $table->unsignedBigInteger('parent_id')->nullable()->after('sub_category'); // For nested items
            $table->integer('selection_mode')->default(0)->after('parent_id'); // 0 = standalone, 1 = selectable option

            // Add index for faster queries
            $table->index(['main_category', 'sub_category']);
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->dropColumn(['main_category', 'sub_category', 'parent_id', 'selection_mode']);
        });
    }
};
