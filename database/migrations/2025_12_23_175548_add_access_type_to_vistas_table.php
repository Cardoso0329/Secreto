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
    
    Schema::table('vistas', function (Blueprint $table) {
        $table->string('access_type')->default('all');
    });
}

public function down()
{
    Schema::table('vistas', function (Blueprint $table) {
        $table->dropColumn('access_type');
    });
}

};
