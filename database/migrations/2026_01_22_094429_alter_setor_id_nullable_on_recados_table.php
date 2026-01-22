<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('recados', function (Blueprint $table) {
            $table->dropForeign(['setor_id']);
        });

        Schema::table('recados', function (Blueprint $table) {
            $table->unsignedBigInteger('setor_id')->nullable()->change();
        });

        Schema::table('recados', function (Blueprint $table) {
            $table->foreign('setor_id')->references('id')->on('setores')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('recados', function (Blueprint $table) {
            $table->dropForeign(['setor_id']);
        });

        Schema::table('recados', function (Blueprint $table) {
            $table->unsignedBigInteger('setor_id')->nullable(false)->change();
        });

        Schema::table('recados', function (Blueprint $table) {
            $table->foreign('setor_id')->references('id')->on('setores')->cascadeOnDelete();
        });
    }
};

