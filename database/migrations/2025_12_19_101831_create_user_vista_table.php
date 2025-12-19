<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_vista', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('vista_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // 👇 DEFINE SE É PESSOAL OU DEPARTAMENTO
            $table->enum('tipo', ['pessoal', 'departamento']);

            $table->timestamps();

            // impede duplicados
            $table->unique(['user_id', 'vista_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_vista');
    }
};
