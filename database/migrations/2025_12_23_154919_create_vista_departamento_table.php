<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('vista_departamento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vista_id')->constrained()->cascadeOnDelete();
            $table->foreignId('departamento_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vista_departamento');
    }
};
