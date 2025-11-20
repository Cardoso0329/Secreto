<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recados', function (Blueprint $table) {
            $table->string('assunto')->after('wip')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('recados', function (Blueprint $table) {
            $table->dropColumn('assunto');
        });
    }
};
