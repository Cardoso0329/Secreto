<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('recados', function (Blueprint $table) {
            $table->foreignId('tipo_formulario_id')->nullable()->constrained('tipo_formularios');
            $table->string('wip')->nullable(); // Só será usado em "Call Center"
        });
    }

    public function down(): void {
        Schema::table('recados', function (Blueprint $table) {
            $table->dropForeign(['tipo_formulario_id']);
            $table->dropColumn(['tipo_formulario_id', 'wip']);
        });
    }
};