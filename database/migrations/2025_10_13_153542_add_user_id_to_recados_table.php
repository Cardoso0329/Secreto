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
        $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
    });
}

public function down()
{
    Schema::table('recados', function (Blueprint $table) {
        $table->dropConstrainedForeignId('user_id');
    });
}

};
