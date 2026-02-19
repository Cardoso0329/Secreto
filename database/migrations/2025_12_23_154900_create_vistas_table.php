<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('vistas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');

            // filtros dos recados
            $table->json('filtros');

            // AND / OR
            $table->enum('logica', ['AND', 'OR'])->default('AND');

            // acessos
            $table->enum('acesso', ['all', 'department', 'owner', 'specific'])
                  ->default('owner');

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vistas');
    }
};
