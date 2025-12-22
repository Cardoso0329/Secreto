<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('recados', function (Blueprint $table) {
        $table->foreignId('campanha_id')
            ->nullable()
            ->constrained('campanhas')
            ->nullOnDelete();
    });
}

public function down()
{
    Schema::table('recados', function (Blueprint $table) {
        $table->dropForeign(['campanha_id']);
        $table->dropColumn('campanha_id');
    });
}

};
