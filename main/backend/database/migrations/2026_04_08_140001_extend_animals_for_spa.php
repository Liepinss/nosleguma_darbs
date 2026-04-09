<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('animals')) {
            return;
        }

        Schema::table('animals', function (Blueprint $table) {
            if (! Schema::hasColumn('animals', 'species')) {
                $table->string('species')->default('Cits')->after('name');
            }
            if (! Schema::hasColumn('animals', 'gender')) {
                $table->string('gender')->nullable()->after('species');
            }
        });

        if (Schema::hasColumn('animals', 'category_id') && Schema::hasTable('categories')) {
            DB::table('animals')->whereNull('category_id')->update(['category_id' => 1]);
        }
    }

    public function down(): void
    {
        Schema::table('animals', function (Blueprint $table) {
            if (Schema::hasColumn('animals', 'species')) {
                $table->dropColumn('species');
            }
            if (Schema::hasColumn('animals', 'gender')) {
                $table->dropColumn('gender');
            }
        });
    }
};
