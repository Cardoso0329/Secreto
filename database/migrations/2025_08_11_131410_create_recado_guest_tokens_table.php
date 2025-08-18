<?php
// database/migrations/xxxx_xx_xx_create_recado_guest_tokens_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('recado_guest_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recado_id')->constrained()->onDelete('cascade');
            $table->string('token', 80)->unique();
            $table->timestamp('expires_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('recado_guest_tokens');
    }
};
