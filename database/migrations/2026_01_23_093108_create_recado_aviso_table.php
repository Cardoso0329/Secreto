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
        Schema::create('recado_aviso', function (Blueprint $table) {
    $table->id();
    $table->foreignId('recado_id')->constrained()->onDelete('cascade');
    $table->foreignId('aviso_id')->constrained()->onDelete('cascade');
    $table->timestamps();

    $table->unique(['recado_id', 'aviso_id']);
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recado_aviso');
    }
};
