<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('recados', function (Blueprint $table) {
            $table->timestamp('anonymized_at')->nullable()->index()->after('termino');
        });
    }

    public function down(): void
    {
        Schema::table('recados', function (Blueprint $table) {
            $table->dropColumn('anonymized_at');
        });
    }
};
