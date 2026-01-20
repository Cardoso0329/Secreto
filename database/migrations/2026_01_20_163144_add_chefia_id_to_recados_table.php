<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recados', function (Blueprint $table) {

            // ✅ Só Call Center vai preencher => nullable
            $table->foreignId('chefia_id')
                ->nullable()
                ->constrained('chefias')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('recados', function (Blueprint $table) {
            $table->dropForeign(['chefia_id']);
            $table->dropColumn('chefia_id');
        });
    }
};
