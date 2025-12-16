<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
      Schema::create('vistas', function (Blueprint $table) {
    $table->id();
    $table->string('nome');
    $table->foreignId('user_id')->nullable(); // dono da vista
    $table->enum('acesso', ['privado', 'publico'])->default('privado');
    $table->json('filtros'); // aqui vamos guardar os filtros
    $table->timestamps();
});

    }

    public function down(): void
    {
        Schema::dropIfExists('vistas');
    }
};
