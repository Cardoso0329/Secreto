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
    Schema::table('users', function (Blueprint $table) {
        $table->unsignedBigInteger('grupo_id')->nullable()->after('cargo_id');
        $table->foreign('grupo_id')->references('id')->on('grupos')->onDelete('set null');
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropForeign(['grupo_id']);
        $table->dropColumn('grupo_id');
    });
}

};
