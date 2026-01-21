<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chefia_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chefia_id')->constrained('chefias')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['chefia_id', 'user_id']); // evita duplicados
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chefia_user');
    }
};
