<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Menambahkan kolom views_count dengan nilai default 0
            $table->unsignedBigInteger('views_count')->default(0)->after('content');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Menghapus kolom jika dilakukan rollback
            $table->dropColumn('views_count');
        });
    }
};
