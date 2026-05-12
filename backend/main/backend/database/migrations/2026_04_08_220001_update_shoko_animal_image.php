<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function shokoImage(): string
    {
        return 'https://images.unsplash.com/photo-1544568100-847a948585b9?auto=format&fit=crop&w=640&q=82';
    }

    public function up(): void
    {
        if (! Schema::hasTable('animals')) {
            return;
        }

        DB::table('animals')->where('name', 'Šoko')->update(['image' => $this->shokoImage()]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('animals')) {
            return;
        }

        DB::table('animals')->where('name', 'Šoko')->update([
            'image' => 'https://cdn.pixabay.com/photo/2016/11/29/03/45/animal-1868916_640.jpg',
        ]);
    }
};
