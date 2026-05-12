<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('applications')) {
            return;
        }

        Schema::table('applications', function (Blueprint $table) {
            if (! Schema::hasColumn('applications', 'phone')) {
                $table->string('phone', 32)->nullable()->after('message');
            }
            if (! Schema::hasColumn('applications', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }
            if (! Schema::hasColumn('applications', 'experience')) {
                $table->text('experience')->nullable()->after('address');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('applications')) {
            return;
        }

        Schema::table('applications', function (Blueprint $table) {
            $cols = array_filter([
                Schema::hasColumn('applications', 'phone') ? 'phone' : null,
                Schema::hasColumn('applications', 'address') ? 'address' : null,
                Schema::hasColumn('applications', 'experience') ? 'experience' : null,
            ]);
            if ($cols !== []) {
                $table->dropColumn($cols);
            }
        });
    }
};
