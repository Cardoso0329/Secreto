<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chefias', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // nome da chefia
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chefias');
    }
};
