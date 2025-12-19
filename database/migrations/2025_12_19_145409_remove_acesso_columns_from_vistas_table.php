<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vistas', function (Blueprint $table) {

            if (Schema::hasColumn('vistas', 'acesso')) {
                $table->dropColumn('acesso');
            }

            if (Schema::hasColumn('vistas', 'usuarios_acesso')) {
                $table->dropColumn('usuarios_acesso');
            }

        });
    }

    public function down(): void
    {
        Schema::table('vistas', function (Blueprint $table) {
            // opcional: não precisamos repor estas colunas
        });
    }
};
