<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tipo_formularios', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
   public function down(): void
{
    // Antes de dropar, remover FKs que dependem dela
    Schema::table('recados', function (Blueprint $table) {
        $table->dropForeign(['tipo_formulario_id']);
        $table->dropColumn('tipo_formulario_id');
    });

    Schema::dropIfExists('tipo_formularios');
}

};
