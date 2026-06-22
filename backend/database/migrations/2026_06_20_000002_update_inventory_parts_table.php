<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_parts', function (Blueprint $table): void {
            $table->unsignedBigInteger('category_id')->nullable()->after('description');
            $table->unsignedInteger('minimum_stock')->default(5)->after('stock_qty');
            $table->string('status')->default('active')->after('minimum_stock');
        });

        // Map existing category strings to category IDs
        $dbParts = DB::table('inventory_parts')->get();
        foreach ($dbParts as $part) {
            $categoryName = $part->category ?? 'Accessories';
            if ($categoryName === 'Other') {
                $categoryName = 'Accessories';
            }

            $catId = DB::table('categories')->where('name', $categoryName)->value('id');
            if ($catId) {
                DB::table('inventory_parts')
                    ->where('id', $part->id)
                    ->update(['category_id' => $catId]);
            }
        }

        Schema::table('inventory_parts', function (Blueprint $table): void {
            $table->dropColumn('category');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_parts', function (Blueprint $table): void {
            $table->dropForeign(['category_id']);
            $table->enum('category', ['Brakes', 'Engine', 'Drivetrain', 'Suspension', 'Electrical', 'Other'])->default('Other');
        });

        Schema::table('inventory_parts', function (Blueprint $table): void {
            $table->dropColumn(['category_id', 'minimum_stock', 'status']);
        });
    }
};
